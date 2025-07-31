import tailwindcss from '@tailwindcss/vite';
// @ts-ignore
import markdownItFigureCaption from 'markdown-it-figure-caption';
import deadFile from 'vite-plugin-deadfile';
import { withMermaid } from 'vitepress-plugin-mermaid';

const BASE_PATH = '/docs/';

// https://vitepress.dev/reference/site-config
export default withMermaid({
    title: 'Beacon',
    description: 'Feature Flag Management for Laravel',
    base: BASE_PATH,
    outDir: '../public/docs',
    head: [
        ['meta', { name: 'author', content: 'Beacon HQ' }],
        ['meta', { name: 'og:type', content: 'website' }],
        ['meta', { name: 'og:title', content: 'Beacon — Feature Flag Management for Laravel' }],
        [
            'meta',
            {
                name: 'og:description',
                content:
                    'Manage your applications Feature Flags with Beacon, a centralized management platform for Laravel and Laravel Pennant with Gradual Rollout and A/B Testing',
            },
        ],
        ['meta', { name: 'og:image', content: '/images/social.png' }],

        ['meta', { name: 'twitter:card', content: 'summary_large_image' }],
        ['meta', { name: 'twitter:title', content: 'Beacon — Feature Flag Management for Laravel' }],
        [
            'meta',
            {
                name: 'twitter:description',
                content:
                    'Manage your applications Feature Flags with Beacon, a centralized management platform for Laravel and Laravel Pennant with Gradual Rollout and A/B Testing',
            },
        ],
        ['meta', { name: 'twitter:image', content: '/images/social.png' }],

        ['link', { rel: 'preconnect', href: 'https://fonts.googleapis.com' }],
        ['link', { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' }],
        ['script', { src: 'https://kit.fontawesome.com/a26c7e9148.js', crossorigin: 'anonymous' }],
        [
            'link',
            {
                href: 'https://fonts.googleapis.com/css2?family=Source+Code+Pro:ital,wght@0,200..900;1,200..900&family=Source+Sans+3:ital,wght@0,200..900;1,200..900&display=swap',
                rel: 'stylesheet',
            },
        ],
    ],
    themeConfig: {
        search: {
            provider: 'local',
        },

        logo: 'https://beacon-hq.dev/images/icon.svg',

        outline: {
            level: [2, 3],
            label: 'On this page',
        },

        sidebar: [
            {
                text: 'Introduction',
                items: [
                    { text: 'Overview', link: '/introduction/overview' },
                    { text: 'Installation', link: '/introduction/install' },
                    { text: 'Get Started', link: '/introduction/get-started' },
                    { text: 'Configuration', link: '/introduction/configuration' },
                ],
            },
            {
                text: 'Using Beacon',
                items: [
                    {
                        text: 'Basics',
                        collapsed: true,
                        link: '/app/basics/login',
                        items: [
                            { text: 'Logging In', link: '/app/basics/login' },
                            { text: 'Creating an Account', link: '/app/basics/registration' },
                            { text: 'Changing Teams', link: '/app/basics/teams' },
                        ],
                    },
                    { text: 'Dashboard', link: '/app/dashboard' },
                    { text: 'Applications', link: '/app/applications' },
                    { text: 'Environments', link: '/app/environments' },
                    {
                        text: 'Feature Flags',
                        collapsed: true,
                        link: '/app/feature-flags/basics',
                        items: [
                            { text: 'Basics', link: '/app/feature-flags/basics' },
                            { text: 'Rollouts', link: '/app/feature-flags/rollouts' },
                            { text: 'Experiments', link: '/app/feature-flags/experiments' },
                        ],
                    },
                    { text: 'Policies', link: '/app/policies' },
                    {
                        text: 'Feature Types',
                        link: '/app/feature-types',
                    },
                    { text: 'Tags', link: '/app/tags' },
                    {
                        text: 'Settings',
                        collapsed: true,
                        link: '/app/settings',
                        items: [
                            { text: 'Organizations', link: '/app/settings/organizations' },
                            { text: 'Teams', link: '/app/settings/teams' },
                            { text: 'Users', link: '/app/settings/users' },
                            { text: 'Access Tokens', link: '/app/settings/access-tokens' },
                        ],
                    },
                ],
            },
            {
                text: 'Core Concepts',
                items: [
                    { text: 'Using Feature Flags', link: '/core/using-feature-flags' },
                    { text: 'Applications & Environments', link: '/core/applications-and-environments' },
                    { text: 'Context', link: '/core/context' },
                    { text: 'Policies', link: '/core/policies' },
                ],
            },
            {
                text: 'Examples',
                items: [{ text: 'Usage Examples', link: '/examples' }],
            },
            {
                text: 'Help',
                items: [{ text: 'Troubleshooting', link: '/troubleshooting' }],
            },
        ],

        footer: {
            message: 'Made with 🦁💖🏳️‍🌈 by <a href="https://www.daveyshafik.com">Davey Shafik</a>.',
            copyright: `Released under the <a href="https://github.com/beacon-hq/app/blob/main/LICENSE.md">FCL-1.0-MIT</a> License. Copyright © 2024-${new Date().getFullYear()} Davey Shafik.`,
        },

        socialLinks: [
            { text: 'GitHub', icon: 'github', link: 'https://github.com/beacon-hq' },
            { text: 'YouTube', icon: 'youtube', link: 'https://youtube.com/@beacon-hq' },
        ],

        plugins: [
            tailwindcss(),
            deadFile({
                root: '../docs',
                include: ['**/*.md'],
            }),
        ],
    },
    markdown: {
        theme: {
            dark: 'monokai',
            light: 'github-light',
        },
        config: (md) => {
            md.use(markdownItFigureCaption);
        },
    },
    vue: {
        template: {
            compilerOptions: {
                isCustomElement: (tag) => tag === 'lite-youtube',
            },
        },
    },
    vite: {
        plugins: [
            // @ts-ignore
            deadFile({
                root: __dirname,
                include: ['**/*.md'],
                exclude: ['**/dist/**', 'config.mts'],
            }),
        ],
    },
});
