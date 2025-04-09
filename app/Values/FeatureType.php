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
use Bag\Attributes\Laravel\FromRouteParameter;
use Bag\Attributes\MapName;
use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Bag\Values\Optional;
use Illuminate\Support\Carbon;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(Optional|string $id, Optional|string $name, Optional|string $description, Optional|string $icon, Color|string $color = '#e3e3e3', bool $temporary = false, Carbon|null $createdAt = null, Carbon|null $updatedAt = null)
 * @method static FeatureTypeCollection<FeatureType> collect(iterable $items)
 * @method static FeatureTypeFactory<FeatureType> factory(Collection|array|int $data = [])
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
        #[FromRouteParameter('feature_type')]
        public Optional|string $id,
        public Optional|string $name,
        public Optional|string $description,
        public Optional|string $icon,
        #[Cast(ColorCast::class)]
        public Color|string $color = '#e3e3e3',
        public bool $temporary = false,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {
    }

    #[Transforms(FeatureTypeModel::class)]
    public static function fromModel(FeatureTypeModel $featureType): array
    {
        return [
            'id' => $featureType->id,
            'name' => $featureType->name,
            'description' => $featureType->description,
            'temporary' => $featureType->temporary,
            'color' => $featureType->color,
            'icon' => $featureType->icon,
            'createdAt' => $featureType->created_at,
            'updatedAt' => $featureType->updated_at,
        ];
    }

    public static function rules(): array
    {
        return [
            'name' => ['required_without:id', 'exclude_with:id'],
            'description' => ['nullable'],
            'color' => ['required'],
            'icon' => ['required'],
        ];
    }
}
