<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Chart from 'primevue/chart';
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    earnings: { type: Array, default: () => [] },
    topServices: { type: Array, default: () => [] },
    appointmentsOverview: { type: Object, default: () => ({ byDay: [], totals: {} }) },
    filters: { type: Object, default: () => ({ start_date: '', end_date: '' }) },
});

const groupBy = ref('day');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const earnings = ref(props.earnings || []);
const topServices = ref(props.topServices || []);
const overview = ref(props.appointmentsOverview || { byDay: [], totals: {} });

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const [year, month, day] = String(dateStr).substring(0, 10).split('-');
    return `${day}/${month}/${year}`;
};

const formatDateLabel = (dateStr, group) => {
    if (!dateStr) return '';
    const [year, month, day] = String(dateStr).substring(0, 10).split('-');
    if (group === 'day') return `${day}/${month}`;
    if (group === 'month') return `${month}/${year.slice(2)}`;
    if (group === 'year') return year;
    return `${day}/${month}/${year}`;
};

const formatDateToYYYYMMDD = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const setPeriod = (period) => {
    const today = new Date();
    let start, end;

    switch (period) {
        case 'today':
            // Hoje: mesma data
            start = new Date(today);
            end = new Date(today);
            groupBy.value = 'day';
            break;

        case 'week':
            // Semana atual: de segunda a domingo
            start = new Date(today);
            const dayOfWeek = start.getDay();
            const offsetToMonday = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
            start.setDate(start.getDate() + offsetToMonday);
            end = new Date(start);
            end.setDate(start.getDate() + 6);
            groupBy.value = 'day';
            break;

        case 'month':
            // Mês: do dia 1 até o último dia do mês
            start = new Date(today.getFullYear(), today.getMonth(), 1);
            end = new Date(today.getFullYear(), today.getMonth() + 1, 0); // dia 0 do próximo mês = último dia deste
            groupBy.value = 'day';
            break;

        case 'year':
            // Ano: 1º de janeiro até 31 de dezembro
            start = new Date(today.getFullYear(), 0, 1);
            end = new Date(today.getFullYear(), 11, 31);
            groupBy.value = 'month';
            break;

        default:
            return;
    }

    startDate.value = formatDateToYYYYMMDD(start);
    endDate.value = formatDateToYYYYMMDD(end);
    fetchData();
};

const fetchData = async () => {
    const params = new URLSearchParams();
    if (startDate.value) params.append('start_date', startDate.value);
    if (endDate.value) params.append('end_date', endDate.value);
    params.append('group', groupBy.value);

    const res = await fetch(route('admin.dashboard.data') + '?' + params.toString(), { headers: { Accept: 'application/json' } });
    if (res.ok) {
        const data = await res.json();
        earnings.value = data.earnings || [];
        topServices.value = data.topServices || [];
        overview.value = data.overview || { byDay: [], totals: {} };
    }
};

onMounted(() => fetchData());

const totalEarnings = computed(() => earnings.value.reduce((s, r) => s + (r.total || 0), 0));
const totalAppointments = computed(() => overview.value.totals.pending + overview.value.totals.confirmed + overview.value.totals.completed + overview.value.totals.canceled);

const chartDataEarnings = computed(() => ({
    labels: earnings.value.map((e) => formatDateLabel(e.period, groupBy.value)),
    datasets: [
        {
            label: 'Receita (R$)',
            data: earnings.value.map((e) => e.total || 0),
            borderColor: '#547558',
            backgroundColor: 'rgba(84, 117, 88, 0.1)',
            fill: true,
            tension: 0.4,
            borderWidth: 2,
            pointRadius: 5,
            pointBackgroundColor: '#547558',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointHoverRadius: 7,
        },
    ],
}));

const chartDataServices = computed(() => ({
    labels: topServices.value.map((s) => s.name),
    datasets: [
        {
            label: 'Realizados',
            data: topServices.value.map((s) => s.performed),
            backgroundColor: ['#547558', '#6b8e5f', '#7fa866', '#95b977', '#a8c88a', '#bbdb9c'],
            borderColor: '#547558',
            borderWidth: 1,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top',
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: function (value) {
                    return 'R$ ' + value.toLocaleString('pt-BR');
                },
            },
        },
    },
};

const chartOptionsServices = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top',
        },
    },
    scales: {
        y: {
            beginAtZero: true,
        },
    },
};
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- HEADER COM FILTROS -->
            <div class="bg-white/90 border border-gray-100 rounded-3xl p-6 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">Dashboard</h2>
                        <p class="text-sm text-gray-600">{{ formatDate(startDate) }} a {{ formatDate(endDate) }}</p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 items-end">
                        <!-- Quick Periods -->
                        <div class="flex flex-wrap gap-2">
                            <button @click="setPeriod('today')" class="px-3 py-2 text-xs font-semibold rounded-lg border border-gray-200 hover:bg-gray-50 transition">Hoje</button>
                            <button @click="setPeriod('week')" class="px-3 py-2 text-xs font-semibold rounded-lg border border-gray-200 hover:bg-gray-50 transition">Semana</button>
                            <button @click="setPeriod('month')" class="px-3 py-2 text-xs font-semibold rounded-lg border border-gray-200 hover:bg-gray-50 transition">Mês</button>
                            <button @click="setPeriod('year')" class="px-3 py-2 text-xs font-semibold rounded-lg border border-gray-200 hover:bg-gray-50 transition">Ano</button>
                        </div>

                        <!-- Date Inputs -->
                        <input type="date" v-model="startDate" @change="fetchData" class="px-3 py-2 text-sm border border-gray-200 rounded-lg" />
                        <input type="date" v-model="endDate" @change="fetchData" class="px-3 py-2 text-sm border border-gray-200 rounded-lg" />
                    </div>
                </div>
            </div>

            <!-- STATS CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border p-4 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Receita Total</div>
                    <div class="text-2xl font-bold text-[#547558] mt-2">R$ {{ totalEarnings.toLocaleString('pt-BR', { maximumFractionDigits: 0 }) }}</div>
                </div>
                <div class="bg-white rounded-2xl border p-4 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Total Agendamentos</div>
                    <div class="text-2xl font-bold text-blue-600 mt-2">{{ totalAppointments }}</div>
                </div>
                <div class="bg-white rounded-2xl border p-4 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Concluídos</div>
                    <div class="text-2xl font-bold text-green-600 mt-2">{{ overview.totals.completed }}</div>
                </div>
                <div class="bg-white rounded-2xl border p-4 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Pendentes</div>
                    <div class="text-2xl font-bold text-amber-600 mt-2">{{ overview.totals.pending }}</div>
                </div>
            </div>

            <!-- MAIN CHARTS -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Earnings Chart -->
                <div class="bg-white rounded-2xl border p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Evolução de Receita</h3>
                    <Chart type="line" :data="chartDataEarnings" :options="chartOptions" style="height: 320px" />
                </div>

                <!-- Services Chart -->
                <div class="bg-white rounded-2xl border p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Serviços Mais Realizados</h3>
                    <Chart v-if="topServices.length" type="bar" :data="chartDataServices" :options="chartOptionsServices" style="height: 320px" />
                    <div v-else class="flex items-center justify-center h-80 text-gray-400">Sem dados</div>
                </div>
            </div>

            <!-- BOTTOM SECTION -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Status Summary -->
                <div class="lg:col-span-2 bg-white rounded-2xl border p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Status dos Agendamentos</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-amber-600">{{ overview.totals.pending }}</div>
                            <div class="text-xs font-semibold text-gray-500 mt-1">Pendentes</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ overview.totals.confirmed }}</div>
                            <div class="text-xs font-semibold text-gray-500 mt-1">Confirmados</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">{{ overview.totals.completed }}</div>
                            <div class="text-xs font-semibold text-gray-500 mt-1">Concluídos</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-red-600">{{ overview.totals.canceled }}</div>
                            <div class="text-xs font-semibold text-gray-500 mt-1">Cancelados</div>
                        </div>
                    </div>
                </div>

                <!-- Agenda -->
                <div class="bg-white rounded-2xl border p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Próximos Dias</h3>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        <div v-for="d in overview.byDay.slice(0, 10)" :key="d.day" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <div class="font-semibold text-gray-900">{{ formatDate(d.day) }}</div>
                            </div>
                            <div class="text-sm font-bold text-[#547558] bg-[#547558]/10 px-3 py-1 rounded-full">{{ d.total }}</div>
                        </div>
                        <div v-if="!overview.byDay.length" class="text-gray-400 text-sm text-center py-6">Sem agendamentos</div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
