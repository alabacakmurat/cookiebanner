<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Template\Templates;

use Chronex\CookieBanner\Template\AbstractTemplate;

class ModernTemplate extends AbstractTemplate
{
	public function getName(): string
	{
		return 'modern';
	}

	public function getDescription(): string
	{
		return 'Modern card-style banner with rounded corners and shadow';
	}

	public function getTemplateFile(): string
	{
		return 'modern.php';
	}

	public function getPositions(): array
	{
		return ['bottom-left', 'bottom-right', 'top-left', 'top-right', 'center'];
	}
}
