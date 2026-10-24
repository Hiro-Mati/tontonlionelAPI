<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\ApiPlatform\V1\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Domain\Entity\Holder;
use App\Infrastructure\Presentation\ApiPlatform\V1\State\HolderProcessor;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\OpenApi\Model\Operation;

/**
 * No operation is exposed yet: they will be declared here once the matching
 * providers and processors exist under the V1 State namespace.
 */
#[ApiResource(
    shortName: 'Holder',
    operations: [
        new Post(
            uriTemplate: '/holders',
            openapi: new Operation(
                tags: ['V1 - Holders'],
                summary: 'Register a holder',
                description: 'Registers a holder, active by default.',
            ),
            deserialize: false,
            processor: HolderProcessor::class

        ),
    ],
    routePrefix: '/v1',
)]
final class HolderResource
{
    #[Assert\NotBlank(message: 'A holder name is required.')]
    public ?string $holderName = null;
    #[Assert\NotBlank(message: 'A birth date is required.')]
    public ?string $birthDate = null;

    public static function fromEntity(Holder $holder): self
    {
        $resource = new self();
        $resource->holderName = $holder->name()->value;
        $resource->birthDate = $holder->birthDate()->value->format('Y-m-d');

        return $resource;
    }
}
