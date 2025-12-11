<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Storage;

use Chronex\CookieBanner\Consent\ConsentData;

/**
 * Session-based storage for consent data.
 *
 * Uses PHP sessions to store consent data. Cookie only contains
 * an opaque token that maps to the session data.
 */
class SessionStorage implements StorageInterface
{
	private const SESSION_KEY = 'chronex_cb_consent_storage';

	private string $secretKey;
	private int $tokenLength;

	public function __construct(string $secretKey = '', int $tokenLength = 32)
	{
		$this->secretKey = $secretKey ?: $this->generateDefaultSecret();
		$this->tokenLength = $tokenLength;
		$this->ensureSessionStarted();
	}

	private function ensureSessionStarted(): void
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
	}

	private function generateDefaultSecret(): string
	{
		// Use a combination of server-specific values
		return hash('sha256', __FILE__ . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
	}

	public function store(ConsentData $consent): string
	{
		$this->ensureSessionStarted();

		$token = $this->generateToken();
		$data = [
			'consent' => $consent->toArray(),
			'created_at' => time(),
			'token_hash' => $this->hashToken($token),
		];

		$_SESSION[self::SESSION_KEY][$token] = $data;

		return $token;
	}

	public function retrieve(string $token): ?ConsentData
	{
		$this->ensureSessionStarted();

		if (!$this->exists($token)) {
			return null;
		}

		$data = $_SESSION[self::SESSION_KEY][$token] ?? null;

		if ($data === null || !isset($data['consent'])) {
			return null;
		}

		// Verify token hash
		if (!hash_equals($data['token_hash'], $this->hashToken($token))) {
			return null;
		}

		return ConsentData::fromArray($data['consent']);
	}

	public function delete(string $token): bool
	{
		$this->ensureSessionStarted();

		if (!$this->exists($token)) {
			return false;
		}

		unset($_SESSION[self::SESSION_KEY][$token]);
		return true;
	}

	public function exists(string $token): bool
	{
		$this->ensureSessionStarted();

		return isset($_SESSION[self::SESSION_KEY][$token]);
	}

	public function update(string $token, ConsentData $consent): bool
	{
		$this->ensureSessionStarted();

		if (!$this->exists($token)) {
			return false;
		}

		$_SESSION[self::SESSION_KEY][$token]['consent'] = $consent->toArray();
		$_SESSION[self::SESSION_KEY][$token]['updated_at'] = time();

		return true;
	}

	public function generateToken(): string
	{
		// Generate cryptographically secure random bytes
		$randomBytes = random_bytes($this->tokenLength);

		// Create an HMAC to make the token opaque
		$hmac = hash_hmac('sha256', $randomBytes, $this->secretKey, true);

		// Combine and encode
		return bin2hex($randomBytes) . '.' . bin2hex($hmac);
	}

	private function hashToken(string $token): string
	{
		return hash_hmac('sha256', $token, $this->secretKey);
	}

	/**
	 * Clean up expired sessions (optional maintenance method)
	 */
	public function cleanup(int $maxAge = 31536000): int
	{
		$this->ensureSessionStarted();

		if (!isset($_SESSION[self::SESSION_KEY])) {
			return 0;
		}

		$now = time();
		$cleaned = 0;

		foreach ($_SESSION[self::SESSION_KEY] as $token => $data) {
			$createdAt = $data['created_at'] ?? 0;
			if (($now - $createdAt) > $maxAge) {
				unset($_SESSION[self::SESSION_KEY][$token]);
				$cleaned++;
			}
		}

		return $cleaned;
	}
}
