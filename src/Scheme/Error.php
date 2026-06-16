<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme;

final readonly class Error implements \JsonSerializable
{
    /**
     * @param list<string> $numberExist
     * @param list<Results> $results
     */
    public function __construct(
        public string $errorText = '',
        public string $code = '',
        public string $message = '',
        public array $numberExist = [],
        public array $results = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        $results = [];
        foreach (($data['results'] ?? []) as $result) {
            $results[] = Results::fromJson((array)$result);
        }
        return new self(
            errorText: (string)($data['error'] ?? ''),
            code: (string)($data['code'] ?? ''),
            message: (string)($data['message'] ?? ''),
            numberExist: array_values(array_map('strval', (array)($data['existing_phone_numbers'] ?? []))),
            results: $results,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'error' => $this->errorText,
            'code' => $this->code,
            'message' => $this->message,
            'existing_phone_numbers' => $this->numberExist,
            'results' => array_map(static fn(Results $r) => $r->jsonSerialize(), $this->results),
        ], static fn(mixed $v) => $v !== null && $v !== '' && $v !== []);
    }
}
