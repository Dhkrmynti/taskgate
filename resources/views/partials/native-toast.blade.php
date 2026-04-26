<script>
    window.showToast = function(message, type = 'success') {
        const container = document.getElementById('toast-container') || (function() {
            const c = document.createElement('div');
            c.id = 'toast-container';
            c.className = 'fixed right-4 top-4 z-[100] space-y-3';
            document.body.appendChild(c);
            return c;
        })();

        const toastId = 'toast-' + Date.now();
        const isError = type === 'error';
        const icon = isError ? 
            `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86l-8.11 14A1 1 0 0 0 3.05 19h17.9a1 1 0 0 0 .87-1.5l-8.11-14a1 1 0 0 0-1.74 0Z"/></svg>` :
            `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>`;
        
        const html = `
            <div id="${toastId}" class="toast-enter w-[min(92vw,420px)]" style="transition: all 0.3s ease-out; opacity: 0; transform: translateY(-20px);">
                <div class="overflow-hidden rounded-xl border ${isError ? 'border-[#f2c8cc] dark:border-[#5c2d34]' : 'border-[#d6dfd0] dark:border-[#38513d]'} bg-[var(--panel-strong)] p-3 shadow-soft">
                    <div class="flex items-start gap-4 px-5 py-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ${isError ? 'bg-[#fff4f4] text-[#c65b68] dark:bg-[#2b171b] dark:text-[#f3c7cd]' : 'bg-[#eef8e9] text-[#4f8a42] dark:bg-[#15201a] dark:text-[#bfd7c2]'}">
                            ${icon}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-[var(--panel-text)]">${isError ? 'Gagal' : 'Berhasil'}</p>
                            <p class="mt-1 text-sm leading-6 text-[var(--panel-muted)]">${message}</p>
                        </div>
                        <button onclick="document.getElementById('${toastId}').remove()" type="button" class="rounded-full p-2 text-[var(--panel-muted)] transition hover:bg-[#f4f5f9] hover:text-[var(--panel-text)]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="h-1.5 w-full ${isError ? 'bg-[#fff1f1] dark:bg-[#2b171b]' : 'bg-[#eef8e9] dark:bg-[#1c2a22]'}">
                        <div id="${toastId}-bar" class="h-full w-full origin-left ${isError ? 'bg-[#d97782]' : 'bg-[#70c26a] dark:bg-[#5fa85a]'}"></div>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('afterbegin', html);
        const toast = document.getElementById(toastId);
        const bar = document.getElementById(toastId + '-bar');

        // Animate in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });

        if (bar) {
            bar.animate([{transform: 'scaleX(1)'}, {transform: 'scaleX(0)'}], {duration: 4000, easing: 'linear', fill: 'forwards'});
        }

        setTimeout(() => {
            if (toast) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(20px)';
                setTimeout(() => toast.remove(), 300);
            }
        }, 4000);
    };
</script>
