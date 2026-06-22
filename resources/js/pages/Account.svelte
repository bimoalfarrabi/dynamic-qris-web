<script>
    import { useForm, usePage } from '@inertiajs/svelte';
    import Layout from '../components/Layout.svelte';

    const page = usePage();

    // Email form
    let emailForm = useForm({
        email: page.props.auth.user.email,
        current_password: '',
    });

    let emailSuccess = $state(false);

    function submitEmail() {
        emailForm.put('/account/email', {
            onSuccess: () => {
                emailForm.reset('current_password');
                emailSuccess = true;
                setTimeout(() => emailSuccess = false, 3000);
            },
        });
    }

    // Password form
    let passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    let passwordSuccess = $state(false);

    function submitPassword() {
        passwordForm.put('/account/password', {
            onSuccess: () => {
                passwordForm.reset();
                passwordSuccess = true;
                setTimeout(() => passwordSuccess = false, 3000);
            },
        });
    }
</script>

<Layout>
    <div class="max-w-md space-y-8">
        <h1 class="text-xl font-bold text-gray-900">Akun</h1>

        <!-- Email Section -->
        <section>
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Ubah Email</h2>

            {#if emailSuccess}
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3 mb-4">
                    Email berhasil diubah.
                </div>
            {/if}

            <form onsubmit={(e) => { e.preventDefault(); submitEmail(); }} class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <div>
                    <label for="email" class="text-xs font-medium text-gray-500 mb-1 block">Email Baru</label>
                    <input
                        id="email"
                        type="email"
                        bind:value={emailForm.email}
                        placeholder="email@example.com"
                        class="w-full input-field"
                        autocomplete="email"
                    />
                    {#if emailForm.errors.email}
                        <p class="text-xs text-red-600 mt-1">{emailForm.errors.email}</p>
                    {/if}
                </div>

                <div>
                    <label for="email_password" class="text-xs font-medium text-gray-500 mb-1 block">Password (konfirmasi)</label>
                    <input
                        id="email_password"
                        type="password"
                        bind:value={emailForm.current_password}
                        placeholder="••••••••"
                        class="w-full input-field"
                        autocomplete="current-password"
                    />
                    {#if emailForm.errors.current_password}
                        <p class="text-xs text-red-600 mt-1">{emailForm.errors.current_password}</p>
                    {/if}
                </div>

                <button
                    type="submit"
                    disabled={emailForm.processing}
                    class="w-full bg-gray-900 text-white rounded-lg py-2.5 text-sm font-medium hover:bg-gray-800 transition-colors disabled:opacity-50"
                >
                    {emailForm.processing ? 'Menyimpan...' : 'Ubah Email'}
                </button>
            </form>
        </section>

        <!-- Password Section -->
        <section>
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Ubah Password</h2>

            {#if passwordSuccess}
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3 mb-4">
                    Password berhasil diubah.
                </div>
            {/if}

            <form onsubmit={(e) => { e.preventDefault(); submitPassword(); }} class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <div>
                    <label for="current_password" class="text-xs font-medium text-gray-500 mb-1 block">Password Lama</label>
                    <input
                        id="current_password"
                        type="password"
                        bind:value={passwordForm.current_password}
                        placeholder="••••••••"
                        class="w-full input-field"
                        autocomplete="current-password"
                    />
                    {#if passwordForm.errors.current_password}
                        <p class="text-xs text-red-600 mt-1">{passwordForm.errors.current_password}</p>
                    {/if}
                </div>

                <div>
                    <label for="password" class="text-xs font-medium text-gray-500 mb-1 block">Password Baru</label>
                    <input
                        id="password"
                        type="password"
                        bind:value={passwordForm.password}
                        placeholder="••••••••"
                        class="w-full input-field"
                        autocomplete="new-password"
                    />
                    {#if passwordForm.errors.password}
                        <p class="text-xs text-red-600 mt-1">{passwordForm.errors.password}</p>
                    {/if}
                </div>

                <div>
                    <label for="password_confirmation" class="text-xs font-medium text-gray-500 mb-1 block">Konfirmasi Password Baru</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        bind:value={passwordForm.password_confirmation}
                        placeholder="••••••••"
                        class="w-full input-field"
                        autocomplete="new-password"
                    />
                </div>

                <button
                    type="submit"
                    disabled={passwordForm.processing}
                    class="w-full bg-gray-900 text-white rounded-lg py-2.5 text-sm font-medium hover:bg-gray-800 transition-colors disabled:opacity-50"
                >
                    {passwordForm.processing ? 'Menyimpan...' : 'Ubah Password'}
                </button>
            </form>
        </section>
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
