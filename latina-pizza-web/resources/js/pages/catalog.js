import { hideLoading, showLoading } from '../core/ui';

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>'"]/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#039;',
        '"': '&quot;',
    })[character]);
}

function positiveInteger(value) {
    const number = Number(value);
    return Number.isInteger(number) && number > 0 ? number : 0;
}

function sizeAbbreviation(name) {
    const normalized = String(name || '').toLowerCase();
    if (normalized.includes('extra')) return 'XL';
    if (normalized.includes('grande')) return 'L';
    if (normalized.includes('mediana')) return 'M';
    if (normalized.includes('peque')) return 'S';
    return String(name || '?').slice(0, 2).toUpperCase();
}

function formatColones(value) {
    return `₡${Math.round(Number(value) || 0).toLocaleString('es-CR')}`;
}

function priceKeyForSize(sizeName) {
    const normalized = String(sizeName || '').toLowerCase();
    if (normalized.includes('extra')) return 'precio_extragrande';
    if (normalized.includes('grande')) return 'precio_grande';
    if (normalized.includes('mediana')) return 'precio_mediana';
    return 'precio_pequena';
}

function parseJson(value, fallback = {}) {
    try {
        return JSON.parse(value || '');
    } catch {
        return fallback;
    }
}

function unwrapApiPayload(payload) {
    return payload?.data ?? payload;
}

export function initCatalogPage() {
    const root = document.querySelector('[data-catalog-root]');
    if (!root) return;

    const apiBase = (root.dataset.apiUrl || '').replace(/\/$/, '');
    const loginUrl = root.dataset.loginUrl || '/login';
    const i18n = parseJson(root.dataset.i18n, {});

    let extrasData = [];
    let basePrice = 0;
    let doughPrice = 0;
    let currentPriceKey = 'precio_pequena';
    let selectedPromotionId = null;
    let promotionBasePrice = 0;
    let promotionExtras = [];

    const productModal = document.getElementById('modalSabor');
    const promotionModal = document.getElementById('modalPromocion');
    const confirmationModal = document.getElementById('modalConfirmacion');

    async function apiGet(path) {
        const response = await fetch(`${apiBase}/api${path}`, {
            headers: { Accept: 'application/json' },
        });
        if (!response.ok) throw new Error(`${path} -> ${response.status}`);
        return unwrapApiPayload(await response.json());
    }

    function closeProductModal() {
        productModal?.classList.add('hidden');
    }

    function closePromotionModal() {
        promotionModal?.classList.add('hidden');
    }

    function closeConfirmationModal() {
        confirmationModal?.classList.add('hidden');
    }

    function updateProductTotal() {
        const selectedExtras = document.querySelectorAll('input[name="extras[]"]:checked');
        let extrasTotal = 0;
        selectedExtras.forEach(extra => {
            extrasTotal += Number.parseFloat(extra.dataset.precio || '0') || 0;
        });

        const total = basePrice + doughPrice + extrasTotal;
        const totalLabel = document.getElementById('precioTotal');
        const totalInput = document.getElementById('inputPrecioTotal');
        if (totalLabel) totalLabel.textContent = `₡${total.toFixed(2)}`;
        if (totalInput) totalInput.value = total.toFixed(2);
    }

    function renderExtras(priceKey) {
        const container = document.getElementById('extrasOpciones');
        if (!container) return;

        container.innerHTML = extrasData.map(extra => {
            const price = Number.parseFloat(extra[priceKey]) || 0;
            return `
                <label class="extra-switch">
                    <span class="extra-switch__info">
                        <span class="extra-switch__dot"></span>
                        <span class="extra-switch__name">${escapeHtml(extra.nombre)}</span>
                    </span>
                    <span class="extra-switch__right">
                        <span class="extra-switch__price">+${formatColones(price)}</span>
                        <span class="switch">
                            <input type="checkbox" name="extras[]" value="${positiveInteger(extra.id)}" data-precio="${price}">
                            <span class="switch__track"><span class="switch__thumb"></span></span>
                        </span>
                    </span>
                </label>
            `;
        }).join('');

        container.querySelectorAll('input[name="extras[]"]').forEach(input => {
            input.addEventListener('change', updateProductTotal);
        });
    }

    function changeSize(price, sizeName) {
        basePrice = Number.parseFloat(price) || 0;
        currentPriceKey = priceKeyForSize(sizeName);
        renderExtras(currentPriceKey);
        updateProductTotal();
    }

    async function openProductModal(button) {
        const flavor = parseJson(button.dataset.sabor, null);
        if (!flavor) return;

        showLoading();
        basePrice = 0;
        doughPrice = 0;
        extrasData = [];

        try {
            const image = document.getElementById('modalImagen');
            const name = document.getElementById('modalNombre');
            const description = document.getElementById('modalDescripcion');
            if (image) image.src = String(flavor.imagen || '');
            if (name) name.textContent = String(flavor.sabor_nombre || '');
            if (description) description.textContent = String(flavor.descripcion || '');

            const rating = document.getElementById('modalRating');
            if (rating) {
                const average = Number.parseFloat(flavor.promedio) || 0;
                const reviews = positiveInteger(flavor.total_resenas);
                if (average > 0) {
                    rating.innerHTML = `<i class="fas fa-star text-amber-400"></i> ${average.toFixed(1)}${reviews ? ` <span class="font-medium text-slate-400">(${reviews})</span>` : ''}`;
                    rating.classList.remove('hidden');
                    rating.classList.add('flex');
                } else {
                    rating.classList.add('hidden');
                    rating.classList.remove('flex');
                }
            }

            const sizesContainer = document.getElementById('modalTamanos');
            const sizes = flavor.tamanos || [];
            if (sizesContainer) {
                sizesContainer.innerHTML = sizes.map((size, index) => `
                    <label class="size-pill">
                        <input type="radio" name="producto_id" value="${positiveInteger(size.producto_id)}"
                            data-precio="${Number(size.precio_base) || 0}"
                            data-tamano="${escapeHtml(String(size.tamano_nombre || '').toLowerCase())}" ${index === 0 ? 'checked' : ''} required>
                        <span class="size-pill__abbr">${escapeHtml(sizeAbbreviation(size.tamano_nombre))}</span>
                        <span class="size-pill__name">${escapeHtml(size.tamano_nombre)}</span>
                        <span class="size-pill__price">${formatColones(size.precio_base)}</span>
                    </label>
                `).join('');

                sizesContainer.querySelectorAll('input[name="producto_id"]').forEach(input => {
                    input.addEventListener('change', () => changeSize(input.dataset.precio, input.dataset.tamano));
                });
            }

            // Preselecciona el primer tamaño para que el precio arranque calculado.
            const firstSize = sizes[0];
            if (firstSize) {
                basePrice = Number.parseFloat(firstSize.precio_base) || 0;
                currentPriceKey = priceKeyForSize(firstSize.tamano_nombre);
            }

            const [doughs, extras] = await Promise.all([
                apiGet('/masas'),
                apiGet('/extras'),
            ]);

            const doughContainer = document.getElementById('masaOpciones');
            if (doughContainer) {
                doughContainer.innerHTML = (doughs || []).map((dough, index) => {
                    const extra = Number(dough.precio_extra) || 0;
                    return `
                        <label class="sauce-pill">
                            <input type="radio" name="masa_id" value="${positiveInteger(dough.id)}" data-precio="${extra}" ${index === 0 ? 'checked' : ''}>
                            <span class="sauce-pill__dot"></span>
                            <span class="sauce-pill__name">${escapeHtml(dough.tipo)}</span>
                            ${extra > 0 ? `<span class="sauce-pill__extra">+${formatColones(extra)}</span>` : ''}
                        </label>
                    `;
                }).join('');

                const updateDough = () => {
                    const checked = doughContainer.querySelector('input[name="masa_id"]:checked');
                    doughPrice = Number.parseFloat(checked?.dataset.precio || '0') || 0;
                    updateProductTotal();
                };
                doughContainer.querySelectorAll('input[name="masa_id"]').forEach(input => {
                    input.addEventListener('change', updateDough);
                });
                updateDough();
            }

            extrasData = Array.isArray(extras) ? extras : [];
            renderExtras(currentPriceKey);
            updateProductTotal();
            productModal?.classList.remove('hidden');
        } catch (error) {
            console.error('No se pudo abrir el producto:', error);
            const doughContainer = document.getElementById('masaOpciones');
            if (doughContainer) doughContainer.innerHTML = `<p class="text-xs text-red-500">${escapeHtml(i18n.error_cargar_masas || 'Error')}</p>`;
            const extrasContainer = document.getElementById('extrasOpciones');
            if (extrasContainer) extrasContainer.innerHTML = `<p class="text-xs text-red-500">${escapeHtml(i18n.error_cargar_extras || 'Error')}</p>`;
        } finally {
            hideLoading();
        }
    }

    async function openPromotionModal(id) {
        const promotionId = positiveInteger(id);
        if (!promotionId) return;

        showLoading();
        selectedPromotionId = promotionId;
        promotionModal?.classList.remove('hidden');

        try {
            const [promotion, doughs, flavors, drinks, extras] = await Promise.all([
                apiGet(`/promociones/${promotionId}`),
                apiGet('/masas'),
                apiGet('/sabores'),
                apiGet('/bebidas'),
                apiGet('/extras'),
            ]);

            promotionExtras = Array.isArray(extras) ? extras : [];
            promotionBasePrice = Number.parseFloat(promotion?.precio_base ?? promotion?.precio_total ?? 0) || 0;
            const includesDrink = promotion?.incluye_bebida === true;
            const pizzaComponents = (promotion?.componentes || []).filter(component => component.tipo === 'pizza');

            let pizzaBlocks = '';
            let globalCounter = 1;

            pizzaComponents.forEach((component, componentIndex) => {
                const sizeText = String(component.tamano?.nombre || 'grande').toLowerCase();
                let priceKey = 'precio_grande';
                if (sizeText.includes('peque')) priceKey = 'precio_pequena';
                else if (sizeText.includes('mediana')) priceKey = 'precio_mediana';
                else if (sizeText.includes('extra')) priceKey = 'precio_extragrande';

                for (let itemIndex = 0; itemIndex < (component.cantidad || 1); itemIndex += 1) {
                    const index = `${componentIndex}_${itemIndex}`;
                    const pizzaLabel = String(i18n.pizza_label || 'Pizza :num - Size :tamano')
                        .replace(':num', globalCounter++)
                        .replace(':tamano', component.tamano?.nombre || '');
                    const flavorOptions = (flavors || []).map(flavor => `<option value="${positiveInteger(flavor.id)}">${escapeHtml(flavor.nombre)}</option>`).join('');
                    const doughOptions = (doughs || []).map(dough => `<option value="${positiveInteger(dough.id)}">${escapeHtml(dough.tipo)}</option>`).join('');
                    const extrasHtml = promotionExtras.map(extra => {
                        const price = Number.parseFloat(extra[priceKey]) || 0;
                        return `
                            <label class="extra-switch">
                                <span class="extra-switch__info">
                                    <span class="extra-switch__dot"></span>
                                    <span class="extra-switch__name">${escapeHtml(extra.nombre)}</span>
                                </span>
                                <span class="extra-switch__right">
                                    <span class="extra-switch__price">+${formatColones(price)}</span>
                                    <span class="switch">
                                        <input type="checkbox" name="extrasPizza${index}[]" value="${positiveInteger(extra.id)}" data-precio="${price}">
                                        <span class="switch__track"><span class="switch__thumb"></span></span>
                                    </span>
                                </span>
                            </label>
                        `;
                    }).join('');

                    pizzaBlocks += `
                        <div class="promo-pizza">
                            <div class="promo-pizza__head">
                                <span class="promo-pizza__badge"><i class="fas fa-pizza-slice"></i> ${escapeHtml(pizzaLabel)}</span>
                            </div>
                            <div class="promo-field">
                                <label class="promo-field__label">${escapeHtml(i18n.label_sabor || 'Sabor')}</label>
                                <select id="promoSabor${index}" class="promo-select">${flavorOptions}</select>
                            </div>
                            <div class="promo-field">
                                <label class="promo-field__label">${escapeHtml(i18n.label_masa || 'Masa')}</label>
                                <select id="promoMasa${index}" class="promo-select">${doughOptions}</select>
                            </div>
                            <div class="promo-field">
                                <label class="promo-field__label">${escapeHtml(i18n.label_extras || 'Extras')}</label>
                                <div class="space-y-2" id="extrasPromo${index}">${extrasHtml}</div>
                            </div>
                            <div class="promo-field">
                                <label class="promo-field__label">${escapeHtml(i18n.label_nota || 'Nota')}</label>
                                <textarea id="notaPizza${index}" class="promo-textarea" rows="2"></textarea>
                            </div>
                        </div>
                    `;
                }
            });

            const drinkOptions = (drinks || []).map(drink => `<option value="${positiveInteger(drink.id)}">${escapeHtml(drink.nombre)}</option>`).join('');
            const drinkHtml = includesDrink ? `
                <div class="promo-pizza">
                    <div class="promo-field">
                        <label class="promo-field__label"><i class="fas fa-mug-hot text-blue-500"></i> ${escapeHtml(i18n.refresco_incluido || 'Bebida incluida')}</label>
                        <select id="selectBebida" class="promo-select">
                            <option value="">${escapeHtml(i18n.seleccione_refresco || 'Seleccione')}</option>
                            ${drinkOptions}
                        </select>
                    </div>
                </div>
            ` : '';

            const container = document.getElementById('contenedorPizzaPersonalizada');
            if (container) {
                container.innerHTML = pizzaBlocks + drinkHtml;
                container.querySelectorAll('input[name^="extrasPizza"]').forEach(input => {
                    input.addEventListener('change', calculatePromotionTotal);
                });
            }

            calculatePromotionTotal();
        } catch (error) {
            console.error('No se pudo abrir la promoción:', error);
            const container = document.getElementById('contenedorPizzaPersonalizada');
            if (container) {
                container.innerHTML = `<p class="text-red-500">${escapeHtml(i18n.error_cargar_promocion || 'Error al cargar datos de la promoción.')}</p>`;
            }
        } finally {
            hideLoading();
        }
    }

    function calculatePromotionTotal() {
        let extrasTotal = 0;
        document.querySelectorAll('input[name^="extrasPizza"]').forEach(input => {
            if (input.checked) extrasTotal += Number.parseFloat(input.dataset.precio || '0') || 0;
        });
        const total = promotionBasePrice + extrasTotal;
        const label = document.getElementById('totalPromo');
        if (label) label.textContent = `${i18n.total || 'Total'} ₡${total.toFixed(2)}`;
    }

    async function addPromotionToCart() {
        const products = [];
        let componentIndex = 0;

        while (document.getElementById(`promoSabor${componentIndex}_0`)) {
            let itemIndex = 0;
            while (document.getElementById(`promoSabor${componentIndex}_${itemIndex}`)) {
                const index = `${componentIndex}_${itemIndex}`;
                const flavorId = document.getElementById(`promoSabor${index}`)?.value;
                const doughId = document.getElementById(`promoMasa${index}`)?.value;
                const extras = Array.from(document.querySelectorAll(`#extrasPromo${index} input[type="checkbox"]:checked`))
                    .map(input => positiveInteger(input.value))
                    .filter(Boolean);
                const note = document.getElementById(`notaPizza${index}`)?.value || '';

                if (!flavorId || !doughId) {
                    window.alert(String(i18n.validar_pizza_incompleta || 'Pizza incompleta').replace(':num', componentIndex + 1));
                    return;
                }

                products.push({
                    tipo: 'pizza',
                    sabor_id: positiveInteger(flavorId),
                    masa_id: positiveInteger(doughId),
                    extras,
                    nota_cliente: note,
                });
                itemIndex += 1;
            }
            componentIndex += 1;
        }

        const drink = document.getElementById('selectBebida');
        if (drink?.value) {
            products.push({ tipo: 'bebida', producto_id: positiveInteger(drink.value) });
        }

        showLoading();
        try {
            const response = await fetch('/carrito/agregar-promocion', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ promocion_id: selectedPromotionId, productos: products }),
            });

            if (response.status === 401) {
                window.location.assign(loginUrl);
                return;
            }
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const payload = await response.json();
            const finalPrice = payload?.data?.precio_total;
            if (Number.isFinite(Number(finalPrice))) {
                const total = document.getElementById('totalPromo');
                if (total) {
                    total.textContent = `${i18n.total || 'Total'} ₡${Number(finalPrice).toLocaleString('es-CR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    })}`;
                }
            }

            closePromotionModal();
            confirmationModal?.classList.remove('hidden');
        } catch (error) {
            console.error('No se pudo agregar la promoción:', error);
            window.alert(i18n.promo_error || 'No se pudo agregar la promoción.');
        } finally {
            hideLoading();
        }
    }

    // Delegación en document: los modales se renderizan fuera de [data-catalog-root]
    // (para no quedar atrapados por el transform del contenedor), así que el listener
    // debe vivir en document para capturar los clics de cerrar/abrir en todos lados.
    document.addEventListener('click', event => {
        const productButton = event.target.closest('[data-catalog-product]');
        if (productButton) {
            openProductModal(productButton);
            return;
        }

        const promotionButton = event.target.closest('[data-promotion-id]');
        if (promotionButton) {
            openPromotionModal(promotionButton.dataset.promotionId);
            return;
        }

        if (event.target.closest('[data-close-product-modal]')) closeProductModal();
        if (event.target.closest('[data-close-promotion-modal]')) closePromotionModal();
        if (event.target.closest('[data-close-confirmation-modal]')) closeConfirmationModal();
        if (event.target.closest('[data-add-promotion]')) addPromotionToCart();

        // Cerrar al hacer clic en el fondo (fuera de la tarjeta del modal)
        if (event.target === productModal) closeProductModal();
        if (event.target === promotionModal) closePromotionModal();
        if (event.target === confirmationModal) closeConfirmationModal();
    });

    // Cerrar con la tecla Escape
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        closeProductModal();
        closePromotionModal();
        closeConfirmationModal();
    });

    document.addEventListener('latina:open-promotion', event => openPromotionModal(event.detail));

    const productForm = document.getElementById('formAgregarProducto');
    productForm?.addEventListener('submit', async event => {
        event.preventDefault();
        showLoading();

        try {
            const response = await fetch('/carrito/agregar', {
                method: 'POST',
                body: new FormData(productForm),
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });

            if (response.status === 401) {
                let payload = {};
                try { payload = await response.json(); } catch { /* response can be empty */ }
                window.location.assign(payload.redirect || loginUrl);
                return;
            }

            if (response.ok && (response.headers.get('content-type') || '').includes('application/json')) {
                const payload = await response.json();
                if (payload.ok && payload.next) {
                    window.location.assign(payload.next);
                    return;
                }
            }

            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            closeProductModal();
            confirmationModal?.classList.remove('hidden');
        } catch (error) {
            console.error('No se pudo agregar el producto:', error);
            window.alert(i18n.producto_error || 'No se pudo agregar el producto.');
        } finally {
            hideLoading();
        }
    });
}
