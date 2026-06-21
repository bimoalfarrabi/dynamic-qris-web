import './bootstrap';
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/svelte';
import { mount } from 'svelte';

const pages = import.meta.glob('./pages/**/*.svelte');

createInertiaApp({
    resolve: (name) => {
        return pages[`./pages/${name}.svelte`]();
    },
    setup({ el, App, props }) {
        mount(App, { target: el, props });
    },
});
