<?php

/**
 * Classic Template - Full-width banner
 *
 * @var \Chronex\CookieBanner\Config\Configuration $config
 * @var array $translations
 * @var array $consent
 * @var array $categories
 * @var string $position
 * @var string|null $privacyPolicyUrl
 * @var string|null $cookiePolicyUrl
 * @var bool $showPreferencesButton
 * @var bool $shouldShowBanner
 * @var callable $t
 */
?>
<div id="chronex-cb-cookie-banner"
	class="chronex-cb-cookie-banner chronex-cb-classic chronex-cb-position-<?= htmlspecialchars($position) ?><?= $shouldShowBanner ? ' chronex-cb-visible' : '' ?>"
	role="dialog"
	aria-modal="true"
	aria-labelledby="chronex-cb-cookie-title"
	aria-describedby="chronex-cb-cookie-description"
	aria-hidden="<?= $shouldShowBanner ? 'false' : 'true' ?>"
	data-template="classic">

	<div class="chronex-cb-cookie-container">
		<div class="chronex-cb-cookie-content">
			<div class="chronex-cb-cookie-text">
				<h2 id="chronex-cb-cookie-title" class="chronex-cb-cookie-title">
					<?= htmlspecialchars($t('title', 'Cookie Settings')) ?>
				</h2>
				<p id="chronex-cb-cookie-description" class="chronex-cb-cookie-description">
					<?= htmlspecialchars($t('description', 'We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.')) ?>
				</p>

				<?php if ($privacyPolicyUrl || $cookiePolicyUrl): ?>
					<p class="chronex-cb-cookie-links">
						<?php if ($privacyPolicyUrl): ?>
							<a href="<?= htmlspecialchars($privacyPolicyUrl) ?>" target="_blank" rel="noopener">
								<?= htmlspecialchars($t('privacy_policy', 'Privacy Policy')) ?>
							</a>
						<?php endif; ?>
						<?php if ($privacyPolicyUrl && $cookiePolicyUrl): ?>
							<span class="chronex-cb-separator">|</span>
						<?php endif; ?>
						<?php if ($cookiePolicyUrl): ?>
							<a href="<?= htmlspecialchars($cookiePolicyUrl) ?>" target="_blank" rel="noopener">
								<?= htmlspecialchars($t('cookie_policy', 'Cookie Policy')) ?>
							</a>
						<?php endif; ?>
					</p>
				<?php endif; ?>
			</div>

			<div class="chronex-cb-cookie-actions">
				<button type="button" class="chronex-cb-btn chronex-cb-btn-reject" data-action="reject-all">
					<?= htmlspecialchars($t('reject_all', 'Reject All')) ?>
				</button>
				<?php if ($showPreferencesButton): ?>
					<button type="button" class="chronex-cb-btn chronex-cb-btn-preferences" data-action="show-preferences">
						<?= htmlspecialchars($t('preferences', 'Preferences')) ?>
					</button>
				<?php endif; ?>
				<button type="button" class="chronex-cb-btn chronex-cb-btn-accept" data-action="accept-all">
					<?= htmlspecialchars($t('accept_all', 'Accept All')) ?>
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Preferences Modal (outside banner for proper positioning) -->
<div id="chronex-cb-preferences-modal" class="chronex-cb-preferences-modal" aria-hidden="true">
	<div class="chronex-cb-preferences-overlay" data-action="close-preferences"></div>
	<div class="chronex-cb-preferences-content" role="dialog" aria-labelledby="chronex-cb-preferences-title">
		<div class="chronex-cb-preferences-header">
			<h3 id="chronex-cb-preferences-title"><?= htmlspecialchars($t('preferences_title', 'Cookie Preferences')) ?></h3>
			<button type="button" class="chronex-cb-preferences-close" data-action="close-preferences" aria-label="<?= htmlspecialchars($t('close', 'Close')) ?>">
				<svg viewBox="0 0 24 24" width="24" height="24">
					<path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
				</svg>
			</button>
		</div>

		<div class="chronex-cb-preferences-body">
			<p class="chronex-cb-preferences-description">
				<?= htmlspecialchars($t('preferences_description', 'Manage your cookie preferences below. You can enable or disable different types of cookies.')) ?>
			</p>

			<div class="chronex-cb-categories">
				<?php foreach ($categories as $key => $category): ?>
					<?php if (!($category['enabled'] ?? true)) continue; ?>
					<div class="chronex-cb-category" data-category="<?= htmlspecialchars($key) ?>">
						<div class="chronex-cb-category-header">
							<div class="chronex-cb-category-info">
								<h4 class="chronex-cb-category-title">
									<?= htmlspecialchars($t("category_{$key}_title", $category['title'] ?? ucfirst($key))) ?>
								</h4>
								<?php if ($category['required'] ?? false): ?>
									<span class="chronex-cb-category-required"><?= htmlspecialchars($t('always_active', 'Always Active')) ?></span>
								<?php endif; ?>
							</div>
							<label class="chronex-cb-toggle">
								<input type="checkbox"
									name="chronex_category_<?= htmlspecialchars($key) ?>"
									data-category="<?= htmlspecialchars($key) ?>"
									<?= ($category['required'] ?? false) ? 'checked disabled' : '' ?>
									<?= ($category['default'] ?? false) ? 'checked' : '' ?>>
								<span class="chronex-cb-toggle-slider"></span>
							</label>
						</div>
						<p class="chronex-cb-category-description">
							<?= htmlspecialchars($t("category_{$key}_description", $category['description'] ?? '')) ?>
						</p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="chronex-cb-preferences-footer">
			<button type="button" class="chronex-cb-btn chronex-cb-btn-reject" data-action="reject-all">
				<?= htmlspecialchars($t('reject_all', 'Reject All')) ?>
			</button>
			<button type="button" class="chronex-cb-btn chronex-cb-btn-save" data-action="save-preferences">
				<?= htmlspecialchars($t('save_preferences', 'Save Preferences')) ?>
			</button>
			<button type="button" class="chronex-cb-btn chronex-cb-btn-accept" data-action="accept-all">
				<?= htmlspecialchars($t('accept_all', 'Accept All')) ?>
			</button>
		</div>
	</div>
</div>