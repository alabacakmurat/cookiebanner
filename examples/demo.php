<?php

/**
 * Havax Cookie Banner - Demo Page
 *
 * This page demonstrates all features of the cookie banner library
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Havax\CookieBanner\CookieBanner;
use Havax\CookieBanner\Event\ConsentEvent;

// Get template from query string
$template = $_GET['template'] ?? 'modern';
$language = $_GET['lang'] ?? 'en';
$position = $_GET['position'] ?? 'bottom-right';

// Initialize cookie banner
$banner = new CookieBanner([
	'template' => $template,
	'position' => $position,
	'language' => $language,
	'privacyPolicyUrl' => '#privacy',
	'cookiePolicyUrl' => '#cookies',
	'inlineAssets' => true,
	'categories' => [
		'necessary' => [
			'enabled' => true,
			'required' => true,
			'title' => 'Necessary',
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
	],
]);

// Register event listeners for demo
$banner->on(ConsentEvent::TYPE_GIVEN, function (ConsentEvent $event) {
	// In a real application, you would save this to your database
	error_log('Consent given: ' . json_encode($event->toLogArray()));
});

$banner->on(ConsentEvent::TYPE_UPDATED, function (ConsentEvent $event) {
	error_log('Consent updated: ' . json_encode($event->toLogArray()));
});

// Register some test scripts
$banner->registerScript(
	'google_analytics',
	'analytics',
	'<script async src="https://www.googletagmanager.com/gtag/js?id=GA-DEMO"></script>',
	'google_analytics'
);

$banner->registerScript(
	'facebook_pixel',
	'advertising',
	'<script>console.log("Facebook Pixel would load here");</script>',
	'facebook_pixel'
);

// Available templates and positions
$templates = $banner->getAvailableTemplates();
$positions = [
	'classic' => ['top', 'bottom'],
	'modern' => ['bottom-left', 'bottom-right', 'top-left', 'top-right', 'center'],
	'minimal' => ['bottom-left', 'bottom-right', 'top-left', 'top-right'],
	'floating' => ['bottom-left', 'bottom-right'],
	'blocking' => ['center'],
];

$languages = ['en' => 'English', 'tr' => 'Türkçe', 'de' => 'Deutsch', 'fr' => 'Français', 'es' => 'Español'];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($language) ?>">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Havax Cookie Banner - Demo</title>
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
			color: #333;
		}

		.container {
			max-width: 1200px;
			margin: 0 auto;
			padding: 40px 20px;
		}

		header {
			background: linear-gradient(135deg, #2563eb, #3b82f6);
			color: white;
			padding: 60px 20px;
			text-align: center;
		}

		header h1 {
			margin: 0 0 10px 0;
			font-size: 2.5em;
		}

		header p {
			margin: 0;
			opacity: 0.9;
			font-size: 1.2em;
		}

		.card {
			background: white;
			border-radius: 12px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
			padding: 24px;
			margin-bottom: 24px;
		}

		.card h2 {
			margin-top: 0;
			padding-bottom: 12px;
			border-bottom: 1px solid #eee;
		}

		.controls {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
			gap: 20px;
			margin-bottom: 24px;
		}

		.control-group {
			display: flex;
			flex-direction: column;
			gap: 8px;
		}

		.control-group label {
			font-weight: 600;
			font-size: 14px;
			color: #555;
		}

		.control-group select {
			padding: 10px 14px;
			border: 1px solid #ddd;
			border-radius: 8px;
			font-size: 14px;
			background: white;
			cursor: pointer;
		}

		.control-group select:focus {
			outline: none;
			border-color: #2563eb;
			box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
		}

		.btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 12px 24px;
			border: none;
			border-radius: 8px;
			font-size: 14px;
			font-weight: 500;
			cursor: pointer;
			transition: all 0.2s;
			text-decoration: none;
		}

		.btn-primary {
			background: #2563eb;
			color: white;
		}

		.btn-primary:hover {
			background: #1d4ed8;
		}

		.btn-secondary {
			background: #f3f4f6;
			color: #333;
			border: 1px solid #ddd;
		}

		.btn-secondary:hover {
			background: #e5e7eb;
		}

		.btn-danger {
			background: #ef4444;
			color: white;
		}

		.btn-danger:hover {
			background: #dc2626;
		}

		.actions {
			display: flex;
			flex-wrap: wrap;
			gap: 12px;
		}

		.consent-status {
			padding: 20px;
			background: #f8fafc;
			border-radius: 8px;
			margin-top: 20px;
		}

		.consent-status h3 {
			margin-top: 0;
			font-size: 16px;
		}

		.consent-status pre {
			background: #1e293b;
			color: #e2e8f0;
			padding: 16px;
			border-radius: 8px;
			overflow-x: auto;
			font-size: 13px;
			margin: 0;
		}

		.event-log {
			max-height: 300px;
			overflow-y: auto;
		}

		.event-item {
			padding: 10px 14px;
			background: #f8fafc;
			border-left: 3px solid #2563eb;
			margin-bottom: 8px;
			font-size: 13px;
		}

		.event-item.consent-given {
			border-left-color: #22c55e;
		}

		.event-item.consent-updated {
			border-left-color: #f59e0b;
		}

		.event-item.consent-withdrawn {
			border-left-color: #ef4444;
		}

		.event-time {
			color: #64748b;
			font-size: 12px;
		}

		.grid-2 {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
			gap: 24px;
		}

		.template-preview {
			border: 2px solid #ddd;
			border-radius: 12px;
			overflow: hidden;
			transition: all 0.2s;
			cursor: pointer;
		}

		.template-preview:hover {
			border-color: #2563eb;
			box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
		}

		.template-preview.active {
			border-color: #2563eb;
		}

		.template-preview img {
			width: 100%;
			height: 150px;
			object-fit: cover;
			background: #f1f5f9;
		}

		.template-info {
			padding: 16px;
		}

		.template-info h4 {
			margin: 0 0 4px 0;
		}

		.template-info p {
			margin: 0;
			color: #64748b;
			font-size: 13px;
		}

		footer {
			text-align: center;
			padding: 40px 20px;
			color: #64748b;
		}

		footer a {
			color: #2563eb;
		}
	</style>
	<?= $banner->renderCss() ?>
</head>

<body>
	<header>
		<h1>Havax Cookie Banner</h1>
		<p>GDPR Compliant Cookie Consent Solution</p>
	</header>

	<div class="container">
		<!-- Configuration Card -->
		<div class="card">
			<h2>Configuration</h2>
			<form method="get" class="controls">
				<div class="control-group">
					<label for="template">Template</label>
					<select name="template" id="template" onchange="this.form.submit()">
						<?php foreach ($templates as $t): ?>
							<option value="<?= $t ?>" <?= $t === $template ? 'selected' : '' ?>>
								<?= ucfirst($t) ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="control-group">
					<label for="position">Position</label>
					<select name="position" id="position" onchange="this.form.submit()">
						<?php foreach ($positions[$template] ?? ['bottom'] as $p): ?>
							<option value="<?= $p ?>" <?= $p === $position ? 'selected' : '' ?>>
								<?= ucfirst(str_replace('-', ' ', $p)) ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="control-group">
					<label for="lang">Language</label>
					<select name="lang" id="lang" onchange="this.form.submit()">
						<?php foreach ($languages as $code => $name): ?>
							<option value="<?= $code ?>" <?= $code === $language ? 'selected' : '' ?>>
								<?= $name ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</form>
		</div>

		<!-- Actions Card -->
		<div class="card">
			<h2>Actions</h2>
			<div class="actions">
				<button class="btn btn-primary" onclick="showBanner()">Show Banner</button>
				<button class="btn btn-secondary" onclick="showPreferences()">Open Preferences</button>
				<button class="btn btn-secondary" onclick="acceptAllCookies()">Accept All</button>
				<button class="btn btn-secondary" onclick="rejectAllCookies()">Reject All</button>
				<button class="btn btn-danger" onclick="withdrawConsent()">Withdraw Consent</button>
				<button class="btn btn-secondary" onclick="clearEvents()">Clear Event Log</button>
			</div>

			<div class="consent-status">
				<h3>Current Consent Status</h3>
				<pre id="consent-status">Loading...</pre>
			</div>
		</div>

		<!-- Events Card -->
		<div class="card">
			<h2>Event Log</h2>
			<p style="color: #64748b; margin-top: 0;">Events are logged in real-time as you interact with the banner</p>
			<div id="event-log" class="event-log">
				<div class="event-item">Waiting for events...</div>
			</div>
		</div>

		<!-- Templates Card -->
		<div class="card">
			<h2>Available Templates</h2>
			<div class="grid-2">
				<?php foreach ($templates as $t): ?>
					<a href="?template=<?= $t ?>&lang=<?= $language ?>&position=<?= array_values($positions[$t])[0] ?? 'bottom' ?>"
						class="template-preview <?= $t === $template ? 'active' : '' ?>"
						style="text-decoration: none; color: inherit;">
						<div style="height: 150px; background: linear-gradient(135deg, #f8fafc, #e2e8f0); display: flex; align-items: center; justify-content: center; font-size: 48px;">
							<?php
							$icons = [
								'classic' => '📋',
								'modern' => '✨',
								'minimal' => '💬',
								'floating' => '🎈',
								'blocking' => '🚫',
							];
							echo $icons[$t] ?? '🍪';
							?>
						</div>
						<div class="template-info">
							<h4><?= ucfirst($t) ?></h4>
							<p>
								<?php
								$descriptions = [
									'classic' => 'Full-width banner at top or bottom',
									'modern' => 'Card-style banner with shadow',
									'minimal' => 'Small popup with essential options',
									'floating' => 'Floating button that expands',
									'blocking' => 'Full-screen overlay blocks site access',
								];
								echo $descriptions[$t] ?? 'Cookie banner template';
								?>
							</p>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Code Example Card -->
		<div class="card">
			<h2>Usage Example</h2>
			<pre style="background: #1e293b; color: #e2e8f0; padding: 20px; border-radius: 8px; overflow-x: auto; font-size: 13px;">&lt;?php
use Havax\CookieBanner\CookieBanner;
use Havax\CookieBanner\Event\ConsentEvent;

$banner = new CookieBanner([
    'template' => '<?= $template ?>',
    'position' => '<?= $position ?>',
    'language' => '<?= $language ?>',
    'privacyPolicyUrl' => '/privacy',
    'cookiePolicyUrl' => '/cookies',
]);

// Register event listener for consent logging
$banner->on(ConsentEvent::TYPE_GIVEN, function(ConsentEvent $event) {
    // Save to your database
    $db->insert('consent_logs', $event->toLogArray());
});

// Register scripts that need consent
$banner->registerScript('google_analytics', 'analytics', '&lt;script...&gt;');

// Render in your layout
echo $banner->renderAll();
?&gt;</pre>
		</div>
	</div>

	<footer>
		<p>Havax Cookie Banner v1.0.0 | <a href="https://github.com/alabacakmurat/cookiebanner">GitHub</a></p>
	</footer>

	<?= $banner->render() ?>
	<?= $banner->renderJs() ?>

	<script>
		// Update consent status display
		function updateConsentStatus() {
			const status = document.getElementById('consent-status');
			if (window.havaxCbInstance) {
				const consent = window.havaxCbInstance.getConsentData();
				status.textContent = consent ?
					JSON.stringify(consent, null, 2) :
					'No consent given yet';
			}
		}

		// Log event to UI
		function logEvent(eventName, data) {
			const log = document.getElementById('event-log');
			const firstItem = log.querySelector('.event-item');
			if (firstItem && firstItem.textContent === 'Waiting for events...') {
				firstItem.remove();
			}

			const item = document.createElement('div');
			const eventClass = eventName.includes('given') ? 'consent-given' :
				eventName.includes('updated') ? 'consent-updated' :
				eventName.includes('withdrawn') ? 'consent-withdrawn' :
				'';

			item.className = 'event-item ' + eventClass;
			item.innerHTML = `
                <strong>${eventName}</strong>
                <span class="event-time">${new Date().toLocaleTimeString()}</span>
                <pre style="margin: 8px 0 0; font-size: 12px; white-space: pre-wrap;">${JSON.stringify(data, null, 2)}</pre>
            `;
			log.insertBefore(item, log.firstChild);
		}

		// Wait for banner to initialize
		document.addEventListener('havax-cb:init', function(e) {
			updateConsentStatus();
			logEvent('havax-cb:init', {
				hasConsent: e.detail.consent !== null
			});
		});

		// Listen for all events
		const events = [
			'havax-cb:consent:given',
			'havax-cb:consent:updated',
			'havax-cb:consent:withdrawn',
			'havax-cb:banner:shown',
			'havax-cb:banner:hidden',
			'havax-cb:preferences:opened',
			'havax-cb:preferences:closed',
			'havax-cb:script:loaded',
			'havax-cb:script:blocked',
			'havax-cb:category:enabled',
			'havax-cb:category:disabled'
		];

		events.forEach(eventName => {
			document.addEventListener(eventName, function(e) {
				logEvent(eventName, e.detail);
				updateConsentStatus();
			});
		});

		// Action functions
		function showBanner() {
			if (window.havaxCbInstance) {
				window.havaxCbInstance.showBanner();
			}
		}

		function showPreferences() {
			if (window.havaxCbInstance) {
				window.havaxCbInstance.showPreferences();
			}
		}

		function acceptAllCookies() {
			if (window.havaxCbInstance) {
				window.havaxCbInstance.acceptAll();
			}
		}

		function rejectAllCookies() {
			if (window.havaxCbInstance) {
				window.havaxCbInstance.rejectAll();
			}
		}

		function withdrawConsent() {
			if (window.havaxCbInstance) {
				window.havaxCbInstance.withdrawConsent();
			}
		}

		function clearEvents() {
			document.getElementById('event-log').innerHTML = '<div class="event-item">Waiting for events...</div>';
		}

		// Initial status update
		setTimeout(updateConsentStatus, 100);

		// Show banner if no consent
		setTimeout(function() {
			if (window.havaxCbInstance && !window.havaxCbInstance.hasConsent()) {
				window.havaxCbInstance.showBanner();
			}
		}, 500);
	</script>
</body>

</html>