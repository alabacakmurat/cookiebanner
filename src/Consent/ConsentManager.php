<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Consent;

use Chronex\CookieBanner\Config\Configuration;
use Chronex\CookieBanner\Event\EventDispatcher;
use Chronex\CookieBanner\Event\ConsentEvent;

class ConsentManager
{
	private Configuration $config;
	private EventDispatcher $eventDispatcher;
	private ?ConsentData $currentConsent = null;
	private ?string $userIdentifier = null;

	public function __construct(Configuration $config, EventDispatcher $eventDispatcher)
	{
		$this->config = $config;
		$this->eventDispatcher = $eventDispatcher;
		$this->loadCurrentConsent();
	}

	/**
	 * Set the user identifier (e.g., user ID, email hash) to associate with consent
	 */
	public function setUserIdentifier(?string $identifier): self
	{
		$this->userIdentifier = $identifier;

		// Update current consent if exists
		if ($this->currentConsent !== null) {
			$this->currentConsent->setUserIdentifier($identifier);
		}

		return $this;
	}

	/**
	 * Get the current user identifier
	 */
	public function getUserIdentifier(): ?string
	{
		return $this->userIdentifier;
	}

	private function loadCurrentConsent(): void
	{
		$cookieName = $this->config->getCookieName();

		if (isset($_COOKIE[$cookieName])) {
			try {
				$data = json_decode(base64_decode($_COOKIE[$cookieName]), true);
				if ($data && is_array($data)) {
					$this->currentConsent = ConsentData::fromArray($data);
				}
			} catch (\Throwable $e) {
				$this->currentConsent = null;
			}
		}
	}

	public function hasConsent(): bool
	{
		return $this->currentConsent !== null;
	}

	public function getCurrentConsent(): ?ConsentData
	{
		return $this->currentConsent;
	}

	public function hasConsentFor(string $category): bool
	{
		if (!$this->hasConsent()) {
			$categoryConfig = $this->config->getCategory($category);
			return $categoryConfig['required'] ?? false;
		}

		return $this->currentConsent->hasCategory($category);
	}

	public function giveConsent(array $acceptedCategories, string $method = 'banner', array $metadata = [], ?array $jsPreviousConsent = null): ConsentData
	{
		$allCategories = array_keys($this->config->getCategories());

		// Use JavaScript's previous consent if provided, otherwise fall back to PHP's current consent
		// This ensures consistency because JS and PHP generate different consent IDs
		$previousConsent = $jsPreviousConsent ?? $this->currentConsent?->toArray();

		// Check if this is an update based on metadata (from JavaScript)
		$isUpdate = $metadata['is_update'] ?? ($previousConsent !== null);

		// Always include required categories
		foreach ($this->config->getCategories() as $key => $category) {
			if ($category['required'] && !in_array($key, $acceptedCategories)) {
				$acceptedCategories[] = $key;
			}
		}

		$acceptedCategories = array_unique($acceptedCategories);
		$rejectedCategories = array_values(array_diff($allCategories, $acceptedCategories));

		$consentData = new ConsentData(
			$acceptedCategories,
			$rejectedCategories,
			null,
			$this->userIdentifier,
			null,
			$method,
			$isUpdate ? $previousConsent : null,
			$metadata
		);

		$this->currentConsent = $consentData;

		// Dispatch event
		$event = new ConsentEvent(
			$isUpdate ? ConsentEvent::TYPE_UPDATED : ConsentEvent::TYPE_GIVEN,
			$consentData
		);
		$this->eventDispatcher->dispatch($event);

		return $consentData;
	}

	public function acceptAll(string $method = 'banner', array $metadata = []): ConsentData
	{
		$allCategories = array_keys($this->config->getCategories());
		return $this->giveConsent($allCategories, $method, $metadata);
	}

	public function rejectAll(string $method = 'banner', array $metadata = []): ConsentData
	{
		$requiredCategories = [];

		foreach ($this->config->getCategories() as $key => $category) {
			if ($category['required']) {
				$requiredCategories[] = $key;
			}
		}

		return $this->giveConsent($requiredCategories, $method, $metadata);
	}

	public function withdrawConsent(array $metadata = [], ?array $previousConsentData = null): void
	{
		// Use currentConsent if available, otherwise try to reconstruct from provided data
		$consentToWithdraw = $this->currentConsent;

		if ($consentToWithdraw === null && $previousConsentData !== null) {
			// Reconstruct consent from JavaScript-provided data
			$consentToWithdraw = ConsentData::fromArray($previousConsentData);
		}

		if ($consentToWithdraw === null) {
			return;
		}

		$event = new ConsentEvent(
			ConsentEvent::TYPE_WITHDRAWN,
			$consentToWithdraw,
			$metadata
		);

		$this->currentConsent = null;
		$this->eventDispatcher->dispatch($event);
	}

	public function getAcceptedCategories(): array
	{
		if (!$this->hasConsent()) {
			$required = [];
			foreach ($this->config->getCategories() as $key => $category) {
				if ($category['required']) {
					$required[] = $key;
				}
			}
			return $required;
		}

		return $this->currentConsent->getAcceptedCategories();
	}

	public function getRejectedCategories(): array
	{
		if (!$this->hasConsent()) {
			$optional = [];
			foreach ($this->config->getCategories() as $key => $category) {
				if (!$category['required']) {
					$optional[] = $key;
				}
			}
			return $optional;
		}

		return $this->currentConsent->getRejectedCategories();
	}

	public function generateConsentCookieValue(): string
	{
		if ($this->currentConsent === null) {
			return '';
		}

		return base64_encode(json_encode($this->currentConsent->toArray()));
	}

	public function getCookieSettings(): array
	{
		return [
			'name' => $this->config->getCookieName(),
			'expiry' => $this->config->getCookieExpiry(),
			'path' => $this->config->getCookiePath(),
			'domain' => $this->config->getCookieDomain(),
			'secure' => $this->config->isCookieSecure(),
			'samesite' => $this->config->isCookieSameSite() ? 'Lax' : 'None',
		];
	}

	public function shouldShowBanner(): bool
	{
		if ($this->config->isShowOnlyOnce() && $this->hasConsent()) {
			return false;
		}

		if ($this->config->isRespectDoNotTrack()) {
			$dnt = $_SERVER['HTTP_DNT'] ?? null;
			if ($dnt === '1') {
				return false;
			}
		}

		return !$this->hasConsent();
	}

	public function getConsentForJavaScript(): array
	{
		return [
			'hasConsent' => $this->hasConsent(),
			'accepted' => $this->getAcceptedCategories(),
			'rejected' => $this->getRejectedCategories(),
			'consentId' => $this->currentConsent?->getConsentId(),
			'timestamp' => $this->currentConsent?->getTimestamp()->format('c'),
		];
	}
}
