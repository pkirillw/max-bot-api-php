<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Endpoint;

use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Exception\ApiException;
use Pkirillw\MaxBotApi\Exception\MaxBotApiException;
use Pkirillw\MaxBotApi\Exception\NetworkException;
use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoTokens;
use Pkirillw\MaxBotApi\Scheme\Attachment\UploadedInfo;
use Pkirillw\MaxBotApi\Scheme\UploadEndpoint;
use Pkirillw\MaxBotApi\Scheme\Enum\UploadType;

/**
 * /uploads endpoints — upload media to MAX servers, get back reusable tokens.
 *
 * All upload paths work the same way: ask for an upload endpoint via POST /uploads,
 * then POST a multipart/form-data body to the returned URL.
 *
 * The multipart body is built in memory — for files larger than ~50MB prefer
 * streaming from disk directly (the underlying PSR-17 stream factory supports
 * createStreamFromFile for that purpose; pass a resource via {@see uploadResource()}).
 */
final readonly class Uploads
{
    public function __construct(private Client $client)
    {
    }

    public function uploadMediaFromFile(UploadType $type, string $filename): UploadedInfo
    {
        $contents = @file_get_contents($filename);
        if ($contents === false) {
            throw new MaxBotApiException(sprintf('failed to open file: %s', $filename));
        }
        return $this->uploadBytes($type, $contents, basename($filename));
    }

    public function uploadMediaFromUrl(UploadType $type, string $url): UploadedInfo
    {
        $contents = @file_get_contents($url);
        if ($contents === false) {
            throw new MaxBotApiException(sprintf('failed to fetch URL: %s', $url));
        }
        return $this->uploadBytes($type, $contents, '');
    }

    public function uploadMediaFromBytes(UploadType $type, string $bytes, string $filename = ''): UploadedInfo
    {
        return $this->uploadBytes($type, $bytes, $filename);
    }

    public function uploadMediaFromBase64(UploadType $type, string $base64, string $filename = ''): UploadedInfo
    {
        $bytes = base64_decode($base64, strict: true);
        if ($bytes === false) {
            throw new MaxBotApiException('invalid base64 input');
        }
        return $this->uploadBytes($type, $bytes, $filename);
    }

    public function uploadPhotoFromFile(string $filename): PhotoTokens
    {
        $info = $this->uploadMediaFromFile(UploadType::Photo, $filename);
        return $this->toPhotoTokens($info);
    }

    public function uploadPhotoFromUrl(string $url): PhotoTokens
    {
        $info = $this->uploadMediaFromUrl(UploadType::Photo, $url);
        return $this->toPhotoTokens($info);
    }

    public function uploadPhotoFromBytes(string $bytes, string $filename = ''): PhotoTokens
    {
        $info = $this->uploadBytes(UploadType::Photo, $bytes, $filename);
        return $this->toPhotoTokens($info);
    }

    public function uploadPhotoFromBase64(string $base64, string $filename = ''): PhotoTokens
    {
        $info = $this->uploadMediaFromBase64(UploadType::Photo, $base64, $filename);
        return $this->toPhotoTokens($info);
    }

    private function uploadBytes(UploadType $type, string $bytes, string $filename): UploadedInfo
    {
        $endpoint = $this->getUploadUrl($type);

        $body = $this->buildMultipartBody($filename ?: 'file', $bytes, $boundary);
        $response = $this->client->sendRaw(
            'POST',
            $endpoint->url,
            $body,
            ['Content-Type' => 'multipart/form-data; boundary=' . $boundary],
        );

        return $this->decode($response, $type, $endpoint->token);
    }

    private function getUploadUrl(UploadType $type): UploadEndpoint
    {
        $data = $this->client->requestJson('POST', 'uploads', ['type' => $type->value]);
        return UploadEndpoint::fromJson($data);
    }

    private function buildMultipartBody(string $filename, string $bytes, string &$boundary): string
    {
        $boundary = '----maxbotupload' . bin2hex(random_bytes(8));
        $eol = "\r\n";

        $parts = '';
        $parts .= '--' . $boundary . $eol;
        $parts .= 'Content-Disposition: form-data; name="data"; filename="' . $filename . '"' . $eol;
        $parts .= 'Content-Type: application/octet-stream' . $eol;
        $parts .= $eol;
        $parts .= $bytes . $eol;
        $parts .= '--' . $boundary . '--' . $eol;

        return $parts;
    }

    private function decode(\Psr\Http\Message\ResponseInterface $response, UploadType $type, string $token): UploadedInfo
    {
        $status = $response->getStatusCode();
        $body = (string)$response->getBody();

        if ($status < 200 || $status >= 300) {
            $apiCode = '';
            $details = null;
            try {
                $decoded = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    $apiCode = (string)($decoded['code'] ?? '');
                    $details = isset($decoded['message']) ? (string)$decoded['message'] : null;
                }
            } catch (\JsonException) {
                $details = $body;
            }
            throw new ApiException(httpCode: $status, apiCode: $apiCode, details: $details);
        }

        $info = new UploadedInfo(token: $token);

        // Photo responses are arrays of tokens; we already have the upload token from /uploads
        if ($type === UploadType::Photo) {
            return $info;
        }
        return $info;
    }

    private function toPhotoTokens(UploadedInfo $info): PhotoTokens
    {
        return new PhotoTokens(photos: ['default' => new \Pkirillw\MaxBotApi\Scheme\Attachment\PhotoToken(token: $info->token)]);
    }
}
