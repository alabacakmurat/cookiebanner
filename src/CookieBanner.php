<?php

declare(strict_types=1);

namespace Havax\CookieBanner;

use Havax\CookieBanner\Config\Configuration;
use Havax\CookieBanner\Consent\ConsentManager;
use Havax\CookieBanner\Consent\ConsentData;
use Havax\CookieBanner\Event\EventDispatcher;
use Havax\CookieBanner\Event\EventInterface;
use Havax\CookieBanner\Event\BannerEvent;
use Havax\CookieBanner\Language\TranslationManager;
use Havax\CookieBanner\Template\TemplateManager;
use Havax\CookieBanner\Template\TemplateInterface;
use Havax\CookieBanner\ScriptBlocker\ScriptBlocker;

class CookieBanner
{
	private Configuration $config;
	private EventDispatcher $eventDispatcher;
	private ConsentManager $consentManager;
	private TranslationManager $translationManager;
	private TemplateManager $templateManager;
	private ScriptBlocker $scriptBlocker;
	private string $assetsUrl = '';
	private bool $inlineAssets = false;
	private string $defaultAssetsPath;
	private ?string $customAssetsPath = null;
	private ?string $apiUrl = null;

	public function __construct(array $config = [])
	{
		$this->config = new Configuration($config);
		$this->eventDispatcher = new EventDispatcher();
		$this->consentManager = new ConsentManager($this->config, $this->eventDispatcher);

		// Store default and custom asset paths
		$this->defaultAssetsPath = dirname(__DIR__) . '/assets';
		$this->customAssetsPath = $config['assetsPath'] ?? null;

		$this->translationManager = new TranslationManager(
			$config['languagesPath'] ?? null,
			$this->config->getLanguage()
		);
		$this->templateManager = new TemplateManager(
			$config['templatesPath'] ?? null,
			$this->customAssetsPath
		);
		$this->scriptBlocker = new ScriptBlocker(
			$this->config,
			$this->consentManager,
			$this->eventDispatcher
		);

		if (isset($config['assetsUrl'])) {
			$this->assetsUrl = rtrim($config['assetsUrl'], '/');
		}

		if (isset($config['inlineAssets'])) {
			$this->inlineAssets = (bool) $config['inlineAssets'];
		}

		if (isset($config['apiUrl'])) {
			$this->apiUrl = $config['apiUrl'];
		}

		// Apply custom translations
		if (isset($config['translations'])) {
			foreach ($config['translations'] as $lang => $translations) {
				$this->translationManager->extend($lang, $translations);
			}
		}

		// Auto-select blocking template if blockingMode is enabled
		if ($this->config->isBlockingMode() && !isset($config['template'])) {
			$this->config->setTemplate('blocking');
		}
	}

	/**
	 * Find an asset file, checking custom path first, then default path
	 */
	protected function findAssetFile(string $relativePath): ?string
	{
		// Check custom path first
		if ($this->customAssetsPath) {
			$customFile = $this->customAssetsPath . '/' . $relativePath;
			if (file_exists($customFile)) {
				return $customFile;
			}
		}

		// Fall back to default path
		$defaultFile = $this->defaultAssetsPath . '/' . $relativePath;
		if (file_exists($defaultFile)) {
			return $defaultFile;
		}

		return null;
	}

    // ==================== Event Methods ====================

	/**
	 * Register an event listener
	 */
	public function on(string $eventName, callable $callback, int $priority = 0): self
	{
		$this->eventDispatcher->on($eventName, $callback, $priority);
		return $this;
	}

	/**
	 * Remove an event listener
	 */
	public function off(string $eventName, ?callable $callback = null): self
	{
		$this->eventDispatcher->off($eventName, $callback);
		return $this;
	}

	/**
	 * Register a one-time event listener
	 */
	public function once(string $eventName, callable $callback, int $priority = 0): self
	{
		$this->eventDispatcher->once($eventName, $callback, $priority);
		return $this;
	}

	/**
	 * Get the event dispatcher
	 */
	public function getEventDispatcher(): EventDispatcher
	{
		return $this->eventDispatcher;
	}

    // ==================== Configuration Methods ====================

	/**
	 * Get configuration
	 */
	public function getConfig(): Configuration
	{
		return $this->config;
	}

	/**
	 * Set template
	 */
	public function setTemplate(string $template): self
	{
		$this->config->setTemplate($template);
		return $this;
	}

	/**
	 * Set position
	 */
	public function setPosition(string $position): self
	{
		$this->config->setPosition($position);
		return $this;
	}

	/**
	 * Set language
	 */
	public function setLanguage(string $language): self
	{
		$this->config->setLanguage($language);
		$this->translationManager->setLanguage($language);
		return $this;
	}

	/**
	 * Add a custom category
	 */
	public function addCategory(string $key, array $config): self
	{
		$this->config->addCategory($key, $config);
		return $this;
	}

	/**
	 * Remove a category
	 */
	public function removeCategory(string $key): self
	{
		$this->config->removeCategory($key);
		return $this;
	}

	/**
	 * Set privacy policy URL
	 */
	public function setPrivacyPolicyUrl(string $url): self
	{
		$this->config->setPrivacyPolicyUrl($url);
		return $this;
	}

	/**
	 * Set cookie policy URL
	 */
	public function setCookiePolicyUrl(string $url): self
	{
		$this->config->setCookiePolicyUrl($url);
		return $this;
	}

    // ==================== Translation Methods ====================

	/**
	 * Get translation manager
	 */
	public function getTranslationManager(): TranslationManager
	{
		return $this->translationManager;
	}

	/**
	 * Add custom translations
	 */
	public function addTranslations(string $language, array $translations): self
	{
		$this->translationManager->extend($language, $translations);
		return $this;
	}

	/**
	 * Register a new language
	 */
	public function registerLanguage(string $code, array $translations): self
	{
		$this->translationManager->registerLanguage($code, $translations);
		return $this;
	}

    // ==================== Template Methods ====================

	/**
	 * Get template manager
	 */
	public function getTemplateManager(): TemplateManager
	{
		return $this->templateManager;
	}

	/**
	 * Register a custom template
	 */
	public function registerTemplate(TemplateInterface $template): self
	{
		$this->templateManager->register($template);
		return $this;
	}

	/**
	 * Get available templates
	 */
	public function getAvailableTemplates(): array
	{
		return $this->templateManager->getAvailableTemplates();
	}

    // ==================== Consent Methods ====================

	/**
	 * Get consent manager
	 */
	public function getConsentManager(): ConsentManager
	{
		return $this->consentManager;
	}

	/**
	 * Check if user has given consent
	 */
	public function hasConsent(): bool
	{
		return $this->consentManager->hasConsent();
	}

	/**
	 * Check consent for specific category
	 */
	public function hasConsentFor(string $category): bool
	{
		return $this->consentManager->hasConsentFor($category);
	}

	/**
	 * Get current consent data
	 */
	public function getConsent(): ?ConsentData
	{
		return $this->consentManager->getCurrentConsent();
	}

	/**
	 * Set user identifier to associate consent with a logged-in user
	 *
	 * @param string|null $identifier User ID, email hash, or any unique identifier
	 */
	public function setUserIdentifier(?string $identifier): self
	{
		$this->consentManager->setUserIdentifier($identifier);
		return $this;
	}

	/**
	 * Get the current user identifier
	 */
	public function getUserIdentifier(): ?string
	{
		return $this->consentManager->getUserIdentifier();
	}

	/**
	 * Give consent programmatically
	 */
	public function giveConsent(array $categories, string $method = 'api'): ConsentData
	{
		return $this->consentManager->giveConsent($categories, $method);
	}

	/**
	 * Accept all cookies
	 */
	public function acceptAll(string $method = 'api'): ConsentData
	{
		return $this->consentManager->acceptAll($method);
	}

	/**
	 * Reject all optional cookies
	 */
	public function rejectAll(string $method = 'api'): ConsentData
	{
		return $this->consentManager->rejectAll($method);
	}

	/**
	 * Withdraw consent
	 */
	public function withdrawConsent(): void
	{
		$this->consentManager->withdrawConsent();
	}

	/**
	 * Check if banner should be shown
	 */
	public function shouldShowBanner(): bool
	{
		return $this->consentManager->shouldShowBanner();
	}

    // ==================== Script Blocker Methods ====================

	/**
	 * Get script blocker
	 */
	public function getScriptBlocker(): ScriptBlocker
	{
		return $this->scriptBlocker;
	}

	/**
	 * Register a script
	 */
	public function registerScript(
		string $id,
		string $category,
		string $script,
		?string $provider = null,
		array $attributes = []
	): self {
		$this->scriptBlocker->registerScript($id, $category, $script, $provider, $attributes);
		return $this;
	}

	/**
	 * Register a provider
	 */
	public function registerProvider(string $name, string $category, array $patterns = []): self
	{
		$this->scriptBlocker->registerProvider($name, $category, ['patterns' => $patterns]);
		return $this;
	}

	/**
	 * Render a registered script (conditionally based on consent)
	 */
	public function renderScript(string $id): string
	{
		return $this->scriptBlocker->renderScript($id);
	}

	/**
	 * Render all registered scripts
	 */
	public function renderAllScripts(): string
	{
		return $this->scriptBlocker->renderAllScripts();
	}

    // ==================== Render Methods ====================

	/**
	 * Render the cookie banner HTML
	 */
	public function render(): string
	{
		$template = $this->config->getTemplate();
		$translations = $this->translationManager->getAll();
		$consentData = $this->consentManager->getConsentForJavaScript();

		// Dispatch before render event
		$event = new BannerEvent(BannerEvent::BEFORE_RENDER, [
			'template' => $template,
			'translations' => $translations,
			'consent' => $consentData,
		]);
		$this->eventDispatcher->dispatch($event);

		// Render template
		$html = $this->templateManager->render($template, $this->config, $translations, $consentData);

		// Dispatch after render event
		$afterEvent = new BannerEvent(BannerEvent::AFTER_RENDER, [], $html);
		$this->eventDispatcher->dispatch($afterEvent);

		// Allow modification via event
		if ($afterEvent->getHtml() !== null) {
			$html = $afterEvent->getHtml();
		}

		return $html;
	}

	/**
	 * Render CSS (inline or link tag)
	 */
	public function renderCss(): string
	{
		$template = $this->config->getTemplate();

		if ($this->inlineAssets) {
			$css = $this->templateManager->getCssContent($template);
			if ($css) {
				return "<style id=\"havax-cb-cookie-banner-css\">\n{$css}\n</style>";
			}
			return '';
		}

		if ($this->assetsUrl) {
			return sprintf(
				'<link rel="stylesheet" href="%s/css/%s.css" id="havax-cb-cookie-banner-css">',
				htmlspecialchars($this->assetsUrl),
				htmlspecialchars($template)
			);
		}

		// Fallback: inline CSS
		$css = $this->templateManager->getCssContent($template);
		if ($css) {
			return "<style id=\"havax-cb-cookie-banner-css\">\n{$css}\n</style>";
		}

		return '';
	}

	/**
	 * Render JavaScript (inline or script tag)
	 */
	public function renderJs(): string
	{
		$jsConfig = $this->getJavaScriptConfig();
		$configJson = json_encode($jsConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

		$output = "<script>window.havaxCbConfig = {$configJson};</script>\n";

		if ($this->inlineAssets) {
			$jsPath = $this->findAssetFile('js/cookiebanner.js');
			if ($jsPath && file_exists($jsPath)) {
				$js = file_get_contents($jsPath);
				$output .= "<script id=\"havax-cb-cookie-banner-js\">\n{$js}\n</script>";
			}
		} elseif ($this->assetsUrl) {
			$output .= sprintf(
				'<script src="%s/js/cookiebanner.js" id="havax-cb-cookie-banner-js"></script>',
				htmlspecialchars($this->assetsUrl)
			);
		} else {
			// Fallback: inline JS (check custom path first, then default)
			$jsPath = $this->findAssetFile('js/cookiebanner.js');
			if ($jsPath && file_exists($jsPath)) {
				$js = file_get_contents($jsPath);
				$output .= "<script id=\"havax-cb-cookie-banner-js\">\n{$js}\n</script>";
			}
		}

		return $output;
	}

	/**
	 * Get JavaScript configuration
	 */
	public function getJavaScriptConfig(): array
	{
		return [
			'cookieName' => $this->config->getCookieName(),
			'cookieExpiry' => $this->config->getCookieExpiry(),
			'cookiePath' => $this->config->getCookiePath(),
			'cookieDomain' => $this->config->getCookieDomain(),
			'cookieSecure' => $this->config->isCookieSecure(),
			'cookieSameSite' => $this->config->isCookieSameSite() ? 'Lax' : 'None',
			'autoBlock' => $this->config->isAutoBlock(),
			'blockingMode' => $this->config->isBlockingMode(),
			'categories' => $this->config->getCategories(),
			'blockerPatterns' => $this->scriptBlocker->getJavaScriptBlockerConfig()['patterns'],
			'consent' => $this->consentManager->getConsentForJavaScript(),
			'showBanner' => $this->shouldShowBanner(),
			'apiUrl' => $this->apiUrl,
		];
	}

	/**
	 * Set API URL for JavaScript to send consent data
	 */
	public function setApiUrl(?string $url): self
	{
		$this->apiUrl = $url;
		return $this;
	}

	/**
	 * Get API URL
	 */
	public function getApiUrl(): ?string
	{
		return $this->apiUrl;
	}

	/**
	 * Render everything (CSS + HTML + JS)
	 */
	public function renderAll(): string
	{
		$output = '';
		$output .= $this->renderCss() . "\n";
		$output .= $this->render() . "\n";
		$output .= $this->renderJs() . "\n";
		return $output;
	}

	/**
	 * Set assets URL (for CDN or custom path)
	 */
	public function setAssetsUrl(string $url): self
	{
		$this->assetsUrl = rtrim($url, '/');
		return $this;
	}

	/**
	 * Enable/disable inline assets
	 */
	public function setInlineAssets(bool $inline): self
	{
		$this->inlineAssets = $inline;
		return $this;
	}

    // ==================== API Endpoint Helper ====================

	/**
	 * Handle API request (for AJAX consent management)
	 */
	public function handleApiRequest(array $request): array
	{
		$action = $request['action'] ?? '';
		$response = ['success' => false];

		switch ($action) {
			case 'get_consent':
				$response = [
					'success' => true,
					'data' => $this->consentManager->getConsentForJavaScript(),
				];
				break;

			case 'give_consent':
				$categories = $request['categories'] ?? [];
				$method = $request['method'] ?? 'api';
				$metadata = $request['metadata'] ?? [];
				$previousConsent = $request['previous_consent'] ?? null;

				if (!empty($categories)) {
					$consent = $this->consentManager->giveConsent($categories, $method, $metadata, $previousConsent);
					$response = [
						'success' => true,
						'data' => $consent->toArray(),
						'cookie' => $this->consentManager->generateConsentCookieValue(),
						'cookieSettings' => $this->consentManager->getCookieSettings(),
					];
				}
				break;

			case 'accept_all':
				$method = $request['method'] ?? 'api';
				$metadata = $request['metadata'] ?? [];
				$consent = $this->consentManager->acceptAll($method, $metadata);
				$response = [
					'success' => true,
					'data' => $consent->toArray(),
					'cookie' => $this->consentManager->generateConsentCookieValue(),
					'cookieSettings' => $this->consentManager->getCookieSettings(),
				];
				break;

			case 'reject_all':
				$method = $request['method'] ?? 'api';
				$metadata = $request['metadata'] ?? [];
				$consent = $this->consentManager->rejectAll($method, $metadata);
				$response = [
					'success' => true,
					'data' => $consent->toArray(),
					'cookie' => $this->consentManager->generateConsentCookieValue(),
					'cookieSettings' => $this->consentManager->getCookieSettings(),
				];
				break;

			case 'withdraw_consent':
				$metadata = $request['metadata'] ?? [];
				$previousConsent = $request['previous_consent'] ?? null;
				$this->consentManager->withdrawConsent($metadata, $previousConsent);
				$response = [
					'success' => true,
					'data' => ['withdrawn' => true],
				];
				break;

			default:
				$response = [
					'success' => false,
					'error' => 'Unknown action',
				];
		}

		return $response;
	}
}
