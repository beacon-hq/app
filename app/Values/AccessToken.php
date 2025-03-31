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
use Illuminate\Support\Str;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(?int $id = null, ?string $name = null, ?string $token = null, ?string $lastUsedAt = null, ?string $createdAt = null)
 */
#[Collection(AccessTokenCollection::class)]
#[Factory(AccessTokenFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class AccessToken extends Bag
{
    use HasFactory;

    public function __construct(
        #[FromRouteParameter('token')]
        public ?int $id = null,
        public ?string $name = null,
        public ?string $token = null,
        public ?string $lastUsedAt = null,
        public ?string $createdAt = null,
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

    public static function rules(): array
    {
        return [
            'name' => ['required', 'string'],
        ];
    }
}
