<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    student: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head :title="`Desempenho - ${student.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('teacher.students.index')"
                    class="text-zinc-400 transition-colors hover:text-white"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2.5"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"
                        />
                    </svg>
                </Link>
                <div>
                    <h2 class="text-xl font-bold leading-tight text-zinc-100">
                        Desempenho de {{ student.name }}
                    </h2>
                    <p class="text-zinc-550 text-xs">
                        {{ student.email }}
                    </p>
                </div>
            </div>
        </template>

        <div class="min-h-[calc(100vh-64px)] bg-zinc-955 py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Summary Card -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4"
                >
                    <div>
                        <h3 class="text-lg font-bold text-white">XP Acumulado</h3>
                        <p class="text-sm text-zinc-400">Total de pontos acumulados na plataforma.</p>
                    </div>
                    <span class="text-3xl font-extrabold text-indigo-400">{{ student.points }} XP</span>
                </div>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Attempts History -->
                    <div
                        class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6"
                    >
                        <h3 class="mb-4 text-lg font-bold text-white">Histórico de Quizzes Realizados</h3>
                        <div class="space-y-4">
                            <div
                                v-for="att in student.attempts"
                                :key="att.id"
                                class="flex items-center justify-between rounded-xl border border-zinc-800 bg-zinc-950/20 p-4"
                            >
                                <div>
                                    <h4 class="text-sm font-bold text-zinc-100">
                                        {{ att.test_title }}
                                    </h4>
                                    <p class="text-xs text-zinc-500 mt-1">
                                        Data de envio: {{ att.completed_at }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-semibold text-zinc-300">
                                        Acertos: {{ att.correct_answers }} / {{ att.total_questions }}
                                    </div>
                                    <div class="text-sm font-bold text-indigo-400 mt-0.5">
                                        +{{ att.score }} XP
                                    </div>
                                </div>
                            </div>

                            <div v-if="student.attempts.length === 0" class="text-center py-8 text-zinc-500 text-sm">
                                Nenhuma avaliação realizada por este estudante.
                            </div>
                        </div>
                    </div>

                    <!-- Score History -->
                    <div
                        class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6"
                    >
                        <h3 class="mb-4 text-lg font-bold text-white">Histórico Geral de Pontos</h3>
                        <div class="space-y-4">
                            <div
                                v-for="hist in student.score_history"
                                :key="hist.id"
                                class="flex items-center justify-between rounded-xl border border-zinc-800 bg-zinc-955/20 p-4"
                            >
                                <div>
                                    <h4 class="text-sm font-semibold text-zinc-200">
                                        {{ hist.description }}
                                    </h4>
                                    <p class="text-xs text-zinc-500 mt-1">
                                        Concedido em: {{ hist.created_at }}
                                    </p>
                                </div>
                                <span class="text-sm font-bold" :class="hist.points >= 0 ? 'text-emerald-400' : 'text-red-400'">
                                    {{ hist.points >= 0 ? '+' : '' }}{{ hist.points }} XP
                                </span>
                            </div>

                            <div v-if="student.score_history.length === 0" class="text-center py-8 text-zinc-500 text-sm">
                                Nenhum registro de histórico de pontuação.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
