<script setup>
// @ts-ignore
import {AlignHorizontalSpaceAround} from 'lucide-vue-next';
</script>
# Multi-Variant Experiments

![Feature Flag Variant Tab](../../screenshots/feature-flags-variants-tab.png){.light-only}
![Feature Flag Variant Tab](../../screenshots/dark/feature-flags-variants-tab.png){.dark-only}

Variants allow you to define different values for a feature flag, enabling A/B testing or feature toggling with multiple options.

Each variant has a type, a value, and a weight percentage that determines how often it will be returned when the flag is active.

You must have at least two variants, and the total weight of all variants must equal 100%.

To evenly distribute variants, you can click the <kbd><AlignHorizontalSpaceAround /> Distribute Evenly</kbd> button, which will automatically set each variant's weight to an equal percentage.

Similar to the [Rollout configuration](#rollout), you can specify the Stickiness behavior, which determines how the variant behaves for a user across multiple requests.

![Feature Flag with Variant Configuration](../../screenshots/feature-flags-variants-configured.png){.light-only}
![Feature Flag with Variant Configuration](../../screenshots/dark/feature-flags-variants-configured.png){.dark-only}
