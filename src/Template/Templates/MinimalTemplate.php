<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Template\Templates;

use Chronex\CookieBanner\Template\AbstractTemplate;

class MinimalTemplate extends AbstractTemplate
{
	public function getName(): string
	{
		return 'minimal';
	}

	public function getDescription(): string
	{
		return 'Minimalistic small popup with essential options only';
	}

	public function getTemplateFile(): string
	{
		return 'minimal.php';
	}

	public function getPositions(): array
	{
		return ['bottom-left', 'bottom-right', 'top-left', 'top-right'];
	}
}
