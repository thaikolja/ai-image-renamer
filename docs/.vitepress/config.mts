import { defineConfig } from 'vitepress'

const isProd = process.env.NODE_ENV==='production'

export default defineConfig({
    title:       'AI Image Renamer',
    description: 'Documentation for the AI Image Renamer WordPress plugin',
    base:        isProd ? '/wp-ai-image-renamer/': '/',
    appearance:  'force-dark',

    head: [
        [ 'link', { rel: 'icon', type: 'image/png', href: '/ai-image-renamer-logo.png' } ],
        [ 'meta', { name: 'og:type', content: 'website' } ],
        [ 'meta', { name: 'og:title', content: 'AI Image Renamer Documentation' } ],
        [
            'meta',
            {
                name: 'og:description',
                content:
                      'Automatically rename uploaded images using AI for SEO-friendly filenames.'
            }
        ]
    ],

    themeConfig: {
        logo:      '/ai-image-renamer-logo.svg',
        siteTitle: 'AI Image Renamer',

        nav: [
            { text: 'Guide', link: '/introduction/' },
            { text: 'Developers', link: '/api/functions' },
            {
                text:  'Links',
                items: [
                    {
                        text: 'WordPress.org',
                        link: 'https://wordpress.org/plugins/ai-image-renamer/'
                    },
                    {
                        text: 'Donate',
                        link: 'https://www.paypal.com/paypalme/thaikolja/10/'
                    },
                    {
                        text: 'GitHub',
                        link: 'https://github.com/thaikolja/wp-ai-image-renamer'
                    },
                    {
                        text: 'GitLab',
                        link: 'https://gitlab.com/thaikolja/wp-ai-image-renamer'
                    }
                ]
            }
        ],

        sidebar: [
            {
                text:  'Introduction',
                items: [
                    { text: 'Overview', link: '/introduction/' },
                    { text: 'Donate', link: '/introduction/donate' },
                    { text: 'Download', link: '/introduction/download' },
                    { text: 'Changelog', link: '/introduction/changelog' }
                ]
            },
            {
                text:  'Usage',
                items: [
                    { text: 'Installation', link: '/usage/installation' },
                    { text: 'Quick Start', link: '/usage/quick-start' },
                    { text: 'Settings', link: '/usage/settings' }
                ]
            },
            {
                text:  'API Reference',
                items: [
                    { text: 'Functions', link: '/api/functions' },
                    { text: 'Filter Hooks', link: '/api/filter-hooks' },
                    { text: 'Action Hooks', link: '/api/action-hooks' },
                    { text: 'Examples', link: '/api/examples' }
                ]
            },
            {
                text:  'Support',
                items: [
                    { text: 'FAQ', link: '/support/faq' },
                    {
                        text: 'Issues',
                        link: 'https://wordpress.org/support/plugin/ai-image-renamer/'
                    },
                    {
                        text: 'Reviews',
                        link: 'https://wordpress.org/support/plugin/ai-image-renamer/reviews/'
                    }
                ]
            }
        ],

        socialLinks: [
            {
                icon: 'github',
                link: 'https://github.com/thaikolja/wp-ai-image-renamer'
            },
            {
                icon: 'gitlab',
                link: 'https://gitlab.com/thaikolja/wp-ai-image-renamer'
            },
            {
                icon: 'paypal',
                link: 'https://www.paypal.com/paypalme/thaikolja/10'
            }
        ],

        editLink: {
            pattern: 'https://gitlab.com/thaikolja/wp-ai-image-renamer/edit/main/docs/:path',
            text:    'Edit this page on GitLab'
        },

        footer: {
            message:   'Released under the GPL-2.0 License.',
            copyright: '© 2025–2026 Kolja Nolte'
        },

        search: {
            provider: 'local'
        }
    }
})
