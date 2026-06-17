<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Exception;

/**
 * Base exception for the MAX Bot API client. All other library exceptions extend it
 * so callers can catch any failure with a single catch clause.
 */
class MaxBotApiException extends \RuntimeException {}
