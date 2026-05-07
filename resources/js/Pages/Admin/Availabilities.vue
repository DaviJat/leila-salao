<template>
    <Head title="Horários" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- HEADER COM TÍTULO E FILTROS -->
            <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                <!-- TÍTULO E DESCRIÇÃO -->
                <div class="flex-shrink-0 px-6">
                    <h2 class="font-bold text-2xl text-gray-900 tracking-tight">Horários Disponíveis</h2>
                    <p class="text-sm text-gray-500 mt-1">Gerencie os horários de disponibilidade para agendamentos</p>
                </div>

                <!-- FILTROS -->
                <div class="bg-white/90 border border-gray-100 rounded-3xl p-4 sm:p-5 shadow-sm lg:flex-1">
                    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 items-center">
                        <!-- Status Filter -->
                        <MultiSelect
                            v-model="statusFilter"
                            :options="statusOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="statusFilterDisplay"
                            class="flex-1 min-w-0 !border !border-gray-300 !rounded-xl"
                            @change="applyFilters(true)"
                            selected-items-label="{0} selecionados" />

                        <!-- Date Range Filters -->
                        <InputText v-model="startDate" type="date" class="w-full sm:w-auto !border !border-gray-300 !px-4 !py-2 !rounded-xl" @change="applyFilters(true)" />

                        <InputText v-model="endDate" type="date" class="w-full sm:w-auto !border !border-gray-300 !px-4 !py-2 !rounded-xl" @change="applyFilters(true)" />

                        <!-- Pattern Button -->
                        <Button label="Padrão" icon="pi pi-calendar-plus" severity="secondary" outlined @click="openPatternModal" class="w-full sm:w-auto" />

                        <!-- Add Button -->
                        <div class="flex items-end">
                            <button
                                @click="openCreateModal"
                                class="w-full sm:w-auto bg-[#547558] text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-md hover:bg-[#435e46] transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Adicionar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Horários -->
            <div class="bg-white/90 border border-gray-100 rounded-3xl shadow-sm overflow-hidden flex flex-col flex-1 p-6">
                <DataTable
                    :value="tableRows"
                    lazy
                    paginator
                    :first="firstRecord"
                    :rows="rowsPerPage"
                    :total-records="availabilities.total"
                    :loading="loading"
                    @page="onPage"
                    :rows-per-page-options="rowsOptions"
                    scroll-height="flex"
                    class="w-full"
                    sort-mode="single"
                    removable-sort
                    :sort-field="sortField"
                    :sort-order="sortOrder"
                    :pt="{
                        table: { class: 'border-collapse' },
                        header: { class: 'bg-gray-50 border-b border-gray-200' },
                        headerCell: { class: 'px-6 py-4 text-left font-semibold text-gray-900' },
                        bodyRow: { class: 'border-b border-gray-100 hover:bg-gray-50 transition-colors' },
                        bodyCell: { class: 'px-6 py-4' },
                    }"
                    @sort="onSort">
                    <!-- Data -->
                    <Column field="date" header="Data" sortable @sort="onSort" style="width: 25%">
                        <template #body="{ data }">
                            <span class="font-semibold text-gray-900">{{ formatDateDisplay(data.date) }}</span>
                        </template>
                    </Column>

                    <!-- Horário -->
                    <Column field="hour" header="Horário" sortable @sort="onSort" style="width: 20%">
                        <template #body="{ data }">
                            <span class="font-semibold text-gray-900">{{ formatTimeDisplay(data.hour) }}</span>
                        </template>
                    </Column>

                    <!-- Status -->
                    <Column field="is_available" header="Status" sortable @sort="onSort" style="width: 25%">
                        <template #body="{ data }">
                            <Tag :value="data.is_available ? 'Disponível' : 'Indisponível'" :severity="data.is_available ? 'success' : 'warning'" class="!rounded-full" />
                        </template>
                    </Column>

                    <!-- Toggle Status -->
                    <Column header="Mudar Status" style="width: 15%; text-align: center">
                        <template #body="{ data }">
                            <Button
                                :icon="data.is_available ? 'pi pi-check' : 'pi pi-times'"
                                :severity="data.is_available ? 'success' : 'danger'"
                                text
                                @click="toggleStatus(data.id, !data.is_available)" />
                        </template>
                    </Column>

                    <!-- Ações -->
                    <Column header="Ações" style="width: 15%; text-align: center">
                        <template #body="{ data }">
                            <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDelete(data.id)" class="p-button-sm" />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <!-- Modal de Adicionar Horários -->
        <Dialog v-model:visible="showModal" modal header="Adicionar Horários" :closable="true" class="w-full sm:w-96" @hide="resetForm">
            <form @submit.prevent="submitAvailabilities" class="space-y-4">
                <!-- Data -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Data*</label>
                    <InputText v-model="form.date" type="date" class="w-full !border !border-gray-300 !px-4 !py-2 !rounded-xl" :class="{ '!border-red-500': form.errors.date }" />
                    <small v-if="form.errors.date" class="text-red-500">{{ form.errors.date }}</small>
                </div>

                <!-- Horários -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Horários*</label>
                    <div class="space-y-2">
                        <div v-for="(hour, index) in form.hours" :key="index" class="flex gap-2 items-end">
                            <InputText
                                v-model="form.hours[index]"
                                type="time"
                                class="flex-1 !border !border-gray-300 !px-4 !py-2 !rounded-xl"
                                :class="{ '!border-red-500': form.errors['hours.' + index] }" />
                            <Button type="button" icon="pi pi-trash" severity="danger" text @click="form.hours.splice(index, 1)" />
                        </div>
                        <Button type="button" label="+ Adicionar horário" severity="info" text @click="form.hours.push('')" class="w-full" />
                    </div>
                    <small v-if="form.errors.hours" class="text-red-500">{{ form.errors.hours }}</small>
                </div>

                <!-- Botões -->
                <div class="flex gap-3 justify-end pt-4">
                    <Button label="Cancelar" severity="secondary" @click="showModal = false" class="px-6 py-2 rounded-full" />
                    <Button label="Adicionar" :loading="form.processing" @click="submitAvailabilities" class="!bg-primary !text-white px-6 py-2 rounded-full" />
                </div>
            </form>
        </Dialog>

        <!-- Modal do Padrão Mensal -->
        <Dialog v-model:visible="showPatternModal" modal header="Padrão do mês" :closable="true" class="w-full sm:w-[34rem]" @hide="resetPatternForm">
            <form @submit.prevent="submitPattern" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Dias da semana*</label>
                    <MultiSelect
                        v-model="patternForm.weekdays"
                        :options="weekdayOptions"
                        option-label="label"
                        option-value="value"
                        placeholder="Selecione os dias"
                        class="w-full !border !border-gray-300 !rounded-xl"
                        selected-items-label="{0} selecionados" />
                    <small v-if="patternForm.errors.weekdays" class="text-red-500">{{ patternForm.errors.weekdays }}</small>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Horários do padrão*</label>
                    <div class="space-y-2">
                        <div v-for="(hour, index) in patternForm.hours" :key="index" class="flex gap-2 items-end">
                            <InputText v-model="patternForm.hours[index]" type="time" class="flex-1 !border !border-gray-300 !px-4 !py-2 !rounded-xl" />
                            <Button type="button" icon="pi pi-trash" severity="danger" text @click="patternForm.hours.splice(index, 1)" />
                        </div>
                        <Button type="button" label="+ Adicionar horário" severity="info" text @click="patternForm.hours.push('')" class="w-full" />
                    </div>
                    <small v-if="patternForm.errors.hours" class="text-red-500">{{ patternForm.errors.hours }}</small>
                </div>

                <p class="text-sm text-gray-500">O sistema vai preencher os dias restantes do mês atual com os horários selecionados, ignorando duplicados.</p>

                <div class="flex gap-3 justify-end pt-4">
                    <Button label="Cancelar" severity="secondary" @click="showPatternModal = false" class="px-6 py-2 rounded-full" />
                    <Button label="Gerar padrão" :loading="patternForm.processing" @click="submitPattern" class="!bg-primary !text-white px-6 py-2 rounded-full" />
                </div>
            </form>
        </Dialog>

        <!-- Confirmação de Deleção -->
        <ConfirmDialog group="deleteAvailability" />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { useConfirm } from 'primevue/useconfirm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import ConfirmDialog from 'primevue/confirmdialog';
import Tag from 'primevue/tag';
import MultiSelect from 'primevue/multiselect';

const confirm = useConfirm();

const props = defineProps({
    availabilities: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const statusFilter = ref(props.filters.status || []);
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const showModal = ref(false);
const showPatternModal = ref(false);
const loading = ref(false);
const rowsPerPage = ref(Number(props.filters.per_page || props.availabilities.per_page || 10));
const sortField = ref(props.filters.sort_field || 'date');
const sortOrder = ref(Number(props.filters.sort_order || 1));
const rowsOptions = [10, 20, 50];

const tableRows = computed(() => props.availabilities.data || []);
const firstRecord = computed(() => (props.availabilities.current_page - 1) * props.availabilities.per_page);

const statusOptions = [
    { label: 'Disponível', value: 'available' },
    { label: 'Indisponível', value: 'unavailable' },
];

const weekdayOptions = [
    { label: 'Domingo', value: 0 },
    { label: 'Segunda', value: 1 },
    { label: 'Terça', value: 2 },
    { label: 'Quarta', value: 3 },
    { label: 'Quinta', value: 4 },
    { label: 'Sexta', value: 5 },
    { label: 'Sábado', value: 6 },
];

const statusFilterDisplay = computed(() => {
    if (statusFilter.value.length === 0) return 'Todos os status';
    if (statusFilter.value.length === 1) {
        const label = statusOptions.find((o) => o.value === statusFilter.value[0])?.label;
        return label || 'Status';
    }
    return `${statusFilter.value.length} status`;
});

const form = useForm({
    date: '',
    hours: [''],
});

const patternForm = useForm({
    weekdays: [1, 2, 3, 4, 5],
    hours: ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00'],
});

const applyFilters = (resetPage = false) => {
    router.get(
        route('admin.availabilities.index'),
        {
            status: statusFilter.value,
            start_date: startDate.value || undefined,
            end_date: endDate.value || undefined,
            per_page: rowsPerPage.value,
            page: resetPage ? 1 : props.availabilities.current_page,
            sort_field: sortField.value,
            sort_order: sortOrder.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: false,
        },
    );
};

const onPage = (event) => {
    rowsPerPage.value = event.rows;
    router.get(
        route('admin.availabilities.index'),
        {
            status: statusFilter.value,
            start_date: startDate.value || undefined,
            end_date: endDate.value || undefined,
            per_page: event.rows,
            page: event.page + 1,
            sort_field: sortField.value,
            sort_order: sortOrder.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: false,
        },
    );
};

const onSort = (event) => {
    sortField.value = event.sortField || 'date';
    sortOrder.value = event.sortOrder || 1;
    applyFilters(true);
};

const openCreateModal = () => {
    resetForm();
    showModal.value = true;
};

const openPatternModal = () => {
    resetPatternForm();
    showPatternModal.value = true;
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.hours = [''];
};

const resetPatternForm = () => {
    patternForm.reset();
    patternForm.clearErrors();
    patternForm.weekdays = [1, 2, 3, 4, 5];
    patternForm.hours = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
};

const submitAvailabilities = () => {
    form.post(route('admin.availabilities.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            resetForm();
            applyFilters(true);
        },
    });
};

const submitPattern = () => {
    patternForm.post(route('admin.availabilities.pattern'), {
        preserveScroll: true,
        onSuccess: () => {
            showPatternModal.value = false;
            resetPatternForm();
            applyFilters(true);
        },
    });
};

const toggleStatus = (id, newStatus) => {
    router.patch(route('admin.availabilities.updateStatus', id), { is_available: newStatus }, { preserveScroll: true });
};

const confirmDelete = (id) => {
    confirm.require({
        group: 'deleteAvailability',
        message: 'Tem certeza que deseja deletar este horário?',
        header: 'Confirmar Deleção',
        icon: 'pi pi-exclamation-triangle',
        accept() {
            router.delete(route('admin.availabilities.destroy', id), {
                preserveScroll: true,
            });
        },
    });
};

const formatDateDisplay = (dateStr) => {
    if (!dateStr) return '-';
    if (dateStr instanceof Date) {
        return Number.isNaN(dateStr.getTime()) ? '-' : dateStr.toLocaleDateString('pt-BR', { year: 'numeric', month: '2-digit', day: '2-digit' });
    }

    const rawDate = String(dateStr).trim();
    const normalizedDate = rawDate.includes('T') || rawDate.includes(' ') ? rawDate : `${rawDate}T00:00:00`;
    const date = new Date(normalizedDate);

    if (!Number.isNaN(date.getTime())) {
        return date.toLocaleDateString('pt-BR', { year: 'numeric', month: '2-digit', day: '2-digit' });
    }

    const match = rawDate.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (match) {
        return `${match[3]}/${match[2]}/${match[1]}`;
    }

    return '-';
};

const formatTimeDisplay = (timeStr) => {
    if (!timeStr) return '-';
    // timeStr pode vir como "HH:mm" ou "HH:mm:ss"
    const time = typeof timeStr === 'string' ? timeStr : timeStr.slice(0, 5);
    return time;
};
</script>
