import { FeatureFlag, FeatureFlagStatus, PolicyDefinitionType, RolloutStrategy, VariantStrategy } from '@/Application';
import { ulid } from 'ulidx';
import { create } from 'zustand';
import { devtools } from 'zustand/middleware';

export type Variant = {
    id: string;
    value: string;
    type: 'string' | 'integer' | 'float' | 'json';
    percentage: number;
};

interface FeatureFlagState {
    featureFlag: FeatureFlag | null;

    // Feature flag actions
    setFeatureFlag: (featureFlag: FeatureFlag | null) => void;

    // Status CRUD actions
    updateStatus: (status: FeatureFlagStatus) => void;
    deleteStatus: (statusId: string) => void;
    addStatus: () => void;

    // Rollout management for specific status
    updateStatusRollout: (
        statusId: string,
        rollout: Partial<{
            percentage: number;
            strategy: RolloutStrategy;
            context: string[];
        }>,
    ) => void;

    // Definition management for specific status
    addStatusPolicyDefinition: (statusId: string) => void;

    // Variant management for specific status
    setStatusVariants: (statusId: string, variants: Variant[]) => void;
    updateStatusVariant: (statusId: string, variantId: string, updates: Partial<Variant>) => void;
    removeStatusVariant: (statusId: string, variantId: string) => void;
    addStatusVariant: (statusId: string) => void;
    distributeStatusVariantsEvenly: (statusId: string) => void;
    updateStatusVariantStickiness: (
        statusId: string,
        variantStickiness: Partial<{
            strategy: VariantStrategy;
            context: string[];
        }>,
    ) => void;
}

export const emptyStatus = (): FeatureFlagStatus => ({
    definition: [],
    application: null,
    environment: null,
    feature_flag: null,
    status: false,
    id: ulid(),
    rollout_strategy: RolloutStrategy.RANDOM,
    rollout_percentage: 100,
    rollout_context: [],
    variants: [],
    variant_strategy: VariantStrategy.RANDOM,
    variant_context: [],
});

export const useFeatureFlagStore = create<FeatureFlagState>()(
    devtools((set) => ({
        featureFlag: null,
        setFeatureFlag: (featureFlag) => set({ featureFlag }),
        updateStatus: (updatedStatus) =>
            set((state) => {
                if (!state.featureFlag) return state;
                const statuses = state.featureFlag.statuses || [];
                return {
                    featureFlag: {
                        ...state.featureFlag,
                        statuses: statuses.map((status) => (status.id === updatedStatus.id ? updatedStatus : status)),
                    },
                };
            }),
        deleteStatus: (statusId) =>
            set((state) => {
                if (!state.featureFlag) return state;
                const statuses = state.featureFlag.statuses || [];
                return {
                    featureFlag: {
                        ...state.featureFlag,
                        statuses: statuses.filter((status) => status.id !== statusId),
                    },
                };
            }),
        addStatus: () =>
            set((state) => {
                if (!state.featureFlag) return state;
                const statuses = state.featureFlag.statuses || [];

                return {
                    featureFlag: {
                        ...state.featureFlag,
                        statuses: [...statuses, emptyStatus()],
                    },
                };
            }),

        // Rollout management for specific status
        updateStatusRollout: (statusId, rolloutUpdates) =>
            set((state) => {
                if (!state.featureFlag) return state;
                const statuses = state.featureFlag.statuses || [];
                return {
                    featureFlag: {
                        ...state.featureFlag,
                        statuses: statuses.map((status) => {
                            if (status.id === statusId) {
                                return {
                                    ...status,
                                    rollout_percentage: rolloutUpdates.percentage ?? status.rollout_percentage,
                                    rollout_strategy: rolloutUpdates.strategy ?? status.rollout_strategy,
                                    rollout_context: rolloutUpdates.context ?? status.rollout_context,
                                };
                            }
                            return status;
                        }),
                    },
                };
            }),

        addStatusPolicyDefinition: (statusId) =>
            set((state) => {
                if (!state.featureFlag) return state;
                const statuses = state.featureFlag.statuses || [];
                return {
                    featureFlag: {
                        ...state.featureFlag,
                        statuses: statuses.map((status) => {
                            if (status.id === statusId) {
                                const newCondition = {
                                    type: PolicyDefinitionType.EXPRESSION,
                                    subject: '',
                                    operator: null,
                                    values: [],
                                };
                                const updatedDefinition = [...(status.definition || []), newCondition];
                                return { ...status, definition: updatedDefinition };
                            }
                            return status;
                        }),
                    },
                };
            }),

        // Variant management for specific status
        setStatusVariants: (statusId, variants) =>
            set((state) => {
                if (!state.featureFlag) return state;
                const statuses = state.featureFlag.statuses || [];
                return {
                    featureFlag: {
                        ...state.featureFlag,
                        statuses: statuses.map((status) => {
                            if (status.id === statusId) {
                                return { ...status, variants };
                            }
                            return status;
                        }),
                    },
                };
            }),

        updateStatusVariant: (statusId, variantId, updates) =>
            set((state) => {
                if (!state.featureFlag) return state;
                const statuses = state.featureFlag.statuses || [];
                return {
                    featureFlag: {
                        ...state.featureFlag,
                        statuses: statuses.map((status) => {
                            if (status.id === statusId) {
                                const updatedVariants = ((status.variants as Variant[]) || []).map((variant) =>
                                    variant.id === variantId ? { ...variant, ...updates } : variant,
                                );
                                return { ...status, variants: updatedVariants };
                            }
                            return status;
                        }),
                    },
                };
            }),

        removeStatusVariant: (statusId, variantId) =>
            set((state) => {
                if (!state.featureFlag) return state;
                const statuses = state.featureFlag.statuses || [];
                return {
                    featureFlag: {
                        ...state.featureFlag,
                        statuses: statuses.map((status) => {
                            if (status.id === statusId) {
                                const updatedVariants = ((status.variants as Variant[]) || []).filter(
                                    (variant) => variant.id !== variantId,
                                );
                                return { ...status, variants: updatedVariants };
                            }
                            return status;
                        }),
                    },
                };
            }),

        addStatusVariant: (statusId) =>
            set((state) => {
                if (!state.featureFlag) return state;
                const statuses = state.featureFlag.statuses || [];
                return {
                    featureFlag: {
                        ...state.featureFlag,
                        statuses: statuses.map((status) => {
                            if (status.id === statusId) {
                                const newVariant: Variant = {
                                    id: ulid(),
                                    value: '',
                                    type: 'string',
                                    percentage: 0,
                                };
                                const updatedVariants = [...((status.variants as Variant[]) || []), newVariant];
                                return { ...status, variants: updatedVariants };
                            }
                            return status;
                        }),
                    },
                };
            }),

        distributeStatusVariantsEvenly: (statusId) =>
            set((state) => {
                if (!state.featureFlag) return state;
                const statuses = state.featureFlag.statuses || [];
                return {
                    featureFlag: {
                        ...state.featureFlag,
                        statuses: statuses.map((status) => {
                            if (status.id === statusId) {
                                const variants = (status.variants as Variant[]) || [];
                                if (variants.length === 0) return status;

                                const count = variants.length;
                                const basePercentage = Math.floor(100 / count);
                                const remainder = 100 % count;

                                const updatedVariants = variants.map((variant, index) => ({
                                    ...variant,
                                    percentage: basePercentage + (index < remainder ? 1 : 0),
                                }));

                                return { ...status, variants: updatedVariants };
                            }
                            return status;
                        }),
                    },
                };
            }),

        updateStatusVariantStickiness: (statusId, variantStickinessUpdates) =>
            set((state) => {
                if (!state.featureFlag) return state;
                const statuses = state.featureFlag.statuses || [];
                return {
                    featureFlag: {
                        ...state.featureFlag,
                        statuses: statuses.map((status) => {
                            if (status.id === statusId) {
                                return {
                                    ...status,
                                    variant_strategy: variantStickinessUpdates.strategy ?? status.variant_strategy,
                                    variant_context:
                                        variantStickinessUpdates.strategy === VariantStrategy.RANDOM
                                            ? null
                                            : (variantStickinessUpdates.context ?? status.variant_context),
                                };
                            }
                            return status;
                        }),
                    },
                };
            }),
    })),
);
