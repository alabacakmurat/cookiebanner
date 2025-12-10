<?php

/**
 * Havax Cookie Banner - Blocking Template
 * Full-screen overlay that blocks site access until cookies are accepted
 */

/** @var array $translations */
/** @var array $categories */
/** @var array $consent */
/** @var string $position */
/** @var string|null $privacyPolicyUrl */
/** @var string|null $cookiePolicyUrl */
/** @var callable $t */
/** @var \Havax\CookieBanner\Config\Configuration $config */
?>
<!-- Havax Cookie Banner - Blocking Mode -->
<div id="havax-cb-cookie-banner"
	class="havax-cb-cookie-banner havax-cb-blocking"
	role="dialog"
	aria-modal="true"
	aria-labelledby="havax-cb-blocking-title"
	aria-describedby="havax-cb-blocking-description"
	data-blocking="true">

	<!-- Full Screen Overlay -->
	<div class="havax-cb-blocking-overlay"></div>

	<!-- Blocking Content -->
	<div class="havax-cb-blocking-container">
		<div class="havax-cb-blocking-card">
			<!-- Header with Icon -->
			<div class="havax-cb-blocking-header">
				<div class="havax-cb-blocking-icon">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="12" cy="12" r="10" />
						<path d="M12 16v-4" />
						<path d="M12 8h.01" />
					</svg>
				</div>
				<h2 id="havax-cb-blocking-title" class="havax-cb-blocking-title">
					<?= htmlspecialchars($t('blocking_title', 'Cookie Consent Required')) ?>
				</h2>
			</div>

			<!-- Main Message -->
			<div class="havax-cb-blocking-body">
				<p id="havax-cb-blocking-description" class="havax-cb-blocking-message">
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
				<div class="havax-cb-blocking-categories">
					<h3 class="havax-cb-blocking-categories-title">
						<?= htmlspecialchars($t('blocking_categories_title', 'We use the following types of cookies:')) ?>
					</h3>

					<div class="havax-cb-category-list">
						<?php foreach ($categories as $key => $category): ?>
							<?php if (!($category['enabled'] ?? true)) continue; ?>
							<div class="havax-cb-category-item">
								<label class="havax-cb-category-label">
									<input type="checkbox"
										name="havax_category_<?= htmlspecialchars($key) ?>"
										class="havax-cb-category-checkbox"
										data-category="<?= htmlspecialchars($key) ?>"
										<?= ($category['required'] ?? false) ? 'checked disabled' : '' ?>
										<?= ($category['default'] ?? false) ? 'checked' : '' ?>>
									<span class="havax-cb-category-toggle">
										<span class="havax-cb-toggle-track"></span>
										<span class="havax-cb-toggle-thumb"></span>
									</span>
									<span class="havax-cb-category-info">
										<span class="havax-cb-category-name">
											<?= htmlspecialchars($t('category_' . $key . '_title', $category['title'] ?? ucfirst($key))) ?>
											<?php if ($category['required'] ?? false): ?>
												<span class="havax-cb-category-badge havax-cb-badge-required">
													<?= htmlspecialchars($t('required', 'Required')) ?>
												</span>
											<?php endif; ?>
										</span>
										<span class="havax-cb-category-desc">
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
					<div class="havax-cb-blocking-links">
						<?php if ($privacyPolicyUrl): ?>
							<a href="<?= htmlspecialchars($privacyPolicyUrl) ?>" target="_blank" rel="noopener" class="havax-cb-policy-link">
								<?= htmlspecialchars($t('privacy_policy', 'Privacy Policy')) ?>
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
									<polyline points="15 3 21 3 21 9" />
									<line x1="10" y1="14" x2="21" y2="3" />
								</svg>
							</a>
						<?php endif; ?>
						<?php if ($cookiePolicyUrl): ?>
							<a href="<?= htmlspecialchars($cookiePolicyUrl) ?>" target="_blank" rel="noopener" class="havax-cb-policy-link">
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
			<div class="havax-cb-blocking-footer">
				<button type="button" class="havax-cb-btn havax-cb-btn-secondary havax-cb-btn-save" data-action="save-preferences">
					<?= htmlspecialchars($t('save_preferences', 'Save Preferences')) ?>
				</button>
				<button type="button" class="havax-cb-btn havax-cb-btn-primary havax-cb-btn-accept" data-action="accept-all">
					<?= htmlspecialchars($t('accept_all', 'Accept All')) ?>
				</button>
			</div>

			<!-- Warning Message -->
			<div class="havax-cb-blocking-warning">
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
<div id="havax-cb-preferences-modal" class="havax-cb-modal havax-cb-blocking-preferences" role="dialog" aria-modal="true" aria-labelledby="havax-cb-prefs-title" hidden>
	<div class="havax-cb-modal-backdrop"></div>
	<div class="havax-cb-modal-container">
		<div class="havax-cb-preferences-panel">
			<div class="havax-cb-preferences-header">
				<h3 id="havax-cb-prefs-title"><?= htmlspecialchars($t('preferences_title', 'Cookie Preferences')) ?></h3>
				<button type="button" class="havax-cb-modal-close" data-action="close-preferences" aria-label="<?= htmlspecialchars($t('close', 'Close')) ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<line x1="18" y1="6" x2="6" y2="18" />
						<line x1="6" y1="6" x2="18" y2="18" />
					</svg>
				</button>
			</div>
			<div class="havax-cb-preferences-body">
				<p class="havax-cb-preferences-description">
					<?= htmlspecialchars($t('preferences_description', 'Manage your cookie preferences below. You can enable or disable different types of cookies.')) ?>
				</p>

				<div class="havax-cb-preferences-categories">
					<?php foreach ($categories as $key => $category): ?>
						<?php if (!($category['enabled'] ?? true)) continue; ?>
						<div class="havax-cb-preference-item">
							<div class="havax-cb-preference-header">
								<div class="havax-cb-preference-info">
									<h4 class="havax-cb-preference-title">
										<?= htmlspecialchars($t('category_' . $key . '_title', $category['title'] ?? ucfirst($key))) ?>
									</h4>
									<?php if ($category['required'] ?? false): ?>
										<span class="havax-cb-badge havax-cb-badge-required"><?= htmlspecialchars($t('always_active', 'Always Active')) ?></span>
									<?php endif; ?>
								</div>
								<label class="havax-cb-switch">
									<input type="checkbox"
										class="havax-cb-preference-toggle"
										data-category="<?= htmlspecialchars($key) ?>"
										<?= ($category['required'] ?? false) ? 'checked disabled' : '' ?>
										<?= ($category['default'] ?? false) ? 'checked' : '' ?>>
									<span class="havax-cb-switch-slider"></span>
								</label>
							</div>
							<p class="havax-cb-preference-description">
								<?= htmlspecialchars($t('category_' . $key . '_description', $category['description'] ?? '')) ?>
							</p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="havax-cb-preferences-footer">
				<button type="button" class="havax-cb-btn havax-cb-btn-secondary" data-action="reject-all">
					<?= htmlspecialchars($t('reject_all', 'Reject All')) ?>
				</button>
				<button type="button" class="havax-cb-btn havax-cb-btn-primary" data-action="save-preferences">
					<?= htmlspecialchars($t('save_preferences', 'Save Preferences')) ?>
				</button>
			</div>
		</div>
	</div>
</div>