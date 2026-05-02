<script setup>
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import SiteLayout from '@/Layouts/SiteLayout.vue';
import axios from 'axios';

const props = defineProps({
    loggedClient: Object,
    appointments: Array,
});

const currentUser = computed(() => props.loggedClient);
const isLoaded = ref(false);

const isOtpModalOpen = ref(false);
const isSendingOtp = ref(false);
const otpError = ref('');

// Controle das Abas (Tabs)
const activeTab = ref('upcoming'); // 'upcoming' ou 'past'

const form = useForm({
    name: '',
    whatsapp: '',
    otp: '',
});

// Formatações e Máscaras

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

const maskedUserPhone = computed(() => {
    if (!currentUser.value?.phone) return '';
    let p = currentUser.value.phone;
    if (p.length === 11) {
        return `(${p.slice(0, 2)}) ${p.slice(2, 7)}-${p.slice(7)}`;
    }
    return p;
});

const safeDate = (isoString) => isoString.substring(0, 10);

const formatDate = (isoString) => {
    return safeDate(isoString).split('-').reverse().join('/');
};

const getDay = (isoString) => {
    return safeDate(isoString).split('-')[2];
};

const getMonthShort = (isoString) => {
    const [y, m, d] = safeDate(isoString).split('-');
    const date = new Date(y, m - 1, d);
    return date.toLocaleDateString('pt-BR', { month: 'short' }).replace('.', '');
};

const isPastAppointment = (isoDateString, timeString) => {
    const appointmentDate = new Date(`${safeDate(isoDateString)}T${timeString}`);
    return appointmentDate < new Date();
};

const calculateTotal = (services) => {
    const total = services.reduce((acc, service) => acc + Number(service.price), 0);
    return `R$ ${total.toFixed(2).replace('.', ',')}`;
};

// Mapeamento de status para rótulos e classes de cor
const formatStatus = (status) => {
    const map = {
        pending: { label: 'Pendente', colorClass: 'bg-yellow-50 text-yellow-700 border-yellow-200' },
        confirmed: { label: 'Confirmado', colorClass: 'bg-green-50 text-green-700 border-green-200' },
        canceled: { label: 'Cancelado', colorClass: 'bg-red-50 text-red-700 border-red-200' },
        completed: { label: 'Concluído', colorClass: 'bg-gray-50 text-gray-700 border-gray-200' },
    };
    return map[status] || { label: status, colorClass: 'bg-gray-50 text-gray-700 border-gray-200' };
};

// Filtragem e Ordenação de Agendamentos
const upcomingAppointments = computed(() => {
    return props.appointments
        .filter((app) => !isPastAppointment(app.availability.date, app.availability.hour))
        .sort((a, b) => {
            // Ordem Crescente (do mais próximo para o mais distante)
            const dateA = new Date(`${safeDate(a.availability.date)}T${a.availability.hour}`);
            const dateB = new Date(`${safeDate(b.availability.date)}T${b.availability.hour}`);
            return dateA - dateB;
        });
});

// Agendamentos passados ordenados do mais recente para o mais antigo
const pastAppointments = computed(() => {
    return props.appointments
        .filter((app) => isPastAppointment(app.availability.date, app.availability.hour))
        .sort((a, b) => {
            // Ordem Decrescente (do mais recente para o mais antigo)
            const dateA = new Date(`${safeDate(a.availability.date)}T${a.availability.hour}`);
            const dateB = new Date(`${safeDate(b.availability.date)}T${b.availability.hour}`);
            return dateB - dateA;
        });
});

// Lista atual baseada na aba ativa
const currentList = computed(() => {
    return activeTab.value === 'upcoming' ? upcomingAppointments.value : pastAppointments.value;
});

onMounted(() => {
    setTimeout(() => (isLoaded.value = true), 50);
});

// Lógica para permitir edição apenas para agendamentos futuros com status "pending" ou "confirmed"
const canEdit = (appointment) => {
    const statusPermitido = ['pending', 'confirmed'].includes(appointment.status);
    const dataAgendamento = new Date(`${safeDate(appointment.availability.date)}T${appointment.availability.hour}`);
    const hoje = new Date();

    // Diferença em milissegundos para 48 horas (2 dias)
    const quarentaEOitoHoras = 48 * 60 * 60 * 1000;
    const prazoValido = dataAgendamento - hoje >= quarentaEOitoHoras;

    return statusPermitido && prazoValido;
};

// Função para lidar com o clique de edição
const handleEditClick = (appointment) => {
    if (canEdit(appointment)) {
        router.get(route('agendar', { edit_id: appointment.id }));
    } else {
        // Bloqueia e abre o modal de contato
        blockedAppointment.value = appointment;
        isBlockModalOpen.value = true;
    }
};
// Lógica para bloqueio de edição e contato via WhatsApp para agendamentos com menos de 48h de antecedência
const isBlockModalOpen = ref(false);
const blockedAppointment = ref(null);

// Gera o link do WhatsApp já com a mensagem pronta
const whatsappLink = computed(() => {
    if (!blockedAppointment.value) return '#';

    const date = formatDate(blockedAppointment.value.availability.date);
    const time = blockedAppointment.value.availability.hour.substring(0, 5);
    const name = currentUser.value ? currentUser.value.full_name.split(' ')[0] : 'Cliente';

    const message = `Olá, Leila! Sou a ${name}. Gostaria de ver a possibilidade de alterar meu agendamento do dia ${date} às ${time}. Sei que faltam menos de 48h, podemos conversar?`;

    // Converte o texto para o formato de URL e insere o número do salão
    const encodedMessage = encodeURIComponent(message);
    const adminNumber = usePage().props.contact.whatsappLink;
    return `https://wa.me/${adminNumber}?text=${encodedMessage}`;
});

// Lógica de Acesso via OTP
const handleAccessClick = async () => {
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

const submitLogin = () => {
    form.transform((data) => ({
        ...data,
        whatsapp: data.whatsapp.replace(/\D/g, ''),
    })).post(route('clients.loginOtp'), {
        preserveScroll: true,
        onSuccess: () => {
            isOtpModalOpen.value = false;
        },
        onError: (errors) => {
            if (errors.otp) otpError.value = errors.otp;
        },
    });
};
</script>

<template>
    <Head title="Meus Agendamentos - Cabeleila" />

    <SiteLayout>
        <div class="min-h-screen pt-28 pb-12 px-4 sm:px-6 bg-[url('/images/background-painel.png')] bg-cover bg-center bg-fixed relative w-full overflow-x-hidden">
            <div class="absolute inset-0 bg-[#FAF8F5]/60 backdrop-blur-[1px] z-0"></div>

            <div class="max-w-4xl mx-auto transition-all duration-1000 ease-out relative z-10 w-full" :class="isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">
                <div class="text-center mb-10">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Meus Agendamentos</h1>
                    <p class="text-sm text-gray-700 max-w-md mx-auto">Consulte seu histórico e próximos horários reservados no salão.</p>
                </div>

                <div v-if="!currentUser" class="animate-fade-in max-w-2xl mx-auto">
                    <form @submit.prevent="handleAccessClick" class="bg-white/90 backdrop-blur-sm p-6 sm:p-10 rounded-3xl shadow-lg border border-gray-100 space-y-6">
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 bg-[#547558]/10 text-[#547558] rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Acesse seus dados</h2>
                            <p class="text-sm text-gray-500 mt-2">Informe seu nome e WhatsApp para visualizar seus agendamentos.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">Como você se chama?</label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Digite seu nome completo"
                                required
                                class="w-full rounded-xl sm:rounded-2xl border-gray-300 focus:border-[#547558] focus:ring focus:ring-[#547558]/20 px-5 sm:px-6 py-4 transition-all text-sm sm:text-base" />
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">Seu WhatsApp</label>
                            <input
                                v-model="form.whatsapp"
                                @input="applyPhoneMask"
                                type="tel"
                                placeholder="(11) 90000-0000"
                                required
                                class="w-full rounded-xl sm:rounded-2xl border-gray-300 focus:border-[#547558] focus:ring focus:ring-[#547558]/20 px-5 sm:px-6 py-4 transition-all text-sm sm:text-base" />
                        </div>

                        <p v-if="otpError && !isOtpModalOpen" class="text-red-500 text-sm font-medium text-center">{{ otpError }}</p>

                        <div class="flex justify-end items-center pt-4">
                            <button
                                type="submit"
                                :disabled="isSendingOtp || form.processing"
                                class="w-full sm:w-auto bg-[#547558] text-white px-8 py-3.5 rounded-full text-base font-bold shadow-md hover:bg-[#435e46] transition-all transform hover:-translate-y-0.5 disabled:opacity-50 flex items-center justify-center gap-2">
                                {{ isSendingOtp ? 'Enviando Código...' : 'Acessar Agendamentos' }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                <div v-else class="animate-fade-in max-w-3xl mx-auto">
                    <div
                        class="mb-8 p-4 bg-[#547558]/10 text-[#547558] rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4 border border-[#547558]/20 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-[#547558] text-white flex items-center justify-center font-bold text-xl uppercase shrink-0 shadow-md">
                                {{ currentUser.full_name.charAt(0) }}
                            </div>
                            <div>
                                <span class="font-bold text-base leading-tight block text-gray-900">Bem-vinda de volta, {{ currentUser.full_name.split(' ')[0] }}!</span>
                                <span class="text-sm font-medium opacity-80">{{ maskedUserPhone }}</span>
                            </div>
                        </div>
                        <Link
                            :href="route('clients.logout')"
                            method="post"
                            as="button"
                            class="flex-shrink-0 text-xs font-bold uppercase tracking-wider underline hover:text-[#435e46] transition-colors ml-16 sm:ml-0">
                            Sair da conta
                        </Link>
                    </div>

                    <div v-if="appointments.length === 0" class="bg-white/90 backdrop-blur-sm rounded-3xl p-10 text-center border border-gray-100 shadow-lg">
                        <div class="w-20 h-20 bg-[#547558]/5 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-[#547558] opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Você ainda não tem agendamentos</h3>
                        <p class="text-gray-500 mb-8 max-w-sm mx-auto">Que tal reservar um momento especial para cuidar de você hoje?</p>
                        <Link
                            :href="route('agendar')"
                            class="inline-flex bg-[#547558] text-white px-8 py-3.5 rounded-full font-bold shadow-md hover:bg-[#435e46] transition-all transform hover:-translate-y-0.5">
                            Fazer meu primeiro agendamento
                        </Link>
                    </div>

                    <div v-else>
                        <div class="flex bg-white/60 p-1.5 rounded-full border border-gray-200 mb-8 mx-auto max-w-sm shadow-sm backdrop-blur-md">
                            <button
                                @click="activeTab = 'upcoming'"
                                :class="activeTab === 'upcoming' ? 'bg-[#547558] text-white shadow-md' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/50'"
                                class="flex-1 py-2.5 rounded-full text-sm font-bold transition-all duration-300">
                                Próximos
                                <span v-if="upcomingAppointments.length > 0" class="ml-1 text-[10px] bg-white/20 px-2 py-0.5 rounded-full">{{ upcomingAppointments.length }}</span>
                            </button>
                            <button
                                @click="activeTab = 'past'"
                                :class="activeTab === 'past' ? 'bg-[#547558] text-white shadow-md' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100/50'"
                                class="flex-1 py-2.5 rounded-full text-sm font-bold transition-all duration-300">
                                Histórico
                            </button>
                        </div>

                        <div v-if="currentList.length === 0" class="bg-white/50 backdrop-blur-sm rounded-3xl p-10 text-center border border-gray-100 shadow-sm mt-4">
                            <p class="text-gray-500 font-medium">
                                {{
                                    activeTab === 'upcoming'
                                        ? 'Você não tem nenhum agendamento futuro marcado no momento.'
                                        : 'Você ainda não possui um histórico de serviços realizados.'
                                }}
                            </p>
                            <Link v-if="activeTab === 'upcoming'" :href="route('agendar')" class="inline-block mt-4 text-[#547558] font-bold underline hover:text-[#435e46]">
                                Agendar um novo horário
                            </Link>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="app in currentList"
                                :key="app.id"
                                class="bg-white/90 backdrop-blur-sm rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all relative overflow-hidden"
                                :class="isPastAppointment(app.availability.date, app.availability.hour) ? 'border-gray-200 opacity-75' : 'border-gray-100'">
                                <div class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 bg-[#547558]/5 rounded-full blur-xl pointer-events-none"></div>

                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 border-b border-gray-100 pb-4 relative z-10">
                                    <div class="flex items-center gap-3 sm:gap-4">
                                        <div
                                            class="bg-[#547558] text-white w-12 h-12 rounded-xl flex flex-col items-center justify-center shrink-0 shadow-sm"
                                            :class="activeTab === 'past' ? 'opacity-80 grayscale-[20%]' : ''">
                                            <span class="text-[9px] font-bold uppercase tracking-widest opacity-90">{{ getMonthShort(app.availability.date) }}</span>
                                            <span class="text-lg font-black leading-none mt-0.5">{{ getDay(app.availability.date) }}</span>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                                                {{ app.availability.hour.substring(0, 5) }}
                                            </h4>
                                            <p class="text-xs text-gray-500 font-medium mt-0.5">{{ formatDate(app.availability.date) }}</p>
                                        </div>
                                    </div>

                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border" :class="formatStatus(app.status).colorClass">
                                        {{ formatStatus(app.status).label }}
                                    </span>
                                </div>

                                <div class="space-y-2 relative z-10 mb-4">
                                    <div v-for="service in app.services" :key="service.id" class="flex justify-between items-center text-sm">
                                        <div class="flex items-center gap-2 text-gray-700 font-medium">
                                            <svg class="w-3.5 h-3.5 opacity-50 text-[#547558]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z" />
                                            </svg>
                                            {{ service.name }}
                                        </div>
                                        <span class="text-gray-900 font-semibold">R$ {{ Number(service.price).toFixed(2).replace('.', ',') }}</span>
                                    </div>
                                </div>

                                <div class="border-t border-gray-100 flex flex-col sm:flex-row justify-between sm:items-center bg-gray-50/50 -mx-5 -mb-5 px-5 py-3 gap-3">
                                    <div class="flex justify-between items-center w-full sm:w-auto flex-1">
                                        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-widest">Total</span>
                                        <span class="text-lg font-black text-[#547558] sm:ml-2">{{ calculateTotal(app.services) }}</span>
                                    </div>

                                    <div
                                        v-if="!isPastAppointment(app.availability.date, app.availability.hour) && ['pending', 'confirmed'].includes(app.status)"
                                        class="w-full sm:w-auto">
                                        <button
                                            type="button"
                                            @click="handleEditClick(app)"
                                            class="w-full sm:w-auto text-xs font-bold text-[#547558] border border-[#547558]/30 px-4 py-2 rounded-full hover:bg-[#547558] hover:text-white transition-all">
                                            Editar Agendamento
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de OTP -->
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

                <p v-if="otpError || form.errors.otp" class="text-red-500 text-sm font-medium mb-6">{{ otpError || form.errors.otp }}</p>

                <div class="space-y-3 mt-6">
                    <button
                        @click="submitLogin"
                        :disabled="form.otp.length < 6 || form.processing"
                        class="w-full bg-[#547558] text-white py-4 rounded-full font-bold shadow-md hover:bg-[#435e46] transition-all disabled:opacity-50">
                        {{ form.processing ? 'Validando...' : 'Acessar Conta' }}
                    </button>
                    <button @click="isOtpModalOpen = false" class="w-full text-gray-500 text-sm font-bold py-2 hover:text-gray-900 transition-colors">Cancelar</button>
                </div>
            </div>
        </div>
        <!-- Modal de bloqueio -->
        <div v-if="isBlockModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="isBlockModalOpen = false"></div>

            <div class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl relative z-10 p-8 text-center animate-fade-in-up">
                <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <h3 class="text-2xl font-bold text-gray-900 mb-2">Atenção ao Prazo</h3>
                <p class="text-sm text-gray-600 mb-6">Alterações com menos de <strong>48 horas</strong> de antecedência precisam ser combinadas diretamente com a nossa equipe.</p>

                <div class="space-y-3 mt-6">
                    <a
                        :href="whatsappLink"
                        target="_blank"
                        class="w-full bg-[#25D366] text-white py-3.5 rounded-full font-bold shadow-md hover:bg-[#20bd5a] transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>
                        Falar no WhatsApp
                    </a>
                    <button @click="isBlockModalOpen = false" class="w-full text-gray-500 text-sm font-bold py-2 hover:text-gray-900 transition-colors">Voltar</button>
                </div>
            </div>
        </div>
    </SiteLayout>
</template>

<style scoped>
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
