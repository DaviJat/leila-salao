<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    clients: Array,
    filters: Object,
});

// ==========================================
// BUSCA E FILTROS
// ==========================================
const search = ref(props.filters.search || '');

let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.clients.index'), { search: value }, { preserveState: true, replace: true });
    }, 500);
});

// ==========================================
// FORMATAÇÕES E MÁSCARAS
// ==========================================
const formatPhoneDisplay = (phone) => {
    if (!phone) return '';
    const cleaned = ('' + phone).replace(/\D/g, '');
    const match = cleaned.match(/^(\d{2})(\d{4,5})(\d{4})$/);
    if (match) return `(${match[1]}) ${match[2]}-${match[3]}`;
    return phone;
};

const applyPhoneMask = (event, formObj) => {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    if (value.length > 2) value = `(${value.slice(0, 2)}) ${value.slice(2)}`;
    if (value.length > 9) value = `${value.slice(0, 10)}-${value.slice(10)}`;
    formObj.phone = value;
};

const applyCpfMask = (event, formObj) => {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    if (value.length > 3) value = `${value.slice(0, 3)}.${value.slice(3)}`;
    if (value.length > 7) value = `${value.slice(0, 7)}.${value.slice(7)}`;
    if (value.length > 11) value = `${value.slice(0, 11)}-${value.slice(11)}`;
    formObj.cpf = value;
};

const applyCepMask = (event, formObj) => {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length > 8) value = value.slice(0, 8);
    if (value.length > 5) value = `${value.slice(0, 5)}-${value.slice(5)}`;
    formObj.postal_code = value;
};

// Integração ViaCEP para autocompletar o endereço
const fetchAddress = async (formObj) => {
    const cep = formObj.postal_code?.replace(/\D/g, '');
    if (cep?.length === 8) {
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();
            if (!data.erro) {
                formObj.street = data.logradouro;
                formObj.neighborhood = data.bairro;
                formObj.city = data.localidade;
                formObj.state = data.uf;
            }
        } catch (error) {
            console.error('Erro ao buscar CEP', error);
        }
    }
};

const openWhatsApp = (phone, name) => {
    const cleanPhone = String(phone).replace(/\D/g, '');
    const text = encodeURIComponent(`Olá, ${name}! Tudo bem? `);
    window.open(`https://wa.me/55${cleanPhone}?text=${text}`, '_blank');
};

// ==========================================
// MODAIS DE CRIAÇÃO E EDIÇÃO
// ==========================================
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const editingClient = ref(null);

const createForm = useForm({
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

const editForm = useForm({
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

const openEditModal = (client) => {
    editingClient.value = client;
    editForm.full_name = client.full_name;
    editForm.phone = formatPhoneDisplay(client.phone);
    editForm.email = client.email || '';

    // Máscara CPF e CEP se existirem
    let cpf = client.cpf || '';
    if (cpf) cpf = cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    editForm.cpf = cpf;

    let cep = client.postal_code || '';
    if (cep) cep = cep.replace(/(\d{5})(\d{3})/, '$1-$2');
    editForm.postal_code = cep;

    editForm.birth_date = client.birth_date ? String(client.birth_date).substring(0, 10) : '';
    editForm.street = client.street || '';
    editForm.number = client.number || '';
    editForm.complement = client.complement || '';
    editForm.neighborhood = client.neighborhood || '';
    editForm.city = client.city || '';
    editForm.state = client.state || '';
    editForm.notes = client.notes || '';

    isEditModalOpen.value = true;
};

const submitCreate = () => {
    createForm.post(route('admin.clients.store'), {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
        },
        onError: (errors) => alert('Verifique os erros:\n' + Object.values(errors).join('\n')),
    });
};

const submitEdit = () => {
    editForm.patch(route('admin.clients.update', editingClient.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
        },
        onError: (errors) => alert('Verifique os erros:\n' + Object.values(errors).join('\n')),
    });
};
</script>

<template>
    <Head title="Base de Clientes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 tracking-tight">Base de Clientes</h2>
                    <p class="text-sm text-gray-500 mt-1">Gerencie os cadastros e histórico do salão.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto bg-white/60 p-2 rounded-2xl border border-gray-100">
                    <div class="relative w-full sm:w-72">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar cliente..."
                            class="w-full h-[42px] rounded-xl border border-gray-100 bg-white shadow-sm pl-10 pr-4 text-sm font-bold text-gray-700 focus:ring-[#547558]/20 focus:border-[#547558] transition-all" />
                    </div>

                    <button
                        @click="isCreateModalOpen = true"
                        class="w-full sm:w-auto h-[42px] bg-[#547558] text-white px-6 rounded-xl text-sm font-bold shadow-md hover:bg-[#435e46] transition-all flex items-center justify-center gap-2">
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
        </template>

        <div class="bg-white/90 backdrop-blur-md shadow-sm sm:rounded-[2rem] border border-gray-100/50 overflow-hidden mt-4">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Cliente</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Contato</th>
                            <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Agendamentos</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-transparent">
                        <tr v-for="client in clients" :key="client.id" class="hover:bg-[#547558]/5 transition-colors group">
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-500 border border-gray-200 flex items-center justify-center font-bold text-sm">
                                        {{ client.full_name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">{{ client.full_name }}</div>
                                        <div class="text-xs font-black text-[#547558] mt-0.5" v-if="client.cpf">
                                            CPF: {{ String(client.cpf).replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ formatPhoneDisplay(client.phone) }}</div>
                                <div class="text-xs font-medium text-gray-500 mt-0.5">{{ client.email || 'Sem email' }}</div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-center">
                                <span class="px-3 py-1 bg-[#547558]/10 text-[#547558] rounded-md text-[10px] font-black uppercase tracking-wider border border-[#547558]/20">
                                    {{ client.appointments_count }} Vez(es)
                                </span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-right text-xs font-bold space-x-3">
                                <button @click="openWhatsApp(client.phone, client.full_name)" class="text-[#547558] hover:underline uppercase transition-colors">Conversar</button>
                                <button @click="openEditModal(client)" class="text-blue-500 hover:text-blue-700 uppercase transition-colors">Ficha Completa</button>
                            </td>
                        </tr>
                        <tr v-if="clients.length === 0">
                            <td colspan="4" class="px-6 py-16 text-center text-gray-500 italic text-sm">Nenhum cliente encontrado.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="isCreateModalOpen || isEditModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div
                class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"
                @click="
                    isCreateModalOpen = false;
                    isEditModalOpen = false;
                "></div>

            <div class="bg-white w-full max-w-3xl rounded-[2rem] shadow-2xl relative z-10 overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xl font-bold text-gray-900">{{ isCreateModalOpen ? 'Novo Cadastro' : 'Ficha do Cliente' }}</h3>
                    <button
                        @click="
                            isCreateModalOpen = false;
                            isEditModalOpen = false;
                        "
                        class="text-gray-400 hover:text-gray-900 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2" /></svg>
                    </button>
                </div>

                <div class="px-8 py-6 overflow-y-auto custom-scrollbar">
                    <form @submit.prevent="isCreateModalOpen ? submitCreate() : submitEdit()" class="space-y-6">
                        <div>
                            <h4 class="text-xs font-black text-[#547558] uppercase tracking-[0.2em] mb-4 border-b border-gray-100 pb-2">Dados Pessoais</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Nome Completo *</label>
                                    <input
                                        v-model="(isCreateModalOpen ? createForm : editForm).full_name"
                                        type="text"
                                        required
                                        class="w-full rounded-xl border-gray-200 focus:border-[#547558] focus:ring-[#547558]/20 text-sm font-medium" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">WhatsApp *</label>
                                    <input
                                        v-model="(isCreateModalOpen ? createForm : editForm).phone"
                                        @input="applyPhoneMask($event, isCreateModalOpen ? createForm : editForm)"
                                        type="tel"
                                        required
                                        class="w-full rounded-xl border-gray-200 focus:border-[#547558] focus:ring-[#547558]/20 text-sm font-medium" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">E-mail</label>
                                    <input
                                        v-model="(isCreateModalOpen ? createForm : editForm).email"
                                        type="email"
                                        class="w-full rounded-xl border-gray-200 focus:border-[#547558] focus:ring-[#547558]/20 text-sm font-medium" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">CPF</label>
                                    <input
                                        v-model="(isCreateModalOpen ? createForm : editForm).cpf"
                                        @input="applyCpfMask($event, isCreateModalOpen ? createForm : editForm)"
                                        type="text"
                                        class="w-full rounded-xl border-gray-200 focus:border-[#547558] focus:ring-[#547558]/20 text-sm font-medium" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Data de Nascimento</label>
                                    <input
                                        v-model="(isCreateModalOpen ? createForm : editForm).birth_date"
                                        type="date"
                                        class="w-full rounded-xl border-gray-200 focus:border-[#547558] focus:ring-[#547558]/20 text-sm font-medium" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-black text-[#547558] uppercase tracking-[0.2em] mb-4 border-b border-gray-100 pb-2">Endereço</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">CEP</label>
                                    <input
                                        v-model="(isCreateModalOpen ? createForm : editForm).postal_code"
                                        @input="applyCepMask($event, isCreateModalOpen ? createForm : editForm)"
                                        @blur="fetchAddress(isCreateModalOpen ? createForm : editForm)"
                                        type="text"
                                        class="w-full rounded-xl border-gray-200 focus:border-[#547558] focus:ring-[#547558]/20 text-sm font-medium"
                                        placeholder="00000-000" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Rua / Logradouro</label>
                                    <input
                                        v-model="(isCreateModalOpen ? createForm : editForm).street"
                                        type="text"
                                        class="w-full rounded-xl border-gray-200 focus:border-[#547558] focus:ring-[#547558]/20 text-sm font-medium" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Número</label>
                                    <input
                                        v-model="(isCreateModalOpen ? createForm : editForm).number"
                                        type="text"
                                        class="w-full rounded-xl border-gray-200 focus:border-[#547558] focus:ring-[#547558]/20 text-sm font-medium" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Bairro</label>
                                    <input
                                        v-model="(isCreateModalOpen ? createForm : editForm).neighborhood"
                                        type="text"
                                        class="w-full rounded-xl border-gray-200 focus:border-[#547558] focus:ring-[#547558]/20 text-sm font-medium" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Complemento</label>
                                    <input
                                        v-model="(isCreateModalOpen ? createForm : editForm).complement"
                                        type="text"
                                        class="w-full rounded-xl border-gray-200 focus:border-[#547558] focus:ring-[#547558]/20 text-sm font-medium" />
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Cidade</label>
                                    <input
                                        v-model="(isCreateModalOpen ? createForm : editForm).city"
                                        type="text"
                                        class="w-full rounded-xl border-gray-200 focus:border-[#547558] focus:ring-[#547558]/20 text-sm font-medium bg-gray-50"
                                        readonly />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">UF</label>
                                    <input
                                        v-model="(isCreateModalOpen ? createForm : editForm).state"
                                        type="text"
                                        class="w-full rounded-xl border-gray-200 focus:border-[#547558] focus:ring-[#547558]/20 text-sm font-medium bg-gray-50"
                                        readonly />
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-black text-[#547558] uppercase tracking-[0.2em] mb-4 border-b border-gray-100 pb-2">Anotações Internas</h4>
                            <textarea
                                v-model="(isCreateModalOpen ? createForm : editForm).notes"
                                rows="3"
                                placeholder="Alergias, preferências, etc..."
                                class="w-full rounded-xl border-gray-200 focus:border-[#547558] focus:ring-[#547558]/20 text-sm font-medium custom-scrollbar"></textarea>
                        </div>
                    </form>
                </div>

                <div class="px-8 py-5 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3">
                    <button
                        @click="
                            isCreateModalOpen = false;
                            isEditModalOpen = false;
                        "
                        class="px-5 py-2.5 rounded-full text-sm font-bold text-gray-500 hover:bg-gray-200 transition-colors">
                        Cancelar
                    </button>
                    <button
                        @click="isCreateModalOpen ? submitCreate() : submitEdit()"
                        :disabled="isCreateModalOpen ? createForm.processing : editForm.processing"
                        class="bg-[#547558] text-white px-8 py-2.5 rounded-full text-sm font-bold shadow-md hover:bg-[#435e46] transition-all disabled:opacity-50">
                        {{ (isCreateModalOpen ? createForm.processing : editForm.processing) ? 'Salvando...' : 'Salvar Cliente' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
}
</style>
