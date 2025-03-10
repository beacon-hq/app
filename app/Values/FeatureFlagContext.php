<?php

declare(strict_types=1);

namespace App\Values;

use App\Values\Casts\FeatureScopeSerializeable;
use App\Values\Factories\FeatureFlagContextFactory;
use Bag\Attributes\CastInput;
use Bag\Attributes\Factory;
use Bag\Attributes\MapName;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(string $scopeType, mixed $scope, string $appName, string $environment, ?string $sessionId = null, ?string $ip = null, ?string $userAgent = null, ?string $referrer = null, ?string $url = null, ?string $method = null)
 */
#[Factory(FeatureFlagContextFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class FeatureFlagContext extends Bag
{
    use HasFactory;

    public function __construct(
        public ?string $scopeType,
        #[CastInput(FeatureScopeSerializeable::class)]
        public ?array $scope,
        public string $appName,
        public string $environment,
        public ?string $sessionId = null,
        public ?string $ip = null,
        public ?string $userAgent = null,
        public ?string $referrer = null,
        public ?string $url = null,
        public ?string $method = null,
    ) {
    }

    public static function rules(): array
    {
        return [
            'scopeType' => ['nullable', 'string'],
            'scope' => ['nullable', 'array'],
            'appName' => ['required', 'string'],
            'environment' => ['required', 'string'],
            'sessionId' => ['nullable', 'string'],
            'ip' => ['nullable', 'string'],
            'userAgent' => ['nullable', 'string'],
            'referrer' => ['nullable', 'string'],
            'url' => ['nullable', 'string'],
            'method' => ['nullable', 'string'],
        ];
    }
}
