<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import TwoFactorAuthenticationForm from './Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = computed(() => usePage().props.auth.user);

const initials = computed(() => {
    const parts = (user.value?.name ?? '').trim().split(/\s+/);
    if (parts.length === 0) return '?';
    if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
    return (
        parts[0].charAt(0) + parts[parts.length - 1].charAt(0)
    ).toUpperCase();
});

const roleLabel = computed(() => {
    const map = {
        super_admin: __('superadmin.users.role_super_admin'),
        admin: __('superadmin.users.role_admin'),
        teacher: __('superadmin.users.role_teacher'),
        student: __('superadmin.users.role_student'),
    };
    return map[user.value?.role] ?? user.value?.role;
});
</script>

<template>
    <Head :title="__('profile.title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="__('profile.title')" />
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Cabeçalho: identidade do usuário -->
                <div
                    class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:flex-row sm:items-center"
                >
                    <div
                        class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-xl font-black text-white shadow-md"
                    >
                        {{ initials }}
                    </div>
                    <div class="min-w-0">
                        <h1
                            class="truncate text-xl font-bold text-gray-900 dark:text-white"
                        >
                            {{ user?.name }}
                        </h1>
                        <p
                            class="truncate text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{ user?.email }}
                        </p>
                        <span
                            class="mt-2 inline-flex items-center rounded-full bg-indigo-500/10 px-3 py-0.5 text-xs font-bold text-indigo-500 dark:text-indigo-400"
                        >
                            {{ roleLabel }}
                        </span>
                    </div>
                </div>

                <!-- Informações do perfil -->
                <div
                    class="rounded-2xl border border-l-4 border-gray-200 border-l-indigo-500 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:border-l-indigo-500 dark:bg-gray-800 sm:p-8"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <!-- Atualização de senha -->
                <div
                    class="rounded-2xl border border-l-4 border-gray-200 border-l-amber-500 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:border-l-amber-500 dark:bg-gray-800 sm:p-8"
                >
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <!-- Autenticação de dois fatores -->
                <div
                    class="rounded-2xl border border-l-4 border-gray-200 border-l-emerald-500 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:border-l-emerald-500 dark:bg-gray-800 sm:p-8"
                >
                    <TwoFactorAuthenticationForm />
                </div>

                <!-- Exclusão de conta -->
                <div
                    class="rounded-2xl border border-l-4 border-rose-200 border-l-rose-500 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-rose-500/30 dark:border-l-rose-500 dark:bg-gray-800 sm:p-8"
                >
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
