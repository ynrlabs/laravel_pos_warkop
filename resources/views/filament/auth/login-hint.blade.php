@if (app()->environment('local', 'testing'))
    <div id="sandbox-warning-box" class="hidden mb-4 rounded-lg border border-red-500/40 bg-red-500/10 p-4 text-xs text-red-900 dark:text-red-200">
        <div class="flex items-center gap-2 font-semibold text-sm text-red-800 dark:text-red-100">
            <span>Perhatian: Frame Sandboxed Terdeteksi</span>
        </div>
        <p class="mt-1.5 leading-relaxed text-[12px]">
            Halaman ini dibuka di dalam embedded preview IDE yang memblokir pengiriman form (<code>allow-forms</code>) dan local storage.
            Form login tidak dapat diproses di dalam preview ini.
        </p>
        <div class="mt-2.5">
            <a
                href="{{ request()->fullUrl() }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 rounded bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 transition"
            >
                Buka di Tab Browser Eksternal
            </a>
        </div>
    </div>

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
                class="rounded border border-amber-600/40 bg-amber-600/20 px-2.5 py-1 text-[11px] font-medium text-amber-950 hover:bg-amber-600/30 transition dark:text-amber-100"
            >
                Isi Super Admin
            </button>
            <button
                type="button"
                onclick="fillLoginForm('manager@warkop.com', 'password123')"
                class="rounded border border-amber-600/40 bg-amber-600/20 px-2.5 py-1 text-[11px] font-medium text-amber-950 hover:bg-amber-600/30 transition dark:text-amber-100"
            >
                Isi Manager
            </button>
            <button
                type="button"
                onclick="fillLoginForm('kasir1@warkop.com', 'password123')"
                class="rounded border border-amber-600/40 bg-amber-600/20 px-2.5 py-1 text-[11px] font-medium text-amber-950 hover:bg-amber-600/30 transition dark:text-amber-100"
            >
                Isi Kasir
            </button>
        </div>
    </div>

    <script>
        (function detectSandboxEnvironment() {
            let isRestricted = false;
            try {
                if (window.self !== window.top) {
                    isRestricted = true;
                }
                const testKey = '__test_storage__';
                window.localStorage.setItem(testKey, '1');
                window.localStorage.removeItem(testKey);
            } catch (err) {
                isRestricted = true;
            }

            if (isRestricted) {
                const warningBox = document.getElementById('sandbox-warning-box');
                if (warningBox) {
                    warningBox.classList.remove('hidden');
                }
            }
        })();

        function fillLoginForm(email, password) {
            const emailInput = document.getElementById('form.email') 
                || document.getElementById('data.email')
                || document.querySelector('input[type="email"]')
                || document.querySelector('input[name="data.email"]')
                || document.querySelector('input[name="email"]');

            const passInput = document.getElementById('form.password')
                || document.getElementById('data.password')
                || document.querySelector('input[type="password"]')
                || document.querySelector('input[name="data.password"]')
                || document.querySelector('input[name="password"]');

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

            try {
                const formEl = (emailInput || passInput)?.closest('form');
                const livewireRoot = formEl?.closest('[wire\\:id]');
                if (window.Livewire && livewireRoot) {
                    const componentId = livewireRoot.getAttribute('wire:id');
                    const comp = window.Livewire.find(componentId);
                    if (comp) {
                        comp.set('data.email', email);
                        comp.set('data.password', password);
                    }
                }
            } catch (ignore) {}
        }
    </script>
@endif
