<?php
/**
 * Floating Template - Floating button that expands to show cookie options
 *
 * @var \VkmToolkit\CookieBanner\Config\Configuration $config
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
<div id="vkm-cookie-banner"
     class="vkm-cookie-banner vkm-floating vkm-position-<?= htmlspecialchars($position) ?>"
     role="dialog"
     aria-modal="true"
     aria-labelledby="vkm-cookie-title"
     data-template="floating">

    <!-- Floating Button (always visible after consent) -->
    <button type="button"
            id="vkm-floating-button"
            class="vkm-floating-button"
            data-action="toggle-panel"
            aria-label="<?= htmlspecialchars($t('cookie_settings', 'Cookie Settings')) ?>">
        <svg viewBox="0 0 24 24" width="24" height="24" class="vkm-icon-cookie">
            <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
        </svg>
        <svg viewBox="0 0 24 24" width="24" height="24" class="vkm-icon-close">
            <path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
        </svg>
    </button>

    <!-- Expandable Panel -->
    <div id="vkm-floating-panel" class="vkm-floating-panel">
        <div class="vkm-panel-header">
            <h3 id="vkm-cookie-title" class="vkm-panel-title">
                <?= htmlspecialchars($t('title', 'Cookie Consent')) ?>
            </h3>
        </div>

        <div class="vkm-panel-body">
            <p class="vkm-panel-description">
                <?= htmlspecialchars($t('description', 'We use cookies to improve your experience.')) ?>
            </p>

            <div class="vkm-categories-list">
                <?php foreach ($categories as $key => $category): ?>
                <?php if (!($category['enabled'] ?? true)) continue; ?>
                <div class="vkm-category-item" data-category="<?= htmlspecialchars($key) ?>">
                    <div class="vkm-category-toggle">
                        <label class="vkm-pill-toggle">
                            <input type="checkbox"
                                   name="vkm_category_<?= htmlspecialchars($key) ?>"
                                   data-category="<?= htmlspecialchars($key) ?>"
                                   <?= ($category['required'] ?? false) ? 'checked disabled' : '' ?>
                                   <?= ($category['default'] ?? false) ? 'checked' : '' ?>>
                            <span class="vkm-pill-label">
                                <?= htmlspecialchars($t("category_{$key}_title", $category['title'] ?? ucfirst($key))) ?>
                                <?php if ($category['required'] ?? false): ?>
                                <span class="vkm-required-dot"></span>
                                <?php endif; ?>
                            </span>
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($privacyPolicyUrl): ?>
            <a href="<?= htmlspecialchars($privacyPolicyUrl) ?>" target="_blank" rel="noopener" class="vkm-panel-link">
                <?= htmlspecialchars($t('learn_more', 'Learn more')) ?>
                <svg viewBox="0 0 24 24" width="14" height="14"><path fill="currentColor" d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
            </a>
            <?php endif; ?>
        </div>

        <div class="vkm-panel-footer">
            <button type="button" class="vkm-btn vkm-btn-outline" data-action="reject-all">
                <?= htmlspecialchars($t('reject', 'Reject')) ?>
            </button>
            <button type="button" class="vkm-btn vkm-btn-outline" data-action="save-preferences">
                <?= htmlspecialchars($t('save', 'Save')) ?>
            </button>
            <button type="button" class="vkm-btn vkm-btn-filled" data-action="accept-all">
                <?= htmlspecialchars($t('accept', 'Accept')) ?>
            </button>
        </div>
    </div>
</div>
