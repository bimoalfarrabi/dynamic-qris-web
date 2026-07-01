<script>
    import { useForm, usePage } from '@inertiajs/svelte';
    import Layout from '../components/Layout.svelte';

    const page = usePage();

    // ponytail: one helper kills two identical success-flash patterns
    function useFlash() {
        let on = $state(false);
        return {
            get on() { return on; },
            trigger() { on = true; setTimeout(() => on = false, 3000); },
        };
    }

    const emailFlash = useFlash();
    const passwordFlash = useFlash();

    let emailForm = useForm({
        email: page.props.auth.user.email,
        current_password: '',
    });

    function submitEmail() {
        emailForm.put('/account/email', {
            onSuccess: () => {
                emailForm.reset('current_password');
                emailFlash.trigger();
            },
        });
    }

    let passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    function submitPassword() {
        passwordForm.put('/account/password', {
            onSuccess: () => {
                passwordForm.reset();
                passwordFlash.trigger();
            },
        });
    }

    // ponytail: shared input classes inline — no scoped CSS needed
    const inputCls = 'w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm outline-none transition-colors focus:border-gray-400 focus:ring-2 focus:ring-gray-400/10';
    const btnCls = 'w-full bg-gray-900 text-white rounded-lg px-4 py-2.5 text-sm font-medium cursor-pointer hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors';
</script>

<Layout>
    <div class="max-w-lg space-y-10">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Akun</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola email dan password akun kamu.</p>
        </div>

        <!-- Email Section -->
        <section>
            <div class="mb-4">
                <h2 class="text-base font-semibold text-gray-800">Ubah Email</h2>
                <p class="text-xs text-gray-500 mt-0.5">Email digunakan untuk login ke dashboard.</p>
            </div>

            {#if emailFlash.on}
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3 mb-4">
                    Email berhasil diubah.
                </div>
            {/if}

            <form onsubmit={(e) => { e.preventDefault(); submitEmail(); }} class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <div>
                    <label for="email" class="text-xs font-medium text-gray-500 mb-1 block">Email</label>
                    <input
                        id="email"
                        type="email"
                        bind:value={emailForm.email}
                        placeholder="email@example.com"
                        class={inputCls}
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
                        class={inputCls}
                        autocomplete="current-password"
                    />
                    {#if emailForm.errors.current_password}
                        <p class="text-xs text-red-600 mt-1">{emailForm.errors.current_password}</p>
                    {/if}
                </div>

                <div class="pt-2">
                    <button type="submit" disabled={emailForm.processing} class={btnCls}>
                        {emailForm.processing ? 'Menyimpan...' : 'Simpan Email'}
                    </button>
                </div>
            </form>
        </section>

        <hr class="border-gray-100" />

        <!-- Password Section -->
        <section>
            <div class="mb-4">
                <h2 class="text-base font-semibold text-gray-800">Ubah Password</h2>
                <p class="text-xs text-gray-500 mt-0.5">Minimal 8 karakter.</p>
            </div>

            {#if passwordFlash.on}
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
                        class={inputCls}
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
                        class={inputCls}
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
                        class={inputCls}
                        autocomplete="new-password"
                    />
                </div>

                <div class="pt-2">
                    <button type="submit" disabled={passwordForm.processing} class={btnCls}>
                        {passwordForm.processing ? 'Menyimpan...' : 'Simpan Password'}
                    </button>
                </div>
            </form>
        </section>
    </div>
</Layout>
