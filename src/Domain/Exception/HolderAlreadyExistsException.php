<?php

namespace App\Domain\Exception;

use App\Domain\ValueObject\HolderId;

class HolderAlreadyExistsException extends \DomainException
{
    public function __construct(HolderId $holderId)
    {
        parent::__construct(\sprintf('A holder with string "%s" already exists.', $holderId));
    }

}
