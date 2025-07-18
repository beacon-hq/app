import { Policy, PolicyDefinition, PolicyDefinitionCollection, PolicyDefinitionType } from '@/Application';
import { create } from 'zustand';
import { devtools } from 'zustand/middleware';

interface PolicyState {
    policy: Policy | null;

    // Policy actions
    setPolicy: (policy: Policy) => void;
    updatePolicy: (policy: Policy) => void;

    // Policy definition management
    updatePolicyDefinition: (definition: PolicyDefinitionCollection) => void;
    addPolicyDefinition: () => void;
    removePolicyDefinition: (index: number) => void;
    updatePolicyDefinitionItem: (index: number, definition: PolicyDefinition) => void;
}

export const usePolicyStore = create<PolicyState>()(
    devtools((set) => ({
        policy: null,

        setPolicy: (policy) => set({ policy }),

        updatePolicy: (updatedPolicy) =>
            set((state) => ({
                policy: updatedPolicy,
            })),

        updatePolicyDefinition: (definition) =>
            set((state) => {
                if (!state.policy) return state;
                return {
                    policy: {
                        ...state.policy,
                        definition,
                    },
                };
            }),

        addPolicyDefinition: () =>
            set((state) => {
                if (!state.policy) return state;
                const currentDefinition = state.policy.definition || [];
                const newDefinition: PolicyDefinition = {
                    type: PolicyDefinitionType.EXPRESSION,
                    subject: '',
                    operator: null,
                    values: [],
                };
                return {
                    policy: {
                        ...state.policy,
                        definition: [...currentDefinition, newDefinition],
                    },
                };
            }),

        removePolicyDefinition: (index) =>
            set((state) => {
                if (!state.policy || !state.policy.definition) return state;
                const definition = [...state.policy.definition];
                definition.splice(index, 1);
                return {
                    policy: {
                        ...state.policy,
                        definition,
                    },
                };
            }),

        updatePolicyDefinitionItem: (index, updatedDefinition) =>
            set((state) => {
                if (!state.policy || !state.policy.definition) return state;
                const definition = [...state.policy.definition];
                definition[index] = updatedDefinition;
                return {
                    policy: {
                        ...state.policy,
                        definition,
                    },
                };
            }),
    }))
);
