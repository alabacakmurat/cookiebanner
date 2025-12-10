<?php

/**
 * Havax Cookie Banner - Basic Usage Example
 *
 * This is the simplest way to use the cookie banner
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Havax\CookieBanner\CookieBanner;

// Basic initialization with defaults
$banner = new CookieBanner([
	'blockingMode' => true,
	'template' => 'modern',
	'position' => 'bottom-right',
	'language' => 'en',
	'privacyPolicyUrl' => '/privacy-policy',
	'inlineAssets' => true,
]);
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Basic Usage - Havax Cookie Banner</title>
	<?= $banner->renderCss() ?>
	<style>
		body {
			font-family: system-ui, sans-serif;
			max-width: 800px;
			margin: 0 auto;
			padding: 40px 20px;
			line-height: 1.6;
		}

		h1 {
			color: #2563eb;
		}

		pre {
			background: #f1f5f9;
			padding: 20px;
			border-radius: 8px;
			overflow-x: auto;
		}
	</style>
</head>

<body>
	<h1>Basic Usage Example</h1>

	<p>This page demonstrates the most basic usage of Havax Cookie Banner.</p>

	<h2>Consent Status</h2>
	<p id="status">Checking...</p>

	<h2>Code</h2>
	<pre>&lt;?php
use Havax\CookieBanner\CookieBanner;

$banner = new CookieBanner([
    'template' => 'modern',
    'position' => 'bottom-right',
    'language' => 'en',
    'privacyPolicyUrl' => '/privacy-policy',
    'inlineAssets' => true,
]);

// In your &lt;head&gt;:
echo $banner->renderCss();

// Before &lt;/body&gt;:
echo $banner->render();
echo $banner->renderJs();
?&gt;</pre>

	<?= $banner->render() ?>
	<?= $banner->renderJs() ?>

	<script>
		document.addEventListener('havax-cb:init', function() {
			updateStatus();
		});

		document.addEventListener('havax-cb:consent:given', function() {
			updateStatus();
		});

		document.addEventListener('havax-cb:consent:withdrawn', function() {
			updateStatus();
		});

		function updateStatus() {
			const status = document.getElementById('status');
			if (window.havaxCbInstance) {
				const hasConsent = window.havaxCbInstance.hasConsent();
				const accepted = window.havaxCbInstance.getAcceptedCategories();
				status.innerHTML = hasConsent ?
					'<strong style="color: green;">Consent given!</strong><br>Accepted: ' + accepted.join(', ') :
					'<strong style="color: orange;">No consent yet</strong>';
			}
		}

		// Show banner on page load if no consent
		setTimeout(function() {
			if (window.havaxCbInstance && !window.havaxCbInstance.hasConsent()) {
				window.havaxCbInstance.showBanner();
			}
		}, 500);
	</script>
</body>

</html>