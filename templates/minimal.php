<?php
/**
 * Minimal Template - Small popup with essential options
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
     class="vkm-cookie-banner vkm-minimal vkm-position-<?= htmlspecialchars($position) ?>"
     role="dialog"
     aria-modal="true"
     aria-labelledby="vkm-cookie-title"
     data-template="minimal">

    <div class="vkm-cookie-popup">
        <button type="button" class="vkm-popup-close" data-action="close-banner" aria-label="<?= htmlspecialchars($t('close', 'Close')) ?>">
            <svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
        </button>

        <p id="vkm-cookie-title" class="vkm-popup-text">
            <?= htmlspecialchars($t('short_description', 'This site uses cookies.')) ?>
            <?php if ($privacyPolicyUrl): ?>
            <a href="<?= htmlspecialchars($privacyPolicyUrl) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($t('learn_more', 'Learn more')) ?></a>
            <?php endif; ?>
        </p>

        <div class="vkm-popup-actions">
            <?php if ($showPreferencesButton): ?>
            <button type="button" class="vkm-btn vkm-btn-link" data-action="show-preferences">
                <?= htmlspecialchars($t('settings', 'Settings')) ?>
            </button>
            <?php endif; ?>
            <button type="button" class="vkm-btn vkm-btn-small vkm-btn-reject" data-action="reject-all">
                <?= htmlspecialchars($t('decline', 'Decline')) ?>
            </button>
            <button type="button" class="vkm-btn vkm-btn-small vkm-btn-accept" data-action="accept-all">
                <?= htmlspecialchars($t('accept', 'Accept')) ?>
            </button>
        </div>
    </div>
</div>

<!-- Preferences Modal (outside banner for proper positioning) -->
<div id="vkm-preferences-modal" class="vkm-preferences-modal" aria-hidden="true">
    <div class="vkm-preferences-overlay" data-action="close-preferences"></div>
    <div class="vkm-preferences-content vkm-preferences-compact" role="dialog" aria-labelledby="vkm-preferences-title">
        <div class="vkm-preferences-header">
            <h3 id="vkm-preferences-title"><?= htmlspecialchars($t('cookie_settings', 'Cookie Settings')) ?></h3>
            <button type="button" class="vkm-preferences-close" data-action="close-preferences" aria-label="<?= htmlspecialchars($t('close', 'Close')) ?>">
                <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>

        <div class="vkm-preferences-body">
            <div class="vkm-categories-compact">
                <?php foreach ($categories as $key => $category): ?>
                <?php if (!($category['enabled'] ?? true)) continue; ?>
                <label class="vkm-category-row" data-category="<?= htmlspecialchars($key) ?>">
                    <span class="vkm-category-name">
                        <?= htmlspecialchars($t("category_{$key}_title", $category['title'] ?? ucfirst($key))) ?>
                        <?php if ($category['required'] ?? false): ?>
                        <small>(<?= htmlspecialchars($t('required', 'Required')) ?>)</small>
                        <?php endif; ?>
                    </span>
                    <input type="checkbox"
                           class="vkm-checkbox"
                           name="vkm_category_<?= htmlspecialchars($key) ?>"
                           data-category="<?= htmlspecialchars($key) ?>"
                           <?= ($category['required'] ?? false) ? 'checked disabled' : '' ?>
                           <?= ($category['default'] ?? false) ? 'checked' : '' ?>>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="vkm-preferences-footer">
            <button type="button" class="vkm-btn vkm-btn-small vkm-btn-save" data-action="save-preferences">
                <?= htmlspecialchars($t('save', 'Save')) ?>
            </button>
        </div>
    </div>
</div>
