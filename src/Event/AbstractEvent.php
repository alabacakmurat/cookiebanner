<?php

declare(strict_types=1);

namespace Havax\CookieBanner\Event;

abstract class AbstractEvent implements EventInterface
{
	protected bool $propagationStopped = false;

	abstract public function getName(): string;

	public function stopPropagation(): void
	{
		$this->propagationStopped = true;
	}

	public function isPropagationStopped(): bool
	{
		return $this->propagationStopped;
	}
}
