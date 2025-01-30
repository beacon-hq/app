<?php

declare(strict_types=1);

namespace App\Values;

use App\Values\Casts\FeatureScopeSerializeable;
use Bag\Attributes\CastInput;
use Bag\Attributes\MapName;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(string $scopeType, mixed $scope, string $appName, string $environment, ?string $sessionId, ?string $ip, ?string $userAgent, ?string $referrer, ?string $url, ?string $method)
 */
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class FeatureFlagContext extends Bag
{
    public function __construct(
        public ?string $scopeType,
        #[CastInput(FeatureScopeSerializeable::class)]
        public array $scope,
        public string $appName,
        public string $environment,
        public ?string $sessionId,
        public ?string $ip,
        public ?string $userAgent,
        public ?string $referrer,
        public ?string $url,
        public ?string $method,
    ) {
    }

    public static function rules(): array
    {
        return [
            'scopeType' => 'required|string',
            'scope' => 'required|array',
            'appName' => 'required|string',
            'environment' => 'required|string',
            'sessionId' => 'nullable|string',
            'ip' => 'nullable|string',
            'userAgent' => 'nullable|string',
            'referrer' => 'nullable|string',
            'url' => 'nullable|string',
            'method' => 'nullable|string',
        ];
    }
}
