<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\PersonalAccessToken;
use App\Values\Collections\AccessTokenCollection;
use App\Values\Factories\AccessTokenFactory;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Traits\HasFactory;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(?int $id = null, ?string $name = null, ?string $token = null, ?string $last_used_at = null, ?string $created_at = null)
 */
#[Collection(AccessTokenCollection::class)]
#[Factory(AccessTokenFactory::class)]
#[TypeScript]
readonly class AccessToken extends Bag
{
    use HasFactory;

    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $token = null,
        public ?string $last_used_at = null,
        public ?string $created_at = null,
    ) {
    }

    #[Transforms(PersonalAccessToken::class)]
    public static function fromModel(PersonalAccessToken $personalAccessToken)
    {
        return [
            'id' => $personalAccessToken->id,
            'name' => $personalAccessToken->name,
            'token' => $personalAccessToken->plain_text_suffix,
            'last_used_at' => $personalAccessToken->last_used_at,
            'created_at' => $personalAccessToken->created_at,
        ];
    }
}
