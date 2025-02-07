<?php

declare(strict_types=1);

namespace App\Values;

use App\Values\Collections\PolicyValueCollection;
use Bag\Attributes\Collection as CollectionAttribute;
use Bag\Attributes\MapName;
use Bag\Bag;
use Bag\Collection;
use Bag\Mappers\SnakeCase;

/**
 * @method static static from(PolicyDefinition $policyDefinition, CollectionAttribute $value, ?bool $status = null)
 */
#[CollectionAttribute(PolicyValueCollection::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
readonly class PolicyValue extends Bag
{
    public function __construct(
        public PolicyDefinition $policyDefinition,
        public Collection $value,
        public ?bool $status = null,
    ) {
    }
}
