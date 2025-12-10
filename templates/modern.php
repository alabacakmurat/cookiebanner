<?php
/**
 * Modern Template - Card-style banner with shadow
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
     class="vkm-cookie-banner vkm-modern vkm-position-<?= htmlspecialchars($position) ?>"
     role="dialog"
     aria-modal="true"
     aria-labelledby="vkm-cookie-title"
     aria-describedby="vkm-cookie-description"
     data-template="modern">

    <div class="vkm-cookie-card">
        <div class="vkm-cookie-icon">
            <svg viewBox="0 0 24 24" width="48" height="48">
                <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
            </svg>
        </div>

        <div class="vkm-cookie-content">
            <h2 id="vkm-cookie-title" class="vkm-cookie-title">
                <?= htmlspecialchars($t('title', 'We value your privacy')) ?>
            </h2>
            <p id="vkm-cookie-description" class="vkm-cookie-description">
                <?= htmlspecialchars($t('description', 'We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic.')) ?>
            </p>

            <?php if ($privacyPolicyUrl || $cookiePolicyUrl): ?>
            <p class="vkm-cookie-links">
                <?php if ($privacyPolicyUrl): ?>
                <a href="<?= htmlspecialchars($privacyPolicyUrl) ?>" target="_blank" rel="noopener">
                    <?= htmlspecialchars($t('privacy_policy', 'Privacy Policy')) ?>
                </a>
                <?php endif; ?>
                <?php if ($cookiePolicyUrl): ?>
                <a href="<?= htmlspecialchars($cookiePolicyUrl) ?>" target="_blank" rel="noopener">
                    <?= htmlspecialchars($t('cookie_policy', 'Cookie Policy')) ?>
                </a>
                <?php endif; ?>
            </p>
            <?php endif; ?>
        </div>

        <div class="vkm-cookie-actions">
            <?php if ($showPreferencesButton): ?>
            <button type="button" class="vkm-btn vkm-btn-preferences" data-action="show-preferences">
                <?= htmlspecialchars($t('customize', 'Customize')) ?>
            </button>
            <?php endif; ?>
            <button type="button" class="vkm-btn vkm-btn-reject" data-action="reject-all">
                <?= htmlspecialchars($t('reject_all', 'Reject')) ?>
            </button>
            <button type="button" class="vkm-btn vkm-btn-accept" data-action="accept-all">
                <?= htmlspecialchars($t('accept_all', 'Accept All')) ?>
            </button>
        </div>
    </div>
</div>

<!-- Preferences Modal (outside banner for proper positioning) -->
<div id="vkm-preferences-modal" class="vkm-preferences-modal" aria-hidden="true">
    <div class="vkm-preferences-overlay" data-action="close-preferences"></div>
    <div class="vkm-preferences-content" role="dialog" aria-labelledby="vkm-preferences-title">
        <div class="vkm-preferences-header">
            <h3 id="vkm-preferences-title"><?= htmlspecialchars($t('preferences_title', 'Manage Cookies')) ?></h3>
            <button type="button" class="vkm-preferences-close" data-action="close-preferences" aria-label="<?= htmlspecialchars($t('close', 'Close')) ?>">
                <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>

        <div class="vkm-preferences-body">
            <p class="vkm-preferences-description">
                <?= htmlspecialchars($t('preferences_description', 'When you visit our website, it may store or retrieve information on your browser, mostly in the form of cookies.')) ?>
            </p>

            <div class="vkm-categories">
                <?php foreach ($categories as $key => $category): ?>
                <?php if (!($category['enabled'] ?? true)) continue; ?>
                <div class="vkm-category" data-category="<?= htmlspecialchars($key) ?>">
                    <div class="vkm-category-header">
                        <div class="vkm-category-info">
                            <h4 class="vkm-category-title">
                                <?= htmlspecialchars($t("category_{$key}_title", $category['title'] ?? ucfirst($key))) ?>
                            </h4>
                        </div>
                        <?php if ($category['required'] ?? false): ?>
                        <span class="vkm-category-badge"><?= htmlspecialchars($t('required', 'Required')) ?></span>
                        <?php else: ?>
                        <label class="vkm-switch">
                            <input type="checkbox"
                                   name="vkm_category_<?= htmlspecialchars($key) ?>"
                                   data-category="<?= htmlspecialchars($key) ?>"
                                   <?= ($category['default'] ?? false) ? 'checked' : '' ?>>
                            <span class="vkm-switch-slider"></span>
                        </label>
                        <?php endif; ?>
                    </div>
                    <p class="vkm-category-description">
                        <?= htmlspecialchars($t("category_{$key}_description", $category['description'] ?? '')) ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="vkm-preferences-footer">
            <button type="button" class="vkm-btn vkm-btn-secondary" data-action="reject-all">
                <?= htmlspecialchars($t('reject_all', 'Reject All')) ?>
            </button>
            <div class="vkm-preferences-footer-right">
                <button type="button" class="vkm-btn vkm-btn-primary" data-action="save-preferences">
                    <?= htmlspecialchars($t('save_preferences', 'Save')) ?>
                </button>
                <button type="button" class="vkm-btn vkm-btn-accept" data-action="accept-all">
                    <?= htmlspecialchars($t('accept_all', 'Accept All')) ?>
                </button>
            </div>
        </div>
    </div>
</div>
