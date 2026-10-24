<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\ValueObject\HolderId;

final class HolderAlreadyExistsException extends \DomainException
{
    public function __construct(HolderId $holderId)
    {
        parent::__construct(\sprintf('A holder with id "%s" already exists.', $holderId->value));
    }
}
