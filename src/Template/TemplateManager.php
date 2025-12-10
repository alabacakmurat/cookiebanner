<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Template;

use Chronex\CookieBanner\Config\Configuration;
use Chronex\CookieBanner\Template\Templates\ClassicTemplate;
use Chronex\CookieBanner\Template\Templates\ModernTemplate;
use Chronex\CookieBanner\Template\Templates\MinimalTemplate;
use Chronex\CookieBanner\Template\Templates\FloatingTemplate;
use Chronex\CookieBanner\Template\Templates\BlockingTemplate;
use InvalidArgumentException;

class TemplateManager
{
	private array $templates = [];
	private string $defaultTemplatesPath;
	private string $defaultAssetsPath;
	private ?string $customTemplatesPath = null;
	private ?string $customAssetsPath = null;

	public function __construct(?string $customTemplatesPath = null, ?string $customAssetsPath = null)
	{
		$this->defaultTemplatesPath = dirname(__DIR__, 2) . '/templates';
		$this->defaultAssetsPath = dirname(__DIR__, 2) . '/assets';
		$this->customTemplatesPath = $customTemplatesPath;
		$this->customAssetsPath = $customAssetsPath;
		$this->registerDefaultTemplates();
	}

	private function registerDefaultTemplates(): void
	{
		$this->register(new ClassicTemplate($this->customTemplatesPath, $this->customAssetsPath));
		$this->register(new ModernTemplate($this->customTemplatesPath, $this->customAssetsPath));
		$this->register(new MinimalTemplate($this->customTemplatesPath, $this->customAssetsPath));
		$this->register(new FloatingTemplate($this->customTemplatesPath, $this->customAssetsPath));
		$this->register(new BlockingTemplate($this->customTemplatesPath, $this->customAssetsPath));
	}

	public function register(TemplateInterface $template): self
	{
		$this->templates[$template->getName()] = $template;
		return $this;
	}

	public function unregister(string $name): self
	{
		unset($this->templates[$name]);
		return $this;
	}

	public function get(string $name): TemplateInterface
	{
		if (!isset($this->templates[$name])) {
			throw new InvalidArgumentException(
				"Template '{$name}' not found. Available: " . implode(', ', $this->getAvailableTemplates())
			);
		}

		return $this->templates[$name];
	}

	public function has(string $name): bool
	{
		return isset($this->templates[$name]);
	}

	public function getAvailableTemplates(): array
	{
		return array_keys($this->templates);
	}

	public function getTemplateInfo(): array
	{
		$info = [];
		foreach ($this->templates as $name => $template) {
			$info[$name] = [
				'name' => $template->getName(),
				'description' => $template->getDescription(),
				'positions' => $template->getPositions(),
				'hasCss' => $template->getCssFile() !== null,
				'hasJs' => $template->getJsFile() !== null,
			];
		}
		return $info;
	}

	public function render(string $name, Configuration $config, array $translations, array $consentData): string
	{
		return $this->get($name)->render($config, $translations, $consentData);
	}

	public function getCssContent(string $name): ?string
	{
		$template = $this->get($name);
		$cssFile = $template->getCssFile();

		if ($cssFile && file_exists($cssFile)) {
			$css = '';

			// First, include base.css (check custom path first, then default)
			$baseCssFile = $this->findAssetFile('css/base.css');
			if ($baseCssFile && file_exists($baseCssFile)) {
				$css .= file_get_contents($baseCssFile) . "\n\n";
			}

			// Then include template-specific CSS (without @import)
			$templateCss = file_get_contents($cssFile);
			// Remove @import statements
			$templateCss = preg_replace('/@import\s+url\([\'"]?\.\/base\.css[\'"]?\);?\s*/', '', $templateCss);
			$css .= $templateCss;

			return $css;
		}

		return null;
	}

	/**
	 * Find an asset file, checking custom path first, then default path
	 */
	public function findAssetFile(string $relativePath): ?string
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

	/**
	 * Get the default assets path (library built-in)
	 */
	public function getDefaultAssetsPath(): string
	{
		return $this->defaultAssetsPath;
	}

	/**
	 * Get the custom assets path (user-provided)
	 */
	public function getCustomAssetsPath(): ?string
	{
		return $this->customAssetsPath;
	}

	public function getJsContent(string $name): ?string
	{
		$template = $this->get($name);
		$jsFile = $template->getJsFile();

		if ($jsFile && file_exists($jsFile)) {
			return file_get_contents($jsFile);
		}

		return null;
	}
}
