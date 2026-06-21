<script>
    let { status } = $props();

    const styles = {
        PENDING: 'bg-yellow-100 text-yellow-800 border-yellow-200',
        SUCCESS: 'bg-green-100 text-green-800 border-green-200',
        EXPIRED: 'bg-gray-100 text-gray-800 border-gray-200',
        CANCELLED: 'bg-red-100 text-red-800 border-red-200',
    };

    const labels = {
        PENDING: 'Pending',
        SUCCESS: 'Sukses',
        EXPIRED: 'Kedaluwarsa',
        CANCELLED: 'Dibatalkan',
    };
</script>

<span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {styles[status] || 'bg-gray-100 text-gray-800 border-gray-200'}"
>
    {labels[status] || status}
</span>
