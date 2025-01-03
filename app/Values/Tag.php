<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\Tag as TagModel;
use App\Values\Collections\TagCollection;
use App\Values\Factories\TagFactory;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\MapName;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Illuminate\Support\Carbon;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(?string $id = null, ?string $slug = null, ?string $name = null, ?string $description = null, ?string $color = null, ?Carbon $createdAt = null, ?Carbon $updatedAt = null)
 */
#[Collection(TagCollection::class)]
#[Factory(TagFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class Tag extends Bag
{
    use HasFactory;

    public function __construct(
        public ?string $id = null,
        public ?string $slug = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $color = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {
    }

    #[Transforms(TagModel::class)]
    public static function fromModel(TagModel $tag): array
    {
        return [
            'id' => $tag->id,
            'slug' => $tag->slug,
            'name' => $tag->name,
            'description' => $tag->description,
            'color' => $tag->color,
            'created_at' => $tag->created_at,
            'updated_at' => $tag->updated_at,
        ];
    }
}
