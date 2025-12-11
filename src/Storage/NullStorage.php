<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Storage;

use Chronex\CookieBanner\Consent\ConsentData;

/**
 * Null storage - falls back to cookie-based storage (legacy mode).
 *
 * This is used when no storage backend is configured.
 * In this mode, consent data is still stored in the cookie but encrypted.
 */
class NullStorage implements StorageInterface
{
	private string $encryptionKey;

	public function __construct(string $encryptionKey = '')
	{
		$this->encryptionKey = $encryptionKey ?: $this->generateDefaultKey();
	}

	private function generateDefaultKey(): string
	{
		return hash('sha256', __FILE__ . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
	}

	public function store(ConsentData $consent): string
	{
		// Encrypt the consent data for cookie storage
		return $this->encrypt(json_encode($consent->toArray()));
	}

	public function retrieve(string $token): ?ConsentData
	{
		$decrypted = $this->decrypt($token);

		if ($decrypted === null) {
			return null;
		}

		$data = json_decode($decrypted, true);

		if (!is_array($data)) {
			return null;
		}

		return ConsentData::fromArray($data);
	}

	public function delete(string $token): bool
	{
		// Nothing to delete in null storage
		return true;
	}

	public function exists(string $token): bool
	{
		return $this->retrieve($token) !== null;
	}

	public function update(string $token, ConsentData $consent): bool
	{
		// In null storage, update just creates a new token
		return true;
	}

	public function generateToken(): string
	{
		return bin2hex(random_bytes(32));
	}

	/**
	 * Encrypt data using AES-256-GCM.
	 */
	private function encrypt(string $data): string
	{
		$key = hash('sha256', $this->encryptionKey, true);
		$iv = random_bytes(12); // GCM recommended IV size
		$tag = '';

		$encrypted = openssl_encrypt(
			$data,
			'aes-256-gcm',
			$key,
			OPENSSL_RAW_DATA,
			$iv,
			$tag
		);

		if ($encrypted === false) {
			throw new \RuntimeException('Encryption failed');
		}

		// Combine IV + tag + encrypted data and encode
		return base64_encode($iv . $tag . $encrypted);
	}

	/**
	 * Decrypt data using AES-256-GCM.
	 */
	private function decrypt(string $token): ?string
	{
		$data = base64_decode($token, true);

		if ($data === false || strlen($data) < 28) {
			return null;
		}

		$key = hash('sha256', $this->encryptionKey, true);
		$iv = substr($data, 0, 12);
		$tag = substr($data, 12, 16);
		$encrypted = substr($data, 28);

		$decrypted = openssl_decrypt(
			$encrypted,
			'aes-256-gcm',
			$key,
			OPENSSL_RAW_DATA,
			$iv,
			$tag
		);

		return $decrypted === false ? null : $decrypted;
	}
}
