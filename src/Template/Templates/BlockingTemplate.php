<?php

declare(strict_types=1);

namespace Havax\CookieBanner\Template\Templates;

use Havax\CookieBanner\Template\AbstractTemplate;

class BlockingTemplate extends AbstractTemplate
{
	public function getName(): string
	{
		return 'blocking';
	}

	public function getDescription(): string
	{
		return 'Full-screen blocking overlay that prevents site access until cookies are accepted';
	}

	public function getTemplateFile(): string
	{
		return 'blocking.php';
	}

	public function getPositions(): array
	{
		// Blocking mode only supports center position
		return ['center'];
	}
}
