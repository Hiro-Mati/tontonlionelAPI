<?php

namespace App\Domain\Port\Repository;

use App\Domain\Entity\Holder;

interface HolderRepositoryInterface
{
    public function findById(string $id): ?Holder;

    public function add(Holder $holder): void;

}
