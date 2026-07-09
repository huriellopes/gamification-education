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

        <div class="min-h-[calc(100vh-80px)] bg-zinc-950 py-8 text-zinc-100">
            <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Cabeçalho: identidade do usuário -->
                <div
                    class="relative flex flex-col gap-4 overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md sm:flex-row sm:items-center"
                >
                    <div
                        class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-indigo-600/10 blur-2xl"
                    />
                    <div
                        class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-xl font-black text-white shadow-lg shadow-indigo-600/20"
                    >
                        {{ initials }}
                    </div>
                    <div class="min-w-0">
                        <h1 class="truncate text-xl font-bold text-white">
                            {{ user?.name }}
                        </h1>
                        <p class="truncate text-sm text-zinc-400">
                            {{ user?.email }}
                        </p>
                        <span
                            class="mt-2 inline-flex items-center rounded-full border border-indigo-500/30 bg-indigo-500/10 px-3 py-0.5 text-xs font-bold text-indigo-300"
                        >
                            {{ roleLabel }}
                        </span>
                    </div>
                </div>

                <!-- Informações do perfil -->
                <div
                    class="rounded-2xl border border-l-4 border-zinc-800 border-l-indigo-500 bg-zinc-900/30 p-6 backdrop-blur-md transition-colors hover:border-zinc-700 sm:p-8"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <!-- Atualização de senha -->
                <div
                    class="rounded-2xl border border-l-4 border-zinc-800 border-l-amber-500 bg-zinc-900/30 p-6 backdrop-blur-md transition-colors hover:border-zinc-700 sm:p-8"
                >
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <!-- Autenticação de dois fatores -->
                <div
                    class="rounded-2xl border border-l-4 border-zinc-800 border-l-emerald-500 bg-zinc-900/30 p-6 backdrop-blur-md transition-colors hover:border-zinc-700 sm:p-8"
                >
                    <TwoFactorAuthenticationForm />
                </div>

                <!-- Exclusão de conta -->
                <div
                    class="rounded-2xl border border-l-4 border-zinc-800 border-l-rose-500 bg-zinc-900/30 p-6 backdrop-blur-md transition-colors hover:border-rose-500/40 sm:p-8"
                >
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
