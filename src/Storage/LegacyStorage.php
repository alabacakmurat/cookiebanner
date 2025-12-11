<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Storage;

use Chronex\CookieBanner\Consent\ConsentData;

/**
 * Legacy storage - maintains backwards compatibility with old base64 cookie format.
 *
 * This storage uses the same format as the original implementation:
 * base64_encode(json_encode($consentData))
 *
 * Use this when you need to maintain compatibility with existing cookies
 * or when JavaScript handles storage without an API.
 */
class LegacyStorage implements StorageInterface
{
	public function store(ConsentData $consent): string
	{
		// Use the same format as original: base64(json)
		return base64_encode(json_encode($consent->toArray()));
	}

	public function retrieve(string $token): ?ConsentData
	{
		try {
			$decoded = base64_decode($token, true);

			if ($decoded === false) {
				return null;
			}

			$data = json_decode($decoded, true);

			if (!is_array($data)) {
				return null;
			}

			return ConsentData::fromArray($data);
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function delete(string $token): bool
	{
		// Nothing to delete - data is in cookie
		return true;
	}

	public function exists(string $token): bool
	{
		return $this->retrieve($token) !== null;
	}

	public function update(string $token, ConsentData $consent): bool
	{
		// In legacy storage, update means generating new token
		return true;
	}

	public function generateToken(): string
	{
		// Not used in legacy mode - token IS the data
		return '';
	}
}
