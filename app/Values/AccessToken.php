<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\AccessToken as AccessTokenModel;
use App\Values\Collections\AccessTokenCollection;
use App\Values\Factories\AccessTokenFactory;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\Laravel\FromRouteParameter;
use Bag\Attributes\MapName;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Bag\Validation\Rules\OptionalOr;
use Bag\Values\Optional;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(Optional|int $id, Optional|string $name, Optional|string $token, string|null $lastUsedAt = null, string|null $createdAt = null)
 * @method static AccessTokenCollection<AccessToken> collect(iterable $items)
 * @method static AccessTokenFactory<AccessToken> factory(Collection|array|int $data = [])
 */
#[Collection(AccessTokenCollection::class)]
#[Factory(AccessTokenFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class AccessToken extends Bag
{
    use HasFactory;

    public function __construct(
        #[FromRouteParameter('access_token')]
        public Optional|int $id,
        public Optional|string $name,
        public Optional|string $token,
        public string|null $lastUsedAt = null,
        public string|null $createdAt = null,
    ) {
    }

    #[Transforms(AccessTokenModel::class)]
    public static function fromModel(AccessTokenModel $personalAccessToken): array
    {
        return [
            'id' => $personalAccessToken->id,
            'name' => $personalAccessToken->name,
            'token' => Str::of($personalAccessToken->plain_text_suffix)->prepend('************************************'),
            'last_used_at' => $personalAccessToken->last_used_at,
            'created_at' => $personalAccessToken->created_at,
        ];
    }

    #[Transforms(NewAccessToken::class)]
    public static function fromNewAccessToken(NewAccessToken $newAccessToken): array
    {
        return [
            'id' => $newAccessToken->accessToken->id,
            'name' => $newAccessToken->accessToken->name,
            'token' => Str::after($newAccessToken->plainTextToken, '|'),
            'last_used_at' => null,
            'created_at' => $newAccessToken->accessToken->created_at,
        ];
    }

    public static function rules(): array
    {
        return [
            'name' => [new OptionalOr(['required', 'string'])],
        ];
    }
}
