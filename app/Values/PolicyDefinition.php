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
use Bag\Collection as Collection1;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Bag\Validation\Rules\OptionalOr;
use Bag\Values\Optional;
use Illuminate\Validation\Rule;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(PolicyDefinitionType $type, string $subject, Optional|PolicyDefinitionMatchOperator $operator, Collection|Optional $values)
 * @method static PolicyDefinitionCollection<PolicyDefinition> collect(iterable $items)
 * @method static PolicyDefinitionFactory<PolicyDefinition> factory(Collection|array|int $data = [])
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
        public ?PolicyDefinitionMatchOperator $operator = null,
        #[LiteralTypeScriptType('string[]')]
        public ?Collection1 $values = null,
    ) {
    }

    public static function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(PolicyDefinitionType::class)],
            'subject' => ['required', 'string'],
            'operator' => [new OptionalOr(['nullable', 'required_if:type,' . PolicyDefinitionType::EXPRESSION->value])],
            'values' => [new OptionalOr(['nullable', 'required_if:type,' . PolicyDefinitionType::EXPRESSION->value])],
        ];
    }
}
