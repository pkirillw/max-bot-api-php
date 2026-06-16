<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Exception;

/**
 * Thrown when MAX API responds with a non-2xx status. Carries the HTTP status code
 * and the API-level error code so callers can match on known failures.
 */
class ApiException extends MaxBotApiException
{
    public function __construct(
        public readonly int $httpCode,
        public readonly string $apiCode,
        public readonly ?string $details = null,
    ) {
        $message = $apiCode !== ''
            ? sprintf('API error %d: %s', $httpCode, $apiCode)
            : sprintf('API error %d', $httpCode);
        if ($details !== null && $details !== '') {
            $message .= sprintf(' (%s)', $details);
        }
        parent::__construct($message);
    }

    /**
     * Returns true when attachment upload is still being processed on the server.
     * Matches the "attachment.not.ready" sentinel from MAX.
     */
    public function isAttachmentNotReady(): bool
    {
        return $this->apiCode === 'attachment.not.ready';
    }
}
