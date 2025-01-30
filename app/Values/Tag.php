<?php

declare(strict_types=1);

namespace App\Values;

use App\Enums\Color;
use App\Models\Tag as TagModel;
use App\Values\Casts\Color as ColorCast;
use App\Values\Collections\TagCollection;
use App\Values\Factories\TagFactory;
use Bag\Attributes\Cast;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\Hidden;
use Bag\Attributes\Laravel\FromRouteParameter;
use Bag\Attributes\MapName;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Illuminate\Support\Carbon;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(?string $id = null, ?string $slug = null, ?string $name = null, ?string $description = null, string|Color $color = '#e3e3e3', ?Carbon $createdAt = null, ?Carbon $updatedAt = null)
 * @method static TagCollection collect(iterable $items)
 */
#[Collection(TagCollection::class)]
#[Factory(TagFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class Tag extends Bag
{
    use HasFactory;

    public function __construct(
        #[FromRouteParameter]
        public ?string $slug = null,
        public ?string $name = null,
        public ?string $description = null,
        #[Cast(ColorCast::class)]
        public string|Color $color = '#e3e3e3',
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        #[Hidden]
        public ?string $id = null,
    ) {
    }

    #[Transforms(TagModel::class)]
    public static function fromModel(TagModel $tag): array
    {
        return [
            'slug' => $tag->slug,
            'name' => $tag->name,
            'description' => $tag->description,
            'color' => $tag->color,
            'created_at' => $tag->created_at,
            'updated_at' => $tag->updated_at,
            'id' => $tag->id,
        ];
    }

    public static function rules(): array
    {
        return [
            'name' => ['required'],
            'color' => ['present'],
            'description' => ['nullable'],
        ];
    }
}
