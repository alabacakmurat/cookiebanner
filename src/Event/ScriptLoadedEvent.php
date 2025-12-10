<?php

declare(strict_types=1);

namespace VkmToolkit\CookieBanner\Event;

class ScriptLoadedEvent extends AbstractEvent
{
    public const NAME = 'script.loaded';

    private string $scriptId;
    private string $category;
    private string $provider;
    private array $metadata;

    public function __construct(
        string $scriptId,
        string $category,
        string $provider = '',
        array $metadata = []
    ) {
        $this->scriptId = $scriptId;
        $this->category = $category;
        $this->provider = $provider;
        $this->metadata = $metadata;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getScriptId(): string
    {
        return $this->scriptId;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function toArray(): array
    {
        return [
            'script_id' => $this->scriptId,
            'category' => $this->category,
            'provider' => $this->provider,
            'metadata' => $this->metadata,
        ];
    }
}
