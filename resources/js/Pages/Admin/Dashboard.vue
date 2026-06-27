<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    students: {
        type: Array,
        required: true,
        default: () => []
    },
    stats: {
        type: Object,
        required: true
    }
});

const searchQuery = ref('');

const filteredStudents = computed(() => {
    if (!searchQuery.value) return props.students;
    const query = searchQuery.value.toLowerCase();
    return props.students.filter(student => 
        student.name.toLowerCase().includes(query) || 
        student.email.toLowerCase().includes(query) ||
        (student.institution && student.institution.name.toLowerCase().includes(query))
    );
});
</script>

<template>
    <Head title="Painel Administrativo" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-zinc-100">
                Painel Administrativo — Gamificação
            </h2>
        </template>

        <div class="py-12 bg-zinc-950 min-h-[calc(100vh-64px)] text-zinc-100">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">
                
                <!-- Cards de Estatísticas com Estética Premium -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Card Alunos -->
                    <div class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 shadow-xl backdrop-blur-md">
                        <div class="absolute top-0 right-0 p-4 opacity-10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-24 w-24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A9.342 9.342 0 0 1 12.625 19.5a9.379 9.379 0 0 1-4.12-.952 4.125 4.125 0 0 1-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M12.625 19.5a9.38 9.38 0 0 1-2.625.372 9.337 9.337 0 0 1-4.121-.952 4.125 4.125 0 0 1 7.533-2.493M12.625 19.5v-.003a9.38 9.38 0 0 0 2.625-.372" />
                            </svg>
                        </div>
                        <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Total de Alunos</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-white">{{ stats.total_students }}</h3>
                        <p class="mt-1 text-xs text-indigo-400">Ativos na plataforma</p>
                    </div>

                    <!-- Card Instituições -->
                    <div class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 shadow-xl backdrop-blur-md">
                        <div class="absolute top-0 right-0 p-4 opacity-10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-24 w-24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.33l-7.5-5-7.5 5V21m16.5 0H3" />
                            </svg>
                        </div>
                        <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Instituições</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-white">{{ stats.total_institutions }}</h3>
                        <Link :href="route('admin.institutions.index')" class="mt-1 inline-block text-xs text-indigo-400 hover:text-indigo-300 hover:underline">Gerenciar Instituições &rarr;</Link>
                    </div>

                    <!-- Card Matérias -->
                    <div class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 shadow-xl backdrop-blur-md">
                        <div class="absolute top-0 right-0 p-4 opacity-10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-24 w-24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg>
                        </div>
                        <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Matérias Ativas</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-white">{{ stats.total_subjects }}</h3>
                        <Link :href="route('admin.subjects.index')" class="mt-1 inline-block text-xs text-indigo-400 hover:text-indigo-300 hover:underline">Ver Matérias &rarr;</Link>
                    </div>

                    <!-- Card Pontos -->
                    <div class="relative overflow-hidden rounded-2xl border border-zinc-500/10 bg-zinc-900/50 p-6 shadow-xl backdrop-blur-md">
                        <div class="absolute top-0 right-0 p-4 opacity-10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-24 w-24 text-yellow-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.195-.12a3.375 3.375 0 0 0 3-3.812L12 3 6.625 6.223a3.375 3.375 0 0 0 3 3.812L10 13.182" />
                            </svg>
                        </div>
                        <p class="text-xs font-bold uppercase tracking-wider text-yellow-500/80">Pontos Distribuídos</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-yellow-400">{{ stats.total_points_distributed.toLocaleString() }}</h3>
                        <p class="mt-1 text-xs text-zinc-500">Acumulado por estudantes</p>
                    </div>
                </div>

                <!-- Seção de Alunos e Busca -->
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-sm">
                    <div class="sm:flex sm:items-center sm:justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-white">Desempenho dos Alunos</h3>
                            <p class="mt-1 text-sm text-zinc-400">Classificação e pontuação atual de todos os estudantes cadastrados.</p>
                        </div>
                        <!-- Input de Busca -->
                        <div class="mt-4 sm:mt-0 sm:ml-4">
                            <div class="relative rounded-md shadow-sm">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Buscar aluno ou instituição..."
                                    class="w-full sm:w-64 rounded-xl border border-zinc-850 bg-zinc-900 px-4 py-2 text-sm text-zinc-100 placeholder-zinc-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Alunos -->
                    <div class="overflow-x-auto rounded-xl border border-zinc-850 bg-zinc-900/20">
                        <table class="min-w-full divide-y divide-zinc-800/80 text-left text-sm">
                            <thead class="bg-zinc-900/50 text-xs font-bold uppercase tracking-wider text-zinc-400">
                                <tr>
                                    <th scope="col" class="px-6 py-4">Aluno</th>
                                    <th scope="col" class="px-6 py-4">E-mail</th>
                                    <th scope="col" class="px-6 py-4">Instituição de Ensino</th>
                                    <th scope="col" class="px-6 py-4 text-right">Pontuação acumulada</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-850/50 text-zinc-300">
                                <tr v-for="student in filteredStudents" :key="student.id" class="transition-colors hover:bg-zinc-800/10">
                                    <td class="whitespace-nowrap px-6 py-4 font-semibold text-white">{{ student.name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-zinc-450">{{ student.email }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-indigo-400">{{ student.institution ? student.institution.name : 'Nenhuma' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold text-yellow-400">{{ student.points.toLocaleString() }} <span class="text-[10px] text-zinc-500 font-semibold uppercase">pts</span></td>
                                </tr>
                                <tr v-if="filteredStudents.length === 0">
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-zinc-500">Nenhum aluno encontrado correspondente à busca.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
