<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\FeatureFlag as FeatureFlagModel;
use App\Values\Collections\FeatureFlagCollection;
use App\Values\Collections\TagCollection;
use App\Values\Factories\FeatureFlagFactory;
use Bag\Attributes\Cast;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\MapName;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Casts\CollectionOf;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Illuminate\Support\Carbon;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(string $name, ?string $slug = null, ?string $description = null, ?string $lastSeenAt = null, ?FeatureType $featureType = null, ?TagCollection<Tag> $tags = null, ?Carbon $createdAt = null, ?Carbon $updatedAt = null)
 */
#[Collection(FeatureFlagCollection::class)]
#[Factory(FeatureFlagFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class FeatureFlag extends Bag
{
    use HasFactory;

    public function __construct(
        public string $name,
        public ?string $slug = null,
        public ?string $description = null,
        public ?string $lastSeenAt = null,
        public ?FeatureType $featureType = null,
        #[Cast(CollectionOf::class, Tag::class)]
        public ?TagCollection $tags = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {
    }

    #[Transforms(FeatureFlagModel::class)]
    public static function fromModel(FeatureFlagModel $featureFlag): array
    {
        return [
            'name' => $featureFlag->name,
            'slug' => $featureFlag->slug,
            'description' => $featureFlag->description,
            'last_seen_at' => $featureFlag->last_seen_at,
            'feature_type' => FeatureType::from($featureFlag->featureType),
            'created_at' => $featureFlag->created_at,
            'updated_at' => $featureFlag->updated_at,
        ];
    }
}
