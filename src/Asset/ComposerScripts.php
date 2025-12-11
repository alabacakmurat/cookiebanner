<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Asset;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

/**
 * Composer Script Handler for Asset Publishing
 *
 * Usage in composer.json:
 * {
 *     "scripts": {
 *         "publish-assets": "Chronex\\CookieBanner\\Asset\\ComposerScripts::publishAssets"
 *     },
 *     "extra": {
 *         "cookiebanner": {
 *             "publish-path": "public/vendor/cookiebanner",
 *             "minify": false,
 *             "combined": false
 *         }
 *     }
 * }
 */
class ComposerScripts
{
    /**
     * Publish assets via Composer script
     */
    public static function publishAssets(Event $event): void
    {
        $io = $event->getIO();
        $composer = $event->getComposer();
        $extra = $composer->getPackage()->getExtra();

        // Get configuration from extra section
        $config = $extra['cookiebanner'] ?? [];
        $targetDir = $config['publish-path'] ?? 'public/vendor/cookiebanner';
        $minify = $config['minify'] ?? false;
        $combined = $config['combined'] ?? false;

        // Support command line arguments
        $arguments = $event->getArguments();
        foreach ($arguments as $arg) {
            if ($arg === '--minify' || $arg === '-m') {
                $minify = true;
            }
            if ($arg === '--combined' || $arg === '-c') {
                $combined = true;
            }
            if (strpos($arg, '--path=') === 0) {
                $targetDir = substr($arg, 7);
            }
        }

        // Make path absolute if relative
        if (!self::isAbsolutePath($targetDir)) {
            $targetDir = getcwd() . '/' . $targetDir;
        }

        $io->write('<info>Publishing CookieBanner assets...</info>');
        $io->write(sprintf('  Target: <comment>%s</comment>', $targetDir));
        $io->write(sprintf('  Minify: <comment>%s</comment>', $minify ? 'yes' : 'no'));
        $io->write(sprintf('  Combined: <comment>%s</comment>', $combined ? 'yes' : 'no'));

        try {
            $files = AssetPublisher::publish($targetDir, $minify, $combined);

            $io->write('<info>Published files:</info>');
            foreach ($files as $file) {
                $io->write(sprintf('  - %s', basename($file)));
            }

            $io->write(sprintf('<info>Successfully published %d file(s)!</info>', count($files)));
        } catch (\Exception $e) {
            $io->writeError(sprintf('<error>Failed to publish assets: %s</error>', $e->getMessage()));
        }
    }

    /**
     * Auto-publish on package install/update (optional hook)
     */
    public static function postInstall(PackageEvent $event): void
    {
        $package = $event->getOperation()->getPackage();

        if ($package->getName() === 'chronex/cookiebanner') {
            $io = $event->getIO();
            $io->write('<info>CookieBanner installed. Run "composer publish-assets" to publish assets.</info>');
        }
    }

    /**
     * Check if path is absolute
     */
    private static function isAbsolutePath(string $path): bool
    {
        // Windows absolute path
        if (preg_match('/^[A-Z]:\\\\/i', $path)) {
            return true;
        }
        // Unix absolute path
        if (str_starts_with($path, '/')) {
            return true;
        }
        return false;
    }
}
