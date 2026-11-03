@if (app()->environment('local', 'testing'))
    <div class="mb-5 rounded-lg border border-amber-500/30 bg-amber-500/10 p-4 text-xs text-amber-950 dark:text-amber-200">
        <div class="font-semibold text-sm text-amber-900 dark:text-amber-100">
            Mode Pengembangan: Kredensial Akun
        </div>

        <div class="mt-2 space-y-1 font-mono text-[11px] text-amber-900/90 dark:text-amber-200/90">
            <div><span class="font-semibold">Super Admin:</span> superadmin@warkop.com | password123</div>
            <div><span class="font-semibold">Manager:</span> manager@warkop.com | password123</div>
            <div><span class="font-semibold">Kasir:</span> kasir1@warkop.com | password123</div>
            <div class="text-[10px] text-amber-800/80 dark:text-amber-300/80">PIN Default: 123456</div>
        </div>

        <div class="mt-3 flex flex-wrap gap-1.5">
            <button
                type="button"
                onclick="fillLoginForm('superadmin@warkop.com', 'password123')"
                class="rounded border border-amber-600/40 bg-amber-600/20 px-2 py-1 text-[11px] font-medium text-amber-950 hover:bg-amber-600/30 dark:text-amber-100"
            >
                Isi Super Admin
            </button>
            <button
                type="button"
                onclick="fillLoginForm('manager@warkop.com', 'password123')"
                class="rounded border border-amber-600/40 bg-amber-600/20 px-2 py-1 text-[11px] font-medium text-amber-950 hover:bg-amber-600/30 dark:text-amber-100"
            >
                Isi Manager
            </button>
            <button
                type="button"
                onclick="fillLoginForm('kasir1@warkop.com', 'password123')"
                class="rounded border border-amber-600/40 bg-amber-600/20 px-2 py-1 text-[11px] font-medium text-amber-950 hover:bg-amber-600/30 dark:text-amber-100"
            >
                Isi Kasir
            </button>
        </div>
    </div>

    <script>
        function fillLoginForm(email, password) {
            const emailInput = document.querySelector('input[type="email"], input[name="email"], input[autocomplete="username"]');
            const passInput = document.querySelector('input[type="password"], input[name="password"]');

            if (emailInput) {
                emailInput.value = email;
                emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                emailInput.dispatchEvent(new Event('change', { bubbles: true }));
            }

            if (passInput) {
                passInput.value = password;
                passInput.dispatchEvent(new Event('input', { bubbles: true }));
                passInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    </script>
@endif
