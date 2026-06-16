<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Webhook;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pkirillw\MaxBotApi\Scheme\Update\UpdateInterface;

/**
 * PSR-15 middleware variant of {@see WebhookHandler}. Use when you need to
 * chain additional middleware around webhook processing (logging, CORS, etc.)
 * and want MAX webhook handling to fall through to the next handler otherwise.
 *
 *   $app->add(new WebhookMiddleware($responseFactory, $secret, $myCallable));
 */
final class WebhookMiddleware implements MiddlewareInterface
{
    private WebhookHandler $handler;

    /**
     * @param callable(UpdateInterface): void|null $updateHandler
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        string $secret = '',
        bool $keepRawUpdates = false,
        ?callable $updateHandler = null,
    ) {
        $this->handler = new WebhookHandler($responseFactory, $secret, $keepRawUpdates);
        if ($updateHandler !== null) {
            $this->handler->setHandler($updateHandler);
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Only intercept POSTs; let everything else fall through.
        if ($request->getMethod() === 'POST') {
            return $this->handler->handle($request);
        }
        return $handler->handle($request);
    }

    public function withHandler(callable $handler): self
    {
        $this->handler->setHandler($handler);
        return $this;
    }
}
