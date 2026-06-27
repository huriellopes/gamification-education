<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    teachers: {
        type: Array,
        default: () => [],
    },
    students: {
        type: Array,
        default: () => [],
    },
});

const isModalOpen = ref(false);
const activeTab = ref('students'); // 'students' ou 'teachers'

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'student',
});

const submit = () => {
    form.post(route('admin.users.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Gerenciar Membros" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-zinc-100">
                Gerenciar Alunos & Professores da Instituição
            </h2>
        </template>

        <div class="min-h-[calc(100vh-64px)] bg-zinc-950 py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <!-- Abas -->
                    <div
                        class="flex rounded-xl border border-zinc-800 bg-zinc-900 p-1"
                    >
                        <button
                            @click="activeTab = 'students'"
                            :class="[
                                'rounded-lg px-4 py-2 text-sm font-semibold transition-all',
                                activeTab === 'students'
                                    ? 'bg-zinc-800 text-white shadow-md'
                                    : 'text-zinc-400 hover:text-zinc-200',
                            ]"
                        >
                            Alunos ({{ students.length }})
                        </button>
                        <button
                            @click="activeTab = 'teachers'"
                            :class="[
                                'rounded-lg px-4 py-2 text-sm font-semibold transition-all',
                                activeTab === 'teachers'
                                    ? 'bg-zinc-800 text-white shadow-md'
                                    : 'text-zinc-400 hover:text-zinc-200',
                            ]"
                        >
                            Professores ({{ teachers.length }})
                        </button>
                    </div>

                    <!-- Botão Novo -->
                    <button
                        @click="isModalOpen = true"
                        class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-900/30 transition-all duration-200 hover:from-violet-500 hover:to-indigo-500"
                    >
                        + Cadastrar Membro
                    </button>
                </div>

                <!-- Lista de Alunos -->
                <div
                    v-if="activeTab === 'students'"
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <h3 class="mb-4 text-lg font-bold text-white">
                        Estudantes Matriculados
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr
                                    class="border-b border-zinc-800 text-xs font-bold uppercase tracking-wider text-zinc-400"
                                >
                                    <th class="px-4 py-3">Nome</th>
                                    <th class="px-4 py-3">E-mail</th>
                                    <th class="px-4 py-3 text-center">
                                        XP Acumulado
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800 text-sm">
                                <tr
                                    v-for="std in students"
                                    :key="std.id"
                                    class="transition-colors hover:bg-zinc-800/30"
                                >
                                    <td
                                        class="px-4 py-4 font-semibold text-zinc-100"
                                    >
                                        {{ std.name }}
                                    </td>
                                    <td class="px-4 py-4 text-zinc-400">
                                        {{ std.email }}
                                    </td>
                                    <td
                                        class="px-4 py-4 text-center font-bold text-emerald-400"
                                    >
                                        {{ std.points }} XP
                                    </td>
                                </tr>
                                <tr v-if="students.length === 0">
                                    <td
                                        colspan="3"
                                        class="py-8 text-center text-zinc-500"
                                    >
                                        Nenhum estudante cadastrado.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Lista de Professores -->
                <div
                    v-if="activeTab === 'teachers'"
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <h3 class="mb-4 text-lg font-bold text-white">
                        Professores da Instituição
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr
                                    class="border-b border-zinc-800 text-xs font-bold uppercase tracking-wider text-zinc-400"
                                >
                                    <th class="px-4 py-3">Nome</th>
                                    <th class="px-4 py-3">E-mail</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800 text-sm">
                                <tr
                                    v-for="tchr in teachers"
                                    :key="tchr.id"
                                    class="transition-colors hover:bg-zinc-800/30"
                                >
                                    <td
                                        class="px-4 py-4 font-semibold text-zinc-100"
                                    >
                                        {{ tchr.name }}
                                    </td>
                                    <td class="px-4 py-4 text-zinc-400">
                                        {{ tchr.email }}
                                    </td>
                                </tr>
                                <tr v-if="teachers.length === 0">
                                    <td
                                        colspan="2"
                                        class="py-8 text-center text-zinc-500"
                                    >
                                        Nenhum professor cadastrado.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Cadastrar Membro -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900 p-6 shadow-2xl"
            >
                <h3 class="text-lg font-bold text-white">
                    Novo Membro da Instituição
                </h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >Função / Perfil</label
                        >
                        <select
                            v-model="form.role"
                            required
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                            <option value="student">Estudante (Aluno)</option>
                            <option value="teacher">Professor</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >Nome Completo</label
                        >
                        <input
                            v-model="form.name"
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
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >Senha Inicial</label
                        >
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="isModalOpen = false"
                            class="rounded-xl border border-zinc-700 bg-transparent px-4 py-2.5 text-sm font-semibold text-zinc-300 transition-all hover:bg-zinc-800"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
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
