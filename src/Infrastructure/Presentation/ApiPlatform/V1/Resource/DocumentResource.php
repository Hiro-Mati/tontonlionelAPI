<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\ApiPlatform\V1\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use App\Domain\Entity\Document;
use App\Infrastructure\Presentation\ApiPlatform\V1\State\DocumentProcessor;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Document',
    operations: [
        new Post(
            uriTemplate: '/documents',
            security: "is_granted('ROLE_OPERATOR')",
            inputFormats: ['multipart' => ['multipart/form-data']],
            openapi: new Operation(
                tags: ['V1 - Documents'],
                summary: 'Store a document',
                description: 'Ingests a document on behalf of a holder, encrypts it (envelope encryption) and stores it on the Zero Trust storage backend.',
            ),
            deserialize: false,
            processor: DocumentProcessor::class,
        ),
    ],
    routePrefix: '/v1',
)]
final class DocumentResource
{
    #[ApiProperty(identifier: true)]
    public ?string $id = null;

    /**
     * With `deserialize: false`, API Platform never populates these fields
     * itself: DocumentProcessor reads them from the raw request and
     * validates them explicitly against these constraints before use.
     * The size and MIME type limits (DOCUMENT_MAX_SIZE, DOCUMENT_MIME_TYPES)
     * are deployment settings, checked by the processor at runtime.
     */
    #[Assert\NotNull(message: 'A valid "file" upload is required.')]
    #[Assert\File]
    // Input only: never serialised back in the 201 response nor in the output schema.
    #[ApiProperty(readable: false)]
    public ?UploadedFile $file = null;

    /**
     * Identifier of the Holder (data subject) the document belongs to,
     * sent as a regular multipart field next to the file.
     */
    #[Assert\NotBlank(message: 'A "holderId" is required.')]
    #[Assert\Uuid(message: 'The "holderId" must be a valid UUID.')]
    public ?string $holderId = null;

    public ?string $fileHash = null;

    public ?string $createdAt = null;

    public static function fromEntity(Document $document): self
    {
        $resource = new self();
        $resource->id = $document->id()->value;
        $resource->holderId = $document->holderId()->value;
        $resource->fileHash = $document->fileHash()->value;
        $resource->createdAt = $document->createdAt()->format(\DateTimeInterface::ATOM);

        return $resource;
    }
}
