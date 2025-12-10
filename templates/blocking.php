<?php

/**
 * Chronex Cookie Banner - Blocking Template
 * Full-screen overlay that blocks site access until cookies are accepted
 */

/** @var array $translations */
/** @var array $categories */
/** @var array $consent */
/** @var string $position */
/** @var string|null $privacyPolicyUrl */
/** @var string|null $cookiePolicyUrl */
/** @var callable $t */
/** @var \Chronex\CookieBanner\Config\Configuration $config */
?>
<!-- Chronex Cookie Banner - Blocking Mode -->
<div id="chronex-cb-cookie-banner"
	class="chronex-cb-cookie-banner chronex-cb-blocking"
	role="dialog"
	aria-modal="true"
	aria-labelledby="chronex-cb-blocking-title"
	aria-describedby="chronex-cb-blocking-description"
	data-blocking="true">

	<!-- Full Screen Overlay -->
	<div class="chronex-cb-blocking-overlay"></div>

	<!-- Blocking Content -->
	<div class="chronex-cb-blocking-container">
		<div class="chronex-cb-blocking-card">
			<!-- Header with Icon -->
			<div class="chronex-cb-blocking-header">
				<div class="chronex-cb-blocking-icon">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="12" cy="12" r="10" />
						<path d="M12 16v-4" />
						<path d="M12 8h.01" />
					</svg>
				</div>
				<h2 id="chronex-cb-blocking-title" class="chronex-cb-blocking-title">
					<?= htmlspecialchars($t('blocking_title', 'Cookie Consent Required')) ?>
				</h2>
			</div>

			<!-- Main Message -->
			<div class="chronex-cb-blocking-body">
				<p id="chronex-cb-blocking-description" class="chronex-cb-blocking-message">
					<?php
					$blockingMessage = $config->getBlockingMessage();
					if ($blockingMessage) {
						echo htmlspecialchars($blockingMessage);
					} else {
						echo htmlspecialchars($t('blocking_message', 'To access this website, you must accept our cookie policy. We use cookies to ensure the basic functionality of the site and to enhance your experience.'));
					}
					?>
				</p>

				<!-- Cookie Categories -->
				<div class="chronex-cb-blocking-categories">
					<h3 class="chronex-cb-blocking-categories-title">
						<?= htmlspecialchars($t('blocking_categories_title', 'We use the following types of cookies:')) ?>
					</h3>

					<div class="chronex-cb-category-list">
						<?php foreach ($categories as $key => $category): ?>
							<?php if (!($category['enabled'] ?? true)) continue; ?>
							<div class="chronex-cb-category-item">
								<label class="chronex-cb-category-label">
									<input type="checkbox"
										name="chronex_category_<?= htmlspecialchars($key) ?>"
										class="chronex-cb-category-checkbox"
										data-category="<?= htmlspecialchars($key) ?>"
										<?= ($category['required'] ?? false) ? 'checked disabled' : '' ?>
										<?= ($category['default'] ?? false) ? 'checked' : '' ?>>
									<span class="chronex-cb-category-toggle">
										<span class="chronex-cb-toggle-track"></span>
										<span class="chronex-cb-toggle-thumb"></span>
									</span>
									<span class="chronex-cb-category-info">
										<span class="chronex-cb-category-name">
											<?= htmlspecialchars($t('category_' . $key . '_title', $category['title'] ?? ucfirst($key))) ?>
											<?php if ($category['required'] ?? false): ?>
												<span class="chronex-cb-category-badge chronex-cb-badge-required">
													<?= htmlspecialchars($t('required', 'Required')) ?>
												</span>
											<?php endif; ?>
										</span>
										<span class="chronex-cb-category-desc">
											<?= htmlspecialchars($t('category_' . $key . '_description', $category['description'] ?? '')) ?>
										</span>
									</span>
								</label>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Policy Links -->
				<?php if ($privacyPolicyUrl || $cookiePolicyUrl): ?>
					<div class="chronex-cb-blocking-links">
						<?php if ($privacyPolicyUrl): ?>
							<a href="<?= htmlspecialchars($privacyPolicyUrl) ?>" target="_blank" rel="noopener" class="chronex-cb-policy-link">
								<?= htmlspecialchars($t('privacy_policy', 'Privacy Policy')) ?>
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
									<polyline points="15 3 21 3 21 9" />
									<line x1="10" y1="14" x2="21" y2="3" />
								</svg>
							</a>
						<?php endif; ?>
						<?php if ($cookiePolicyUrl): ?>
							<a href="<?= htmlspecialchars($cookiePolicyUrl) ?>" target="_blank" rel="noopener" class="chronex-cb-policy-link">
								<?= htmlspecialchars($t('cookie_policy', 'Cookie Policy')) ?>
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
									<polyline points="15 3 21 3 21 9" />
									<line x1="10" y1="14" x2="21" y2="3" />
								</svg>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Actions -->
			<div class="chronex-cb-blocking-footer">
				<button type="button" class="chronex-cb-btn chronex-cb-btn-secondary chronex-cb-btn-save" data-action="save-preferences">
					<?= htmlspecialchars($t('save_preferences', 'Save Preferences')) ?>
				</button>
				<button type="button" class="chronex-cb-btn chronex-cb-btn-primary chronex-cb-btn-accept" data-action="accept-all">
					<?= htmlspecialchars($t('accept_all', 'Accept All')) ?>
				</button>
			</div>

			<!-- Warning Message -->
			<div class="chronex-cb-blocking-warning">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
					<line x1="12" y1="9" x2="12" y2="13" />
					<line x1="12" y1="17" x2="12.01" y2="17" />
				</svg>
				<span><?= htmlspecialchars($t('blocking_warning', 'You cannot use this website without accepting at least the required cookies.')) ?></span>
			</div>
		</div>
	</div>
</div>

<!-- Preferences Modal for Blocking Mode -->
<div id="chronex-cb-preferences-modal" class="chronex-cb-modal chronex-cb-blocking-preferences" role="dialog" aria-modal="true" aria-labelledby="chronex-cb-prefs-title" hidden>
	<div class="chronex-cb-modal-backdrop"></div>
	<div class="chronex-cb-modal-container">
		<div class="chronex-cb-preferences-panel">
			<div class="chronex-cb-preferences-header">
				<h3 id="chronex-cb-prefs-title"><?= htmlspecialchars($t('preferences_title', 'Cookie Preferences')) ?></h3>
				<button type="button" class="chronex-cb-modal-close" data-action="close-preferences" aria-label="<?= htmlspecialchars($t('close', 'Close')) ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<line x1="18" y1="6" x2="6" y2="18" />
						<line x1="6" y1="6" x2="18" y2="18" />
					</svg>
				</button>
			</div>
			<div class="chronex-cb-preferences-body">
				<p class="chronex-cb-preferences-description">
					<?= htmlspecialchars($t('preferences_description', 'Manage your cookie preferences below. You can enable or disable different types of cookies.')) ?>
				</p>

				<div class="chronex-cb-preferences-categories">
					<?php foreach ($categories as $key => $category): ?>
						<?php if (!($category['enabled'] ?? true)) continue; ?>
						<div class="chronex-cb-preference-item">
							<div class="chronex-cb-preference-header">
								<div class="chronex-cb-preference-info">
									<h4 class="chronex-cb-preference-title">
										<?= htmlspecialchars($t('category_' . $key . '_title', $category['title'] ?? ucfirst($key))) ?>
									</h4>
									<?php if ($category['required'] ?? false): ?>
										<span class="chronex-cb-badge chronex-cb-badge-required"><?= htmlspecialchars($t('always_active', 'Always Active')) ?></span>
									<?php endif; ?>
								</div>
								<label class="chronex-cb-switch">
									<input type="checkbox"
										class="chronex-cb-preference-toggle"
										data-category="<?= htmlspecialchars($key) ?>"
										<?= ($category['required'] ?? false) ? 'checked disabled' : '' ?>
										<?= ($category['default'] ?? false) ? 'checked' : '' ?>>
									<span class="chronex-cb-switch-slider"></span>
								</label>
							</div>
							<p class="chronex-cb-preference-description">
								<?= htmlspecialchars($t('category_' . $key . '_description', $category['description'] ?? '')) ?>
							</p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="chronex-cb-preferences-footer">
				<button type="button" class="chronex-cb-btn chronex-cb-btn-secondary" data-action="reject-all">
					<?= htmlspecialchars($t('reject_all', 'Reject All')) ?>
				</button>
				<button type="button" class="chronex-cb-btn chronex-cb-btn-primary" data-action="save-preferences">
					<?= htmlspecialchars($t('save_preferences', 'Save Preferences')) ?>
				</button>
			</div>
		</div>
	</div>
</div>