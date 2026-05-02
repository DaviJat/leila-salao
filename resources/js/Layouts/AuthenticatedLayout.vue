<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const isRouteActive = (routeName) => {
    return route().current(routeName) || route().current(routeName + '.*');
};
</script>

<template>
    <div class="min-h-screen bg-[url('/images/background-painel.png')] bg-cover bg-center bg-fixed relative w-full overflow-x-hidden font-sans text-gray-900">
        <div class="absolute inset-0 bg-[#FAF8F5]/85 backdrop-blur-[2px] z-0"></div>

        <div class="relative z-10 flex flex-col min-h-screen">
            <div class="pt-6 px-4 sm:px-6 lg:px-8 w-full max-w-7xl mx-auto">
                <nav class="bg-white rounded-full shadow-sm px-6 py-3 sm:px-8 flex items-center justify-between border border-gray-100">
                    <div class="flex items-center">
                        <Link :href="route('dashboard')" class="flex items-center gap-3 transition-transform duration-300 hover:scale-105 group">
                            <img src="/images/logo-cabeleila.svg" alt="Logo Cabeleila" class="h-8 sm:h-10 w-auto object-contain mb-1" />

                            <div class="hidden sm:flex items-center">
                                <div class="h-8 w-px bg-gray-200 mr-3"></div>
                                <span class="py-1 text-[#547558] text-md font-light uppercase tracking-[0.2em]"> Admin </span>
                            </div>
                        </Link>
                    </div>

                    <div class="hidden md:flex items-center space-x-10">
                        <Link
                            :href="route('dashboard')"
                            class="text-sm font-medium transition-colors hover:text-[#547558]"
                            :class="isRouteActive('dashboard') ? 'text-[#547558] font-bold border-b-2 border-[#547558]' : 'text-gray-600'">
                            Dashboard
                        </Link>
                        <Link
                            :href="route('admin.appointments.index')"
                            class="text-sm font-medium transition-colors hover:text-[#547558]"
                            :class="isRouteActive('admin.appointments') ? 'text-[#547558] font-bold border-b-2 border-[#547558]' : 'text-gray-600'">
                            Agenda
                        </Link>
                        <Link
                            :href="route('admin.clients.index')"
                            class="text-sm font-medium transition-colors hover:text-[#547558]"
                            :class="isRouteActive('admin.clients') ? 'text-[#547558] font-bold border-b-2 border-[#547558]' : 'text-gray-600'">
                            Clientes
                        </Link>
                    </div>

                    <div class="hidden sm:flex items-center">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="flex items-center gap-3 px-4 py-2 rounded-full border border-gray-100 hover:border-[#547558]/30 hover:bg-gray-50 transition-all group">
                                    <div class="w-7 h-7 rounded-full bg-[#547558]/10 text-[#547558] flex items-center justify-center text-xs font-bold">
                                        {{ $page.props.auth.user.name.charAt(0) }}
                                    </div>
                                    <span class="text-sm font-bold text-gray-700">{{ $page.props.auth.user.name.split(' ')[0] }}</span>
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-[#547558] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Meu Perfil</DropdownLink>
                                <div class="border-t border-gray-100"></div>
                                <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-600 font-bold">Sair do Painel</DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <div class="sm:hidden flex items-center">
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="text-gray-500 hover:text-[#547558] focus:outline-none w-10 h-10 flex items-center justify-center relative">
                            <transition
                                enter-active-class="transition duration-300 ease-out absolute"
                                enter-from-class="opacity-0 -rotate-90 scale-50"
                                enter-to-class="opacity-100 rotate-0 scale-100"
                                leave-active-class="transition duration-200 ease-in absolute"
                                leave-from-class="opacity-100 rotate-0 scale-100"
                                leave-to-class="opacity-0 rotate-90 scale-50">
                                <svg v-if="!showingNavigationDropdown" class="h-6 w-6 absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                <svg v-else class="h-6 w-6 absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </transition>
                        </button>
                    </div>
                </nav>

                <transition
                    enter-active-class="transition ease-out duration-300 transform origin-top"
                    enter-from-class="opacity-0 -translate-y-4 scale-y-95"
                    enter-to-class="opacity-100 translate-y-0 scale-y-100"
                    leave-active-class="transition ease-in duration-200 transform origin-top"
                    leave-from-class="opacity-100 translate-y-0 scale-y-100"
                    leave-to-class="opacity-0 -translate-y-4 scale-y-95">
                    <div
                        v-show="showingNavigationDropdown"
                        class="sm:hidden mt-2 bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-gray-100 overflow-hidden relative z-50">
                        <div class="pt-2 pb-3 space-y-1 px-2">
                            <ResponsiveNavLink :href="route('dashboard')" :active="isRouteActive('dashboard')" class="rounded-lg">Dashboard</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.appointments.index')" :active="isRouteActive('admin.appointments')" class="rounded-lg">Agenda</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.clients.index')" :active="isRouteActive('admin.clients')" class="rounded-lg">Clientes</ResponsiveNavLink>
                        </div>
                        <div class="pt-4 pb-2 border-t border-gray-100 bg-gray-50/50">
                            <div class="px-4 mb-3 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#547558] text-white flex items-center justify-center text-sm font-bold">
                                    {{ $page.props.auth.user.name.charAt(0) }}
                                </div>
                                <div>
                                    <div class="font-bold text-gray-800 text-base">{{ $page.props.auth.user.name }}</div>
                                    <div class="text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                                </div>
                            </div>
                            <div class="px-2 space-y-1">
                                <ResponsiveNavLink :href="route('profile.edit')" class="rounded-lg">Meu Perfil</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('logout')" method="post" as="button" class="text-red-600 font-bold rounded-lg">Sair do Painel</ResponsiveNavLink>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>

            <header v-if="$slots.header" class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-12 animate-fade-in">
                <slot name="header" />
            </header>

            <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 pb-12 relative z-10">
                <slot />
            </main>

            <footer class="w-full border-t border-gray-200/50 bg-white/40 backdrop-blur-md mt-auto relative z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex justify-center items-center">
                    <p class="flex items-center justify-center text-sm text-gray-500 font-medium text-center">
                        &copy; {{ new Date().getFullYear() }} Cabeleila. Todos os direitos reservados.
                    </p>
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
</style>
