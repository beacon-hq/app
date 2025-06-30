<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Boolean;
use App\Enums\PolicyDefinitionMatchOperator;
use App\Enums\PolicyDefinitionType;
use App\Enums\RolloutStrategy;
use App\Enums\VariantStrategy;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Services\PolicyService;
use App\Values\FeatureFlagContext;
use App\Values\FeatureFlagResponse;
use App\Values\Policy as PolicyValue;
use App\Values\PolicyDefinition;
use Carbon\Carbon;
use Illuminate\Support\Collection as LaravelCollection;
use Illuminate\Support\Str;
use lastguest\Murmur;

class FeatureFlagStatusRepository
{
    public function __construct(protected PolicyService $policyService)
    {
    }

    public function evaluate(FeatureFlag $featureFlag, FeatureFlagContext $context): FeatureFlagResponse
    {
        $status = FeatureFlagStatus::query()
            ->whereApplication($context->appName)
            ->whereEnvironment($context->environment)
            ->whereFeatureFlag($featureFlag)
            ->firstOrFail();

        if ($featureFlag->status === false || $status->status === false || ($status->definition?->count() ?? 0) === 0) {
            return FeatureFlagResponse::from(featureFlag: $featureFlag->name, value: null, active: $featureFlag->status && $status->status);
        }

        $active = $this->evaluatePolicy($status, $context);

        if ($active && ($status->rollout_percentage ?? 100) < 100) {
            $active = $this->evaluateRollout($status, $context);
        }

        if ($active) {
            $value = $this->selectVariantValue($status, $context);

            return FeatureFlagResponse::from(featureFlag: $featureFlag->name, value: $value, active: $active);
        }

        return FeatureFlagResponse::from(featureFlag: $featureFlag->name, value: null, active: $active);
    }

    protected function evaluateExpression(PolicyDefinition $policyDefinition, FeatureFlagContext $context): bool
    {
        $contextValue = \data_get($context, $policyDefinition->subject) ?? \data_get($context->scope, $policyDefinition->subject);
        if ($contextValue === null) {
            return false;
        }

        return $this->compareExpressionValue(operator: $policyDefinition->operator, contextValue: $contextValue, policyValues: $policyDefinition->values);
    }

    protected function compareExpressionValue(PolicyDefinitionMatchOperator $operator, mixed $contextValue, LaravelCollection $policyValues): bool
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
                PolicyDefinitionMatchOperator::MATCHES_ALL => $contextValues->filter(fn (mixed $contextValue) => !$policyValues->reduce(fn (bool $carry, mixed $policyValue) => $carry && Str::of($contextValue)->isMatch($policyValue), true))->isEmpty(),
                PolicyDefinitionMatchOperator::NOT_MATCHES_ALL => $contextValues->filter(fn (mixed $contextValue) => !$policyValues->reduce(fn (bool $carry, mixed $policyValue) => $carry && !Str::of($contextValue)->isMatch($policyValue), true))->isEmpty(),
                PolicyDefinitionMatchOperator::MATCHES_ANY => $contextValues->filter(fn (mixed $contextValue) => !$policyValues->reduce(fn (bool $carry, mixed $policyValue) => $carry || Str::of($contextValue)->isMatch($policyValue), false))->isEmpty(),
                PolicyDefinitionMatchOperator::NOT_MATCHES_ANY => $contextValues->filter(fn (mixed $contextValue) => !$policyValues->reduce(fn (bool $carry, mixed $policyValue) => !$carry || !Str::of($contextValue)->isMatch($policyValue), true))->isEmpty(),
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

    protected function evaluateDateRange(PolicyDefinition $policyDefinition): bool
    {
        $now = now();
        $comparisonDate = Carbon::parse($policyDefinition->subject, 'UTC');

        if ($policyDefinition->operator === PolicyDefinitionMatchOperator::LESS_THAN_EQUAL) {
            return $now->isBefore($comparisonDate) || $now->equalTo($comparisonDate);
        }

        return $now->isAfter($comparisonDate) || $now->equalTo($comparisonDate);
    }

    protected function evaluatePolicy(FeatureFlagStatus|PolicyValue $policyContainer, FeatureFlagContext $context): bool
    {
        return $this->evaluateExpressionResults($policyContainer->definition->map(function (PolicyDefinition $policyDefinition) use ($context) {
            if ($policyDefinition->type === PolicyDefinitionType::EXPRESSION) {
                return $this->evaluateExpression($policyDefinition, $context);
            }

            if ($policyDefinition->type === PolicyDefinitionType::POLICY) {
                return $this->evaluatePolicy($this->policyService->find($policyDefinition->subject), $context);
            }

            if ($policyDefinition->type === PolicyDefinitionType::DATETIME) {
                return $this->evaluateDateRange($policyDefinition);
            }

            return $policyDefinition;
        })->toBase());
    }

    protected function evaluateExpressionResults(LaravelCollection $values): bool
    {
        $result = $values->shift();

        while ($values->isNotEmpty()) {
            /** @var PolicyDefinition|bool $operator */
            $operator = $values->shift();
            $nextValue = $values->shift();

            // No operator between policy expressions, default to AND
            if (\is_bool($operator) && \is_bool($nextValue)) {
                $result = $this->applyOperator(
                    $this->applyOperator($result, $operator, Boolean::AND->value),
                    $nextValue,
                    Boolean::AND->value
                );

                continue;
            }

            // No operator and also the end of the policy expressions, default to AND
            if (\is_bool($operator) && $nextValue === null) {
                $result = $this->applyOperator($result, $operator, Boolean::AND->value);

                continue;
            }

            if ($result === true && Boolean::OR->is(Boolean::from($operator->subject))) {
                break;
            }

            if ($result === false && Boolean::AND->is(Boolean::from($operator->subject))) {
                break;
            }

            if ($nextValue !== null && \is_bool($nextValue)) {
                $result = $this->applyOperator($result, $nextValue, $operator->subject);
            }
        }

        return $result;
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

        if ($rolloutStrategy === RolloutStrategy::RANDOM) {
            return $this->evaluateRandomRollout($rolloutPercentage);
        }

        if ($rolloutStrategy === RolloutStrategy::CONTEXT) {
            return $this->evaluateStickyRollout($status, $context, $rolloutPercentage);
        }

        return false;
    }

    protected function evaluateRandomRollout(int $rolloutPercentage): bool
    {
        return \random_int(1, 100) <= $rolloutPercentage;
    }

    protected function evaluateStickyRollout(FeatureFlagStatus $status, FeatureFlagContext $context, int $rolloutPercentage): bool
    {
        $rolloutContext = $status->rollout_context ?? [];

        if (empty($rolloutContext)) {
            return $this->evaluateRandomRollout($rolloutPercentage);
        }

        $contextValues = [];

        foreach ($rolloutContext as $contextKey) {
            $value = \data_get($context, $contextKey) ?? \data_get($context->scope, $contextKey);
            if ($value !== null) {
                $contextValues[] = (string) $value;
            }
        }

        if (empty($contextValues)) {
            return $this->evaluateRandomRollout($rolloutPercentage);
        }

        $concatenatedValues = \implode('|', $contextValues);
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

        if ($variantStrategy === VariantStrategy::CONTEXT) {
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
            $value = \data_get($context, $contextKey) ?? \data_get($context->scope, $contextKey);
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

        $concatenatedValues = \implode('|', $contextValues);
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
