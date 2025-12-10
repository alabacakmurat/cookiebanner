<?php

/**
 * Havax Cookie Banner - Blocking Mode Example
 *
 * This example demonstrates blocking mode where users MUST accept cookies
 * before they can use the website. The site is completely inaccessible
 * until consent is given.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Havax\CookieBanner\CookieBanner;

// Initialize with blocking mode enabled
$banner = new CookieBanner([
	'blockingMode' => true,  // This enables full-screen blocking
	'language' => 'en',
	'privacyPolicyUrl' => '/privacy',
	'cookiePolicyUrl' => '/cookies',
	'inlineAssets' => true,
	// Optional: Custom message for blocking mode
	'blockingMessage' => 'This website requires cookies to function. Please accept our cookie policy to continue using our services.',
	'categories' => [
		'necessary' => [
			'enabled' => true,
			'required' => true,
		],
		'functional' => [
			'enabled' => true,
			'required' => false,
		],
		'analytics' => [
			'enabled' => true,
			'required' => false,
		],
		'marketing' => [
			'enabled' => true,
			'required' => false,
		],
	],
]);

// Get consent status
$hasConsent = $banner->hasConsent();
$consent = $banner->getConsent();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Blocking Mode - Havax Cookie Banner</title>
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
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			min-height: 100vh;
		}

		.container {
			max-width: 800px;
			margin: 0 auto;
			padding: 40px 20px;
		}

		.hero {
			text-align: center;
			padding: 60px 20px;
			color: white;
		}

		.hero h1 {
			font-size: 2.5rem;
			margin-bottom: 16px;
			text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
		}

		.hero p {
			font-size: 1.2rem;
			opacity: 0.9;
			max-width: 600px;
			margin: 0 auto;
		}

		.card {
			background: white;
			border-radius: 16px;
			box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
			padding: 32px;
			margin-bottom: 24px;
		}

		.card h2 {
			margin-top: 0;
			color: #4f46e5;
			border-bottom: 2px solid #e5e7eb;
			padding-bottom: 12px;
		}

		.status-badge {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			padding: 8px 16px;
			border-radius: 20px;
			font-weight: 600;
			font-size: 14px;
		}

		.status-badge.active {
			background: #dcfce7;
			color: #166534;
		}

		.status-badge.inactive {
			background: #fee2e2;
			color: #991b1b;
		}

		.feature-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
			gap: 20px;
			margin-top: 24px;
		}

		.feature-item {
			background: #f8fafc;
			border-radius: 12px;
			padding: 20px;
		}

		.feature-item h4 {
			margin: 0 0 8px 0;
			color: #1e293b;
		}

		.feature-item p {
			margin: 0;
			font-size: 14px;
			color: #64748b;
		}

		.btn-group {
			display: flex;
			gap: 12px;
			flex-wrap: wrap;
			margin-top: 24px;
		}

		.btn {
			padding: 12px 24px;
			border: none;
			border-radius: 8px;
			cursor: pointer;
			font-size: 14px;
			font-weight: 600;
			transition: all 0.2s;
		}

		.btn-primary {
			background: linear-gradient(135deg, #4f46e5, #7c3aed);
			color: white;
		}

		.btn-primary:hover {
			transform: translateY(-2px);
			box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
		}

		.btn-secondary {
			background: #f1f5f9;
			color: #475569;
		}

		.btn-secondary:hover {
			background: #e2e8f0;
		}

		.btn-danger {
			background: #ef4444;
			color: white;
		}

		.btn-danger:hover {
			background: #dc2626;
		}

		code {
			background: #1e293b;
			color: #e2e8f0;
			padding: 2px 8px;
			border-radius: 4px;
			font-size: 13px;
		}

		pre {
			background: #1e293b;
			color: #e2e8f0;
			padding: 20px;
			border-radius: 8px;
			overflow-x: auto;
			font-size: 13px;
		}

		.warning {
			background: #fef3c7;
			border: 1px solid #f59e0b;
			border-radius: 8px;
			padding: 16px;
			color: #92400e;
		}

		.warning strong {
			display: block;
			margin-bottom: 4px;
		}
	</style>
</head>

<body>
	<div class="hero">
		<h1>Blocking Mode Demo</h1>
		<p>This page demonstrates the blocking mode where users must accept cookies before accessing the site</p>
	</div>

	<div class="container">
		<!-- Status Card -->
		<div class="card">
			<h2>Consent Status</h2>
			<span class="status-badge <?= $hasConsent ? 'active' : 'inactive' ?>">
				<?php if ($hasConsent): ?>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<polyline points="20 6 9 17 4 12" />
					</svg>
					Consent Given - Site Accessible
				<?php else: ?>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="12" cy="12" r="10" />
						<line x1="15" y1="9" x2="9" y2="15" />
						<line x1="9" y1="9" x2="15" y2="15" />
					</svg>
					No Consent - Site Blocked
				<?php endif; ?>
			</span>

			<?php if ($hasConsent): ?>
				<div class="feature-grid">
					<div class="feature-item">
						<h4>Accepted Categories</h4>
						<p><?= implode(', ', array_map('ucfirst', $consent->getAcceptedCategories())) ?></p>
					</div>
					<div class="feature-item">
						<h4>Consent ID</h4>
						<p style="word-break: break-all;"><?= substr($consent->getConsentId(), 0, 20) ?>...</p>
					</div>
				</div>
			<?php endif; ?>

			<div class="btn-group">
				<?php if ($hasConsent): ?>
					<button class="btn btn-danger" onclick="havaxCbInstance.withdrawConsent()">
						Withdraw Consent (Re-block Site)
					</button>
				<?php else: ?>
					<button class="btn btn-primary" onclick="havaxCbInstance.showBanner()">
						Show Consent Dialog
					</button>
				<?php endif; ?>
			</div>
		</div>

		<!-- How It Works -->
		<div class="card">
			<h2>How Blocking Mode Works</h2>
			<div class="feature-grid">
				<div class="feature-item">
					<h4>Full-Screen Overlay</h4>
					<p>A full-screen overlay blocks all content until the user makes a choice</p>
				</div>
				<div class="feature-item">
					<h4>Body Scroll Lock</h4>
					<p>Page scrolling is disabled while the consent dialog is shown</p>
				</div>
				<div class="feature-item">
					<h4>Click Prevention</h4>
					<p>All interactions outside the dialog are blocked</p>
				</div>
				<div class="feature-item">
					<h4>Keyboard Blocking</h4>
					<p>Keyboard interactions are also prevented except for accessibility</p>
				</div>
			</div>
		</div>

		<!-- Warning -->
		<div class="card">
			<div class="warning">
				<strong>Important Note:</strong>
				Blocking mode should be used carefully. While it ensures consent, it may impact user experience.
				Consider if this approach is appropriate for your use case and complies with your local regulations.
			</div>
		</div>

		<!-- Code Example -->
		<div class="card">
			<h2>Implementation</h2>
			<pre>
$banner = new CookieBanner([
    'blockingMode' => true,  // Enable blocking mode
    'language' => 'en',
    'privacyPolicyUrl' => '/privacy',
    'cookiePolicyUrl' => '/cookies',

    // Optional: Custom blocking message
    'blockingMessage' => 'Please accept our cookies to continue.',
]);

// In your template
echo $banner->renderCss();
echo $banner->render();
echo $banner->renderJs();</pre>
		</div>

		<!-- Language Test -->
		<div class="card">
			<h2>Test Different Languages</h2>
			<p>Try blocking mode in different languages:</p>
			<div class="btn-group">
				<a href="?lang=en" class="btn btn-secondary">English</a>
				<a href="?lang=tr" class="btn btn-secondary">Türkçe</a>
				<a href="?lang=de" class="btn btn-secondary">Deutsch</a>
				<a href="?lang=fr" class="btn btn-secondary">Français</a>
				<a href="?lang=es" class="btn btn-secondary">Español</a>
			</div>
		</div>
	</div>

	<?= $banner->render() ?>
	<?= $banner->renderJs() ?>

	<script>
		// Auto-show banner if no consent (blocking mode will handle this automatically)
		document.addEventListener('havax-cb:init', function() {
			if (!havaxCbInstance.hasConsent()) {
				havaxCbInstance.showBanner();
			}
		});

		// Reload page when consent changes
		document.addEventListener('havax-cb:consent:given', function() {
			setTimeout(() => location.reload(), 500);
		});

		document.addEventListener('havax-cb:consent:withdrawn', function() {
			setTimeout(() => location.reload(), 500);
		});
	</script>
</body>

</html>