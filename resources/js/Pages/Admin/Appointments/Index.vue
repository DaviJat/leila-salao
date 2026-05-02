<script setup>
// ==========================================
// IMPORTAÇÕES E PROPRIEDADES
// ==========================================
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    appointments: Array,
    filters: Object,
    dbServices: Array,
    availableSlots: Object,
    allClients: Array,
});

// ==========================================
// FILTROS E ORDENAÇÃO DA TABELA
// ==========================================
const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);
const sortBy = ref(props.filters.sort_by);
const sortDir = ref(props.filters.sort_dir);

// Dispara a busca no banco de dados com os filtros atuais
const applyFilters = () => {
    router.get(
        route('admin.appointments.index'),
        {
            start_date: startDate.value,
            end_date: endDate.value,
            sort_by: sortBy.value,
            sort_dir: sortDir.value,
        },
        { preserveState: true },
    );
};

// Alterna a direção da ordenação ao clicar no cabeçalho
const handleSort = (column) => {
    if (sortBy.value === column) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDir.value = 'asc';
    }
    applyFilters();
};

// ==========================================
// FORMATAÇÃO DE DADOS (VISUAL)
// ==========================================

// Estilização das badges de status
const statusMap = {
    pending: { label: 'Pendente', class: 'bg-yellow-50 text-yellow-700 border-yellow-200' },
    confirmed: { label: 'Confirmado', class: 'bg-[#547558]/10 text-[#547558] border-[#547558]/20' },
    canceled: { label: 'Cancelado', class: 'bg-red-50 text-red-600 border-red-200' },
    completed: { label: 'Concluído', class: 'bg-gray-100 text-gray-600 border-gray-200' },
};

// Aplica a máscara (00) 00000-0000 para exibição
const formatPhoneDisplay = (phone) => {
    if (!phone) return '';
    const cleaned = ('' + phone).replace(/\D/g, '');
    const match = cleaned.match(/^(\d{2})(\d{4,5})(\d{4})$/);
    if (match) return `(${match[1]}) ${match[2]}-${match[3]}`;
    return phone;
};

// Pega os 10 primeiros caracteres (YYYY-MM-DD) e inverte para DD/MM/YYYY
const formatDateDisplay = (dateStr) => {
    if (!dateStr) return '';
    const cleanDate = String(dateStr).substring(0, 10);
    const [year, month, day] = cleanDate.split('-');
    return `${day}/${month}/${year}`;
};

// ==========================================
// GESTÃO DE STATUS E NOTIFICAÇÃO (WHATSAPP)
// ==========================================
const confirmModal = ref({
    isOpen: false,
    title: '',
    message: '',
    actionText: '',
    actionClass: '',
    onConfirm: null,
});

// A função intercepta a ação "Finalizar" e resolve rapidamente, ou abre modal para Confirmar/Cancelar
const updateStatus = (app, status) => {
    // 1. REGRA DO "FINALIZAR" (Rápido, sem modal e sem WhatsApp)
    if (status === 'completed') {
        if (confirm('Deseja marcar este atendimento como concluído?')) {
            router.patch(route('admin.appointments.status', app.id), { status });
        }
        return; // Encerra a função aqui, para não abrir o modal
    }

    // 2. REGRA DO "CONFIRMAR" E "CANCELAR" (Com modal customizado e WhatsApp)
    let actionName = status === 'canceled' ? 'Cancelar Agendamento' : 'Confirmar Agendamento';
    let actionClass = status === 'canceled' ? 'bg-red-500 hover:bg-red-600' : 'bg-[#547558] hover:bg-[#435e46]';

    confirmModal.value = {
        isOpen: true,
        title: 'Confirmação de Status',
        message: `Tem certeza que deseja ${actionName.toLowerCase()}? Uma nova aba do WhatsApp abrirá para você enviar a mensagem ao cliente.`,
        actionText: actionName,
        actionClass: actionClass,
        onConfirm: () => {
            router.patch(
                route('admin.appointments.status', app.id),
                { status },
                {
                    onSuccess: () => {
                        const phone = app.client.phone.replace(/\D/g, '');
                        const date = formatDateDisplay(app.availability.date);
                        const time = app.availability.hour.substring(0, 5);

                        let text =
                            status === 'confirmed'
                                ? `✅ *Agendamento Confirmado!*\n\nOlá, ${app.client.full_name}! Seu horário para o dia *${date}* às *${time}* foi confirmado. Te esperamos ansiosamente!`
                                : `❌ *Agendamento Cancelado*\n\nOlá, ${app.client.full_name}. Informamos que seu agendamento do dia *${date}* às *${time}* foi cancelado. Se desejar, acesse nosso site para reagendar.`;

                        window.open(`https://wa.me/55${phone}?text=${encodeURIComponent(text)}`, '_blank');
                    },
                },
            );
            confirmModal.value.isOpen = false;
        },
    };
};

// ==========================================
// MODAL DE NOVO AGENDAMENTO (CRIAR E BUSCAR)
// ==========================================
const isCreateModalOpen = ref(false);
const showClientSuggestions = ref(false);

const form = useForm({
    client_name: '',
    client_phone: '',
    date: '',
    time: '',
    services: [],
});

// Filtra a lista de clientes com base no nome digitado
const filteredClients = computed(() => {
    if (form.client_name.length < 2) return [];
    return props.allClients
        .filter((client) => client.full_name.toLowerCase().includes(form.client_name.toLowerCase()) || client.phone.includes(form.client_name.replace(/\D/g, '')))
        .slice(0, 5); // Limita a 5 resultados para não poluir a tela
});

// Preenche o formulário ao clicar em um cliente da busca
const selectClient = (client) => {
    form.client_name = client.full_name;
    let val = client.phone;
    if (val.length === 11) {
        form.client_phone = `(${val.slice(0, 2)}) ${val.slice(2, 7)}-${val.slice(7)}`;
    } else {
        form.client_phone = val;
    }
    showClientSuggestions.value = false;
};

// Fecha a listagem de sugestões com um leve atraso para permitir o clique
const closeClientSuggestions = () => {
    setTimeout(() => {
        showClientSuggestions.value = false;
    }, 200);
};

// Retorna apenas os horários disponíveis para a data selecionada
const availableTimes = computed(() => {
    if (!form.date) return [];
    return props.availableSlots[form.date] || [];
});

// Limpa o horário sempre que a Leila trocar o dia selecionado
const handleDateSelection = () => {
    form.time = '';
};

// Aplica a máscara no input do WhatsApp em tempo real
const applyPhoneMask = (event) => {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    if (value.length > 2) value = `(${value.slice(0, 2)}) ${value.slice(2)}`;
    if (value.length > 9) value = `${value.slice(0, 10)}-${value.slice(10)}`;
    form.client_phone = value;
};

// Adiciona ou remove um serviço do array
const toggleService = (service) => {
    const index = form.services.findIndex((s) => s.id === service.id);
    if (index === -1) form.services.push(service);
    else form.services.splice(index, 1);
};

// Envia o formulário e abre o WhatsApp com a mensagem de confirmação
const submitCreate = () => {
    const phone = form.client_phone.replace(/\D/g, '');
    const date = formatDateDisplay(form.date);
    const time = form.time.substring(0, 5);
    const services = form.services.map((s) => s.name).join(', ');

    const waText = `🎉 *Novo Agendamento!*\n\nOlá, ${form.client_name}! Um horário foi agendado para você no salão:\n\n📅 *Data:* ${date}\n⏰ *Hora:* ${time}\n✂️ *Serviços:* ${services}\n\nPara gerenciar seus horários, acesse nosso site.`;

    form.transform((data) => ({
        ...data,
        client_phone: phone,
    })).post(route('admin.appointments.store'), {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            form.reset();
            applyFilters();
            window.open(`https://wa.me/55${phone}?text=${encodeURIComponent(waText)}`, '_blank');
        },
    });
};

// ==========================================
// MODAL DE EDIÇÃO DO AGENDAMENTO
// ==========================================
const isEditModalOpen = ref(false);
const editingAppointment = ref(null);

const editForm = useForm({
    date: '',
    time: '',
    services: [],
});

const openEditModal = (app) => {
    editingAppointment.value = app;
    editForm.date = String(app.availability.date).substring(0, 10);
    editForm.time = app.availability.hour.substring(0, 5);
    editForm.services = [...app.services];
    isEditModalOpen.value = true;
};

// Computa os horários livres, mas garante que o horário ATUAL do cliente apareça na lista se o dia for o mesmo
const availableTimesEdit = computed(() => {
    if (!editForm.date) return [];
    let times = [...(props.availableSlots[editForm.date] || [])];

    if (editingAppointment.value && editForm.date === String(editingAppointment.value.availability.date).substring(0, 10)) {
        const originalTime = editingAppointment.value.availability.hour.substring(0, 5);
        if (!times.includes(originalTime)) {
            times.push(originalTime);
            times.sort();
        }
    }
    return times;
});

const toggleEditService = (service) => {
    const index = editForm.services.findIndex((s) => s.id === service.id);
    if (index === -1) editForm.services.push(service);
    else editForm.services.splice(index, 1);
};

// ENVIO DA EDIÇÃO COM TRATAMENTO DE ERRO
const submitEdit = () => {
    const phone = String(editingAppointment.value?.client?.phone || '').replace(/\D/g, '');
    const clientName = editingAppointment.value?.client?.full_name || 'Cliente';
    const newDate = formatDateDisplay(editForm.date);
    const newTime = String(editForm.time).substring(0, 5);
    const newServices = editForm.services.map((s) => s.name).join(', ');

    const waText = `🔄 *Agendamento Atualizado!*\n\nOlá, ${clientName}! Seu agendamento no salão foi modificado.\n\n📅 *Nova Data:* ${newDate}\n⏰ *Novo Horário:* ${newTime}\n✂️ *Serviços:* ${newServices}\n\nTe esperamos lá!`;

    editForm.put(route('admin.appointments.update', editingAppointment.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            applyFilters();
            window.open(`https://wa.me/55${phone}?text=${encodeURIComponent(waText)}`, '_blank');
        },
        onError: (errors) => {
            const errorMessages = Object.values(errors).join('\n');
            alert('Não foi possível salvar as alterações. Verifique os erros:\n\n' + errorMessages);
        },
    });
};
</script>

<template>
    <Head title="Gerenciar Agenda" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 tracking-tight">Agenda do Salão</h2>
                    <p class="text-sm text-gray-500 mt-1">Gerencie os horários do período selecionado.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto bg-white/60 p-2 rounded-2xl border border-gray-100">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-2">De</span>
                        <div class="relative w-full sm:w-[150px]">
                            <div class="w-full rounded-xl bg-white shadow-sm px-3 py-2.5 flex items-center justify-between text-sm font-bold text-gray-700">
                                <span>{{ formatDateDisplay(startDate) }}</span>
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <input v-model="startDate" @change="applyFilters" type="date" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-2">Até</span>
                        <div class="relative w-full sm:w-[150px]">
                            <div class="w-full rounded-xl bg-white shadow-sm px-3 py-2.5 flex items-center justify-between text-sm font-bold text-gray-700">
                                <span>{{ formatDateDisplay(endDate) }}</span>
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <input v-model="endDate" @change="applyFilters" type="date" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                        </div>
                    </div>

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
        </template>

        <div class="bg-white/90 backdrop-blur-md shadow-sm sm:rounded-[2rem] border border-gray-100/50 overflow-hidden mt-4">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th
                                @click="handleSort('date')"
                                class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] cursor-pointer hover:text-[#547558] transition-colors">
                                <div class="flex items-center gap-1">
                                    Data / Horário
                                    <svg
                                        v-if="sortBy === 'date'"
                                        class="w-3 h-3"
                                        :class="sortDir === 'desc' ? 'rotate-180' : ''"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path d="M19 9l-7 7-7-7" stroke-width="2" />
                                    </svg>
                                </div>
                            </th>
                            <th
                                @click="handleSort('client')"
                                class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] cursor-pointer hover:text-[#547558] transition-colors">
                                <div class="flex items-center gap-1">
                                    Cliente
                                    <svg
                                        v-if="sortBy === 'client'"
                                        class="w-3 h-3"
                                        :class="sortDir === 'desc' ? 'rotate-180' : ''"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path d="M19 9l-7 7-7-7" stroke-width="2" />
                                    </svg>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Serviços</th>
                            <th
                                @click="handleSort('status')"
                                class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] cursor-pointer hover:text-[#547558] transition-colors">
                                <div class="flex items-center gap-1">
                                    Status
                                    <svg
                                        v-if="sortBy === 'status'"
                                        class="w-3 h-3"
                                        :class="sortDir === 'desc' ? 'rotate-180' : ''"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path d="M19 9l-7 7-7-7" stroke-width="2" />
                                    </svg>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-transparent">
                        <tr v-for="app in appointments" :key="app.id" class="hover:bg-[#547558]/5 transition-colors group">
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">
                                    {{ formatDateDisplay(app.availability.date) }}
                                </div>
                                <div class="text-xs font-black text-[#547558]">{{ app.availability.hour.substring(0, 5) }}</div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ app.client.full_name }}</div>
                                <div class="text-xs font-medium text-gray-500 mt-0.5">{{ formatPhoneDisplay(app.client.phone) }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-sm font-medium text-gray-600 truncate max-w-[180px]" :title="app.services.map((s) => s.name).join(', ')">
                                    {{ app.services.map((s) => s.name).join(' + ') }}
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <span :class="['px-3 py-1 text-[10px] font-black uppercase tracking-wider rounded-md border', statusMap[app.status].class]">
                                    {{ statusMap[app.status].label }}
                                </span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-right text-xs font-bold space-x-3">
                                <button v-if="['pending', 'confirmed'].includes(app.status)" @click="openEditModal(app)" class="text-blue-500 hover:text-blue-700 uppercase">
                                    Editar
                                </button>
                                <button v-if="app.status === 'pending'" @click="updateStatus(app, 'confirmed')" class="text-[#547558] hover:underline uppercase">Confirmar</button>
                                <button
                                    v-if="['pending', 'confirmed'].includes(app.status)"
                                    @click="updateStatus(app, 'canceled')"
                                    class="text-red-500 hover:text-red-700 uppercase">
                                    Cancelar
                                </button>
                                <button v-if="app.status === 'confirmed'" @click="updateStatus(app, 'completed')" class="text-gray-400 hover:text-gray-900 uppercase">
                                    Finalizar
                                </button>
                            </td>
                        </tr>
                        <tr v-if="appointments.length === 0">
                            <td colspan="5" class="px-6 py-16 text-center text-gray-500 italic text-sm">Nenhum registro encontrado para este período.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="isCreateModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" @click="isCreateModalOpen = false"></div>

            <div class="bg-white w-full max-w-xl rounded-[2rem] shadow-2xl relative z-10 overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xl font-bold text-gray-900">Novo Agendamento</h3>
                    <button @click="isCreateModalOpen = false" class="text-gray-400 hover:text-gray-900 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2" /></svg>
                    </button>
                </div>

                <div class="px-8 py-6 overflow-y-auto custom-scrollbar">
                    <form @submit.prevent="submitCreate" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="relative">
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Cliente</label>
                                <input
                                    v-model="form.client_name"
                                    @focus="showClientSuggestions = true"
                                    @blur="closeClientSuggestions"
                                    type="text"
                                    required
                                    placeholder="Comece a digitar..."
                                    class="w-full rounded-xl border-gray-200 focus:border-[#547558] focus:ring-[#547558]/20 text-sm" />
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
                                        <span class="text-xs text-gray-500">{{ formatPhoneDisplay(client.phone) }}</span>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">WhatsApp</label>
                                <input
                                    v-model="form.client_phone"
                                    @input="applyPhoneMask"
                                    type="tel"
                                    required
                                    class="w-full rounded-xl border-gray-200 focus:border-[#547558] focus:ring-[#547558]/20 text-sm" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <div>
                                <label class="block text-xs font-bold text-[#547558] uppercase tracking-widest mb-2">Data Disponível</label>
                                <select v-model="form.date" @change="handleDateSelection" required class="w-full rounded-xl border-gray-200 text-sm font-bold text-gray-700">
                                    <option value="" disabled>Selecione um dia</option>
                                    <option v-for="(times, dateKey) in availableSlots" :key="dateKey" :value="dateKey">
                                        {{ formatDateDisplay(dateKey) }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#547558] uppercase tracking-widest mb-2">Hora</label>
                                <select v-model="form.time" :disabled="!form.date" required class="w-full rounded-xl border-gray-200 text-sm font-bold">
                                    <option value="" disabled>Selecione a hora</option>
                                    <option v-for="time in availableTimes" :key="time" :value="time">{{ time }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Serviços</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[160px] overflow-y-auto pr-2 custom-scrollbar">
                                <label
                                    v-for="service in dbServices"
                                    :key="service.id"
                                    class="flex items-center p-3 rounded-xl border cursor-pointer transition-all"
                                    :class="form.services.find((s) => s.id === service.id) ? 'border-[#547558] bg-[#547558]/5' : 'border-gray-200 hover:border-[#547558]/50'">
                                    <input type="checkbox" :value="service" @change="toggleService(service)" class="hidden" />
                                    <div
                                        class="w-4 h-4 rounded border mr-3 flex items-center justify-center"
                                        :class="form.services.find((s) => s.id === service.id) ? 'bg-[#547558] border-[#547558]' : 'border-gray-300 bg-white'">
                                        <svg v-if="form.services.find((s) => s.id === service.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M5 13l4 4L19 7" stroke-width="3" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">{{ service.name }}</span>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="px-8 py-5 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3">
                    <button @click="isCreateModalOpen = false" class="px-5 py-2.5 rounded-full text-sm font-bold text-gray-500 hover:bg-gray-200 transition-colors">
                        Cancelar
                    </button>
                    <button
                        @click="submitCreate"
                        :disabled="form.processing || form.services.length === 0 || !form.date || !form.time"
                        class="bg-[#547558] text-white px-8 py-2.5 rounded-full text-sm font-bold shadow-md hover:bg-[#435e46] transition-all disabled:opacity-50">
                        {{ form.processing ? 'Agendando...' : 'Confirmar' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="isEditModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" @click="isEditModalOpen = false"></div>

            <div class="bg-white w-full max-w-xl rounded-[2rem] shadow-2xl relative z-10 overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xl font-bold text-gray-900">Editar Agendamento</h3>
                    <button @click="isEditModalOpen = false" class="text-gray-400 hover:text-gray-900 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2" /></svg>
                    </button>
                </div>

                <div class="px-8 py-6 overflow-y-auto custom-scrollbar">
                    <form @submit.prevent="submitEdit" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Cliente</label>
                                <input
                                    type="text"
                                    disabled
                                    :value="editingAppointment?.client.full_name"
                                    class="w-full rounded-xl border-gray-200 bg-gray-50 text-gray-500 text-sm cursor-not-allowed" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">WhatsApp</label>
                                <input
                                    type="text"
                                    disabled
                                    :value="formatPhoneDisplay(editingAppointment?.client.phone)"
                                    class="w-full rounded-xl border-gray-200 bg-gray-50 text-gray-500 text-sm cursor-not-allowed" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-blue-50/50 rounded-2xl border border-blue-100">
                            <div>
                                <label class="block text-xs font-bold text-blue-600 uppercase tracking-widest mb-2">Nova Data</label>
                                <select
                                    v-model="editForm.date"
                                    @change="editForm.time = ''"
                                    required
                                    class="w-full rounded-xl border-blue-200 focus:border-blue-500 focus:ring-blue-500/20 text-sm font-bold text-gray-700">
                                    <option value="" disabled>Selecione um dia</option>
                                    <option v-for="(times, dateKey) in availableSlots" :key="dateKey" :value="dateKey">
                                        {{ formatDateDisplay(dateKey) }}
                                    </option>
                                    <option
                                        v-if="editingAppointment && !availableSlots[String(editingAppointment.availability.date).substring(0, 10)]"
                                        :value="String(editingAppointment.availability.date).substring(0, 10)">
                                        {{ formatDateDisplay(editingAppointment.availability.date) }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-blue-600 uppercase tracking-widest mb-2">Novo Horário</label>
                                <select
                                    v-model="editForm.time"
                                    :disabled="!editForm.date"
                                    required
                                    class="w-full rounded-xl border-blue-200 focus:border-blue-500 focus:ring-blue-500/20 text-sm font-bold">
                                    <option value="" disabled>Selecione a hora</option>
                                    <option v-for="time in availableTimesEdit" :key="time" :value="time">{{ time }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Serviços Contratados</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[160px] overflow-y-auto pr-2 custom-scrollbar">
                                <label
                                    v-for="service in dbServices"
                                    :key="service.id"
                                    class="flex items-center p-3 rounded-xl border cursor-pointer transition-all"
                                    :class="editForm.services.find((s) => s.id === service.id) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'">
                                    <input type="checkbox" :value="service" @change="toggleEditService(service)" class="hidden" />
                                    <div
                                        class="w-4 h-4 rounded border mr-3 flex items-center justify-center"
                                        :class="editForm.services.find((s) => s.id === service.id) ? 'bg-blue-500 border-blue-500' : 'border-gray-300 bg-white'">
                                        <svg
                                            v-if="editForm.services.find((s) => s.id === service.id)"
                                            class="w-3 h-3 text-white"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path d="M5 13l4 4L19 7" stroke-width="3" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">{{ service.name }}</span>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="px-8 py-5 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3">
                    <button @click="isEditModalOpen = false" class="px-5 py-2.5 rounded-full text-sm font-bold text-gray-500 hover:bg-gray-200 transition-colors">Cancelar</button>
                    <button
                        @click="submitEdit"
                        :disabled="editForm.processing || editForm.services.length === 0 || !editForm.date || !editForm.time"
                        class="bg-blue-600 text-white px-8 py-2.5 rounded-full text-sm font-bold shadow-md hover:bg-blue-700 transition-all disabled:opacity-50">
                        {{ editForm.processing ? 'Salvando...' : 'Salvar e Notificar' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="confirmModal.isOpen" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" @click="confirmModal.isOpen = false"></div>

            <div class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl relative z-10 overflow-hidden transform transition-all text-center p-8 animate-fade-in-up">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-gray-100 text-gray-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ confirmModal.title }}</h3>
                <p class="text-sm text-gray-500 mb-8">{{ confirmModal.message }}</p>

                <div class="flex flex-col gap-3">
                    <button
                        @click="confirmModal.onConfirm"
                        :class="['w-full text-white px-6 py-3.5 rounded-full text-sm font-bold shadow-md transition-all', confirmModal.actionClass]">
                        {{ confirmModal.actionText }}
                    </button>
                    <button @click="confirmModal.isOpen = false" class="w-full text-gray-500 px-6 py-3.5 rounded-full text-sm font-bold hover:bg-gray-100 transition-colors">
                        Voltar
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.3s ease-out;
}
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
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
