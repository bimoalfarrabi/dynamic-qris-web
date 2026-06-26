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

    function statusColor(ok) {
        return ok ? 'text-green-600' : 'text-red-600';
    }

    function statusBg(ok) {
        return ok ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
    }

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
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">QRIS-ify API</p>
                        <p class="text-xs text-gray-500 font-mono">{base_url}</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {statusBg(qrisify.ok)}">
                    <span class="w-1.5 h-1.5 rounded-full {qrisify.ok ? 'bg-green-500' : 'bg-red-500'}"></span>
                    <span class="{statusColor(qrisify.ok)}">{qrisify.ok ? 'Terhubung' : 'Gagal'}</span>
                </span>
            </div>

            <div class="px-5 py-4 space-y-3">
                <!-- Log rows -->
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">HTTP Status</span>
                    <span class="font-mono font-medium {qrisify.status_code >= 200 && qrisify.status_code < 300 ? 'text-green-600' : 'text-red-600'}">
                        {qrisify.status_code ?? 'timeout / no response'}
                    </span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Response Time</span>
                    <span class="font-mono font-medium text-gray-900">{formatMs(qrisify.response_time_ms)}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Dicek pada</span>
                    <span class="font-mono text-gray-600 text-xs">{new Date(qrisify.checked_at).toLocaleString('id-ID')}</span>
                </div>

                {#if qrisify.error}
                    <div class="mt-2 rounded-lg bg-red-50 border border-red-200 px-4 py-3">
                        <p class="text-xs font-medium text-red-700 mb-1">Error Detail</p>
                        <pre class="text-xs text-red-600 whitespace-pre-wrap break-all font-mono">{qrisify.error}</pre>
                    </div>
                {/if}
            </div>
        </div>

        <!-- Laravel self health -->
        <div class="mt-4 bg-white rounded-xl border border-gray-200 overflow-hidden">
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
