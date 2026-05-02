<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

// Verifica se a rota atual é a página de agendamento
const isAgendarPage = computed(() => {
    return route().current('agendar');
});

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="min-h-screen bg-[#FAF8F5] font-sans text-gray-800 antialiased relative">
        <header class="fixed top-0 inset-x-0 z-50 transition-all duration-500" :class="isScrolled ? 'bg-white shadow-md' : 'bg-white shadow-sm lg:bg-transparent lg:shadow-none'">
            <nav
                class="mx-auto py-4 flex items-center justify-between transition-all duration-500"
                :class="
                    isScrolled ? 'w-full max-w-full px-6 lg:px-12' : 'w-full px-6 lg:max-w-7xl lg:bg-white lg:rounded-b-[2rem] lg:px-12 lg:shadow-[0_10px_40px_rgba(0,0,0,0.04)]'
                ">
                <div class="flex-1 flex items-center justify-start transition-transform duration-500 hover:scale-105">
                    <img src="/images/logo-cabeleila.svg" alt="Logo Cabeleila" class="h-10 lg:h-12 w-auto object-contain" />
                </div>

                <div
                    class="hidden lg:flex flex-1 items-center justify-center gap-8 text-md font-medium transition-colors duration-300"
                    :class="isScrolled ? 'text-gray-600' : 'text-gray-500'">
                    <Link href="/#inicio" class="text-gray-900 font-semibold hover:text-[#547558] transition-colors duration-300">Início</Link>
                    <Link href="/#sobre" class="hover:text-[#547558] transition-colors duration-300">Sobre Nós</Link>
                    <Link href="/#servicos" class="hover:text-[#547558] transition-colors duration-300">Serviços</Link>
                    <Link href="/#contato" class="hover:text-[#547558] transition-colors duration-300">Contato</Link>
                </div>

                <div class="hidden lg:flex flex-1 items-center justify-end gap-5">
                    <div class="text-right flex flex-col justify-center transition-opacity duration-300">
                        <span class="text-sm text-[#547558] leading-none mb-1">Ligue agora:</span>
                        <span class="text-md font-semibold text-gray-900 leading-none hover:text-[#547558] transition-colors duration-300">(11) 99999-0000</span>
                    </div>

                    <Link
                        :href="isAgendarPage ? '/' : route('agendar')"
                        class="rounded-full border border-gray-300 px-5 py-2 text-md font-medium text-gray-700 hover:border-[#547558] hover:text-[#547558] hover:bg-[#547558]/5 transition-all duration-300 flex items-center gap-1.5 group">
                        <svg
                            v-if="isAgendarPage"
                            class="w-3.5 h-3.5 text-gray-400 group-hover:text-[#547558] transition-colors duration-300 transform group-hover:-translate-x-0.5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>

                        <span>{{ isAgendarPage ? 'Voltar ao Início' : 'Agendar' }}</span>

                        <svg
                            v-if="!isAgendarPage"
                            class="w-3.5 h-3.5 text-gray-400 group-hover:text-[#547558] transition-colors duration-300 transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                        </svg>
                    </Link>
                </div>

                <div class="flex lg:hidden flex-1 justify-end">
                    <button
                        @click="isMobileMenuOpen = !isMobileMenuOpen"
                        class="text-gray-600 hover:text-[#547558] focus:outline-none p-2 relative w-10 h-10 flex items-center justify-center">
                        <transition
                            enter-active-class="transition duration-300 ease-out absolute"
                            enter-from-class="opacity-0 -rotate-90 scale-50"
                            enter-to-class="opacity-100 rotate-0 scale-100"
                            leave-active-class="transition duration-200 ease-in absolute"
                            leave-from-class="opacity-100 rotate-0 scale-100"
                            leave-to-class="opacity-0 rotate-90 scale-50">
                            <svg v-if="!isMobileMenuOpen" class="h-7 w-7 absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg v-else class="h-7 w-7 absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div v-show="isMobileMenuOpen" class="lg:hidden absolute top-full left-0 w-full bg-white border-t border-gray-100 shadow-xl z-40">
                    <div class="px-6 py-6 flex flex-col gap-4">
                        <Link
                            href="/#inicio"
                            @click="isMobileMenuOpen = false"
                            class="text-gray-900 text-lg font-semibold border-b border-gray-50 pb-2 hover:pl-2 transition-all duration-300"
                            >Início</Link
                        >
                        <Link
                            href="/#sobre"
                            @click="isMobileMenuOpen = false"
                            class="text-gray-600 text-lg hover:text-[#547558] border-b border-gray-50 pb-2 hover:pl-2 transition-all duration-300"
                            >Sobre Nós</Link
                        >
                        <Link
                            href="/#servicos"
                            @click="isMobileMenuOpen = false"
                            class="text-gray-600 text-lg hover:text-[#547558] border-b border-gray-50 pb-2 hover:pl-2 transition-all duration-300"
                            >Serviços</Link
                        >
                        <Link
                            href="/#contato"
                            @click="isMobileMenuOpen = false"
                            class="text-gray-600 text-lg hover:text-[#547558] border-b border-gray-50 pb-2 hover:pl-2 transition-all duration-300"
                            >Contato</Link
                        >

                        <div class="pt-4 flex flex-col gap-4">
                            <div class="flex flex-col">
                                <span class="text-sm text-[#547558] leading-none mb-1">Ligue agora:</span>
                                <span class="text-lg font-semibold text-gray-900 leading-none">(11) 99999-0000</span>
                            </div>

                            <Link
                                :href="isAgendarPage ? '/' : route('agendar')"
                                @click="isMobileMenuOpen = false"
                                class="rounded-full bg-[#547558] text-white px-5 py-3 flex items-center justify-center gap-2 text-base font-medium hover:bg-[#435e46] transition-all duration-300 w-full shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
                                <svg v-if="isAgendarPage" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                <span>{{ isAgendarPage ? 'Voltar ao Início' : 'Agendar Horário' }}</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </transition>
        </header>

        <main>
            <slot />
        </main>

        <footer id="contato" class="bg-[#668266] text-white pt-24 pb-12 relative overflow-hidden">
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-[#547558] opacity-30 blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-[#547558] opacity-30 blur-3xl pointer-events-none"></div>

            <div class="mx-auto max-w-7xl px-6 lg:px-12 relative z-10">
                <div class="flex flex-col lg:flex-row justify-between items-center pb-16 border-b border-white/20 mb-16 gap-8">
                    <div>
                        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-3">Fique por dentro das novidades</h2>
                        <p class="text-white/80">Assine nossa newsletter para receber dicas de beleza e promoções exclusivas.</p>
                    </div>
                    <div class="relative w-full max-w-md">
                        <input
                            type="email"
                            placeholder="Seu melhor e-mail"
                            class="w-full rounded-full bg-white/10 border border-white/20 py-4 pl-6 pr-36 text-white outline-none focus:ring-2 focus:ring-white placeholder-white/60 transition-all" />
                        <button
                            class="absolute right-1.5 top-1.5 bottom-1.5 rounded-full bg-white text-[#668266] px-8 text-md font-bold hover:bg-gray-100 transition-colors duration-300">
                            Assinar
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
                    <div class="lg:pr-8">
                        <div class="flex items-center gap-3 mb-6">
                            <img src="/images/logo-cabeleila.svg" alt="Logo Cabeleila" class="h-10 w-auto object-contain brightness-0 invert opacity-90" />
                        </div>
                        <p class="text-sm leading-relaxed mb-8 text-white/80">
                            A sua beleza levada a sério. Especialistas em transformar visuais e elevar a autoestima com técnicas de ponta e muito carinho.
                        </p>

                        <div class="flex gap-4">
                            <a
                                href="#"
                                class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-white hover:text-[#668266] transition-all duration-300 hover:-translate-y-1 border border-white/20 hover:border-white">
                                <span class="sr-only">Instagram</span>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
                                </svg>
                            </a>
                            <a
                                href="#"
                                class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-white hover:text-[#668266] transition-all duration-300 hover:-translate-y-1 border border-white/20 hover:border-white">
                                <span class="sr-only">Facebook</span>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" />
                                </svg>
                            </a>
                            <a
                                href="#"
                                class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-white hover:text-[#668266] transition-all duration-300 hover:-translate-y-1 border border-white/20 hover:border-white">
                                <span class="sr-only">TikTok</span>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xl font-bold text-white mb-6">Links Úteis</h4>
                        <ul class="space-y-4 text-md">
                            <li>
                                <Link href="/#inicio" class="text-white/80 hover:text-white hover:pl-2 transition-all duration-300 flex items-center"
                                    ><span class="mr-2 text-white/50">›</span> Início</Link
                                >
                            </li>
                            <li>
                                <Link href="/#sobre" class="text-white/80 hover:text-white hover:pl-2 transition-all duration-300 flex items-center"
                                    ><span class="mr-2 text-white/50">›</span> Sobre Nós</Link
                                >
                            </li>
                            <li>
                                <Link href="/#servicos" class="text-white/80 hover:text-white hover:pl-2 transition-all duration-300 flex items-center"
                                    ><span class="mr-2 text-white/50">›</span> Serviços</Link
                                >
                            </li>
                            <li>
                                <Link href="/#portfolio" class="text-white/80 hover:text-white hover:pl-2 transition-all duration-300 flex items-center"
                                    ><span class="mr-2 text-white/50">›</span> Portfólio</Link
                                >
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-xl font-bold text-white mb-6">Serviços Populares</h4>
                        <ul class="space-y-4 text-md">
                            <li>
                                <Link href="/#servicos" class="text-white/80 hover:text-white hover:pl-2 transition-all duration-300 flex items-center"
                                    ><span class="mr-2 text-white/50">›</span> Corte e Visagismo</Link
                                >
                            </li>
                            <li>
                                <Link href="/#servicos" class="text-white/80 hover:text-white hover:pl-2 transition-all duration-300 flex items-center"
                                    ><span class="mr-2 text-white/50">›</span> Coloração e Mechas</Link
                                >
                            </li>
                            <li>
                                <Link href="/#servicos" class="text-white/80 hover:text-white hover:pl-2 transition-all duration-300 flex items-center"
                                    ><span class="mr-2 text-white/50">›</span> Tratamentos Capilares</Link
                                >
                            </li>
                            <li>
                                <Link href="/#servicos" class="text-white/80 hover:text-white hover:pl-2 transition-all duration-300 flex items-center"
                                    ><span class="mr-2 text-white/50">›</span> Penteados e Make</Link
                                >
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-xl font-bold text-white mb-6">Informações</h4>
                        <ul class="space-y-6 text-md">
                            <li class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white shrink-0 border border-white/20">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-white/60 mb-1">WhatsApp</p>
                                    <a href="tel:+5511999990000" class="text-white font-medium hover:text-gray-200 transition-colors">(11) 99999-0000</a>
                                </div>
                            </li>
                            <li class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white shrink-0 border border-white/20">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-white/60 mb-1">E-mail</p>
                                    <a href="mailto:contato@cabeleila.com.br" class="text-white font-medium hover:text-gray-200 transition-colors break-all"
                                        >contato@cabeleila.com.br</a
                                    >
                                </div>
                            </li>
                            <li class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white shrink-0 border border-white/20">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-white/60 mb-1">Endereço</p>
                                    <p class="text-white font-medium">Av. Getúlio Vargas, 123<br />Feira de Santana, BA</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-center pt-8 border-t border-white/20 text-sm text-white/60">
                    <p>&copy; 2026. Todos os direitos reservados Cabeleila.</p>
                    <button
                        class="mt-4 md:mt-0 flex items-center gap-2 text-white/60 hover:text-white transition-colors group"
                        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })">
                        <span>Voltar ao topo</span>
                        <div
                            class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center group-hover:bg-white group-hover:text-[#668266] transition-colors border border-white/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                            </svg>
                        </div>
                    </button>
                </div>
            </div>
        </footer>
    </div>
</template>
