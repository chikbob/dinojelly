<template>
    <div class="dashboard">
        <div class="dashboard__hero">
            <div>
                <h1 class="dashboard__title">{{ t("admin.dashboard.title") }}</h1>
                <p class="dashboard__subtitle">{{ t("admin.dashboard.subtitle") }}</p>
            </div>
        </div>

        <section class="dashboard__stats">
            <article class="stat-card">
                <span>{{ t("admin.dashboard.users") }}</span>
                <strong>{{ stats.users }}</strong>
            </article>
            <article class="stat-card">
                <span>{{ t("admin.dashboard.products") }}</span>
                <strong>{{ stats.products }}</strong>
            </article>
            <article class="stat-card">
                <span>{{ t("admin.dashboard.orders") }}</span>
                <strong>{{ stats.orders }}</strong>
            </article>
            <article class="stat-card">
                <span>{{ t("admin.dashboard.revenue") }}</span>
                <strong>{{ formatMoney(stats.revenue) }}</strong>
            </article>
            <article class="stat-card">
                <span>{{ t("admin.dashboard.aov") }}</span>
                <strong>{{ formatMoney(stats.average_order_value) }}</strong>
            </article>
            <article class="stat-card">
                <span>{{ t("admin.dashboard.repeatCustomers") }}</span>
                <strong>{{ stats.repeat_customers }}</strong>
            </article>
        </section>

        <section class="dashboard__grid">
            <div class="panel panel--wide">
                <h2>{{ t("admin.dashboard.chartTitle") }}</h2>
                <div class="panel__chart">
                    <Bar :data="revenueOrdersChartData" :options="revenueOrdersChartOptions" />
                </div>
            </div>

            <div class="panel">
                <h2>{{ t("admin.dashboard.paymentMix") }}</h2>
                <div class="panel__chart panel__chart--small">
                    <Doughnut :data="paymentChartData" :options="paymentChartOptions" />
                </div>
            </div>
        </section>

        <section class="dashboard__grid">
            <div class="panel">
                <h2>{{ t("admin.dashboard.funnelTitle") }}</h2>
                <div class="funnel">
                    <div class="funnel__row">
                        <span>{{ t("admin.dashboard.funnel.users") }}</span>
                        <strong>{{ funnel.users }}</strong>
                    </div>
                    <div class="funnel__row">
                        <span>{{ t("admin.dashboard.funnel.favorites") }}</span>
                        <strong>{{ funnel.favorites }}</strong>
                    </div>
                    <div class="funnel__row">
                        <span>{{ t("admin.dashboard.funnel.carts") }}</span>
                        <strong>{{ funnel.carts }}</strong>
                    </div>
                    <div class="funnel__row">
                        <span>{{ t("admin.dashboard.funnel.orders") }}</span>
                        <strong>{{ funnel.orders }}</strong>
                    </div>
                    <div class="funnel__row">
                        <span>{{ t("admin.dashboard.funnel.completed") }}</span>
                        <strong>{{ funnel.completed_orders }}</strong>
                    </div>
                </div>
            </div>

            <div class="panel">
                <h2>{{ t("admin.dashboard.recoveryTitle") }}</h2>
                <div class="recovery">
                    <div class="recovery__card">
                        <span>{{ t("admin.dashboard.recovery.sent") }}</span>
                        <strong>{{ recovery.sent }}</strong>
                    </div>
                    <div class="recovery__card">
                        <span>{{ t("admin.dashboard.recovery.recovered") }}</span>
                        <strong>{{ recovery.recovered }}</strong>
                    </div>
                    <div class="recovery__card">
                        <span>{{ t("admin.dashboard.recovery.pending") }}</span>
                        <strong>{{ recovery.pending }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <h2>{{ t("admin.dashboard.topProducts") }}</h2>
            <div class="top-products__wrap">
                <table class="top-products">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>{{ t("admin.products.name") }}</th>
                        <th>{{ t("admin.dashboard.topProductsQty") }}</th>
                        <th>{{ t("admin.dashboard.topProductsRevenue") }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="product in topProducts" :key="product.id">
                        <td>#{{ product.id }}</td>
                        <td>{{ product.name }}</td>
                        <td>{{ product.total_quantity }}</td>
                        <td>{{ formatMoney(product.revenue) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>

<script setup>
import {Bar, Doughnut} from 'vue-chartjs'
import {
    ArcElement,
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js'
import {computed} from "vue";
import {useI18n} from '../../lang/useI18n'

const {t} = useI18n()

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    ArcElement,
    Legend,
    Tooltip
)

const props = defineProps({
    stats: Object,
    ordersChart: Array,
    paymentBreakdown: Object,
    topProducts: Array,
    funnel: Object,
    recovery: Object,
})

const formatMoney = (value) => `${Number(value ?? 0).toFixed(0)} ${t("currency.symbol")}`

const revenueOrdersChartData = computed(() => ({
    labels: props.ordersChart.map((d) => d.date),
    datasets: [
        {
            label: t("admin.dashboard.orders"),
            data: props.ordersChart.map((d) => d.orders),
            backgroundColor: '#93c5fd',
            borderRadius: 8,
            yAxisID: 'y',
        },
        {
            label: t("admin.dashboard.revenue"),
            data: props.ordersChart.map((d) => d.revenue),
            backgroundColor: '#34d399',
            borderRadius: 8,
            yAxisID: 'y1',
        },
    ],
}))

const revenueOrdersChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        mode: 'index',
        intersect: false,
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                precision: 0,
            },
        },
        y1: {
            beginAtZero: true,
            position: 'right',
            grid: {
                drawOnChartArea: false,
            },
        },
    },
}

const paymentChartData = computed(() => ({
    labels: [
        t("payments.status.pending"),
        t("payments.status.paid"),
        t("payments.status.failed"),
        t("payments.status.canceled"),
    ],
    datasets: [
        {
            data: [
                props.paymentBreakdown.pending,
                props.paymentBreakdown.paid,
                props.paymentBreakdown.failed,
                props.paymentBreakdown.canceled,
            ],
            backgroundColor: ['#93c5fd', '#34d399', '#fbbf24', '#fda4af'],
            borderWidth: 0,
        },
    ],
}))

const paymentChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
        },
    },
}
</script>

<style scoped lang="scss">
.dashboard {
    max-width: 1320px;
    margin: 0 auto;
    padding: 24px 8px 40px;
    font-family: "Press Start 2P", system-ui;

    &__hero {
        margin-bottom: 24px;
        min-width: 0;
    }

    &__title {
        margin: 0 0 10px;
        font-size: 28px;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    &__subtitle {
        margin: 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.6;
        max-width: 920px;
        overflow-wrap: anywhere;
    }

    &__stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    &__grid {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }
}

.stat-card,
.panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 20px;
}

.stat-card {
    display: grid;
    gap: 10px;
    min-width: 0;

    span {
        color: #64748b;
        font-size: 11px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    strong {
        font-size: 18px;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }
}

.panel {
    min-width: 0;

    &--wide {
        min-height: 360px;
    }

    h2 {
        margin: 0 0 16px;
        font-size: 14px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    &__chart {
        height: 280px;
        min-width: 0;
    }

    &__chart--small {
        height: 320px;
    }
}

.funnel,
.recovery {
    display: grid;
    gap: 12px;
}

.funnel__row,
.recovery__card {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 12px 14px;
    border-radius: 14px;
    background: #f8fafc;
    min-width: 0;

    span {
        color: #64748b;
        font-size: 11px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    strong {
        font-size: 12px;
        line-height: 1.5;
        text-align: right;
        overflow-wrap: anywhere;
    }
}

.top-products__wrap {
    width: 100%;
    overflow-x: auto;
}

.top-products {
    width: 100%;
    min-width: 640px;
    border-collapse: collapse;

    th,
    td {
        padding: 14px;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
        font-size: 11px;
        vertical-align: top;
        overflow-wrap: anywhere;
    }

    th {
        background: #f8fafc;
    }
}

@media (max-width: 1100px) {
    .dashboard__grid {
        grid-template-columns: minmax(0, 1fr);
    }
}

@media (max-width: 720px) {
    .dashboard {
        padding-inline: 0;
    }

    .dashboard__title {
        font-size: 22px;
    }

    .funnel__row,
    .recovery__card {
        flex-direction: column;
        align-items: flex-start;
    }

    .funnel__row strong,
    .recovery__card strong {
        text-align: left;
    }
}
</style>
