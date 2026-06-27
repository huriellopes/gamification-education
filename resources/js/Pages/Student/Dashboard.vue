<script setup>
import LeaderboardWidget from '@/Components/LeaderboardWidget.vue';
import PointsBadge from '@/Components/PointsBadge.vue';
import SubjectCard from '@/Components/SubjectCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    subjects: {
        type: Array,
        default: () => [],
    },
    scoreHistory: {
        type: Array,
        default: () => [],
    },
    leaderboard: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        required: true,
    },
});

// Calcula nível baseado nos pontos (ex: a cada 100 pontos sobe 1 nível)
const currentLevel = computed(() => {
    return Math.floor(props.stats.points / 100) + 1;
});

// Calcula progresso para o próximo nível (XP restante)
const levelProgress = computed(() => {
    return props.stats.points % 100;
});
</script>

<template>
    <Head title="Painel do Aluno" />

    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h2 class="text-xl font-bold leading-tight text-zinc-100">
                        Olá, {{ $page.props.auth.user.name }}!
                    </h2>
                    <p class="text-xs text-zinc-400">
                        {{
                            $page.props.auth.user.institution
                                ? $page.props.auth.user.institution.name
                                : 'Nenhuma instituição associada'
                        }}
                    </p>
                </div>
                <!-- Badge de Pontos Animado -->
                <PointsBadge :points="stats.points" size="lg" />
            </div>
        </template>

        <div class="bg-zinc-955 min-h-[calc(100vh-64px)] py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Informações e Flash Messages -->
                <div
                    v-if="$page.props.flash?.success"
                    class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-400"
                >
                    {{ $page.props.flash.success }}
                </div>
                <div
                    v-if="$page.props.flash?.info"
                    class="rounded-xl border border-indigo-500/30 bg-indigo-500/10 p-4 text-sm text-indigo-400"
                >
                    {{ $page.props.flash.info }}
                </div>

                <!-- Painel de Nível/XP do Aluno (Premium Glassmorphism) -->
                <div
                    class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-gradient-to-r from-zinc-900 via-zinc-900/80 to-zinc-950 p-6 shadow-xl"
                >
                    <div
                        class="absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l from-indigo-500/5 to-transparent"
                    ></div>
                    <div
                        class="relative z-10 flex flex-col items-center justify-between gap-6 md:flex-row"
                    >
                        <div class="flex items-center gap-4">
                            <!-- Círculo do Nível -->
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-2xl font-black text-white shadow-[0_0_20px_rgba(99,102,241,0.4)]"
                            >
                                {{ currentLevel }}
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">
                                    Nível {{ currentLevel }}
                                </h3>
                                <p class="text-xs text-zinc-400">
                                    Acumule mais
                                    {{ 100 - levelProgress }} pontos (XP) para o
                                    Nível {{ currentLevel + 1 }}
                                </p>
                            </div>
                        </div>

                        <!-- Barra de XP -->
                        <div class="w-full md:w-1/2">
                            <div
                                class="mb-1.5 flex justify-between text-xs font-bold"
                            >
                                <span class="text-zinc-500"
                                    >Progresso do Nível</span
                                >
                                <span class="text-indigo-400"
                                    >{{ levelProgress }} / 100 XP</span
                                >
                            </div>
                            <div
                                class="h-3 w-full overflow-hidden rounded-full border border-zinc-800 bg-zinc-800"
                            >
                                <div
                                    class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 shadow-[0_0_10px_rgba(99,102,241,0.2)] transition-all duration-500"
                                    :style="{ width: `${levelProgress}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid Principal: Matérias e Barra Lateral -->
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <!-- Esquerda: Matérias/Trilhas (2 Colunas) -->
                    <div class="space-y-6 lg:col-span-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white">
                                Minhas Trilhas de Estudo
                            </h3>
                            <span class="text-xs font-semibold text-zinc-500"
                                >{{ subjects.length }} matérias
                                disponíveis</span
                            >
                        </div>

                        <div
                            v-if="subjects.length === 0"
                            class="border-zinc-850 rounded-2xl border border-dashed p-12 text-center text-sm text-zinc-500"
                        >
                            Nenhuma matéria disponível para a sua instituição
                            neste momento.
                        </div>

                        <div
                            v-else
                            class="grid grid-cols-1 gap-6 md:grid-cols-2"
                        >
                            <SubjectCard
                                v-for="subj in subjects"
                                :key="subj.id"
                                :subject="subj"
                            />
                        </div>
                    </div>

                    <!-- Direita: Leaderboard Lateral & Histórico (1 Coluna) -->
                    <div class="space-y-8">
                        <!-- Widget de Ranking -->
                        <LeaderboardWidget :users="leaderboard" />

                        <!-- Histórico Recente de Pontos (Extrato) -->
                        <div
                            class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6"
                        >
                            <h3
                                class="mb-4 flex items-center gap-2 text-sm font-bold text-white"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    class="h-4 w-4 text-zinc-400"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                    />
                                </svg>
                                Histórico de Pontos
                            </h3>

                            <div
                                v-if="scoreHistory.length === 0"
                                class="py-6 text-center text-xs text-zinc-500"
                            >
                                Nenhuma pontuação registrada. Comece a ler
                                materiais ou fazer testes!
                            </div>

                            <ul v-else class="space-y-3">
                                <li
                                    v-for="log in scoreHistory"
                                    :key="log.id"
                                    class="flex items-start justify-between border-b border-zinc-900 pb-2.5 text-xs last:border-0 last:pb-0"
                                >
                                    <div class="pr-2">
                                        <p class="font-medium text-zinc-200">
                                            {{ log.description }}
                                        </p>
                                        <span
                                            class="mt-0.5 block text-[9px] text-zinc-500"
                                            >{{
                                                new Date(
                                                    log.created_at,
                                                ).toLocaleDateString('pt-BR')
                                            }}</span
                                        >
                                    </div>
                                    <span
                                        class="shrink-0 font-bold"
                                        :class="
                                            log.points >= 0
                                                ? 'text-emerald-400'
                                                : 'text-red-400'
                                        "
                                    >
                                        {{
                                            log.points >= 0
                                                ? `+${log.points}`
                                                : log.points
                                        }}
                                        XP
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
