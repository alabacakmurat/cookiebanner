<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Consent;

use Chronex\CookieBanner\Config\Configuration;
use Chronex\CookieBanner\Event\EventDispatcher;
use Chronex\CookieBanner\Event\ConsentEvent;
use Chronex\CookieBanner\Storage\StorageInterface;
use Chronex\CookieBanner\Storage\LegacyStorage;

class ConsentManager
{
	private Configuration $config;
	private EventDispatcher $eventDispatcher;
	private ?ConsentData $currentConsent = null;
	private ?string $userIdentifier = null;
	private StorageInterface $storage;
	private ?string $currentToken = null;

	public function __construct(Configuration $config, EventDispatcher $eventDispatcher, ?StorageInterface $storage = null)
	{
		$this->config = $config;
		$this->eventDispatcher = $eventDispatcher;
		$this->storage = $storage ?? new LegacyStorage();
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
		$this->currentConsent = null;
		$this->currentToken = null;

		if (isset($_COOKIE[$cookieName])) {
			try {
				$token = $_COOKIE[$cookieName];
				$this->currentToken = $token;

				// Retrieve consent data from storage using the token
				$this->currentConsent = $this->storage->retrieve($token);
			} catch (\Throwable $e) {
				$this->currentConsent = null;
				$this->currentToken = null;
			}
		}
	}

	public function hasConsent(): bool
	{
		return $this->currentConsent !== null;
	}

	/**
	 * Get debug information about consent loading state.
	 * Useful for troubleshooting why consent is not being detected.
	 *
	 * @return array
	 */
	public function getDebugInfo(): array
	{
		$cookieName = $this->config->getCookieName();

		return [
			'cookie_name' => $cookieName,
			'cookie_exists' => isset($_COOKIE[$cookieName]),
			'cookie_value' => isset($_COOKIE[$cookieName]) ? substr($_COOKIE[$cookieName], 0, 20) . '...' : null,
			'current_token' => $this->currentToken ? substr($this->currentToken, 0, 20) . '...' : null,
			'has_consent' => $this->hasConsent(),
			'storage_class' => get_class($this->storage),
			'consent_id' => $this->currentConsent?->getConsentId(),
		];
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

		// Store consent and update token
		if ($isUpdate && $this->currentToken !== null) {
			// Update existing storage
			$this->storage->update($this->currentToken, $consentData);
		} else {
			// Store new consent
			$this->currentToken = $this->storage->store($consentData);
		}

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

		// Delete from storage
		if ($this->currentToken !== null) {
			$this->storage->delete($this->currentToken);
			$this->currentToken = null;
		}

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

	/**
	 * Generate the cookie value (opaque token) for the current consent.
	 *
	 * @return string The storage token to be stored in the cookie
	 */
	public function generateConsentCookieValue(): string
	{
		if ($this->currentConsent === null) {
			return '';
		}

		// If we have an existing token and just updated the consent, return that token
		if ($this->currentToken !== null) {
			return $this->currentToken;
		}

		// Store consent and get a new token
		$this->currentToken = $this->storage->store($this->currentConsent);

		return $this->currentToken;
	}

	/**
	 * Store the current consent and return the storage token.
	 *
	 * @return string|null The storage token or null if no consent
	 */
	public function storeConsent(): ?string
	{
		if ($this->currentConsent === null) {
			return null;
		}

		// If updating existing consent
		if ($this->currentToken !== null && $this->storage->exists($this->currentToken)) {
			$this->storage->update($this->currentToken, $this->currentConsent);
			return $this->currentToken;
		}

		// Store new consent
		$this->currentToken = $this->storage->store($this->currentConsent);
		return $this->currentToken;
	}

	/**
	 * Get the current storage token.
	 *
	 * @return string|null
	 */
	public function getCurrentToken(): ?string
	{
		return $this->currentToken;
	}

	/**
	 * Delete the stored consent.
	 *
	 * @return bool
	 */
	public function deleteStoredConsent(): bool
	{
		if ($this->currentToken === null) {
			return false;
		}

		$result = $this->storage->delete($this->currentToken);
		$this->currentToken = null;

		return $result;
	}

	/**
	 * Get the storage backend.
	 *
	 * @return StorageInterface
	 */
	public function getStorage(): StorageInterface
	{
		return $this->storage;
	}

	/**
	 * Set the storage backend.
	 *
	 * @param StorageInterface $storage
	 * @return self
	 */
	public function setStorage(StorageInterface $storage): self
	{
		$this->storage = $storage;
		// Reload consent from new storage
		$this->loadCurrentConsent();
		return $this;
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
