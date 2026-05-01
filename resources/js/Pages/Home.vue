<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
});

// Variáveis para controlar o estado da Navbar
const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);

// Função do Scroll da Navbar
const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    // Scroll Reveal
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                // Quando o elemento entra na tela
                if (entry.isIntersecting) {
                    // Remove o invisível e empurrado para baixo
                    entry.target.classList.remove('opacity-0', 'translate-y-12');
                    // Adiciona visibilidade total e posição original
                    entry.target.classList.add('opacity-100', 'translate-y-0');
                    // Desativa o observador para esse elemento (anima só uma vez)
                    observer.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.1, // Dispara a animação quando 10% do elemento aparece
            rootMargin: '0px 0px -50px 0px', // Faz aparecer um pouquinho antes de chegar bem no meio da tela
        },
    );

    // Pega todos os elementos que têm a classe 'reveal' e coloca o observador neles
    document.querySelectorAll('.reveal').forEach((el) => {
        observer.observe(el);
    });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <Head title="Cabeleila - Salão de Beleza" />

    <div class="min-h-screen bg-[#FAF8F5] font-sans text-gray-800 antialiased relative">
        <!-- Cabeçalho -->
        <header class="fixed top-0 inset-x-0 z-50 transition-all duration-500" :class="isScrolled ? 'bg-white shadow-md' : 'bg-white shadow-sm lg:bg-transparent lg:shadow-none'">
            <!-- Navbar Dinâmica -->
            <nav
                class="mx-auto py-4 flex items-center justify-between transition-all duration-500"
                :class="
                    isScrolled ? 'w-full max-w-full px-6 lg:px-12' : 'w-full px-6 lg:max-w-7xl lg:bg-white lg:rounded-b-[2rem] lg:px-12 lg:shadow-[0_10px_40px_rgba(0,0,0,0.04)]'
                ">
                <!-- Logo da Empresa -->
                <div class="flex-1 flex items-center justify-start transition-transform duration-500 hover:scale-105">
                    <img src="/images/logo-cabeleila.svg" alt="Logo Cabeleila" class="h-10 lg:h-12 w-auto object-contain" />
                </div>

                <!-- Links de Navegação -->
                <div
                    class="hidden lg:flex flex-1 items-center justify-center gap-8 text-md font-medium transition-colors duration-300"
                    :class="isScrolled ? 'text-gray-600' : 'text-gray-500'">
                    <a href="#inicio" class="text-gray-900 font-semibold hover:text-[#547558] transition-colors duration-300">Início</a>
                    <a href="#sobre" class="hover:text-[#547558] transition-colors duration-300">Sobre Nós</a>
                    <a href="#servicos" class="hover:text-[#547558] transition-colors duration-300">Serviços</a>
                    <a href="#contato" class="hover:text-[#547558] transition-colors duration-300">Contato</a>
                </div>

                <!-- Número de Contato e Botão de Agendamento -->
                <div class="hidden lg:flex flex-1 items-center justify-end gap-5">
                    <div class="text-right flex flex-col justify-center transition-opacity duration-300">
                        <span class="text-sm text-[#547558] leading-none mb-1">Ligue agora:</span>
                        <span class="text-md font-semibold text-gray-900 leading-none hover:text-[#547558] transition-colors duration-300">(11) 99999-0000</span>
                    </div>
                    <a
                        href="#"
                        class="rounded-full border border-gray-300 px-5 py-2 text-md font-medium text-gray-700 hover:border-[#547558] hover:text-[#547558] hover:bg-[#547558]/5 transition-all duration-300 flex items-center gap-1.5 group">
                        Agendar
                        <svg
                            class="w-3.5 h-3.5 text-gray-400 group-hover:text-[#547558] transition-colors duration-300 transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                        </svg>
                    </a>
                </div>

                <!-- Botão de Menu Mobile -->
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

            <!-- Menu Dropdown Mobile -->
            <transition
                enter-active-class="transition ease-out duration-300 transform origin-top"
                enter-from-class="opacity-0 -translate-y-4 scale-y-95"
                enter-to-class="opacity-100 translate-y-0 scale-y-100"
                leave-active-class="transition ease-in duration-200 transform origin-top"
                leave-from-class="opacity-100 translate-y-0 scale-y-100"
                leave-to-class="opacity-0 -translate-y-4 scale-y-95">
                <div v-show="isMobileMenuOpen" class="lg:hidden absolute top-full left-0 w-full bg-white border-t border-gray-100 shadow-xl z-40">
                    <div class="px-6 py-6 flex flex-col gap-4">
                        <a
                            href="#inicio"
                            @click="isMobileMenuOpen = false"
                            class="text-gray-900 text-lg font-semibold border-b border-gray-50 pb-2 hover:pl-2 transition-all duration-300"
                            >Início</a
                        >
                        <a
                            href="#sobre"
                            @click="isMobileMenuOpen = false"
                            class="text-gray-600 text-lg hover:text-[#547558] border-b border-gray-50 pb-2 hover:pl-2 transition-all duration-300"
                            >Sobre Nós</a
                        >
                        <a
                            href="#servicos"
                            @click="isMobileMenuOpen = false"
                            class="text-gray-600 text-lg hover:text-[#547558] border-b border-gray-50 pb-2 hover:pl-2 transition-all duration-300"
                            >Serviços</a
                        >
                        <a
                            href="#contato"
                            @click="isMobileMenuOpen = false"
                            class="text-gray-600 text-lg hover:text-[#547558] border-b border-gray-50 pb-2 hover:pl-2 transition-all duration-300"
                            >Contato</a
                        >

                        <div class="pt-4 flex flex-col gap-4">
                            <div class="flex flex-col">
                                <span class="text-sm text-[#547558] leading-none mb-1">Ligue agora:</span>
                                <span class="text-lg font-semibold text-gray-900 leading-none">(11) 99999-0000</span>
                            </div>
                            <a
                                href="#"
                                class="rounded-full bg-[#547558] text-white px-5 py-3 text-center text-base font-medium hover:bg-[#435e46] transition-all duration-300 w-full shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
                                Agendar Horário
                            </a>
                        </div>
                    </div>
                </div>
            </transition>
        </header>

        <!-- Conteúdo Principal -->
        <main>
            <!-- Hero -->
            <section
                id="inicio"
                class="relative pt-32 md:pt-44 pb-16 lg:pt-52 lg:pb-40 px-6 lg:px-12"
                style="background-image: url('/images/background-hero.png'); background-size: cover; background-position: center; background-repeat: no-repeat">
                <div class="mx-auto max-w-7xl grid lg:grid-cols-3 gap-12 items-center relative z-10">
                    <!-- Conteúdo Esquerda (Com Reveal) -->
                    <div class="max-w-xl lg:col-span-2 reveal opacity-0 translate-y-12 transition-all duration-1000 ease-out">
                        <p class="text-[#547558] text-xl font-semibold mb-4">Seu Salão de Beleza Exclusivo</p>
                        <h1 class="text-5xl lg:text-7xl font-bold text-gray-800 leading-[1.1] mb-6">
                            Cabelos Incríveis, <br />
                            Beleza & <span class="text-[#547558]">Estilo</span>
                        </h1>
                        <p class="text-gray-700 mb-8 lg:mb-10 leading-relaxed text-md">
                            Descubra o corte perfeito e as cores que combinam com a sua essência. Nossos especialistas estão prontos para transformar o seu visual e realçar a sua
                            beleza natural com produtos de altíssima qualidade.
                        </p>

                        <div class="flex flex-wrap items-center gap-4">
                            <a href="#" class="rounded-full bg-[#547558] px-6 py-3 text-lg font-bold text-white shadow-sm hover:bg-[#435e46] transition flex items-center gap-2">
                                Agendar Horário
                                <svg class="w-3.5 h-3.5 border border-white rounded-full p-[1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                                </svg>
                            </a>
                            <a
                                href="#servicos"
                                class="rounded-full border border-gray-400 px-6 py-3 text-lg font-bold text-gray-600 hover:border-[#547558] hover:text-[#547558] transition flex items-center gap-2 group">
                                Ver Serviços
                                <svg
                                    class="w-3.5 h-3.5 text-gray-400 border border-gray-400 rounded-full p-[1px] group-hover:text-[#547558] group-hover:border-[#547558]"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="hidden lg:block relative h-[550px] pointer-events-none"></div>
                </div>
            </section>

            <!-- Sobre Nós -->
            <section id="sobre" class="py-16 lg:py-24 bg-[#FAF8F5]">
                <div class="mx-auto max-w-7xl px-6 lg:px-12 grid lg:grid-cols-2 gap-16 items-center">
                    <!-- Imagens Sobrepostas (Com Reveal Animado) -->
                    <div class="relative h-[600px] hidden md:block reveal opacity-0 translate-y-12 transition-all duration-1000 ease-out">
                        <img
                            src="/images/cortando-cabelo.png"
                            alt="Cortando o cabelo"
                            class="absolute bottom-0 left-0 w-3/4 h-[450px] object-cover rounded-3xl shadow-lg z-10 border-8 border-[#FAF8F5] transition-transform duration-500 hover:scale-[1.02]" />
                        <img
                            src="/images/foto-salao-home.png"
                            alt="Lavatório salão"
                            class="absolute top-0 right-0 w-2/3 h-[350px] object-cover rounded-3xl shadow-2xl z-20 border-8 border-[#FAF8F5] transition-transform duration-500 hover:scale-[1.02]" />
                    </div>

                    <!-- Conteúdo de Texto (Com Reveal Animado e Delay) -->
                    <div class="reveal opacity-0 translate-y-12 transition-all duration-1000 delay-200 ease-out">
                        <p class="text-[#547558] text-xl font-semibold mb-4">Sobre o Cabeleila</p>
                        <h2 class="text-4xl lg:text-5xl font-bold text-gray-800 leading-tight mb-6">Empoderando sua autoestima com cuidado expert</h2>
                        <p class="text-gray-700 mb-10 text-md leading-relaxed">
                            No Cabeleila, nós acreditamos que o seu cabelo é a sua coroa. Nossa equipe dedicada de hair stylists está comprometida em oferecer tratamentos
                            personalizados, cortes modernos e colorações vibrantes que respeitam a saúde dos seus fios.
                        </p>

                        <!-- Grid de Destaques -->
                        <div class="grid sm:grid-cols-2 gap-8 mb-10">
                            <!-- Destaque 1 -->
                            <div class="flex gap-4 items-start group">
                                <div
                                    class="flex-shrink-0 w-14 h-14 bg-white shadow-sm rounded-2xl flex items-center justify-center text-[#547558] group-hover:bg-[#547558] group-hover:text-white transition-colors duration-300">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M6.5 6.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5Zm0 0L17.5 17.5m0-11a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5Zm0 0L6.5 17.5" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 text-lg mb-1">Cortes Modernos</h4>
                                    <p class="text-sm text-gray-600 leading-relaxed">Técnicas atualizadas para o caimento perfeito.</p>
                                </div>
                            </div>

                            <!-- Destaque 2 -->
                            <div class="flex gap-4 items-start group">
                                <div
                                    class="flex-shrink-0 w-14 h-14 bg-white shadow-sm rounded-2xl flex items-center justify-center text-[#547558] group-hover:bg-[#547558] group-hover:text-white transition-colors duration-300">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 text-lg mb-1">Cuidado Capilar</h4>
                                    <p class="text-sm text-gray-600 leading-relaxed">Tratamentos profundos para a saúde dos fios.</p>
                                </div>
                            </div>

                            <!-- Destaque 3 -->
                            <div class="flex gap-4 items-start group">
                                <div
                                    class="flex-shrink-0 w-14 h-14 bg-white shadow-sm rounded-2xl flex items-center justify-center text-[#547558] group-hover:bg-[#547558] group-hover:text-white transition-colors duration-300">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 text-lg mb-1">Colorimetria</h4>
                                    <p class="text-sm text-gray-600 leading-relaxed">Especialistas em mechas, loiros e colorações.</p>
                                </div>
                            </div>

                            <!-- Destaque 4 -->
                            <div class="flex gap-4 items-start group">
                                <div
                                    class="flex-shrink-0 w-14 h-14 bg-white shadow-sm rounded-2xl flex items-center justify-center text-[#547558] group-hover:bg-[#547558] group-hover:text-white transition-colors duration-300">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 text-lg mb-1">Estilo Único</h4>
                                    <p class="text-sm text-gray-600 leading-relaxed">Penteados incríveis para eventos especiais.</p>
                                </div>
                            </div>
                        </div>

                        <p class="text-md text-gray-700 mb-8 leading-relaxed">
                            No Cabeleila, cada cliente é único. Venha tomar um café conosco e descubra como podemos elevar a sua identidade visual ao próximo nível.
                        </p>

                        <div class="flex items-center gap-6">
                            <a
                                href="#contato"
                                class="rounded-full bg-[#547558] px-8 py-3.5 text-lg font-bold text-white shadow-sm hover:bg-[#435e46] hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2">
                                Fale Conosco
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Serviços -->
            <section id="servicos" class="py-16 lg:py-24 bg-white">
                <div class="mx-auto max-w-7xl px-6 lg:px-12 text-center">
                    <p class="reveal opacity-0 translate-y-12 transition-all duration-1000 ease-out text-[#547558] text-xl font-semibold mb-4">Nossos Serviços</p>
                    <h2 class="reveal opacity-0 translate-y-12 transition-all duration-1000 delay-100 ease-out text-4xl lg:text-5xl font-bold text-gray-800 mb-6">
                        Realce sua Beleza com Especialistas
                    </h2>
                    <p class="reveal opacity-0 translate-y-12 transition-all duration-1000 delay-200 ease-out text-gray-700 max-w-2xl mx-auto mb-16 text-md leading-relaxed">
                        Conheça nosso portfólio de serviços desenvolvidos para cuidar da saúde e estética dos seus cabelos com os melhores produtos do mercado.
                    </p>

                    <!-- Grid de Serviços (Com efeito cascata - Delay progressivo) -->
                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 text-left">
                        <!-- Serviço 1 (Sobe Primeiro) -->
                        <div
                            class="reveal opacity-0 translate-y-12 transition-all duration-1000 delay-100 ease-out relative h-[450px] rounded-[2rem] overflow-hidden group shadow-lg">
                            <img
                                src="https://images.unsplash.com/photo-1700760934268-8aa0ef52ce0a?q=80&w=764&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                alt="Corte Feminino"
                                class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/40 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-300"></div>

                            <div class="absolute bottom-0 left-0 w-full p-8 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                <h3 class="text-2xl font-bold text-white mb-2">Corte e Visagismo</h3>
                                <p class="text-gray-200 text-sm mb-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-100 leading-relaxed">
                                    Cortes que harmonizam com o formato do seu rosto e estilo de vida.
                                </p>
                                <button
                                    class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-[#547558] hover:text-white transition-all duration-300 ml-auto border border-white/30">
                                    <svg class="w-5 h-5 transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Serviço 2 (Sobe depois) -->
                        <div
                            class="reveal opacity-0 translate-y-12 transition-all duration-1000 delay-200 ease-out relative h-[450px] rounded-[2rem] overflow-hidden group shadow-lg">
                            <img
                                src="https://images.unsplash.com/photo-1554519934-e32b1629d9ee?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                alt="Coloração e Mechas"
                                class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/40 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-300"></div>

                            <div class="absolute bottom-0 left-0 w-full p-8 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                <h3 class="text-2xl font-bold text-white mb-2">Coloração e Mechas</h3>
                                <p class="text-gray-200 text-sm mb-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-100 leading-relaxed">
                                    Morenas iluminadas, loiros impecáveis e cores vibrantes.
                                </p>
                                <button
                                    class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-[#547558] hover:text-white transition-all duration-300 ml-auto border border-white/30">
                                    <svg class="w-5 h-5 transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Serviço 3 -->
                        <div
                            class="reveal opacity-0 translate-y-12 transition-all duration-1000 delay-300 ease-out relative h-[450px] rounded-[2rem] overflow-hidden group shadow-lg">
                            <img
                                src="https://images.unsplash.com/photo-1634449571010-02389ed0f9b0?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                alt="Tratamentos Capilares"
                                class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/40 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-300"></div>

                            <div class="absolute bottom-0 left-0 w-full p-8 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                <h3 class="text-2xl font-bold text-white mb-2">Tratamentos Capilares</h3>
                                <p class="text-gray-200 text-sm mb-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-100 leading-relaxed">
                                    Cronograma capilar, hidratação profunda e reconstrução de fios.
                                </p>
                                <button
                                    class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-[#547558] hover:text-white transition-all duration-300 ml-auto border border-white/30">
                                    <svg class="w-5 h-5 transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Serviço 4 -->
                        <div
                            class="reveal opacity-0 translate-y-12 transition-all duration-1000 delay-500 ease-out relative h-[450px] rounded-[2rem] overflow-hidden group shadow-lg">
                            <img
                                src="https://images.unsplash.com/photo-1611826585949-b0ccabd2c1a4?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                alt="Penteados e Maquiagem"
                                class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/40 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-300"></div>

                            <div class="absolute bottom-0 left-0 w-full p-8 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                <h3 class="text-2xl font-bold text-white mb-2">Penteados e Make</h3>
                                <p class="text-gray-200 text-sm mb-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-100 leading-relaxed">
                                    Produção completa para noivas, formaturas e eventos sociais.
                                </p>
                                <button
                                    class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-[#547558] hover:text-white transition-all duration-300 ml-auto border border-white/30">
                                    <svg class="w-5 h-5 transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Footer (Reveal) -->
            <footer id="contato" class="reveal opacity-0 translate-y-12 transition-all duration-1000 ease-out bg-[#668266] text-white pt-24 pb-12 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-[#547558] opacity-30 blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-[#547558] opacity-30 blur-3xl pointer-events-none"></div>

                <div class="mx-auto max-w-7xl px-6 lg:px-12 relative z-10">
                    <!-- Newsletter -->
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

                    <!-- Grid de Links e Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
                        <!-- Brand Info -->
                        <div class="lg:pr-8">
                            <div class="flex items-center gap-3 mb-6">
                                <img src="/images/logo-cabeleila.svg" alt="Logo Cabeleila" class="h-10 w-auto object-contain brightness-0 invert opacity-90" />
                            </div>
                            <p class="text-sm leading-relaxed mb-8 text-white/80">
                                A sua beleza levada a sério. Especialistas em transformar visuais e elevar a autoestima com técnicas de ponta e muito carinho.
                            </p>

                            <!-- Redes Sociais -->
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

                        <!-- Links Úteis -->
                        <div>
                            <h4 class="text-xl font-bold text-white mb-6">Links Úteis</h4>
                            <ul class="space-y-4 text-md">
                                <li>
                                    <a href="#inicio" class="text-white/80 hover:text-white hover:pl-2 transition-all duration-300 flex items-center"
                                        ><span class="mr-2 text-white/50">›</span> Início</a
                                    >
                                </li>
                                <li>
                                    <a href="#sobre" class="text-white/80 hover:text-white hover:pl-2 transition-all duration-300 flex items-center"
                                        ><span class="mr-2 text-white/50">›</span> Sobre Nós</a
                                    >
                                </li>
                                <li>
                                    <a href="#servicos" class="text-white/80 hover:text-white hover:pl-2 transition-all duration-300 flex items-center"
                                        ><span class="mr-2 text-white/50">›</span> Serviços</a
                                    >
                                </li>
                                <li>
                                    <a href="#" class="text-white/80 hover:text-white hover:pl-2 transition-all duration-300 flex items-center"
                                        ><span class="mr-2 text-white/50">›</span> Portfólio</a
                                    >
                                </li>
                            </ul>
                        </div>

                        <!-- Serviços -->
                        <div>
                            <h4 class="text-xl font-bold text-white mb-6">Serviços Populares</h4>
                            <ul class="space-y-4 text-md">
                                <li>
                                    <a href="#" class="text-white/80 hover:text-white hover:pl-2 transition-all duration-300 flex items-center"
                                        ><span class="mr-2 text-white/50">›</span> Corte e Visagismo</a
                                    >
                                </li>
                                <li>
                                    <a href="#" class="text-white/80 hover:text-white hover:pl-2 transition-all duration-300 flex items-center"
                                        ><span class="mr-2 text-white/50">›</span> Coloração e Mechas</a
                                    >
                                </li>
                                <li>
                                    <a href="#" class="text-white/80 hover:text-white hover:pl-2 transition-all duration-300 flex items-center"
                                        ><span class="mr-2 text-white/50">›</span> Tratamentos Capilares</a
                                    >
                                </li>
                                <li>
                                    <a href="#" class="text-white/80 hover:text-white hover:pl-2 transition-all duration-300 flex items-center"
                                        ><span class="mr-2 text-white/50">›</span> Penteados e Make</a
                                    >
                                </li>
                            </ul>
                        </div>

                        <!-- Contato Info -->
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
        </main>
    </div>
</template>
