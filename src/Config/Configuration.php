<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Config;

use InvalidArgumentException;

class Configuration
{
	private string $template = 'modern';
	private string $position = 'bottom';
	private string $language = 'en';
	private string $cookieName = 'chronex_cb_consent';
	private int $cookieExpiry = 365;
	private string $cookiePath = '/';
	private string $cookieDomain = '';
	private bool $cookieSecure = true;
	private bool $cookieSameSite = true;
	private array $categories = [];
	private array $translations = [];
	private bool $autoBlock = true;
	private bool $respectDoNotTrack = false;
	private bool $showOnlyOnce = false;
	private string $assetsPath = '';
	private string $templatesPath = '';
	private ?string $privacyPolicyUrl = null;
	private ?string $cookiePolicyUrl = null;
	private bool $showPreferencesButton = true;
	private string $hashAlgorithm = 'sha256';
	private bool $collectAnonymousStats = false;
	private bool $blockingMode = false;
	private ?string $blockingMessage = null;

	public function __construct(array $config = [])
	{
		$this->setDefaults();
		$this->applyConfig($config);
	}

	private function setDefaults(): void
	{
		$this->assetsPath = dirname(__DIR__, 2) . '/assets';
		$this->templatesPath = dirname(__DIR__, 2) . '/templates';

		$this->categories = [
			'necessary' => [
				'enabled' => true,
				'required' => true,
				'default' => true,
				'title' => 'Necessary',
				'description' => 'Essential cookies required for the website to function properly.',
				'scripts' => [],
			],
			'functional' => [
				'enabled' => true,
				'required' => false,
				'default' => false,
				'title' => 'Functional',
				'description' => 'Cookies that enhance website functionality and personalization.',
				'scripts' => [],
			],
			'analytics' => [
				'enabled' => true,
				'required' => false,
				'default' => false,
				'title' => 'Analytics',
				'description' => 'Cookies used to analyze website traffic and user behavior.',
				'scripts' => [],
			],
			'marketing' => [
				'enabled' => true,
				'required' => false,
				'default' => false,
				'title' => 'Marketing',
				'description' => 'Cookies used for marketing and email campaigns.',
				'scripts' => [],
			],
			'advertising' => [
				'enabled' => true,
				'required' => false,
				'default' => false,
				'title' => 'Advertising',
				'description' => 'Cookies used to display personalized advertisements.',
				'scripts' => [],
			],
		];
	}

	private function applyConfig(array $config): void
	{
		foreach ($config as $key => $value) {
			$method = 'set' . str_replace('_', '', ucwords($key, '_'));
			if (method_exists($this, $method)) {
				$this->$method($value);
			} elseif (property_exists($this, $key)) {
				$this->$key = $value;
			}
		}
	}

	public function getTemplate(): string
	{
		return $this->template;
	}

	public function setTemplate(string $template): self
	{
		$this->template = $template;
		return $this;
	}

	public function getPosition(): string
	{
		return $this->position;
	}

	public function setPosition(string $position): self
	{
		$validPositions = ['top', 'bottom', 'top-left', 'top-right', 'bottom-left', 'bottom-right', 'center'];
		if (!in_array($position, $validPositions)) {
			throw new InvalidArgumentException("Invalid position: {$position}. Valid: " . implode(', ', $validPositions));
		}
		$this->position = $position;
		return $this;
	}

	public function getLanguage(): string
	{
		return $this->language;
	}

	public function setLanguage(string $language): self
	{
		$this->language = $language;
		return $this;
	}

	public function getCookieName(): string
	{
		return $this->cookieName;
	}

	public function setCookieName(string $cookieName): self
	{
		$this->cookieName = $cookieName;
		return $this;
	}

	public function getCookieExpiry(): int
	{
		return $this->cookieExpiry;
	}

	public function setCookieExpiry(int $days): self
	{
		$this->cookieExpiry = $days;
		return $this;
	}

	public function getCookiePath(): string
	{
		return $this->cookiePath;
	}

	public function setCookiePath(string $path): self
	{
		$this->cookiePath = $path;
		return $this;
	}

	public function getCookieDomain(): string
	{
		return $this->cookieDomain;
	}

	public function setCookieDomain(string $domain): self
	{
		$this->cookieDomain = $domain;
		return $this;
	}

	public function isCookieSecure(): bool
	{
		return $this->cookieSecure;
	}

	public function setCookieSecure(bool $secure): self
	{
		$this->cookieSecure = $secure;
		return $this;
	}

	public function isCookieSameSite(): bool
	{
		return $this->cookieSameSite;
	}

	public function setCookieSameSite(bool $sameSite): self
	{
		$this->cookieSameSite = $sameSite;
		return $this;
	}

	public function getCategories(): array
	{
		return $this->categories;
	}

	public function setCategories(array $categories): self
	{
		foreach ($categories as $key => $category) {
			if (isset($this->categories[$key])) {
				$this->categories[$key] = array_merge($this->categories[$key], $category);
			} else {
				$this->categories[$key] = array_merge([
					'enabled' => true,
					'required' => false,
					'default' => false,
					'title' => ucfirst($key),
					'description' => '',
					'scripts' => [],
				], $category);
			}
		}
		return $this;
	}

	public function addCategory(string $key, array $config): self
	{
		$this->categories[$key] = array_merge([
			'enabled' => true,
			'required' => false,
			'default' => false,
			'title' => ucfirst($key),
			'description' => '',
			'scripts' => [],
		], $config);
		return $this;
	}

	public function removeCategory(string $key): self
	{
		if ($key !== 'necessary') {
			unset($this->categories[$key]);
		}
		return $this;
	}

	public function getCategory(string $key): ?array
	{
		return $this->categories[$key] ?? null;
	}

	public function getTranslations(): array
	{
		return $this->translations;
	}

	public function setTranslations(array $translations): self
	{
		$this->translations = $translations;
		return $this;
	}

	public function isAutoBlock(): bool
	{
		return $this->autoBlock;
	}

	public function setAutoBlock(bool $autoBlock): self
	{
		$this->autoBlock = $autoBlock;
		return $this;
	}

	public function isRespectDoNotTrack(): bool
	{
		return $this->respectDoNotTrack;
	}

	public function setRespectDoNotTrack(bool $respect): self
	{
		$this->respectDoNotTrack = $respect;
		return $this;
	}

	public function isShowOnlyOnce(): bool
	{
		return $this->showOnlyOnce;
	}

	public function setShowOnlyOnce(bool $showOnlyOnce): self
	{
		$this->showOnlyOnce = $showOnlyOnce;
		return $this;
	}

	public function getAssetsPath(): string
	{
		return $this->assetsPath;
	}

	public function setAssetsPath(string $path): self
	{
		$this->assetsPath = rtrim($path, '/\\');
		return $this;
	}

	public function getTemplatesPath(): string
	{
		return $this->templatesPath;
	}

	public function setTemplatesPath(string $path): self
	{
		$this->templatesPath = rtrim($path, '/\\');
		return $this;
	}

	public function getPrivacyPolicyUrl(): ?string
	{
		return $this->privacyPolicyUrl;
	}

	public function setPrivacyPolicyUrl(?string $url): self
	{
		$this->privacyPolicyUrl = $url;
		return $this;
	}

	public function getCookiePolicyUrl(): ?string
	{
		return $this->cookiePolicyUrl;
	}

	public function setCookiePolicyUrl(?string $url): self
	{
		$this->cookiePolicyUrl = $url;
		return $this;
	}

	public function isShowPreferencesButton(): bool
	{
		return $this->showPreferencesButton;
	}

	public function setShowPreferencesButton(bool $show): self
	{
		$this->showPreferencesButton = $show;
		return $this;
	}

	public function getHashAlgorithm(): string
	{
		return $this->hashAlgorithm;
	}

	public function setHashAlgorithm(string $algorithm): self
	{
		$this->hashAlgorithm = $algorithm;
		return $this;
	}

	public function isCollectAnonymousStats(): bool
	{
		return $this->collectAnonymousStats;
	}

	public function setCollectAnonymousStats(bool $collect): self
	{
		$this->collectAnonymousStats = $collect;
		return $this;
	}

	public function isBlockingMode(): bool
	{
		return $this->blockingMode;
	}

	public function setBlockingMode(bool $blocking): self
	{
		$this->blockingMode = $blocking;
		return $this;
	}

	public function getBlockingMessage(): ?string
	{
		return $this->blockingMessage;
	}

	public function setBlockingMessage(?string $message): self
	{
		$this->blockingMessage = $message;
		return $this;
	}

	public function toArray(): array
	{
		return [
			'template' => $this->template,
			'position' => $this->position,
			'language' => $this->language,
			'cookieName' => $this->cookieName,
			'cookieExpiry' => $this->cookieExpiry,
			'cookiePath' => $this->cookiePath,
			'cookieDomain' => $this->cookieDomain,
			'cookieSecure' => $this->cookieSecure,
			'cookieSameSite' => $this->cookieSameSite,
			'categories' => $this->categories,
			'autoBlock' => $this->autoBlock,
			'respectDoNotTrack' => $this->respectDoNotTrack,
			'showOnlyOnce' => $this->showOnlyOnce,
			'privacyPolicyUrl' => $this->privacyPolicyUrl,
			'cookiePolicyUrl' => $this->cookiePolicyUrl,
			'showPreferencesButton' => $this->showPreferencesButton,
			'hashAlgorithm' => $this->hashAlgorithm,
			'blockingMode' => $this->blockingMode,
			'blockingMessage' => $this->blockingMessage,
		];
	}
}
