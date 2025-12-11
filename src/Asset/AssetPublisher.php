<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Asset;

/**
 * Asset Publisher - Publishes cookie banner assets to a target directory
 */
class AssetPublisher
{
    private string $sourceDir;
    private string $targetDir;
    private bool $minify;
    private array $publishedFiles = [];

    public function __construct(string $targetDir, bool $minify = false)
    {
        $this->sourceDir = dirname(__DIR__, 2) . '/assets';
        $this->targetDir = rtrim($targetDir, '/\\');
        $this->minify = $minify;
    }

    /**
     * Publish all assets (CSS and JS)
     */
    public function publishAll(): array
    {
        $this->publishedFiles = [];

        $this->ensureDirectory($this->targetDir);
        $this->ensureDirectory($this->targetDir . '/css');
        $this->ensureDirectory($this->targetDir . '/js');

        $this->publishCss();
        $this->publishJs();

        return $this->publishedFiles;
    }

    /**
     * Publish only CSS files
     */
    public function publishCss(): array
    {
        $cssDir = $this->sourceDir . '/css';
        $targetCssDir = $this->targetDir . '/css';

        $this->ensureDirectory($targetCssDir);

        foreach (glob($cssDir . '/*.css') as $file) {
            $filename = basename($file);
            $content = file_get_contents($file);

            if ($this->minify) {
                $content = $this->minifyCss($content);
                $filename = str_replace('.css', '.min.css', $filename);
            }

            $targetPath = $targetCssDir . '/' . $filename;
            file_put_contents($targetPath, $content);
            $this->publishedFiles[] = $targetPath;
        }

        return $this->publishedFiles;
    }

    /**
     * Publish only JS files
     */
    public function publishJs(): array
    {
        $jsDir = $this->sourceDir . '/js';
        $targetJsDir = $this->targetDir . '/js';

        $this->ensureDirectory($targetJsDir);

        foreach (glob($jsDir . '/*.js') as $file) {
            $filename = basename($file);
            $content = file_get_contents($file);

            if ($this->minify) {
                $content = $this->minifyJs($content);
                $filename = str_replace('.js', '.min.js', $filename);
            }

            $targetPath = $targetJsDir . '/' . $filename;
            file_put_contents($targetPath, $content);
            $this->publishedFiles[] = $targetPath;
        }

        return $this->publishedFiles;
    }

    /**
     * Publish a combined CSS file (all templates in one)
     */
    public function publishCombinedCss(string $filename = 'cookiebanner.css'): string
    {
        $cssDir = $this->sourceDir . '/css';
        $this->ensureDirectory($this->targetDir . '/css');

        // Load base first, then templates
        $combined = '';
        $baseFile = $cssDir . '/base.css';

        if (file_exists($baseFile)) {
            $combined .= "/* Base Styles */\n" . file_get_contents($baseFile) . "\n\n";
        }

        $templates = ['classic', 'modern', 'floating', 'minimal', 'blocking'];
        foreach ($templates as $template) {
            $templateFile = $cssDir . '/' . $template . '.css';
            if (file_exists($templateFile)) {
                $combined .= "/* {$template} Template */\n" . file_get_contents($templateFile) . "\n\n";
            }
        }

        if ($this->minify) {
            $combined = $this->minifyCss($combined);
            $filename = str_replace('.css', '.min.css', $filename);
        }

        $targetPath = $this->targetDir . '/css/' . $filename;
        file_put_contents($targetPath, $combined);
        $this->publishedFiles[] = $targetPath;

        return $targetPath;
    }

    /**
     * Get list of available CSS templates
     */
    public function getAvailableTemplates(): array
    {
        $templates = [];
        $cssDir = $this->sourceDir . '/css';

        foreach (glob($cssDir . '/*.css') as $file) {
            $name = basename($file, '.css');
            if ($name !== 'base') {
                $templates[] = $name;
            }
        }

        return $templates;
    }

    /**
     * Simple CSS minification (removes comments, whitespace)
     */
    private function minifyCss(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Remove whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        // Remove whitespace around special characters
        $css = preg_replace('/\s*([\{\};:,>~+])\s*/', '$1', $css);
        // Remove trailing semicolons before closing braces
        $css = str_replace(';}', '}', $css);

        return trim($css);
    }

    /**
     * Simple JS minification (removes comments, excess whitespace)
     * Note: For production, consider using terser or uglify-js
     */
    private function minifyJs(string $js): string
    {
        // Remove single-line comments (but not URLs)
        $js = preg_replace('#(?<!:)//[^\n]*#', '', $js);
        // Remove multi-line comments
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);
        // Remove excess whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        // Remove whitespace around operators (basic)
        $js = preg_replace('/\s*([{};,=:+\-*\/&|<>!?])\s*/', '$1', $js);
        // Restore necessary spaces
        $js = preg_replace('/(return|var|let|const|function|if|else|for|while|typeof|instanceof|new|throw|catch|try|finally)([^\s\w])/', '$1 $2', $js);

        return trim($js);
    }

    /**
     * Ensure directory exists
     */
    private function ensureDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Get source directory path
     */
    public function getSourceDir(): string
    {
        return $this->sourceDir;
    }

    /**
     * Get target directory path
     */
    public function getTargetDir(): string
    {
        return $this->targetDir;
    }

    /**
     * Get list of published files
     */
    public function getPublishedFiles(): array
    {
        return $this->publishedFiles;
    }

    /**
     * Static helper for quick publishing from Composer scripts
     */
    public static function publish(string $targetDir, bool $minify = false, bool $combined = false): array
    {
        $publisher = new self($targetDir, $minify);

        if ($combined) {
            $publisher->publishCombinedCss();
            $publisher->publishJs();
            return $publisher->getPublishedFiles();
        }

        return $publisher->publishAll();
    }
}
