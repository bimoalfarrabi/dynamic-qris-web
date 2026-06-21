<script>
    import { useForm } from '@inertiajs/svelte';
    import Layout from '../components/Layout.svelte';

    let form = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    let showSuccess = $state(false);

    function submit() {
        form.put('/change-password', {
            onSuccess: () => {
                form.reset();
                showSuccess = true;
                setTimeout(() => showSuccess = false, 3000);
            },
        });
    }
</script>

<Layout>
    <div class="max-w-md">
        <h1 class="text-xl font-bold text-gray-900 mb-6">Ubah Password</h1>

        {#if showSuccess}
            <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3 mb-4">
                Password berhasil diubah.
            </div>
        {/if}

        <form onsubmit={(e) => { e.preventDefault(); submit(); }} class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div>
                <label for="current_password" class="text-xs font-medium text-gray-500 mb-1 block">Password Lama</label>
                <input
                    id="current_password"
                    type="password"
                    bind:value={form.current_password}
                    placeholder="••••••••"
                    class="w-full input-field"
                    autocomplete="current-password"
                />
                {#if form.errors.current_password}
                    <p class="text-xs text-red-600 mt-1">{form.errors.current_password}</p>
                {/if}
            </div>

            <div>
                <label for="password" class="text-xs font-medium text-gray-500 mb-1 block">Password Baru</label>
                <input
                    id="password"
                    type="password"
                    bind:value={form.password}
                    placeholder="••••••••"
                    class="w-full input-field"
                    autocomplete="new-password"
                />
                {#if form.errors.password}
                    <p class="text-xs text-red-600 mt-1">{form.errors.password}</p>
                {/if}
            </div>

            <div>
                <label for="password_confirmation" class="text-xs font-medium text-gray-500 mb-1 block">Konfirmasi Password Baru</label>
                <input
                    id="password_confirmation"
                    type="password"
                    bind:value={form.password_confirmation}
                    placeholder="••••••••"
                    class="w-full input-field"
                    autocomplete="new-password"
                />
            </div>

            <button
                type="submit"
                disabled={form.processing}
                class="w-full bg-gray-900 text-white rounded-lg py-2.5 text-sm font-medium hover:bg-gray-800 transition-colors disabled:opacity-50"
            >
                {form.processing ? 'Menyimpan...' : 'Ubah Password'}
            </button>
        </form>
    </div>
</Layout>

<style>
    .input-field {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        outline: none;
        transition: border-color 0.15s;
    }
    .input-field:focus {
        border-color: #6b7280;
    }
</style>
