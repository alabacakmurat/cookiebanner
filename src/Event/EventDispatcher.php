<?php

declare(strict_types=1);

namespace Havax\CookieBanner\Event;

class EventDispatcher
{
	private array $listeners = [];
	private array $wildcardListeners = [];

	public function on(string $eventName, callable $callback, int $priority = 0): self
	{
		if ($eventName === '*') {
			$this->wildcardListeners[] = [
				'callback' => $callback,
				'priority' => $priority,
			];
			usort($this->wildcardListeners, fn($a, $b) => $b['priority'] <=> $a['priority']);
		} else {
			if (!isset($this->listeners[$eventName])) {
				$this->listeners[$eventName] = [];
			}
			$this->listeners[$eventName][] = [
				'callback' => $callback,
				'priority' => $priority,
			];
			usort($this->listeners[$eventName], fn($a, $b) => $b['priority'] <=> $a['priority']);
		}

		return $this;
	}

	public function off(string $eventName, ?callable $callback = null): self
	{
		if ($eventName === '*') {
			if ($callback === null) {
				$this->wildcardListeners = [];
			} else {
				$this->wildcardListeners = array_filter(
					$this->wildcardListeners,
					fn($listener) => $listener['callback'] !== $callback
				);
			}
		} else {
			if ($callback === null) {
				unset($this->listeners[$eventName]);
			} elseif (isset($this->listeners[$eventName])) {
				$this->listeners[$eventName] = array_filter(
					$this->listeners[$eventName],
					fn($listener) => $listener['callback'] !== $callback
				);
			}
		}

		return $this;
	}

	public function once(string $eventName, callable $callback, int $priority = 0): self
	{
		$wrapper = function (EventInterface $event) use ($eventName, $callback, &$wrapper) {
			$this->off($eventName, $wrapper);
			return $callback($event);
		};

		return $this->on($eventName, $wrapper, $priority);
	}

	public function dispatch(EventInterface $event): EventInterface
	{
		$eventName = $event->getName();

		// Dispatch to wildcard listeners first
		foreach ($this->wildcardListeners as $listener) {
			if ($event->isPropagationStopped()) {
				break;
			}
			$listener['callback']($event);
		}

		// Then dispatch to specific listeners
		if (isset($this->listeners[$eventName])) {
			foreach ($this->listeners[$eventName] as $listener) {
				if ($event->isPropagationStopped()) {
					break;
				}
				$listener['callback']($event);
			}
		}

		return $event;
	}

	public function hasListeners(string $eventName): bool
	{
		return !empty($this->listeners[$eventName]) || !empty($this->wildcardListeners);
	}

	public function getListeners(string $eventName): array
	{
		$listeners = $this->wildcardListeners;

		if (isset($this->listeners[$eventName])) {
			$listeners = array_merge($listeners, $this->listeners[$eventName]);
		}

		usort($listeners, fn($a, $b) => $b['priority'] <=> $a['priority']);

		return array_map(fn($l) => $l['callback'], $listeners);
	}

	public function getEventNames(): array
	{
		return array_keys($this->listeners);
	}

	public function clearListeners(): self
	{
		$this->listeners = [];
		$this->wildcardListeners = [];
		return $this;
	}
}
