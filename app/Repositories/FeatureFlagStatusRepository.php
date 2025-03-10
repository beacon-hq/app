<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Boolean;
use App\Enums\PolicyDefinitionMatchOperator;
use App\Enums\PolicyDefinitionType;
use App\Models\FeatureFlagStatus;
use App\Models\Policy;
use App\Values\FeatureFlag;
use App\Values\FeatureFlagContext;
use App\Values\FeatureFlagResponse;
use App\Values\PolicyDefinition;
use App\Values\PolicyValue;
use Illuminate\Support\Collection as LaravelCollection;
use Illuminate\Support\Str;

class FeatureFlagStatusRepository
{
    public function first(FeatureFlag $featureFlag, FeatureFlagContext $context): FeatureFlagResponse
    {
        $status = FeatureFlagStatus::query()
            ->whereApplication($context->appName)
            ->whereEnvironment($context->environment)
            ->whereFeatureFlag($featureFlag)
            ->firstOrFail();

        if ($status->policies()->count() === 0) {
            return FeatureFlagResponse::from(featureFlag: $featureFlag->slug, value: null, active: true);
        }

        $statuses = $status->policies?->map(function (Policy $policy) use ($context) {
            $values = collect($policy->definition)
                ->map(function (PolicyDefinition $policyDefinition) use ($context, $policy) {
                    static $i = 0;

                    if ($policyDefinition->type === PolicyDefinitionType::EXPRESSION) {
                        /** @var PolicyValue[] $values */
                        $values = $policy->pivot->values;

                        return $this->evaluateExpression($values[$i++], $context);
                    }

                    return $policyDefinition;
                })
                ->reduce(function (LaravelCollection $carry, PolicyDefinition|PolicyValue $item) use ($policy) {
                    if ($item instanceof PolicyValue && $item->status === false) {
                        $carry = $carry->put($carry->count() - 1, $item->status);
                    }

                    if ($item instanceof PolicyDefinition && $item->type === PolicyDefinitionType::OPERATOR) {
                        $carry = $carry->push($item, true);
                    }

                    return $carry;
                }, LaravelCollection::make([true]));

            $result = $values->shift();

            while ($values->isNotEmpty()) {
                /** @var PolicyDefinition $operator */
                $operator = $values->shift();
                $nextValue = $values->shift();

                if ($result === true && Boolean::OR->is(Boolean::from($operator->subject))) {
                    break;
                }

                if ($result === false && Boolean::AND->is(Boolean::from($operator->subject))) {
                    break;
                }

                $result = $this->applyOperator($result, $nextValue, $operator->subject);
            }

            return $result;
        });

        return FeatureFlagResponse::from(featureFlag: $featureFlag->slug, value: null, active: $statuses?->first() ?? false);
    }

    protected function evaluateExpression(PolicyValue $policyValue, FeatureFlagContext $context): PolicyValue
    {
        $contextValue = \data_get($context, $policyValue->policyDefinition->subject) ?? \data_get($context->scope, $policyValue->policyDefinition->subject);
        if ($contextValue === null) {
            return $policyValue->with(status: false);
        }

        return $policyValue->with(
            status: $this->compareExpressionValue(operator: $policyValue->policyDefinition->operator, contextValue: $contextValue, policyValues: $policyValue->values)
        );
    }

    protected function compareExpressionValue(PolicyDefinitionMatchOperator $operator, mixed $contextValue, LaravelCollection $policyValues)
    {
        try {
            if (\is_scalar($contextValue) && $policyValues->containsOneItem()) {
                return match ($operator) {
                    PolicyDefinitionMatchOperator::EQUAL => $contextValue === $policyValues->first(),
                    PolicyDefinitionMatchOperator::NOT_EQUAL => $contextValue !== $policyValues->first(),
                    PolicyDefinitionMatchOperator::GREATER_THAN => $contextValue > $policyValues->first(),
                    PolicyDefinitionMatchOperator::GREATER_THAN_EQUAL => $contextValue >= $policyValues->first(),
                    PolicyDefinitionMatchOperator::LESS_THAN => $contextValue < $policyValues->first(),
                    PolicyDefinitionMatchOperator::LESS_THAN_EQUAL => $contextValue <= $policyValues->first(),
                    PolicyDefinitionMatchOperator::CONTAINS_ALL, PolicyDefinitionMatchOperator::CONTAINS_ANY => Str::of((string) $contextValue)->contains((string) $policyValues->first()),
                    PolicyDefinitionMatchOperator::NOT_CONTAINS_ALL, PolicyDefinitionMatchOperator::NOT_CONTAINS_ANY => !Str::of((string) $contextValue)->contains((string) $policyValues->first()),
                    PolicyDefinitionMatchOperator::MATCHES_ALL, PolicyDefinitionMatchOperator::MATCHES_ANY => Str::of((string) $contextValue)->isMatch((string) $policyValues->first()),
                    PolicyDefinitionMatchOperator::NOT_MATCHES_ALL, PolicyDefinitionMatchOperator::NOT_MATCHES_ANY => !Str::of((string) $contextValue)->isMatch((string) $policyValues->first()),
                    PolicyDefinitionMatchOperator::ONE_OF, PolicyDefinitionMatchOperator::NOT_ONE_OF => false,
                };
            }

            if (\is_scalar($contextValue)) {
                return match ($operator) {
                    PolicyDefinitionMatchOperator::EQUAL, PolicyDefinitionMatchOperator::ONE_OF => $policyValues->contains($contextValue),
                    PolicyDefinitionMatchOperator::NOT_EQUAL, PolicyDefinitionMatchOperator::NOT_ONE_OF => !$policyValues->contains($contextValue),
                    PolicyDefinitionMatchOperator::CONTAINS_ALL => $policyValues->reduce(fn (bool $carry, mixed $policyValue) => $carry && Str::of($contextValue)->contains((string) $policyValue), true),
                    PolicyDefinitionMatchOperator::CONTAINS_ANY => Str::of($contextValue)->contains($policyValues),
                    PolicyDefinitionMatchOperator::NOT_CONTAINS_ALL => $policyValues->reduce(fn (bool $carry, mixed $policyValue) => $carry && !Str::of($contextValue)->contains((string) $policyValue), true),
                    PolicyDefinitionMatchOperator::NOT_CONTAINS_ANY => $policyValues->reduce(fn (bool $carry, mixed $policyValue) => $carry || !Str::of($contextValue)->contains((string) $policyValue), false),
                    PolicyDefinitionMatchOperator::MATCHES_ALL => $policyValues->reduce(fn (bool $carry, mixed $policyValue) => $carry && Str::of($contextValue)->isMatch((string) $policyValue), true),
                    PolicyDefinitionMatchOperator::MATCHES_ANY => $policyValues->reduce(fn (bool $carry, mixed $policyValue) => $carry || Str::of($contextValue)->isMatch((string) $policyValue), false),
                    PolicyDefinitionMatchOperator::NOT_MATCHES_ALL => $policyValues->reduce(fn (bool $carry, mixed $policyValue) => $carry && !Str::of($contextValue)->isMatch((string) $policyValue), true),
                    PolicyDefinitionMatchOperator::NOT_MATCHES_ANY => $policyValues->reduce(fn (bool $carry, mixed $policyValue) => $carry || !Str::of($contextValue)->isMatch((string) $policyValue), false),
                };
            }

            $contextValues = collect($contextValue);

            return match ($operator) {
                PolicyDefinitionMatchOperator::EQUAL => $contextValues->count() === $policyValues->count() && $contextValues->diff($policyValues)->isEmpty(),
                PolicyDefinitionMatchOperator::NOT_EQUAL => $contextValues->count() !== $policyValues->count() || $contextValues->diff($policyValues)->isNotEmpty(),
                PolicyDefinitionMatchOperator::CONTAINS_ALL => $contextValues->count() < $policyValues->count() || $contextValues->filter(fn (mixed $contextValue) => !$policyValues->contains($contextValue))->isEmpty(),
                PolicyDefinitionMatchOperator::NOT_CONTAINS_ALL => $contextValues->filter(fn (mixed $contextValue) => !$policyValues->contains($contextValue))->count() === $contextValues->count(),
                PolicyDefinitionMatchOperator::CONTAINS_ANY => $contextValues->filter(fn (mixed $contextValue) => $policyValues->contains($contextValue))->isNotEmpty(),
                PolicyDefinitionMatchOperator::NOT_CONTAINS_ANY => $contextValues->filter(fn (mixed $contextValue) => !$policyValues->contains($contextValue))->isNotEmpty(),
                PolicyDefinitionMatchOperator::MATCHES_ALL => $contextValues->filter(fn (mixed $contextValue) => !$policyValues->reduce(fn ($carry, $policyValue) => $carry && Str::of($contextValue)->isMatch($policyValue), true))->isEmpty(),
                PolicyDefinitionMatchOperator::NOT_MATCHES_ALL => $contextValues->filter(fn (mixed $contextValue) => !$policyValues->reduce(fn ($carry, $policyValue) => $carry && !Str::of($contextValue)->isMatch($policyValue), true))->isEmpty(),
                PolicyDefinitionMatchOperator::MATCHES_ANY => $contextValues->filter(fn (mixed $contextValue) => !$policyValues->reduce(fn ($carry, $policyValue) => $carry || Str::of($contextValue)->isMatch($policyValue), false))->isEmpty(),
                PolicyDefinitionMatchOperator::NOT_MATCHES_ANY => $contextValues->filter(fn (mixed $contextValue) => !$policyValues->reduce(fn ($carry, $policyValue) => !$carry || !Str::of($contextValue)->isMatch($policyValue), true))->isEmpty(),
                PolicyDefinitionMatchOperator::ONE_OF => $contextValues->filter(fn (mixed $contextValue) => !$policyValues->contains($contextValue))->isEmpty(),
                PolicyDefinitionMatchOperator::NOT_ONE_OF => $contextValues->filter(fn (mixed $contextValue) => $policyValues->contains($contextValue))->isEmpty(),
            };
        } catch (\Throwable) {
            return false;
        }
    }

    protected function applyOperator(bool $left, bool $right, string $operator): bool
    {
        return match (Boolean::from($operator)) {
            Boolean::AND => $left && $right,
            Boolean::OR => $left || $right,
            Boolean::NOT => $left && !$right,
            Boolean::XOR => $left xor $right,
        };
    }
}
