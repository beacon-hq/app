<?php

declare(strict_types=1);

namespace App\Values;

use App\Values\Collections\PolicyValueCollection;
use Bag\Attributes\Collection as CollectionAttribute;
use Bag\Attributes\MapName;
use Bag\Bag;
use Bag\Collection;
use Bag\Mappers\SnakeCase;
use Spatie\TypeScriptTransformer\Attributes\Hidden as HiddenFromTypeScript;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(PolicyDefinition $policyDefinition, CollectionAttribute $value, ?bool $status = null)
 */
#[CollectionAttribute(PolicyValueCollection::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class PolicyValue extends Bag
{
    public function __construct(
        public PolicyDefinition $policyDefinition,
        #[LiteralTypeScriptType('Array<any>')]
        public Collection $value,
        #[HiddenFromTypeScript]
        public ?bool $status = null,
    ) {
    }
}
