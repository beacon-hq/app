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
use Bag\Attributes\Laravel\FromRouteParameter;
use Bag\Attributes\MapName;
use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Bag\Values\Optional;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(Optional|string $id, Optional|string $name, Optional|string $description, Color|string $color = '#e3e3e3', Carbon|null $createdAt = null, Carbon|null $updatedAt = null)
 * @method static TagCollection<Tag> collect(iterable $items)
 * @method static TagFactory<Tag> factory(Collection|array|int $data = [])
 */
#[Collection(TagCollection::class)]
#[Factory(TagFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[StripExtraParameters]
#[TypeScript]
readonly class Tag extends Bag
{
    use HasFactory;

    public function __construct(
        #[FromRouteParameter('tag')]
        public Optional|string $id,
        public Optional|string $name,
        public Optional|string $description,
        #[Cast(ColorCast::class)]
        public Color|string $color = '#e3e3e3',
        public ?CarbonImmutable $createdAt = null,
        public ?CarbonImmutable $updatedAt = null,
    ) {
    }

    #[Transforms(TagModel::class)]
    public static function fromModel(TagModel $tag): array
    {
        return [
            'id' => $tag->id,
            'name' => $tag->name,
            'description' => $tag->description,
            'color' => $tag->color,
            'created_at' => $tag->created_at,
            'updated_at' => $tag->updated_at,
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
