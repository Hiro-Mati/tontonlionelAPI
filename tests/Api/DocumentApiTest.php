<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Domain\Entity\Document;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class DocumentApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private const FIXTURE_PATH = __DIR__.'/../http/passport.png';

    private const HOLDER_ID = '770e8400-e29b-41d4-a716-446655440111';

    protected function setUp(): void
    {
        parent::setUp();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->createQuery('DELETE FROM '.Document::class)->execute();
    }

    public function testItStoresADocument(): void
    {
        $client = static::createClient();

        $response = $client->request('POST', '/api/v1/documents', [
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'parameters' => ['holderId' => self::HOLDER_ID],
                'files' => [
                    'file' => new UploadedFile(self::FIXTURE_PATH, 'passport.png', 'image/png', null, true),
                ],
            ],
        ]);

        self::assertResponseStatusCodeSame(201);

        $data = $response->toArray();
        self::assertArrayNotHasKey('wrappedDataKey', $data);
        self::assertArrayNotHasKey('storageKey', $data);
        self::assertNotEmpty($data['fileHash']);
        self::assertSame(self::HOLDER_ID, $data['holderId']);
    }

    public function testItRejectsAMissingFile(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/v1/documents', [
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'parameters' => ['holderId' => self::HOLDER_ID],
            ],
        ]);

        self::assertResponseStatusCodeSame(422);
    }

    public function testItRejectsAMissingHolderId(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/v1/documents', [
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'files' => [
                    'file' => new UploadedFile(self::FIXTURE_PATH, 'passport.png', 'image/png', null, true),
                ],
            ],
        ]);

        self::assertResponseStatusCodeSame(422);
    }
}
