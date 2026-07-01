<script>
    import Layout from '../components/Layout.svelte';
    import { router } from '@inertiajs/svelte';

    let { qrisify, base_url } = $props();

    let isChecking = $state(false);

    function refresh() {
        isChecking = true;
        router.reload({
            onFinish: () => { isChecking = false; },
        });
    }

    // ponytail: two parallel functions → one object lookup
    const statusStyle = (ok) => ok
        ? { color: 'text-green-600', bg: 'bg-green-50 border-green-200' }
        : { color: 'text-red-600', bg: 'bg-red-50 border-red-200' };

    function formatMs(ms) {
        if (ms === null || ms === undefined) return '-';
        return `${ms}ms`;
    }
</script>

<Layout>
    <div class="max-w-2xl">
        <!-- Header row -->
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-base font-semibold text-gray-900">Status Koneksi</h3>
            <button
                onclick={refresh}
                disabled={isChecking}
                class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 transition-colors"
            >
                <svg class="w-4 h-4 {isChecking ? 'animate-spin' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {isChecking ? 'Memeriksa…' : 'Cek Ulang'}
            </button>
        </div>

        <!-- QRIS-ify Card -->
        {@const s = statusStyle(qrisify?.ok)}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">QRIS-ify API</p>
                        <p class="text-xs text-gray-500">{base_url}</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {s.bg}">
                    <span class="w-1.5 h-1.5 rounded-full {qrisify?.ok ? 'bg-green-500' : 'bg-red-500'}"></span>
                    <span class="{s.color}">{qrisify?.ok ? 'Online' : 'Offline'}</span>
                </span>
            </div>
            <div class="px-5 py-4 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Status Code</p>
                    <p class="font-medium text-gray-900">{qrisify?.status_code ?? '-'}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Response Time</p>
                    <p class="font-medium text-gray-900">{formatMs(qrisify?.response_time_ms)}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Terakhir Dicek</p>
                    <p class="font-medium text-gray-900">{qrisify?.checked_at ?? '-'}</p>
                </div>
                {#if qrisify?.error}
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Error</p>
                        <p class="font-medium text-red-600 text-xs">{qrisify.error}</p>
                    </div>
                {/if}
            </div>
        </div>

        <!-- Laravel Backend Card (always online if page loads) -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Laravel Backend</p>
                        <p class="text-xs text-gray-500">Server ini</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border bg-green-50 border-green-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                    <span class="text-green-600">Online</span>
                </span>
            </div>
        </div>
    </div>
</Layout>
