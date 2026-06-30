<script setup>
import MetricCard from '@/Components/MetricCard.vue';
import PageHeader from '@/Components/PageHeader.vue';
import WelcomeWidget from '@/Components/WelcomeWidget.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { BarChart3, BookOpen, GraduationCap, Trophy, Users } from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    metrics: {
        type: Object,
        default: () => ({
            classrooms_count: 0,
            students_count: 0,
            subjects_count: 0,
        }),
    },
    classroomPerformance: {
        type: Array,
        default: () => [],
    },
    studentPerformance: {
        type: Array,
        default: () => [],
    },
});

const maxClassroomAvg = computed(() =>
    Math.max(1, ...props.classroomPerformance.map((c) => c.average_points)),
);
const maxStudentPoints = computed(() =>
    Math.max(1, ...props.studentPerformance.map((s) => s.points)),
);

const barWidth = (value, max) =>
    `${Math.max(2, Math.round((value / max) * 100))}%`;

// Dados reativos: recarrega métricas e desempenho periodicamente.
const refreshInterval = ref(null);

onMounted(() => {
    refreshInterval.value = setInterval(() => {
        router.reload({
            only: ['metrics', 'classroomPerformance', 'studentPerformance'],
            preserveScroll: true,
            preserveState: true,
        });
    }, 15000);
});

onUnmounted(() => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
    }
});
</script>

<template>
    <Head :title="__('teacher.dashboard.title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="__('teacher.dashboard.header')" />
        </template>

        <div class="min-h-[calc(100vh-80px)] bg-zinc-950 py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <WelcomeWidget />

                <div
                    v-if="$page.props.flash?.success"
                    class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-400"
                >
                    {{ $page.props.flash.success }}
                </div>

                <!-- Métricas do professor -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <MetricCard
                        :title="__('classrooms.title')"
                        :value="metrics.classrooms_count"
                        color="text-white"
                    >
                        <template #icon
                            ><GraduationCap class="h-12 w-12"
                        /></template>
                        <template #icon-header
                            ><GraduationCap
                                class="mb-2 h-8 w-8 text-indigo-500"
                        /></template>
                    </MetricCard>
                    <MetricCard
                        :title="__('nav.sidebar.students')"
                        :value="metrics.students_count"
                        color="text-white"
                    >
                        <template #icon><Users class="h-12 w-12" /></template>
                        <template #icon-header
                            ><Users class="mb-2 h-8 w-8 text-indigo-500"
                        /></template>
                    </MetricCard>
                    <MetricCard
                        :title="__('nav.sidebar.subjects')"
                        :value="metrics.subjects_count"
                        color="text-white"
                    >
                        <template #icon
                            ><BookOpen class="h-12 w-12"
                        /></template>
                        <template #icon-header
                            ><BookOpen class="mb-2 h-8 w-8 text-indigo-500"
                        /></template>
                    </MetricCard>
                </div>

                <!-- Gráficos de desempenho -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Desempenho por turma -->
                    <div
                        class="rounded-2xl border border-zinc-800 bg-zinc-900/40 p-6"
                    >
                        <div class="mb-5 flex items-center gap-2">
                            <BarChart3 class="h-5 w-5 text-indigo-400" />
                            <h3 class="text-base font-bold text-white">
                                {{
                                    __(
                                        'teacher.dashboard.classroom_performance',
                                    )
                                }}
                            </h3>
                        </div>

                        <div
                            v-if="classroomPerformance.length"
                            class="space-y-4"
                        >
                            <div
                                v-for="c in classroomPerformance"
                                :key="c.name"
                            >
                                <div
                                    class="mb-1 flex items-center justify-between text-xs"
                                >
                                    <span class="font-semibold text-zinc-200">{{
                                        c.name
                                    }}</span>
                                    <span class="text-zinc-400">
                                        {{ c.average_points }} XP ·
                                        {{ c.students_count }}
                                        {{ __('nav.sidebar.students') }}
                                    </span>
                                </div>
                                <div
                                    class="h-2.5 w-full overflow-hidden rounded-full bg-zinc-950"
                                >
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500"
                                        :style="{
                                            width: barWidth(
                                                c.average_points,
                                                maxClassroomAvg,
                                            ),
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <p
                            v-else
                            class="py-8 text-center text-sm text-zinc-500"
                        >
                            {{ __('teacher.dashboard.no_performance') }}
                        </p>
                    </div>

                    <!-- Desempenho dos alunos -->
                    <div
                        class="rounded-2xl border border-zinc-800 bg-zinc-900/40 p-6"
                    >
                        <div class="mb-5 flex items-center gap-2">
                            <Trophy class="h-5 w-5 text-amber-400" />
                            <h3 class="text-base font-bold text-white">
                                {{
                                    __('teacher.dashboard.student_performance')
                                }}
                            </h3>
                        </div>

                        <div v-if="studentPerformance.length" class="space-y-4">
                            <div
                                v-for="(s, i) in studentPerformance"
                                :key="s.name + i"
                            >
                                <div
                                    class="mb-1 flex items-center justify-between text-xs"
                                >
                                    <span
                                        class="truncate font-semibold text-zinc-200"
                                    >
                                        {{ i + 1 }}. {{ s.name }}
                                    </span>
                                    <span
                                        class="shrink-0 font-bold text-indigo-400"
                                        >{{ s.points }} XP</span
                                    >
                                </div>
                                <div
                                    class="h-2.5 w-full overflow-hidden rounded-full bg-zinc-950"
                                >
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500"
                                        :style="{
                                            width: barWidth(
                                                s.points,
                                                maxStudentPoints,
                                            ),
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <p
                            v-else
                            class="py-8 text-center text-sm text-zinc-500"
                        >
                            {{ __('teacher.dashboard.no_performance') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
