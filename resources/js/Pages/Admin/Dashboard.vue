<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    students: {
        type: Array,
        required: true,
        default: () => [],
    },
    teachers: {
        type: Array,
        required: true,
        default: () => [],
    },
    stats: {
        type: Object,
        required: true,
    },
});

const searchQuery = ref('');

const filteredStudents = computed(() => {
    if (!searchQuery.value) return props.students;
    const query = searchQuery.value.toLowerCase();
    return props.students.filter(
        (student) =>
            student.name.toLowerCase().includes(query) ||
            student.email.toLowerCase().includes(query),
    );
});
</script>

<template>
    <Head title="Painel Administrativo" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-zinc-100">
                Painel Administrativo —
                {{
                    $page.props.auth.user.institution?.name ||
                    'Minha Instituição'
                }}
            </h2>
        </template>

        <div class="min-h-[calc(100vh-64px)] bg-zinc-950 py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Cards de Estatísticas com Estética Premium -->
                <div
                    class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <!-- Card Alunos -->
                    <div
                        class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 shadow-xl backdrop-blur-md"
                    >
                        <div class="absolute right-0 top-0 p-4 opacity-10">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-20 w-20 text-white"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A9.342 9.342 0 0 1 12.625 19.5a9.379 9.379 0 0 1-4.12-.952 4.125 4.125 0 0 1-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M12.625 19.5a9.38 9.38 0 0 1-2.625.372 9.337 9.337 0 0 1-4.121-.952 4.125 4.125 0 0 1 7.533-2.493M12.625 19.5v-.003a9.38 9.38 0 0 0 2.625-.372"
                                />
                            </svg>
                        </div>
                        <p
                            class="text-zinc-550 text-xs font-bold uppercase tracking-wider"
                        >
                            Alunos Matriculados
                        </p>
                        <h3 class="mt-2 text-3xl font-extrabold text-white">
                            {{ stats.total_students }}
                        </h3>
                        <Link
                            :href="route('admin.users.index')"
                            class="mt-2 inline-block text-xs font-semibold text-indigo-400 transition-colors hover:text-indigo-300"
                        >
                            Gerenciar Alunos &rarr;
                        </Link>
                    </div>

                    <!-- Card Professores -->
                    <div
                        class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 shadow-xl backdrop-blur-md"
                    >
                        <div class="absolute right-0 top-0 p-4 opacity-10">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-20 w-20 text-white"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M22.25 9.313a11.975 11.975 0 0 1-2.81 7.832m-11.214-.07a11.975 11.975 0 0 1-2.81-7.832"
                                />
                            </svg>
                        </div>
                        <p
                            class="text-zinc-550 text-xs font-bold uppercase tracking-wider"
                        >
                            Corpo Docente (Professores)
                        </p>
                        <h3 class="mt-2 text-3xl font-extrabold text-white">
                            {{ stats.total_teachers }}
                        </h3>
                        <Link
                            :href="route('admin.users.index')"
                            class="mt-2 inline-block text-xs font-semibold text-indigo-400 transition-colors hover:text-indigo-300"
                        >
                            Gerenciar Professores &rarr;
                        </Link>
                    </div>

                    <!-- Card Matérias -->
                    <div
                        class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 shadow-xl backdrop-blur-md"
                    >
                        <div class="absolute right-0 top-0 p-4 opacity-10">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-20 w-20 text-white"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"
                                />
                            </svg>
                        </div>
                        <p
                            class="text-zinc-550 text-xs font-bold uppercase tracking-wider"
                        >
                            Matérias Ativas
                        </p>
                        <h3 class="mt-2 text-3xl font-extrabold text-white">
                            {{ stats.total_subjects }}
                        </h3>
                        <Link
                            :href="route('admin.subjects.index')"
                            class="mt-2 inline-block text-xs font-semibold text-indigo-400 transition-colors hover:text-indigo-300"
                        >
                            Gerenciar Matérias &rarr;
                        </Link>
                    </div>
                </div>

                <!-- Painel de Relatório e Desempenho (Tabela de Alunos) -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <div
                        class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
                    >
                        <div>
                            <h3 class="text-lg font-bold text-white">
                                Classificação de Alunos (Internal Ranking)
                            </h3>
                            <p class="text-xs text-zinc-400">
                                Veja o desempenho geral e a pontuação XP dos
                                alunos da sua instituição.
                            </p>
                        </div>
                        <div class="relative w-full max-w-xs">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Buscar aluno..."
                                class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 placeholder-zinc-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            />
                        </div>
                    </div>

                    <div class="mt-6 overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr
                                    class="border-b border-zinc-800 text-xs font-bold uppercase tracking-wider text-zinc-400"
                                >
                                    <th class="px-4 py-3 text-center">#</th>
                                    <th class="px-4 py-3">Nome</th>
                                    <th class="px-4 py-3">E-mail</th>
                                    <th class="px-4 py-3 text-right">
                                        XP Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800 text-sm">
                                <tr
                                    v-for="(student, idx) in filteredStudents"
                                    :key="student.id"
                                    class="transition-colors hover:bg-zinc-800/20"
                                >
                                    <td class="px-4 py-4 text-center">
                                        <span
                                            v-if="idx === 0"
                                            class="inline-block rounded bg-amber-500/10 px-2 py-0.5 text-xs font-bold text-amber-500"
                                            >🥇 1º</span
                                        >
                                        <span
                                            v-else-if="idx === 1"
                                            class="inline-block rounded bg-zinc-300/10 px-2 py-0.5 text-xs font-bold text-zinc-300"
                                            >🥈 2º</span
                                        >
                                        <span
                                            v-else-if="idx === 2"
                                            class="inline-block rounded bg-amber-700/10 px-2 py-0.5 text-xs font-bold text-amber-700"
                                            >🥉 3º</span
                                        >
                                        <span
                                            v-else
                                            class="text-xs font-semibold text-zinc-500"
                                            >{{ idx + 1 }}º</span
                                        >
                                    </td>
                                    <td
                                        class="px-4 py-4 font-semibold text-white"
                                    >
                                        {{ student.name }}
                                    </td>
                                    <td class="px-4 py-4 text-zinc-400">
                                        {{ student.email }}
                                    </td>
                                    <td
                                        class="px-4 py-4 text-right font-black text-emerald-400"
                                    >
                                        {{ student.points }} XP
                                    </td>
                                </tr>
                                <tr v-if="filteredStudents.length === 0">
                                    <td
                                        colspan="4"
                                        class="py-8 text-center text-zinc-500"
                                    >
                                        Nenhum aluno encontrado.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Painel de Corpo Docente -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <h3 class="mb-2 text-lg font-bold text-white">
                        Professores Cadastrados
                    </h3>
                    <p class="mb-6 text-xs text-zinc-400">
                        Lista de professores que podem lecionar e publicar
                        conteúdos para os alunos.
                    </p>
                    <div
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <div
                            v-for="tchr in teachers"
                            :key="tchr.id"
                            class="flex flex-col justify-between rounded-xl border border-zinc-800 bg-zinc-900/40 p-4"
                        >
                            <div>
                                <div class="font-semibold text-white">
                                    {{ tchr.name }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    {{ tchr.email }}
                                </div>
                            </div>
                        </div>
                        <div
                            v-if="teachers.length === 0"
                            class="col-span-full py-8 text-center text-sm text-zinc-500"
                        >
                            Nenhum professor cadastrado nesta instituição ainda.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
