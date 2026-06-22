<script>
    import { useForm } from '@inertiajs/svelte';

    let form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    function submit() {
        form.post('/login', {
            onSuccess: () => {
                form.reset('password');
            },
        });
    }
</script>

<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gray-900 text-white mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Dynamic QRIS</h1>
            <p class="text-sm text-gray-500 mt-1">Login ke dashboard</p>
        </div>

        <!-- Form -->
        <form onsubmit={(e) => { e.preventDefault(); submit(); }} class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            {#if form.errors.email}
                <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                    {form.errors.email}
                </div>
            {/if}

            <div>
                <label for="email" class="text-xs font-medium text-gray-500 mb-1 block">Email</label>
                <input
                    id="email"
                    type="email"
                    bind:value={form.email}
                    placeholder="admin@qris.local"
                    class="w-full"
                    autocomplete="email"
                />
                {#if form.errors.email}
                    <p class="text-xs text-red-600 mt-1">{form.errors.email}</p>
                {/if}
            </div>

            <div>
                <label for="password" class="text-xs font-medium text-gray-500 mb-1 block">Password</label>
                <input
                    id="password"
                    type="password"
                    bind:value={form.password}
                    placeholder="••••••••"
                    class="w-full"
                    autocomplete="current-password"
                />
                {#if form.errors.password}
                    <p class="text-xs text-red-600 mt-1">{form.errors.password}</p>
                {/if}
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input type="checkbox" bind:checked={form.remember} class="rounded border-gray-300" />
                Ingat saya
            </label>

            <button
                type="submit"
                disabled={form.processing}
                class="w-full bg-gray-900 text-white rounded-lg py-2.5 text-sm font-medium hover:bg-gray-800 transition-colors disabled:opacity-50"
            >
                {form.processing ? 'Memproses...' : 'Login'}
            </button>
        </form>
    </div>
</div>

<style>
    input[type="email"], input[type="password"] {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        outline: none;
        transition: border-color 0.15s;
    }
    input[type="email"]:focus, input[type="password"]:focus {
        border-color: #6b7280;
    }
    input[type="checkbox"] {
        width: 1rem;
        height: 1rem;
    }
</style>
