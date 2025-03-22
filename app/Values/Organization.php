<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\Organization as OrganizationModel;
use App\Values\Collections\OrganizationCollection;
use Bag\Attributes\Collection;
use Bag\Attributes\Laravel\FromRouteParameter;
use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[Collection(OrganizationCollection::class)]
#[StripExtraParameters]
#[TypeScript]
/**
 * @method static OrganizationCollection collect(iterable $items)
 */
readonly class Organization extends Bag
{
    public function __construct(
        #[FromRouteParameter]
        public ?string $slug,
        #[FromRouteParameter]
        public ?string $id,
        public ?User $owner,
        public ?string $name,
    ) {
    }

    #[Transforms(OrganizationModel::class)]
    public static function fromModel(OrganizationModel $organization): array
    {
        return [
            'slug' => $organization->slug,
            'id' => $organization->id,
            'owner' => $organization->owner !== null ? User::from($organization->owner) : null,
            'name' => $organization->name,
        ];
    }
}
