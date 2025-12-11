<?php

/**
 * Chronex Cookie Banner - SQLite Storage Example
 *
 * This example demonstrates how to use SQLite database as storage backend
 * with API-based consent management.
 *
 * Features:
 * - Persistent consent storage in SQLite database
 * - API endpoint for JavaScript communication
 * - Admin panel to view stored consents
 * - Statistics and export functionality
 */

// Suppress HTML error output for API requests
if (isset($_GET['api'])) {
	ini_set('display_errors', '0');
	error_reporting(0);
}

require_once __DIR__ . '/../../vendor/autoload.php';

use Chronex\CookieBanner\CookieBanner;
use Chronex\CookieBanner\Storage\SqliteStorage;
use Chronex\CookieBanner\Event\ConsentEvent;

// ============================================================================
// SQLITE STORAGE SETUP
// ============================================================================

// Database file will be created in this directory
$databasePath = __DIR__ . '/consents.sqlite';

// Create SQLite storage instance
$storage = new SqliteStorage(
	$databasePath,
	'cookie_consents',  // Table name
	'your-secret-key-change-in-production'  // Secret key for token generation
);

// ============================================================================
// COOKIE BANNER SETUP
// ============================================================================

$banner = new CookieBanner([
	'template' => 'modern',
	'position' => 'bottom-right',
	'language' => 'tr',
	'privacyPolicyUrl' => '/privacy',
	'cookiePolicyUrl' => '/cookies',
	'inlineAssets' => true,
	'apiUrl' => '?api=1',
	'categories' => [
		'necessary' => [
			'title' => 'Zorunlu',
			'description' => 'Web sitesinin düzgün çalışması için gerekli çerezler.',
			'required' => true,
		],
		'functional' => [
			'title' => 'İşlevsel',
			'description' => 'Tercihlerinizi hatırlayan çerezler.',
			'required' => false,
		],
		'analytics' => [
			'title' => 'Analitik',
			'description' => 'Siteyi nasıl kullandığınızı anlamamıza yardımcı olan çerezler.',
			'required' => false,
		],
		'marketing' => [
			'title' => 'Pazarlama',
			'description' => 'Kişiselleştirilmiş reklamlar için kullanılan çerezler.',
			'required' => false,
		],
	],
]);

// Set the SQLite storage
$banner->setStorage($storage);

// ============================================================================
// EVENT HANDLERS
// ============================================================================

$banner->on(ConsentEvent::TYPE_GIVEN, function (ConsentEvent $event) {
	error_log(sprintf(
		'[CONSENT_GIVEN] ID: %s, Categories: %s',
		$event->getConsentId(),
		implode(', ', $event->getAcceptedCategories())
	));
});

$banner->on(ConsentEvent::TYPE_UPDATED, function (ConsentEvent $event) {
	error_log(sprintf(
		'[CONSENT_UPDATED] ID: %s, Categories: %s',
		$event->getConsentId(),
		implode(', ', $event->getAcceptedCategories())
	));
});

$banner->on(ConsentEvent::TYPE_WITHDRAWN, function (ConsentEvent $event) {
	error_log(sprintf(
		'[CONSENT_WITHDRAWN] Previous ID: %s',
		$event->getConsentId()
	));
});

// ============================================================================
// API HANDLER
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
	header('Content-Type: application/json');

	try {
		$request = json_decode(file_get_contents('php://input'), true) ?? $_POST;
		$response = $banner->handleApiRequest($request);
		echo json_encode($response);
	} catch (\Throwable $e) {
		echo json_encode([
			'success' => false,
			'error' => $e->getMessage(),
			'trace' => $e->getTraceAsString(),
		]);
	}
	exit;
}

// ============================================================================
// ADMIN ACTIONS
// ============================================================================

$adminAction = $_GET['admin'] ?? null;
$adminData = null;

if ($adminAction) {
	switch ($adminAction) {
		case 'list':
			$page = max(1, (int) ($_GET['page'] ?? 1));
			$limit = 20;
			$offset = ($page - 1) * $limit;
			$adminData = [
				'records' => $storage->getAll($limit, $offset),
				'total' => $storage->count(),
				'page' => $page,
				'pages' => ceil($storage->count() / $limit),
			];
			break;

		case 'stats':
			$adminData = $storage->getStatistics();
			break;

		case 'export':
			header('Content-Type: application/json');
			header('Content-Disposition: attachment; filename="consents_export_' . date('Y-m-d') . '.json"');
			echo json_encode($storage->exportAll(), JSON_PRETTY_PRINT);
			exit;

		case 'cleanup':
			$days = (int) ($_GET['days'] ?? 365);
			$deleted = $storage->cleanup($days);
			$adminData = ['deleted' => $deleted];
			break;
	}
}

// ============================================================================
// PAGE OUTPUT
// ============================================================================

$hasConsent = $banner->hasConsent();
$stats = $storage->getStatistics();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>SQLite Storage Example - Chronex Cookie Banner</title>
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
			background: #f1f5f9;
		}

		.header {
			background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
			color: white;
			padding: 40px 20px;
			text-align: center;
		}

		.header h1 {
			margin: 0 0 8px 0;
			font-size: 28px;
		}

		.header p {
			margin: 0;
			opacity: 0.9;
		}

		.container {
			max-width: 1200px;
			margin: 0 auto;
			padding: 24px 20px;
		}

		.grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
			gap: 20px;
			margin-bottom: 24px;
		}

		.card {
			background: white;
			border-radius: 12px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
			padding: 24px;
		}

		.card h2 {
			margin: 0 0 16px 0;
			font-size: 18px;
			color: #1e293b;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		.card h2 .icon {
			width: 24px;
			height: 24px;
			background: #e0f2fe;
			border-radius: 6px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 14px;
		}

		.stat-value {
			font-size: 36px;
			font-weight: 700;
			color: #1e40af;
			line-height: 1;
		}

		.stat-label {
			font-size: 14px;
			color: #64748b;
			margin-top: 4px;
		}

		.status-badge {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 6px 12px;
			border-radius: 20px;
			font-size: 14px;
			font-weight: 500;
		}

		.status-badge.success {
			background: #dcfce7;
			color: #166534;
		}

		.status-badge.warning {
			background: #fef3c7;
			color: #92400e;
		}

		.btn {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 10px 16px;
			border: none;
			border-radius: 8px;
			font-size: 14px;
			font-weight: 500;
			cursor: pointer;
			text-decoration: none;
			transition: all 0.2s;
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
			background: #fee2e2;
			color: #dc2626;
		}

		.btn-danger:hover {
			background: #fecaca;
		}

		.btn-group {
			display: flex;
			gap: 8px;
			flex-wrap: wrap;
		}

		.table-wrapper {
			overflow-x: auto;
		}

		table {
			width: 100%;
			border-collapse: collapse;
			font-size: 14px;
		}

		th,
		td {
			padding: 12px;
			text-align: left;
			border-bottom: 1px solid #e2e8f0;
		}

		th {
			background: #f8fafc;
			font-weight: 600;
			color: #475569;
		}

		tr:hover {
			background: #f8fafc;
		}

		.categories-list {
			display: flex;
			gap: 4px;
			flex-wrap: wrap;
		}

		.category-tag {
			padding: 2px 8px;
			background: #e0f2fe;
			color: #0369a1;
			border-radius: 4px;
			font-size: 12px;
		}

		.category-tag.rejected {
			background: #fee2e2;
			color: #dc2626;
		}

		.pagination {
			display: flex;
			gap: 8px;
			justify-content: center;
			margin-top: 20px;
		}

		.pagination a {
			padding: 8px 12px;
			background: white;
			border: 1px solid #e2e8f0;
			border-radius: 6px;
			color: #475569;
			text-decoration: none;
		}

		.pagination a:hover {
			background: #f8fafc;
		}

		.pagination a.active {
			background: #2563eb;
			color: white;
			border-color: #2563eb;
		}

		.code-block {
			background: #1e293b;
			color: #e2e8f0;
			padding: 16px;
			border-radius: 8px;
			overflow-x: auto;
			font-family: 'Fira Code', monospace;
			font-size: 13px;
			line-height: 1.6;
		}

		.section-title {
			font-size: 12px;
			font-weight: 600;
			color: #64748b;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			margin-bottom: 12px;
		}

		.chart-bar {
			display: flex;
			align-items: center;
			gap: 12px;
			margin-bottom: 8px;
		}

		.chart-label {
			width: 100px;
			font-size: 13px;
			color: #64748b;
		}

		.chart-fill {
			flex: 1;
			height: 24px;
			background: #e2e8f0;
			border-radius: 4px;
			overflow: hidden;
		}

		.chart-fill-inner {
			height: 100%;
			background: linear-gradient(90deg, #3b82f6, #1d4ed8);
			border-radius: 4px;
			transition: width 0.3s;
		}

		.chart-value {
			width: 50px;
			text-align: right;
			font-weight: 600;
			color: #1e293b;
		}

		.info-box {
			background: #eff6ff;
			border: 1px solid #bfdbfe;
			border-radius: 8px;
			padding: 16px;
			margin-bottom: 20px;
		}

		.info-box p {
			margin: 0;
			color: #1e40af;
			font-size: 14px;
		}

		#event-log {
			background: #0f172a;
			color: #94a3b8;
			padding: 12px;
			border-radius: 8px;
			height: 150px;
			overflow-y: auto;
			font-family: monospace;
			font-size: 12px;
		}

		#event-log .event {
			padding: 4px 8px;
			margin-bottom: 4px;
			border-radius: 4px;
		}

		#event-log .event.success {
			background: rgba(34, 197, 94, 0.2);
			color: #4ade80;
		}

		#event-log .event.info {
			background: rgba(59, 130, 246, 0.2);
			color: #60a5fa;
		}

		#event-log .event.warning {
			background: rgba(234, 179, 8, 0.2);
			color: #facc15;
		}
	</style>
</head>

<body>
	<div class="header">
		<h1>SQLite Storage Example</h1>
		<p>API-based consent management with persistent SQLite database storage</p>
	</div>

	<div class="container">
		<!-- Status Cards -->
		<div class="grid">
			<div class="card">
				<h2><span class="icon">📊</span> Total Consents</h2>
				<div class="stat-value"><?= number_format($stats['total']) ?></div>
				<div class="stat-label">Stored in database</div>
			</div>

			<div class="card">
				<h2><span class="icon">🔐</span> Current Status</h2>
				<?php if ($hasConsent): ?>
					<span class="status-badge success">✓ Consent Given</span>
					<p style="margin: 12px 0 0; color: #64748b; font-size: 14px;">
						Accepted: <?= implode(', ', $banner->getAcceptedCategories()) ?>
					</p>
				<?php else: ?>
					<span class="status-badge warning">⚠ No Consent</span>
					<p style="margin: 12px 0 0; color: #64748b; font-size: 14px;">
						Banner will be shown
					</p>
				<?php endif; ?>
			</div>

			<div class="card">
				<h2><span class="icon">💾</span> Database</h2>
				<div style="font-size: 14px; color: #64748b;">
					<div>File: consents.sqlite</div>
					<div>Size: <?= file_exists($databasePath) ? number_format(filesize($databasePath) / 1024, 1) . ' KB' : 'N/A' ?></div>
				</div>
			</div>
		</div>

		<!-- Actions -->
		<div class="card" style="margin-bottom: 24px;">
			<h2><span class="icon">⚡</span> Actions</h2>
			<div class="btn-group">
				<button class="btn btn-primary" onclick="chronexCbInstance.showBanner()">Show Banner</button>
				<button class="btn btn-secondary" onclick="chronexCbInstance.showPreferences()">Preferences</button>
				<button class="btn btn-danger" onclick="chronexCbInstance.withdrawConsent()">Withdraw Consent</button>
				<a href="?admin=list" class="btn btn-secondary">View All Records</a>
				<a href="?admin=stats" class="btn btn-secondary">Statistics</a>
				<a href="?admin=export" class="btn btn-secondary">Export JSON</a>
			</div>
		</div>

		<!-- Event Log -->
		<div class="card" style="margin-bottom: 24px;">
			<h2><span class="icon">📝</span> Live Event Log</h2>
			<div id="event-log"></div>
		</div>

		<?php if ($adminAction === 'list' && $adminData): ?>
			<!-- Records List -->
			<div class="card">
				<h2><span class="icon">📋</span> Consent Records (<?= $adminData['total'] ?> total)</h2>
				<div class="table-wrapper">
					<table>
						<thead>
							<tr>
								<th>ID</th>
								<th>Consent ID</th>
								<th>Method</th>
								<th>Accepted</th>
								<th>Rejected</th>
								<th>Created</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($adminData['records'] as $record): ?>
								<tr>
									<td><?= htmlspecialchars($record->getConsentId()) ?></td>
									<td><code style="font-size: 11px;"><?= substr($record->getConsentId(), 0, 16) ?>...</code></td>
									<td><?= htmlspecialchars($record->getConsentMethod() ?? '-') ?></td>
									<td>
										<div class="categories-list">
											<?php foreach ($record->getAcceptedCategories() as $cat): ?>
												<span class="category-tag"><?= htmlspecialchars($cat) ?></span>
											<?php endforeach; ?>
										</div>
									</td>
									<td>
										<div class="categories-list">
											<?php foreach ($record->getRejectedCategories() as $cat): ?>
												<span class="category-tag rejected"><?= htmlspecialchars($cat) ?></span>
											<?php endforeach; ?>
										</div>
									</td>
									<td><?= $record->getTimestamp()->format('Y-m-d H:i') ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<?php if ($adminData['pages'] > 1): ?>
					<div class="pagination">
						<?php for ($i = 1; $i <= $adminData['pages']; $i++): ?>
							<a href="?admin=list&page=<?= $i ?>" class="<?= $i === $adminData['page'] ? 'active' : '' ?>"><?= $i ?></a>
						<?php endfor; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ($adminAction === 'stats' && $adminData): ?>
			<!-- Statistics -->
			<div class="card">
				<h2><span class="icon">📈</span> Statistics</h2>

				<div class="section-title">By Consent Method</div>
				<?php
				$maxMethod = max($adminData['by_method'] ?: [1]);
				foreach ($adminData['by_method'] as $method => $count):
					$percent = ($count / $maxMethod) * 100;
				?>
					<div class="chart-bar">
						<div class="chart-label"><?= htmlspecialchars($method) ?></div>
						<div class="chart-fill">
							<div class="chart-fill-inner" style="width: <?= $percent ?>%"></div>
						</div>
						<div class="chart-value"><?= $count ?></div>
					</div>
				<?php endforeach; ?>

				<?php if (!empty($adminData['by_date'])): ?>
					<div class="section-title" style="margin-top: 24px;">Last 30 Days</div>
					<?php
					$maxDate = max($adminData['by_date']);
					foreach (array_slice($adminData['by_date'], -10, 10, true) as $date => $count):
						$percent = ($count / $maxDate) * 100;
					?>
						<div class="chart-bar">
							<div class="chart-label"><?= $date ?></div>
							<div class="chart-fill">
								<div class="chart-fill-inner" style="width: <?= $percent ?>%"></div>
							</div>
							<div class="chart-value"><?= $count ?></div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ($adminAction === 'cleanup' && $adminData): ?>
			<div class="card">
				<div class="info-box">
					<p>✓ Cleanup completed. <?= $adminData['deleted'] ?> old records deleted.</p>
				</div>
			</div>
		<?php endif; ?>

		<!-- Code Example -->
		<div class="card">
			<h2><span class="icon">💻</span> Implementation</h2>

			<div class="section-title">1. Create SQLite Storage</div>
			<div class="code-block">
				<pre>use Chronex\CookieBanner\Storage\SqliteStorage;

$storage = new SqliteStorage(
    __DIR__ . '/consents.sqlite',  // Database path
    'cookie_consents',              // Table name
    'your-secret-key'               // Secret for tokens
);</pre>
			</div>

			<div class="section-title" style="margin-top: 20px;">2. Configure Banner with Storage</div>
			<div class="code-block">
				<pre>$banner = new CookieBanner([
    'template' => 'modern',
    'apiUrl' => '?api=1',
]);

// Set the SQLite storage
$banner->setStorage($storage);</pre>
			</div>

			<div class="section-title" style="margin-top: 20px;">3. Handle API Requests</div>
			<div class="code-block">
				<pre>if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
    header('Content-Type: application/json');
    $request = json_decode(file_get_contents('php://input'), true);
    echo json_encode($banner->handleApiRequest($request));
    exit;
}</pre>
			</div>

			<div class="section-title" style="margin-top: 20px;">4. Query Stored Consents</div>
			<div class="code-block">
				<pre>// Get all records with pagination
$records = $storage->getAll($limit, $offset);

// Find by user identifier
$consent = $storage->findByUserIdentifier($userId);

// Get statistics
$stats = $storage->getStatistics();

// Export all data
$export = $storage->exportAll();

// Cleanup old records (older than 365 days)
$deleted = $storage->cleanup(365);</pre>
			</div>
		</div>
	</div>

	<?= $banner->render() ?>
	<?= $banner->renderJs() ?>

	<script>
		const eventLog = document.getElementById('event-log');

		function logEvent(type, message, className = 'info') {
			const time = new Date().toLocaleTimeString();
			const div = document.createElement('div');
			div.className = 'event ' + className;
			div.innerHTML = `<strong>[${time}]</strong> ${type}: ${message}`;
			eventLog.insertBefore(div, eventLog.firstChild);
		}

		document.addEventListener('chronex-cb:init', function(e) {
			logEvent('INIT', 'Banner initialized', 'info');
		});

		document.addEventListener('chronex-cb:consent:given', function(e) {
			logEvent('CONSENT', 'Given: ' + e.detail.acceptedCategories.join(', '), 'success');
			setTimeout(() => location.reload(), 500);
		});

		document.addEventListener('chronex-cb:consent:updated', function(e) {
			logEvent('CONSENT', 'Updated: ' + e.detail.acceptedCategories.join(', '), 'success');
			setTimeout(() => location.reload(), 500);
		});

		document.addEventListener('chronex-cb:consent:withdrawn', function(e) {
			logEvent('CONSENT', 'Withdrawn', 'warning');
			setTimeout(() => location.reload(), 500);
		});

		document.addEventListener('chronex-cb:api:success', function(e) {
			logEvent('API', 'Success: ' + e.detail.action, 'success');
		});

		document.addEventListener('chronex-cb:api:error', function(e) {
			logEvent('API', 'Error: ' + e.detail.error, 'warning');
		});
	</script>
</body>

</html>
