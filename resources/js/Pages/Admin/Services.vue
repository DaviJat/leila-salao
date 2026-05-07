<template>
    <Head title="Serviços" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- HEADER COM TÍTULO E FILTROS -->
            <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                <!-- TÍTULO E DESCRIÇÃO -->
                <div class="flex-shrink-0 px-6">
                    <h2 class="font-bold text-2xl text-gray-900 tracking-tight">Serviços</h2>
                    <p class="text-sm text-gray-500 mt-1">Gerencie todos os serviços oferecidos pelo salão</p>
                </div>

                <!-- FILTROS -->
                <div class="bg-white/90 border border-gray-100 rounded-3xl p-4 sm:p-5 shadow-sm lg:flex-1">
                    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 items-center">
                        <InputText v-model="search" placeholder="Buscar serviço..." class="flex-1 min-w-0 w-full !border !border-gray-300 !px-4 !py-2 !rounded-xl" />

                        <div class="flex items-end">
                            <button
                                @click="openCreateModal"
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

            <!-- Tabela de Serviços -->
            <div class="bg-white/90 border border-gray-100 rounded-3xl shadow-sm overflow-hidden flex flex-col flex-1 p-6">
                <DataTable
                    :value="tableRows"
                    lazy
                    paginator
                    :first="firstRecord"
                    :rows="rowsPerPage"
                    :total-records="services.total"
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
                    <!-- Nome -->
                    <Column field="name" header="Nome" sortable @sort="onSort" style="width: 25%">
                        <template #body="{ data }">
                            <span class="font-semibold text-gray-900">{{ data.name }}</span>
                        </template>
                    </Column>

                    <!-- Descrição -->
                    <Column field="description" header="Descrição" style="width: 35%">
                        <template #body="{ data }">
                            <span class="text-gray-600 text-sm line-clamp-2">{{ data.description || '-' }}</span>
                        </template>
                    </Column>

                    <!-- Preço -->
                    <Column field="price" header="Preço" sortable @sort="onSort" style="width: 15%">
                        <template #body="{ data }">
                            <span class="font-semibold text-gray-900">R$ {{ formatPrice(data.price) }}</span>
                        </template>
                    </Column>

                    <!-- Duração -->
                    <Column field="duration_minutes" header="Duração" sortable @sort="onSort" style="width: 15%">
                        <template #body="{ data }">
                            <span class="text-gray-600">{{ data.duration_minutes }}min</span>
                        </template>
                    </Column>

                    <!-- Ações -->
                    <Column header="Ações" style="width: 10%; text-align: center">
                        <template #body="{ data }">
                            <div class="flex items-center justify-center gap-2">
                                <Button icon="pi pi-pencil" outlined rounded severity="info" @click="openEditModal(data)" class="p-button-sm" />
                                <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDelete(data.id)" class="p-button-sm" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <!-- Modal de Criar/Editar -->
        <Dialog v-model:visible="showModal" modal :header="isEditing ? 'Editar Serviço' : 'Novo Serviço'" :closable="true" class="w-full sm:w-96" @hide="resetForm">
            <form @submit.prevent="submitService" class="space-y-4">
                <!-- Nome -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nome*</label>
                    <InputText
                        v-model="form.name"
                        placeholder="Digite o nome do serviço"
                        class="w-full !border !border-gray-300 !px-4 !py-2 !rounded-xl"
                        :class="{ '!border-red-500': form.errors.name }" />
                    <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                </div>

                <!-- Descrição -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Descrição</label>
                    <textarea
                        v-model="form.description"
                        placeholder="Digite a descrição do serviço"
                        rows="3"
                        class="w-full border border-gray-300 px-4 py-2 rounded-xl focus:outline-none focus:border-primary" />
                    <small v-if="form.errors.description" class="text-red-500">{{ form.errors.description }}</small>
                </div>

                <!-- Preço -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Preço (R$)*</label>
                    <InputText
                        v-model="form.price"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        class="w-full !border !border-gray-300 !px-4 !py-2 !rounded-xl"
                        :class="{ '!border-red-500': form.errors.price }" />
                    <small v-if="form.errors.price" class="text-red-500">{{ form.errors.price }}</small>
                </div>

                <!-- Duração -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Duração (minutos)*</label>
                    <InputText
                        v-model="form.duration_minutes"
                        type="number"
                        min="5"
                        step="5"
                        placeholder="30"
                        class="w-full !border !border-gray-300 !px-4 !py-2 !rounded-xl"
                        :class="{ '!border-red-500': form.errors.duration_minutes }" />
                    <small v-if="form.errors.duration_minutes" class="text-red-500">{{ form.errors.duration_minutes }}</small>
                </div>

                <!-- Botões -->
                <div class="flex gap-3 justify-end pt-4">
                    <Button label="Cancelar" severity="secondary" @click="showModal = false" class="px-6 py-2 rounded-full" />
                    <Button label="Salvar" :loading="form.processing" @click="submitService" class="!bg-primary !text-white px-6 py-2 rounded-full" />
                </div>
            </form>
        </Dialog>

        <!-- Confirmação de Deleção -->
        <ConfirmDialog group="deleteService" />
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const confirm = useConfirm();

const props = defineProps({
    services: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || '');
const showModal = ref(false);
const isEditing = ref(false);
const loading = ref(false);
const rowsPerPage = ref(Number(props.filters.per_page || props.services.per_page || 10));
const sortField = ref(props.filters.sort_field || 'name');
const sortOrder = ref(Number(props.filters.sort_order || 1));
const rowsOptions = [10, 20, 50];

const tableRows = computed(() => props.services.data || []);
const firstRecord = computed(() => (props.services.current_page - 1) * props.services.per_page);

const form = useForm({
    name: '',
    description: '',
    price: '',
    duration_minutes: '',
});

// Debounce search
let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters(true);
    }, 400);
});

const applyFilters = (resetPage = false) => {
    router.get(
        route('admin.services.index'),
        {
            search: search.value || undefined,
            per_page: rowsPerPage.value,
            page: resetPage ? 1 : props.services.current_page,
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
        route('admin.services.index'),
        {
            search: search.value || undefined,
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
    sortField.value = event.sortField || 'name';
    sortOrder.value = event.sortOrder || 1;
    applyFilters(true);
};

const openCreateModal = () => {
    isEditing.value = false;
    resetForm();
    showModal.value = true;
};

const openEditModal = (service) => {
    isEditing.value = true;
    form.name = service.name;
    form.description = service.description || '';
    form.price = service.price;
    form.duration_minutes = service.duration_minutes;
    form.id = service.id;
    showModal.value = true;
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    isEditing.value = false;
};

const submitService = () => {
    if (isEditing.value) {
        form.patch(route('admin.services.update', form.id), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                resetForm();
            },
        });
    } else {
        form.post(route('admin.services.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                resetForm();
            },
        });
    }
};

const confirmDelete = (id) => {
    confirm.require({
        group: 'deleteService',
        message: 'Tem certeza que deseja deletar este serviço?',
        header: 'Confirmar Deleção',
        icon: 'pi pi-exclamation-triangle',
        accept() {
            router.delete(route('admin.services.destroy', id), {
                preserveScroll: true,
            });
        },
    });
};

const formatPrice = (price) => {
    return Number(price || 0).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};
</script>
