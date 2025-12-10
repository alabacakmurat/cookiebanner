<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Template;

use Chronex\CookieBanner\Config\Configuration;

interface TemplateInterface
{
	public function getName(): string;
	public function getDescription(): string;
	public function render(Configuration $config, array $translations, array $consentData): string;
	public function getCssFile(): ?string;
	public function getJsFile(): ?string;
	public function getPositions(): array;
}
