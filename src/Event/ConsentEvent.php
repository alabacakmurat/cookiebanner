<?php

declare(strict_types=1);

namespace Havax\CookieBanner\Event;

use Havax\CookieBanner\Consent\ConsentData;
use DateTimeImmutable;

class ConsentEvent extends AbstractEvent
{
	public const TYPE_GIVEN = 'consent.given';
	public const TYPE_UPDATED = 'consent.updated';
	public const TYPE_WITHDRAWN = 'consent.withdrawn';
	public const TYPE_EXPIRED = 'consent.expired';

	private string $type;
	private ConsentData $consentData;
	private array $additionalData;

	public function __construct(string $type, ConsentData $consentData, array $additionalData = [])
	{
		$this->type = $type;
		$this->consentData = $consentData;
		$this->additionalData = $additionalData;
	}

	public function getName(): string
	{
		return $this->type;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getConsentData(): ConsentData
	{
		return $this->consentData;
	}

	public function getConsentId(): string
	{
		return $this->consentData->getConsentId();
	}

	public function getAcceptedCategories(): array
	{
		return $this->consentData->getAcceptedCategories();
	}

	public function getRejectedCategories(): array
	{
		return $this->consentData->getRejectedCategories();
	}

	public function getTimestamp(): DateTimeImmutable
	{
		return $this->consentData->getTimestamp();
	}

	public function getUserIdentifier(): ?string
	{
		return $this->consentData->getUserIdentifier();
	}

	public function getIpAddress(): string
	{
		return $this->consentData->getIpAddress();
	}

	public function getAnonymizedIpAddress(): string
	{
		return $this->consentData->getAnonymizedIpAddress();
	}

	public function getUserAgent(): string
	{
		return $this->consentData->getUserAgent();
	}

	public function getPageUrl(): string
	{
		return $this->consentData->getPageUrl();
	}

	public function getReferrer(): string
	{
		return $this->consentData->getReferrer();
	}

	public function getConsentMethod(): string
	{
		return $this->consentData->getConsentMethod();
	}

	public function getPreviousConsent(): ?array
	{
		return $this->consentData->getPreviousConsent();
	}

	public function getMetadata(): array
	{
		return $this->consentData->getMetadata();
	}

	public function getAdditionalData(): array
	{
		return $this->additionalData;
	}

	public function getConsentProof(): string
	{
		return $this->consentData->getConsentProof();
	}

	public function isFirstConsent(): bool
	{
		return $this->type === self::TYPE_GIVEN;
	}

	public function isUpdate(): bool
	{
		return $this->type === self::TYPE_UPDATED;
	}

	public function isWithdrawn(): bool
	{
		return $this->type === self::TYPE_WITHDRAWN;
	}

	public function toArray(): array
	{
		return [
			'event_type' => $this->type,
			'consent_data' => $this->consentData->toArray(),
			'additional_data' => $this->additionalData,
		];
	}

	public function toLogArray(): array
	{
		return [
			'event_type' => $this->type,
			'consent_id' => $this->getConsentId(),
			'accepted_categories' => $this->getAcceptedCategories(),
			'rejected_categories' => $this->getRejectedCategories(),
			'timestamp' => $this->getTimestamp()->format('c'),
			'user_identifier' => $this->getUserIdentifier(),
			'ip_address' => $this->getIpAddress(),
			'ip_anonymized' => $this->getAnonymizedIpAddress(),
			'user_agent' => $this->getUserAgent(),
			'page_url' => $this->getPageUrl(),
			'referrer' => $this->getReferrer(),
			'consent_method' => $this->getConsentMethod(),
			'previous_consent' => $this->getPreviousConsent(),
			'consent_proof' => $this->getConsentProof(),
			'metadata' => $this->getMetadata(),
			'additional_data' => $this->additionalData,
		];
	}
}
