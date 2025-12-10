<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Consent;

use DateTimeImmutable;
use JsonSerializable;

class ConsentData implements JsonSerializable
{
	private string $consentId;
	private array $acceptedCategories;
	private array $rejectedCategories;
	private DateTimeImmutable $timestamp;
	private ?string $userIdentifier;
	private string $ipAddress;
	private string $userAgent;
	private string $pageUrl;
	private string $referrer;
	private string $consentMethod;
	private ?array $previousConsent;
	private array $metadata;

	public function __construct(
		array $acceptedCategories,
		array $rejectedCategories = [],
		?string $consentId = null,
		?string $userIdentifier = null,
		?DateTimeImmutable $timestamp = null,
		string $consentMethod = 'banner',
		?array $previousConsent = null,
		array $metadata = []
	) {
		$this->consentId = $consentId ?? $this->generateConsentId();
		$this->acceptedCategories = $acceptedCategories;
		$this->rejectedCategories = $rejectedCategories;
		$this->timestamp = $timestamp ?? new DateTimeImmutable();
		$this->userIdentifier = $userIdentifier;
		$this->ipAddress = $this->detectIpAddress();
		$this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
		$this->pageUrl = $this->detectPageUrl();
		$this->referrer = $_SERVER['HTTP_REFERER'] ?? '';
		$this->consentMethod = $consentMethod;
		$this->previousConsent = $previousConsent;
		$this->metadata = $metadata;
	}

	private function generateConsentId(): string
	{
		$data = [
			'time' => microtime(true),
			'random' => bin2hex(random_bytes(16)),
			'ip' => $this->detectIpAddress(),
			'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
		];

		return hash('sha256', json_encode($data));
	}

	private function detectIpAddress(): string
	{
		$headers = [
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		];

		foreach ($headers as $header) {
			if (!empty($_SERVER[$header])) {
				$ips = explode(',', $_SERVER[$header]);
				$ip = trim($ips[0]);
				if (filter_var($ip, FILTER_VALIDATE_IP)) {
					return $ip;
				}
			}
		}

		return '0.0.0.0';
	}

	private function detectPageUrl(): string
	{
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
		$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
		$uri = $_SERVER['REQUEST_URI'] ?? '/';

		return "{$protocol}://{$host}{$uri}";
	}

	public function getConsentId(): string
	{
		return $this->consentId;
	}

	public function setConsentId(string $consentId): self
	{
		$this->consentId = $consentId;
		return $this;
	}

	public function getAcceptedCategories(): array
	{
		return $this->acceptedCategories;
	}

	public function getRejectedCategories(): array
	{
		return $this->rejectedCategories;
	}

	public function getTimestamp(): DateTimeImmutable
	{
		return $this->timestamp;
	}

	public function getUserIdentifier(): ?string
	{
		return $this->userIdentifier;
	}

	public function setUserIdentifier(?string $identifier): self
	{
		$this->userIdentifier = $identifier;
		return $this;
	}

	public function getIpAddress(): string
	{
		return $this->ipAddress;
	}

	public function getAnonymizedIpAddress(): string
	{
		$ip = $this->ipAddress;

		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$parts = explode('.', $ip);
			$parts[3] = '0';
			return implode('.', $parts);
		}

		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			return substr($ip, 0, strrpos($ip, ':') + 1) . '0000';
		}

		return '0.0.0.0';
	}

	public function getUserAgent(): string
	{
		return $this->userAgent;
	}

	public function getPageUrl(): string
	{
		return $this->pageUrl;
	}

	public function getReferrer(): string
	{
		return $this->referrer;
	}

	public function getConsentMethod(): string
	{
		return $this->consentMethod;
	}

	public function getPreviousConsent(): ?array
	{
		return $this->previousConsent;
	}

	public function getMetadata(): array
	{
		return $this->metadata;
	}

	public function setMetadata(array $metadata): self
	{
		$this->metadata = $metadata;
		return $this;
	}

	public function addMetadata(string $key, mixed $value): self
	{
		$this->metadata[$key] = $value;
		return $this;
	}

	public function hasCategory(string $category): bool
	{
		return in_array($category, $this->acceptedCategories, true);
	}

	public function isAllAccepted(): bool
	{
		return empty($this->rejectedCategories);
	}

	public function isAllRejected(): bool
	{
		$nonRequired = array_filter($this->acceptedCategories, fn($cat) => $cat !== 'necessary');
		return empty($nonRequired);
	}

	public function getConsentProof(): string
	{
		$proofData = [
			'consent_id' => $this->consentId,
			'timestamp' => $this->timestamp->format('c'),
			'accepted' => $this->acceptedCategories,
			'rejected' => $this->rejectedCategories,
			'ip_hash' => hash('sha256', $this->ipAddress),
			'ua_hash' => hash('sha256', $this->userAgent),
		];

		return base64_encode(json_encode($proofData));
	}

	public function jsonSerialize(): array
	{
		return [
			'consent_id' => $this->consentId,
			'accepted_categories' => $this->acceptedCategories,
			'rejected_categories' => $this->rejectedCategories,
			'timestamp' => $this->timestamp->format('c'),
			'user_identifier' => $this->userIdentifier,
			'ip_address' => $this->ipAddress,
			'ip_anonymized' => $this->getAnonymizedIpAddress(),
			'user_agent' => $this->userAgent,
			'page_url' => $this->pageUrl,
			'referrer' => $this->referrer,
			'consent_method' => $this->consentMethod,
			'previous_consent' => $this->previousConsent,
			'metadata' => $this->metadata,
			'consent_proof' => $this->getConsentProof(),
		];
	}

	public function toArray(): array
	{
		return $this->jsonSerialize();
	}

	public static function fromArray(array $data): self
	{
		$consent = new self(
			$data['accepted_categories'] ?? [],
			$data['rejected_categories'] ?? [],
			$data['consent_id'] ?? null,
			$data['user_identifier'] ?? null,
			isset($data['timestamp']) ? new DateTimeImmutable($data['timestamp']) : null,
			$data['consent_method'] ?? 'banner',
			$data['previous_consent'] ?? null,
			$data['metadata'] ?? []
		);

		return $consent;
	}
}
