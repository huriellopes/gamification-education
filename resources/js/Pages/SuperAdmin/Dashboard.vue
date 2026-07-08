<script setup>
import LineChart from '@/Components/LineChart.vue';
import MetricCard from '@/Components/MetricCard.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SystemHealthPanel from '@/Components/SystemHealthPanel.vue';
import WelcomeWidget from '@/Components/WelcomeWidget.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, School, TrendingUp, Users } from '@lucide/vue';

defineProps({
    metrics: {
        type: Object,
        default: () => ({
            total_institutions: 0,
            active_students: 0,
            total_xp: 0,
            active_subjects: 0,
        }),
    },
    health: {
        type: Object,
        default: () => ({ checks: [], summary: {} }),
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
</script>

<template>
    <Head :title="__('superadmin.dashboard.head_title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="__('superadmin.dashboard.header')" />
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

                <!-- Saúde do Sistema (integridade + operacional) -->
                <SystemHealthPanel :report="health" />

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

                        <LineChart
                            :data="performanceChart"
                            value-key="points"
                            color="#4f46e5"
                            :floor="100"
                            gradient-id="perfGrad"
                            variant="indigo"
                            :tooltip-label="
                                __(
                                    'superadmin.dashboard.performance_tooltip_label',
                                )
                            "
                            :value-suffix="__('superadmin.dashboard.xp_suffix')"
                        />
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

                        <LineChart
                            :data="siteVisitsChart"
                            value-key="visits"
                            color="#10b981"
                            :floor="10"
                            gradient-id="visitsGrad"
                            variant="emerald"
                            :tooltip-label="
                                __('superadmin.dashboard.visits_tooltip_label')
                            "
                            :value-suffix="
                                __('superadmin.dashboard.visits_suffix')
                            "
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
