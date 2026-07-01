<script>
    import Layout from '../../components/Layout.svelte';
    import StatusBadge from '../../components/StatusBadge.svelte';
    import { formatCurrency, formatDate } from '../../lib/format.js';

    let { transaction } = $props();

    // ponytail: $derived silences Svelte 5 "initial value only" warnings — props are static but compiler can't prove it
    const detailRows = $derived([
        { label: 'Transaction ID', value: transaction.id, mono: true },
        { label: 'QRIS-ify ID', value: transaction.qrisify_transaction_id, mono: true },
        { label: 'External ID', value: transaction.external_id },
        { label: 'Status', value: null, badge: transaction.status },
        { label: 'Jumlah Diminta', value: formatCurrency(transaction.amount_requested) },
        { label: 'Kode Unik', value: transaction.unique_code },
        { label: 'Total Bayar', value: formatCurrency(transaction.amount_total) },
        { label: 'Payment Provider', value: transaction.payment_provider || '-' },
        { label: 'Webhook Status', value: transaction.webhook_status },
        { label: 'Dibuat', value: formatDate(transaction.created_at) },
        { label: 'Kedaluwarsa', value: formatDate(transaction.expires_at) },
        { label: 'Dibayar Pada', value: transaction.paid_at ? formatDate(transaction.paid_at) : '-' },
        { label: 'Dibatalkan Pada', value: transaction.cancelled_at ? formatDate(transaction.cancelled_at) : '-' },
        { label: 'Diperbarui', value: formatDate(transaction.updated_at) },
    ]);
</script>

<Layout>
    <div class="max-w-3xl">
        <!-- Back button -->
        <a href="/transactions" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke daftar
        </a>

        <!-- Header -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">
                        {formatCurrency(transaction.amount_total || transaction.amount_requested)}
                    </h1>
                    <p class="text-sm text-gray-500 mt-1 font-mono">{transaction.id}</p>
                </div>
                <StatusBadge status={transaction.status} />
            </div>
        </div>

        <!-- Detail -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
            {#each detailRows as row}
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 last:border-0">
                    <span class="text-sm text-gray-500">{row.label}</span>
                    {#if row.badge}
                        <StatusBadge status={row.badge} />
                    {:else if row.mono}
                        <span class="text-sm text-gray-900 font-mono text-xs text-right break-all">{row.value || '-'}</span>
                    {:else}
                        <span class="text-sm text-gray-900 text-right">{row.value || '-'}</span>
                    {/if}
                </div>
            {/each}
        </div>

        <!-- QR String (if available) -->
        {#if transaction.qris_string}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">QRIS String</h3>
                </div>
                <div class="px-5 py-4">
                    <code class="text-xs text-gray-600 break-all font-mono block bg-gray-50 p-3 rounded-lg">
                        {transaction.qris_string}
                    </code>
                </div>
            </div>
        {/if}
    </div>
</Layout>
