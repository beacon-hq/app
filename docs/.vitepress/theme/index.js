// .vitepress/theme/index.js
import Layout from './BeaconLayout.vue';
import './tailwind.postcss';

export default {
    // override the Layout with a wrapper component that
    // injects the slots
    Layout,
};
