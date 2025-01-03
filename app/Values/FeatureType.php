<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\FeatureType as FeatureTypeModel;
use App\Values\Collections\FeatureTypeCollection;
use App\Values\Factories\FeatureTypeFactory;
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
 * @method static static from(string $name, ?string $id = null, ?string $slug = null, ?string $description = null, ?bool $temporary = null, ?string $color = null, ?string $icon = null, ?Carbon $createdAt = null, ?Carbon $updatedAt = null)
 */
#[Collection(FeatureTypeCollection::class)]
#[Factory(FeatureTypeFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class FeatureType extends Bag
{
    use HasFactory;

    public function __construct(
        public string $name,
        public ?string $id = null,
        public ?string $slug = null,
        public ?string $description = null,
        public ?bool $temporary = null,
        public ?string $color = null,
        public ?string $icon = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {
    }

    #[Transforms(FeatureTypeModel::class)]
    public static function fromModel(FeatureTypeModel $featureType): array
    {
        return [
            'name' => $featureType->name,
            'id' => $featureType->id,
            'slug' => $featureType->slug,
            'description' => $featureType->description,
            'temporary' => $featureType->temporary,
            'color' => $featureType->color,
            'icon' => $featureType->icon,
        ];
    }
}
