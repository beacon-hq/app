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
use Bag\Validation\Rules\OptionalOr;
use Bag\Values\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(string|null $scopeType, Optional|array $scope, string $appName, string $environment, Optional|string $sessionId, Optional|string $ip, Optional|string $userAgent, Optional|string $referrer, Optional|string $url, Optional|string $method)
 * @method static FeatureFlagContextFactory<FeatureFlagContext> factory(Collection|array|int $data = [])
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
        public Optional|array $scope,
        public string $appName,
        public string $environment,
        public Optional|string $sessionId,
        public Optional|string $ip,
        public Optional|string $userAgent,
        public Optional|string $referrer,
        public Optional|string $url,
        public Optional|string $method,
    ) {
    }

    public static function rules(): array
    {
        return [
            'scopeType' => ['nullable', 'string'],
            'scope' => [new OptionalOr(['nullable', 'array'])],
            'appName' => ['required', 'string'],
            'environment' => ['required', 'string'],
            'sessionId' => [new OptionalOr(['nullable', 'string'])],
            'ip' => [new OptionalOr(['nullable', 'string'])],
            'userAgent' => [new OptionalOr(['nullable', 'string'])],
            'referrer' => [new OptionalOr(['nullable', 'string'])],
            'url' => [new OptionalOr(['nullable', 'string'])],
            'method' => [new OptionalOr(['nullable', 'string'])],
        ];
    }
}
