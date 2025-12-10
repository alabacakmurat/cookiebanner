<?php

declare(strict_types=1);

namespace VkmToolkit\CookieBanner\Event;

interface EventInterface
{
    public function getName(): string;
    public function stopPropagation(): void;
    public function isPropagationStopped(): bool;
}
