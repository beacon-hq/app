import '../css/app.css';
import './bootstrap';
import { SidebarProvider } from '@/Components/ui/sidebar';
import { ThemeProvider } from '@/theme-provider';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME ?? 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx', { eager: false })),
    setup({ el, App, props }) {
        createRoot(el).render(
            <ThemeProvider defaultTheme="system">
                <SidebarProvider defaultOpen={true}>
                    <App {...props} />
                </SidebarProvider>
            </ThemeProvider>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});
