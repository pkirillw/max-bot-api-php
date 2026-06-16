<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

use Pkirillw\MaxBotApi\Scheme\User;

final readonly class ContactAttachmentPayload implements \JsonSerializable
{
    public function __construct(
        public string $vcfInfo = '',
        public ?User $maxInfo = null,
        public string $hash = '',
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            vcfInfo: (string)($data['vcf_info'] ?? ''),
            maxInfo: isset($data['max_info']) ? User::fromJson((array)$data['max_info']) : null,
            hash: (string)($data['hash'] ?? ''),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'vcf_info' => $this->vcfInfo,
            'max_info' => $this->maxInfo?->jsonSerialize(),
            'hash' => $this->hash,
        ];
    }
}
