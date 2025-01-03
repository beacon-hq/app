<?php

declare(strict_types=1);

namespace App\Values;

use App\Enums\PolicyDefinitionType;
use App\Values\Collections\PolicyDefinitionCollection;
use App\Values\Factories\PolicyDefinitionFactory;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Bag;
use Bag\Traits\HasFactory;
use Spatie\TypeScriptTransformer\Attributes\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(PolicyDefinitionType $type, string $subject, ?string $operator = null)
 */
#[Collection(PolicyDefinitionCollection::class)]
#[Factory(PolicyDefinitionFactory::class)]
#[TypeScript]
readonly class PolicyDefinition extends Bag
{
    use HasFactory;

    public function __construct(
        public PolicyDefinitionType $type,
        public string $subject,
        #[Optional]
        public ?string $operator = null,
    ) {
    }
}
