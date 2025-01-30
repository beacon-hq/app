<?php

declare(strict_types=1);

namespace App\Values;

use App\Enums\Color;
use App\Models\FeatureType as FeatureTypeModel;
use App\Values\Casts\Color as ColorCast;
use App\Values\Collections\FeatureTypeCollection;
use App\Values\Factories\FeatureTypeFactory;
use Bag\Attributes\Cast;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\Hidden;
use Bag\Attributes\Laravel\FromRouteParameter;
use Bag\Attributes\MapName;
use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Illuminate\Support\Carbon;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(string $name, ?string $id = null, ?string $slug = null, ?string $description = null, ?bool $temporary = null, string|Color $color = '#e3e3e3', ?string $icon = null, ?Carbon $createdAt = null, ?Carbon $updatedAt = null)
 * @method static FeatureTypeCollection collect(iterable $items)
 */
#[Collection(FeatureTypeCollection::class)]
#[Factory(FeatureTypeFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[StripExtraParameters]
#[TypeScript]
readonly class FeatureType extends Bag
{
    use HasFactory;

    public function __construct(
        #[FromRouteParameter]
        public ?string $slug = null,
        public ?string $name = null,
        public ?string $description = null,
        public bool $temporary = false,
        #[Cast(ColorCast::class)]
        public string|Color $color = '#e3e3e3',
        public ?string $icon = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        #[Hidden]
        public ?string $id = null,
    ) {
    }

    #[Transforms(FeatureTypeModel::class)]
    public static function fromModel(FeatureTypeModel $featureType): array
    {
        return [
            'name' => $featureType->name,
            'slug' => $featureType->slug,
            'description' => $featureType->description,
            'temporary' => $featureType->temporary,
            'color' => $featureType->color,
            'icon' => $featureType->icon,
            'createdAt' => $featureType->created_at,
            'updatedAt' => $featureType->updated_at,
            'id' => $featureType->id,
        ];
    }

    public static function rules(): array
    {
        return [
            'name' => ['required_without:slug', 'exclude_with:slug'],
            'description' => ['nullable'],
            'color' => ['required'],
            'icon' => ['required'],
        ];
    }
}
