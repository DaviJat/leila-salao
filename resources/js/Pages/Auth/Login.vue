<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
// Removi o PrimaryButton da importação
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

// Controle a animação de entrada
const isLoaded = ref(false);

onMounted(() => {
    setTimeout(() => {
        isLoaded.value = true;
    }, 50);
});
</script>

<template>
    <Head title="Entrar - Cabeleila" />

    <div class="min-h-screen flex font-sans text-gray-800">
        <div
            class="flex-1 flex flex-col justify-center py-12 px-6 sm:px-12 lg:flex-none lg:w-1/2 xl:px-24 relative z-10 transition-all duration-1000 ease-out bg-[url('/images/background-hero.png')] bg-cover bg-center lg:bg-none lg:bg-[#FAF8F5]"
            :class="isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">
            <div class="absolute inset-0 bg-[#FAF8F5]/80 backdrop-blur-[2px] lg:hidden"></div>

            <div class="absolute top-8 left-6 sm:left-12 xl:left-24 z-20">
                <Link href="/" class="flex items-center text-sm font-medium text-gray-600 hover:text-[#547558] transition-colors group">
                    <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar ao site
                </Link>
            </div>

            <div class="mx-auto w-full max-w-sm lg:w-96 mt-12 lg:mt-0 relative z-20">
                <div class="flex items-center gap-3 mb-10">
                    <img src="/images/logo-cabeleila.svg" alt="Logo Cabeleila" class="h-10 w-auto object-contain" />
                </div>

                <h2 class="text-3xl font-bold text-gray-900 leading-tight">Bem-vinda de volta</h2>
                <p class="mt-2 text-sm text-gray-600 mb-8">Por favor, insira seus dados para acessar sua conta.</p>

                <div v-if="status" class="mb-4 text-sm font-medium text-[#547558] p-4 bg-[#547558]/10 rounded-xl">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="email" value="E-mail" class="text-gray-900 font-bold" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-2 block w-full rounded-full border-gray-300 focus:border-[#547558] focus:ring focus:ring-[#547558] focus:ring-opacity-20 px-5 py-3 shadow-sm transition-colors"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="exemplo@email.com" />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Senha" class="text-gray-900 font-bold" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-2 block w-full rounded-full border-gray-300 focus:border-[#547558] focus:ring focus:ring-[#547558] focus:ring-opacity-20 px-5 py-3 shadow-sm transition-colors"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••" />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer group">
                            <Checkbox
                                name="remember"
                                v-model:checked="form.remember"
                                class="text-[#547558] focus:ring-[#547558] rounded border-gray-400 group-hover:border-[#547558] transition-colors" />
                            <span class="ms-2 text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors">Lembrar-me</span>
                        </label>

                        <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm font-bold text-[#547558] hover:text-[#435e46] transition-colors">
                            Esqueceu a senha?
                        </Link>
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-full shadow-sm text-sm font-bold text-white bg-[#547558] hover:bg-[#435e46] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#547558] transition-all transform hover:-translate-y-0.5"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                            :disabled="form.processing">
                            Entrar na Conta
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div
            class="hidden lg:block relative w-0 flex-1 bg-gray-900 transition-all duration-1000 delay-200 ease-out"
            :class="isLoaded ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-12'">
            <img class="absolute inset-0 h-full w-full object-cover opacity-90" src="/images/foto-salao-login.png" alt="Ambiente do salão" />

            <div class="absolute inset-0 bg-gradient-to-t from-[#547558]/80 via-transparent to-transparent"></div>

            <div class="absolute bottom-12 left-12 right-12 text-white">
                <blockquote class="space-y-4">
                    <p class="text-2xl font-bold leading-tight">"O cuidado que você merece, a autoestima que você conquista."</p>
                    <footer class="text-sm font-medium text-white/80">Equipe Cabeleila</footer>
                </blockquote>
            </div>
        </div>
    </div>
</template>
