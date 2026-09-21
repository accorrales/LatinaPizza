function initProductCreateForm() {
    const category = document.getElementById('categoria_id');
    const pizzaFields = document.getElementById('camposPizza');
    const categoryName = document.getElementById('categoria_nombre');
    const flavor = document.getElementById('sabor_id');
    const size = document.getElementById('tamano_id');
    if (!category || !pizzaFields || !categoryName) return;

    const toggle = () => {
        const selectedName = category.selectedOptions[0]?.textContent?.trim() || '';
        categoryName.value = selectedName;
        const pizza = selectedName.toLowerCase().includes('pizza');
        pizzaFields.classList.toggle('hidden', !pizza);
        if (!pizza) {
            if (flavor) flavor.value = '';
            if (size) size.value = '';
        }
    };

    category.addEventListener('change', toggle);
    toggle();
}

function updatePromotionComponentVisibility(component) {
    const type = component.querySelector('.component-type');
    if (!type) return;
    const isDrink = type.value === 'bebida';
    component.querySelector('[data-pizza-fields]')?.classList.toggle('hidden', isDrink);
    component.querySelector('[data-drink-fields]')?.classList.toggle('hidden', !isDrink);
}

function initPromotionEditor() {
    const root = document.querySelector('[data-promotion-editor]');
    if (!root) return;

    const container = root.querySelector('#componentes');
    const addButton = root.querySelector('#agregar-componente');
    const template = root.querySelector('#promotion-component-template');
    if (!container || !addButton || !template) return;

    let index = Number.parseInt(root.dataset.nextIndex || '0', 10) || 0;

    const addComponent = () => {
        const fragment = template.content.cloneNode(true);
        fragment.querySelectorAll('[name]').forEach(control => {
            control.name = control.name.replaceAll('__INDEX__', String(index));
        });
        const component = fragment.querySelector('[data-component]');
        container.appendChild(fragment);
        if (component) updatePromotionComponentVisibility(component);
        index += 1;
    };

    addButton.addEventListener('click', addComponent);
    container.addEventListener('click', event => {
        const remove = event.target.closest('[data-remove]');
        if (remove) remove.closest('[data-component]')?.remove();
    });
    container.addEventListener('change', event => {
        if (event.target.matches('.component-type')) {
            updatePromotionComponentVisibility(event.target.closest('[data-component]'));
        }
    });

    container.querySelectorAll('[data-component]').forEach(updatePromotionComponentVisibility);
    if (!container.querySelector('[data-component]')) addComponent();
}

export function initAdminPages() {
    initProductCreateForm();
    initPromotionEditor();
}
