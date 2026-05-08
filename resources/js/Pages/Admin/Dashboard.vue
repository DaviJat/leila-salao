<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import Chart from 'primevue/chart';

const props = defineProps({
    filters: Object,
    kpis: Object,
    charts: Object,
});

/** * Converte string de data ISO (YYYY-MM-DD) para objeto Date sem offset de fuso horário.
 */
const parseDate = (dateString) => {
    if (!dateString) return null;
    return new Date(dateString + 'T00:00:00');
};

const dateRange = ref([parseDate(props.filters?.start_date), parseDate(props.filters?.end_date)]);

/** * Sincroniza o estado local do DatePicker com as propriedades enviadas pelo backend.
 */
watch(
    () => props.filters,
    (newFilters) => {
        if (newFilters) {
            dateRange.value = [parseDate(newFilters.start_date), parseDate(newFilters.end_date)];
        }
    },
    { deep: true },
);

/** * Formata valores numéricos para o padrão monetário brasileiro (BRL).
 */
const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
};

/** * Converte objeto Date nativo para o formato de persistência do banco (YYYY-MM-DD).
 */
const formatDateToDB = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${d.getFullYear()}-${month}-${day}`;
};

/** * Submete os filtros de data aplicados via Inertia, preservando o estado e o scroll atual da página.
 */
const applyFilter = (start, end) => {
    if (!start || !end) return;

    router.get(
        window.location.pathname,
        {
            start_date: formatDateToDB(start),
            end_date: formatDateToDB(end),
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

/** Métodos auxiliares de seleção rápida de períodos. */
const setToday = () => {
    const today = new Date();
    applyFilter(today, today);
};

const setWeek = () => {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), today.getDate() - today.getDay());
    const lastDay = new Date(today.getFullYear(), today.getMonth(), today.getDate() - today.getDay() + 6);
    applyFilter(firstDay, lastDay);
};

const setMonth = () => {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    applyFilter(firstDay, lastDay);
};

const setYear = () => {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), 0, 1);
    const lastDay = new Date(today.getFullYear(), 11, 31);
    applyFilter(firstDay, lastDay);
};

/** * Intercepta o evento de alteração manual do DatePicker para submissão imediata.
 */
const onDateSelect = (val) => {
    if (val && val[0] && val[1]) {
        applyFilter(val[0], val[1]);
    }
};

/** Estados de configuração para o componente Chart.js. */
const lineChartData = ref({});
const lineChartOptions = ref({});
const barChartData = ref({});
const barChartOptions = ref({});

/** * Estrutura a configuração de renderização dos gráficos, absorvendo dinamicamente as
 * variáveis de cor do preset global do PrimeVue.
 */
const setChartData = () => {
    if (!props.charts) return;

    const documentStyle = getComputedStyle(document.documentElement);
    const brandColor = documentStyle.getPropertyValue('--p-primary-500').trim() || '#5a7253';
    const brandColorLight = brandColor + '20';
    const textColor = documentStyle.getPropertyValue('--p-surface-700').trim() || '#51514e';
    const textColorSecondary = documentStyle.getPropertyValue('--p-surface-500').trim() || '#7e7e7a';
    const surfaceBorder = documentStyle.getPropertyValue('--p-surface-200').trim() || '#e8e8e6';

    lineChartData.value = {
        labels: props.charts.receita.labels,
        datasets: [
            {
                label: 'Receita (R$)',
                data: props.charts.receita.data,
                fill: true,
                borderColor: brandColor,
                backgroundColor: brandColorLight,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: brandColor,
            },
        ],
    };

    lineChartOptions.value = {
        maintainAspectRatio: false,
        plugins: { legend: { labels: { color: textColor, font: { family: 'Inter, sans-serif' } } } },
        scales: {
            x: { ticks: { color: textColorSecondary }, grid: { color: surfaceBorder } },
            y: { ticks: { color: textColorSecondary }, grid: { color: surfaceBorder } },
        },
    };

    barChartData.value = {
        labels: props.charts.servicos.labels,
        datasets: [
            {
                label: 'Realizados',
                data: props.charts.servicos.data,
                backgroundColor: brandColor,
                borderRadius: 4,
            },
        ],
    };

    barChartOptions.value = {
        maintainAspectRatio: false,
        plugins: { legend: { labels: { color: textColor, font: { family: 'Inter, sans-serif' } } } },
        scales: {
            x: { ticks: { color: textColorSecondary }, grid: { display: false } },
            y: { ticks: { color: textColorSecondary, stepSize: 1 }, grid: { color: surfaceBorder } },
        },
    };
};

/** * Monitora atualizações do objeto charts para re-renderização em tempo real.
 */
watch(
    () => props.charts,
    () => {
        setChartData();
    },
    { deep: true },
);

onMounted(() => {
    setChartData();
});
</script>

<template>
    <Head title="Painel - Cabeleila" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto font-sans transition-all duration-700 ease-out">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-sm text-gray-500 mt-1" v-if="props.filters?.start_date">
                        {{ props.filters.start_date.split('-').reverse().join('/') }} a
                        {{ props.filters.end_date.split('-').reverse().join('/') }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Button
                        label="Hoje"
                        size="small"
                        outlined
                        class="!rounded-full !text-gray-600 !border-gray-300 hover:!bg-primary-50 hover:!text-primary-700"
                        @click="setToday" />
                    <Button
                        label="Semana"
                        size="small"
                        outlined
                        class="!rounded-full !text-gray-600 !border-gray-300 hover:!bg-primary-50 hover:!text-primary-700"
                        @click="setWeek" />
                    <Button
                        label="Mês"
                        size="small"
                        outlined
                        class="!rounded-full !text-gray-600 !border-gray-300 hover:!bg-primary-50 hover:!text-primary-700"
                        @click="setMonth" />
                    <Button label="Ano" size="small" outlined class="!rounded-full !text-gray-600 !border-gray-300 hover:!bg-primary-50 hover:!text-primary-700" @click="setYear" />

                    <div class="w-px h-6 bg-gray-300 mx-2 hidden sm:block"></div>

                    <DatePicker
                        v-model="dateRange"
                        selectionMode="range"
                        dateFormat="dd/mm/yy"
                        placeholder="Período Personalizado"
                        showIcon
                        class="w-full sm:w-auto"
                        @update:modelValue="onDateSelect" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Receita Total</h3>
                    <p class="text-2xl font-bold text-primary-600">{{ formatCurrency(kpis?.receitaTotal) }}</p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Agendamentos</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ kpis?.totalAgendamentos }}</p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Concluídos</h3>
                    <p class="text-2xl font-bold text-green-600">{{ kpis?.concluidos }}</p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Confirmados</h3>
                    <p class="text-2xl font-bold text-teal-600">{{ kpis?.confirmados }}</p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Pendentes</h3>
                    <p class="text-2xl font-bold text-orange-500">{{ kpis?.pendentes }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Evolução de Receita</h3>
                    <div class="h-80 relative">
                        <Chart type="line" :data="lineChartData" :options="lineChartOptions" class="h-full w-full" />
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Serviços Mais Realizados</h3>
                    <div class="h-80 relative">
                        <Chart type="bar" :data="barChartData" :options="barChartOptions" class="h-full w-full" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
