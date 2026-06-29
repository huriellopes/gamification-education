<script setup>
import MetricCard from '@/Components/MetricCard.vue';
import WelcomeWidget from '@/Components/WelcomeWidget.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, School, TrendingUp, Users } from '@lucide/vue';
import { computed, ref } from 'vue';

const props = defineProps({
    metrics: {
        type: Object,
        default: () => ({
            total_institutions: 0,
            active_students: 0,
            total_xp: 0,
            active_subjects: 0,
        }),
    },
    performanceChart: {
        type: Array,
        default: () => [],
    },
    siteVisitsChart: {
        type: Array,
        default: () => [],
    },
});

// Chart 1: Performance XP
const maxPoints = computed(() => {
    const vals = props.performanceChart.map((d) => d.points);
    return Math.max(...vals, 100);
});

const perfCoords = computed(() => {
    return props.performanceChart.map((d, idx) => {
        const x = 50 + idx * (520 / 6);
        const y = 210 - (d.points / maxPoints.value) * 180;
        return { x, y, points: d.points };
    });
});

const perfLinePath = computed(() => {
    const coords = perfCoords.value;
    if (coords.length === 0) return '';
    return coords.reduce((acc, curr, idx) => {
        return idx === 0
            ? `M ${curr.x} ${curr.y}`
            : `${acc} L ${curr.x} ${curr.y}`;
    }, '');
});

const perfAreaPath = computed(() => {
    const coords = perfCoords.value;
    if (coords.length === 0) return '';
    const firstX = coords[0].x;
    const lastX = coords[coords.length - 1].x;
    return `${perfLinePath.value} L ${lastX} 210 L ${firstX} 210 Z`;
});

// Chart 2: Site Visits
const maxVisits = computed(() => {
    const vals = props.siteVisitsChart.map((d) => d.visits);
    return Math.max(...vals, 10);
});

const visitsCoords = computed(() => {
    return props.siteVisitsChart.map((d, idx) => {
        const x = 50 + idx * (520 / 6);
        const y = 210 - (d.visits / maxVisits.value) * 180;
        return { x, y, visits: d.visits };
    });
});

const visitsLinePath = computed(() => {
    const coords = visitsCoords.value;
    if (coords.length === 0) return '';
    return coords.reduce((acc, curr, idx) => {
        return idx === 0
            ? `M ${curr.x} ${curr.y}`
            : `${acc} L ${curr.x} ${curr.y}`;
    }, '');
});

const visitsAreaPath = computed(() => {
    const coords = visitsCoords.value;
    if (coords.length === 0) return '';
    const firstX = coords[0].x;
    const lastX = coords[coords.length - 1].x;
    return `${visitsLinePath.value} L ${lastX} 210 L ${firstX} 210 Z`;
});

const activePerfTooltip = ref(null);
const showPerfTooltip = (pt) => {
    activePerfTooltip.value = {
        left: `${(pt.x / 600) * 100}%`,
        top: `${(pt.y / 240) * 100}%`,
        value: `${pt.points} ${__('superadmin.dashboard.xp_suffix')}`,
    };
};
const hidePerfTooltip = () => {
    activePerfTooltip.value = null;
};

const activeVisitsTooltip = ref(null);
const showVisitsTooltip = (pt) => {
    activeVisitsTooltip.value = {
        left: `${(pt.x / 600) * 100}%`,
        top: `${(pt.y / 240) * 100}%`,
        value: `${pt.visits} ${__('superadmin.dashboard.visits_suffix')}`,
    };
};
const hideVisitsTooltip = () => {
    activeVisitsTooltip.value = null;
};
</script>

<template>
    <Head :title="__('superadmin.dashboard.head_title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-zinc-100">
                {{ __('superadmin.dashboard.header') }}
            </h2>
        </template>

        <div class="min-h-[calc(100vh-80px)] bg-zinc-950 py-6 text-zinc-100">
            <div class="space-y-8">
                <WelcomeWidget />

                <!-- Cards de Estatísticas com Estética Premium -->
                <div
                    class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <!-- Instituições -->
                    <MetricCard
                        :title="__('superadmin.dashboard.card_institutions')"
                        :value="metrics.total_institutions"
                        color="text-white"
                    >
                        <template #icon><School class="h-12 w-12" /></template>
                        <template #icon-header>
                            <School class="mb-2 h-8 w-8 text-indigo-500" />
                        </template>
                        <Link
                            :href="route('super-admin.institutions.index')"
                            class="mt-2 inline-block text-xs font-semibold text-indigo-400 transition-colors hover:text-indigo-300"
                        >
                            {{ __('superadmin.dashboard.manage_institutions') }}
                            &rarr;
                        </Link>
                    </MetricCard>

                    <!-- Alunos Ativos -->
                    <MetricCard
                        :title="__('superadmin.dashboard.card_active_students')"
                        :value="metrics.active_students"
                        color="text-white"
                    >
                        <template #icon><Users class="h-12 w-12" /></template>
                        <template #icon-header>
                            <Users class="mb-2 h-8 w-8 text-indigo-500" />
                        </template>
                        <Link
                            :href="route('super-admin.users.index')"
                            class="mt-2 inline-block text-xs font-semibold text-indigo-400 transition-colors hover:text-indigo-300"
                        >
                            {{ __('superadmin.dashboard.view_users') }} &rarr;
                        </Link>
                    </MetricCard>

                    <!-- XP Acumulado -->
                    <MetricCard
                        :title="__('superadmin.dashboard.card_total_xp')"
                        :value="metrics.total_xp"
                        color="text-white"
                    >
                        <template #icon
                            ><TrendingUp class="h-12 w-12"
                        /></template>
                        <template #icon-header>
                            <TrendingUp class="mb-2 h-8 w-8 text-indigo-500" />
                        </template>
                        <span
                            class="mt-2 block text-xs font-semibold text-zinc-400"
                            >{{ __('superadmin.dashboard.global_xp') }}</span
                        >
                    </MetricCard>

                    <!-- Matérias Ativas -->
                    <MetricCard
                        :title="__('superadmin.dashboard.card_active_subjects')"
                        :value="metrics.active_subjects"
                        color="text-white"
                    >
                        <template #icon
                            ><BookOpen class="h-12 w-12"
                        /></template>
                        <template #icon-header>
                            <BookOpen class="mb-2 h-8 w-8 text-indigo-500" />
                        </template>
                        <Link
                            :href="route('super-admin.subjects.index')"
                            class="mt-2 inline-block text-xs font-semibold text-indigo-400 transition-colors hover:text-indigo-300"
                        >
                            {{ __('superadmin.dashboard.manage_subjects') }}
                            &rarr;
                        </Link>
                    </MetricCard>
                </div>

                <!-- Gráficos de Desempenho e Visitas -->
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Gráfico 1: Desempenho (XP Acumulado) -->
                    <div
                        class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                    >
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-white">
                                {{
                                    __('superadmin.dashboard.performance_title')
                                }}
                            </h3>
                            <p class="text-xs text-zinc-400">
                                {{
                                    __(
                                        'superadmin.dashboard.performance_subtitle',
                                    )
                                }}
                            </p>
                        </div>

                        <div class="relative h-64 w-full">
                            <svg
                                class="h-full w-full"
                                viewBox="0 0 600 240"
                                preserveAspectRatio="none"
                            >
                                <defs>
                                    <linearGradient
                                        id="perfGrad"
                                        x1="0"
                                        y1="0"
                                        x2="0"
                                        y2="1"
                                    >
                                        <stop
                                            offset="0%"
                                            stop-color="#4f46e5"
                                            stop-opacity="0.4"
                                        />
                                        <stop
                                            offset="100%"
                                            stop-color="#4f46e5"
                                            stop-opacity="0"
                                        />
                                    </linearGradient>
                                </defs>

                                <line
                                    x1="50"
                                    y1="30"
                                    x2="570"
                                    y2="30"
                                    stroke="#27272a"
                                    stroke-dasharray="3"
                                />
                                <line
                                    x1="50"
                                    y1="90"
                                    x2="570"
                                    y2="90"
                                    stroke="#27272a"
                                    stroke-dasharray="3"
                                />
                                <line
                                    x1="50"
                                    y1="150"
                                    x2="570"
                                    y2="150"
                                    stroke="#27272a"
                                    stroke-dasharray="3"
                                />
                                <line
                                    x1="50"
                                    y1="210"
                                    x2="570"
                                    y2="210"
                                    stroke="#27272a"
                                />

                                <text
                                    x="15"
                                    y="34"
                                    fill="#71717a"
                                    class="font-mono text-[10px] font-bold"
                                >
                                    {{ Math.round(maxPoints) }}
                                </text>
                                <text
                                    x="15"
                                    y="124"
                                    fill="#71717a"
                                    class="font-mono text-[10px] font-bold"
                                >
                                    {{ Math.round(maxPoints / 2) }}
                                </text>
                                <text
                                    x="15"
                                    y="214"
                                    fill="#71717a"
                                    class="font-mono text-[10px] font-bold"
                                >
                                    0
                                </text>

                                <path :d="perfAreaPath" fill="url(#perfGrad)" />
                                <path
                                    :d="perfLinePath"
                                    fill="none"
                                    stroke="#4f46e5"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />

                                <g v-for="(pt, idx) in perfCoords" :key="idx">
                                    <circle
                                        :cx="pt.x"
                                        :cy="pt.y"
                                        r="4.5"
                                        fill="#4f46e5"
                                        stroke="#18181b"
                                        stroke-width="2"
                                        class="hover:r-6 cursor-pointer transition-all duration-150 hover:fill-white"
                                        @mouseenter="showPerfTooltip(pt)"
                                        @mouseleave="hidePerfTooltip"
                                    />
                                </g>

                                <text
                                    v-for="(d, idx) in performanceChart"
                                    :key="idx"
                                    :x="50 + idx * (520 / 6)"
                                    y="235"
                                    text-anchor="middle"
                                    fill="#71717a"
                                    class="font-mono text-[10px] font-bold"
                                >
                                    {{ d.day }}
                                </text>
                            </svg>

                            <!-- Floating HTML Tooltip -->
                            <div
                                v-if="activePerfTooltip"
                                class="pointer-events-none absolute z-30 -translate-x-1/2 -translate-y-[calc(100%+12px)] rounded-xl border border-indigo-500/30 bg-zinc-950/95 px-2.5 py-1.5 text-center shadow-xl backdrop-blur-md transition-all duration-150"
                                :style="{
                                    left: activePerfTooltip.left,
                                    top: activePerfTooltip.top,
                                }"
                            >
                                <div
                                    class="text-zinc-450 text-[9px] font-bold uppercase tracking-wider"
                                >
                                    {{
                                        __(
                                            'superadmin.dashboard.performance_tooltip_label',
                                        )
                                    }}
                                </div>
                                <div
                                    class="mt-0.5 font-mono text-xs font-extrabold text-indigo-300"
                                >
                                    {{ activePerfTooltip.value }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico 2: Visitas ao Site Público -->
                    <div
                        class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                    >
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-white">
                                {{ __('superadmin.dashboard.visits_title') }}
                            </h3>
                            <p class="text-xs text-zinc-400">
                                {{ __('superadmin.dashboard.visits_subtitle') }}
                            </p>
                        </div>

                        <div class="relative h-64 w-full">
                            <svg
                                class="h-full w-full"
                                viewBox="0 0 600 240"
                                preserveAspectRatio="none"
                            >
                                <defs>
                                    <linearGradient
                                        id="visitsGrad"
                                        x1="0"
                                        y1="0"
                                        x2="0"
                                        y2="1"
                                    >
                                        <stop
                                            offset="0%"
                                            stop-color="#10b981"
                                            stop-opacity="0.4"
                                        />
                                        <stop
                                            offset="100%"
                                            stop-color="#10b981"
                                            stop-opacity="0"
                                        />
                                    </linearGradient>
                                </defs>

                                <line
                                    x1="50"
                                    y1="30"
                                    x2="570"
                                    y2="30"
                                    stroke="#27272a"
                                    stroke-dasharray="3"
                                />
                                <line
                                    x1="50"
                                    y1="90"
                                    x2="570"
                                    y2="90"
                                    stroke="#27272a"
                                    stroke-dasharray="3"
                                />
                                <line
                                    x1="50"
                                    y1="150"
                                    x2="570"
                                    y2="150"
                                    stroke="#27272a"
                                    stroke-dasharray="3"
                                />
                                <line
                                    x1="50"
                                    y1="210"
                                    x2="570"
                                    y2="210"
                                    stroke="#27272a"
                                />

                                <text
                                    x="15"
                                    y="34"
                                    fill="#71717a"
                                    class="font-mono text-[10px] font-bold"
                                >
                                    {{ Math.round(maxVisits) }}
                                </text>
                                <text
                                    x="15"
                                    y="124"
                                    fill="#71717a"
                                    class="font-mono text-[10px] font-bold"
                                >
                                    {{ Math.round(maxVisits / 2) }}
                                </text>
                                <text
                                    x="15"
                                    y="214"
                                    fill="#71717a"
                                    class="font-mono text-[10px] font-bold"
                                >
                                    0
                                </text>

                                <path
                                    :d="visitsAreaPath"
                                    fill="url(#visitsGrad)"
                                />
                                <path
                                    :d="visitsLinePath"
                                    fill="none"
                                    stroke="#10b981"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />

                                <g v-for="(pt, idx) in visitsCoords" :key="idx">
                                    <circle
                                        :cx="pt.x"
                                        :cy="pt.y"
                                        r="4.5"
                                        fill="#10b981"
                                        stroke="#18181b"
                                        stroke-width="2"
                                        class="hover:r-6 cursor-pointer transition-all duration-150 hover:fill-white"
                                        @mouseenter="showVisitsTooltip(pt)"
                                        @mouseleave="hideVisitsTooltip"
                                    />
                                </g>

                                <text
                                    v-for="(d, idx) in siteVisitsChart"
                                    :key="idx"
                                    :x="50 + idx * (520 / 6)"
                                    y="235"
                                    text-anchor="middle"
                                    fill="#71717a"
                                    class="font-mono text-[10px] font-bold"
                                >
                                    {{ d.day }}
                                </text>
                            </svg>

                            <!-- Floating HTML Tooltip -->
                            <div
                                v-if="activeVisitsTooltip"
                                class="pointer-events-none absolute z-30 -translate-x-1/2 -translate-y-[calc(100%+12px)] rounded-xl border border-emerald-500/30 bg-zinc-950/95 px-2.5 py-1.5 text-center shadow-xl backdrop-blur-md transition-all duration-150"
                                :style="{
                                    left: activeVisitsTooltip.left,
                                    top: activeVisitsTooltip.top,
                                }"
                            >
                                <div
                                    class="text-zinc-450 text-[9px] font-bold uppercase tracking-wider"
                                >
                                    {{
                                        __(
                                            'superadmin.dashboard.visits_tooltip_label',
                                        )
                                    }}
                                </div>
                                <div
                                    class="mt-0.5 font-mono text-xs font-extrabold text-emerald-400"
                                >
                                    {{ activeVisitsTooltip.value }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
