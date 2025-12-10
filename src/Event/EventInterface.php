<?php

declare(strict_types=1);

namespace Havax\CookieBanner\Event;

interface EventInterface
{
	public function getName(): string;
	public function stopPropagation(): void;
	public function isPropagationStopped(): bool;
}
