<script setup>
import Button from '@/Components/Button.vue';
import DataTable from '@/Components/DataTable.vue';
import LineChart from '@/Components/LineChart.vue';
import MetricCard from '@/Components/MetricCard.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SystemHealthPanel from '@/Components/SystemHealthPanel.vue';
import WelcomeWidget from '@/Components/WelcomeWidget.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { BookOpen, GraduationCap, Users } from '@lucide/vue';
import { onMounted, onUnmounted, ref, watch } from 'vue';

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
    reports: {
        type: Array,
        default: () => [],
    },
    health: {
        type: Object,
        default: () => ({ checks: [], summary: {} }),
    },
    performanceChart: {
        type: Array,
        default: () => [],
    },
});

const reportColumns = [
    { key: 'name', label: __('common.name'), sortable: true },
    { key: 'status', label: __('common.status'), sortable: true },
    {
        key: 'created_at',
        label: __('admin.dashboard.requested_at'),
        sortable: true,
    },
    {
        key: 'actions',
        label: __('common.actions'),
        sortable: false,
        align: 'right',
    },
];

const studentColumns = [
    {
        key: 'rank',
        label: __('admin.dashboard.col_rank'),
        sortable: false,
        align: 'center',
    },
    { key: 'name', label: __('common.name'), sortable: true },
    { key: 'email', label: __('common.email'), sortable: true },
    {
        key: 'points',
        label: __('admin.dashboard.col_xp_total'),
        sortable: true,
        align: 'right',
    },
];

// Polling Relativos a Relatórios
const pollInterval = ref(null);

const checkAndStartPolling = () => {
    const hasPending = props.reports.some((r) => r.status === 'pending');
    if (hasPending) {
        if (!pollInterval.value) {
            pollInterval.value = setInterval(() => {
                router.reload({
                    only: ['reports', 'stats'],
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: () => {
                        const stillPending = props.reports.some(
                            (r) => r.status === 'pending',
                        );
                        if (!stillPending) {
                            stopPolling();
                        }
                    },
                });
            }, 4000);
        }
    } else {
        stopPolling();
    }
};

const stopPolling = () => {
    if (pollInterval.value) {
        clearInterval(pollInterval.value);
        pollInterval.value = null;
    }
};

watch(
    () => props.reports,
    () => {
        checkAndStartPolling();
    },
    { deep: true },
);

const generalSyncInterval = ref(null);

onMounted(() => {
    checkAndStartPolling();
    generalSyncInterval.value = setInterval(() => {
        router.reload({
            only: ['stats'],
            preserveScroll: true,
            preserveState: true,
        });
    }, 15000);
});

onUnmounted(() => {
    stopPolling();
    if (generalSyncInterval.value) {
        clearInterval(generalSyncInterval.value);
    }
});
</script>

<template>
    <Head :title="__('admin.dashboard.title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader>
                {{ __('admin.dashboard.header') }}
                {{
                    $page.props.auth.user.institution?.name ||
                    __('admin.dashboard.default_institution')
                }}
            </PageHeader>
        </template>

        <div class="min-h-[calc(100vh-80px)] bg-zinc-950 py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <WelcomeWidget />

                <!-- Cards de Estatísticas com Estética Premium -->
                <div
                    class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <!-- Card Alunos -->
                    <MetricCard
                        :title="__('admin.dashboard.students_enrolled')"
                        :value="stats.total_students"
                        color="text-white"
                    >
                        <template #icon><Users class="h-12 w-12" /></template>
                        <template #icon-header>
                            <Users class="mb-2 h-8 w-8 text-indigo-500" />
                        </template>
                        <Link
                            :href="route('admin.users.index')"
                            class="mt-2 inline-block text-xs font-semibold text-indigo-400 transition-colors hover:text-indigo-300"
                        >
                            {{ __('admin.dashboard.manage_students') }} &rarr;
                        </Link>
                    </MetricCard>

                    <!-- Card Professores -->
                    <MetricCard
                        :title="__('admin.dashboard.teaching_staff')"
                        :value="stats.total_teachers"
                        color="text-white"
                    >
                        <template #icon
                            ><GraduationCap class="h-12 w-12"
                        /></template>
                        <template #icon-header>
                            <GraduationCap
                                class="mb-2 h-8 w-8 text-indigo-500"
                            />
                        </template>
                        <Link
                            :href="route('admin.users.index')"
                            class="mt-2 inline-block text-xs font-semibold text-indigo-400 transition-colors hover:text-indigo-300"
                        >
                            {{ __('admin.dashboard.manage_teachers') }} &rarr;
                        </Link>
                    </MetricCard>

                    <!-- Card Matérias -->
                    <MetricCard
                        :title="__('admin.dashboard.active_subjects')"
                        :value="stats.total_subjects"
                        color="text-white"
                    >
                        <template #icon
                            ><BookOpen class="h-12 w-12"
                        /></template>
                        <template #icon-header>
                            <BookOpen class="mb-2 h-8 w-8 text-indigo-500" />
                        </template>
                        <Link
                            :href="route('admin.subjects.index')"
                            class="mt-2 inline-block text-xs font-semibold text-indigo-400 transition-colors hover:text-indigo-300"
                        >
                            {{ __('admin.dashboard.manage_subjects') }} &rarr;
                        </Link>
                    </MetricCard>
                </div>

                <!-- Saúde (integridade da instituição) -->
                <SystemHealthPanel :report="health" i18n-base="admin.health" />

                <!-- Gráfico de Desempenho dos Alunos -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-white">
                                {{
                                    __(
                                        'admin.dashboard.institution_performance',
                                    )
                                }}
                            </h3>
                            <p class="text-xs text-zinc-400">
                                {{
                                    __(
                                        'admin.dashboard.institution_performance_desc',
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <LineChart
                        :data="performanceChart"
                        value-key="points"
                        color="#4f46e5"
                        :floor="100"
                        gradient-id="chartGrad"
                        variant="indigo"
                        :tooltip-label="__('admin.dashboard.performance')"
                        value-suffix="XP"
                    />
                </div>

                <!-- Fila de Relatórios Solicitados -->
                <div
                    v-if="reports.length > 0"
                    class="mb-8 rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <h3 class="mb-2 text-lg font-bold text-white">
                        {{ __('admin.dashboard.reports_requested') }}
                    </h3>
                    <p class="mb-4 text-xs text-zinc-400">
                        {{ __('admin.dashboard.reports_requested_desc') }}
                    </p>

                    <DataTable
                        :items="reports"
                        :columns="reportColumns"
                        :searchPlaceholder="__('admin.dashboard.search_report')"
                    >
                        <template #name="{ item }">
                            <span class="font-semibold text-zinc-200">{{
                                item.name
                            }}</span>
                        </template>
                        <template #status="{ item }">
                            <span
                                v-if="item.status === 'pending'"
                                class="inline-flex items-center gap-1.5 rounded-full bg-amber-400/10 px-2.5 py-1 text-xs font-semibold text-amber-400"
                            >
                                <span
                                    class="h-1.5 w-1.5 animate-pulse rounded-full bg-amber-400"
                                ></span>
                                {{ __('admin.dashboard.processing') }}
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/10 px-2.5 py-1 text-xs font-semibold text-emerald-400"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-emerald-400"
                                ></span>
                                {{ __('admin.dashboard.completed') }}
                            </span>
                        </template>
                        <template #created_at="{ item }">
                            <span class="font-mono text-xs text-zinc-400">
                                {{
                                    new Date(item.created_at).toLocaleString(
                                        'pt-BR',
                                    )
                                }}
                            </span>
                        </template>
                        <template #actions="{ item }">
                            <a
                                v-if="item.status === 'completed'"
                                :href="route('reports.download', item.id)"
                                class="active:scale-98 inline-flex items-center gap-1.5 rounded-xl border border-zinc-800 bg-zinc-950/40 px-4 py-2 text-sm font-semibold text-indigo-400 transition-all hover:bg-zinc-800 hover:text-zinc-200"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                    ></path>
                                </svg>
                                <span class="hidden md:inline">{{
                                    __('admin.dashboard.download_report')
                                }}</span>
                            </a>
                            <span
                                v-else
                                class="animate-pulse text-xs font-bold text-zinc-400"
                                >{{ __('admin.dashboard.waiting') }}</span
                            >
                        </template>
                    </DataTable>
                </div>

                <!-- Painel de Relatório e Desempenho (Tabela de Alunos) -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <div
                        class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center"
                    >
                        <div>
                            <h3 class="text-lg font-bold text-white">
                                {{ __('admin.dashboard.ranking_title') }}
                            </h3>
                            <div class="mt-1 flex items-center gap-3">
                                <p class="text-xs text-zinc-400">
                                    {{ __('admin.dashboard.ranking_desc') }}
                                </p>
                                <Button
                                    variant="secondary"
                                    @click="
                                        router.post(
                                            route('admin.reports.performance'),
                                        )
                                    "
                                    class="border-emerald-500/20 !px-3 !py-1.5 text-xs text-emerald-400 hover:bg-emerald-500/10"
                                >
                                    <template #icon>
                                        <svg
                                            class="h-3.5 w-3.5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            ></path>
                                        </svg>
                                    </template>
                                    <span class="hidden md:inline">{{
                                        __('admin.dashboard.export_report')
                                    }}</span>
                                </Button>
                            </div>
                        </div>
                    </div>

                    <DataTable
                        :items="students"
                        :columns="studentColumns"
                        :searchPlaceholder="
                            __('admin.dashboard.search_student')
                        "
                    >
                        <template #rank="{ item }">
                            <span
                                v-if="
                                    students.findIndex(
                                        (s) => s.id === item.id,
                                    ) === 0
                                "
                                class="inline-block rounded bg-amber-500/10 px-2 py-0.5 text-xs font-bold text-amber-500"
                                >🥇 1º</span
                            >
                            <span
                                v-else-if="
                                    students.findIndex(
                                        (s) => s.id === item.id,
                                    ) === 1
                                "
                                class="inline-block rounded bg-zinc-300/10 px-2 py-0.5 text-xs font-bold text-zinc-300"
                                >🥈 2º</span
                            >
                            <span
                                v-else-if="
                                    students.findIndex(
                                        (s) => s.id === item.id,
                                    ) === 2
                                "
                                class="inline-block rounded bg-amber-700/10 px-2 py-0.5 text-xs font-bold text-amber-700"
                                >🥉 3º</span
                            >
                            <span
                                v-else
                                class="text-xs font-semibold text-zinc-400"
                                >{{
                                    students.findIndex(
                                        (s) => s.id === item.id,
                                    ) + 1
                                }}º</span
                            >
                        </template>
                        <template #name="{ item }">
                            <span class="font-semibold text-white">{{
                                item.name
                            }}</span>
                        </template>
                        <template #email="{ item }">
                            <span class="text-zinc-400">{{ item.email }}</span>
                        </template>
                        <template #points="{ item }">
                            <span class="text-emerald-450 font-bold"
                                >{{ item.points }} XP</span
                            >
                        </template>
                    </DataTable>
                </div>

                <!-- Painel de Corpo Docente -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <h3 class="mb-2 text-lg font-bold text-white">
                        {{ __('admin.dashboard.registered_teachers') }}
                    </h3>
                    <p class="mb-6 text-xs text-zinc-400">
                        {{ __('admin.dashboard.registered_teachers_desc') }}
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
                                <div class="text-xs text-zinc-400">
                                    {{ tchr.email }}
                                </div>
                            </div>
                        </div>
                        <div
                            v-if="teachers.length === 0"
                            class="col-span-full py-8 text-center text-sm text-zinc-400"
                        >
                            {{ __('admin.dashboard.no_teachers') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
