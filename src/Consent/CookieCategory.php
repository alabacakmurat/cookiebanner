<?php

declare(strict_types=1);

namespace Havax\CookieBanner\Consent;

enum CookieCategory: string
{
	case NECESSARY = 'necessary';
	case FUNCTIONAL = 'functional';
	case ANALYTICS = 'analytics';
	case MARKETING = 'marketing';
	case ADVERTISING = 'advertising';

	public function isRequired(): bool
	{
		return $this === self::NECESSARY;
	}

	public function getDefaultTitle(): string
	{
		return match ($this) {
			self::NECESSARY => 'Necessary',
			self::FUNCTIONAL => 'Functional',
			self::ANALYTICS => 'Analytics',
			self::MARKETING => 'Marketing',
			self::ADVERTISING => 'Advertising',
		};
	}

	public function getDefaultDescription(): string
	{
		return match ($this) {
			self::NECESSARY => 'Essential cookies required for the website to function properly.',
			self::FUNCTIONAL => 'Cookies that enhance website functionality and personalization.',
			self::ANALYTICS => 'Cookies used to analyze website traffic and user behavior.',
			self::MARKETING => 'Cookies used for marketing and email campaigns.',
			self::ADVERTISING => 'Cookies used to display personalized advertisements.',
		};
	}

	public static function fromString(string $value): ?self
	{
		foreach (self::cases() as $case) {
			if ($case->value === strtolower($value)) {
				return $case;
			}
		}
		return null;
	}

	public static function getAll(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
	}
}
