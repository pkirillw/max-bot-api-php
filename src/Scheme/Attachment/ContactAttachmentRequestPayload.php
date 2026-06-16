<?php

declare(strict_types=1);

namespace Pkirillw\MaxBotApi\Scheme\Attachment;

final readonly class ContactAttachmentRequestPayload implements \JsonSerializable
{
    public function __construct(
        public string $name = '',
        public int $contactId = 0,
        public string $vcfInfo = '',
        public string $vcfPhone = '',
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromJson(array $data): self
    {
        return new self(
            name: (string)($data['name'] ?? ''),
            contactId: (int)($data['contact_id'] ?? 0),
            vcfInfo: (string)($data['vcf_info'] ?? ''),
            vcfPhone: (string)($data['vcf_phone'] ?? ''),
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'name' => $this->name,
            'contact_id' => $this->contactId,
            'vcf_info' => $this->vcfInfo,
            'vcf_phone' => $this->vcfPhone,
        ], static fn(mixed $v) => $v !== null && $v !== '' && $v !== 0);
    }
}
