<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import MultiSelect from 'primevue/multiselect';

const props = defineProps({
    appointments: {
        type: Object,
        required: true,
    },
    services: {
        type: Array,
        default: () => [],
    },
    availabilitiesByDate: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    allClients: {
        type: Array,
        default: () => [],
    },
});

const defaultStatusFilter = ['pending', 'confirmed'];

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || defaultStatusFilter);
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const rowsPerPage = ref(Number(props.filters.per_page || props.appointments.per_page || 10));
const sortField = ref(props.filters.sort_field || 'date');
const sortOrder = ref(Number(props.filters.sort_order || 1));

const statusOptions = [
    { label: 'Selecionar todos', value: 'all' },
    { label: 'Pendente', value: 'pending' },
    { label: 'Confirmado', value: 'confirmed' },
    { label: 'Cancelado', value: 'canceled' },
    { label: 'Finalizado', value: 'completed' },
];

const rowsOptions = [10, 20, 50];
const allStatusValues = ['pending', 'confirmed', 'canceled', 'completed'];

// Formatação para exibição de datas
const formatDateDisplay = (dateStr) => {
    if (!dateStr) return '';
    const cleanDate = String(dateStr).substring(0, 10);
    const [year, month, day] = cleanDate.split('-');
    return `${day}/${month}/${year}`;
};

const tableRows = computed(() => props.appointments.data || []);
const firstRecord = computed(() => (props.appointments.current_page - 1) * props.appointments.per_page);

const statusMeta = (status) => {
    const map = {
        pending: { label: 'Pendente', severity: 'warn' },
        confirmed: { label: 'Confirmado', severity: 'success' },
        canceled: { label: 'Cancelado', severity: 'danger' },
        completed: { label: 'Finalizado', severity: 'info' },
    };

    return map[status] || { label: status, severity: 'secondary' };
};

const formatDate = (dateIso) => {
    if (!dateIso) return '-';
    return String(dateIso).substring(0, 10).split('-').reverse().join('/');
};

const formatTime = (time) => (time ? String(time).substring(0, 5) : '-');

const formatPhone = (phone) => {
    if (!phone) return '';
    const cleaned = String(phone).replace(/\D/g, '');
    if (cleaned.length === 11) {
        return `(${cleaned.slice(0, 2)}) ${cleaned.slice(2, 7)}-${cleaned.slice(7)}`;
    }
    return phone;
};

const formatPrice = (value) => {
    const price = Number(value || 0);
    return price.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

const openWhatsApp = (phone, name) => {
    if (!phone) return;
    const cleanPhone = String(phone).replace(/\D/g, '');
    const text = encodeURIComponent(`Ola, ${name}! Estou falando sobre seu agendamento no Cabeleila.`);
    window.open(`https://wa.me/55${cleanPhone}?text=${text}`, '_blank');
};

let searchTimeout = null;
const buildFilterParams = (page = props.appointments.current_page) => ({
    search: search.value || undefined,
    status: statusFilter.value && statusFilter.value.length > 0 ? statusFilter.value : undefined,
    start_date: startDate.value || undefined,
    end_date: endDate.value || undefined,
    per_page: rowsPerPage.value,
    page,
    sort_field: sortField.value,
    sort_order: sortOrder.value,
});

const applyFilters = (resetPage = false) => {
    router.get(route('admin.appointments.index'), buildFilterParams(resetPage ? 1 : props.appointments.current_page), {
        preserveState: true,
        preserveScroll: true,
        replace: false,
    });
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(true), 400);
});

watch(statusFilter, () => applyFilters(true));
watch(startDate, () => applyFilters(true));
watch(endDate, () => applyFilters(true));

const selectAllStatuses = () => {
    statusFilter.value = [...allStatusValues];
};

const statusFilterDisplay = computed(() => {
    if (statusFilter.value.length === 0) return 'Status';
    if (statusFilter.value.length === 4) return 'Todos os status';
    if (statusFilter.value.length === 1) return `${statusOptions.find((o) => o.value === statusFilter.value[0])?.label}`;
    return `${statusFilter.value.length} status`;
});

const handleStatusChange = (event) => {
    const selectedValues = event.value || [];

    if (selectedValues.includes('all')) {
        selectAllStatuses();
        return;
    }

    statusFilter.value = selectedValues.filter((value) => value !== 'all');
};

const onSort = (event) => {
    sortField.value = event.sortField || 'date';
    sortOrder.value = event.sortOrder || 1;
    applyFilters(true);
};

const onPage = (event) => {
    rowsPerPage.value = event.rows;
    router.get(route('admin.appointments.index'), buildFilterParams(event.page + 1), {
        preserveState: true,
        preserveScroll: true,
        replace: false,
    });
};

const statusForm = useForm({ status: '' });

const updateStatus = (appointment, status) => {
    appointmentToUpdateStatus.value = appointment;
    statusToConfirm.value = status;
    statusModalVisible.value = true;
};

const confirmStatusUpdate = () => {
    if (!appointmentToUpdateStatus.value || !statusToConfirm.value) return;

    statusForm
        .transform(() => ({ status: statusToConfirm.value }))
        .patch(route('admin.appointments.status', appointmentToUpdateStatus.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                statusModalVisible.value = false;
                appointmentToUpdateStatus.value = null;
                statusToConfirm.value = null;
            },
        });
};

const canConfirm = (appointment) => appointment.status === 'pending';
const canCancel = (appointment) => ['pending', 'confirmed'].includes(appointment.status);
const canFinalize = (appointment) => appointment.status === 'confirmed';

// ==========================================
// MODAL CRIAR NOVO AGENDAMENTO
// ==========================================
const isCreateModalOpen = ref(false);
const showClientSuggestions = ref(false);

const createForm = useForm({
    client_name: '',
    client_phone: '',
    date: '',
    time: '',
    services: [],
});

const filteredClients = computed(() => {
    if (createForm.client_name.length < 2) return [];
    return props.allClients
        .filter((client) => client.full_name.toLowerCase().includes(createForm.client_name.toLowerCase()) || client.phone.includes(createForm.client_name.replace(/\D/g, '')))
        .slice(0, 5);
});

const selectClient = (client) => {
    createForm.client_name = client.full_name;
    let val = client.phone;
    if (val.length === 11) {
        createForm.client_phone = `(${val.slice(0, 2)}) ${val.slice(2, 7)}-${val.slice(7)}`;
    } else {
        createForm.client_phone = val;
    }
    showClientSuggestions.value = false;
};

const closeClientSuggestions = () => {
    setTimeout(() => {
        showClientSuggestions.value = false;
    }, 200);
};

const availableTimes = computed(() => {
    if (!createForm.date) return [];
    const slots = props.availabilitiesByDate[createForm.date] || [];
    return slots.map((slot) => (typeof slot === 'string' ? slot : slot.hour || slot));
});

const handleDateSelection = () => {
    createForm.time = '';
};

const applyPhoneMask = (event) => {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    if (value.length > 2) value = `(${value.slice(0, 2)}) ${value.slice(2)}`;
    if (value.length > 9) value = `${value.slice(0, 10)}-${value.slice(10)}`;
    createForm.client_phone = value;
};

const toggleCreateService = (service) => {
    const index = createForm.services.findIndex((s) => s.id === service.id);
    if (index === -1) createForm.services.push(service);
    else createForm.services.splice(index, 1);
};

const submitCreate = () => {
    const phone = createForm.client_phone.replace(/\D/g, '');
    const date = formatDateDisplay(createForm.date);
    const time = createForm.time.substring(0, 5);
    const services = createForm.services.map((s) => s.name).join(', ');

    const waText = `🎉 *Novo Agendamento!*\n\nOlá, ${createForm.client_name}! Um horário foi agendado para você no salão:\n\n📅 *Data:* ${date}\n⏰ *Hora:* ${time}\n✂️ *Serviços:* ${services}\n\nPara gerenciar seus horários, acesse nosso site.`;

    createForm
        .transform((data) => ({
            ...data,
            client_phone: phone,
        }))
        .post(route('admin.appointments.store'), {
            onSuccess: () => {
                isCreateModalOpen.value = false;
                createForm.reset();
                applyFilters();
                window.open(`https://wa.me/55${phone}?text=${encodeURIComponent(waText)}`, '_blank');
            },
        });
};

// ==========================================
// MODAL EDITAR AGENDAMENTO
// ==========================================
const editModalVisible = ref(false);
const selectedAppointment = ref(null);

const editForm = useForm({
    date: '',
    time: '',
    services: [],
});

const toggleEditService = (service) => {
    const index = editForm.services.findIndex((s) => s.id === service.id);
    if (index === -1) editForm.services.push(service);
    else editForm.services.splice(index, 1);
};

// ==========================================
// MODAL CONFIRMAR STATUS
// ==========================================
const statusModalVisible = ref(false);
const statusToConfirm = ref(null);
const appointmentToUpdateStatus = ref(null);

const dateOptions = computed(() => {
    const options = Object.keys(props.availabilitiesByDate)
        .sort((a, b) => new Date(a) - new Date(b))
        .map((date) => ({
            value: date,
            label: formatDate(date),
        }));

    const selectedDate = String(selectedAppointment.value?.availability?.date || '').substring(0, 10);

    if (selectedDate && !options.some((option) => option.value === selectedDate)) {
        options.unshift({
            value: selectedDate,
            label: `${formatDate(selectedDate)} (atual)`,
        });
    }

    return options;
});

const currentDateOption = computed(() => {
    if (!selectedAppointment.value) return null;

    const appointmentDate = String(selectedAppointment.value.availability?.date || '').substring(0, 10);
    if (!appointmentDate) return null;

    return {
        date: appointmentDate,
        label: `${formatDate(appointmentDate)} (atual)`,
    };
});

const currentTimeOption = computed(() => {
    if (!selectedAppointment.value) return null;
    const appointmentDate = String(selectedAppointment.value.availability?.date || '').substring(0, 10);
    const appointmentTime = formatTime(selectedAppointment.value.availability?.hour);

    if (appointmentDate === editForm.date && appointmentTime) {
        return { hour: appointmentTime, isCurrent: true };
    }

    return null;
});

const timeOptions = computed(() => {
    const slots = props.availabilitiesByDate?.[editForm.date] || [];
    const mapped = slots.map((slot) => ({ hour: slot.hour, isCurrent: false }));

    if (currentTimeOption.value && !mapped.some((slot) => slot.hour === currentTimeOption.value.hour)) {
        mapped.unshift(currentTimeOption.value);
    }

    return mapped;
});

watch(
    () => editForm.date,
    () => {
        if (!timeOptions.value.some((slot) => slot.hour === editForm.time)) {
            editForm.time = '';
        }
    },
);

const openEditModal = (appointment) => {
    selectedAppointment.value = appointment;
    editForm.date = String(appointment.availability?.date || '').substring(0, 10);
    editForm.time = formatTime(appointment.availability?.hour);
    editForm.services = appointment.services || [];
    editModalVisible.value = true;
};

const submitEdit = () => {
    if (!selectedAppointment.value) return;

    const initialDate = String(selectedAppointment.value.availability?.date || '').substring(0, 10);
    const initialTime = formatTime(selectedAppointment.value.availability?.hour);
    const initialServices = (selectedAppointment.value.services || [])
        .map((service) => service.id)
        .sort()
        .join(',');
    const updatedServices = editForm.services
        .map((service) => service.id)
        .sort()
        .join(',');
    const hasChanges = initialDate !== editForm.date || initialTime !== editForm.time || initialServices !== updatedServices;

    editForm
        .transform((data) => ({
            ...data,
            services: data.services.map((service) => ({ id: service.id })),
        }))
        .put(route('admin.appointments.update', selectedAppointment.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                editModalVisible.value = false;
                selectedAppointment.value = null;
                if (hasChanges) {
                    applyFilters();
                }
            },
        });
};
</script>

<template>
    <Head title="Agenda" />

    <AuthenticatedLayout>
        <section class="flex min-h-[calc(100vh-9rem)] flex-col gap-5 p-">
            <!-- HEADER COM TÍTULO E FILTROS -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- TÍTULO E DESCRIÇÃO -->
                <div class="flex-shrink-0 px-6">
                    <h2 class="font-bold text-2xl text-gray-900 tracking-tight">Agenda</h2>
                    <p class="text-sm text-gray-500 mt-1">Gerencie os agendamentos do salão.</p>
                </div>

                <!-- FILTROS -->
                <div class="bg-white/90 border border-gray-100 rounded-3xl p-4 sm:p-5 shadow-sm lg:flex-1">
                    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 items-center">
                        <InputText v-model="search" placeholder="Buscar por nome ou telefone" class="flex-1 min-w-0 sm:flex-1 w-full" aria-label="Buscar por nome ou telefone" />

                        <div class="relative w-full sm:w-36">
                            <div
                                class="w-full rounded-xl bg-white shadow-sm px-3 py-2.5 flex items-center justify-between text-sm font-bold text-gray-700 border border-gray-100 whitespace-nowrap">
                                <span>{{ formatDateDisplay(startDate) || 'De' }}</span>
                                <svg class="w-4 h-4 text-gray-500 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <input v-model="startDate" type="date" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" aria-label="Data inicial" />
                        </div>

                        <div class="relative w-full sm:w-36">
                            <div
                                class="w-full rounded-xl bg-white shadow-sm px-3 py-2.5 flex items-center justify-between text-sm font-bold text-gray-700 border border-gray-100 whitespace-nowrap">
                                <span>{{ formatDateDisplay(endDate) || 'Até' }}</span>
                                <svg class="w-4 h-4 text-gray-500 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <input v-model="endDate" type="date" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" aria-label="Data final" />
                        </div>

                        <div class="w-full sm:w-44">
                            <MultiSelect
                                v-model="statusFilter"
                                :options="statusOptions"
                                option-label="label"
                                option-value="value"
                                :placeholder="statusFilterDisplay"
                                class="w-full"
                                :max-selected-labels="0"
                                display="chip"
                                selected-items-label="{0} selecionados"
                                @change="handleStatusChange" />
                        </div>

                        <div class="flex items-end">
                            <button
                                @click="isCreateModalOpen = true"
                                class="w-full sm:w-auto bg-[#547558] text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-md hover:bg-[#435e46] transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Novo
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white/90 border border-gray-100 rounded-3xl shadow-sm overflow-hidden flex flex-col flex-1 p-6">
                <DataTable
                    :value="tableRows"
                    data-key="id"
                    paginator
                    lazy
                    scrollable
                    scroll-height="flex"
                    :rows="props.appointments.per_page"
                    :total-records="props.appointments.total"
                    :first="firstRecord"
                    :rows-per-page-options="rowsOptions"
                    paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                    current-page-report-template="{first} a {last} de {totalRecords}"
                    responsive-layout="scroll"
                    sort-mode="single"
                    removable-sort
                    :sort-field="sortField"
                    :sort-order="sortOrder"
                    class="appointments-table p-datatable-sm flex-1"
                    table-style="min-width: 100%"
                    @page="onPage"
                    @sort="onSort">
                    <template #empty>
                        <div class="py-12 text-center text-gray-500">Nenhum agendamento encontrado.</div>
                    </template>

                    <Column header="Data &amp; Hora" sortable sortField="date" style="min-width: 9rem">
                        <template #body="slotProps">
                            <div class="flex flex-col">
                                <span class="font-semibold text-gray-900">{{ formatDate(slotProps.data.availability?.date) }}</span>
                                <span class="text-xs text-gray-500">{{ formatTime(slotProps.data.availability?.hour) }}</span>
                            </div>
                        </template>
                    </Column>

                    <Column header="Cliente" sortable sortField="client_name" style="min-width: 14rem">
                        <template #body="slotProps">
                            <div>
                                <p class="font-semibold text-gray-900">{{ slotProps.data.client?.full_name || 'Cliente' }}</p>
                                <button
                                    type="button"
                                    class="text-xs text-[#547558] hover:underline"
                                    @click="openWhatsApp(slotProps.data.client?.phone, slotProps.data.client?.full_name)">
                                    {{ formatPhone(slotProps.data.client?.phone) || 'Sem telefone' }}
                                </button>
                            </div>
                        </template>
                    </Column>

                    <Column header="Servicos &amp; Total" sortable sortField="total_price" style="min-width: 13rem">
                        <template #body="slotProps">
                            <div class="flex flex-col gap-1">
                                <p class="text-sm text-gray-700">
                                    {{ slotProps.data.services?.map((service) => service.name).join(', ') || 'Sem servicos' }}
                                </p>
                                <span class="font-semibold text-gray-800 text-sm">{{ formatPrice(slotProps.data.total_price) }}</span>
                            </div>
                        </template>
                    </Column>

                    <Column header="Status" sortable sortField="status" style="min-width: 9rem">
                        <template #body="slotProps">
                            <Tag :value="statusMeta(slotProps.data.status).label" :severity="statusMeta(slotProps.data.status).severity" rounded />
                        </template>
                    </Column>

                    <Column header="Ações" style="min-width: 20rem">
                        <template #body="slotProps">
                            <div class="flex flex-wrap gap-2">
                                <Button label="Editar" size="small" icon="pi pi-pencil" outlined @click="openEditModal(slotProps.data)" />

                                <Button
                                    v-if="canConfirm(slotProps.data)"
                                    label="Confirmar"
                                    size="small"
                                    icon="pi pi-check"
                                    severity="success"
                                    outlined
                                    :loading="statusForm.processing"
                                    @click="updateStatus(slotProps.data, 'confirmed')" />

                                <Button
                                    v-if="canCancel(slotProps.data)"
                                    label="Cancelar"
                                    size="small"
                                    icon="pi pi-ban"
                                    severity="danger"
                                    outlined
                                    :loading="statusForm.processing"
                                    @click="updateStatus(slotProps.data, 'canceled')" />

                                <Button
                                    v-if="canFinalize(slotProps.data)"
                                    label="Finalizar"
                                    size="small"
                                    icon="pi pi-verified"
                                    severity="info"
                                    outlined
                                    :loading="statusForm.processing"
                                    @click="updateStatus(slotProps.data, 'completed')" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </section>

        <!-- MODAL CRIAR NOVO AGENDAMENTO -->
        <Dialog v-model:visible="isCreateModalOpen" modal header="Novo Agendamento" :style="{ width: '38rem' }">
            <form class="space-y-4" @submit.prevent="submitCreate">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="relative">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Cliente</label>
                        <InputText
                            v-model="createForm.client_name"
                            @focus="showClientSuggestions = true"
                            @blur="closeClientSuggestions"
                            placeholder="Comece a digitar..."
                            class="w-full" />
                        <div
                            v-if="showClientSuggestions && filteredClients.length > 0"
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-100 shadow-xl rounded-xl overflow-hidden">
                            <button
                                v-for="client in filteredClients"
                                :key="client.id"
                                type="button"
                                @mousedown.prevent="selectClient(client)"
                                class="w-full text-left px-4 py-3 hover:bg-gray-50 flex flex-col border-b border-gray-50 last:border-0">
                                <span class="font-bold text-sm text-gray-900">{{ client.full_name }}</span>
                                <span class="text-xs text-gray-500">{{ formatPhone(client.phone) }}</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">WhatsApp</label>
                        <InputText v-model="createForm.client_phone" @input="applyPhoneMask" type="tel" placeholder="(11) 90000-0000" class="w-full" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#547558] uppercase tracking-wider mb-2">Data</label>
                        <Dropdown
                            v-model="createForm.date"
                            :options="
                                Object.keys(props.availabilitiesByDate)
                                    .sort((a, b) => new Date(a) - new Date(b))
                                    .map((d) => ({ value: d, label: formatDateDisplay(d) }))
                            "
                            option-label="label"
                            option-value="value"
                            placeholder="Selecione um dia"
                            @change="handleDateSelection"
                            class="w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#547558] uppercase tracking-wider mb-2">Hora</label>
                        <Dropdown v-model="createForm.time" :options="availableTimes" placeholder="Selecione a hora" :disabled="!createForm.date" class="w-full" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Serviços</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[160px] overflow-y-auto pr-2">
                        <label
                            v-for="service in props.services"
                            :key="service.id"
                            class="flex items-center p-3 rounded-xl border cursor-pointer transition-all"
                            :class="createForm.services.find((s) => s.id === service.id) ? 'border-[#547558] bg-[#547558]/5' : 'border-gray-200 hover:border-[#547558]/50'">
                            <input type="checkbox" :value="service" @change="toggleCreateService(service)" class="hidden" />
                            <div
                                class="w-4 h-4 rounded border mr-3 flex items-center justify-center"
                                :class="createForm.services.find((s) => s.id === service.id) ? 'bg-[#547558] border-[#547558]' : 'border-gray-300 bg-white'">
                                <svg v-if="createForm.services.find((s) => s.id === service.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M5 13l4 4L19 7" stroke-width="3" />
                                </svg>
                            </div>
                            <span class="text-sm font-bold text-gray-900">{{ service.name }}</span>
                        </label>
                    </div>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <Button type="button" label="Cancelar" text @click="isCreateModalOpen = false" />
                    <Button
                        type="submit"
                        label="Confirmar"
                        icon="pi pi-save"
                        :disabled="createForm.processing || createForm.services.length === 0 || !createForm.date || !createForm.time"
                        :loading="createForm.processing" />
                </div>
            </form>
        </Dialog>

        <!-- MODAL CONFIRMAR STATUS -->
        <Dialog v-model:visible="statusModalVisible" modal header="Confirmar alteração" :style="{ width: '28rem' }">
            <div class="space-y-4">
                <p class="text-gray-600">
                    Deseja marcar este agendamento como <strong>{{ statusMeta(statusToConfirm).label.toLowerCase() }}</strong
                    >?
                </p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <p class="text-sm text-gray-700"><span class="font-semibold">Cliente:</span> {{ appointmentToUpdateStatus?.client?.full_name }}</p>
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold">Data/Hora:</span> {{ formatDate(appointmentToUpdateStatus?.availability?.date) }} às
                        {{ formatTime(appointmentToUpdateStatus?.availability?.hour) }}
                    </p>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <Button type="button" label="Cancelar" text @click="statusModalVisible = false" />
                    <Button type="button" label="Confirmar" icon="pi pi-check" :loading="statusForm.processing" @click="confirmStatusUpdate" />
                </div>
            </div>
        </Dialog>

        <!-- MODAL EDITAR AGENDAMENTO -->
        <Dialog v-model:visible="editModalVisible" modal header="Editar agendamento" :style="{ width: '38rem' }">
            <form class="space-y-4" @submit.prevent="submitEdit">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Data</label>
                        <Dropdown v-model="editForm.date" :options="dateOptions" option-label="label" option-value="value" placeholder="Selecione uma data" class="w-full" />
                        <p v-if="currentDateOption" class="text-xs text-gray-500 mt-1">Atual: {{ currentDateOption.label }}</p>
                        <p v-if="editForm.errors.date" class="text-xs text-red-500 mt-1">{{ editForm.errors.date }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Horario</label>
                        <Dropdown v-model="editForm.time" :options="timeOptions" option-label="hour" option-value="hour" placeholder="Selecione um horario" class="w-full">
                            <template #option="slotProps">
                                <span>
                                    {{ slotProps.option.hour }}
                                    <span v-if="slotProps.option.isCurrent" class="text-xs text-gray-500">(atual)</span>
                                </span>
                            </template>
                        </Dropdown>
                        <p v-if="editForm.errors.time" class="text-xs text-red-500 mt-1">{{ editForm.errors.time }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Serviços</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[160px] overflow-y-auto pr-2">
                        <label
                            v-for="service in props.services"
                            :key="service.id"
                            class="flex items-center p-3 rounded-xl border cursor-pointer transition-all"
                            :class="editForm.services.find((s) => s.id === service.id) ? 'border-[#547558] bg-[#547558]/5' : 'border-gray-200 hover:border-[#547558]/50'">
                            <input type="checkbox" :value="service" @change="toggleEditService(service)" class="hidden" />
                            <div
                                class="w-4 h-4 rounded border mr-3 flex items-center justify-center"
                                :class="editForm.services.find((s) => s.id === service.id) ? 'bg-[#547558] border-[#547558]' : 'border-gray-300 bg-white'">
                                <svg v-if="editForm.services.find((s) => s.id === service.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M5 13l4 4L19 7" stroke-width="3" />
                                </svg>
                            </div>
                            <span class="text-sm font-bold text-gray-900">{{ service.name }}</span>
                        </label>
                    </div>
                    <p v-if="editForm.errors.services" class="text-xs text-red-500 mt-1">{{ editForm.errors.services }}</p>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <Button type="button" label="Fechar" text @click="editModalVisible = false" />
                    <Button type="submit" label="Salvar" icon="pi pi-save" :loading="editForm.processing" />
                </div>
            </form>
        </Dialog>
    </AuthenticatedLayout>
</template>

<style scoped>
:deep(.appointments-table .p-datatable-wrapper) {
    border-bottom: 1px solid rgb(243 244 246);
}

:deep(.appointments-table .p-datatable-thead > tr > th) {
    white-space: nowrap;
}

:deep(.appointments-table .p-paginator) {
    border-top: 0;
}

:deep(.p-datatable-paginator-bottom) {
    border: none !important;
}
</style>
