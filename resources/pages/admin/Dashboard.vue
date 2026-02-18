<template>
    <div class="dashboard">
        <h1 class="dashboard__title">
            {{ t("admin.dashboard.title") }}
        </h1>

        <!-- Stats -->
        <div class="stats">
            <div class="stat-card">
                <span>{{ t("admin.dashboard.users") }}</span>
                <strong>{{ stats.users }}</strong>
            </div>

            <div class="stat-card">
                <span>{{ t("admin.dashboard.products") }}</span>
                <strong>{{ stats.products }}</strong>
            </div>

            <div class="stat-card">
                <span>{{ t("admin.dashboard.orders") }}</span>
                <strong>{{ stats.orders }}</strong>
            </div>
        </div>

        <!-- Chart -->
        <div class="chart">
            <h2>{{ t("admin.dashboard.chartTitle") }}</h2>
            <Line :data="chartData" :options="chartOptions"/>
        </div>
    </div>
</template>

<script setup>
import {Line} from 'vue-chartjs'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Tooltip,
    Filler,
} from 'chart.js'

import {useI18n} from '../../lang/useI18n'
import {computed} from "vue";

const {t} = useI18n()

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Tooltip,
    Filler
)

const props = defineProps({
    stats: Object,
    ordersChart: Array,
})

const chartData = computed(() => ({
    labels: props.ordersChart.map(d => d.date),
    datasets: [
        {
            label: t("admin.dashboard.orders"),
            data: props.ordersChart.map(d => d.total),
            borderColor: '#3ecf8e',
            backgroundColor: 'rgba(62, 207, 142, 0.2)',
            fill: true,
            tension: 0.4,
        },
    ],
}))

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                precision: 0,
            },
        },
    },
}
</script>

<style scoped lang="scss">
.dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
    font-family: "Press Start 2P", system-ui;

    &__title {
        font-size: 24px;
        text-align: center;
        margin-bottom: 40px;
    }
}

.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: #f5f7fa;
    padding: 24px;
    border-radius: 12px;
    text-align: center;

    span {
        display: block;
        font-size: 12px;
        margin-bottom: 10px;
        color: #666;
    }

    strong {
        font-size: 28px;
    }
}

.chart {
    background: #f5f7fa;
    border-radius: 12px;
    padding: 24px;
    height: 300px;

    h2 {
        font-size: 14px;
        margin-bottom: 16px;
        text-align: center;
    }
}
</style>
