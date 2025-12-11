<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Language;

class TranslationManager
{
	private string $defaultLanguage = 'en';
	private string $currentLanguage;
	private array $translations = [];
	private array $languageFiles = [];
	private string $defaultLanguagesPath;
	private ?string $customLanguagesPath = null;

	public function __construct(?string $customLanguagesPath = null, string $language = 'en')
	{
		$this->defaultLanguagesPath = dirname(__DIR__, 2) . '/languages';
		$this->customLanguagesPath = $customLanguagesPath;
		$this->currentLanguage = $language;
		$this->loadDefaultLanguages();
	}

	private function loadDefaultLanguages(): void
	{
		// Load from default library language files
		$this->loadLanguageFilesFromPath($this->defaultLanguagesPath);

		// Load from custom language files (merge/override)
		if ($this->customLanguagesPath) {
			$this->loadLanguageFilesFromPath($this->customLanguagesPath);
		}
	}

	private function loadLanguageFilesFromPath(string $path): void
	{
		if (!is_dir($path)) {
			return;
		}

		// Load PHP files
		$files = glob($path . '/*.php');
		foreach ($files as $file) {
			$lang = basename($file, '.php');
			$translations = include $file;
			if (is_array($translations)) {
				$this->registerLanguage($lang, $translations, true); // Always merge
			}
		}

		// Load JSON files
		$jsonFiles = glob($path . '/*.json');
		foreach ($jsonFiles as $file) {
			$lang = basename($file, '.json');
			$translations = json_decode(file_get_contents($file), true);
			if (is_array($translations)) {
				$this->registerLanguage($lang, $translations, true); // Always merge
			}
		}
	}

	public function registerLanguage(string $code, array $translations, bool $merge = false): self
	{
		if ($merge && isset($this->translations[$code])) {
			$this->translations[$code] = array_merge($this->translations[$code], $translations);
		} else {
			$this->translations[$code] = $translations;
		}
		return $this;
	}

	public function setLanguage(string $code): self
	{
		$this->currentLanguage = $code;
		return $this;
	}

	public function getLanguage(): string
	{
		return $this->currentLanguage;
	}

	public function getAvailableLanguages(): array
	{
		return array_keys($this->translations);
	}

	public function hasLanguage(string $code): bool
	{
		return isset($this->translations[$code]);
	}

	public function get(string $key, ?string $default = null, ?string $language = null): string
	{
		$lang = $language ?? $this->currentLanguage;

		// Try current language
		if (isset($this->translations[$lang][$key])) {
			return $this->translations[$lang][$key];
		}

		// Try language without region (e.g., en-US -> en)
		$baseLang = explode('-', $lang)[0];
		if ($baseLang !== $lang && isset($this->translations[$baseLang][$key])) {
			return $this->translations[$baseLang][$key];
		}

		// Try default language
		if (isset($this->translations[$this->defaultLanguage][$key])) {
			return $this->translations[$this->defaultLanguage][$key];
		}

		// Return default or key
		return $default ?? $key;
	}

	public function getAll(?string $language = null): array
	{
		$lang = $language ?? $this->currentLanguage;
		return $this->translations[$lang] ?? $this->translations[$this->defaultLanguage] ?? [];
	}

	public function extend(string $language, array $translations): self
	{
		if (!isset($this->translations[$language])) {
			$this->translations[$language] = [];
		}
		$this->translations[$language] = array_merge($this->translations[$language], $translations);
		return $this;
	}

	public function setDefaultLanguage(string $code): self
	{
		$this->defaultLanguage = $code;
		return $this;
	}
}
