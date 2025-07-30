// .vitepress/theme/index.js
import '../../../resources/css/app.css';
import OldVersionWarning from './OldVersionWarning.vue';
import VersionSwitcher from './VersionSwitcher.vue';
import './custom.css';
import 'lite-youtube-embed/src/lite-yt-embed.css';
import 'viewerjs/dist/viewer.min.css';
import { inBrowser, useRoute } from 'vitepress';
import imageViewer from 'vitepress-plugin-image-viewer';
import vImageViewer from 'vitepress-plugin-image-viewer/lib/vImageViewer.vue';
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
        app.component('vImageViewer', vImageViewer);
    },
    Layout() {
        return h(DefaultTheme.Layout, null, {
            'home-hero-before': () => h(OldVersionWarning),
            'doc-before': () => h(OldVersionWarning),
            'not-found': () => h(OldVersionWarning),
        });
    },
    setup() {
        const route = useRoute();
        imageViewer(route);
    },
};
