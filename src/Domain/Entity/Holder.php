<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\InvalidHolderStatusTransitionException;
use App\Domain\ValueObject\BirthDate;
use App\Domain\ValueObject\HolderId;
use App\Domain\ValueObject\HolderName;
use App\Domain\ValueObject\HolderStatus;

final readonly class Holder
{
    private function __construct(
        private HolderId $id,
        private HolderName $name,
        private BirthDate $birthDate,
        private HolderStatus $status,
        private \DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $consentCollectedAt,
        private ?\DateTimeImmutable $erasureRequestedAt,
        private ?\DateTimeImmutable $anonymizedAt,
    ) {}

    public static function register(
        HolderId $id,
        HolderName $name,
        BirthDate $birthDate,
        LegalBasis $legalBasis,
        ?\DateTimeImmutable $consentCollectedAt = null,
    ): self {
        self::assertConsentConsistency($legalBasis, $consentCollectedAt);

        return new self(
            id: $id,
            name: $name,
            birthDate: $birthDate,
            status: HolderStatus::Active,
            createdAt: new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
            consentCollectedAt: $consentCollectedAt,
            erasureRequestedAt: null,
            anonymizedAt: null,
        );
    }

    /**
     * Records a GDPR erasure request (art. 17). Only an active holder can
     * move to the ErasureRequested state.
     *
     * @throws InvalidHolderStatusTransitionException
     */
    public function requestErasure(): self
    {
        if (HolderStatus::Active !== $this->status) {
            throw new InvalidHolderStatusTransitionException($this->status, HolderStatus::ErasureRequested);
        }

        return new self(
            id: $this->id,
            name: $this->name,
            birthDate: $this->birthDate,
            status: HolderStatus::ErasureRequested,
            createdAt: $this->createdAt,
            consentCollectedAt: $this->consentCollectedAt,
            erasureRequestedAt: new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
            anonymizedAt: $this->anonymizedAt,
        );
    }

    /**
     * Carries out the erasure by anonymizing the holder. Only a holder with a
     * pending erasure request can be anonymized.
     *
     * @throws InvalidHolderStatusTransitionException
     */
    public function anonymize(HolderName $anonymizedName): self
    {
        if (HolderStatus::ErasureRequested !== $this->status) {
            throw new InvalidHolderStatusTransitionException($this->status, HolderStatus::Anonymized);
        }

        return new self(
            id: $this->id,
            name: $anonymizedName,
            birthDate: $this->birthDate,
            status: HolderStatus::Anonymized,
            createdAt: $this->createdAt,
            consentCollectedAt: $this->consentCollectedAt,
            erasureRequestedAt: $this->erasureRequestedAt,
            anonymizedAt: new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        );
    }

    public function id(): HolderId
    {
        return $this->id;
    }

    public function name(): HolderName
    {
        return $this->name;
    }

    public function birthDate(): BirthDate
    {
        return $this->birthDate;
    }

    public function legalBasis(): LegalBasis
    {
        return $this->legalBasis;
    }

    public function status(): HolderStatus
    {
        return $this->status;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function consentCollectedAt(): ?\DateTimeImmutable
    {
        return $this->consentCollectedAt;
    }

    public function erasureRequestedAt(): ?\DateTimeImmutable
    {
        return $this->erasureRequestedAt;
    }

    public function anonymizedAt(): ?\DateTimeImmutable
    {
        return $this->anonymizedAt;
    }

    private static function assertConsentConsistency(
        LegalBasis $legalBasis,
        ?\DateTimeImmutable $consentCollectedAt,
    ): void {
        if (LegalBasis::Consent === $legalBasis && null === $consentCollectedAt) {
            throw ConsentDateRequiresConsentBasisException::consentBasisWithoutDate();
        }

        if (LegalBasis::Consent !== $legalBasis && null !== $consentCollectedAt) {
            throw ConsentDateRequiresConsentBasisException::dateWithoutConsentBasis();
        }
    }
}
