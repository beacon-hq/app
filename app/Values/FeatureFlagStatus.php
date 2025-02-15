<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\FeatureFlagStatus as FeatureFlagStatusModel;
use App\Values\Collections\FeatureFlagStatusCollection;
use App\Values\Collections\PolicyCollection;
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
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(?Application $application, ?Environment $environment, ?FeatureFlag $featureFlag, ?PolicyCollection $policies, bool $status, ?string $id = null)
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
        public ?Application $application,
        public ?Environment $environment,
        public ?FeatureFlag $featureFlag,
        #[Cast(CollectionOf::class, Policy::class)]
        public ?PolicyCollection $policies,
        public bool $status,
        public ?string $id = null,
    ) {
    }

    #[Transforms(FeatureFlagStatusModel::class)]
    public static function fromModel(FeatureFlagStatusModel $featureFlagStatus): array
    {
        return [
            'id' => $featureFlagStatus->id,
            'application' => $featureFlagStatus->application,
            'environment' => $featureFlagStatus->environment,
            'policies' => $featureFlagStatus->policies,
            'status' => $featureFlagStatus->status,
        ];
    }
}
