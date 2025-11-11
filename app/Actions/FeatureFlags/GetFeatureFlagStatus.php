<?php

declare(strict_types=1);

namespace App\Actions\FeatureFlags;

use App\Actions\Traits\AsAction;
use App\Enums\Boolean;
use App\Enums\PolicyDefinitionMatchOperator;
use App\Enums\PolicyDefinitionType;
use App\Enums\RolloutStrategy;
use App\Enums\VariantStrategy;
use App\Events\FeatureFlagEvaluatedEvent;
use App\Events\FeatureFlagMissedEvent;
use App\Facades\Murmur;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Values\FeatureFlag as FeatureFlagValue;
use App\Values\FeatureFlagContext;
use App\Values\FeatureFlagResponse;
use App\Values\PolicyDefinition;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Lottery;
use Illuminate\Support\Str;

class GetFeatureFlagStatus
{
    use AsAction;

    public function handle(FeatureFlagValue $featureFlag, FeatureFlagContext $context): FeatureFlagResponse
    {
        try {
            $featureFlag = FeatureFlag::query()
                ->where('name', $featureFlag->name)
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            $response = FeatureFlagResponse::from($featureFlag->name, null, false);

            FeatureFlagMissedEvent::dispatch(FeatureFlagValue::withoutValidation(['name' => $featureFlag->name]), $context, $response);

            return $response;
        }

        $response = $this->evaluate($featureFlag, $context);

        // Dispatch the feature flag evaluation event
        FeatureFlagEvaluatedEvent::dispatch(
            FeatureFlagValue::from($featureFlag),
            $context,
            $response,
        );

        return $response;
    }

    protected function evaluate(FeatureFlag $featureFlag, FeatureFlagContext $context): FeatureFlagResponse
    {
        // If the feature flag has no specific status for the application and environment,
        // return with inactive status
        if (FeatureFlagStatus::whereFeatureFlag($featureFlag)->count() === 0) {
            return FeatureFlagResponse::from(featureFlag: $featureFlag->name, value: null, active: false);
        }

        try {
            $status = FeatureFlagStatus::query()
                ->whereApplication($context->appName)
                ->whereEnvironment($context->environment)
                ->whereFeatureFlag($featureFlag)
                ->firstOrFail();

            if ($featureFlag->status === false || $status->status === false) {
                return FeatureFlagResponse::from(featureFlag: $featureFlag->name, value: null, active: $featureFlag->status && $status->status);
            }

            $active = true;
            if (($status->definition?->count() ?? 0) > 0) {
                $active = $this->evaluatePolicy($status, $context);
            }

            if ($active && ($status->rollout_percentage ?? 100) < 100) {
                $active = $this->evaluateRollout($status, $context);
            }

            if ($active) {
                $value = $this->selectVariantValue($status, $context);

                return FeatureFlagResponse::from(featureFlag: $featureFlag->name, value: $value, active: $active);
            }

            return FeatureFlagResponse::from(featureFlag: $featureFlag->name, value: null, active: $active);
        } catch (ModelNotFoundException $e) {
            return FeatureFlagResponse::from(featureFlag: $featureFlag->name, value: null, active: false);
        }
    }

    protected function evaluatePolicy(FeatureFlagStatus $policyContainer, FeatureFlagContext $context): bool
    {
        // If there are no policy definitions, return true by default
        if ($policyContainer->definition === null || $policyContainer->definition->isEmpty()) {
            return true;
        }

        // Find all operator positions
        $operatorPositions = [];
        /** @var PolicyDefinition $policyDefinition */
        foreach ($policyContainer->definition as $index => $policyDefinition) {
            if ($policyDefinition->type === PolicyDefinitionType::OPERATOR) {
                $operatorPositions[] = $index;
            }
        }

        // If there are no operators, evaluate a single expression
        if (empty($operatorPositions)) {
            /** @var PolicyDefinition $policyDefinition */
            $policyDefinition = $policyContainer->definition->first();
            if ($policyDefinition->type === PolicyDefinitionType::EXPRESSION) {
                return $this->evaluateExpression($policyDefinition, $context);
            }
            if ($policyDefinition->type === PolicyDefinitionType::DATETIME) {
                return $this->evaluateDateRange($policyDefinition);
            }
            if ($policyDefinition->type === PolicyDefinitionType::POLICY) {
                return $this->evaluatePolicy(
                    FeatureFlagStatus::find($policyDefinition->subject) ?? new FeatureFlagStatus(),
                    $context
                );
            }

            return false;
        }

        // Process each expression and operator
        $result = null;
        $currentOperator = Boolean::AND->value;

        foreach ($policyContainer->definition as $index => $policyDefinition) {
            // If it's an operator, update the current operator and continue
            if ($policyDefinition->type === PolicyDefinitionType::OPERATOR) {
                $currentOperator = $policyDefinition->subject;

                continue;
            }

            // Evaluate the current expression
            $expressionResult = false;
            if ($policyDefinition->type === PolicyDefinitionType::EXPRESSION) {
                $expressionResult = $this->evaluateExpression($policyDefinition, $context);
            } elseif ($policyDefinition->type === PolicyDefinitionType::DATETIME) {
                $expressionResult = $this->evaluateDateRange($policyDefinition);
            } elseif ($policyDefinition->type === PolicyDefinitionType::POLICY) {
                $expressionResult = $this->evaluatePolicy(
                    FeatureFlagStatus::find($policyDefinition->subject) ?? new FeatureFlagStatus(),
                    $context
                );
            }

            // If this is the first result, just store it
            if ($result === null) {
                $result = $expressionResult;

                continue;
            }

            // Otherwise, apply the current operator
            $result = $this->applyOperator($result, $expressionResult, $currentOperator);

            // Short-circuit evaluation
            if ($result && $currentOperator === Boolean::OR->value) {
                return true;
            }
            if (!$result && $currentOperator === Boolean::AND->value) {
                return false;
            }
        }

        return $result ?? false;
    }

    protected function evaluateExpression(PolicyDefinition $policyDefinition, FeatureFlagContext $context): bool
    {
        $contextValue = data_get($context, $policyDefinition->subject) ?? data_get($context->scope, $policyDefinition->subject);

        // Don't immediately return false for null values, as they might be part of an OR expression
        if ($contextValue === null) {
            // For equality checks, a null value can never be equal to anything except null
            if ($policyDefinition->operator === 'equal' && !collect($policyDefinition->values)->contains(null)) {
                return false;
            }

            // For inequality checks, a null value might be considered not equal to non-null values
            if ($policyDefinition->operator === 'not_equal' && collect($policyDefinition->values)->contains(null)) {
                return false;
            }

            // Return false for all other operators when context value is null
            if (!in_array($policyDefinition->operator, ['equal', 'not_equal'])) {
                return false;
            }
        }

        $result = $this->compareExpressionValue(
            operator: $policyDefinition->operator,
            contextValue: $contextValue,
            policyValues: collect($policyDefinition->values)
        );

        return $result;
    }

    protected function compareExpressionValue(PolicyDefinitionMatchOperator $operator, mixed $contextValue, Collection $policyValues): bool
    {
        try {
            if (is_scalar($contextValue) && $policyValues->count() === 1) {
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
                    default => false,
                };
            }

            if (is_scalar($contextValue)) {
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
                    default => false,
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
                PolicyDefinitionMatchOperator::MATCHES_ALL => $contextValues->filter(fn (mixed $contextValue) => !$policyValues->reduce(fn (bool $carry, mixed $policyValue) => $carry && Str::of($contextValue)->isMatch($policyValue), true))->isEmpty(),
                PolicyDefinitionMatchOperator::NOT_MATCHES_ALL => $contextValues->filter(fn (mixed $contextValue) => !$policyValues->reduce(fn (bool $carry, mixed $policyValue) => $carry && !Str::of($contextValue)->isMatch($policyValue), true))->isEmpty(),
                PolicyDefinitionMatchOperator::MATCHES_ANY => $contextValues->filter(fn (mixed $contextValue) => !$policyValues->reduce(fn (bool $carry, mixed $policyValue) => $carry || Str::of($contextValue)->isMatch($policyValue), false))->isEmpty(),
                PolicyDefinitionMatchOperator::NOT_MATCHES_ANY => $contextValues->filter(fn (mixed $contextValue) => !$policyValues->reduce(fn (bool $carry, mixed $policyValue) => !$carry || !Str::of($contextValue)->isMatch($policyValue), true))->isEmpty(),
                PolicyDefinitionMatchOperator::ONE_OF => $contextValues->filter(fn (mixed $contextValue) => !$policyValues->contains($contextValue))->isEmpty(),
                PolicyDefinitionMatchOperator::NOT_ONE_OF => $contextValues->filter(fn (mixed $contextValue) => $policyValues->contains($contextValue))->isEmpty(),
                default => false,
            };
        } catch (\Throwable) {
            return false;
        }
    }

    protected function evaluateExpressionResults($values): bool
    {
        // If empty, return false
        if ($values->isEmpty()) {
            return false;
        }

        $result = $values->shift();

        // If only one item and it's a boolean, return it
        if ($values->isEmpty() && is_bool($result)) {
            return $result;
        }

        while ($values->isNotEmpty()) {
            $operator = $values->shift();
            $nextValue = $values->isEmpty() ? null : $values->shift();

            // No operator between policy expressions, default to AND
            if (is_bool($operator) && is_bool($nextValue)) {
                $result = $this->applyOperator($result, $operator, Boolean::AND->value);
                $result = $this->applyOperator($result, $nextValue, Boolean::AND->value);

                continue;
            }

            // No operator and also the end of the policy expressions, default to AND
            if (is_bool($operator) && $nextValue === null) {
                $result = $this->applyOperator($result, $operator, Boolean::AND->value);

                continue;
            }

            // If we have an operator and a next value
            if ($operator !== null && $nextValue !== null && is_bool($nextValue)) {
                // Handle OR operator: if result is already true, we can short-circuit
                if ($result === true && $operator->subject === Boolean::OR->value) {
                    return true;
                }

                // Handle AND operator: if result is already false, we can short-circuit
                if ($result === false && $operator->subject === Boolean::AND->value) {
                    return false;
                }

                // Otherwise, apply the operator
                $result = $this->applyOperator($result, $nextValue, $operator->subject);
            }
        }

        return $result;
    }

    protected function applyOperator(bool $left, bool $right, string $operator): bool
    {
        return match ($operator) {
            'AND' => $left && $right,
            'OR' => $left || $right,
            'AND NOT' => $left && !$right,
            'XOR' => $left xor $right,
            default => false,
        };
    }

    protected function evaluateDateRange(PolicyDefinition $policyDefinition): bool
    {
        $now = now();
        $comparisonDate = Carbon::parse($policyDefinition->subject, 'UTC');

        if ($policyDefinition->operator === PolicyDefinitionMatchOperator::LESS_THAN_EQUAL) {
            return $now->isBefore($comparisonDate) || $now->equalTo($comparisonDate);
        }

        return $now->isAfter($comparisonDate) || $now->equalTo($comparisonDate);
    }

    protected function evaluateRollout(FeatureFlagStatus $status, FeatureFlagContext $context): bool
    {
        $rolloutPercentage = $status->rollout_percentage ?? 100;

        if ($rolloutPercentage >= 100) {
            return true;
        }

        if ($rolloutPercentage <= 0) {
            return false;
        }

        $rolloutStrategy = $status->rollout_strategy ?? RolloutStrategy::RANDOM;

        if ($rolloutStrategy->is(RolloutStrategy::RANDOM)) {
            return $this->evaluateRandomRollout($rolloutPercentage);
        }

        if ($rolloutStrategy->is(RolloutStrategy::CONTEXT)) {
            return $this->evaluateStickyRollout($status, $context, $rolloutPercentage);
        }

        return false;
    }

    protected function evaluateRandomRollout(int $rolloutPercentage): bool
    {
        return Lottery::odds($rolloutPercentage, 100)->choose();
    }

    protected function evaluateStickyRollout(FeatureFlagStatus $status, FeatureFlagContext $context, int $rolloutPercentage): bool
    {
        $rolloutContext = $status->rollout_context ?? [];

        if (empty($rolloutContext)) {
            return $this->evaluateRandomRollout($rolloutPercentage);
        }

        $contextValues = [];

        foreach ($rolloutContext as $contextKey) {
            $value = data_get($context, $contextKey) ?? data_get($context->scope, $contextKey);
            if ($value !== null) {
                $contextValues[] = (string) $value;
            }
        }

        if (empty($contextValues)) {
            return $this->evaluateRandomRollout($rolloutPercentage);
        }

        $concatenatedValues = implode('|', $contextValues);
        $normalizedHash = Murmur::hash3_int($concatenatedValues) % 100 + 1;

        return $normalizedHash <= $rolloutPercentage;
    }

    protected function selectVariantValue(FeatureFlagStatus $status, FeatureFlagContext $context): mixed
    {
        $variants = $status->variants ?? [];

        if (empty($variants)) {
            return null;
        }

        $variantStrategy = $status->variant_strategy ?? VariantStrategy::RANDOM;

        if ($variantStrategy->is(VariantStrategy::CONTEXT)) {
            return $this->selectStickyVariantValue($status, $context, $variants);
        }

        return $this->selectRandomVariantValue($variants);
    }

    protected function selectStickyVariantValue(FeatureFlagStatus $status, FeatureFlagContext $context, array $variants): mixed
    {
        $variantContext = $status->variant_context ?? [];

        if (empty($variantContext)) {
            return $this->selectRandomVariantValue($variants);
        }

        $contextValues = [];
        foreach ($variantContext as $contextKey) {
            $value = data_get($context, $contextKey) ?? data_get($context->scope, $contextKey);
            if ($value !== null) {
                $contextValues[] = (string) $value;
            }
        }

        if (empty($contextValues)) {
            return $this->selectRandomVariantValue($variants);
        }

        $totalPercentage = array_sum(array_column($variants, 'percentage'));
        if ($totalPercentage <= 0) {
            return null;
        }

        $concatenatedValues = implode('|', $contextValues);
        $hash = Murmur::hash3_int($concatenatedValues);

        $normalizedHash = ($hash % $totalPercentage) + 1;

        $cumulativePercentage = 0;
        foreach ($variants as $variant) {
            $cumulativePercentage += $variant['percentage'];
            if ($normalizedHash <= $cumulativePercentage) {
                return $this->convertVariantValue($variant['value'], $variant['type']);
            }
        }

        return $this->convertVariantValue($variants[0]['value'], $variants[0]['type']);
    }

    protected function selectRandomVariantValue(array $variants): mixed
    {
        $totalPercentage = array_sum(array_column($variants, 'percentage'));

        if ($totalPercentage <= 0) {
            return null;
        }

        $random = random_int(1, max(100, $totalPercentage));

        $cumulativePercentage = 0;
        foreach ($variants as $variant) {
            $cumulativePercentage += $variant['percentage'];
            if ($random <= $cumulativePercentage) {
                return $this->convertVariantValue($variant['value'], $variant['type']);
            }
        }

        return $this->convertVariantValue($variants[0]['value'], $variants[0]['type']);
    }

    protected function convertVariantValue(string $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            'string' => $value,
            default => $value,
        };
    }
}
