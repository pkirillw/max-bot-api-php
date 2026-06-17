<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Webhook;

use Pkirillw\MaxBotApi\Client\Client;
use Pkirillw\MaxBotApi\Exception\UpdateParsingException;
use Pkirillw\MaxBotApi\Scheme\Update\UpdateInterface;
use Pkirillw\MaxBotApi\Scheme\Update\UpdateParser;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PSR-15 request handler that turns MAX webhook calls into typed UpdateInterface events.
 *
 * Usage with Slim / Mezzio / Nyholm:
 *
 *   $handler = new WebhookHandler($responseFactory, $secret, $keepRawUpdates);
 *   $handler->setHandler(function (UpdateInterface $update): void {
 *       // ...
 *   });
 *   $app->post('/webhook', $handler);
 *
 * Or via handle(): call directly inside any PSR-15 stack.
 */
final class WebhookHandler implements RequestHandlerInterface
{
    /** @var callable(UpdateInterface): void|null */
    private $handler = null;

    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private string $secret = '',
        private bool $keepRawUpdates = false,
    ) {}

    /**
     * Register the callable invoked for each parsed update.
     *
     * @param callable(UpdateInterface): void $handler
     */
    public function setHandler(callable $handler): self
    {
        $this->handler = $handler;
        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->secret !== '') {
            $received = $request->getHeaderLine(Client::SECRET_HEADER);
            if ($received === '' || !hash_equals($this->secret, $received)) {
                return $this->jsonResponse(401, ['error' => 'invalid secret']);
            }
        }

        if ($request->getMethod() !== 'POST') {
            return $this->jsonResponse(405, ['error' => 'method not allowed']);
        }

        $body = (string) $request->getBody();
        if ($body === '') {
            return $this->jsonResponse(400, ['error' => 'empty body']);
        }

        try {
            $update = UpdateParser::fromJsonString($body, $this->keepRawUpdates);
        } catch (\Throwable $e) {
            throw new UpdateParsingException($e->getMessage(), 0, $e);
        }

        if ($update === null) {
            return $this->jsonResponse(400, ['error' => 'unknown update type']);
        }

        if ($this->handler !== null) {
            ($this->handler)($update);
        }

        return $this->jsonResponse(200, ['ok' => true]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function jsonResponse(int $status, array $payload): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status);
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $response->getBody()->write($json);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
