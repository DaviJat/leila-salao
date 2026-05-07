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

const props = defineProps({
    clients: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || '');
const rowsPerPage = ref(Number(props.filters.per_page || props.clients.per_page || 10));
const sortField = ref(props.filters.sort_field || 'full_name');
const sortOrder = ref(Number(props.filters.sort_order || 1));

const rowsOptions = [10, 20, 50];
const tableRows = computed(() => props.clients.data || []);
const firstRecord = computed(() => (props.clients.current_page - 1) * props.clients.per_page);

const clearDigits = (value) => String(value || '').replace(/\D/g, '');

const formatPhone = (phone) => {
    if (!phone) return '';

    const cleaned = clearDigits(phone);
    if (cleaned.length === 11) {
        return `(${cleaned.slice(0, 2)}) ${cleaned.slice(2, 7)}-${cleaned.slice(7)}`;
    }

    if (cleaned.length === 10) {
        return `(${cleaned.slice(0, 2)}) ${cleaned.slice(2, 6)}-${cleaned.slice(6)}`;
    }

    return phone;
};

const formatCpf = (cpf) => {
    if (!cpf) return '';

    const cleaned = clearDigits(cpf).slice(0, 11);
    if (cleaned.length !== 11) return cpf;

    return cleaned.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
};

const formatBirthDate = (date) => {
    if (!date) return '-';
    return String(date).substring(0, 10).split('-').reverse().join('/');
};

const formatAddress = (client) => {
    const address = [client.street, client.number, client.neighborhood].filter(Boolean).join(', ');
    const locality = [client.city, client.state].filter(Boolean).join(' / ');

    return [address, locality].filter(Boolean).join(' · ') || 'Sem endereço';
};

const applyPhoneMask = (event) => {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    if (value.length > 2) value = `(${value.slice(0, 2)}) ${value.slice(2)}`;
    if (value.length > 9) value = `${value.slice(0, 10)}-${value.slice(10)}`;
    clientForm.phone = value;
};

const applyCpfMask = (event) => {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    if (value.length > 3) value = `${value.slice(0, 3)}.${value.slice(3)}`;
    if (value.length > 7) value = `${value.slice(0, 7)}.${value.slice(7)}`;
    if (value.length > 11) value = `${value.slice(0, 11)}-${value.slice(11)}`;
    clientForm.cpf = value;
};

const applyCepMask = (event) => {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length > 8) value = value.slice(0, 8);
    if (value.length > 5) value = `${value.slice(0, 5)}-${value.slice(5)}`;
    clientForm.postal_code = value;
};

const fetchAddress = async () => {
    const cep = clearDigits(clientForm.postal_code);

    if (cep.length !== 8) return;

    try {
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await response.json();

        if (!data.erro) {
            clientForm.street = data.logradouro || '';
            clientForm.neighborhood = data.bairro || '';
            clientForm.city = data.localidade || '';
            clientForm.state = data.uf || '';
        }
    } catch (error) {
        console.error('Erro ao buscar CEP', error);
    }
};

const openWhatsApp = (phone, name) => {
    const cleanPhone = clearDigits(phone);
    if (!cleanPhone) return;

    const text = encodeURIComponent(`Olá, ${name}! Tudo bem?`);
    window.open(`https://wa.me/55${cleanPhone}?text=${text}`, '_blank');
};

let searchTimeout = null;
const buildFilterParams = (page = props.clients.current_page) => ({
    search: search.value || undefined,
    per_page: rowsPerPage.value,
    page,
    sort_field: sortField.value,
    sort_order: sortOrder.value,
});

const applyFilters = (resetPage = false) => {
    router.get(route('admin.clients.index'), buildFilterParams(resetPage ? 1 : props.clients.current_page), {
        preserveState: true,
        preserveScroll: true,
        replace: false,
    });
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(true), 400);
});

const onSort = (event) => {
    sortField.value = event.sortField || 'full_name';
    sortOrder.value = event.sortOrder || 1;
    applyFilters(true);
};

const onPage = (event) => {
    rowsPerPage.value = event.rows;
    router.get(route('admin.clients.index'), buildFilterParams(event.page + 1), {
        preserveState: true,
        preserveScroll: true,
        replace: false,
    });
};

const clientModalVisible = ref(false);
const clientMode = ref('create');
const selectedClient = ref(null);

const clientForm = useForm({
    full_name: '',
    phone: '',
    email: '',
    cpf: '',
    birth_date: '',
    postal_code: '',
    street: '',
    number: '',
    complement: '',
    neighborhood: '',
    city: '',
    state: '',
    notes: '',
});

const modalTitle = computed(() => (clientMode.value === 'create' ? 'Novo Cliente' : 'Editar Cliente'));
const submitLabel = computed(() => (clientMode.value === 'create' ? 'Salvar Cliente' : 'Atualizar Cliente'));

const resetClientForm = () => {
    clientForm.reset();
    clientForm.clearErrors();
};

const closeClientModal = () => {
    clientModalVisible.value = false;
    selectedClient.value = null;
    resetClientForm();
};

const openCreateModal = () => {
    clientMode.value = 'create';
    selectedClient.value = null;
    resetClientForm();
    clientModalVisible.value = true;
};

const openEditModal = (client) => {
    clientMode.value = 'edit';
    selectedClient.value = client;

    clientForm.full_name = client.full_name || '';
    clientForm.phone = formatPhone(client.phone);
    clientForm.email = client.email || '';
    clientForm.cpf = formatCpf(client.cpf);
    clientForm.birth_date = client.birth_date ? String(client.birth_date).substring(0, 10) : '';
    clientForm.postal_code = client.postal_code ? String(client.postal_code).replace(/(\d{5})(\d{3})/, '$1-$2') : '';
    clientForm.street = client.street || '';
    clientForm.number = client.number || '';
    clientForm.complement = client.complement || '';
    clientForm.neighborhood = client.neighborhood || '';
    clientForm.city = client.city || '';
    clientForm.state = client.state || '';
    clientForm.notes = client.notes || '';

    clientForm.clearErrors();
    clientModalVisible.value = true;
};

const submitClient = () => {
    if (clientMode.value === 'create') {
        clientForm.post(route('admin.clients.store'), {
            preserveScroll: true,
            onSuccess: closeClientModal,
        });
        return;
    }

    if (!selectedClient.value) return;

    clientForm.patch(route('admin.clients.update', selectedClient.value.id), {
        preserveScroll: true,
        onSuccess: closeClientModal,
    });
};
</script>

<template>
    <Head title="Clientes" />

    <AuthenticatedLayout>
        <section class="flex min-h-[calc(100vh-9rem)] flex-col gap-5">
            <!-- HEADER COM TÍTULO E FILTROS -->
            <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                <!-- TÍTULO E DESCRIÇÃO -->
                <div class="flex-shrink-0 px-6">
                    <h2 class="font-bold text-2xl text-gray-900 tracking-tight">Clientes</h2>
                    <p class="text-sm text-gray-500 mt-1">Gerencie a base de clientes do salão.</p>
                </div>

                <!-- FILTROS -->
                <div class="bg-white/90 border border-gray-100 rounded-3xl p-4 sm:p-5 shadow-sm lg:flex-1">
                    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 items-center">
                        <InputText v-model="search" placeholder="Buscar por nome, telefone ou CPF" class="flex-1 min-w-0 w-full" aria-label="Buscar por nome, telefone ou CPF" />

                        <div class="flex items-end">
                            <button
                                @click="openCreateModal"
                                class="w-full sm:w-auto bg-[#547558] text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-md hover:bg-[#435e46] transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                Novo Cliente
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
                    :rows="rowsPerPage"
                    :total-records="props.clients.total"
                    :first="firstRecord"
                    :rows-per-page-options="rowsOptions"
                    paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                    current-page-report-template="{first} a {last} de {totalRecords}"
                    responsive-layout="scroll"
                    sort-mode="single"
                    removable-sort
                    :sort-field="sortField"
                    :sort-order="sortOrder"
                    class="clients-table p-datatable-sm flex-1"
                    table-style="min-width: 100%"
                    @page="onPage"
                    @sort="onSort">
                    <template #empty>
                        <div class="py-12 text-center text-gray-500">Nenhum cliente encontrado.</div>
                    </template>

                    <Column header="Cliente" sortable sortField="full_name" style="min-width: 18rem">
                        <template #body="slotProps">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-gray-100 text-sm font-bold text-gray-500">
                                    {{ slotProps.data.full_name?.charAt(0)?.toUpperCase() || 'C' }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ slotProps.data.full_name }}</p>
                                    <p v-if="slotProps.data.cpf" class="text-xs text-gray-500">CPF: {{ formatCpf(slotProps.data.cpf) }}</p>
                                    <p v-if="slotProps.data.birth_date" class="text-xs text-gray-500">Nascimento: {{ formatBirthDate(slotProps.data.birth_date) }}</p>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column header="Contato" sortable sortField="phone" style="min-width: 14rem">
                        <template #body="slotProps">
                            <div>
                                <p class="font-semibold text-gray-900">{{ formatPhone(slotProps.data.phone) }}</p>
                                <p class="text-xs text-gray-500">{{ slotProps.data.email || 'Sem email' }}</p>
                            </div>
                        </template>
                    </Column>

                    <Column header="Endereço" sortable sortField="city" style="min-width: 18rem">
                        <template #body="slotProps">
                            <div>
                                <p class="text-sm font-medium text-gray-700">{{ formatAddress(slotProps.data) }}</p>
                                <p v-if="slotProps.data.postal_code" class="text-xs text-gray-500 mt-0.5">
                                    CEP {{ String(slotProps.data.postal_code).replace(/(\d{5})(\d{3})/, '$1-$2') }}
                                </p>
                            </div>
                        </template>
                    </Column>

                    <Column header="Agendamentos" sortable sortField="appointments_count" style="min-width: 10rem">
                        <template #body="slotProps">
                            <Tag :value="`${slotProps.data.appointments_count || 0} agend.`" severity="success" rounded />
                        </template>
                    </Column>

                    <Column header="Ações" style="min-width: 16rem">
                        <template #body="slotProps">
                            <div class="flex flex-wrap gap-2">
                                <Button
                                    label="Conversar"
                                    icon="pi pi-whatsapp"
                                    size="small"
                                    outlined
                                    severity="success"
                                    @click="openWhatsApp(slotProps.data.phone, slotProps.data.full_name)" />

                                <Button label="Editar" icon="pi pi-pencil" size="small" outlined @click="openEditModal(slotProps.data)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </section>

        <Dialog v-model:visible="clientModalVisible" modal :header="modalTitle" :style="{ width: '48rem' }" @hide="closeClientModal">
            <form class="space-y-5" @submit.prevent="submitClient">
                <div>
                    <h4 class="text-xs font-black text-[#547558] uppercase tracking-[0.2em] mb-4 border-b border-gray-100 pb-2">Dados Pessoais</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nome Completo *</label>
                            <InputText v-model="clientForm.full_name" class="w-full" :invalid="!!clientForm.errors.full_name" placeholder="Nome do cliente" />
                            <p v-if="clientForm.errors.full_name" class="text-xs text-red-500 mt-1">{{ clientForm.errors.full_name }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">WhatsApp *</label>
                            <InputText v-model="clientForm.phone" @input="applyPhoneMask" class="w-full" :invalid="!!clientForm.errors.phone" placeholder="(11) 90000-0000" />
                            <p v-if="clientForm.errors.phone" class="text-xs text-red-500 mt-1">{{ clientForm.errors.phone }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">E-mail</label>
                            <InputText v-model="clientForm.email" type="email" class="w-full" :invalid="!!clientForm.errors.email" placeholder="cliente@email.com" />
                            <p v-if="clientForm.errors.email" class="text-xs text-red-500 mt-1">{{ clientForm.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">CPF</label>
                            <InputText v-model="clientForm.cpf" @input="applyCpfMask" class="w-full" :invalid="!!clientForm.errors.cpf" placeholder="000.000.000-00" />
                            <p v-if="clientForm.errors.cpf" class="text-xs text-red-500 mt-1">{{ clientForm.errors.cpf }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Data de Nascimento</label>
                            <InputText v-model="clientForm.birth_date" type="date" class="w-full" :invalid="!!clientForm.errors.birth_date" />
                            <p v-if="clientForm.errors.birth_date" class="text-xs text-red-500 mt-1">{{ clientForm.errors.birth_date }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-black text-[#547558] uppercase tracking-[0.2em] mb-4 border-b border-gray-100 pb-2">Endereço</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">CEP</label>
                            <InputText
                                v-model="clientForm.postal_code"
                                @input="applyCepMask"
                                @blur="fetchAddress"
                                class="w-full"
                                :invalid="!!clientForm.errors.postal_code"
                                placeholder="00000-000" />
                            <p v-if="clientForm.errors.postal_code" class="text-xs text-red-500 mt-1">{{ clientForm.errors.postal_code }}</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Rua / Logradouro</label>
                            <InputText v-model="clientForm.street" class="w-full" :invalid="!!clientForm.errors.street" />
                            <p v-if="clientForm.errors.street" class="text-xs text-red-500 mt-1">{{ clientForm.errors.street }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Número</label>
                            <InputText v-model="clientForm.number" class="w-full" :invalid="!!clientForm.errors.number" />
                            <p v-if="clientForm.errors.number" class="text-xs text-red-500 mt-1">{{ clientForm.errors.number }}</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Complemento</label>
                            <InputText v-model="clientForm.complement" class="w-full" :invalid="!!clientForm.errors.complement" />
                            <p v-if="clientForm.errors.complement" class="text-xs text-red-500 mt-1">{{ clientForm.errors.complement }}</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Bairro</label>
                            <InputText v-model="clientForm.neighborhood" class="w-full" :invalid="!!clientForm.errors.neighborhood" />
                            <p v-if="clientForm.errors.neighborhood" class="text-xs text-red-500 mt-1">{{ clientForm.errors.neighborhood }}</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Cidade</label>
                            <InputText v-model="clientForm.city" class="w-full" :invalid="!!clientForm.errors.city" />
                            <p v-if="clientForm.errors.city" class="text-xs text-red-500 mt-1">{{ clientForm.errors.city }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">UF</label>
                            <InputText v-model="clientForm.state" class="w-full" :invalid="!!clientForm.errors.state" maxlength="2" />
                            <p v-if="clientForm.errors.state" class="text-xs text-red-500 mt-1">{{ clientForm.errors.state }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-black text-[#547558] uppercase tracking-[0.2em] mb-4 border-b border-gray-100 pb-2">Anotações Internas</h4>
                    <textarea
                        v-model="clientForm.notes"
                        rows="3"
                        placeholder="Alergias, preferências, observações importantes..."
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-[#547558] focus:ring-2 focus:ring-[#547558]/15"></textarea>
                    <p v-if="clientForm.errors.notes" class="text-xs text-red-500 mt-1">{{ clientForm.errors.notes }}</p>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <Button type="button" label="Cancelar" text @click="closeClientModal" />
                    <Button type="submit" :label="submitLabel" icon="pi pi-save" :loading="clientForm.processing" />
                </div>
            </form>
        </Dialog>
    </AuthenticatedLayout>
</template>

<style scoped>
:deep(.clients-table .p-datatable-wrapper) {
    border-bottom: 1px solid rgb(243 244 246);
}

:deep(.clients-table .p-datatable-thead > tr > th) {
    white-space: nowrap;
}

:deep(.clients-table .p-paginator) {
    border-top: 0;
}

:deep(.p-datatable-paginator-bottom) {
    border: none !important;
}
</style>
