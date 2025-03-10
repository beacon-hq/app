<?php

declare(strict_types=1);

namespace App\Values;

use App\Enums\PolicyDefinitionMatchOperator;
use App\Enums\PolicyDefinitionType;
use App\Values\Collections\PolicyDefinitionCollection;
use App\Values\Factories\PolicyDefinitionFactory;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\MapName;
use Bag\Attributes\StripExtraParameters;
use Bag\Bag;
use Bag\Collection as BagCollection;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Illuminate\Validation\Rule;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(PolicyDefinitionType $type, string $subject, ?PolicyDefinitionMatchOperator $operator = null, ?BagCollection $values = null)
 * @method static PolicyDefinitionCollection collect(iterable $items)
 */
#[Collection(PolicyDefinitionCollection::class)]
#[Factory(PolicyDefinitionFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[StripExtraParameters]
#[TypeScript]
readonly class PolicyDefinition extends Bag
{
    use HasFactory;

    public function __construct(
        public PolicyDefinitionType $type,
        public string $subject,
        #[Optional]
        public ?PolicyDefinitionMatchOperator $operator = null,
        #[LiteralTypeScriptType('string[]')]
        public ?BagCollection $values = null,
    ) {
    }

    public static function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(PolicyDefinitionType::class)],
            'subject' => ['required', 'string'],
            'operator' => ['nullable', 'required_if:type,' . PolicyDefinitionType::EXPRESSION->value],
            'values' => ['nullable', 'required_if:type,' . PolicyDefinitionType::EXPRESSION->value],
        ];
    }
}
