<script setup>
// @ts-ignore
import { CirclePlus, Pencil } from 'lucide-vue-next';
</script>

# Policies

Policies are used to define the conditions under which a feature is active. Policies can
be created as re-usable sets of conditions, and they function exactly as if they were created within a feature flag
directly.

> [!TIP]
> For in-depth details of how a policy is structured, review the [Core Policies documentation](../core/policies).

![Policy Listing](../screenshots/policies-initial.png){.light-only}
![Policy Listing](../screenshots/dark/policies-initial.png){.dark-only}

## Creating a Policy

To create a new policy, click the <kbd><CirclePlus /> New Policy</kbd> button.

![Create Policy Form](../screenshots/policies-form-create.png){.light-only}
![Create Policy Form](../screenshots/dark/policies-form-create.png){.dark-only}


## Editing a Policy

To Edit a policy, click the <Pencil /> edit button next to the policy you want to modify.

![Policy Edit](../screenshots/policies-edit.png){.light-only}
![Policy Edit](../screenshots/dark/policies-edit.png){.dark-only}

## Adding Conditions to a Policy

Policy Conditions can be one of the following:

* Expression: A comparison expression that is applied to the [Beacon Context](../core/context).
* Policy: A reference to another Policy that is included in this Policy.
* Date/Time: A date or time condition that must be met.
* Operator: A logical operator that combines multiple other condition types.

### Expression Conditions

Expression conditions allow you to define a condition based on the Beacon Context.

To use an Expression, set the <kbd>Type</kbd> to <kbd>Expression</kbd>, then enter a Context property, choose an [Operator](../core/policies#Operators), and enter one or more values to compare the Context value against.

![Policy Expression Condition](../screenshots/policies-form-expression.png){.light-only}
![Policy Expression Condition](../screenshots/dark/policies-form-expression.png){.dark-only}

> [!TIP]
> Use Laravel's dot-notation to reference nested properties in the Beacon Context.

### Policy Conditions

Policy conditions allow you to include another Policy within the current Policy. This is useful for re-using common conditions across multiple Policies.

Policy conditions are inlined, meaning that the conditions of the referenced Policy are evaluated as part of the current Policy.

![Policy Condition](../screenshots/policies-form-policy.png){.light-only}
![Policy Condition](../screenshots/dark/policies-form-policy.png){.dark-only}

> [!INFO]
> You cannot choose a Policy that references the current Policy, as this would create an infinite loop.

To use a Policy, set the <kbd>Type</kbd> to <kbd>Policy</kbd>, then select the Policy you want to include from the dropdown.

### Date/Time Conditions

Date/Time conditions allow you to specify a condition based on a specific date or time.

To use a Date/Time condition, set the <kbd>Type</kbd> to <kbd>Date/Time</kbd>, then enter a Context property, choose an [Operator](../core/policies#Operators-1), and choose a date and/or time value to compare the Context value against.

![Date/Time Condition](../screenshots/policies-form-datetime.png){.light-only}
![Date/Time Condition](../screenshots/dark/policies-form-datetime.png){.dark-only}

### Operator Conditions

Operator conditions allow you to combine multiple other condition types using logical operators.

To use an Operator condition, set the <kbd>Type</kbd> to <kbd>Operator</kbd>, then select the logical operator you want to use (AND, OR, AND NOT, XOR).

![Operator Condition](../screenshots/policies-form-operator.png){.light-only}
![Operator Condition](../screenshots/dark/policies-form-operator.png){.dark-only}

> [!WARNING]
> Operators should only be used between two other conditions. Trailing operators will be ignored.

Using Operator conditions, you can combine multiple conditions together to create complex policies:

![Complex Policy](../screenshots/policies-form-complex.png){.light-only}
![Complex Policy](../screenshots/dark/policies-form-complex.png){.dark-only}
