<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import SiteLayout from '@/Layouts/SiteLayout.vue'; // <--- Importamos o layout aqui!

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

// A lógica do Navbar foi para o Layout. Mantemos apenas o Scroll Reveal aqui.
onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.remove('opacity-0', 'translate-y-12');
                    entry.target.classList.add('opacity-100', 'translate-y-0');
                    observer.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px',
        },
    );

    document.querySelectorAll('.reveal').forEach((el) => {
        observer.observe(el);
    });
});
</script>

<template>
    <Head title="Cabeleila - Salão de Beleza" />

    <SiteLayout>
        <section
            id="inicio"
            class="relative pt-32 md:pt-44 pb-16 lg:pt-52 lg:pb-40 px-6 lg:px-12"
            style="background-image: url('/images/background-hero.png'); background-size: cover; background-position: center; background-repeat: no-repeat">
            <div class="mx-auto max-w-7xl grid lg:grid-cols-3 gap-12 items-center relative z-10">
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
                        <Link
                            :href="route('agendar')"
                            class="rounded-full bg-[#547558] px-6 py-3 text-lg font-bold text-white shadow-sm hover:bg-[#435e46] transition flex items-center gap-2">
                            Agendar Horário
                            <svg class="w-3.5 h-3.5 border border-white rounded-full p-[1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                            </svg>
                        </Link>
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

        <section id="sobre" class="py-16 lg:py-24 bg-[#FAF8F5]">
            <div class="mx-auto max-w-7xl px-6 lg:px-12 grid lg:grid-cols-2 gap-16 items-center">
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

                <div class="reveal opacity-0 translate-y-12 transition-all duration-1000 delay-200 ease-out">
                    <p class="text-[#547558] text-xl font-semibold mb-4">Sobre o Cabeleila</p>
                    <h2 class="text-4xl lg:text-5xl font-bold text-gray-800 leading-tight mb-6">Empoderando sua autoestima com cuidado expert</h2>
                    <p class="text-gray-700 mb-10 text-md leading-relaxed">
                        No Cabeleila, nós acreditamos que o seu cabelo é a sua coroa. Nossa equipe dedicada de hair stylists está comprometida em oferecer tratamentos
                        personalizados, cortes modernos e colorações vibrantes que respeitam a saúde dos seus fios.
                    </p>

                    <div class="grid sm:grid-cols-2 gap-8 mb-10">
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

        <section id="servicos" class="py-16 lg:py-24 bg-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-12 text-center">
                <p class="reveal opacity-0 translate-y-12 transition-all duration-1000 ease-out text-[#547558] text-xl font-semibold mb-4">Nossos Serviços</p>
                <h2 class="reveal opacity-0 translate-y-12 transition-all duration-1000 delay-100 ease-out text-4xl lg:text-5xl font-bold text-gray-800 mb-6">
                    Realce sua Beleza com Especialistas
                </h2>
                <p class="reveal opacity-0 translate-y-12 transition-all duration-1000 delay-200 ease-out text-gray-700 max-w-2xl mx-auto mb-16 text-md leading-relaxed">
                    Conheça nosso portfólio de serviços desenvolvidos para cuidar da saúde e estética dos seus cabelos com os melhores produtos do mercado.
                </p>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 text-left">
                    <div class="reveal opacity-0 translate-y-12 transition-all duration-1000 delay-100 ease-out relative h-[450px] rounded-[2rem] overflow-hidden group shadow-lg">
                        <img
                            src="https://images.unsplash.com/photo-1700760934268-8aa0ef52ce0a?q=80&w=764&auto=format&fit=crop"
                            alt="Corte Feminino"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/40 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-300"></div>
                        <div class="absolute bottom-0 left-0 w-full p-8 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                            <h3 class="text-2xl font-bold text-white mb-2">Corte e Visagismo</h3>
                            <p class="text-gray-200 text-sm mb-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-100 leading-relaxed">
                                Cortes que harmonizam com o formato do seu rosto e estilo de vida.
                            </p>
                            <Link
                                :href="route('agendar')"
                                class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-[#547558] hover:text-white transition-all duration-300 ml-auto border border-white/30">
                                <svg class="w-5 h-5 transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <div class="reveal opacity-0 translate-y-12 transition-all duration-1000 delay-200 ease-out relative h-[450px] rounded-[2rem] overflow-hidden group shadow-lg">
                        <img
                            src="https://images.unsplash.com/photo-1554519934-e32b1629d9ee?q=80&w=687&auto=format&fit=crop"
                            alt="Coloração e Mechas"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/40 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-300"></div>
                        <div class="absolute bottom-0 left-0 w-full p-8 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                            <h3 class="text-2xl font-bold text-white mb-2">Coloração e Mechas</h3>
                            <p class="text-gray-200 text-sm mb-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-100 leading-relaxed">
                                Morenas iluminadas, loiros impecáveis e cores vibrantes.
                            </p>
                            <Link
                                :href="route('agendar')"
                                class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-[#547558] hover:text-white transition-all duration-300 ml-auto border border-white/30">
                                <svg class="w-5 h-5 transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <div class="reveal opacity-0 translate-y-12 transition-all duration-1000 delay-300 ease-out relative h-[450px] rounded-[2rem] overflow-hidden group shadow-lg">
                        <img
                            src="https://images.unsplash.com/photo-1634449571010-02389ed0f9b0?q=80&w=1170&auto=format&fit=crop"
                            alt="Tratamentos Capilares"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/40 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-300"></div>
                        <div class="absolute bottom-0 left-0 w-full p-8 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                            <h3 class="text-2xl font-bold text-white mb-2">Tratamentos Capilares</h3>
                            <p class="text-gray-200 text-sm mb-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-100 leading-relaxed">
                                Cronograma capilar, hidratação profunda e reconstrução de fios.
                            </p>
                            <Link
                                :href="route('agendar')"
                                class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-[#547558] hover:text-white transition-all duration-300 ml-auto border border-white/30">
                                <svg class="w-5 h-5 transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <div class="reveal opacity-0 translate-y-12 transition-all duration-1000 delay-500 ease-out relative h-[450px] rounded-[2rem] overflow-hidden group shadow-lg">
                        <img
                            src="https://images.unsplash.com/photo-1611826585949-b0ccabd2c1a4?q=80&w=687&auto=format&fit=crop"
                            alt="Penteados e Maquiagem"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/40 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-300"></div>
                        <div class="absolute bottom-0 left-0 w-full p-8 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                            <h3 class="text-2xl font-bold text-white mb-2">Penteados e Make</h3>
                            <p class="text-gray-200 text-sm mb-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-100 leading-relaxed">
                                Produção completa para noivas, formaturas e eventos sociais.
                            </p>
                            <Link
                                :href="route('agendar')"
                                class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-[#547558] hover:text-white transition-all duration-300 ml-auto border border-white/30">
                                <svg class="w-5 h-5 transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </SiteLayout>
</template>
