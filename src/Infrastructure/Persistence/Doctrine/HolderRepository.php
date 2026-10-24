<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Entity\Holder;
use App\Domain\Port\Repository\HolderRepositoryInterface;
use App\Domain\ValueObject\HolderId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Holder>
 */
final class HolderRepository extends ServiceEntityRepository implements HolderRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Holder::class);
    }

    public function findById(HolderId $id): ?Holder
    {
        return $this->find($id->value);
    }

    public function add(Holder $holder): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($holder);
        $entityManager->flush();
    }
}
