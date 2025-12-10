<?php
/**
 * Classic Template - Full-width banner
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
     class="vkm-cookie-banner vkm-classic vkm-position-<?= htmlspecialchars($position) ?>"
     role="dialog"
     aria-modal="true"
     aria-labelledby="vkm-cookie-title"
     aria-describedby="vkm-cookie-description"
     data-template="classic">

    <div class="vkm-cookie-container">
        <div class="vkm-cookie-content">
            <div class="vkm-cookie-text">
                <h2 id="vkm-cookie-title" class="vkm-cookie-title">
                    <?= htmlspecialchars($t('title', 'Cookie Settings')) ?>
                </h2>
                <p id="vkm-cookie-description" class="vkm-cookie-description">
                    <?= htmlspecialchars($t('description', 'We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.')) ?>
                </p>

                <?php if ($privacyPolicyUrl || $cookiePolicyUrl): ?>
                <p class="vkm-cookie-links">
                    <?php if ($privacyPolicyUrl): ?>
                    <a href="<?= htmlspecialchars($privacyPolicyUrl) ?>" target="_blank" rel="noopener">
                        <?= htmlspecialchars($t('privacy_policy', 'Privacy Policy')) ?>
                    </a>
                    <?php endif; ?>
                    <?php if ($privacyPolicyUrl && $cookiePolicyUrl): ?>
                    <span class="vkm-separator">|</span>
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
                <button type="button" class="vkm-btn vkm-btn-reject" data-action="reject-all">
                    <?= htmlspecialchars($t('reject_all', 'Reject All')) ?>
                </button>
                <?php if ($showPreferencesButton): ?>
                <button type="button" class="vkm-btn vkm-btn-preferences" data-action="show-preferences">
                    <?= htmlspecialchars($t('preferences', 'Preferences')) ?>
                </button>
                <?php endif; ?>
                <button type="button" class="vkm-btn vkm-btn-accept" data-action="accept-all">
                    <?= htmlspecialchars($t('accept_all', 'Accept All')) ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Preferences Modal (outside banner for proper positioning) -->
<div id="vkm-preferences-modal" class="vkm-preferences-modal" aria-hidden="true">
    <div class="vkm-preferences-overlay" data-action="close-preferences"></div>
    <div class="vkm-preferences-content" role="dialog" aria-labelledby="vkm-preferences-title">
        <div class="vkm-preferences-header">
            <h3 id="vkm-preferences-title"><?= htmlspecialchars($t('preferences_title', 'Cookie Preferences')) ?></h3>
            <button type="button" class="vkm-preferences-close" data-action="close-preferences" aria-label="<?= htmlspecialchars($t('close', 'Close')) ?>">
                <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>

        <div class="vkm-preferences-body">
            <p class="vkm-preferences-description">
                <?= htmlspecialchars($t('preferences_description', 'Manage your cookie preferences below. You can enable or disable different types of cookies.')) ?>
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
                            <?php if ($category['required'] ?? false): ?>
                            <span class="vkm-category-required"><?= htmlspecialchars($t('always_active', 'Always Active')) ?></span>
                            <?php endif; ?>
                        </div>
                        <label class="vkm-toggle">
                            <input type="checkbox"
                                   name="vkm_category_<?= htmlspecialchars($key) ?>"
                                   data-category="<?= htmlspecialchars($key) ?>"
                                   <?= ($category['required'] ?? false) ? 'checked disabled' : '' ?>
                                   <?= ($category['default'] ?? false) ? 'checked' : '' ?>>
                            <span class="vkm-toggle-slider"></span>
                        </label>
                    </div>
                    <p class="vkm-category-description">
                        <?= htmlspecialchars($t("category_{$key}_description", $category['description'] ?? '')) ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="vkm-preferences-footer">
            <button type="button" class="vkm-btn vkm-btn-reject" data-action="reject-all">
                <?= htmlspecialchars($t('reject_all', 'Reject All')) ?>
            </button>
            <button type="button" class="vkm-btn vkm-btn-save" data-action="save-preferences">
                <?= htmlspecialchars($t('save_preferences', 'Save Preferences')) ?>
            </button>
            <button type="button" class="vkm-btn vkm-btn-accept" data-action="accept-all">
                <?= htmlspecialchars($t('accept_all', 'Accept All')) ?>
            </button>
        </div>
    </div>
</div>
