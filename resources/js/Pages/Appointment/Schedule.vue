<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import SiteLayout from '@/Layouts/SiteLayout.vue';
import axios from 'axios';

const props = defineProps({
    dbServices: Array,
    availableSlots: Object,
    loggedClient: Object,
    flash: Object,
    editingAppointment: Object, // Nova prop para receber o agendamento em edição
});

// Dados do usuário logado (se houver)
const page = usePage();
const currentUser = computed(() => props.loggedClient);

// Modo Edição
const isEditing = computed(() => !!props.editingAppointment);

// Lógica do calendário
const currentDate = ref(new Date());
const currentMonth = ref(currentDate.value.getMonth());
const currentYear = ref(currentDate.value.getFullYear());

// Formatação do nome do mês e ano para exibição
const monthNameAndYear = computed(() => {
    const date = new Date(currentYear.value, currentMonth.value);
    const month = date.toLocaleString('pt-BR', { month: 'long' });
    return `${month.charAt(0).toUpperCase() + month.slice(1)} ${currentYear.value}`;
});

// Lógica para avançar para o próximo mês
const nextMonth = () => {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
};

// Lógica para voltar ao mês anterior
const prevMonth = () => {
    const realDate = new Date();
    if (currentYear.value === realDate.getFullYear() && currentMonth.value === realDate.getMonth()) {
        return;
    }

    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
};

// Gera os dias para preencher a grade (Grid) do calendário
const calendarDays = computed(() => {
    const year = currentYear.value;
    const month = currentMonth.value;

    const firstDayOfMonth = new Date(year, month, 1).getDay(); // 0 (Dom) a 6 (Sáb)
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const days = [];

    // Espaços vazios no início do mês
    for (let i = 0; i < firstDayOfMonth; i++) {
        days.push({ empty: true });
    }

    // Dias do mês
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    for (let i = 1; i <= daysInMonth; i++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
        const iterDate = new Date(dateStr + 'T00:00:00');

        const isPast = iterDate < today;
        const slotsAvailable = props.availableSlots[dateStr] || [];
        const hasSlots = slotsAvailable.length > 0;

        days.push({
            empty: false,
            date: dateStr,
            dayNumber: i,
            isPast: isPast,
            hasSlots: hasSlots,
            isSelectable: !isPast && hasSlots,
        });
    }
    return days;
});

// Filtra os horários disponíveis para a data selecionada
const availableTimesForSelectedDate = computed(() => {
    if (!form.date) return [];
    return props.availableSlots[form.date] || [];
});

// Seleciona uma data do calendário
const selectDate = (day) => {
    if (!day.isSelectable) return;
    form.date = day.date;
    form.time = ''; // Reseta o horário ao trocar de dia
};

// Estados e lógica do formulário
const step = ref(1);
const isLoaded = ref(false);

const isOtpModalOpen = ref(false);
const isSendingOtp = ref(false);
const otpError = ref('');

const form = useForm({
    services: [],
    date: '',
    time: '',
    name: '',
    whatsapp: '',
    otp: '',
});

// Máscara simples para o campo de telefone
const applyPhoneMask = (event) => {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);

    if (value.length > 2) {
        value = `(${value.slice(0, 2)}) ${value.slice(2)}`;
    }
    if (value.length > 9) {
        value = `${value.slice(0, 10)}-${value.slice(10)}`;
    }

    form.whatsapp = value;
};

const totalPrice = computed(() => {
    const total = form.services.reduce((acc, service) => {
        return acc + Number(service.price);
    }, 0);
    return `R$ ${total.toFixed(2).replace('.', ',')}`;
});

onMounted(() => {
    setTimeout(() => (isLoaded.value = true), 50);

    // Preenchimento de dados do cliente logado
    if (currentUser.value && currentUser.value.phone) {
        form.name = currentUser.value.full_name;

        let savedPhone = currentUser.value.phone;
        if (savedPhone.length === 11) {
            form.whatsapp = `(${savedPhone.slice(0, 2)}) ${savedPhone.slice(2, 7)}-${savedPhone.slice(7)}`;
        } else {
            form.whatsapp = savedPhone;
        }
    }

    // Se estiver em modo de edição, pré-seleciona os serviços do agendamento
    if (props.editingAppointment) {
        form.services = [...props.editingAppointment.services];
    }
});

// Navegação entre os passos do formulário
const nextStep = () => {
    if (step.value === 1 && form.services.length === 0) return alert('Selecione pelo menos um serviço.');
    if (step.value === 2 && (!form.date || !form.time)) return alert('Selecione uma data e um horário.');
    step.value++;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
const prevStep = () => {
    step.value--;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const goBack = () => {
    window.history.back();
};

// Seleção de serviços
const toggleService = (service) => {
    const index = form.services.findIndex((s) => s.id === service.id);
    if (index === -1) form.services.push(service);
    else form.services.splice(index, 1);
};

const isServiceSelected = (id) => form.services.some((s) => s.id === id);
const selectedServicesNames = computed(() => form.services.map((s) => s.name).join(' + '));

const handleFinalizeClick = async () => {
    if (currentUser.value) {
        submitAppointment();
        return;
    }

    const cleanWhatsapp = form.whatsapp.replace(/\D/g, '');
    if (cleanWhatsapp.length < 10) {
        otpError.value = 'Por favor, insira um número de telefone válido com DDD.';
        return;
    }

    isSendingOtp.value = true;
    otpError.value = '';

    try {
        await axios.post(route('appointments.sendOtp'), {
            whatsapp: cleanWhatsapp,
            name: form.name,
        });
        isOtpModalOpen.value = true;
    } catch (error) {
        otpError.value = 'Erro ao enviar código. Verifique seu número.';
    } finally {
        isSendingOtp.value = false;
    }
};

const submitAppointment = () => {
    // Define o método HTTP e a Rota com base no modo (Criação vs Edição)
    const method = isEditing.value ? 'put' : 'post';
    const url = isEditing.value ? route('appointments.update', props.editingAppointment.id) : route('appointments.store');

    form.transform((data) => ({
        ...data,
        whatsapp: data.whatsapp.replace(/\D/g, ''),
    }))[method](url, {
        onSuccess: (page) => {
            isOtpModalOpen.value = false;
            // A mensagem de sucesso do Controller de Edição/Criação ativará o Modal Verde
            if (page.props.flash?.success) {
                showSuccessModal.value = true;
            }
        },
        onError: (errors) => {
            if (errors.otp) otpError.value = errors.otp;
        },
    });
};

// Modal de sucesso
const showSuccessModal = ref(false);
</script>

<template>
    <Head :title="isEditing ? 'Editar Agendamento - Cabeleila' : 'Agendar Horário - Cabeleila'" />

    <SiteLayout>
        <div class="min-h-screen pt-28 pb-12 px-4 sm:px-6 bg-[url('/images/background-painel.png')] bg-cover bg-center bg-fixed relative w-full overflow-x-hidden">
            <div class="absolute inset-0 bg-[#FAF8F5]/60 backdrop-blur-[1px] z-0"></div>

            <div class="max-w-4xl mx-auto transition-all duration-1000 ease-out relative z-10 w-full" :class="isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">
                <div class="text-center mb-8">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                        {{ isEditing ? 'Reagendar sua Experiência' : 'Agende a sua Experiência' }}
                    </h1>
                    <p class="text-sm text-gray-700 max-w-md mx-auto">
                        {{ isEditing ? 'Ajuste os serviços e escolha um novo horário na nossa agenda.' : 'Siga os passos abaixo para reservar o seu momento de cuidado e beleza.' }}
                    </p>
                </div>

                <div class="flex items-start justify-center max-w-2xl mx-auto mb-10 px-2">
                    <div class="flex flex-col items-center w-20 sm:w-24 relative z-10">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-500 border-2"
                            :class="step >= 1 ? 'bg-[#547558] border-[#547558] text-white shadow-md' : 'bg-white border-gray-300 text-gray-400'">
                            1
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wide mt-2 text-center" :class="step >= 1 ? 'text-[#547558]' : 'text-gray-500'"
                            >Serviços</span
                        >
                    </div>

                    <div class="flex-1 h-1 mt-5 bg-gray-300 rounded-full overflow-hidden mx-1 sm:mx-2 shadow-inner">
                        <div class="h-full bg-[#547558] transition-all duration-500" :style="{ width: step >= 2 ? '100%' : '0%' }"></div>
                    </div>

                    <div class="flex flex-col items-center w-20 sm:w-24 relative z-10">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-500 border-2"
                            :class="step >= 2 ? 'bg-[#547558] border-[#547558] text-white shadow-md' : 'bg-white border-gray-300 text-gray-400'">
                            2
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wide mt-2 text-center" :class="step >= 2 ? 'text-[#547558]' : 'text-gray-500'"
                            >Data e Hora</span
                        >
                    </div>

                    <div class="flex-1 h-1 mt-5 bg-gray-300 rounded-full overflow-hidden mx-1 sm:mx-2 shadow-inner">
                        <div class="h-full bg-[#547558] transition-all duration-500" :style="{ width: step >= 3 ? '100%' : '0%' }"></div>
                    </div>

                    <div class="flex flex-col items-center w-20 sm:w-24 relative z-10">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-500 border-2"
                            :class="step >= 3 ? 'bg-[#547558] border-[#547558] text-white shadow-md' : 'bg-white border-gray-300 text-gray-400'">
                            3
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wide mt-2 text-center" :class="step >= 3 ? 'text-[#547558]' : 'text-gray-500'"
                            >Confirmação</span
                        >
                    </div>
                </div>

                <div v-if="step === 1" class="animate-fade-in">
                    <div class="grid sm:grid-cols-2 gap-3 sm:gap-4">
                        <button
                            v-for="service in dbServices"
                            :key="service.id"
                            @click="toggleService(service)"
                            type="button"
                            class="flex flex-col p-4 sm:p-5 rounded-2xl border-2 text-left transition-all duration-200 relative bg-white/90 backdrop-blur-sm group"
                            :class="
                                isServiceSelected(service.id)
                                    ? 'border-[#547558] ring-2 ring-[#547558]/10 shadow-md'
                                    : 'border-transparent shadow-sm hover:shadow-md hover:border-gray-300'
                            ">
                            <div v-if="isServiceSelected(service.id)" class="absolute top-4 right-4 text-[#547558]">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>

                            <h4 class="font-bold text-base sm:text-lg text-gray-900 mb-1 pr-6">{{ service.name }}</h4>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-xs text-gray-500 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" />
                                    </svg>
                                    {{ service.duration_minutes }} min
                                </span>
                            </div>
                            <span class="mt-auto font-bold text-[#547558] text-lg sm:text-xl">R$ {{ Number(service.price).toFixed(2).replace('.', ',') }}</span>
                        </button>
                    </div>

                    <div class="mt-8 flex flex-col-reverse sm:flex-row justify-between items-center gap-4 px-2 w-full">
                        <button
                            @click="goBack"
                            type="button"
                            class="w-full sm:w-auto text-gray-600 text-sm sm:text-base font-bold hover:text-gray-900 transition-colors flex justify-center items-center gap-1 py-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-width="2" />
                            </svg>
                            Voltar
                        </button>
                        <button
                            @click="nextStep"
                            type="button"
                            :disabled="form.services.length === 0"
                            class="w-full sm:w-auto bg-[#547558] text-white px-8 sm:px-12 py-3.5 sm:py-4 rounded-full font-bold text-sm sm:text-base shadow-lg hover:bg-[#435e46] transition-all transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2">
                            Prosseguir
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div v-if="step === 2" class="animate-fade-in">
                    <div class="bg-white/90 backdrop-blur-sm p-6 sm:p-8 rounded-3xl shadow-lg border border-gray-100 flex flex-col lg:flex-row gap-8 lg:gap-12">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="font-bold text-lg text-gray-900 capitalize">{{ monthNameAndYear }}</h3>
                                <div class="flex gap-2">
                                    <button @click="prevMonth" class="p-2 rounded-full hover:bg-gray-100 text-gray-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </button>
                                    <button @click="nextMonth" class="p-2 rounded-full hover:bg-gray-100 text-gray-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-7 gap-1 text-center mb-2">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Dom</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Seg</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Ter</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Qua</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Qui</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sex</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sáb</span>
                            </div>

                            <div class="grid grid-cols-7 gap-1 sm:gap-2">
                                <template v-for="(day, index) in calendarDays" :key="index">
                                    <div v-if="day.empty" class="p-2"></div>
                                    <button
                                        v-else
                                        @click="selectDate(day)"
                                        :disabled="!day.isSelectable"
                                        class="aspect-square flex items-center justify-center rounded-full text-sm font-medium transition-all relative"
                                        :class="[
                                            form.date === day.date
                                                ? 'bg-[#547558] text-white shadow-md font-bold scale-105'
                                                : day.isSelectable
                                                  ? 'bg-gray-50 text-gray-700 hover:bg-[#547558]/10 hover:text-[#547558]'
                                                  : 'text-gray-300 cursor-not-allowed bg-transparent',
                                        ]">
                                        {{ day.dayNumber }}

                                        <span v-if="day.isSelectable && form.date !== day.date" class="absolute bottom-1 w-1 h-1 bg-[#547558] rounded-full opacity-50"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="hidden lg:block w-px bg-gray-100"></div>
                        <div class="block lg:hidden h-px w-full bg-gray-100"></div>

                        <div class="flex-1 flex flex-col">
                            <label class="block text-sm font-bold text-[#547558] mb-6 uppercase tracking-[0.1em]">
                                {{ form.date ? `Horários para ${form.date.split('-').reverse().join('/')}` : 'Selecione uma data' }}
                            </label>

                            <div v-if="!form.date" class="flex-1 flex flex-col items-center justify-center text-gray-400 py-8">
                                <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-sm font-medium">Escolha um dia no calendário</p>
                            </div>

                            <div v-else-if="availableTimesForSelectedDate.length === 0" class="flex-1 flex items-center justify-center text-gray-500 text-sm py-8">
                                Nenhum horário disponível neste dia.
                            </div>

                            <div v-else class="grid grid-cols-3 gap-3 overflow-y-auto max-h-[250px] pr-2 custom-scrollbar">
                                <button
                                    v-for="time in availableTimesForSelectedDate"
                                    :key="time"
                                    @click="form.time = time"
                                    class="py-3 rounded-xl text-sm font-bold transition-all border-2"
                                    :class="
                                        form.time === time ? 'bg-[#547558] border-[#547558] text-white shadow-md' : 'bg-white border-gray-200 text-gray-600 hover:border-[#547558]'
                                    ">
                                    {{ time }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col-reverse sm:flex-row justify-end items-center gap-4 px-2">
                        <button
                            @click="prevStep"
                            class="w-full sm:w-auto text-gray-600 text-sm sm:text-base font-bold hover:text-gray-900 transition-colors flex justify-center items-center gap-1 py-2 sm:mr-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-width="2" /></svg> Voltar
                        </button>
                        <button
                            @click="nextStep"
                            :disabled="!form.date || !form.time"
                            class="w-full sm:w-auto bg-[#547558] text-white px-8 py-3.5 rounded-full text-sm sm:text-base font-bold shadow-md hover:bg-[#435e46] transition-all transform hover:-translate-y-0.5 disabled:opacity-50 flex justify-center items-center gap-2">
                            Último Passo
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-width="2.5" /></svg>
                        </button>
                    </div>
                </div>

                <div v-if="step === 3" class="animate-fade-in">
                    <div class="flex flex-col lg:grid lg:grid-cols-5 lg:items-stretch gap-6 sm:gap-8">
                        <div class="lg:col-span-3 lg:order-1 flex flex-col h-full">
                            <form
                                @submit.prevent="handleFinalizeClick"
                                class="h-full bg-white/90 backdrop-blur-sm p-6 sm:p-8 rounded-3xl shadow-lg border border-gray-100 space-y-6">
                                <div v-if="currentUser" class="mb-2 p-4 bg-[#547558]/10 text-[#547558] rounded-2xl flex items-center gap-3">
                                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="font-medium text-sm leading-tight">Bem-vinda de volta, {{ form.name.split(' ')[0] }}! Seus dados já estão preenchidos.</span>
                                    <Link
                                        :href="route('clients.logout')"
                                        method="post"
                                        as="button"
                                        class="text-xs font-bold uppercase tracking-wider underline hover:text-[#435e46] transition-colors">
                                        Clique aqui para sair
                                    </Link>
                                </div>
                                <div>
                                    <label class="block text-sm sm:text-base font-bold text-gray-800 mb-2">Nome Completo</label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        :readonly="!!currentUser"
                                        placeholder="Digite seu nome completo"
                                        required
                                        class="w-full rounded-xl sm:rounded-2xl border-gray-300 focus:border-[#547558] focus:ring focus:ring-[#547558]/20 px-5 sm:px-6 py-4 transition-all text-sm sm:text-base read-only:bg-gray-50 read-only:text-gray-500 read-only:border-gray-200" />
                                </div>

                                <div>
                                    <label class="block text-sm sm:text-base font-bold text-gray-800 mb-2">WhatsApp</label>
                                    <input
                                        v-model="form.whatsapp"
                                        @input="applyPhoneMask"
                                        type="tel"
                                        :readonly="!!currentUser"
                                        placeholder="(11) 90000-0000"
                                        required
                                        class="w-full rounded-xl sm:rounded-2xl border-gray-300 focus:border-[#547558] focus:ring focus:ring-[#547558]/20 px-5 sm:px-6 py-4 transition-all text-sm sm:text-base read-only:bg-gray-50 read-only:text-gray-500 read-only:border-gray-200" />
                                </div>

                                <p v-if="otpError && !isOtpModalOpen" class="text-red-500 text-sm font-medium">{{ otpError }}</p>

                                <div class="flex flex-col-reverse sm:flex-row justify-end items-center gap-4 mt-6">
                                    <button
                                        @click="prevStep"
                                        type="button"
                                        class="w-full sm:w-auto text-gray-600 text-sm sm:text-base font-bold hover:text-gray-900 transition-colors flex items-center justify-center gap-1 py-2 sm:mr-4">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-width="2" /></svg>
                                        Voltar
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="isSendingOtp || form.processing"
                                        class="w-full sm:w-auto bg-[#547558] text-white px-8 sm:px-10 py-3.5 rounded-full text-sm sm:text-base font-bold shadow-md hover:bg-[#435e46] transition-all transform hover:-translate-y-0.5 disabled:opacity-50 flex items-center justify-center gap-2">
                                        {{
                                            isSendingOtp ? 'Processando...' : currentUser ? (isEditing ? 'Confirmar Alteração' : 'Finalizar Agendamento') : 'Confirmar Agendamento'
                                        }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="lg:col-span-2 order-1 lg:order-2 flex flex-col h-full">
                            <div class="h-full bg-[#547558] text-white rounded-3xl shadow-xl p-6 sticky top-28 overflow-hidden">
                                <div class="absolute top-0 right-0 -mr-6 -mt-6 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
                                <div class="flex flex-col justify-between h-full">
                                    <div>
                                        <h3 class="text-xl font-bold mb-4 border-b border-white/20 pb-3">Resumo do Agendamento</h3>
                                        <div class="space-y-3">
                                            <div class="flex items-start gap-2">
                                                <div class="mt-0.5">
                                                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M6.5 6.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5Zm0 0L17.5 17.5m0-11a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5Zm0 0L6.5 17.5" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-sm uppercase opacity-70 font-bold tracking-tighter mb-0.5">Serviços</p>
                                                    <p class="font-bold text-md leading-tight">{{ selectedServicesNames }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-2">
                                                <div class="mt-0.5">
                                                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-sm uppercase opacity-70 font-bold tracking-tighter mb-0.5">Data</p>
                                                    <p class="font-bold text-md">{{ form.date.split('-').reverse().join('/') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-white/10 p-3 rounded-xl flex flex-col gap-2 mt-4">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-bold uppercase opacity-90 tracking-widest">Horário</span>
                                            <span class="text-lg font-black">{{ form.time }}</span>
                                        </div>
                                        <div class="h-px w-full bg-white/20 my-1"></div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-bold uppercase opacity-90 tracking-widest">Total</span>
                                            <span class="text-lg font-black">{{ totalPrice }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isOtpModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="isOtpModalOpen = false"></div>

            <div class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl relative z-10 p-8 text-center animate-fade-in-up">
                <div class="w-16 h-16 bg-[#547558]/10 text-[#547558] rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>

                <h3 class="text-2xl font-bold text-gray-900 mb-2">Confirme sua identidade</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Enviamos um código de 6 dígitos para o WhatsApp <strong>{{ form.whatsapp }}</strong
                    >.
                </p>

                <input
                    v-model="form.otp"
                    type="text"
                    maxlength="6"
                    placeholder="000000"
                    class="w-full text-center text-3xl font-bold tracking-[0.5em] rounded-xl border-gray-300 focus:border-[#547558] focus:ring focus:ring-[#547558]/20 py-4 mb-2 transition-all" />

                <p v-if="otpError || form.errors.otp" class="text-red-500 text-sm font-medium mb-6">
                    {{ otpError || form.errors.otp }}
                </p>

                <div class="space-y-3 mt-6">
                    <button
                        @click="submitAppointment"
                        :disabled="form.otp.length < 6 || form.processing"
                        class="w-full bg-[#547558] text-white py-4 rounded-full font-bold shadow-md hover:bg-[#435e46] transition-all disabled:opacity-50">
                        {{ form.processing ? 'Validando...' : 'Validar e Agendar' }}
                    </button>
                    <button @click="isOtpModalOpen = false" class="w-full text-gray-500 text-sm font-bold py-2 hover:text-gray-900 transition-colors">Cancelar</button>
                </div>
            </div>
        </div>

        <div v-if="showSuccessModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-[#547558] bg-opacity-95 backdrop-blur-md"></div>

            <div class="bg-[#FAF8F5] rounded-[3rem] p-10 sm:p-14 text-center relative z-10 max-w-lg w-full shadow-2xl animate-fade-in-up">
                <div class="w-24 h-24 bg-[#547558]/10 text-[#547558] rounded-full flex items-center justify-center mx-auto mb-8 relative">
                    <div class="absolute inset-0 border-4 border-[#547558] rounded-full opacity-20 animate-ping"></div>
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h2 class="text-3xl sm:text-4xl font-black text-gray-900 mb-4">{{ isEditing ? 'Alteração Confirmada!' : `Tudo Certo, ${form.name.split(' ')[0]}!` }}</h2>
                <p class="text-gray-600 mb-2 text-lg">{{ isEditing ? 'Seu agendamento foi atualizado com sucesso.' : 'O seu momento está reservado.' }}</p>
                <p class="text-gray-500 mb-10 text-sm">Enviamos os detalhes para o seu WhatsApp.</p>

                <Link
                    href="/meus-agendamentos"
                    class="bg-[#547558] text-white px-8 py-4 rounded-full font-bold text-lg shadow-xl hover:bg-[#435e46] transition-all block w-full hover:-translate-y-1">
                    Ver Meus Agendamentos
                </Link>
            </div>
        </div>
    </SiteLayout>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #e5e7eb;
    border-radius: 20px;
}

.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
.animate-fade-in-up {
    animation: fadeInUp 0.4s ease-out;
}
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
