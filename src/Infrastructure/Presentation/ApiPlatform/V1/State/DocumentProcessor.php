<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\ApiPlatform\V1\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use App\Application\UseCase\StoreDocument\StoreDocumentCommand;
use App\Application\UseCase\StoreDocument\StoreDocumentUseCase;
use App\Domain\Exception\HolderNotFoundException;
use App\Infrastructure\Presentation\ApiPlatform\V1\Resource\DocumentResource;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @implements ProcessorInterface<mixed, DocumentResource>
 */
final readonly class DocumentProcessor implements ProcessorInterface
{
    public function __construct(
        private StoreDocumentUseCase $useCase,
        private RequestStack $requestStack,
        private ValidatorInterface $validator,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): DocumentResource
    {
        $request = $this->requestStack->getCurrentRequest();

        $input = new DocumentResource();
        $file = $request?->files->get('file');
        $input->file = $file instanceof UploadedFile ? $file : null;
        $holderId = $request?->request->get('holderId');
        $input->holderId = \is_string($holderId) ? $holderId : null;

        $this->validator->validate($input);

        if (null === $input->file || null === $input->holderId) {
            throw new UnprocessableEntityHttpException('Missing required document parameters.');
        }

        try {
            $document = $this->useCase->execute(new StoreDocumentCommand(
                content: $input->file->getContent(),
                holderId: $input->holderId,
            ));
        } catch (HolderNotFoundException|\InvalidArgumentException $e) {
            // An unknown holder makes the request unprocessable: the resource being
            // created is the document, so this is not a 404 on the request URI.
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        return DocumentResource::fromEntity($document);
    }
}
