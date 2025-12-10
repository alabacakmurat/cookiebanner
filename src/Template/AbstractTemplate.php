<?php

declare(strict_types=1);

namespace VkmToolkit\CookieBanner\Template;

use VkmToolkit\CookieBanner\Config\Configuration;

abstract class AbstractTemplate implements TemplateInterface
{
    protected string $defaultTemplatesPath;
    protected string $defaultAssetsPath;
    protected ?string $customTemplatesPath = null;
    protected ?string $customAssetsPath = null;

    public function __construct(?string $customTemplatesPath = null, ?string $customAssetsPath = null)
    {
        $this->defaultTemplatesPath = dirname(__DIR__, 2) . '/templates';
        $this->defaultAssetsPath = dirname(__DIR__, 2) . '/assets';
        $this->customTemplatesPath = $customTemplatesPath;
        $this->customAssetsPath = $customAssetsPath;
    }

    abstract public function getName(): string;
    abstract public function getDescription(): string;
    abstract public function getTemplateFile(): string;

    /**
     * Find a template file, checking custom path first, then default path
     */
    protected function findTemplateFile(string $filename): ?string
    {
        // Check custom path first
        if ($this->customTemplatesPath) {
            $customFile = $this->customTemplatesPath . '/' . $filename;
            if (file_exists($customFile)) {
                return $customFile;
            }
        }

        // Fall back to default path
        $defaultFile = $this->defaultTemplatesPath . '/' . $filename;
        if (file_exists($defaultFile)) {
            return $defaultFile;
        }

        return null;
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

    public function render(Configuration $config, array $translations, array $consentData): string
    {
        $templateFile = $this->findTemplateFile($this->getTemplateFile());

        if (!$templateFile) {
            throw new \RuntimeException("Template file not found: {$this->getTemplateFile()}");
        }

        $data = $this->prepareData($config, $translations, $consentData);

        ob_start();
        extract($data, EXTR_SKIP);
        include $templateFile;
        return ob_get_clean();
    }

    protected function prepareData(Configuration $config, array $translations, array $consentData): array
    {
        return [
            'config' => $config,
            'translations' => $translations,
            'consent' => $consentData,
            'categories' => $config->getCategories(),
            'position' => $config->getPosition(),
            'privacyPolicyUrl' => $config->getPrivacyPolicyUrl(),
            'cookiePolicyUrl' => $config->getCookiePolicyUrl(),
            'showPreferencesButton' => $config->isShowPreferencesButton(),
            'templateName' => $this->getName(),
            't' => fn(string $key, ?string $default = null) => $translations[$key] ?? $default ?? $key,
        ];
    }

    public function getCssFile(): ?string
    {
        return $this->findAssetFile('css/' . $this->getName() . '.css');
    }

    public function getJsFile(): ?string
    {
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

    public function getPositions(): array
    {
        return ['top', 'bottom', 'top-left', 'top-right', 'bottom-left', 'bottom-right', 'center'];
    }

    protected function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
