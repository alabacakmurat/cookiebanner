<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Template\Templates;

use Chronex\CookieBanner\Template\AbstractTemplate;

class FloatingTemplate extends AbstractTemplate
{
	public function getName(): string
	{
		return 'floating';
	}

	public function getDescription(): string
	{
		return 'Floating button that expands to show cookie options';
	}

	public function getTemplateFile(): string
	{
		return 'floating.php';
	}

	public function getPositions(): array
	{
		return ['bottom-left', 'bottom-right'];
	}
}
