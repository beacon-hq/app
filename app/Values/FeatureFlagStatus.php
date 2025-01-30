<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\FeatureFlagStatus as FeatureFlagStatusModel;
use App\Values\Collections\FeatureFlagStatusCollection;
use App\Values\Collections\PolicyCollection;
use Bag\Attributes\Cast;
use Bag\Attributes\Collection;
use Bag\Attributes\MapName;
use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Casts\CollectionOf;
use Bag\Mappers\SnakeCase;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[Collection(FeatureFlagStatusCollection::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[StripExtraParameters]
#[TypeScript]
readonly class FeatureFlagStatus extends Bag
{
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
