<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\FeatureFlagStatus as FeatureFlagStatusModel;
use App\Values\Collections\FeatureFlagStatusCollection;
use App\Values\Collections\PolicyDefinitionCollection;
use App\Values\Factories\FeatureFlagStatusFactory;
use Bag\Attributes\Cast;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\MapName;
use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Casts\CollectionOf;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Bag\Values\Optional;
use Cache;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(Optional|string $id, Application|null $application, Environment|null $environment, FeatureFlag|null $featureFlag, bool $status, Optional|PolicyDefinitionCollection $definition)
 * @method static FeatureFlagStatusCollection<FeatureFlagStatus> collect(iterable $items)
 * @method static FeatureFlagStatusFactory<FeatureFlagStatus> factory(Collection|array|int $data = [])
 */
#[Collection(FeatureFlagStatusCollection::class)]
#[Factory(FeatureFlagStatusFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[StripExtraParameters]
#[TypeScript]
readonly class FeatureFlagStatus extends Bag
{
    use HasFactory;

    public function __construct(
        public Optional|string $id,
        public ?Application $application,
        public ?Environment $environment,
        public ?FeatureFlag $featureFlag,
        public bool $status,
        #[Cast(CollectionOf::class, PolicyDefinition::class)]
        public Optional|PolicyDefinitionCollection $definition,
    ) {
    }

    #[Transforms(FeatureFlagStatusModel::class)]
    public static function fromModel(FeatureFlagStatusModel $featureFlagStatus): array
    {
        return [
            'id' => $featureFlagStatus->id,
            'application' => Cache::driver('array')->rememberForever('application:' . $featureFlagStatus->application_id, fn () => $featureFlagStatus->application),
            'environment' => Cache::driver('array')->rememberForever('environment:' . $featureFlagStatus->environment_id, fn () => $featureFlagStatus->environment),
            'definition' => $featureFlagStatus->definition,
            'status' => $featureFlagStatus->status,
        ];
    }
}
