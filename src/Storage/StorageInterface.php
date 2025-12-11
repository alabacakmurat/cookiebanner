<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Storage;

use Chronex\CookieBanner\Consent\ConsentData;

/**
 * Interface for consent data storage backends.
 *
 * Implementations can use sessions, databases, cache systems, etc.
 * Cookie will only store an opaque token/ID, not the actual consent data.
 */
interface StorageInterface
{
	/**
	 * Store consent data and return a storage token.
	 *
	 * The token should be opaque and not reveal any information
	 * about the stored data when decoded.
	 *
	 * @param ConsentData $consent The consent data to store
	 * @return string An opaque token to identify this consent
	 */
	public function store(ConsentData $consent): string;

	/**
	 * Retrieve consent data by token.
	 *
	 * @param string $token The storage token
	 * @return ConsentData|null The consent data or null if not found/expired
	 */
	public function retrieve(string $token): ?ConsentData;

	/**
	 * Delete consent data by token.
	 *
	 * @param string $token The storage token
	 * @return bool True if deleted, false if not found
	 */
	public function delete(string $token): bool;

	/**
	 * Check if a token exists and is valid.
	 *
	 * @param string $token The storage token
	 * @return bool True if exists and valid
	 */
	public function exists(string $token): bool;

	/**
	 * Update existing consent data.
	 *
	 * @param string $token The storage token
	 * @param ConsentData $consent The new consent data
	 * @return bool True if updated, false if token not found
	 */
	public function update(string $token, ConsentData $consent): bool;

	/**
	 * Generate a new opaque token.
	 *
	 * @return string A cryptographically secure, opaque token
	 */
	public function generateToken(): string;
}
