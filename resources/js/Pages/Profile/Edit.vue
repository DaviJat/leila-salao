<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Perfil" />

    <AuthenticatedLayout>
        <template #header>
            <div class="px-6">
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Perfil</h2>
                <p class="text-sm text-gray-500 mt-1">Atualize seus dados de acesso e preferências da conta.</p>
            </div>
        </template>

        <div class="space-y-6">
            <div class="bg-white/90 border border-gray-100 rounded-3xl p-6 shadow-sm">
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" class="max-w-2xl" />
            </div>

            <div class="bg-white/90 border border-gray-100 rounded-3xl p-6 shadow-sm">
                <UpdatePasswordForm class="max-w-2xl" />
            </div>

            <div class="bg-white/90 border border-gray-100 rounded-3xl p-6 shadow-sm">
                <DeleteUserForm class="max-w-2xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
