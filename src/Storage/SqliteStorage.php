<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Storage;

use Chronex\CookieBanner\Consent\ConsentData;
use PDO;
use PDOException;

/**
 * SQLite-based storage for consent data.
 *
 * Stores consent data in a SQLite database file. Cookie only contains
 * an opaque token that maps to the database record.
 */
class SqliteStorage implements StorageInterface
{
	private PDO $pdo;
	private string $tableName;
	private string $secretKey;
	private int $tokenLength;

	/**
	 * @param string $databasePath Path to SQLite database file
	 * @param string $tableName Table name for consent storage
	 * @param string $secretKey Secret key for token generation
	 * @param int $tokenLength Length of random bytes for token
	 */
	public function __construct(
		string $databasePath,
		string $tableName = 'cookie_consents',
		string $secretKey = '',
		int $tokenLength = 32
	) {
		$this->tableName = $tableName;
		$this->secretKey = $secretKey ?: $this->generateDefaultSecret();
		$this->tokenLength = $tokenLength;

		$this->initializeDatabase($databasePath);
	}

	private function generateDefaultSecret(): string
	{
		return hash('sha256', __FILE__ . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
	}

	private function initializeDatabase(string $databasePath): void
	{
		$isNewDatabase = !file_exists($databasePath);

		$this->pdo = new PDO(
			'sqlite:' . $databasePath,
			null,
			null,
			[
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES => false,
			]
		);

		// Enable WAL mode for better concurrency
		$this->pdo->exec('PRAGMA journal_mode=WAL');

		if ($isNewDatabase) {
			$this->createTable();
		}
	}

	private function createTable(): void
	{
		$sql = "CREATE TABLE IF NOT EXISTS {$this->tableName} (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			token TEXT UNIQUE NOT NULL,
			token_hash TEXT NOT NULL,
			consent_id TEXT NOT NULL,
			user_identifier TEXT,
			accepted_categories TEXT NOT NULL,
			rejected_categories TEXT NOT NULL,
			consent_method TEXT,
			ip_address TEXT,
			user_agent TEXT,
			metadata TEXT,
			previous_consent TEXT,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
		)";

		$this->pdo->exec($sql);

		// Create indexes for faster lookups
		$this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_{$this->tableName}_token ON {$this->tableName}(token)");
		$this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_{$this->tableName}_consent_id ON {$this->tableName}(consent_id)");
		$this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_{$this->tableName}_user_identifier ON {$this->tableName}(user_identifier)");
	}

	public function store(ConsentData $consent): string
	{
		$token = $this->generateToken();
		$tokenHash = $this->hashToken($token);
		$data = $consent->toArray();

		$sql = "INSERT INTO {$this->tableName} (
			token, token_hash, consent_id, user_identifier,
			accepted_categories, rejected_categories, consent_method,
			ip_address, user_agent, metadata, previous_consent
		) VALUES (
			:token, :token_hash, :consent_id, :user_identifier,
			:accepted_categories, :rejected_categories, :consent_method,
			:ip_address, :user_agent, :metadata, :previous_consent
		)";

		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			'token' => $token,
			'token_hash' => $tokenHash,
			'consent_id' => $data['consent_id'],
			'user_identifier' => $data['user_identifier'] ?? null,
			'accepted_categories' => json_encode($data['accepted_categories']),
			'rejected_categories' => json_encode($data['rejected_categories']),
			'consent_method' => $data['consent_method'] ?? null,
			'ip_address' => $data['ip_address'] ?? null,
			'user_agent' => $data['user_agent'] ?? null,
			'metadata' => json_encode($data['metadata'] ?? []),
			'previous_consent' => $data['previous_consent'] ? json_encode($data['previous_consent']) : null,
		]);

		return $token;
	}

	public function retrieve(string $token): ?ConsentData
	{
		$sql = "SELECT * FROM {$this->tableName} WHERE token = :token LIMIT 1";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(['token' => $token]);
		$row = $stmt->fetch();

		if (!$row) {
			return null;
		}

		// Verify token hash
		if (!hash_equals($row['token_hash'], $this->hashToken($token))) {
			return null;
		}

		return $this->rowToConsentData($row);
	}

	public function delete(string $token): bool
	{
		$sql = "DELETE FROM {$this->tableName} WHERE token = :token";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(['token' => $token]);

		return $stmt->rowCount() > 0;
	}

	public function exists(string $token): bool
	{
		$sql = "SELECT 1 FROM {$this->tableName} WHERE token = :token LIMIT 1";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(['token' => $token]);

		return $stmt->fetch() !== false;
	}

	public function update(string $token, ConsentData $consent): bool
	{
		$data = $consent->toArray();

		$sql = "UPDATE {$this->tableName} SET
			consent_id = :consent_id,
			user_identifier = :user_identifier,
			accepted_categories = :accepted_categories,
			rejected_categories = :rejected_categories,
			consent_method = :consent_method,
			ip_address = :ip_address,
			user_agent = :user_agent,
			metadata = :metadata,
			previous_consent = :previous_consent,
			updated_at = CURRENT_TIMESTAMP
		WHERE token = :token";

		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			'token' => $token,
			'consent_id' => $data['consent_id'],
			'user_identifier' => $data['user_identifier'] ?? null,
			'accepted_categories' => json_encode($data['accepted_categories']),
			'rejected_categories' => json_encode($data['rejected_categories']),
			'consent_method' => $data['consent_method'] ?? null,
			'ip_address' => $data['ip_address'] ?? null,
			'user_agent' => $data['user_agent'] ?? null,
			'metadata' => json_encode($data['metadata'] ?? []),
			'previous_consent' => $data['previous_consent'] ? json_encode($data['previous_consent']) : null,
		]);

		return $stmt->rowCount() > 0;
	}

	public function generateToken(): string
	{
		$randomBytes = random_bytes($this->tokenLength);
		$hmac = hash_hmac('sha256', $randomBytes, $this->secretKey, true);

		return bin2hex($randomBytes) . '.' . bin2hex($hmac);
	}

	private function hashToken(string $token): string
	{
		return hash_hmac('sha256', $token, $this->secretKey);
	}

	private function rowToConsentData(array $row): ConsentData
	{
		$acceptedCategories = json_decode($row['accepted_categories'], true) ?? [];
		$rejectedCategories = json_decode($row['rejected_categories'], true) ?? [];
		$metadata = json_decode($row['metadata'] ?? '[]', true) ?? [];
		$previousConsent = $row['previous_consent'] ? json_decode($row['previous_consent'], true) : null;

		// Parse timestamp from database
		$timestamp = null;
		if (!empty($row['created_at'])) {
			$timestamp = new \DateTimeImmutable($row['created_at']);
		}

		return new ConsentData(
			$acceptedCategories,
			$rejectedCategories,
			$row['consent_id'],
			$row['user_identifier'],
			$timestamp,
			$row['consent_method'] ?? 'banner',
			$previousConsent,
			$metadata
		);
	}

	/**
	 * Get the PDO instance for advanced queries.
	 */
	public function getPdo(): PDO
	{
		return $this->pdo;
	}

	/**
	 * Get the table name.
	 */
	public function getTableName(): string
	{
		return $this->tableName;
	}

	/**
	 * Find consent by user identifier.
	 *
	 * @param string $userIdentifier
	 * @return ConsentData|null
	 */
	public function findByUserIdentifier(string $userIdentifier): ?ConsentData
	{
		$sql = "SELECT * FROM {$this->tableName} WHERE user_identifier = :user_identifier ORDER BY created_at DESC LIMIT 1";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(['user_identifier' => $userIdentifier]);
		$row = $stmt->fetch();

		if (!$row) {
			return null;
		}

		return $this->rowToConsentData($row);
	}

	/**
	 * Find consent by consent ID.
	 *
	 * @param string $consentId
	 * @return ConsentData|null
	 */
	public function findByConsentId(string $consentId): ?ConsentData
	{
		$sql = "SELECT * FROM {$this->tableName} WHERE consent_id = :consent_id ORDER BY created_at DESC LIMIT 1";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(['consent_id' => $consentId]);
		$row = $stmt->fetch();

		if (!$row) {
			return null;
		}

		return $this->rowToConsentData($row);
	}

	/**
	 * Get all consent records with pagination.
	 *
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function getAll(int $limit = 100, int $offset = 0): array
	{
		$sql = "SELECT * FROM {$this->tableName} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
		$stmt = $this->pdo->prepare($sql);
		$stmt->bindValue('limit', $limit, PDO::PARAM_INT);
		$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
		$stmt->execute();

		$results = [];
		while ($row = $stmt->fetch()) {
			$results[] = $this->rowToConsentData($row);
		}

		return $results;
	}

	/**
	 * Count total consent records.
	 *
	 * @return int
	 */
	public function count(): int
	{
		$sql = "SELECT COUNT(*) as total FROM {$this->tableName}";
		$stmt = $this->pdo->query($sql);
		$row = $stmt->fetch();

		return (int) ($row['total'] ?? 0);
	}

	/**
	 * Get consent statistics.
	 *
	 * @return array
	 */
	public function getStatistics(): array
	{
		$stats = [
			'total' => $this->count(),
			'by_method' => [],
			'by_date' => [],
		];

		// By consent method
		$sql = "SELECT consent_method, COUNT(*) as count FROM {$this->tableName} GROUP BY consent_method";
		$stmt = $this->pdo->query($sql);
		while ($row = $stmt->fetch()) {
			$stats['by_method'][$row['consent_method'] ?? 'unknown'] = (int) $row['count'];
		}

		// By date (last 30 days)
		$sql = "SELECT DATE(created_at) as date, COUNT(*) as count FROM {$this->tableName}
				WHERE created_at >= DATE('now', '-30 days')
				GROUP BY DATE(created_at) ORDER BY date";
		$stmt = $this->pdo->query($sql);
		while ($row = $stmt->fetch()) {
			$stats['by_date'][$row['date']] = (int) $row['count'];
		}

		return $stats;
	}

	/**
	 * Clean up old records.
	 *
	 * @param int $daysOld Delete records older than this many days
	 * @return int Number of deleted records
	 */
	public function cleanup(int $daysOld = 365): int
	{
		$sql = "DELETE FROM {$this->tableName} WHERE created_at < DATE('now', :days)";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(['days' => "-{$daysOld} days"]);

		return $stmt->rowCount();
	}

	/**
	 * Export all records to array.
	 *
	 * @return array
	 */
	public function exportAll(): array
	{
		$sql = "SELECT * FROM {$this->tableName} ORDER BY created_at";
		$stmt = $this->pdo->query($sql);

		$results = [];
		while ($row = $stmt->fetch()) {
			$row['accepted_categories'] = json_decode($row['accepted_categories'], true);
			$row['rejected_categories'] = json_decode($row['rejected_categories'], true);
			$row['metadata'] = json_decode($row['metadata'] ?? '[]', true);
			$row['previous_consent'] = $row['previous_consent'] ? json_decode($row['previous_consent'], true) : null;
			unset($row['token'], $row['token_hash']); // Remove sensitive data
			$results[] = $row;
		}

		return $results;
	}
}
