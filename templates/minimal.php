<?php

/**
 * Minimal Template - Small popup with essential options
 *
 * @var \Havax\CookieBanner\Config\Configuration $config
 * @var array $translations
 * @var array $consent
 * @var array $categories
 * @var string $position
 * @var string|null $privacyPolicyUrl
 * @var string|null $cookiePolicyUrl
 * @var bool $showPreferencesButton
 * @var callable $t
 */
?>
<div id="havax-cb-cookie-banner"
	class="havax-cb-cookie-banner havax-cb-minimal havax-cb-position-<?= htmlspecialchars($position) ?>"
	role="dialog"
	aria-modal="true"
	aria-labelledby="havax-cb-cookie-title"
	data-template="minimal">

	<div class="havax-cb-cookie-popup">
		<button type="button" class="havax-cb-popup-close" data-action="close-banner" aria-label="<?= htmlspecialchars($t('close', 'Close')) ?>">
			<svg viewBox="0 0 24 24" width="16" height="16">
				<path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
			</svg>
		</button>

		<p id="havax-cb-cookie-title" class="havax-cb-popup-text">
			<?= htmlspecialchars($t('short_description', 'This site uses cookies.')) ?>
			<?php if ($privacyPolicyUrl): ?>
				<a href="<?= htmlspecialchars($privacyPolicyUrl) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($t('learn_more', 'Learn more')) ?></a>
			<?php endif; ?>
		</p>

		<div class="havax-cb-popup-actions">
			<?php if ($showPreferencesButton): ?>
				<button type="button" class="havax-cb-btn havax-cb-btn-link" data-action="show-preferences">
					<?= htmlspecialchars($t('settings', 'Settings')) ?>
				</button>
			<?php endif; ?>
			<button type="button" class="havax-cb-btn havax-cb-btn-small havax-cb-btn-reject" data-action="reject-all">
				<?= htmlspecialchars($t('decline', 'Decline')) ?>
			</button>
			<button type="button" class="havax-cb-btn havax-cb-btn-small havax-cb-btn-accept" data-action="accept-all">
				<?= htmlspecialchars($t('accept', 'Accept')) ?>
			</button>
		</div>
	</div>
</div>

<!-- Preferences Modal (outside banner for proper positioning) -->
<div id="havax-cb-preferences-modal" class="havax-cb-preferences-modal" aria-hidden="true">
	<div class="havax-cb-preferences-overlay" data-action="close-preferences"></div>
	<div class="havax-cb-preferences-content havax-cb-preferences-compact" role="dialog" aria-labelledby="havax-cb-preferences-title">
		<div class="havax-cb-preferences-header">
			<h3 id="havax-cb-preferences-title"><?= htmlspecialchars($t('cookie_settings', 'Cookie Settings')) ?></h3>
			<button type="button" class="havax-cb-preferences-close" data-action="close-preferences" aria-label="<?= htmlspecialchars($t('close', 'Close')) ?>">
				<svg viewBox="0 0 24 24" width="20" height="20">
					<path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
				</svg>
			</button>
		</div>

		<div class="havax-cb-preferences-body">
			<div class="havax-cb-categories-compact">
				<?php foreach ($categories as $key => $category): ?>
					<?php if (!($category['enabled'] ?? true)) continue; ?>
					<label class="havax-cb-category-row" data-category="<?= htmlspecialchars($key) ?>">
						<span class="havax-cb-category-name">
							<?= htmlspecialchars($t("category_{$key}_title", $category['title'] ?? ucfirst($key))) ?>
							<?php if ($category['required'] ?? false): ?>
								<small>(<?= htmlspecialchars($t('required', 'Required')) ?>)</small>
							<?php endif; ?>
						</span>
						<input type="checkbox"
							class="havax-cb-checkbox"
							name="havax_category_<?= htmlspecialchars($key) ?>"
							data-category="<?= htmlspecialchars($key) ?>"
							<?= ($category['required'] ?? false) ? 'checked disabled' : '' ?>
							<?= ($category['default'] ?? false) ? 'checked' : '' ?>>
					</label>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="havax-cb-preferences-footer">
			<button type="button" class="havax-cb-btn havax-cb-btn-small havax-cb-btn-save" data-action="save-preferences">
				<?= htmlspecialchars($t('save', 'Save')) ?>
			</button>
		</div>
	</div>
</div>