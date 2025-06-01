<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Boolean;
use App\Enums\PolicyDefinitionMatchOperator;
use App\Enums\PolicyDefinitionType;
use App\Models\FeatureFlagStatus;
use App\Services\PolicyService;
use App\Values\FeatureFlag;
use App\Values\FeatureFlagContext;
use App\Values\FeatureFlagResponse;
use App\Values\Policy as PolicyValue;
use App\Values\PolicyDefinition;
use Carbon\Carbon;
use Illuminate\Support\Collection as LaravelCollection;
use Illuminate\Support\Str;

class FeatureFlagStatusRepository
{
    public function __construct(protected PolicyService $policyService)
    {
    }

    public function first(FeatureFlag $featureFlag, FeatureFlagContext $context): FeatureFlagResponse
    {
        $status = FeatureFlagStatus::query()
            ->whereApplication($context->appName)
            ->whereEnvironment($context->environment)
            ->whereFeatureFlag($featureFlag)
            ->firstOrFail();

        // No application/environment policy
        if ($featureFlag->status === false || $status->status === false || ($status->definition?->count() ?? 0) === 0) {
            return FeatureFlagResponse::from(featureFlag: $featureFlag->name, value: null, active: $featureFlag->status && $status->status);
        }

        return FeatureFlagResponse::from(featureFlag: $featureFlag->name, value: null, active: $this->evaluatePolicy($status, $context));
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
        if ($policyDefinition->values === null || $policyDefinition->values->count() < 2) {
            return false;
        }

        $now = now();
        $startDate = Carbon::parse($policyDefinition->values->get(0));
        $endDate = Carbon::parse($policyDefinition->values->get(1));

        return $now->between($startDate, $endDate);
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

            if ($policyDefinition->type === PolicyDefinitionType::DATE_RANGE) {
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

            $result = $this->applyOperator($result, $nextValue, $operator->subject);
        }

        return $result;
    }
}
