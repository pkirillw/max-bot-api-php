<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Client;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as PsrHttpClientInterface;
use Psr\Http\Client\NetworkExceptionInterface as PsrNetworkException;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Pkirillw\MaxBotApi\Exception\ApiException;
use Pkirillw\MaxBotApi\Exception\EmptyTokenException;
use Pkirillw\MaxBotApi\Exception\NetworkException;
use Pkirillw\MaxBotApi\Exception\SerializationException;
use Pkirillw\MaxBotApi\Exception\TimeoutException;

/**
 * Thin wrapper over PSR-18 / PSR-17 that adds:
 *  - Authorization header injection
 *  - API versioning (?v=<version>)
 *  - JSON encoding of request bodies and decoding of responses
 *  - Translation of transport-level errors into library exceptions
 *
 * The class is stateless beyond its constructor arguments: one Client can be
 * shared across many Endpoint instances.
 */
final class Client
{
    public const SECRET_HEADER = 'X-Max-Bot-Api-Secret';
    public const USER_AGENT_TEMPLATE = 'max-bot-api-client-php/{version}';

    private Options $options;

    public function __construct(
        private string $token,
        Options $options,
        private PsrHttpClientInterface $http,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
        if ($token === '') {
            throw new EmptyTokenException();
        }
        $this->options = $options;
    }

    public function getOptions(): Options
    {
        return $this->options;
    }

    public function setOptions(Options $options): void
    {
        $this->options = $options;
    }

    /**
     * Builds and sends a JSON request, returns the decoded body as a PSR-7 stream
     * (caller is responsible for draining it).
     *
     * @param string                                                 $method HTTP verb
     * @param string                                                 $path   path under the base URL
     * @param array<string, string|list<string>|int|float|bool>      $query  query params
     * @param \JsonSerializable|array<string, mixed>|null            $body   JSON body
     * @param bool                                                   $reset  when true, omits Authorization header (used for token-less requests)
     */
    public function request(string $method, string $path, array $query = [], \JsonSerializable|array|null $body = null, bool $reset = false): ResponseInterface
    {
        $uri = $this->buildUri($path, $query);

        $request = $this->requestFactory->createRequest($method, $uri);
        $request = $request->withHeader('User-Agent', str_replace('{version}', $this->options->version, self::USER_AGENT_TEMPLATE));
        $request = $request->withHeader('Accept', 'application/json');

        if (!$reset) {
            $request = $request->withHeader('Authorization', $this->token);
        }

        if ($body !== null) {
            try {
                $json = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } catch (\JsonException $e) {
                throw new SerializationException('marshal', 'request body', $e);
            }
            $request = $request->withBody($this->streamFactory->createStream($json));
            $request = $request->withHeader('Content-Type', 'application/json');
        }

        return $this->send($request, sprintf('%s %s', $method, $path));
    }

    /**
     * Send a prepared request and decode a JSON response into an array.
     *
     * @return array<string, mixed>
     */
    public function requestJson(string $method, string $path, array $query = [], \JsonSerializable|array|null $body = null, bool $reset = false): array
    {
        $response = $this->request($method, $path, $query, $body, $reset);
        return $this->decodeResponse($response, sprintf('%s %s', $method, $path));
    }

    /**
     * @return array<string, mixed>
     */
    public function decodeResponse(ResponseInterface $response, string $operation): array
    {
        $body = (string)$response->getBody();
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            $this->raiseApiError($response->getStatusCode(), $body, $operation);
        }

        if ($body === '') {
            return [];
        }

        try {
            $decoded = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new SerializationException('unmarshal', $operation, $e);
        }

        return is_array($decoded) ? $decoded : ['value' => $decoded];
    }

    /**
     * Sends a multipart upload to a previously obtained upload endpoint.
     *
     * @param array<string, string> $headers additional request headers (e.g. Content-Type)
     * @param string|resource       $body    request body (stream or string)
     */
    public function sendRaw(string $method, string $url, mixed $body, array $headers = []): ResponseInterface
    {
        $request = $this->requestFactory->createRequest($method, $url);
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if (is_string($body)) {
            $request = $request->withBody($this->streamFactory->createStream($body));
        } elseif (is_resource($body)) {
            $request = $request->withBody($this->streamFactory->createStreamFromResource($body));
        }

        return $this->send($request, $method . ' ' . $url);
    }

    private function send(RequestInterface $request, string $operation): ResponseInterface
    {
        try {
            return $this->http->sendRequest($request);
        } catch (PsrNetworkException $e) {
            throw new TimeoutException($operation, $e->getMessage());
        } catch (RequestExceptionInterface $e) {
            throw new NetworkException($operation, $e);
        } catch (ClientExceptionInterface $e) {
            // PSR-18 recommends checking for timeout-shaped failures via exception type.
            $message = $e->getMessage();
            if (str_contains(strtolower($message), 'timed out') || str_contains(strtolower($message), 'timeout')) {
                throw new TimeoutException($operation, $message);
            }
            throw new NetworkException($operation, $e);
        }
    }

    /**
     * @param array<string, string|list<string>|int|float|bool> $query
     */
    private function buildUri(string $path, array $query): string
    {
        $base = rtrim($this->options->baseUrl, '/');
        $path = '/' . ltrim($path, '/');

        $params = $query;
        $params['v'] = $this->options->version;

        $queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        return $base . $path . ($queryString !== '' ? '?' . $queryString : '');
    }

    private function raiseApiError(int $httpCode, string $body, string $operation): never
    {
        $apiCode = '';
        $details = null;

        if ($body !== '') {
            try {
                $decoded = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    $apiCode = (string)($decoded['code'] ?? '');
                    $details = isset($decoded['message']) ? (string)$decoded['message'] : null;
                }
            } catch (\JsonException) {
                $details = $body;
            }
        }

        throw new ApiException(
            httpCode: $httpCode,
            apiCode: $apiCode,
            details: $details,
        );
    }
}
