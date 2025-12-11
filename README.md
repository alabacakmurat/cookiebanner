# Chronex Cookie Banner

A flexible, customizable, and GDPR-compliant cookie consent banner for PHP applications.

## Features

-   **Multiple Templates**: Classic, Modern, Minimal, and Floating designs
-   **Automatic Script Blocking**: Block third-party scripts until consent is given
-   **Event Hooks**: Comprehensive event system for logging and integration
-   **Multi-language Support**: 10+ languages included, easily extendable
-   **GDPR Compliant**: Consent proof, anonymized IP logging, withdrawal support
-   **Pluggable Storage**: Store consent in sessions, database, or encrypted cookies
-   **Customizable**: Categories, colors, translations, and more
-   **No Dependencies**: Vanilla JavaScript, works with any PHP project

## Installation

```bash
composer require chronex/cookiebanner
```

## Publishing Assets

The library includes CSS and JavaScript files that need to be accessible from your web server. You can publish these assets using Composer or PHP.

### Using Composer (Recommended)

```bash
# Publish with default settings
composer publish-assets

# Publish minified files
composer publish-assets -- --minify

# Publish to a custom path
composer publish-assets -- --path=public/assets/cookiebanner

# Publish combined CSS (all templates in one file) + minified
composer publish-assets -- --minify --combined
```

### Configure in composer.json

Add configuration to the `extra` section of your project's `composer.json`:

```json
{
    "extra": {
        "cookiebanner": {
            "publish-path": "public/vendor/cookiebanner",
            "minify": true,
            "combined": false
        }
    }
}
```

### Using PHP

```php
use Chronex\CookieBanner\Asset\AssetPublisher;

// Basic publishing
$publisher = new AssetPublisher('/path/to/public/assets');
$files = $publisher->publishAll();

// Publish with minification
$publisher = new AssetPublisher('/path/to/public/assets', minify: true);
$files = $publisher->publishAll();

// Publish only CSS or JS
$publisher->publishCss();
$publisher->publishJs();

// Publish combined CSS (all templates in one file)
$publisher->publishCombinedCss('cookiebanner-all.css');

// Quick static method
AssetPublisher::publish('/path/to/assets', minify: true, combined: true);
```

### Published File Structure

```
public/vendor/cookiebanner/
├── css/
│   ├── base.css          (or base.min.css)
│   ├── classic.css
│   ├── modern.css
│   ├── floating.css
│   ├── minimal.css
│   └── blocking.css
└── js/
    └── cookiebanner.js   (or cookiebanner.min.js)
```

After publishing, update your configuration:

```php
$banner = new CookieBanner([
    'assetsUrl' => '/vendor/cookiebanner',
    // ...
]);
```

## Quick Start

```php
<?php
use Chronex\CookieBanner\CookieBanner;

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
    'cookieName' => 'chronex_cb_consent',
    'cookieExpiry' => 365,               // days
    'cookiePath' => '/',
    'cookieDomain' => '',
    'cookieSecure' => true,
    'cookieSameSite' => true,            // Lax

    // Behavior
    'autoBlock' => true,                 // Auto-block scripts
    'showPreferencesButton' => true,
    'respectDoNotTrack' => false,

    // Storage (see "Consent Storage" section)
    'storageType' => 'legacy',           // legacy, encrypted, session, callback
    'storageEncryptionKey' => '',        // Required for encrypted storage

    // Assets
    'inlineAssets' => false,             // Inline CSS/JS
    'assetsUrl' => '/vendor/chronex/cookiebanner/assets',
]);
```

## Templates

### Classic

Full-width banner at top or bottom of the page.

-   Positions: `top`, `bottom`

### Modern

Card-style banner with shadow and rounded corners.

-   Positions: `bottom-left`, `bottom-right`, `top-left`, `top-right`, `center`

### Minimal

Small popup with essential options only.

-   Positions: `bottom-left`, `bottom-right`, `top-left`, `top-right`

### Floating

Floating button that expands to show cookie options.

-   Positions: `bottom-left`, `bottom-right`

## Cookie Categories

Default categories:

| Category      | Required | Description                                |
| ------------- | -------- | ------------------------------------------ |
| `necessary`   | Yes      | Essential cookies for site functionality   |
| `functional`  | No       | Enhanced functionality and personalization |
| `analytics`   | No       | Traffic analysis (Google Analytics, etc.)  |
| `marketing`   | No       | Marketing and email campaigns              |
| `advertising` | No       | Personalized advertisements                |

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
use Chronex\CookieBanner\Event\ConsentEvent;

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

-   `consent.given` - First time consent
-   `consent.updated` - Preferences changed
-   `consent.withdrawn` - Consent withdrawn
-   `script.loaded` - Script activated
-   `script.blocked` - Script blocked
-   `banner.before_render` - Before HTML render
-   `banner.after_render` - After HTML render

**JavaScript Events:**

-   `chronex-cb:init` - Banner initialized
-   `chronex-cb:consent:given` - Consent given
-   `chronex-cb:consent:updated` - Consent updated
-   `chronex-cb:consent:withdrawn` - Consent withdrawn
-   `chronex-cb:banner:shown` - Banner displayed
-   `chronex-cb:banner:hidden` - Banner hidden
-   `chronex-cb:preferences:opened` - Preferences modal opened
-   `chronex-cb:preferences:closed` - Preferences modal closed
-   `chronex-cb:script:loaded` - Script loaded
-   `chronex-cb:script:blocked` - Script blocked
-   `chronex-cb:category:enabled` - Category enabled
-   `chronex-cb:category:disabled` - Category disabled

## Consent Storage

By default, consent data is stored in a base64-encoded cookie. For enhanced security and flexibility, you can use different storage backends.

### Storage Types

| Type | Cookie Contains | Description |
|------|-----------------|-------------|
| `legacy` | Base64 JSON (default) | Original format, backwards compatible |
| `encrypted` | AES-256-GCM encrypted data | Cookie cannot be decoded without key |
| `session` | Opaque token | Data stored in PHP session |
| `callback` | Opaque token | Data stored in your backend (DB, Redis, etc.) |

### Legacy Storage (Default)

No configuration needed. Cookie contains base64-encoded consent data. Fully backwards compatible with existing installations.

```php
$banner = new CookieBanner([
    'storageType' => 'legacy',  // This is the default
]);
```

### Encrypted Storage

Cookie data is encrypted with AES-256-GCM. Cannot be decoded by users.

```php
$banner = new CookieBanner([
    'storageType' => 'encrypted',
    'storageEncryptionKey' => 'your-32-character-secret-key!!!',
]);
```

### Session Storage

Consent data stored in PHP session. Cookie only contains an opaque token.

```php
$banner = new CookieBanner([
    'storageType' => 'session',
    'storageEncryptionKey' => 'secret-for-token-generation',
    'apiUrl' => '/consent-api',  // Required for JS to communicate with PHP
]);
```

### Database Storage (Callbacks)

Store consent in your own backend (database, Redis, etc.):

```php
$banner = new CookieBanner([
    'apiUrl' => '/consent-api',
    'storageCallbacks' => [
        // Store consent - receives ConsentData and token, returns token
        'store' => function (ConsentData $consent, string $token) use ($db): string {
            $db->insert('consent_records', [
                'token' => $token,
                'consent_id' => $consent->getConsentId(),
                'accepted_categories' => json_encode($consent->getAcceptedCategories()),
                'rejected_categories' => json_encode($consent->getRejectedCategories()),
                'timestamp' => $consent->getTimestamp()->format('Y-m-d H:i:s'),
                'ip_anonymized' => $consent->getAnonymizedIpAddress(),
                'user_agent' => $consent->getUserAgent(),
                'consent_method' => $consent->getConsentMethod(),
            ]);
            return $token;
        },

        // Retrieve consent - receives token, returns array or null
        'retrieve' => function (string $token) use ($db): ?array {
            $row = $db->find('consent_records', $token);
            if (!$row) return null;

            return [
                'consent_id' => $row['consent_id'],
                'accepted_categories' => json_decode($row['accepted_categories'], true),
                'rejected_categories' => json_decode($row['rejected_categories'], true),
                'timestamp' => $row['timestamp'],
                'consent_method' => $row['consent_method'],
            ];
        },

        // Delete consent - receives token, returns bool
        'delete' => function (string $token) use ($db): bool {
            return $db->delete('consent_records', $token);
        },
    ],
]);
```

### Using Methods

You can also set storage after initialization:

```php
// Use session storage
$banner->useSessionStorage('secret-key');

// Use encrypted cookie storage
$banner->useEncryptedStorage('encryption-key');

// Use custom callbacks
$banner->setStorageCallbacks(
    storeCallback: fn($consent, $token) => ...,
    retrieveCallback: fn($token) => ...,
    deleteCallback: fn($token) => ...,
    secretKey: 'your-secret'
);

// Use custom storage class
$banner->setStorage(new MyCustomStorage());
```

### Custom Storage Class

Implement `StorageInterface` for complete control:

```php
use Chronex\CookieBanner\Storage\StorageInterface;
use Chronex\CookieBanner\Consent\ConsentData;

class RedisStorage implements StorageInterface
{
    private Redis $redis;

    public function store(ConsentData $consent): string
    {
        $token = $this->generateToken();
        $this->redis->setex("consent:{$token}", 86400 * 365, json_encode($consent->toArray()));
        return $token;
    }

    public function retrieve(string $token): ?ConsentData
    {
        $data = $this->redis->get("consent:{$token}");
        if (!$data) return null;
        return ConsentData::fromArray(json_decode($data, true));
    }

    public function delete(string $token): bool
    {
        return $this->redis->del("consent:{$token}") > 0;
    }

    public function exists(string $token): bool
    {
        return $this->redis->exists("consent:{$token}");
    }

    public function update(string $token, ConsentData $consent): bool
    {
        if (!$this->exists($token)) return false;
        $this->redis->setex("consent:{$token}", 86400 * 365, json_encode($consent->toArray()));
        return true;
    }

    public function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}

// Use it
$banner->setStorage(new RedisStorage($redis));
```

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

Add `type="text/plain"` and `data-chronex-cb-category` to any script:

```html
<script type="text/plain" data-chronex-cb-category="analytics">
    // This won't execute until analytics consent is given
    gtag('js', new Date());
</script>
```

### Built-in Providers

The following third-party scripts are automatically detected and blocked:

-   **Analytics**: Google Analytics, Google Tag Manager, Matomo, Hotjar
-   **Advertising**: Google Ads, Facebook Pixel, Twitter Pixel, TikTok Pixel
-   **Marketing**: LinkedIn Insight, HubSpot
-   **Functional**: Intercom, Crisp, YouTube, Vimeo

## JavaScript API

```javascript
// Check consent
chronexCbInstance.hasConsent(); // Boolean
chronexCbInstance.hasConsentFor("analytics"); // Boolean
chronexCbInstance.getAcceptedCategories(); // Array
chronexCbInstance.getRejectedCategories(); // Array

// Control banner
chronexCbInstance.showBanner();
chronexCbInstance.hideBanner();
chronexCbInstance.showPreferences();
chronexCbInstance.closePreferences();

// Consent actions
chronexCbInstance.acceptAll();
chronexCbInstance.rejectAll();
chronexCbInstance.withdrawConsent();

// Get consent data
chronexCbInstance.getConsentData();
chronexCbInstance.getConsentProof();

// Event listeners
chronexCbInstance.on("consent.given", (data) => {
    console.log("Consent given:", data);
});
```

## Multi-language Support

### Built-in Languages

-   English (en)
-   Turkish (tr)
-   German (de)
-   French (fr)
-   Spanish (es)
-   Dutch (nl)
-   Italian (it)
-   Portuguese (pt)
-   Polish (pl)
-   Russian (ru)

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
use Chronex\CookieBanner\Template\AbstractTemplate;

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
fetch("/consent-api?api", {
    method: "POST",
    body: JSON.stringify({ action: "get_consent" }),
});

// Give consent
fetch("/consent-api?api", {
    method: "POST",
    body: JSON.stringify({
        action: "give_consent",
        categories: ["necessary", "analytics"],
        method: "preferences",
    }),
});

// Accept all
fetch("/consent-api?api", {
    method: "POST",
    body: JSON.stringify({ action: "accept_all" }),
});

// Reject all
fetch("/consent-api?api", {
    method: "POST",
    body: JSON.stringify({ action: "reject_all" }),
});

// Withdraw consent
fetch("/consent-api?api", {
    method: "POST",
    body: JSON.stringify({ action: "withdraw_consent" }),
});
```

## CSS Customization

Override CSS variables:

```css
:root {
    --chronex-cb-primary: #2563eb;
    --chronex-cb-primary-hover: #1d4ed8;
    --chronex-cb-background: #ffffff;
    --chronex-cb-text: #1e293b;
    --chronex-cb-border: #e2e8f0;
    --chronex-cb-radius: 8px;
    --chronex-cb-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
```

## Examples

Check the `examples/` directory:

-   `basic.php` - Simple usage
-   `advanced.php` - Event hooks, script blocking
-   `demo.php` - Interactive demo with all features
-   `custom-storage.php` - Session, database, and encrypted storage examples
-   `user-tracking.php` - Associate consent with logged-in users
-   `blocking.php` - Blocking mode (require consent before site access)

## Requirements

-   PHP 8.0+
-   No external dependencies

## License

MIT License - see [LICENSE](LICENSE) for details.

## Credits

Developed by [Chronex](https://github.com/alabacakmurat)
