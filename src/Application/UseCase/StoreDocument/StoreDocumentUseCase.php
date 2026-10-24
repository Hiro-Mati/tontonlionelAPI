<?php

declare(strict_types=1);

namespace App\Application\UseCase\StoreDocument;

use App\Domain\Entity\Document;
use App\Domain\Exception\HolderNotFoundException;
use App\Domain\Port\Gateway\CipherGatewayInterface;
use App\Domain\Port\Gateway\StorageGatewayInterface;
use App\Domain\Port\Repository\DocumentRepositoryInterface;
use App\Domain\Port\Repository\HolderRepositoryInterface;
use App\Domain\Port\Service\DocumentIdGeneratorInterface;
use App\Domain\ValueObject\FileHash;
use App\Domain\ValueObject\HolderId;

final readonly class StoreDocumentUseCase
{
    public function __construct(
        private CipherGatewayInterface $cipher,
        private StorageGatewayInterface $storage,
        private DocumentRepositoryInterface $documents,
        private HolderRepositoryInterface $holders,
        private DocumentIdGeneratorInterface $idGenerator,
    ) {}

    /**
     * @throws HolderNotFoundException when the document references an unknown holder
     */
    public function execute(StoreDocumentCommand $command): Document
    {
        $holderId = HolderId::fromString($command->holderId);

        if (null === $this->holders->findById($holderId)) {
            throw new HolderNotFoundException($holderId);
        }

        $fileHash = FileHash::fromPlaintext($command->content);
        $envelope = $this->cipher->encrypt($command->content);

        $id = $this->idGenerator->generate();

        $this->storage->store($id, $envelope->payload->ciphertext);

        $document = Document::create(
            id: $id,
            holderId: $holderId,
            fileHash: $fileHash,
            wrappedDataKey: $envelope->wrappedDataKey,
        );

        $this->documents->add($document);

        return $document;
    }
}
