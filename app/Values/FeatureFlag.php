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
use Illuminate\Support\Carbon;
use Spatie\TypeScriptTransformer\Attributes\Hidden as HiddenFromTypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(string $name, ?string $slug = null, ?string $description = null, ?string $lastSeenAt = null, ?FeatureType $featureType = null, ?TagCollection<Tag> $tags = null, ?Carbon $createdAt = null, ?Carbon $updatedAt = null)
 * @method static FeatureFlagCollection collect(iterable $items)
 */
#[Collection(FeatureFlagCollection::class)]
#[Factory(FeatureFlagFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class FeatureFlag extends Bag
{
    use HasFactory;

    public function __construct(
        #[HiddenFromTypeScript]
        public ?string $id = null,
        public ?string $name = null,
        #[FromRouteParameter]
        public ?string $slug = null,
        public ?string $description = null,
        public ?string $lastSeenAt = null,
        public ?FeatureType $featureType = null,
        #[Cast(CollectionOf::class, Tag::class)]
        public ?TagCollection $tags = null,
        #[Cast(CollectionOf::class, Application::class)]
        #[HiddenFromTypeScript]
        public ?ApplicationCollection $applications = null,
        #[Cast(CollectionOf::class, FeatureFlagStatus::class)]
        public ?FeatureFlagStatusCollection $statuses = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {
    }

    #[Transforms(FeatureFlagModel::class)]
    public static function fromModel(FeatureFlagModel $featureFlag): array
    {
        return [
            'id' => $featureFlag->id,
            'name' => $featureFlag->name,
            'slug' => $featureFlag->slug,
            'description' => $featureFlag->description,
            'last_seen_at' => $featureFlag->last_seen_at,
            'feature_type' => FeatureType::from($featureFlag->featureType),
            'tags' => Tag::collect($featureFlag->tags),
            'statuses' => FeatureFlagStatus::collect($featureFlag->statuses()->with('application')->get()->sortBy(['application.name', 'environment.name'])->values()),
            'created_at' => $featureFlag->created_at,
            'updated_at' => $featureFlag->updated_at,
        ];
    }

    public function toJson($options = 0): string|false
    {
        return json_encode($this->toCollection()->except('id')->toArray(), JSON_THROW_ON_ERROR | $options);
    }

    public static function rules(): array
    {
        return [
            'name' => ['required_without:slug', 'exclude_with:slug'],
            'description' => ['nullable'],
            'feature_type' => ['required'],
        ];
    }
}
