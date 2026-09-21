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
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="extras[]" value="${positiveInteger(extra.id)}" data-precio="${price}">
                    ${escapeHtml(extra.nombre)} (+₡${price.toFixed(0)})
                </label>
            `;
        }).join('');

        container.querySelectorAll('input[name="extras[]"]').forEach(input => {
            input.addEventListener('change', updateProductTotal);
        });
    }

    function changeSize(price, sizeName) {
        basePrice = Number.parseFloat(price) || 0;
        const normalized = String(sizeName || '').toLowerCase();
        let key = 'precio_pequena';
        if (normalized.includes('mediana')) key = 'precio_mediana';
        else if (normalized.includes('grande') && !normalized.includes('extra')) key = 'precio_grande';
        else if (normalized.includes('extra')) key = 'precio_extragrande';
        renderExtras(key);
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

            const sizesContainer = document.getElementById('modalTamanos');
            if (sizesContainer) {
                sizesContainer.innerHTML = (flavor.tamanos || []).map(size => `
                    <label class="flex items-center gap-2 border px-3 py-1 rounded cursor-pointer text-sm text-gray-700">
                        <input type="radio" name="producto_id" value="${positiveInteger(size.producto_id)}"
                            data-precio="${Number(size.precio_base) || 0}"
                            data-tamano="${escapeHtml(String(size.tamano_nombre || '').toLowerCase())}" required>
                        ${escapeHtml(size.tamano_nombre)} - ₡${Number.parseFloat(size.precio_base || 0).toFixed(2)}
                    </label>
                `).join('');

                sizesContainer.querySelectorAll('input[name="producto_id"]').forEach(input => {
                    input.addEventListener('change', () => changeSize(input.dataset.precio, input.dataset.tamano));
                });
            }

            const [doughs, extras] = await Promise.all([
                apiGet('/masas'),
                apiGet('/extras'),
            ]);

            const doughSelect = document.getElementById('masa');
            if (doughSelect) {
                doughSelect.innerHTML = (doughs || []).map(dough => `
                    <option value="${positiveInteger(dough.id)}" data-precio="${Number(dough.precio_extra) || 0}">
                        ${escapeHtml(dough.tipo)} (+₡${Number(dough.precio_extra) || 0})
                    </option>
                `).join('');
                doughSelect.onchange = () => {
                    doughPrice = Number.parseFloat(doughSelect.selectedOptions[0]?.dataset.precio || '0') || 0;
                    updateProductTotal();
                };
                doughSelect.dispatchEvent(new Event('change'));
            }

            extrasData = Array.isArray(extras) ? extras : [];
            renderExtras('precio_pequena');
            productModal?.classList.remove('hidden');
        } catch (error) {
            console.error('No se pudo abrir el producto:', error);
            const doughSelect = document.getElementById('masa');
            if (doughSelect) doughSelect.innerHTML = `<option>${escapeHtml(i18n.error_cargar_masas || 'Error')}</option>`;
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
                            <label class="block text-sm">
                                <input type="checkbox" name="extrasPizza${index}[]" value="${positiveInteger(extra.id)}" data-precio="${price}">
                                ${escapeHtml(extra.nombre)} (+₡${price})
                            </label>
                        `;
                    }).join('');

                    pizzaBlocks += `
                        <div class="mb-6 border-b pb-4">
                            <h3 class="text-sm font-bold text-gray-800 mb-2">🍕 ${escapeHtml(pizzaLabel)}</h3>
                            <label class="text-sm">${escapeHtml(i18n.label_sabor || 'Sabor')}</label>
                            <select id="promoSabor${index}" class="w-full border rounded px-2 py-1 mb-2">${flavorOptions}</select>
                            <label class="text-sm">${escapeHtml(i18n.label_masa || 'Masa')}</label>
                            <select id="promoMasa${index}" class="w-full border rounded px-2 py-1 mb-2">${doughOptions}</select>
                            <label class="text-sm">${escapeHtml(i18n.label_extras || 'Extras')}</label>
                            <div class="mb-2 text-sm" id="extrasPromo${index}">${extrasHtml}</div>
                            <label class="text-sm">${escapeHtml(i18n.label_nota || 'Nota')}</label>
                            <textarea id="notaPizza${index}" class="w-full border rounded px-2 py-1 text-sm mb-2"></textarea>
                        </div>
                    `;
                }
            });

            const drinkOptions = (drinks || []).map(drink => `<option value="${positiveInteger(drink.id)}">${escapeHtml(drink.nombre)}</option>`).join('');
            const drinkHtml = includesDrink ? `
                <div class="mt-4">
                    <label class="text-sm font-bold text-gray-700">🥤 ${escapeHtml(i18n.refresco_incluido || 'Bebida incluida')}</label>
                    <select id="selectBebida" class="w-full border px-3 py-2 rounded text-sm mt-1">
                        <option value="">${escapeHtml(i18n.seleccione_refresco || 'Seleccione')}</option>
                        ${drinkOptions}
                    </select>
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

    root.addEventListener('click', event => {
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
