<?php
/**
 * VKM Cookie Banner - Blocking Template
 * Full-screen overlay that blocks site access until cookies are accepted
 */

/** @var array $translations */
/** @var array $categories */
/** @var array $consent */
/** @var string $position */
/** @var string|null $privacyPolicyUrl */
/** @var string|null $cookiePolicyUrl */
/** @var callable $t */
/** @var \VkmToolkit\CookieBanner\Config\Configuration $config */
?>
<!-- VKM Cookie Banner - Blocking Mode -->
<div id="vkm-cookie-banner"
     class="vkm-cookie-banner vkm-blocking"
     role="dialog"
     aria-modal="true"
     aria-labelledby="vkm-blocking-title"
     aria-describedby="vkm-blocking-description"
     data-blocking="true">

    <!-- Full Screen Overlay -->
    <div class="vkm-blocking-overlay"></div>

    <!-- Blocking Content -->
    <div class="vkm-blocking-container">
        <div class="vkm-blocking-card">
            <!-- Header with Icon -->
            <div class="vkm-blocking-header">
                <div class="vkm-blocking-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 16v-4"/>
                        <path d="M12 8h.01"/>
                    </svg>
                </div>
                <h2 id="vkm-blocking-title" class="vkm-blocking-title">
                    <?= htmlspecialchars($t('blocking_title', 'Cookie Consent Required')) ?>
                </h2>
            </div>

            <!-- Main Message -->
            <div class="vkm-blocking-body">
                <p id="vkm-blocking-description" class="vkm-blocking-message">
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
                <div class="vkm-blocking-categories">
                    <h3 class="vkm-blocking-categories-title">
                        <?= htmlspecialchars($t('blocking_categories_title', 'We use the following types of cookies:')) ?>
                    </h3>

                    <div class="vkm-category-list">
                        <?php foreach ($categories as $key => $category): ?>
                            <?php if (!($category['enabled'] ?? true)) continue; ?>
                            <div class="vkm-category-item">
                                <label class="vkm-category-label">
                                    <input type="checkbox"
                                           name="vkm_category_<?= htmlspecialchars($key) ?>"
                                           class="vkm-category-checkbox"
                                           data-category="<?= htmlspecialchars($key) ?>"
                                           <?= ($category['required'] ?? false) ? 'checked disabled' : '' ?>
                                           <?= ($category['default'] ?? false) ? 'checked' : '' ?>>
                                    <span class="vkm-category-toggle">
                                        <span class="vkm-toggle-track"></span>
                                        <span class="vkm-toggle-thumb"></span>
                                    </span>
                                    <span class="vkm-category-info">
                                        <span class="vkm-category-name">
                                            <?= htmlspecialchars($t('category_' . $key . '_title', $category['title'] ?? ucfirst($key))) ?>
                                            <?php if ($category['required'] ?? false): ?>
                                                <span class="vkm-category-badge vkm-badge-required">
                                                    <?= htmlspecialchars($t('required', 'Required')) ?>
                                                </span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="vkm-category-desc">
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
                <div class="vkm-blocking-links">
                    <?php if ($privacyPolicyUrl): ?>
                        <a href="<?= htmlspecialchars($privacyPolicyUrl) ?>" target="_blank" rel="noopener" class="vkm-policy-link">
                            <?= htmlspecialchars($t('privacy_policy', 'Privacy Policy')) ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                    <?php if ($cookiePolicyUrl): ?>
                        <a href="<?= htmlspecialchars($cookiePolicyUrl) ?>" target="_blank" rel="noopener" class="vkm-policy-link">
                            <?= htmlspecialchars($t('cookie_policy', 'Cookie Policy')) ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <div class="vkm-blocking-footer">
                <button type="button" class="vkm-btn vkm-btn-secondary vkm-btn-save" data-action="save-preferences">
                    <?= htmlspecialchars($t('save_preferences', 'Save Preferences')) ?>
                </button>
                <button type="button" class="vkm-btn vkm-btn-primary vkm-btn-accept" data-action="accept-all">
                    <?= htmlspecialchars($t('accept_all', 'Accept All')) ?>
                </button>
            </div>

            <!-- Warning Message -->
            <div class="vkm-blocking-warning">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span><?= htmlspecialchars($t('blocking_warning', 'You cannot use this website without accepting at least the required cookies.')) ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Preferences Modal for Blocking Mode -->
<div id="vkm-preferences-modal" class="vkm-modal vkm-blocking-preferences" role="dialog" aria-modal="true" aria-labelledby="vkm-prefs-title" hidden>
    <div class="vkm-modal-backdrop"></div>
    <div class="vkm-modal-container">
        <div class="vkm-preferences-panel">
            <div class="vkm-preferences-header">
                <h3 id="vkm-prefs-title"><?= htmlspecialchars($t('preferences_title', 'Cookie Preferences')) ?></h3>
                <button type="button" class="vkm-modal-close" data-action="close-preferences" aria-label="<?= htmlspecialchars($t('close', 'Close')) ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="vkm-preferences-body">
                <p class="vkm-preferences-description">
                    <?= htmlspecialchars($t('preferences_description', 'Manage your cookie preferences below. You can enable or disable different types of cookies.')) ?>
                </p>

                <div class="vkm-preferences-categories">
                    <?php foreach ($categories as $key => $category): ?>
                        <?php if (!($category['enabled'] ?? true)) continue; ?>
                        <div class="vkm-preference-item">
                            <div class="vkm-preference-header">
                                <div class="vkm-preference-info">
                                    <h4 class="vkm-preference-title">
                                        <?= htmlspecialchars($t('category_' . $key . '_title', $category['title'] ?? ucfirst($key))) ?>
                                    </h4>
                                    <?php if ($category['required'] ?? false): ?>
                                        <span class="vkm-badge vkm-badge-required"><?= htmlspecialchars($t('always_active', 'Always Active')) ?></span>
                                    <?php endif; ?>
                                </div>
                                <label class="vkm-switch">
                                    <input type="checkbox"
                                           class="vkm-preference-toggle"
                                           data-category="<?= htmlspecialchars($key) ?>"
                                           <?= ($category['required'] ?? false) ? 'checked disabled' : '' ?>
                                           <?= ($category['default'] ?? false) ? 'checked' : '' ?>>
                                    <span class="vkm-switch-slider"></span>
                                </label>
                            </div>
                            <p class="vkm-preference-description">
                                <?= htmlspecialchars($t('category_' . $key . '_description', $category['description'] ?? '')) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="vkm-preferences-footer">
                <button type="button" class="vkm-btn vkm-btn-secondary" data-action="reject-all">
                    <?= htmlspecialchars($t('reject_all', 'Reject All')) ?>
                </button>
                <button type="button" class="vkm-btn vkm-btn-primary" data-action="save-preferences">
                    <?= htmlspecialchars($t('save_preferences', 'Save Preferences')) ?>
                </button>
            </div>
        </div>
    </div>
</div>
