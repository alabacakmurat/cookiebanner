<?php

declare(strict_types=1);

namespace Havax\CookieBanner\Template\Templates;

use Havax\CookieBanner\Template\AbstractTemplate;

class ClassicTemplate extends AbstractTemplate
{
	public function getName(): string
	{
		return 'classic';
	}

	public function getDescription(): string
	{
		return 'Classic full-width banner at top or bottom of the page';
	}

	public function getTemplateFile(): string
	{
		return 'classic.php';
	}

	public function getPositions(): array
	{
		return ['top', 'bottom'];
	}
}
