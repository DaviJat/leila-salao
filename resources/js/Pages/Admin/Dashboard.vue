<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="text-center sm:text-left">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Visão Geral</h2>
                <p class="text-sm text-gray-600">Acompanhe o movimento do salão hoje.</p>
            </div>
        </template>

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                <div
                    class="bg-white/90 backdrop-blur-sm p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-100/50 flex flex-col items-center sm:items-start text-center sm:text-left">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Agendamentos Hoje
                    </p>
                    <p class="text-4xl sm:text-5xl font-semibold text-gray-900">{{ stats?.today_total || 0 }}</p>
                </div>

                <div
                    class="bg-white/90 backdrop-blur-sm p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-100/50 flex flex-col items-center sm:items-start text-center sm:text-left">
                    <p class="text-xs font-bold text-yellow-600 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Pendentes
                    </p>
                    <p class="text-4xl sm:text-5xl font-semibold text-gray-900">{{ stats?.pending || 0 }}</p>
                </div>

                <div
                    class="bg-white/90 backdrop-blur-sm p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-100/50 flex flex-col items-center sm:items-start text-center sm:text-left">
                    <p class="text-xs font-bold text-[#547558] uppercase tracking-widest mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Confirmados
                    </p>
                    <p class="text-4xl sm:text-5xl font-semibold text-gray-900">{{ stats?.confirmed || 0 }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mt-8">
                <Link
                    :href="route('admin.appointments.index')"
                    class="bg-white/90 backdrop-blur-sm p-8 sm:p-10 rounded-2xl shadow-sm border border-gray-100/50 flex flex-col items-center justify-center text-center hover:shadow-md hover:border-[#547558]/30 transition-all duration-300 group">
                    <div
                        class="w-12 h-12 bg-gray-50 text-[#547558] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-[#547558]/10 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Gerenciar Agenda</h3>
                    <p class="text-sm text-gray-500">Visualize horários, confirme ou cancele agendamentos.</p>
                </Link>

                <Link
                    :href="route('admin.clients.index')"
                    class="bg-white/90 backdrop-blur-sm p-8 sm:p-10 rounded-2xl shadow-sm border border-gray-100/50 flex flex-col items-center justify-center text-center hover:shadow-md hover:border-[#547558]/30 transition-all duration-300 group">
                    <div
                        class="w-12 h-12 bg-gray-50 text-[#547558] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-[#547558]/10 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Base de Clientes</h3>
                    <p class="text-sm text-gray-500">Cadastro completo, histórico e contatos de clientes.</p>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
