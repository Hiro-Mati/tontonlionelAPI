<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\ApiPlatform\V1\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use App\Domain\Entity\Holder;
use App\Infrastructure\Presentation\ApiPlatform\V1\State\GetHolderProvider;
use App\Infrastructure\Presentation\ApiPlatform\V1\State\HolderProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Holder',
    operations: [
        new Post(
            uriTemplate: '/holders',
            security: "is_granted('ROLE_OPERATOR')",
            openapi: new Operation(
                tags: ['V1 - Holders'],
                summary: 'Register a holder',
                description: 'Registers a holder (data subject), active by default.',
            ),
            deserialize: false,
            processor: HolderProcessor::class,
        ),
        new Get(
            uriTemplate: '/holders/{id}',
            security: "is_granted('ROLE_READER')",
            openapi: new Operation(
                tags: ['V1 - Holders'],
                summary: 'Get a holder',
                description: 'Retrieves a single holder by id.',
            ),
            provider: GetHolderProvider::class,
        ),
    ],
    routePrefix: '/v1',
)]
final class HolderResource
{
    #[ApiProperty(identifier: true)]
    public ?string $id = null;

    /**
     * With `deserialize: false`, API Platform never populates these fields
     * itself: HolderProcessor reads them from the raw request and validates
     * them explicitly against these constraints before use.
     */
    #[Assert\NotBlank(message: 'A holder name is required.')]
    public ?string $holderName = null;

    #[Assert\NotBlank(message: 'A birth date is required.')]
    #[Assert\Date(message: 'The birth date must use the Y-m-d format.')]
    public ?string $birthDate = null;

    public ?string $status = null;

    public ?string $createdAt = null;

    public ?string $consentCollectedAt = null;

    public ?string $erasureRequestedAt = null;

    public ?string $anonymizedAt = null;

    public static function fromEntity(Holder $holder): self
    {
        $resource = new self();
        $resource->id = $holder->id()->value;
        $resource->holderName = $holder->name()->value;
        $resource->birthDate = (string) $holder->birthDate();
        $resource->status = $holder->status()->value;
        $resource->createdAt = $holder->createdAt()->format(\DateTimeInterface::ATOM);
        $resource->consentCollectedAt = $holder->consentCollectedAt()?->format(\DateTimeInterface::ATOM);
        $resource->erasureRequestedAt = $holder->erasureRequestedAt()?->format(\DateTimeInterface::ATOM);
        $resource->anonymizedAt = $holder->anonymizedAt()?->format(\DateTimeInterface::ATOM);

        return $resource;
    }
}
