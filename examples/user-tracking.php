<?php

/**
 * Havax Cookie Banner - User Tracking Example
 *
 * This example demonstrates how to associate cookie consent with logged-in users.
 * This is useful for:
 * - Linking consent records to user accounts in your database
 * - Allowing users to manage their consent from their profile
 * - Syncing consent across devices for the same user
 * - GDPR compliance: providing users access to their consent history
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Havax\CookieBanner\CookieBanner;
use Havax\CookieBanner\Event\ConsentEvent;

// ============================================================
// Simulate a logged-in user (replace with your auth system)
// ============================================================

// In a real application, you would get this from your session/auth system:
// $currentUser = Auth::user();
// $userId = $currentUser?->id;

// For demo purposes, we'll simulate login/logout via query string
$isLoggedIn = isset($_GET['user']);
$userId = $_GET['user'] ?? null;

// Create a hashed identifier for privacy (recommended)
// You can use: user ID, email hash, or any unique identifier
$userIdentifier = $userId ? hash('sha256', 'user_' . $userId . '_secret_salt') : null;

// ============================================================
// Initialize Cookie Banner with User Identifier
// ============================================================

$banner = new CookieBanner([
	'template' => 'modern',
	'position' => 'bottom-right',
	'language' => 'en',
	'privacyPolicyUrl' => '/privacy',
	'cookiePolicyUrl' => '/cookies',
	'inlineAssets' => true,
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

// Set user identifier if logged in
if ($userIdentifier) {
	$banner->setUserIdentifier($userIdentifier);
}

// ============================================================
// Event Hooks - Save consent with user association
// ============================================================

$banner->on(ConsentEvent::TYPE_GIVEN, function (ConsentEvent $event) use ($userId, $isLoggedIn) {
	$logData = [
		'event' => 'consent_given',
		'consent_id' => $event->getConsentId(),
		'timestamp' => $event->getTimestamp()->format('Y-m-d H:i:s'),

		// User association
		'user_identifier' => $event->getUserIdentifier(), // Hashed identifier
		'is_logged_in' => $isLoggedIn,
		'user_id' => $userId, // Original user ID (for your database)

		// Consent details
		'accepted' => $event->getAcceptedCategories(),
		'rejected' => $event->getRejectedCategories(),
		'method' => $event->getConsentMethod(),

		// Technical details
		'ip_anonymized' => $event->getAnonymizedIpAddress(),
		'user_agent' => $event->getUserAgent(),
		'page_url' => $event->getPageUrl(),
	];

	// In production, save to your database:
	// $db->table('consent_records')->insert([
	//     'user_id' => $userId,
	//     'consent_id' => $event->getConsentId(),
	//     'accepted_categories' => json_encode($event->getAcceptedCategories()),
	//     'rejected_categories' => json_encode($event->getRejectedCategories()),
	//     'ip_address' => $event->getAnonymizedIpAddress(),
	//     'user_agent' => $event->getUserAgent(),
	//     'created_at' => $event->getTimestamp(),
	// ]);

	// For demo, save to file
	file_put_contents(
		__DIR__ . '/user_consent_log.json',
		json_encode($logData, JSON_PRETTY_PRINT) . "\n---\n",
		FILE_APPEND
	);

	error_log('USER CONSENT: ' . json_encode($logData));
});

$banner->on(ConsentEvent::TYPE_UPDATED, function (ConsentEvent $event) use ($userId) {
	$logData = [
		'event' => 'consent_updated',
		'consent_id' => $event->getConsentId(),
		'user_identifier' => $event->getUserIdentifier(),
		'user_id' => $userId,
		'timestamp' => $event->getTimestamp()->format('Y-m-d H:i:s'),
		'accepted' => $event->getAcceptedCategories(),
		'rejected' => $event->getRejectedCategories(),
		'previous' => $event->getPreviousConsent(),
	];

	file_put_contents(
		__DIR__ . '/user_consent_log.json',
		json_encode($logData, JSON_PRETTY_PRINT) . "\n---\n",
		FILE_APPEND
	);
});

$banner->on(ConsentEvent::TYPE_WITHDRAWN, function (ConsentEvent $event) use ($userId) {
	$logData = [
		'event' => 'consent_withdrawn',
		'consent_id' => $event->getConsentId(),
		'user_identifier' => $event->getUserIdentifier(),
		'user_id' => $userId,
		'timestamp' => date('Y-m-d H:i:s'),
	];

	file_put_contents(
		__DIR__ . '/user_consent_log.json',
		json_encode($logData, JSON_PRETTY_PRINT) . "\n---\n",
		FILE_APPEND
	);
});

// Get consent status
$hasConsent = $banner->hasConsent();
$consent = $banner->getConsent();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>User Tracking - Havax Cookie Banner</title>
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
			background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
			min-height: 100vh;
		}

		.container {
			max-width: 900px;
			margin: 0 auto;
			padding: 40px 20px;
		}

		.hero {
			text-align: center;
			padding: 40px 20px;
			color: white;
		}

		.hero h1 {
			font-size: 2.2rem;
			margin-bottom: 12px;
		}

		.hero p {
			opacity: 0.9;
			max-width: 600px;
			margin: 0 auto;
		}

		.card {
			background: white;
			border-radius: 12px;
			box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
			padding: 28px;
			margin-bottom: 24px;
		}

		.card h2 {
			margin-top: 0;
			color: #1e3a5f;
			border-bottom: 2px solid #e5e7eb;
			padding-bottom: 12px;
		}

		.user-status {
			display: flex;
			align-items: center;
			gap: 16px;
			padding: 20px;
			border-radius: 12px;
			margin-bottom: 20px;
		}

		.user-status.logged-in {
			background: linear-gradient(135deg, #ecfdf5, #d1fae5);
			border: 1px solid #10b981;
		}

		.user-status.logged-out {
			background: linear-gradient(135deg, #fef3c7, #fde68a);
			border: 1px solid #f59e0b;
		}

		.user-avatar {
			width: 60px;
			height: 60px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 24px;
		}

		.user-status.logged-in .user-avatar {
			background: #10b981;
			color: white;
		}

		.user-status.logged-out .user-avatar {
			background: #f59e0b;
			color: white;
		}

		.user-info h3 {
			margin: 0 0 4px 0;
		}

		.user-info p {
			margin: 0;
			color: #64748b;
			font-size: 14px;
		}

		.badge {
			display: inline-block;
			padding: 4px 12px;
			border-radius: 20px;
			font-size: 12px;
			font-weight: 600;
		}

		.badge-green {
			background: #dcfce7;
			color: #166534;
		}

		.badge-yellow {
			background: #fef9c3;
			color: #854d0e;
		}

		.badge-blue {
			background: #dbeafe;
			color: #1e40af;
		}

		.badge-red {
			background: #fee2e2;
			color: #991b1b;
		}

		.btn-group {
			display: flex;
			gap: 10px;
			flex-wrap: wrap;
			margin-top: 20px;
		}

		.btn {
			padding: 10px 20px;
			border: none;
			border-radius: 8px;
			cursor: pointer;
			font-size: 14px;
			font-weight: 500;
			text-decoration: none;
			display: inline-flex;
			align-items: center;
			gap: 8px;
			transition: all 0.2s;
		}

		.btn-primary {
			background: #1e3a5f;
			color: white;
		}

		.btn-primary:hover {
			background: #2d5a87;
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

		.info-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
			gap: 16px;
			margin-top: 20px;
		}

		.info-item {
			background: #f8fafc;
			padding: 16px;
			border-radius: 8px;
		}

		.info-item label {
			display: block;
			font-size: 12px;
			color: #64748b;
			margin-bottom: 4px;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}

		.info-item .value {
			font-weight: 600;
			color: #1e293b;
			word-break: break-all;
		}

		pre {
			background: #1e293b;
			color: #e2e8f0;
			padding: 20px;
			border-radius: 8px;
			overflow-x: auto;
			font-size: 13px;
			line-height: 1.5;
		}

		.code-comment {
			color: #64748b;
		}

		.code-keyword {
			color: #c084fc;
		}

		.code-string {
			color: #86efac;
		}

		.code-variable {
			color: #93c5fd;
		}

		.highlight-box {
			background: #eff6ff;
			border: 1px solid #3b82f6;
			border-radius: 8px;
			padding: 16px;
			margin: 16px 0;
		}

		.highlight-box strong {
			color: #1e40af;
		}
	</style>
</head>

<body>
	<div class="hero">
		<h1>User Tracking Example</h1>
		<p>Associate cookie consent with logged-in users for GDPR compliance and user preference management</p>
	</div>

	<div class="container">
		<!-- User Status Card -->
		<div class="card">
			<h2>User Status</h2>
			<div class="user-status <?= $isLoggedIn ? 'logged-in' : 'logged-out' ?>">
				<div class="user-avatar">
					<?= $isLoggedIn ? '&#128100;' : '&#128683;' ?>
				</div>
				<div class="user-info">
					<?php if ($isLoggedIn): ?>
						<h3>User #<?= htmlspecialchars($userId) ?></h3>
						<p>Consent will be associated with this user account</p>
					<?php else: ?>
						<h3>Guest User</h3>
						<p>Consent will be anonymous (no user association)</p>
					<?php endif; ?>
				</div>
			</div>

			<div class="btn-group">
				<?php if ($isLoggedIn): ?>
					<a href="?" class="btn btn-secondary">Logout (Guest Mode)</a>
					<a href="?user=<?= intval($userId) + 1 ?>" class="btn btn-secondary">Switch to User #<?= intval($userId) + 1 ?></a>
				<?php else: ?>
					<a href="?user=1" class="btn btn-primary">Login as User #1</a>
					<a href="?user=2" class="btn btn-primary">Login as User #2</a>
					<a href="?user=100" class="btn btn-primary">Login as User #100</a>
				<?php endif; ?>
			</div>
		</div>

		<!-- Consent Status Card -->
		<div class="card">
			<h2>Consent Status</h2>

			<?php if ($hasConsent): ?>
				<span class="badge badge-green">Consent Given</span>

				<div class="info-grid">
					<div class="info-item">
						<label>Consent ID</label>
						<div class="value"><?= substr($consent->getConsentId(), 0, 20) ?>...</div>
					</div>
					<div class="info-item">
						<label>User Identifier</label>
						<div class="value">
							<?php if ($consent->getUserIdentifier()): ?>
								<?= substr($consent->getUserIdentifier(), 0, 16) ?>...
							<?php else: ?>
								<span style="color: #94a3b8;">None (Guest)</span>
							<?php endif; ?>
						</div>
					</div>
					<div class="info-item">
						<label>Timestamp</label>
						<div class="value"><?= $consent->getTimestamp()->format('Y-m-d H:i:s') ?></div>
					</div>
					<div class="info-item">
						<label>Accepted Categories</label>
						<div class="value">
							<?php foreach ($consent->getAcceptedCategories() as $cat): ?>
								<span class="badge badge-green"><?= ucfirst($cat) ?></span>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="highlight-box">
					<strong>Note:</strong> When you give consent while logged in, the consent record is linked to your user account.
					This allows you to manage consent from your profile and sync preferences across devices.
				</div>
			<?php else: ?>
				<span class="badge badge-yellow">No Consent Yet</span>
				<p style="color: #64748b; margin-top: 12px;">
					Click the button below to show the consent banner and test the user association.
				</p>
			<?php endif; ?>

			<div class="btn-group">
				<button class="btn btn-primary" onclick="havaxCbInstance.showBanner()">Show Consent Banner</button>
				<button class="btn btn-secondary" onclick="havaxCbInstance.showPreferences()">Open Preferences</button>
				<?php if ($hasConsent): ?>
					<button class="btn btn-danger" onclick="havaxCbInstance.withdrawConsent()">Withdraw Consent</button>
				<?php endif; ?>
			</div>
		</div>

		<!-- Implementation Guide -->
		<div class="card">
			<h2>Implementation</h2>
			<pre><span class="code-comment">// Get your logged-in user (from your auth system)</span>
<span class="code-variable">$userId</span> = <span class="code-variable">Auth</span>::<span class="code-keyword">user</span>()?-><span class="code-keyword">id</span>;

<span class="code-comment">// Create a hashed identifier for privacy</span>
<span class="code-variable">$userIdentifier</span> = <span class="code-variable">$userId</span>
    ? <span class="code-keyword">hash</span>(<span class="code-string">'sha256'</span>, <span class="code-string">'user_'</span> . <span class="code-variable">$userId</span> . <span class="code-string">'_secret_salt'</span>)
    : <span class="code-keyword">null</span>;

<span class="code-comment">// Initialize cookie banner</span>
<span class="code-variable">$banner</span> = <span class="code-keyword">new</span> <span class="code-variable">CookieBanner</span>([...]);

<span class="code-comment">// Associate consent with user</span>
<span class="code-keyword">if</span> (<span class="code-variable">$userIdentifier</span>) {
    <span class="code-variable">$banner</span>-><span class="code-keyword">setUserIdentifier</span>(<span class="code-variable">$userIdentifier</span>);
}

<span class="code-comment">// Now listen for consent events</span>
<span class="code-variable">$banner</span>-><span class="code-keyword">on</span>(<span class="code-variable">ConsentEvent</span>::<span class="code-variable">TYPE_GIVEN</span>, <span class="code-keyword">function</span>(<span class="code-variable">ConsentEvent</span> <span class="code-variable">$event</span>) <span class="code-keyword">use</span> (<span class="code-variable">$userId</span>) {
    <span class="code-comment">// Save to your database with user association</span>
    <span class="code-variable">$db</span>-><span class="code-keyword">table</span>(<span class="code-string">'consent_records'</span>)-><span class="code-keyword">insert</span>([
        <span class="code-string">'user_id'</span>          => <span class="code-variable">$userId</span>,
        <span class="code-string">'consent_id'</span>       => <span class="code-variable">$event</span>-><span class="code-keyword">getConsentId</span>(),
        <span class="code-string">'user_identifier'</span>  => <span class="code-variable">$event</span>-><span class="code-keyword">getUserIdentifier</span>(),
        <span class="code-string">'accepted'</span>         => <span class="code-keyword">json_encode</span>(<span class="code-variable">$event</span>-><span class="code-keyword">getAcceptedCategories</span>()),
        <span class="code-string">'rejected'</span>         => <span class="code-keyword">json_encode</span>(<span class="code-variable">$event</span>-><span class="code-keyword">getRejectedCategories</span>()),
        <span class="code-string">'ip_anonymized'</span>    => <span class="code-variable">$event</span>-><span class="code-keyword">getAnonymizedIpAddress</span>(),
        <span class="code-string">'created_at'</span>       => <span class="code-variable">$event</span>-><span class="code-keyword">getTimestamp</span>(),
    ]);
});</pre>
		</div>

		<!-- Use Cases -->
		<div class="card">
			<h2>Use Cases</h2>
			<div class="info-grid">
				<div class="info-item">
					<label>GDPR Compliance</label>
					<div class="value">Link consent records to user accounts for data subject access requests</div>
				</div>
				<div class="info-item">
					<label>User Profile</label>
					<div class="value">Let users manage their cookie preferences from their account settings</div>
				</div>
				<div class="info-item">
					<label>Cross-Device Sync</label>
					<div class="value">Sync consent preferences when users log in on different devices</div>
				</div>
				<div class="info-item">
					<label>Audit Trail</label>
					<div class="value">Maintain a complete history of consent changes per user</div>
				</div>
			</div>
		</div>
	</div>

	<?= $banner->render() ?>
	<?= $banner->renderJs() ?>

	<script>
		// Show banner if no consent
		document.addEventListener('havax-cb:init', function() {
			if (!havaxCbInstance.hasConsent()) {
				havaxCbInstance.showBanner();
			}
		});

		// Reload page on consent change
		document.addEventListener('havax-cb:consent:given', function() {
			setTimeout(() => location.reload(), 500);
		});

		document.addEventListener('havax-cb:consent:withdrawn', function() {
			setTimeout(() => location.reload(), 500);
		});
	</script>
</body>

</html>