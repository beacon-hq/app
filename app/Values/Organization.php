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

/**
 * @method static static from(string|null $id, User|null $owner, string|null $name)
 * @method static OrganizationCollection<Organization> collect(iterable $items)
 */
#[Collection(OrganizationCollection::class)]
#[StripExtraParameters]
#[TypeScript]
readonly class Organization extends Bag
{
    public function __construct(
        #[FromRouteParameter('organization')]
        public ?string $id,
        public ?User $owner,
        public ?string $name,
    ) {
    }

    #[Transforms(OrganizationModel::class)]
    public static function fromModel(OrganizationModel $organization): array
    {
        return [
            'id' => $organization->id,
            'owner' => $organization->owner !== null ? User::from($organization->owner) : null,
            'name' => $organization->name,
        ];
    }
}
