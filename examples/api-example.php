<?php

/**
 * Chronex Cookie Banner - API Usage Example
 *
 * This example demonstrates how to use the PHP API with JavaScript integration.
 * The API enables server-side event handling for consent logging, database storage,
 * and user tracking.
 *
 * ============================================================================
 * API DOCUMENTATION
 * ============================================================================
 *
 * CONFIGURATION
 * -------------
 * To enable the API, set the 'apiUrl' option when initializing CookieBanner:
 *
 *   $banner = new CookieBanner([
 *       'apiUrl' => '?api=1',  // or '/api/consent' or any endpoint
 *   ]);
 *
 * API ACTIONS
 * -----------
 * The API accepts POST requests with JSON body containing an 'action' field:
 *
 * 1. get_consent
 *    - Returns current consent status
 *    - Request:  { "action": "get_consent" }
 *    - Response: { "success": true, "data": { "hasConsent": true, "accepted": [...], "rejected": [...] } }
 *
 * 2. give_consent
 *    - Gives consent for specified categories
 *    - Request:  { "action": "give_consent", "categories": ["necessary", "analytics"], "method": "banner" }
 *    - Response: { "success": true, "data": { "consent_id": "...", ... } }
 *
 * 3. accept_all
 *    - Accepts all cookie categories
 *    - Request:  { "action": "accept_all", "method": "banner" }
 *    - Response: { "success": true, "data": { "consent_id": "...", ... } }
 *
 * 4. reject_all
 *    - Rejects all optional cookies (keeps required only)
 *    - Request:  { "action": "reject_all", "method": "banner" }
 *    - Response: { "success": true, "data": { "consent_id": "...", ... } }
 *
 * 5. withdraw_consent
 *    - Withdraws all consent
 *    - Request:  { "action": "withdraw_consent", "previous_consent": { ... } }
 *    - Response: { "success": true, "data": { "withdrawn": true } }
 *
 * PHP EVENTS
 * ----------
 * When API is used, these PHP events are triggered:
 *
 * - ConsentEvent::TYPE_GIVEN     - First time consent is given
 * - ConsentEvent::TYPE_UPDATED   - Consent preferences are updated
 * - ConsentEvent::TYPE_WITHDRAWN - Consent is withdrawn
 *
 * JAVASCRIPT EVENTS
 * -----------------
 * JavaScript dispatches these events on document:
 *
 * - chronex-cb:init              - Banner initialized
 * - chronex-cb:consent:given     - First consent given
 * - chronex-cb:consent:updated   - Consent updated
 * - chronex-cb:consent:withdrawn - Consent withdrawn
 * - chronex-cb:api:success       - API call successful
 * - chronex-cb:api:error         - API call failed
 *
 * USER IDENTIFIER
 * ---------------
 * To associate consent with logged-in users:
 *
 *   $banner->setUserIdentifier($userId);
 *   // or with hash for privacy:
 *   $banner->setUserIdentifier(hash('sha256', $userEmail . $salt));
 *
 * ============================================================================
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Chronex\CookieBanner\CookieBanner;
use Chronex\CookieBanner\Event\ConsentEvent;

// ============================================================================
// BASIC SETUP WITH API
// ============================================================================

$banner = new CookieBanner([
	'template' => 'modern',
	'position' => 'bottom-right',
	'language' => 'en',
	'privacyPolicyUrl' => '/privacy',
	'inlineAssets' => true,
	'apiUrl' => '?api=1', // Enable API - JavaScript will call this endpoint
]);

// ============================================================================
// USER IDENTIFIER (Optional - for logged-in users)
// ============================================================================

// Simulate a logged-in user (in real app, get from session)
$loggedInUser = [
	'id' => 12345,
	'email' => 'user@example.com',
];

// Option 1: Use user ID directly
// $banner->setUserIdentifier((string) $loggedInUser['id']);

// Option 2: Use hashed identifier for privacy (recommended)
$salt = 'your-secret-salt-here';
$hashedIdentifier = hash('sha256', $loggedInUser['email'] . $salt);
$banner->setUserIdentifier($hashedIdentifier);

// ============================================================================
// PHP EVENT HANDLERS
// ============================================================================

// Helper function for logging
function logToFile(string $filename, array $data): void
{
	$logFile = __DIR__ . '/' . $filename;
	$logs = [];

	if (file_exists($logFile)) {
		$content = file_get_contents($logFile);
		$decoded = json_decode($content, true);
		if (is_array($decoded)) {
			$logs = $decoded;
		}
	}

	$logs[] = $data;

	file_put_contents(
		$logFile,
		json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
	);
}

// Event: First consent given
$banner->on(ConsentEvent::TYPE_GIVEN, function (ConsentEvent $event) {
	logToFile('api_consent_log.json', [
		'event' => 'consent_given',
		'consent_id' => $event->getConsentId(),
		'user_identifier' => $event->getUserIdentifier(),
		'timestamp' => $event->getTimestamp()->format('Y-m-d H:i:s'),
		'ip_anonymized' => $event->getAnonymizedIpAddress(),
		'accepted' => $event->getAcceptedCategories(),
		'rejected' => $event->getRejectedCategories(),
		'method' => $event->getConsentMethod(),
	]);
});

// Event: Consent updated
$banner->on(ConsentEvent::TYPE_UPDATED, function (ConsentEvent $event) {
	$previous = $event->getPreviousConsent();
	logToFile('api_consent_log.json', [
		'event' => 'consent_updated',
		'consent_id' => $event->getConsentId(),
		'user_identifier' => $event->getUserIdentifier(),
		'timestamp' => $event->getTimestamp()->format('Y-m-d H:i:s'),
		'accepted' => $event->getAcceptedCategories(),
		'rejected' => $event->getRejectedCategories(),
		'previous_consent_id' => $previous['consent_id'] ?? null,
		'previous_accepted' => $previous['accepted_categories'] ?? [],
	]);
});

// Event: Consent withdrawn
$banner->on(ConsentEvent::TYPE_WITHDRAWN, function (ConsentEvent $event) {
	logToFile('api_consent_log.json', [
		'event' => 'consent_withdrawn',
		'timestamp' => date('Y-m-d H:i:s'),
		'user_identifier' => $event->getUserIdentifier(),
		'previous_consent_id' => $event->getConsentId(),
		'previous_accepted' => $event->getAcceptedCategories(),
	]);
});

// ============================================================================
// HANDLE API REQUESTS
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
	header('Content-Type: application/json');
	$request = json_decode(file_get_contents('php://input'), true) ?? $_POST;
	$response = $banner->handleApiRequest($request);
	echo json_encode($response);
	exit;
}

// ============================================================================
// PAGE OUTPUT
// ============================================================================

$hasConsent = $banner->hasConsent();
$userIdentifier = $banner->getUserIdentifier();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>API Example - Chronex Cookie Banner</title>
	<?= $banner->renderCss() ?>
	<style>
		* {
			box-sizing: border-box;
		}

		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
			line-height: 1.6;
			margin: 0;
			padding: 0;
			background: #f8fafc;
		}

		.container {
			max-width: 900px;
			margin: 0 auto;
			padding: 40px 20px;
		}

		h1 {
			color: #1e40af;
			margin-bottom: 8px;
		}

		.subtitle {
			color: #64748b;
			margin-bottom: 32px;
		}

		.card {
			background: white;
			border-radius: 12px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
			padding: 24px;
			margin-bottom: 24px;
		}

		.card h2 {
			margin-top: 0;
			color: #334155;
			border-bottom: 1px solid #e2e8f0;
			padding-bottom: 12px;
		}

		.status {
			display: flex;
			gap: 16px;
			flex-wrap: wrap;
		}

		.status-item {
			background: #f1f5f9;
			padding: 12px 16px;
			border-radius: 8px;
			flex: 1;
			min-width: 200px;
		}

		.status-item.active {
			background: #dcfce7;
			border: 1px solid #22c55e;
		}

		.status-item label {
			display: block;
			font-size: 12px;
			color: #64748b;
			text-transform: uppercase;
			margin-bottom: 4px;
		}

		.status-item span {
			font-weight: 600;
			color: #1e293b;
		}

		pre {
			background: #1e293b;
			color: #e2e8f0;
			padding: 16px;
			border-radius: 8px;
			overflow-x: auto;
			font-size: 13px;
			line-height: 1.5;
		}

		code {
			font-family: 'Fira Code', 'Monaco', monospace;
		}

		.btn {
			padding: 10px 20px;
			border: none;
			border-radius: 8px;
			cursor: pointer;
			font-size: 14px;
			font-weight: 500;
			margin-right: 8px;
			margin-bottom: 8px;
		}

		.btn-primary {
			background: #2563eb;
			color: white;
		}

		.btn-primary:hover {
			background: #1d4ed8;
		}

		.btn-secondary {
			background: #e2e8f0;
			color: #475569;
		}

		.btn-secondary:hover {
			background: #cbd5e1;
		}

		.btn-danger {
			background: #ef4444;
			color: white;
		}

		.btn-danger:hover {
			background: #dc2626;
		}

		#event-log {
			background: #0f172a;
			color: #94a3b8;
			padding: 16px;
			border-radius: 8px;
			height: 200px;
			overflow-y: auto;
			font-family: monospace;
			font-size: 12px;
		}

		#event-log .event {
			margin-bottom: 8px;
			padding: 4px 8px;
			border-radius: 4px;
		}

		#event-log .event.given {
			background: rgba(34, 197, 94, 0.2);
			color: #4ade80;
		}

		#event-log .event.updated {
			background: rgba(59, 130, 246, 0.2);
			color: #60a5fa;
		}

		#event-log .event.withdrawn {
			background: rgba(239, 68, 68, 0.2);
			color: #f87171;
		}

		#event-log .event.api {
			background: rgba(168, 85, 247, 0.2);
			color: #c084fc;
		}

		.section-title {
			font-size: 14px;
			font-weight: 600;
			color: #64748b;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			margin-bottom: 12px;
		}
	</style>
</head>

<body>
	<div class="container">
		<h1>API Integration Example</h1>
		<p class="subtitle">PHP event handling with JavaScript consent management</p>

		<!-- Current Status -->
		<div class="card">
			<h2>Current Status</h2>
			<div class="status">
				<div class="status-item <?= $hasConsent ? 'active' : '' ?>">
					<label>Consent Status</label>
					<span><?= $hasConsent ? 'Given' : 'Not Given' ?></span>
				</div>
				<div class="status-item">
					<label>User Identifier</label>
					<span><?= $userIdentifier ? substr($userIdentifier, 0, 16) . '...' : 'Not Set' ?></span>
				</div>
				<div class="status-item">
					<label>API Endpoint</label>
					<span>?api=1</span>
				</div>
			</div>

			<div style="margin-top: 20px;">
				<button class="btn btn-primary" onclick="chronexCbInstance.showBanner()">Show Banner</button>
				<button class="btn btn-secondary" onclick="chronexCbInstance.showPreferences()">Preferences</button>
				<button class="btn btn-danger" onclick="chronexCbInstance.withdrawConsent()">Withdraw</button>
			</div>
		</div>

		<!-- Event Log -->
		<div class="card">
			<h2>Live Event Log</h2>
			<p style="color: #64748b; margin-bottom: 16px;">Events are logged here in real-time (JS) and saved to api_consent_log.json (PHP)</p>
			<div id="event-log"></div>
		</div>

		<!-- PHP Setup Code -->
		<div class="card">
			<h2>PHP Setup</h2>
			<div class="section-title">1. Initialize with API URL</div>
			<pre><code>$banner = new CookieBanner([
    'template' => 'modern',
    'apiUrl' => '?api=1', // Enable API
]);

// Optional: Associate with logged-in user
$banner->setUserIdentifier(hash('sha256', $userEmail . $salt));</code></pre>

			<div class="section-title" style="margin-top: 24px;">2. Register Event Handlers</div>
			<pre><code>$banner->on(ConsentEvent::TYPE_GIVEN, function (ConsentEvent $event) {
    // Save to database
    $db->insert('consent_logs', [
        'consent_id' => $event->getConsentId(),
        'user_id' => $event->getUserIdentifier(),
        'accepted' => json_encode($event->getAcceptedCategories()),
        'timestamp' => $event->getTimestamp()->format('Y-m-d H:i:s'),
    ]);
});

$banner->on(ConsentEvent::TYPE_UPDATED, function (ConsentEvent $event) {
    // Log consent changes
});

$banner->on(ConsentEvent::TYPE_WITHDRAWN, function (ConsentEvent $event) {
    // Handle withdrawal
});</code></pre>

			<div class="section-title" style="margin-top: 24px;">3. Handle API Requests</div>
			<pre><code>if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
    header('Content-Type: application/json');
    $request = json_decode(file_get_contents('php://input'), true);
    echo json_encode($banner->handleApiRequest($request));
    exit;
}</code></pre>
		</div>

		<!-- JavaScript API -->
		<div class="card">
			<h2>JavaScript API</h2>
			<div class="section-title">Methods</div>
			<pre><code>// Show/hide banner
chronexCbInstance.showBanner();
chronexCbInstance.hideBanner();

// Manage consent
chronexCbInstance.acceptAll();
chronexCbInstance.rejectAll();
chronexCbInstance.giveConsent(['necessary', 'analytics'], 'custom');
chronexCbInstance.withdrawConsent();

// Check consent
chronexCbInstance.hasConsent();           // true/false
chronexCbInstance.hasConsentFor('analytics'); // true/false
chronexCbInstance.getAcceptedCategories();    // ['necessary', ...]

// Preferences modal
chronexCbInstance.showPreferences();
chronexCbInstance.closePreferences();</code></pre>

			<div class="section-title" style="margin-top: 24px;">Events</div>
			<pre><code>document.addEventListener('chronex-cb:consent:given', function(e) {
    console.log('Consent given:', e.detail.acceptedCategories);
});

document.addEventListener('chronex-cb:consent:updated', function(e) {
    console.log('Consent updated:', e.detail.consent);
});

document.addEventListener('chronex-cb:consent:withdrawn', function(e) {
    console.log('Consent withdrawn');
});

document.addEventListener('chronex-cb:api:success', function(e) {
    console.log('API success:', e.detail.action);
});

document.addEventListener('chronex-cb:api:error', function(e) {
    console.log('API error:', e.detail.error);
});</code></pre>
		</div>
	</div>

	<?= $banner->render() ?>
	<?= $banner->renderJs() ?>

	<script>
		const eventLog = document.getElementById('event-log');

		function logEvent(type, message, className) {
			const time = new Date().toLocaleTimeString();
			const div = document.createElement('div');
			div.className = 'event ' + className;
			div.innerHTML = `<strong>[${time}]</strong> ${type}: ${message}`;
			eventLog.insertBefore(div, eventLog.firstChild);
		}

		// Listen for all events
		document.addEventListener('chronex-cb:init', function(e) {
			logEvent('INIT', 'Banner initialized', 'api');
		});

		document.addEventListener('chronex-cb:consent:given', function(e) {
			const cats = e.detail.acceptedCategories.join(', ');
			logEvent('CONSENT_GIVEN', `Accepted: ${cats}`, 'given');
		});

		document.addEventListener('chronex-cb:consent:updated', function(e) {
			const cats = e.detail.acceptedCategories.join(', ');
			logEvent('CONSENT_UPDATED', `Now accepted: ${cats}`, 'updated');
		});

		document.addEventListener('chronex-cb:consent:withdrawn', function(e) {
			logEvent('CONSENT_WITHDRAWN', 'All consent removed', 'withdrawn');
		});

		document.addEventListener('chronex-cb:api:success', function(e) {
			logEvent('API_SUCCESS', `Action: ${e.detail.action}`, 'api');
		});

		document.addEventListener('chronex-cb:api:error', function(e) {
			logEvent('API_ERROR', `${e.detail.action}: ${e.detail.error}`, 'withdrawn');
		});

		// Show banner if no consent
		document.addEventListener('chronex-cb:init', function() {
			if (!chronexCbInstance.hasConsent()) {
				chronexCbInstance.showBanner();
			}
		});
	</script>
</body>

</html>