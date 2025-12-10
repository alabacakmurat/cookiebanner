<?php

declare(strict_types=1);

namespace VkmToolkit\CookieBanner\Template\Templates;

use VkmToolkit\CookieBanner\Template\AbstractTemplate;

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
