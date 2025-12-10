<?php

/**
 * VKM Cookie Banner - Advanced Usage Example
 *
 * This example shows advanced features like:
 * - Event hooks for consent logging
 * - Script blocking
 * - Custom categories
 * - Custom translations
 * - API integration
 */

require_once __DIR__ . '/../vendor/autoload.php';

use VkmToolkit\CookieBanner\CookieBanner;
use VkmToolkit\CookieBanner\Event\ConsentEvent;
use VkmToolkit\CookieBanner\Event\ScriptLoadedEvent;
use VkmToolkit\CookieBanner\Event\ScriptBlockedEvent;

// Initialize with advanced options
$banner = new CookieBanner([
	'template' => 'modern',
	'position' => 'bottom-right',
	'language' => 'en',
	'privacyPolicyUrl' => '/privacy',
	'cookiePolicyUrl' => '/cookies',
	'inlineAssets' => true,
	'autoBlock' => true,
	'cookieExpiry' => 365,
	'apiUrl' => '?api=1', // Enable PHP event hooks via API
	'categories' => [
		'necessary' => [
			'enabled' => true,
			'required' => true,
		],
		'functional' => [
			'enabled' => true,
			'required' => false,
			'default' => false,
		],
		'analytics' => [
			'enabled' => true,
			'required' => false,
			'default' => false,
		],
		'marketing' => [
			'enabled' => true,
			'required' => false,
			'default' => false,
		],
		'advertising' => [
			'enabled' => true,
			'required' => false,
			'default' => false,
		],
		// Custom category
		'social' => [
			'enabled' => true,
			'required' => false,
			'default' => false,
			'title' => 'Social Media',
			'description' => 'Cookies for social media integrations and sharing features.',
		],
	],
	'translations' => [
		'en' => [
			'category_social_title' => 'Social Media',
			'category_social_description' => 'Enable social media features like share buttons and embedded content.',
		],
	],
]);

// ============================================================
// Event Hooks - This is where you integrate with your database
// ============================================================

// Helper function to save log as proper JSON array
function saveConsentLog(array $logData): void {
	$logFile = __DIR__ . '/consent_log.json';

	// Read existing logs or create empty array
	$logs = [];
	if (file_exists($logFile)) {
		$content = file_get_contents($logFile);
		$decoded = json_decode($content, true);
		if (is_array($decoded)) {
			$logs = $decoded;
		}
	}

	// Add new log entry
	$logs[] = $logData;

	// Save as formatted JSON array
	file_put_contents(
		$logFile,
		json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
	);
}

// Log all consent events
$banner->on(ConsentEvent::TYPE_GIVEN, function (ConsentEvent $event) {
	// In a real application, save to database
	$logData = [
		'event' => 'consent_given',
		'consent_id' => $event->getConsentId(),
		'timestamp' => $event->getTimestamp()->format('Y-m-d H:i:s'),
		'ip' => $event->getAnonymizedIpAddress(), // Use anonymized IP for GDPR
		'user_agent' => $event->getUserAgent(),
		'accepted' => $event->getAcceptedCategories(),
		'rejected' => $event->getRejectedCategories(),
		'method' => $event->getConsentMethod(),
		'proof' => $event->getConsentProof(),
	];

	saveConsentLog($logData);
	error_log('CONSENT GIVEN: ' . json_encode($logData));
});

$banner->on(ConsentEvent::TYPE_UPDATED, function (ConsentEvent $event) {
	$logData = [
		'event' => 'consent_updated',
		'consent_id' => $event->getConsentId(),
		'timestamp' => $event->getTimestamp()->format('Y-m-d H:i:s'),
		'accepted' => $event->getAcceptedCategories(),
		'rejected' => $event->getRejectedCategories(),
		'previous' => $event->getPreviousConsent(),
	];

	saveConsentLog($logData);
});

$banner->on(ConsentEvent::TYPE_WITHDRAWN, function (ConsentEvent $event) {
	$logData = [
		'event' => 'consent_withdrawn',
		'timestamp' => date('Y-m-d H:i:s'),
		'previous_consent_id' => $event->getConsentId(),
		'previous_accepted' => $event->getAcceptedCategories(),
		'previous_rejected' => $event->getRejectedCategories(),
	];

	saveConsentLog($logData);
});

// Listen for script events
$banner->on(ScriptLoadedEvent::NAME, function (ScriptLoadedEvent $event) {
	die('Script loaded: ' . $event->getScriptId() . ' (category: ' . $event->getCategory() . ')');
});

$banner->on(ScriptBlockedEvent::NAME, function (ScriptBlockedEvent $event) {
	error_log('Script blocked: ' . $event->getScriptId() . ' (category: ' . $event->getCategory() . ')');
});

// ============================================================
// Register Third-Party Scripts
// ============================================================

// Google Analytics
$banner->registerScript(
	'google_analytics',
	'analytics',
	<<<HTML
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'GA_MEASUREMENT_ID');
        console.log('Google Analytics loaded!');
    </script>
    HTML,
	'google_analytics'
);

// Facebook Pixel
$banner->registerScript(
	'facebook_pixel',
	'advertising',
	<<<HTML
    <script>
        // Facebook Pixel would load here
        console.log('Facebook Pixel loaded!');
    </script>
    HTML,
	'facebook_pixel'
);

// Hotjar
$banner->registerScript(
	'hotjar',
	'analytics',
	<<<HTML
    <script>
        // Hotjar would load here
        console.log('Hotjar loaded!');
    </script>
    HTML,
	'hotjar'
);

// Custom social media script
$banner->registerScript(
	'social_share',
	'social',
	<<<HTML
    <script>
        console.log('Social sharing buttons loaded!');
    </script>
    HTML,
	'social_share'
);

// ============================================================
// Handle API requests (for AJAX consent management)
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
	header('Content-Type: application/json');
	$request = json_decode(file_get_contents('php://input'), true) ?? $_POST;
	$response = $banner->handleApiRequest($request);
	echo json_encode($response);
	exit;
}

// Get current consent info
$hasConsent = $banner->hasConsent();
$consent = $banner->getConsent();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Advanced Usage - VKM Cookie Banner</title>
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
			background: #f5f5f5;
		}

		.container {
			max-width: 1200px;
			margin: 0 auto;
			padding: 40px 20px;
		}

		h1 {
			color: #2563eb;
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
			border-bottom: 1px solid #eee;
			padding-bottom: 12px;
		}

		pre {
			background: #1e293b;
			color: #e2e8f0;
			padding: 20px;
			border-radius: 8px;
			overflow-x: auto;
			font-size: 13px;
		}

		.grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
			gap: 20px;
		}

		.status-card {
			padding: 20px;
			border-radius: 8px;
			background: #f8fafc;
		}

		.status-card.active {
			background: #ecfdf5;
			border: 1px solid #22c55e;
		}

		.status-card.inactive {
			background: #fef2f2;
			border: 1px solid #ef4444;
		}

		.status-card h4 {
			margin: 0 0 8px 0;
		}

		.badge {
			display: inline-block;
			padding: 4px 10px;
			border-radius: 12px;
			font-size: 12px;
			font-weight: 500;
		}

		.badge-green {
			background: #dcfce7;
			color: #166534;
		}

		.badge-red {
			background: #fee2e2;
			color: #991b1b;
		}

		.badge-blue {
			background: #dbeafe;
			color: #1e40af;
		}

		.btn {
			padding: 10px 20px;
			border: none;
			border-radius: 8px;
			cursor: pointer;
			font-size: 14px;
			font-weight: 500;
			transition: all 0.2s;
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
			background: #f1f5f9;
			color: #475569;
		}

		.btn-secondary:hover {
			background: #e2e8f0;
		}

		.scripts-status {
			margin-top: 16px;
		}

		.script-item {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 12px 16px;
			background: #f8fafc;
			border-radius: 8px;
			margin-bottom: 8px;
		}

		.script-name {
			font-weight: 500;
		}

		.script-category {
			color: #64748b;
			font-size: 13px;
		}
	</style>
</head>

<body>
	<div class="container">
		<h1>Advanced Usage Example</h1>
		<p class="subtitle">Event hooks, script blocking, custom categories, and more</p>

		<!-- Current Status -->
		<div class="card">
			<h2>Current Consent Status</h2>
			<div class="grid">
				<div class="status-card <?= $hasConsent ? 'active' : 'inactive' ?>">
					<h4>Consent Status</h4>
					<?php if ($hasConsent): ?>
						<span class="badge badge-green">Consent Given</span>
						<p style="margin: 12px 0 0; font-size: 13px; color: #64748b;">
							ID: <?= substr($consent->getConsentId(), 0, 16) ?>...
						</p>
					<?php else: ?>
						<span class="badge badge-red">No Consent</span>
					<?php endif; ?>
				</div>

				<div class="status-card">
					<h4>Accepted Categories</h4>
					<?php
					$accepted = $hasConsent ? $consent->getAcceptedCategories() : ['necessary'];
					foreach ($accepted as $cat): ?>
						<span class="badge badge-green"><?= ucfirst($cat) ?></span>
					<?php endforeach; ?>
				</div>

				<div class="status-card">
					<h4>Blocked Categories</h4>
					<?php
					$rejected = $hasConsent ? $consent->getRejectedCategories() : [];
					if (empty($rejected)): ?>
						<span class="badge badge-blue">None</span>
					<?php else: ?>
						<?php foreach ($rejected as $cat): ?>
							<span class="badge badge-red"><?= ucfirst($cat) ?></span>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>

			<div style="margin-top: 20px;">
				<button class="btn btn-primary" onclick="vkmCookieBanner.showBanner()">Show Banner</button>
				<button class="btn btn-secondary" onclick="vkmCookieBanner.showPreferences()">Open Preferences</button>
				<button class="btn btn-secondary" onclick="vkmCookieBanner.withdrawConsent()">Withdraw Consent</button>
			</div>
		</div>

		<!-- Registered Scripts -->
		<div class="card">
			<h2>Registered Scripts</h2>
			<p style="color: #64748b;">These scripts are conditionally loaded based on user consent</p>

			<div class="scripts-status">
				<?php
				$scripts = [
					['id' => 'google_analytics', 'name' => 'Google Analytics', 'category' => 'analytics'],
					['id' => 'facebook_pixel', 'name' => 'Facebook Pixel', 'category' => 'advertising'],
					['id' => 'hotjar', 'name' => 'Hotjar', 'category' => 'analytics'],
					['id' => 'social_share', 'name' => 'Social Share', 'category' => 'social'],
				];
				foreach ($scripts as $script):
					$allowed = $banner->hasConsentFor($script['category']);
				?>
					<div class="script-item">
						<div>
							<div class="script-name"><?= $script['name'] ?></div>
							<div class="script-category">Category: <?= ucfirst($script['category']) ?></div>
						</div>
						<span class="badge <?= $allowed ? 'badge-green' : 'badge-red' ?>">
							<?= $allowed ? 'Allowed' : 'Blocked' ?>
						</span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Code Example -->
		<div class="card">
			<h2>Event Hooks Example</h2>
			<pre>
// Register event listeners to save consent to your database
$banner->on(ConsentEvent::TYPE_GIVEN, function(ConsentEvent $event) {
    $db->insert('consent_logs', [
        'consent_id' => $event->getConsentId(),
        'timestamp' => $event->getTimestamp()->format('Y-m-d H:i:s'),
        'ip_anonymized' => $event->getAnonymizedIpAddress(),
        'user_agent' => $event->getUserAgent(),
        'accepted' => json_encode($event->getAcceptedCategories()),
        'rejected' => json_encode($event->getRejectedCategories()),
        'method' => $event->getConsentMethod(),
        'consent_proof' => $event->getConsentProof(),
    ]);
});

$banner->on(ConsentEvent::TYPE_UPDATED, function(ConsentEvent $event) {
    // Log consent updates
});

$banner->on(ConsentEvent::TYPE_WITHDRAWN, function(ConsentEvent $event) {
    // Handle consent withdrawal
});</pre>
		</div>

		<!-- Script Registration -->
		<div class="card">
			<h2>Script Registration Example</h2>
			<pre>
// Register scripts that need consent
$banner->registerScript(
    'google_analytics',
    'analytics',
    '&lt;script src="https://www.googletagmanager.com/gtag/js?id=GA_ID"&gt;&lt;/script&gt;',
    'google_analytics'
);

// Scripts are automatically blocked if consent not given
// and automatically loaded when consent is granted</pre>
		</div>

		<!-- API Example -->
		<div class="card">
			<h2>API Integration</h2>
			<pre>
// Handle AJAX consent requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
    $request = json_decode(file_get_contents('php://input'), true);
    $response = $banner->handleApiRequest($request);
    echo json_encode($response);
}

// Available actions:
// - get_consent
// - give_consent (with categories array)
// - accept_all
// - reject_all
// - withdraw_consent</pre>
		</div>
	</div>

	<?= $banner->render() ?>
	<?= $banner->renderJs() ?>

	<!-- Render registered scripts (conditionally based on consent) -->
	<?= $banner->renderAllScripts() ?>

	<script>
		// Show banner if no consent
		document.addEventListener('vkm:init', function() {
			if (!vkmCookieBanner.hasConsent()) {
				vkmCookieBanner.showBanner();
			}
		});

		// Reload page on consent change to show updated status
		document.addEventListener('vkm:consent:given', function() {
			setTimeout(() => location.reload(), 500);
		});

		document.addEventListener('vkm:consent:withdrawn', function() {
			setTimeout(() => location.reload(), 500);
		});
	</script>
</body>

</html>