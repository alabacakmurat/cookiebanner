<?php

declare(strict_types=1);

namespace VkmToolkit\CookieBanner\ScriptBlocker;

use VkmToolkit\CookieBanner\Config\Configuration;
use VkmToolkit\CookieBanner\Consent\ConsentManager;
use VkmToolkit\CookieBanner\Event\EventDispatcher;
use VkmToolkit\CookieBanner\Event\ScriptBlockedEvent;
use VkmToolkit\CookieBanner\Event\ScriptLoadedEvent;

class ScriptBlocker
{
    private Configuration $config;
    private ConsentManager $consentManager;
    private EventDispatcher $eventDispatcher;
    private array $registeredScripts = [];
    private array $providers = [];

    public function __construct(
        Configuration $config,
        ConsentManager $consentManager,
        EventDispatcher $eventDispatcher
    ) {
        $this->config = $config;
        $this->consentManager = $consentManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->registerDefaultProviders();
    }

    private function registerDefaultProviders(): void
    {
        // Google Analytics
        $this->registerProvider('google_analytics', 'analytics', [
            'patterns' => [
                'google-analytics.com/analytics.js',
                'googletagmanager.com/gtag/js',
                'ga.js',
            ],
        ]);

        // Google Tag Manager
        $this->registerProvider('google_tag_manager', 'analytics', [
            'patterns' => [
                'googletagmanager.com/gtm.js',
            ],
        ]);

        // Google Ads
        $this->registerProvider('google_ads', 'advertising', [
            'patterns' => [
                'googleadservices.com',
                'googlesyndication.com',
                'googleads.g.doubleclick.net',
                'pagead2.googlesyndication.com',
            ],
        ]);

        // Facebook Pixel
        $this->registerProvider('facebook_pixel', 'advertising', [
            'patterns' => [
                'connect.facebook.net',
                'facebook.com/tr',
            ],
        ]);

        // Hotjar
        $this->registerProvider('hotjar', 'analytics', [
            'patterns' => [
                'static.hotjar.com',
                'script.hotjar.com',
            ],
        ]);

        // LinkedIn Insight
        $this->registerProvider('linkedin_insight', 'marketing', [
            'patterns' => [
                'snap.licdn.com/li.lms-analytics',
            ],
        ]);

        // Twitter/X Pixel
        $this->registerProvider('twitter_pixel', 'advertising', [
            'patterns' => [
                'static.ads-twitter.com',
                'analytics.twitter.com',
            ],
        ]);

        // TikTok Pixel
        $this->registerProvider('tiktok_pixel', 'advertising', [
            'patterns' => [
                'analytics.tiktok.com',
            ],
        ]);

        // Intercom
        $this->registerProvider('intercom', 'functional', [
            'patterns' => [
                'widget.intercom.io',
                'js.intercomcdn.com',
            ],
        ]);

        // Crisp Chat
        $this->registerProvider('crisp', 'functional', [
            'patterns' => [
                'client.crisp.chat',
            ],
        ]);

        // HubSpot
        $this->registerProvider('hubspot', 'marketing', [
            'patterns' => [
                'js.hs-scripts.com',
                'js.hsforms.net',
            ],
        ]);

        // Matomo/Piwik
        $this->registerProvider('matomo', 'analytics', [
            'patterns' => [
                'matomo.js',
                'piwik.js',
            ],
        ]);

        // YouTube (embedded videos with tracking)
        $this->registerProvider('youtube', 'functional', [
            'patterns' => [
                'youtube.com/iframe_api',
                'youtube.com/embed',
            ],
        ]);

        // Vimeo
        $this->registerProvider('vimeo', 'functional', [
            'patterns' => [
                'player.vimeo.com',
            ],
        ]);
    }

    public function registerProvider(string $name, string $category, array $config = []): self
    {
        $this->providers[$name] = [
            'name' => $name,
            'category' => $category,
            'patterns' => $config['patterns'] ?? [],
            'enabled' => $config['enabled'] ?? true,
        ];
        return $this;
    }

    public function unregisterProvider(string $name): self
    {
        unset($this->providers[$name]);
        return $this;
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getProvider(string $name): ?array
    {
        return $this->providers[$name] ?? null;
    }

    public function registerScript(
        string $id,
        string $category,
        string $script,
        ?string $provider = null,
        array $attributes = []
    ): self {
        $this->registeredScripts[$id] = [
            'id' => $id,
            'category' => $category,
            'script' => $script,
            'provider' => $provider,
            'attributes' => $attributes,
            'type' => $this->detectScriptType($script),
        ];
        return $this;
    }

    public function unregisterScript(string $id): self
    {
        unset($this->registeredScripts[$id]);
        return $this;
    }

    private function detectScriptType(string $script): string
    {
        if (preg_match('/<script[^>]*src=["\']/', $script)) {
            return 'external';
        }
        if (preg_match('/<script[^>]*>/', $script)) {
            return 'inline';
        }
        return 'raw';
    }

    public function renderScript(string $id): string
    {
        if (!isset($this->registeredScripts[$id])) {
            return '';
        }

        $scriptData = $this->registeredScripts[$id];
        $category = $scriptData['category'];
        $hasConsent = $this->consentManager->hasConsentFor($category);

        if ($hasConsent) {
            // Dispatch loaded event
            $event = new ScriptLoadedEvent(
                $id,
                $category,
                $scriptData['provider'] ?? '',
                []
            );
            $this->eventDispatcher->dispatch($event);

            return $scriptData['script'];
        }

        // Dispatch blocked event
        $event = new ScriptBlockedEvent(
            $id,
            $category,
            $scriptData['provider'] ?? '',
            $scriptData['script'],
            []
        );
        $this->eventDispatcher->dispatch($event);

        // Return blocked script (text/plain)
        return $this->convertToBlockedScript($scriptData);
    }

    private function convertToBlockedScript(array $scriptData): string
    {
        $script = $scriptData['script'];
        $category = $scriptData['category'];
        $id = $scriptData['id'];

        // For external scripts
        if ($scriptData['type'] === 'external') {
            return preg_replace(
                '/<script([^>]*)>/',
                '<script type="text/plain" data-vkm-category="' . htmlspecialchars($category) . '" data-vkm-script-id="' . htmlspecialchars($id) . '"$1>',
                $script
            );
        }

        // For inline scripts
        if ($scriptData['type'] === 'inline') {
            return preg_replace(
                '/<script([^>]*)>/',
                '<script type="text/plain" data-vkm-category="' . htmlspecialchars($category) . '" data-vkm-script-id="' . htmlspecialchars($id) . '"$1>',
                $script
            );
        }

        // For raw JavaScript code
        return '<script type="text/plain" data-vkm-category="' . htmlspecialchars($category) . '" data-vkm-script-id="' . htmlspecialchars($id) . '">'
            . $script
            . '</script>';
    }

    public function renderAllScripts(): string
    {
        $output = '';
        foreach ($this->registeredScripts as $id => $script) {
            $output .= $this->renderScript($id);
        }
        return $output;
    }

    public function renderScriptsByCategory(string $category): string
    {
        $output = '';
        foreach ($this->registeredScripts as $id => $script) {
            if ($script['category'] === $category) {
                $output .= $this->renderScript($id);
            }
        }
        return $output;
    }

    public function shouldBlockScript(string $src): bool
    {
        if (!$this->config->isAutoBlock()) {
            return false;
        }

        foreach ($this->providers as $provider) {
            if (!$provider['enabled']) {
                continue;
            }

            foreach ($provider['patterns'] as $pattern) {
                if (stripos($src, $pattern) !== false) {
                    return !$this->consentManager->hasConsentFor($provider['category']);
                }
            }
        }

        return false;
    }

    public function getCategoryForScript(string $src): ?string
    {
        foreach ($this->providers as $provider) {
            foreach ($provider['patterns'] as $pattern) {
                if (stripos($src, $pattern) !== false) {
                    return $provider['category'];
                }
            }
        }
        return null;
    }

    public function getJavaScriptBlockerConfig(): array
    {
        $patterns = [];

        foreach ($this->providers as $name => $provider) {
            if (!$provider['enabled']) {
                continue;
            }

            foreach ($provider['patterns'] as $pattern) {
                $patterns[] = [
                    'pattern' => $pattern,
                    'category' => $provider['category'],
                    'provider' => $name,
                ];
            }
        }

        return [
            'enabled' => $this->config->isAutoBlock(),
            'patterns' => $patterns,
            'registeredScripts' => array_map(function ($script) {
                return [
                    'id' => $script['id'],
                    'category' => $script['category'],
                    'provider' => $script['provider'],
                ];
            }, $this->registeredScripts),
        ];
    }

    public function getRegisteredScripts(): array
    {
        return $this->registeredScripts;
    }
}
