<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Storage;

use Chronex\CookieBanner\Consent\ConsentData;
use InvalidArgumentException;

/**
 * Callback-based storage for consent data.
 *
 * Allows users to provide custom getter/setter callbacks
 * for storing consent data in their own backend (database, Redis, etc.)
 */
class CallbackStorage implements StorageInterface
{
	/** @var callable(ConsentData): string */
	private $storeCallback;

	/** @var callable(string): ?array */
	private $retrieveCallback;

	/** @var callable(string): bool */
	private $deleteCallback;

	/** @var callable(string): bool */
	private $existsCallback;

	/** @var callable(string, ConsentData): bool */
	private $updateCallback;

	private string $secretKey;
	private int $tokenLength;

	/**
	 * @param callable(ConsentData): string $storeCallback Stores consent, returns token
	 * @param callable(string): ?array $retrieveCallback Retrieves consent array by token
	 * @param callable(string): bool $deleteCallback Deletes consent by token
	 * @param callable(string): bool|null $existsCallback Checks if token exists (optional)
	 * @param callable(string, ConsentData): bool|null $updateCallback Updates consent (optional)
	 * @param string $secretKey Secret key for token generation
	 * @param int $tokenLength Token length in bytes
	 */
	public function __construct(
		callable $storeCallback,
		callable $retrieveCallback,
		callable $deleteCallback,
		?callable $existsCallback = null,
		?callable $updateCallback = null,
		string $secretKey = '',
		int $tokenLength = 32
	) {
		$this->storeCallback = $storeCallback;
		$this->retrieveCallback = $retrieveCallback;
		$this->deleteCallback = $deleteCallback;
		$this->existsCallback = $existsCallback ?? fn(string $token) => $this->retrieve($token) !== null;
		$this->updateCallback = $updateCallback ?? function (string $token, ConsentData $consent): bool {
			if (!$this->exists($token)) {
				return false;
			}
			($this->deleteCallback)($token);
			($this->storeCallback)($consent);
			return true;
		};
		$this->secretKey = $secretKey ?: $this->generateDefaultSecret();
		$this->tokenLength = $tokenLength;
	}

	private function generateDefaultSecret(): string
	{
		return hash('sha256', __FILE__ . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
	}

	public function store(ConsentData $consent): string
	{
		$token = $this->generateToken();

		// Call user's store callback, passing the token and consent
		$result = ($this->storeCallback)($consent, $token);

		// If callback returns a custom token, use that
		if (is_string($result) && $result !== '') {
			return $result;
		}

		return $token;
	}

	public function retrieve(string $token): ?ConsentData
	{
		$data = ($this->retrieveCallback)($token);

		if ($data === null || !is_array($data)) {
			return null;
		}

		return ConsentData::fromArray($data);
	}

	public function delete(string $token): bool
	{
		return ($this->deleteCallback)($token);
	}

	public function exists(string $token): bool
	{
		return ($this->existsCallback)($token);
	}

	public function update(string $token, ConsentData $consent): bool
	{
		return ($this->updateCallback)($token, $consent);
	}

	public function generateToken(): string
	{
		$randomBytes = random_bytes($this->tokenLength);
		$hmac = hash_hmac('sha256', $randomBytes, $this->secretKey, true);

		return bin2hex($randomBytes) . '.' . bin2hex($hmac);
	}

	/**
	 * Create a CallbackStorage from an array of callbacks.
	 *
	 * @param array{
	 *     store: callable(ConsentData, string): ?string,
	 *     retrieve: callable(string): ?array,
	 *     delete: callable(string): bool,
	 *     exists?: callable(string): bool,
	 *     update?: callable(string, ConsentData): bool
	 * } $callbacks
	 * @param string $secretKey
	 * @return self
	 */
	public static function fromArray(array $callbacks, string $secretKey = ''): self
	{
		if (!isset($callbacks['store'], $callbacks['retrieve'], $callbacks['delete'])) {
			throw new InvalidArgumentException(
				'Callbacks array must contain: store, retrieve, delete'
			);
		}

		return new self(
			$callbacks['store'],
			$callbacks['retrieve'],
			$callbacks['delete'],
			$callbacks['exists'] ?? null,
			$callbacks['update'] ?? null,
			$secretKey
		);
	}
}
