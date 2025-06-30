<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\FeatureFlag as FeatureFlagModel;
use App\Values\Collections\ApplicationCollection;
use App\Values\Collections\FeatureFlagCollection;
use App\Values\Collections\FeatureFlagStatusCollection;
use App\Values\Collections\TagCollection;
use App\Values\Factories\FeatureFlagFactory;
use Bag\Attributes\Cast;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\Laravel\FromRouteParameter;
use Bag\Attributes\MapName;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Casts\CollectionOf;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Bag\Values\Optional;
use Illuminate\Support\Carbon;
use Spatie\TypeScriptTransformer\Attributes\Hidden as HiddenFromTypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(Optional|string $id, Optional|string $name, Optional|string $description, Carbon|null $lastSeenAt, FeatureType|Optional $featureType, Optional|TagCollection $tags, ApplicationCollection|Optional $applications, FeatureFlagStatusCollection|Optional $statuses, Carbon|null $createdAt = null, Carbon|null $updatedAt = null, bool $status = false)
 * @method static FeatureFlagCollection<FeatureFlag> collect(iterable $items)
 * @method static FeatureFlagFactory<FeatureFlag> factory(Collection|array|int $data = [])
 */
#[Collection(FeatureFlagCollection::class)]
#[Factory(FeatureFlagFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class FeatureFlag extends Bag
{
    use HasFactory;

    public function __construct(
        #[FromRouteParameter('feature_flag')]
        public Optional|string $id,
        public Optional|string $name,
        public Optional|string $description,
        public Carbon|null $lastSeenAt,
        public FeatureType|Optional $featureType,
        #[Cast(CollectionOf::class, Tag::class)]
        public Optional|TagCollection $tags,
        #[Cast(CollectionOf::class, Application::class), HiddenFromTypeScript]
        public ApplicationCollection|Optional $applications,
        #[Cast(CollectionOf::class, FeatureFlagStatus::class)]
        public FeatureFlagStatusCollection|Optional $statuses,
        public Carbon|null $createdAt = null,
        public Carbon|null $updatedAt = null,
        public bool $status = false,
    ) {
    }

    #[Transforms(FeatureFlagModel::class)]
    public static function fromModel(FeatureFlagModel $featureFlag): array
    {
        return [
            'id' => $featureFlag->id,
            'name' => $featureFlag->name,
            'description' => $featureFlag->description,
            'last_seen_at' => $featureFlag->last_seen_at,
            'feature_type' => FeatureType::from($featureFlag->featureType),
            'tags' => Tag::collect($featureFlag->tags),
            'statuses' => FeatureFlagStatus::collect($featureFlag->statuses),
            'created_at' => $featureFlag->created_at,
            'updated_at' => $featureFlag->updated_at,
            'status' => $featureFlag->status ?? false,
        ];
    }

    public static function rules(): array
    {
        return [
            'name' => ['required_without:id', 'exclude_with:id'],
            'description' => ['nullable'],
            'feature_type' => ['required'],
            'status' => ['boolean', 'nullable'],
        ];
    }
}
