function loadStripeSdk() {
    if (typeof window.Stripe === 'function') return Promise.resolve(window.Stripe);

    return new Promise((resolve, reject) => {
        const existing = document.querySelector('script[data-stripe-sdk]');
        if (existing) {
            existing.addEventListener('load', () => resolve(window.Stripe), { once: true });
            existing.addEventListener('error', reject, { once: true });
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://js.stripe.com/v3/';
        script.async = true;
        script.dataset.stripeSdk = '1';
        script.addEventListener('load', () => resolve(window.Stripe), { once: true });
        script.addEventListener('error', () => reject(new Error('No se pudo cargar Stripe.js.')), { once: true });
        document.head.appendChild(script);
    });
}

export function initCheckoutPage() {
    const form = document.getElementById('checkout-form');
    if (!form) return;

    const button = document.getElementById('btn-submit');
    const buttonText = document.getElementById('btn-text');
    const spinner = document.getElementById('btn-spinner');
    const radios = form.querySelectorAll('input[name="metodo_pago"]');
    const stripeBox = document.getElementById('stripe-box');
    const paymentError = document.getElementById('payment-error');
    const paymentIntentInput = document.getElementById('payment_intent_id');
    const stripeEnabled = form.dataset.stripeEnabled === '1';
    const stripeKey = form.dataset.stripeKey || '';
    const intentUrl = form.dataset.intentUrl || '';
    const submitLabel = form.dataset.submitLabel || 'Confirmar pedido';

    let loading = false;

    const setLoading = value => {
        loading = value;
        if (button) button.disabled = value;
        spinner?.classList.toggle('hidden', !value);
        if (buttonText) buttonText.textContent = value ? 'Procesando...' : submitLabel;
    };

    if (!stripeEnabled) {
        form.addEventListener('submit', event => {
            if (loading) {
                event.preventDefault();
                return;
            }
            setLoading(true);
        });
        return;
    }

    let stripe = null;
    let elements = null;
    let paymentElement = null;
    let clientSecret = null;
    let paymentIntentId = null;
    let mounted = false;

    const showStripeBox = show => stripeBox?.classList.toggle('hidden', !show);
    const showPaymentError = message => {
        if (!paymentError) return;
        paymentError.textContent = message || '';
        paymentError.classList.toggle('hidden', !message);
    };

    async function fetchIntentAndSyncElements({ forceRemount = false } = {}) {
        const response = await fetch(intentUrl, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({}),
        });
        if (!response.ok) throw new Error('No se pudo sincronizar el monto del pago.');

        const payload = await response.json();
        const newId = payload.id || payload.payment_intent_id;
        const newSecret = payload.client_secret;
        if (!newId || !newSecret) throw new Error('Respuesta inválida del intent.');

        if (!stripe) {
            const Stripe = await loadStripeSdk();
            if (typeof Stripe !== 'function') throw new Error('Stripe no está disponible.');
            stripe = Stripe(stripeKey);
        }

        if (forceRemount || newId !== paymentIntentId || !elements) {
            paymentIntentId = newId;
            clientSecret = newSecret;
            paymentElement?.unmount?.();
            elements = stripe.elements({ clientSecret });
            paymentElement = elements.create('payment');
            paymentElement.mount('#payment-element');
        } else {
            clientSecret = newSecret;
        }

        mounted = true;
    }

    async function ensureStripeMounted() {
        if (!mounted) await fetchIntentAndSyncElements({ forceRemount: true });
    }

    radios.forEach(radio => {
        radio.addEventListener('change', async event => {
            showPaymentError('');
            if (event.target.value !== 'stripe') {
                showStripeBox(false);
                return;
            }

            showStripeBox(true);
            try {
                await ensureStripeMounted();
            } catch (error) {
                console.error(error);
                showPaymentError('No se pudo inicializar Stripe. Intenta de nuevo.');
            }
        });
    });

    const initiallySelected = Array.from(radios).find(radio => radio.checked)?.value;
    if (initiallySelected === 'stripe') {
        showStripeBox(true);
        ensureStripeMounted().catch(error => {
            console.error(error);
            showPaymentError('No se pudo inicializar Stripe.');
        });
    }

    form.addEventListener('submit', async event => {
        const method = Array.from(radios).find(radio => radio.checked)?.value || 'efectivo';
        if (loading) {
            event.preventDefault();
            return;
        }

        if (method !== 'stripe') {
            setLoading(true);
            return;
        }

        event.preventDefault();
        showPaymentError('');
        setLoading(true);

        try {
            await fetchIntentAndSyncElements();
            const { error } = await stripe.confirmPayment({ elements, redirect: 'if_required' });
            if (error) {
                showPaymentError(error.message || 'Se ha producido un error de procesamiento.');
                setLoading(false);
                return;
            }

            const { paymentIntent } = await stripe.retrievePaymentIntent(clientSecret);
            if (paymentIntent?.status === 'succeeded') {
                if (paymentIntentInput) paymentIntentInput.value = paymentIntentId;
                form.submit();
                return;
            }

            showPaymentError('No se pudo confirmar el pago. Intenta de nuevo.');
        } catch (error) {
            console.error(error);
            showPaymentError(error?.message || 'No se pudo procesar el pago.');
        }

        setLoading(false);
    });
}
