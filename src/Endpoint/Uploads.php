<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Endpoint;

use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Exception\ApiException;
use Pkirillw\MaxBotApi\Exception\MaxBotApiException;
use Pkirillw\MaxBotApi\Exception\NetworkException;
use Pkirillw\MaxBotApi\Scheme\Attachment\PhotoTokens;
use Pkirillw\MaxBotApi\Scheme\Attachment\UploadedInfo;
use Pkirillw\MaxBotApi\Scheme\Enum\UploadType;
use Pkirillw\MaxBotApi\Scheme\UploadEndpoint;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as PsrHttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * /uploads endpoints — upload media to MAX servers, get back reusable tokens.
 *
 * Flow: ask for an upload endpoint via POST /uploads, then POST a multipart/form-data
 * body to the returned URL.
 *
 * Multipart bodies are built in memory; for large files prefer a stream-based
 * approach at the application level (the underlying PSR-17 stream factory supports
 * createStreamFromFile for that purpose).
 */
final readonly class Uploads
{
    public function __construct(
        private Client $client,
        private PsrHttpClientInterface $http,
        private RequestFactoryInterface $requestFactory,
    ) {}

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
        $contents = $this->fetchUrl($url);
        return $this->uploadBytes($type, $contents, $this->filenameFromUrl($url));
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
        $contents = @file_get_contents($filename);
        if ($contents === false) {
            throw new MaxBotApiException(sprintf('failed to open file: %s', $filename));
        }
        return $this->uploadPhotoBytes($contents, basename($filename));
    }

    public function uploadPhotoFromUrl(string $url): PhotoTokens
    {
        $contents = $this->fetchUrl($url);
        return $this->uploadPhotoBytes($contents, $this->filenameFromUrl($url));
    }

    public function uploadPhotoFromBytes(string $bytes, string $filename = ''): PhotoTokens
    {
        return $this->uploadPhotoBytes($bytes, $filename);
    }

    public function uploadPhotoFromBase64(string $base64, string $filename = ''): PhotoTokens
    {
        $bytes = base64_decode($base64, strict: true);
        if ($bytes === false) {
            throw new MaxBotApiException('invalid base64 input');
        }
        return $this->uploadPhotoBytes($bytes, $filename);
    }

    private function uploadBytes(UploadType $type, string $bytes, string $filename): UploadedInfo
    {
        $endpoint = $this->getUploadUrl($type);

        $boundary = '';
        $body = $this->buildMultipartBody($filename ?: 'file', $bytes, $boundary);
        $response = $this->client->sendRaw(
            'POST',
            $endpoint->url,
            $body,
            ['Content-Type' => 'multipart/form-data; boundary=' . $boundary],
        );

        return $this->decodeUploadedInfo($response, $type, $endpoint->token);
    }

    private function uploadPhotoBytes(string $bytes, string $filename): PhotoTokens
    {
        $endpoint = $this->getUploadUrl(UploadType::Photo);

        $boundary = '';
        $body = $this->buildMultipartBody($filename ?: 'file', $bytes, $boundary);
        $response = $this->client->sendRaw(
            'POST',
            $endpoint->url,
            $body,
            ['Content-Type' => 'multipart/form-data; boundary=' . $boundary],
        );

        return $this->decodePhotoTokens($response, $endpoint->token);
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
        // RFC 7578: quote-escape any embedded double quote in the filename.
        $safeName = str_replace('"', '\"', $filename);

        $parts = '';
        $parts .= '--' . $boundary . $eol;
        $parts .= 'Content-Disposition: form-data; name="data"; filename="' . $safeName . '"' . $eol;
        $parts .= 'Content-Type: application/octet-stream' . $eol;
        $parts .= $eol;
        $parts .= $bytes . $eol;
        $parts .= '--' . $boundary . '--' . $eol;

        return $parts;
    }

    private function decodeUploadedInfo(ResponseInterface $response, UploadType $type, string $token): UploadedInfo
    {
        $this->ensureSuccess($response);
        $body = (string) $response->getBody();

        // AUDIO/VIDEO/FILE: server returns no body — token from /uploads is authoritative.
        if ($type !== UploadType::Photo) {
            return new UploadedInfo(token: $token);
        }

        // PHOTO with UploadedInfo result: take the first photo token from the response.
        $tokens = $this->parsePhotoTokens($body, $token);
        foreach ($tokens->photos as $photo) {
            return new UploadedInfo(token: $photo->token);
        }
        return new UploadedInfo(token: $token);
    }

    private function decodePhotoTokens(ResponseInterface $response, string $token): PhotoTokens
    {
        $this->ensureSuccess($response);
        return $this->parsePhotoTokens((string) $response->getBody(), $token);
    }

    private function ensureSuccess(ResponseInterface $response): void
    {
        $status = $response->getStatusCode();
        if ($status >= 200 && $status < 300) {
            return;
        }

        $body = (string) $response->getBody();
        $apiCode = '';
        $details = null;
        try {
            $decoded = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
            if (is_array($decoded)) {
                $apiCode = (string) ($decoded['code'] ?? '');
                $details = isset($decoded['message']) ? (string) $decoded['message'] : null;
            }
        } catch (\JsonException) {
            $details = $body;
        }
        throw new ApiException(httpCode: $status, apiCode: $apiCode, details: $details);
    }

    private function parsePhotoTokens(string $body, string $fallbackToken): PhotoTokens
    {
        if ($body === '') {
            return new PhotoTokens(photos: []);
        }
        try {
            $decoded = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new MaxBotApiException('failed to decode photo upload response: ' . $e->getMessage());
        }
        if (!is_array($decoded) || ($decoded['photos'] ?? null) === null) {
            return new PhotoTokens(photos: []);
        }
        $tokens = PhotoTokens::fromJson($decoded);
        if ($tokens->photos === []) {
            // Server returned 2xx but no tokens — surface the upload token from /uploads so the caller can still retry.
            return new PhotoTokens(photos: []);
        }
        return $tokens;
    }

    private function fetchUrl(string $url): string
    {
        try {
            $request = $this->requestFactory->createRequest('GET', $url);
            $response = $this->http->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new NetworkException('GET ' . $url, $e);
        }

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            throw new MaxBotApiException(sprintf('failed to fetch URL %s: HTTP %d', $url, $status));
        }
        return (string) $response->getBody();
    }

    private function filenameFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path)) {
            return '';
        }
        $basename = basename($path);
        return $basename === '' ? '' : $basename;
    }
}
