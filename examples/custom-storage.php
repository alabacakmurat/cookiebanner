<?php

/**
 * Custom Storage Example
 *
 * This example demonstrates how to use custom storage backends
 * for consent data instead of storing everything in cookies.
 *
 * Benefits:
 * - Cookie only contains an opaque token (not decodable)
 * - Consent data stored server-side (session, database, Redis, etc.)
 * - Better security and privacy
 * - Easier consent management for logged-in users
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Chronex\CookieBanner\CookieBanner;
use Chronex\CookieBanner\Storage\SessionStorage;
use Chronex\CookieBanner\Storage\CallbackStorage;
use Chronex\CookieBanner\Consent\ConsentData;

// ============================================================
// EXAMPLE 1: Session-based Storage
// ============================================================
// Consent data is stored in PHP session, cookie only has a token

$bannerWithSession = new CookieBanner([
	'storageType' => 'session',
	'storageEncryptionKey' => 'your-secret-key-here',
	'apiUrl' => '?api=consent',
]);

// Or set it after initialization:
// $banner->useSessionStorage('your-secret-key');


// ============================================================
// EXAMPLE 2: Database Storage with Callbacks
// ============================================================
// Store consent in your database

// Simulated database connection (use your actual PDO/ORM)
$db = new class {
	private array $consents = [];

	public function insert(string $table, array $data): bool
	{
		$this->consents[$data['token']] = $data;
		return true;
	}

	public function find(string $table, string $token): ?array
	{
		return $this->consents[$token] ?? null;
	}

	public function delete(string $table, string $token): bool
	{
		unset($this->consents[$token]);
		return true;
	}

	public function exists(string $table, string $token): bool
	{
		return isset($this->consents[$token]);
	}
};

$bannerWithDatabase = new CookieBanner([
	'storageEncryptionKey' => 'your-secret-key-here',
	'apiUrl' => '?api=consent',
	'storageCallbacks' => [
		// Store consent - receives ConsentData and token
		'store' => function (ConsentData $consent, string $token) use ($db): string {
			$db->insert('consent_records', [
				'token' => $token,
				'consent_id' => $consent->getConsentId(),
				'user_identifier' => $consent->getUserIdentifier(),
				'accepted_categories' => json_encode($consent->getAcceptedCategories()),
				'rejected_categories' => json_encode($consent->getRejectedCategories()),
				'timestamp' => $consent->getTimestamp()->format('Y-m-d H:i:s'),
				'ip_address' => $consent->getAnonymizedIpAddress(),
				'user_agent' => $consent->getUserAgent(),
				'consent_method' => $consent->getConsentMethod(),
				'metadata' => json_encode($consent->getMetadata()),
				'created_at' => date('Y-m-d H:i:s'),
			]);
			return $token; // Return the token to be stored in cookie
		},

		// Retrieve consent - receives token, returns array or null
		'retrieve' => function (string $token) use ($db): ?array {
			$row = $db->find('consent_records', $token);
			if (!$row) {
				return null;
			}

			return [
				'consent_id' => $row['consent_id'],
				'accepted_categories' => json_decode($row['accepted_categories'], true),
				'rejected_categories' => json_decode($row['rejected_categories'], true),
				'timestamp' => $row['timestamp'],
				'user_identifier' => $row['user_identifier'],
				'consent_method' => $row['consent_method'],
				'metadata' => json_decode($row['metadata'], true) ?? [],
			];
		},

		// Delete consent - receives token, returns bool
		'delete' => function (string $token) use ($db): bool {
			return $db->delete('consent_records', $token);
		},

		// Optional: Check if token exists
		'exists' => function (string $token) use ($db): bool {
			return $db->exists('consent_records', $token);
		},
	],
]);


// ============================================================
// EXAMPLE 3: Using setStorageCallbacks() method
// ============================================================

$bannerWithCallbacks = new CookieBanner([
	'apiUrl' => '?api=consent',
]);

$bannerWithCallbacks->setStorageCallbacks(
	// Store
	function (ConsentData $consent, string $token) use ($db): string {
		$db->insert('consent_records', [
			'token' => $token,
			'data' => json_encode($consent->toArray()),
		]);
		return $token;
	},
	// Retrieve
	function (string $token) use ($db): ?array {
		$row = $db->find('consent_records', $token);
		return $row ? json_decode($row['data'], true) : null;
	},
	// Delete
	function (string $token) use ($db): bool {
		return $db->delete('consent_records', $token);
	},
	// Secret key for token generation
	'your-secret-key-here'
);


// ============================================================
// EXAMPLE 4: Encrypted Cookie Storage (Default)
// ============================================================
// Consent data is encrypted and stored in cookie
// Cookie value is not decodable without the encryption key

$bannerEncrypted = new CookieBanner([
	'storageType' => 'encrypted',
	'storageEncryptionKey' => 'your-32-character-encryption-key!',
	'apiUrl' => '?api=consent',
]);


// ============================================================
// DEMO: Choose which example to use
// ============================================================

// Use session storage for this demo
$banner = $bannerWithSession;

// Handle API requests
if (isset($_GET['api']) && $_GET['api'] === 'consent') {
	header('Content-Type: application/json');
	$input = json_decode(file_get_contents('php://input'), true) ?? [];
	echo json_encode($banner->handleApiRequest($input));
	exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Custom Storage Example - GDPR Cookie Banner</title>
	<?php echo $banner->renderCss(); ?>
</head>
<body>
	<div style="max-width: 800px; margin: 50px auto; padding: 20px; font-family: sans-serif;">
		<h1>Custom Storage Example</h1>

		<div style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;">
			<h2>How it works</h2>
			<p>In this example, consent data is stored server-side instead of in the cookie.</p>
			<ul>
				<li><strong>Cookie contains:</strong> An opaque, non-decodable token</li>
				<li><strong>Server stores:</strong> Full consent data (categories, timestamp, etc.)</li>
				<li><strong>Benefits:</strong> Better security, easier management, GDPR compliance</li>
			</ul>
		</div>

		<div style="background: #e8f4e8; padding: 20px; border-radius: 8px; margin: 20px 0;">
			<h3>Current Consent Status</h3>
			<?php
			$consent = $banner->getConsent();
			if ($consent) {
				echo "<p><strong>Has Consent:</strong> Yes</p>";
				echo "<p><strong>Consent ID:</strong> " . htmlspecialchars($consent->getConsentId()) . "</p>";
				echo "<p><strong>Accepted:</strong> " . implode(', ', $consent->getAcceptedCategories()) . "</p>";
				echo "<p><strong>Rejected:</strong> " . implode(', ', $consent->getRejectedCategories()) . "</p>";
				echo "<p><strong>Method:</strong> " . htmlspecialchars($consent->getConsentMethod()) . "</p>";
				echo "<p><strong>Timestamp:</strong> " . $consent->getTimestamp()->format('Y-m-d H:i:s') . "</p>";

				// Show cookie value (should be opaque token)
				$cookieName = $banner->getConfig()->getCookieName();
				if (isset($_COOKIE[$cookieName])) {
					echo "<p><strong>Cookie Value (Token):</strong></p>";
					echo "<pre style='word-break: break-all; background: #fff; padding: 10px;'>" .
						htmlspecialchars($_COOKIE[$cookieName]) . "</pre>";
					echo "<p><em>Note: This token cannot be decoded to reveal consent data.</em></p>";
				}
			} else {
				echo "<p>No consent given yet.</p>";
			}
			?>
		</div>

		<div style="background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;">
			<h3>Storage Types</h3>
			<ul>
				<li><strong>legacy:</strong> Original base64 encoded cookie (default, backwards compatible)</li>
				<li><strong>encrypted:</strong> AES-256-GCM encrypted data in cookie</li>
				<li><strong>session:</strong> Uses PHP sessions (good for simple setups)</li>
				<li><strong>callback:</strong> Use your own storage backend (database, Redis, etc.)</li>
			</ul>
		</div>

		<button onclick="window.chronexCbInstance?.showPreferences()" style="padding: 10px 20px; cursor: pointer;">
			Manage Cookie Preferences
		</button>

		<button onclick="window.chronexCbInstance?.withdrawConsent()" style="padding: 10px 20px; cursor: pointer; margin-left: 10px;">
			Withdraw Consent
		</button>
	</div>

	<?php echo $banner->render(); ?>
	<?php echo $banner->renderJs(); ?>

	<script>
	// Listen for consent events
	document.addEventListener('chronex-cb:consent:given', function(e) {
		console.log('Consent given:', e.detail);
		// The cookie now contains an opaque token, not base64 encoded data
	});

	document.addEventListener('chronex-cb:consent:updated', function(e) {
		console.log('Consent updated:', e.detail);
	});

	document.addEventListener('chronex-cb:api:success', function(e) {
		console.log('API success:', e.detail);
		// Reload to show updated status
		if (e.detail.action === 'give_consent') {
			setTimeout(() => location.reload(), 500);
		}
	});
	</script>
</body>
</html>
