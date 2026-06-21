<script>
    import Layout from '../components/Layout.svelte';
    import StatusBadge from '../components/StatusBadge.svelte';
    import { formatCurrency, formatDate } from '../lib/format.js';

    let { stats, recentTransactions = [] } = $props();
</script>

<Layout>
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Today Count -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">Transaksi Hari Ini</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{stats.today.count}</p>
            <p class="text-xs text-gray-500 mt-1">{stats.today.success} sukses</p>
        </div>

        <!-- Today Revenue -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">Pendapatan Hari Ini</span>
                <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{formatCurrency(stats.today.revenue)}</p>
            <p class="text-xs text-gray-500 mt-1">dari {stats.today.success} transaksi</p>
        </div>

        <!-- This Week -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">Minggu Ini</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{stats.this_week.count}</p>
            <p class="text-xs text-gray-500 mt-1">{formatCurrency(stats.this_week.revenue)}</p>
        </div>

        <!-- Success Rate -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">Success Rate</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{stats.success_rate}%</p>
            <p class="text-xs text-gray-500 mt-1">{stats.pending} pending</p>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Transaksi Terbaru</h3>
            <a href="/transactions" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                Lihat semua →
            </a>
        </div>

        {#if recentTransactions.length === 0}
            <div class="px-5 py-12 text-center">
                <p class="text-sm text-gray-500">Belum ada transaksi</p>
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
                            <th class="px-5 py-3 text-left font-medium text-gray-500">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        {#each recentTransactions as tx}
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3 text-gray-600 font-mono text-xs">
                                    <a href="/transactions/{tx.id}" class="text-blue-600 hover:underline">
                                        {tx.id.slice(0, 8)}...
                                    </a>
                                </td>
                                <td class="px-5 py-3 text-gray-600">{tx.external_id || '-'}</td>
                                <td class="px-5 py-3 font-medium text-gray-900">{formatCurrency(tx.amount_total || tx.amount_requested)}</td>
                                <td class="px-5 py-3"><StatusBadge status={tx.status} /></td>
                                <td class="px-5 py-3 text-gray-500 text-xs">{formatDate(tx.created_at)}</td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        {/if}
    </div>
</Layout>
