<?php

declare(strict_types=1);

namespace Havax\CookieBanner\Event;

class BannerEvent extends AbstractEvent
{
	public const BEFORE_RENDER = 'banner.before_render';
	public const AFTER_RENDER = 'banner.after_render';
	public const SHOWN = 'banner.shown';
	public const HIDDEN = 'banner.hidden';
	public const PREFERENCES_OPENED = 'banner.preferences_opened';
	public const PREFERENCES_CLOSED = 'banner.preferences_closed';

	private string $eventName;
	private array $data;
	private ?string $html = null;

	public function __construct(string $eventName, array $data = [], ?string $html = null)
	{
		$this->eventName = $eventName;
		$this->data = $data;
		$this->html = $html;
	}

	public function getName(): string
	{
		return $this->eventName;
	}

	public function getData(): array
	{
		return $this->data;
	}

	public function setData(array $data): self
	{
		$this->data = $data;
		return $this;
	}

	public function getHtml(): ?string
	{
		return $this->html;
	}

	public function setHtml(?string $html): self
	{
		$this->html = $html;
		return $this;
	}

	public function toArray(): array
	{
		return [
			'event_name' => $this->eventName,
			'data' => $this->data,
		];
	}
}
