# VKM Cookie Banner

A flexible, customizable, and GDPR-compliant cookie consent banner for PHP applications.

## Features

- **Multiple Templates**: Classic, Modern, Minimal, and Floating designs
- **Automatic Script Blocking**: Block third-party scripts until consent is given
- **Event Hooks**: Comprehensive event system for logging and integration
- **Multi-language Support**: 10+ languages included, easily extendable
- **GDPR Compliant**: Consent proof, anonymized IP logging, withdrawal support
- **Customizable**: Categories, colors, translations, and more
- **No Dependencies**: Vanilla JavaScript, works with any PHP project

## Installation

```bash
composer require vkmtoolkit/cookiebanner
```

## Quick Start

```php
<?php
use VkmToolkit\CookieBanner\CookieBanner;

$banner = new CookieBanner([
    'template' => 'modern',
    'position' => 'bottom-right',
    'language' => 'en',
    'privacyPolicyUrl' => '/privacy-policy',
]);

// In your HTML <head>:
echo $banner->renderCss();

// Before </body>:
echo $banner->render();
echo $banner->renderJs();
```

## Configuration Options

```php
$banner = new CookieBanner([
    // Template settings
    'template' => 'modern',              // classic, modern, minimal, floating
    'position' => 'bottom-right',        // depends on template
    'language' => 'en',                  // en, tr, de, fr, es, nl, it, pt, pl, ru

    // URLs
    'privacyPolicyUrl' => '/privacy',
    'cookiePolicyUrl' => '/cookies',

    // Cookie settings
    'cookieName' => 'vkm_cookie_consent',
    'cookieExpiry' => 365,               // days
    'cookiePath' => '/',
    'cookieDomain' => '',
    'cookieSecure' => true,
    'cookieSameSite' => true,            // Lax

    // Behavior
    'autoBlock' => true,                 // Auto-block scripts
    'showPreferencesButton' => true,
    'respectDoNotTrack' => false,

    // Assets
    'inlineAssets' => false,             // Inline CSS/JS
    'assetsUrl' => '/vendor/vkmtoolkit/cookiebanner/assets',
]);
```

## Templates

### Classic
Full-width banner at top or bottom of the page.
- Positions: `top`, `bottom`

### Modern
Card-style banner with shadow and rounded corners.
- Positions: `bottom-left`, `bottom-right`, `top-left`, `top-right`, `center`

### Minimal
Small popup with essential options only.
- Positions: `bottom-left`, `bottom-right`, `top-left`, `top-right`

### Floating
Floating button that expands to show cookie options.
- Positions: `bottom-left`, `bottom-right`

## Cookie Categories

Default categories:

| Category | Required | Description |
|----------|----------|-------------|
| `necessary` | Yes | Essential cookies for site functionality |
| `functional` | No | Enhanced functionality and personalization |
| `analytics` | No | Traffic analysis (Google Analytics, etc.) |
| `marketing` | No | Marketing and email campaigns |
| `advertising` | No | Personalized advertisements |

### Custom Categories

```php
$banner->addCategory('social', [
    'enabled' => true,
    'required' => false,
    'default' => false,
    'title' => 'Social Media',
    'description' => 'Cookies for social media integrations.',
]);
```

## Event Hooks

The most powerful feature for GDPR compliance - log every consent action.

```php
use VkmToolkit\CookieBanner\Event\ConsentEvent;

// When user gives consent for the first time
$banner->on(ConsentEvent::TYPE_GIVEN, function(ConsentEvent $event) {
    $db->insert('consent_logs', [
        'consent_id' => $event->getConsentId(),
        'timestamp' => $event->getTimestamp()->format('Y-m-d H:i:s'),
        'ip_anonymized' => $event->getAnonymizedIpAddress(),
        'user_agent' => $event->getUserAgent(),
        'page_url' => $event->getPageUrl(),
        'accepted' => json_encode($event->getAcceptedCategories()),
        'rejected' => json_encode($event->getRejectedCategories()),
        'method' => $event->getConsentMethod(),
        'consent_proof' => $event->getConsentProof(),
    ]);
});

// When user updates their preferences
$banner->on(ConsentEvent::TYPE_UPDATED, function(ConsentEvent $event) {
    // Log the update
});

// When user withdraws consent
$banner->on(ConsentEvent::TYPE_WITHDRAWN, function(ConsentEvent $event) {
    // Handle withdrawal
});
```

### Available Events

**PHP Events:**
- `consent.given` - First time consent
- `consent.updated` - Preferences changed
- `consent.withdrawn` - Consent withdrawn
- `script.loaded` - Script activated
- `script.blocked` - Script blocked
- `banner.before_render` - Before HTML render
- `banner.after_render` - After HTML render

**JavaScript Events:**
- `vkm:init` - Banner initialized
- `vkm:consent:given` - Consent given
- `vkm:consent:updated` - Consent updated
- `vkm:consent:withdrawn` - Consent withdrawn
- `vkm:banner:shown` - Banner displayed
- `vkm:banner:hidden` - Banner hidden
- `vkm:preferences:opened` - Preferences modal opened
- `vkm:preferences:closed` - Preferences modal closed
- `vkm:script:loaded` - Script loaded
- `vkm:script:blocked` - Script blocked
- `vkm:category:enabled` - Category enabled
- `vkm:category:disabled` - Category disabled

## Script Blocking

Automatically block third-party scripts until consent is given.

### PHP-based Blocking

```php
// Register a script
$banner->registerScript(
    'google_analytics',           // Unique ID
    'analytics',                  // Category
    '<script async src="https://www.googletagmanager.com/gtag/js?id=GA_ID"></script>',
    'google_analytics'            // Provider name (optional)
);

// Render conditionally
echo $banner->renderScript('google_analytics');

// Or render all registered scripts
echo $banner->renderAllScripts();
```

### JavaScript-based Blocking

Add `type="text/plain"` and `data-vkm-category` to any script:

```html
<script type="text/plain" data-vkm-category="analytics">
    // This won't execute until analytics consent is given
    gtag('js', new Date());
</script>
```

### Built-in Providers

The following third-party scripts are automatically detected and blocked:

- **Analytics**: Google Analytics, Google Tag Manager, Matomo, Hotjar
- **Advertising**: Google Ads, Facebook Pixel, Twitter Pixel, TikTok Pixel
- **Marketing**: LinkedIn Insight, HubSpot
- **Functional**: Intercom, Crisp, YouTube, Vimeo

## JavaScript API

```javascript
// Check consent
vkmCookieBanner.hasConsent();                    // Boolean
vkmCookieBanner.hasConsentFor('analytics');      // Boolean
vkmCookieBanner.getAcceptedCategories();         // Array
vkmCookieBanner.getRejectedCategories();         // Array

// Control banner
vkmCookieBanner.showBanner();
vkmCookieBanner.hideBanner();
vkmCookieBanner.showPreferences();
vkmCookieBanner.closePreferences();

// Consent actions
vkmCookieBanner.acceptAll();
vkmCookieBanner.rejectAll();
vkmCookieBanner.withdrawConsent();

// Get consent data
vkmCookieBanner.getConsentData();
vkmCookieBanner.getConsentProof();

// Event listeners
vkmCookieBanner.on('consent.given', (data) => {
    console.log('Consent given:', data);
});
```

## Multi-language Support

### Built-in Languages
- English (en)
- Turkish (tr)
- German (de)
- French (fr)
- Spanish (es)
- Dutch (nl)
- Italian (it)
- Portuguese (pt)
- Polish (pl)
- Russian (ru)

### Add Custom Translations

```php
$banner->addTranslations('en', [
    'title' => 'We use cookies',
    'description' => 'Custom description here...',
    'accept_all' => 'Accept All',
    'reject_all' => 'Reject All',
    'category_analytics_title' => 'Analytics Cookies',
    'category_analytics_description' => 'Help us understand...',
]);
```

### Register New Language

```php
$banner->registerLanguage('ja', [
    'title' => 'クッキーの設定',
    'accept_all' => 'すべて受け入れる',
    // ... all translation keys
]);
```

## Custom Templates

Create your own template:

```php
use VkmToolkit\CookieBanner\Template\AbstractTemplate;

class MyCustomTemplate extends AbstractTemplate
{
    public function getName(): string
    {
        return 'my-template';
    }

    public function getDescription(): string
    {
        return 'My custom cookie banner template';
    }

    public function getTemplateFile(): string
    {
        return 'my-template.php';
    }

    public function getPositions(): array
    {
        return ['bottom', 'top'];
    }
}

// Register
$banner->registerTemplate(new MyCustomTemplate('/path/to/templates'));
```

## API Endpoint

Handle AJAX requests for consent management:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
    header('Content-Type: application/json');
    $request = json_decode(file_get_contents('php://input'), true);
    $response = $banner->handleApiRequest($request);
    echo json_encode($response);
    exit;
}
```

### API Actions

```javascript
// Get current consent
fetch('/consent-api?api', {
    method: 'POST',
    body: JSON.stringify({ action: 'get_consent' })
});

// Give consent
fetch('/consent-api?api', {
    method: 'POST',
    body: JSON.stringify({
        action: 'give_consent',
        categories: ['necessary', 'analytics'],
        method: 'preferences'
    })
});

// Accept all
fetch('/consent-api?api', {
    method: 'POST',
    body: JSON.stringify({ action: 'accept_all' })
});

// Reject all
fetch('/consent-api?api', {
    method: 'POST',
    body: JSON.stringify({ action: 'reject_all' })
});

// Withdraw consent
fetch('/consent-api?api', {
    method: 'POST',
    body: JSON.stringify({ action: 'withdraw_consent' })
});
```

## CSS Customization

Override CSS variables:

```css
:root {
    --vkm-primary: #2563eb;
    --vkm-primary-hover: #1d4ed8;
    --vkm-background: #ffffff;
    --vkm-text: #1e293b;
    --vkm-border: #e2e8f0;
    --vkm-radius: 8px;
    --vkm-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
```

## Examples

Check the `examples/` directory:

- `basic.php` - Simple usage
- `advanced.php` - Event hooks, script blocking
- `demo.php` - Interactive demo with all features

## Requirements

- PHP 8.0+
- No external dependencies

## License

MIT License - see [LICENSE](LICENSE) for details.

## Credits

Developed by [VKM Admins](https://github.com/vkmadmins)
