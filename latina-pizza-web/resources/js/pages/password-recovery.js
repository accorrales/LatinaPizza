export function initPasswordRecovery() {
    document.querySelectorAll('[data-recovery-form]').forEach((form) => {
        const button = form.querySelector('button[type="submit"]');
        const originalText = button.textContent;
        form.addEventListener('submit', () => {
            button.disabled = true;
            button.textContent = 'Procesando…';
            form.setAttribute('aria-busy', 'true');
        });
        window.addEventListener('pageshow', () => {
            button.disabled = false;
            button.textContent = originalText;
            form.removeAttribute('aria-busy');
        });
    });

    const resend = document.querySelector('[data-resend-at]');
    if (resend) {
        const update = () => {
            const seconds = Math.max(0, Math.ceil(Number(resend.dataset.resendAt) - Date.now() / 1000));
            resend.disabled = seconds > 0;
            resend.textContent = seconds > 0 ? `Podés reenviar en ${seconds} s` : 'Reenviar código';
            return seconds;
        };
        if (update() > 0) {
            const timer = window.setInterval(() => {
                if (update() === 0) window.clearInterval(timer);
            }, 1000);
        }
    }

    document.querySelector('[data-toggle-password]')?.addEventListener('click', (event) => {
        const button = event.currentTarget;
        const visible = button.getAttribute('aria-pressed') !== 'true';
        document.querySelectorAll('[autocomplete="new-password"]').forEach((input) => {
            input.type = visible ? 'text' : 'password';
        });
        button.setAttribute('aria-pressed', String(visible));
        button.textContent = visible ? 'Ocultar contraseñas' : 'Mostrar contraseñas';
    });
}
