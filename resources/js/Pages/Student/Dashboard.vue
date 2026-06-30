<script setup>
import LeaderboardWidget from '@/Components/LeaderboardWidget.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PointsBadge from '@/Components/PointsBadge.vue';
import SubjectCard from '@/Components/SubjectCard.vue';
import WelcomeWidget from '@/Components/WelcomeWidget.vue';
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
    <Head :title="__('student.dashboard.title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                :title="
                    __('student.dashboard.greeting', {
                        name: $page.props.auth.user.name,
                    })
                "
                :subtitle="
                    $page.props.auth.user.institution
                        ? $page.props.auth.user.institution.name
                        : __('student.dashboard.no_institution')
                "
            >
                <template #actions>
                    <!-- Badge de Pontos Animado -->
                    <PointsBadge :points="stats.points" size="lg" />
                </template>
            </PageHeader>
        </template>

        <div class="bg-zinc-955 min-h-[calc(100vh-80px)] py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <WelcomeWidget />

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
                                    {{
                                        __('student.dashboard.level', {
                                            level: currentLevel,
                                        })
                                    }}
                                </h3>
                                <p class="text-xs text-zinc-400">
                                    {{
                                        __(
                                            'student.dashboard.accumulate_hint',
                                            {
                                                points: 100 - levelProgress,
                                                next: currentLevel + 1,
                                            },
                                        )
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Barra de XP -->
                        <div class="w-full md:w-1/2">
                            <div
                                class="mb-1.5 flex justify-between text-xs font-bold"
                            >
                                <span class="text-zinc-500">{{
                                    __('student.dashboard.level_progress')
                                }}</span>
                                <span class="text-indigo-400">{{
                                    __('student.dashboard.xp_count', {
                                        current: levelProgress,
                                    })
                                }}</span>
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
                                {{ __('student.dashboard.my_tracks') }}
                            </h3>
                            <span class="text-xs font-semibold text-zinc-500">{{
                                __('student.dashboard.subjects_available', {
                                    count: subjects.length,
                                })
                            }}</span>
                        </div>

                        <div
                            v-if="subjects.length === 0"
                            class="border-zinc-850 rounded-2xl border border-dashed p-12 text-center text-sm text-zinc-500"
                        >
                            {{ __('student.dashboard.no_subjects') }}
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
                                {{ __('student.dashboard.points_history') }}
                            </h3>

                            <div
                                v-if="scoreHistory.length === 0"
                                class="py-6 text-center text-xs text-zinc-500"
                            >
                                {{ __('student.dashboard.no_history') }}
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
