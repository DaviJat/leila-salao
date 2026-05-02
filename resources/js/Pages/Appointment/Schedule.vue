<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import SiteLayout from '@/Layouts/SiteLayout.vue';

// Dados simulados para serviços, datas e horários disponíveis (substituir por chamadas reais à API)
const dbServices = [
    { id: 1, name: 'Corte e Visagismo', price: 'R$ 80,00', duration: '45 min' },
    { id: 2, name: 'Coloração e Mechas', price: 'A partir de R$ 250', duration: '2h 30m' },
    { id: 3, name: 'Tratamento Capilar', price: 'R$ 120,00', duration: '1h' },
    { id: 4, name: 'Penteado e Make', price: 'R$ 180,00', duration: '1h 30m' },
    { id: 5, name: 'Escova Modeladora', price: 'R$ 60,00', duration: '40 min' },
    { id: 6, name: 'Spa do Couro Cabeludo', price: 'R$ 100,00', duration: '50 min' },
];

const dbAvailableDates = [
    { date: '2026-05-02', label: 'Sáb, 02 Mai' },
    { date: '2026-05-04', label: 'Seg, 04 Mai' },
    { date: '2026-05-05', label: 'Ter, 05 Mai' },
    { date: '2026-05-06', label: 'Qua, 06 Mai' },
];

const dbAvailableTimes = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];

// Estados reativos para controle do formulário e navegação
const step = ref(1);
const isLoaded = ref(false);

const form = useForm({
    services: [],
    date: '',
    time: '',
    name: '',
    whatsapp: '',
});

onMounted(() => {
    setTimeout(() => (isLoaded.value = true), 50);
});

// Funções para navegação entre os passos, seleção de serviços e submissão do formulário
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

const toggleService = (service) => {
    const index = form.services.findIndex((s) => s.id === service.id);
    if (index === -1) {
        form.services.push(service);
    } else {
        form.services.splice(index, 1);
    }
};

const isServiceSelected = (id) => form.services.some((s) => s.id === id);
const selectedServicesNames = computed(() => form.services.map((s) => s.name).join(' + '));

const submitBooking = () => {
    // to-do: form.post(route('appointments.store'));
    alert(`Agendamento confirmado para ${form.name}!`);
};
</script>

<template>
    <Head title="Agendar Horário - Cabeleila" />

    <SiteLayout>
        <div class="min-h-screen pt-28 pb-12 px-4 sm:px-6 bg-[url('/images/background-painel.png')] bg-cover bg-center bg-fixed relative w-full overflow-x-hidden">
            <div class="absolute inset-0 bg-[#FAF8F5]/60 backdrop-blur-[1px] z-0"></div>

            <div class="max-w-4xl mx-auto transition-all duration-1000 ease-out relative z-10 w-full" :class="isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">
                <div class="text-center mb-4 lg:mb-8">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">Agende a sua Experiência</h1>
                    <p class="text-sm text-gray-700 max-w-md mx-auto">Siga os passos abaixo para reservar o seu momento de cuidado e beleza.</p>
                </div>

                <!-- Passos -->
                <div class="flex items-start justify-center max-w-2xl mx-auto mb-6 lg:mb-10 px-2">
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

                <!-- Passo 1: Seleção de Serviços -->
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
                                    {{ service.duration }}
                                </span>
                            </div>
                            <span class="mt-auto font-bold text-[#547558] text-lg sm:text-xl">{{ service.price }}</span>
                        </button>
                    </div>

                    <div class="mt-8 flex justify-center">
                        <button
                            @click="nextStep"
                            :disabled="form.services.length === 0"
                            class="bg-[#547558] text-white px-8 py-3 rounded-full font-bold text-sm sm:text-base shadow-md hover:bg-[#435e46] transition-all transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            Prosseguir
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Passo 2: Escolha de Data e Hora -->
                <div v-if="step === 2" class="animate-fade-in">
                    <div class="bg-white/90 backdrop-blur-sm p-6 sm:p-8 rounded-3xl shadow-lg border border-gray-100">
                        <div class="mb-8 text-center">
                            <label class="block text-xs font-bold text-[#547558] mb-4 uppercase tracking-[0.2em]">Escolha o Melhor Dia</label>
                            <div class="flex gap-3 overflow-x-auto pb-4 snap-x hide-scrollbar sm:justify-center">
                                <button
                                    v-for="day in dbAvailableDates"
                                    :key="day.date"
                                    @click="
                                        form.date = day.date;
                                        form.time = '';
                                    "
                                    class="snap-start shrink-0 flex flex-col items-center justify-center p-4 rounded-[1.5rem] border-2 min-w-[100px] transition-all"
                                    :class="
                                        form.date === day.date
                                            ? 'border-[#547558] bg-[#547558] text-white shadow-md'
                                            : 'border-gray-200 bg-white text-gray-500 hover:border-[#547558]/30'
                                    ">
                                    <span class="text-[10px] uppercase font-bold mb-1">{{ day.label.split(',')[0] }}</span>
                                    <span class="text-2xl font-black">{{ day.label.split(' ')[2] }}</span>
                                    <span class="text-[10px] font-medium">{{ day.label.split(' ')[3] }}</span>
                                </button>
                            </div>
                        </div>

                        <div v-if="form.date" class="animate-fade-in-up text-center">
                            <label class="block text-xs font-bold text-[#547558] mb-4 uppercase tracking-[0.2em]">Horários em {{ form.date.split('-').reverse().join('/') }}</label>
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                <button
                                    v-for="time in dbAvailableTimes"
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

                    <div class="mt-8 flex justify-between items-center px-2">
                        <button @click="prevStep" class="text-gray-600 text-sm sm:text-base font-bold hover:text-gray-900 transition-colors flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-width="2" /></svg> Voltar
                        </button>
                        <button
                            @click="nextStep"
                            :disabled="!form.date || !form.time"
                            class="bg-[#547558] text-white px-4 sm:px-6 py-3 rounded-full text-sm sm:text-base font-bold shadow-md hover:bg-[#435e46] transition-all transform hover:-translate-y-0.5 disabled:opacity-50 flex items-center gap-2">
                            Último Passo
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-width="2.5" /></svg>
                        </button>
                    </div>
                </div>

                <!-- Passo 3: Informações do Cliente -->
                <div v-if="step === 3" class="animate-fade-in">
                    <div class="grid lg:grid-cols-5 gap-6 sm:gap-8">
                        <div class="lg:col-span-3">
                            <form @submit.prevent="submitBooking" class="bg-white/90 backdrop-blur-sm p-6 sm:p-8 rounded-3xl shadow-lg border border-gray-100 space-y-6">
                                <div>
                                    <label class="block text-xs sm:text-sm font-bold text-gray-900 mb-2">Nome Completo</label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Como gostaria de ser chamada?"
                                        required
                                        class="w-full rounded-xl border-gray-300 focus:border-[#547558] focus:ring focus:ring-[#547558]/20 px-4 py-3 transition-all text-sm" />
                                </div>

                                <div>
                                    <label class="block text-xs sm:text-sm font-bold text-gray-900 mb-2">WhatsApp</label>
                                    <input
                                        v-model="form.whatsapp"
                                        type="tel"
                                        placeholder="(DD) 90000-0000"
                                        required
                                        class="w-full rounded-xl border-gray-300 focus:border-[#547558] focus:ring focus:ring-[#547558]/20 px-4 py-3 transition-all text-sm" />
                                </div>

                                <div class="flex justify-between items-center">
                                    <button @click="prevStep" class="text-gray-600 text-sm sm:text-base font-bold hover:text-gray-900 transition-colors flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-width="2" /></svg>
                                        Voltar
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="bg-[#547558] text-white px-4 sm:px-6 py-3 rounded-full text-sm sm:text-base font-bold shadow-md hover:bg-[#435e46] transition-all transform hover:-translate-y-0.5 disabled:opacity-50 flex items-center gap-2">
                                        Finalizar
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M5 13l4 4L19 7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="lg:col-span-2">
                            <div class="bg-[#547558] text-white rounded-3xl shadow-xl p-6 sticky top-28 overflow-hidden">
                                <div class="absolute top-0 right-0 -mr-6 -mt-6 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
                                <h3 class="text-xl font-bold mb-4 border-b border-white/20 pb-3">Resumo do Horário</h3>
                                <div class="lg:space-y-8 space-y-4">
                                    <div class="flex items-start gap-2">
                                        <div class="mt-0.5">
                                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758L5 19m0-14l4.121 4.121"
                                                    stroke-width="2"
                                                    stroke-linecap="round" />
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
                                                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm uppercase opacity-70 font-bold tracking-tighter mb-0.5">Data</p>
                                            <p class="font-bold text-md">{{ form.date.split('-').reverse().join('/') }}</p>
                                        </div>
                                    </div>
                                    <div class="bg-white/10 p-3 rounded-xl flex items-center justify-between mt-2">
                                        <span class="text-md font-bold uppercase opacity-90 tracking-widest">Horário</span>
                                        <span class="text-xl font-black">{{ form.time }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
