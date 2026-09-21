import Chart from 'chart.js/auto';

const moneyFormat = new Intl.NumberFormat('es-CR', {
    style: 'currency',
    currency: 'CRC',
    maximumFractionDigits: 0,
});

const dateTimeFormat = new Intl.DateTimeFormat('es-CR', {
    dateStyle: 'medium',
    timeStyle: 'short',
});

function configureChartTheme() {
    Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Noto Sans, Ubuntu, Cantarell';
    Chart.defaults.color = '#475569';
}

function lineConfig(labels, orders, revenue, context) {
    const grid = '#E5E7EB';
    const gradient = context.createLinearGradient(0, 0, 0, 220);
    gradient.addColorStop(0, 'rgba(244,63,94,0.30)');
    gradient.addColorStop(1, 'rgba(244,63,94,0.04)');

    return {
        type: 'line',
        data: {
            labels,
            datasets: [
                { label: '# Pedidos', data: orders, borderWidth: 2, tension: 0.3, borderColor: 'rgb(59,130,246)', backgroundColor: 'transparent' },
                { label: 'Ingresos', data: revenue, yAxisID: 'y1', borderWidth: 2, tension: 0.3, borderColor: 'rgb(244,63,94)', fill: true, backgroundColor: gradient },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            resizeDelay: 150,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: true, position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: contextValue => contextValue.dataset.label === 'Ingresos'
                            ? `${contextValue.dataset.label}: ${moneyFormat.format(contextValue.parsed.y)}`
                            : `${contextValue.dataset.label}: ${contextValue.parsed.y}`,
                    },
                },
            },
            elements: { point: { radius: 0, hitRadius: 6, hoverRadius: 4 } },
            scales: {
                x: { grid: { color: grid, drawTicks: false } },
                y: { grid: { color: grid }, beginAtZero: true, ticks: { precision: 0 } },
                y1: { grid: { drawOnChartArea: false }, beginAtZero: true, position: 'right' },
            },
        },
    };
}

export function registerSalesDashboard(Alpine) {
    configureChartTheme();
    const charts = { daily: null, weekly: null, monthly: null };
    let timer = null;
    let fetching = false;
    let abortController = null;

    const setChartData = (chart, labels, orders, revenue) => {
        chart.data.labels = labels;
        chart.data.datasets[0].data = orders;
        chart.data.datasets[1].data = revenue;
        chart.update('none');
    };

    const ensureChart = (key, canvasId, labels, orders, revenue) => {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const context = canvas.getContext('2d');
        if (!charts[key]) charts[key] = new Chart(context, lineConfig(labels, orders, revenue, context));
        else setChartData(charts[key], labels, orders, revenue);
    };

    Alpine.data('salesDashboard', () => ({
        apiBase: '',
        filters: { tipo_pedido: '', sucursal_id: '' },
        prodRange: 'month',
        topProducts: [],
        maxQty: 1,
        kpi: {
            todayRevenueFmt: '₡0',
            todayOrders: 0,
            weekRevenueFmt: '₡0',
            weekOrders: 0,
            monthRevenueFmt: '₡0',
            monthOrders: 0,
        },
        lastUpdated: '-',
        autoRefresh: false,
        loading: { daily: true, weekly: true, monthly: true, top: true },

        async init() {
            this.apiBase = this.$el.dataset.apiUrl || '/admin/ventas/api';
            await this.reloadAll(true);
        },

        money(value) {
            return moneyFormat.format(value || 0);
        },

        barWidth(quantity) {
            return `${(Math.max(1, Number(quantity) || 0) / Math.max(1, this.maxQty)) * 100}%`;
        },

        setProductRangeDay() {
            this.prodRange = 'day';
            this.loadTopProducts();
        },

        setProductRangeWeek() {
            this.prodRange = 'week';
            this.loadTopProducts();
        },

        setProductRangeMonth() {
            this.prodRange = 'month';
            this.loadTopProducts();
        },

        paramsQuery() {
            const query = new URLSearchParams();
            if (this.filters.tipo_pedido) query.set('tipo_pedido', this.filters.tipo_pedido);
            if (this.filters.sucursal_id) query.set('sucursal_id', this.filters.sucursal_id);
            return query.toString();
        },

        async reloadAll(showLoading = false) {
            if (fetching) return;
            fetching = true;
            if (showLoading) this.loading = { daily: true, weekly: true, monthly: true, top: true };

            abortController?.abort();
            abortController = new AbortController();

            try {
                const query = this.paramsQuery();
                await Promise.all([
                    this.loadDaily(query, abortController.signal),
                    this.loadWeekly(query, abortController.signal),
                    this.loadMonthly(query, abortController.signal),
                    this.loadTopProducts(query, abortController.signal),
                ]);
                this.computeKpis();
                this.lastUpdated = dateTimeFormat.format(new Date());
            } catch (error) {
                if (error.name !== 'AbortError') console.error(error);
            } finally {
                fetching = false;
            }
        },

        async fetchSeries(path, query, signal) {
            const response = await fetch(`${this.apiBase}${path}${query ? `?${query}` : ''}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal,
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        },

        async loadDaily(query, signal) {
            try {
                const payload = await this.fetchSeries('/sales/daily', query, signal);
                const labels = payload.data.map(row => row.date);
                const orders = payload.data.map(row => row.orders);
                const revenue = payload.data.map(row => row.revenue);
                ensureChart('daily', 'chartDaily', labels, orders, revenue);
                this._dailyCache = { labels, orders, revenue };
            } finally {
                this.loading.daily = false;
            }
        },

        async loadWeekly(query, signal) {
            try {
                const payload = await this.fetchSeries('/sales/weekly', query, signal);
                const labels = payload.data.map(row => row.week);
                const orders = payload.data.map(row => row.orders);
                const revenue = payload.data.map(row => row.revenue);
                ensureChart('weekly', 'chartWeekly', labels, orders, revenue);
                this._weeklyCache = { labels, orders, revenue };
            } finally {
                this.loading.weekly = false;
            }
        },

        async loadMonthly(query, signal) {
            try {
                const payload = await this.fetchSeries('/sales/monthly', query, signal);
                const labels = payload.data.map(row => row.month);
                const orders = payload.data.map(row => row.orders);
                const revenue = payload.data.map(row => row.revenue);
                ensureChart('monthly', 'chartMonthly', labels, orders, revenue);
                this._monthlyCache = { labels, orders, revenue };
            } finally {
                this.loading.monthly = false;
            }
        },

        async loadTopProducts(query = this.paramsQuery(), signal = undefined) {
            this.loading.top = true;
            const separator = query ? '&' : '';
            try {
                const response = await fetch(`${this.apiBase}/products/top?range=${this.prodRange}${separator}${query}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal,
                });
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const payload = await response.json();
                this.topProducts = payload.data || [];
                this.maxQty = Math.max(1, ...this.topProducts.map(row => row.qty || 0));
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error(error);
                    window.alert(`Error al cargar productos: ${error.message}`);
                }
            } finally {
                this.loading.top = false;
            }
        },

        computeKpis() {
            const last = values => values?.length ? values[values.length - 1] : 0;
            this.kpi.todayOrders = last(this._dailyCache?.orders) || 0;
            this.kpi.todayRevenueFmt = moneyFormat.format(last(this._dailyCache?.revenue) || 0);
            this.kpi.weekOrders = last(this._weeklyCache?.orders) || 0;
            this.kpi.weekRevenueFmt = moneyFormat.format(last(this._weeklyCache?.revenue) || 0);
            this.kpi.monthOrders = last(this._monthlyCache?.orders) || 0;
            this.kpi.monthRevenueFmt = moneyFormat.format(last(this._monthlyCache?.revenue) || 0);
        },

        toggleAutoRefresh() {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
            if (this.autoRefresh) {
                timer = window.setInterval(() => this.reloadAll(false), 30000);
                window.addEventListener('beforeunload', () => window.clearInterval(timer), { once: true });
            }
        },

        exportTopCsv() {
            const rows = [['Producto', 'Cantidad', 'Ingresos']]
                .concat(this.topProducts.map(row => [row.name, String(row.qty), String(row.revenue)]));
            const csv = rows
                .map(row => row.map(value => `"${String(value ?? '').replace(/"/g, '""')}"`).join(','))
                .join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const anchor = document.createElement('a');
            anchor.href = url;
            anchor.download = `top_productos_${this.prodRange}.csv`;
            anchor.click();
            URL.revokeObjectURL(url);
        },
    }));
}
