// .vitepress/theme/index.js
import '../../../resources/css/app.css';
import OldVersionWarning from './OldVersionWarning.vue';
import VersionSwitcher from './VersionSwitcher.vue';
import './custom.css';
import 'lite-youtube-embed/src/lite-yt-embed.css';
import 'viewerjs/dist/viewer.min.css';
import { inBrowser } from 'vitepress';
import DefaultTheme from 'vitepress/theme-without-fonts';
import { h } from 'vue';

if (inBrowser) {
    // @ts-ignore
    import('lite-youtube-embed');
}

export default {
    extends: DefaultTheme,
    enhanceApp({ app }) {
        app.component('VersionSwitcher', VersionSwitcher);
    },
    Layout() {
        return h(DefaultTheme.Layout, null, {
            'home-hero-before': () => h(OldVersionWarning),
            'doc-before': () => h(OldVersionWarning),
            'not-found': () => h(OldVersionWarning),
        });
    },
};
