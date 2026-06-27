<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    institutions: {
        type: Array,
        default: () => [],
    },
    admins: {
        type: Array,
        default: () => [],
    },
});

const isInstitutionModalOpen = ref(false);
const isAdminModalOpen = ref(false);

const instForm = useForm({
    name: '',
    description: '',
});

const adminForm = useForm({
    institution_id: '',
    name: '',
    email: '',
    password: '',
});

const submitInstitution = () => {
    instForm.post(route('super-admin.institutions.store'), {
        onSuccess: () => {
            isInstitutionModalOpen.value = false;
            instForm.reset();
        },
    });
};

const submitAdmin = () => {
    adminForm.post(route('super-admin.admins.store'), {
        onSuccess: () => {
            isAdminModalOpen.value = false;
            adminForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Painel Super Admin" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-zinc-100">
                Painel Super Administrador — Plataforma de Gamificação
            </h2>
        </template>

        <div class="min-h-[calc(100vh-64px)] bg-zinc-950 py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Ações do Super Admin -->
                <div class="flex flex-wrap gap-4">
                    <button
                        @click="isInstitutionModalOpen = true"
                        class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-900/30 transition-all duration-200 hover:from-violet-500 hover:to-indigo-500"
                    >
                        + Cadastrar Instituição
                    </button>
                    <button
                        @click="isAdminModalOpen = true"
                        class="rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-teal-900/30 transition-all duration-200 hover:from-emerald-500 hover:to-teal-500"
                    >
                        + Cadastrar Administrador
                    </button>
                </div>

                <!-- Listagem de Instituições -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <h3 class="mb-4 text-lg font-bold text-white">
                        Instituições Ativas
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr
                                    class="border-b border-zinc-800 text-xs font-bold uppercase tracking-wider text-zinc-400"
                                >
                                    <th class="px-4 py-3">Nome</th>
                                    <th class="px-4 py-3">Descrição</th>
                                    <th class="px-4 py-3 text-center">
                                        Matérias
                                    </th>
                                    <th class="px-4 py-3 text-center">
                                        Membros
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800 text-sm">
                                <tr
                                    v-for="inst in institutions"
                                    :key="inst.id"
                                    class="transition-colors hover:bg-zinc-800/30"
                                >
                                    <td
                                        class="px-4 py-4 font-semibold text-zinc-100"
                                    >
                                        {{ inst.name }}
                                    </td>
                                    <td
                                        class="max-w-xs truncate px-4 py-4 text-zinc-400"
                                    >
                                        {{ inst.description || 'N/A' }}
                                    </td>
                                    <td
                                        class="px-4 py-4 text-center font-bold text-indigo-400"
                                    >
                                        {{ inst.subjects_count }}
                                    </td>
                                    <td
                                        class="px-4 py-4 text-center font-bold text-emerald-400"
                                    >
                                        {{ inst.users_count }}
                                    </td>
                                </tr>
                                <tr v-if="institutions.length === 0">
                                    <td
                                        colspan="4"
                                        class="py-8 text-center text-zinc-500"
                                    >
                                        Nenhuma instituição cadastrada.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Listagem de Administradores -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <h3 class="mb-4 text-lg font-bold text-white">
                        Administradores de Instituição
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr
                                    class="border-b border-zinc-800 text-xs font-bold uppercase tracking-wider text-zinc-400"
                                >
                                    <th class="px-4 py-3">Nome</th>
                                    <th class="px-4 py-3">E-mail</th>
                                    <th class="px-4 py-3">Instituição</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800 text-sm">
                                <tr
                                    v-for="adm in admins"
                                    :key="adm.id"
                                    class="transition-colors hover:bg-zinc-800/30"
                                >
                                    <td
                                        class="px-4 py-4 font-medium text-zinc-100"
                                    >
                                        {{ adm.name }}
                                    </td>
                                    <td class="px-4 py-4 text-zinc-400">
                                        {{ adm.email }}
                                    </td>
                                    <td class="px-4 py-4 text-indigo-400">
                                        {{
                                            adm.institution
                                                ? adm.institution.name
                                                : 'N/A'
                                        }}
                                    </td>
                                </tr>
                                <tr v-if="admins.length === 0">
                                    <td
                                        colspan="3"
                                        class="py-8 text-center text-zinc-500"
                                    >
                                        Nenhum administrador cadastrado.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Nova Instituição -->
        <div
            v-if="isInstitutionModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900 p-6 shadow-2xl"
            >
                <h3 class="text-lg font-bold text-white">Nova Instituição</h3>
                <form @submit.prevent="submitInstitution" class="space-y-4">
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >Nome</label
                        >
                        <input
                            v-model="instForm.name"
                            type="text"
                            required
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >Descrição</label
                        >
                        <textarea
                            v-model="instForm.description"
                            rows="3"
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        ></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="isInstitutionModalOpen = false"
                            class="rounded-xl border border-zinc-700 bg-transparent px-4 py-2.5 text-sm font-semibold text-zinc-300 transition-all hover:bg-zinc-800"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="instForm.processing"
                            class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white transition-all hover:bg-indigo-500 disabled:opacity-55"
                        >
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Novo Administrador -->
        <div
            v-if="isAdminModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900 p-6 shadow-2xl"
            >
                <h3 class="text-lg font-bold text-white">
                    Novo Administrador de Instituição
                </h3>
                <form @submit.prevent="submitAdmin" class="space-y-4">
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >Instituição</label
                        >
                        <select
                            v-model="adminForm.institution_id"
                            required
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                            <option value="">Selecione uma instituição</option>
                            <option
                                v-for="inst in institutions"
                                :key="inst.id"
                                :value="inst.id"
                            >
                                {{ inst.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >Nome</label
                        >
                        <input
                            v-model="adminForm.name"
                            type="text"
                            required
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >E-mail</label
                        >
                        <input
                            v-model="adminForm.email"
                            type="email"
                            required
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >Senha</label
                        >
                        <input
                            v-model="adminForm.password"
                            type="password"
                            required
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="isAdminModalOpen = false"
                            class="rounded-xl border border-zinc-700 bg-transparent px-4 py-2.5 text-sm font-semibold text-zinc-300 transition-all hover:bg-zinc-800"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="adminForm.processing"
                            class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white transition-all hover:bg-indigo-500 disabled:opacity-55"
                        >
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
