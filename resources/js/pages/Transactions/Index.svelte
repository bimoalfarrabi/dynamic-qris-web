<script>
    import Layout from '../../components/Layout.svelte';
    import StatusBadge from '../../components/StatusBadge.svelte';
    import { formatCurrency, formatDate } from '../../lib/format.js';
    import { router } from '@inertiajs/svelte';

    let { transactions, filters = {} } = $props();

    let search = $state(filters?.search || '');
    let status = $state(filters?.status || '');
    let dateFrom = $state(filters?.date_from || '');
    let dateTo = $state(filters?.date_to || '');

    let searchTimeout;

    function applyFilters() {
        const params = {};
        if (search) params.search = search;
        if (status) params.status = status;
        if (dateFrom) params.date_from = dateFrom;
        if (dateTo) params.date_to = dateTo;

        router.get('/transactions', params, {
            preserveState: true,
            preserveScroll: true,
        });
    }

    function onSearchInput() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 300);
    }

    function resetFilters() {
        search = '';
        status = '';
        dateFrom = '';
        dateTo = '';
        router.get('/transactions', {}, { preserveScroll: true });
    }

    const statusOptions = [
        { value: '', label: 'Semua Status' },
        { value: 'PENDING', label: 'Pending' },
        { value: 'SUCCESS', label: 'Sukses' },
        { value: 'EXPIRED', label: 'Kedaluwarsa' },
        { value: 'CANCELLED', label: 'Dibatalkan' },
    ];
</script>

<Layout>
    <!-- Filters -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="text-xs font-medium text-gray-500 mb-1 block">Cari</label>
                <div class="relative">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        bind:value={search}
                        oninput={onSearchInput}
                        placeholder="ID, External ID..."
                        class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="text-xs font-medium text-gray-500 mb-1 block">Status</label>
                <select
                    bind:value={status}
                    onchange={applyFilters}
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                >
                    {#each statusOptions as opt}
                        <option value={opt.value}>{opt.label}</option>
                    {/each}
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="text-xs font-medium text-gray-500 mb-1 block">Dari Tanggal</label>
                <input
                    type="date"
                    bind:value={dateFrom}
                    onchange={applyFilters}
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <!-- Date To -->
            <div>
                <label class="text-xs font-medium text-gray-500 mb-1 block">Sampai Tanggal</label>
                <input
                    type="date"
                    bind:value={dateTo}
                    onchange={applyFilters}
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>
        </div>

        {#if filters.search || filters.status || filters.date_from || filters.date_to}
            <button
                onclick={resetFilters}
                class="mt-3 text-xs text-gray-500 hover:text-gray-700 font-medium"
            >
                ✕ Reset filter
            </button>
        {/if}
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {#if !transactions?.data || transactions.data.length === 0}
            <div class="px-5 py-16 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2M9 7h6m2 14H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v12a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm text-gray-500">Tidak ada transaksi ditemukan</p>
            </div>
        {:else}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="px-5 py-3 text-left font-medium text-gray-500">ID</th>
                            <th class="px-5 py-3 text-left font-medium text-gray-500">External ID</th>
                            <th class="px-5 py-3 text-left font-medium text-gray-500">Jumlah</th>
                            <th class="px-5 py-3 text-left font-medium text-gray-500">Status</th>
                            <th class="px-5 py-3 text-left font-medium text-gray-500">Webhook</th>
                            <th class="px-5 py-3 text-left font-medium text-gray-500">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        {#each transactions.data as tx}
                            <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick={() => window.location.href = `/transactions/${tx.id}`}>
                                <td class="px-5 py-3 font-mono text-xs text-blue-600 hover:underline">
                                    {tx.id.slice(0, 8)}...
                                </td>
                                <td class="px-5 py-3 text-gray-600">{tx.external_id || '-'}</td>
                                <td class="px-5 py-3 font-medium text-gray-900">
                                    {formatCurrency(tx.amount_total || tx.amount_requested)}
                                </td>
                                <td class="px-5 py-3"><StatusBadge status={tx.status} /></td>
                                <td class="px-5 py-3">
                                    {#if tx.webhook_status === 'SENT_SUCCESS'}
                                        <span class="text-xs text-green-600">✓ Terkirim</span>
                                    {:else if tx.webhook_status === 'FAILED'}
                                        <span class="text-xs text-red-600">✗ Gagal</span>
                                    {:else}
                                        <span class="text-xs text-gray-400">—</span>
                                    {/if}
                                </td>
                                <td class="px-5 py-3 text-gray-500 text-xs">{formatDate(tx.created_at)}</td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-5 py-3 border-t border-gray-200 flex items-center justify-between">
                <p class="text-xs text-gray-500">
                    Menampilkan {transactions.from ?? 0}–{transactions.to ?? 0} dari {transactions.total ?? 0}
                </p>
                <div class="flex items-center gap-1">
                    {#if transactions.prev_page_url}
                        <a href={transactions.prev_page_url} class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50" preserve-scroll>← Sebelumnya</a>
                    {/if}
                    <span class="px-3 py-1.5 text-xs text-gray-600">Hal. {transactions.current_page}/{transactions.last_page}</span>
                    {#if transactions.next_page_url}
                        <a href={transactions.next_page_url} class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50" preserve-scroll>Selanjutnya →</a>
                    {/if}
                </div>
            </div>
        {/if}
    </div>
</Layout>
