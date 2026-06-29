<script>
    import { usePage, useForm } from '@inertiajs/svelte';

    let { children } = $props();

    const page = usePage();

    const navItems = [
        { href: '/', label: 'Dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
        { href: '/transactions', label: 'Transaksi', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
        { href: '/status', label: 'Status', icon: 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0' },
        { href: '/account', label: 'Akun', icon: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z' },
    ];

    function isActive(href) {
        const url = page.url;
        if (href === '/') return url === '/';
        return url.startsWith(href);
    }

    const logoutForm = useForm({});

    function logout() {
        logoutForm.post('/logout');
    }
</script>

<div class="min-h-screen bg-gray-50">
    <!-- Sidebar — desktop only -->
    <aside class="hidden md:flex fixed inset-y-0 left-0 w-64 bg-gray-900 text-white flex-col">
        <div class="px-6 py-5 border-b border-gray-800">
            <h1 class="text-lg font-bold tracking-tight">Dynamic QRIS</h1>
            <p class="text-xs text-gray-400 mt-0.5">Personal Dashboard</p>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1">
            {#each navItems as item}
                <a
                    href={item.href}
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {isActive(item.href) ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'}"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d={item.icon} />
                    </svg>
                    {item.label}
                </a>
            {/each}
        </nav>

        <div class="px-6 py-4 border-t border-gray-800">
            <button
                onclick={logout}
                class="flex items-center gap-3 text-sm text-gray-400 hover:text-white transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
            </button>
        </div>
    </aside>

    <!-- Main content -->
    <main class="md:ml-64 min-h-screen">
        <header class="bg-white border-b border-gray-200 px-4 md:px-8 py-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">
                    {page.component.split('/').pop()}
                </h2>
                <div class="text-sm text-gray-500 hidden sm:block">
                    {new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}
                </div>
            </div>
        </header>

        <!-- ponytail: pb-16 makes room for bottom nav on mobile -->
        <div class="px-4 md:px-8 py-6 pb-20 md:pb-6">
            {@render children()}
        </div>
    </main>

    <!-- Bottom nav — mobile only -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 bg-gray-900 border-t border-gray-800 flex z-50">
        {#each navItems as item}
            <a
                href={item.href}
                class="flex-1 flex flex-col items-center gap-1 py-2.5 text-xs font-medium transition-colors {isActive(item.href) ? 'text-white' : 'text-gray-400'}"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d={item.icon} />
                </svg>
                {item.label}
            </a>
        {/each}
        <button
            onclick={logout}
            class="flex-1 flex flex-col items-center gap-1 py-2.5 text-xs font-medium text-gray-400 transition-colors"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Keluar
        </button>
    </nav>
</div>
