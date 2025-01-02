import { withMermaid } from 'vitepress-plugin-mermaid';

const BASE_PATH = '/';

// https://vitepress.dev/reference/site-config
export default withMermaid({
    title: 'Beacon',
    description: 'Feature Flag Management for Laravel',
    base: BASE_PATH,
    head: [
        ['meta', { name: 'author', content: 'Davey Shafik' }],
        ['meta', { name: 'twitter:image', content: BASE_PATH + 'assets/images/social.png' }],
        ['meta', { name: 'og:image', content: BASE_PATH + 'assets/images/social.png' }],
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
        // search: {
        //   provider: 'local',
        // },
        // https://vitepress.dev/reference/default-theme-config

        footer: {
            message: 'Made with 🦁💖🏳️‍🌈 by <a href="https://www.daveyshafik.com">Davey Shafik</a>.',
            copyright: 'Released under the MIT License. Copyright © 2025 Davey Shafik.',
        },

        socialLinks: [
            { text: 'GitHub', icon: 'github', link: 'https://github.com/beacon-hq' },
            { text: 'Twitch', icon: 'twitch', link: 'https://twitch.tv/daveyshafik' },
            { text: 'YouTube', icon: 'youtube', link: 'https://youtube.com/@dshafik' },
        ],
    },
    markdown: {
        theme: {
            dark: 'monokai',
            light: 'github-light',
        },
    },
});
