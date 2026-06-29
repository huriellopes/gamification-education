<script setup>
import MetricCard from '@/Components/MetricCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Button from '@/Components/Button.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { BookOpen, GraduationCap, Users } from '@lucide/vue';
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';

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
    performanceChart: {
        type: Array,
        default: () => [],
    },
});

const maxPoints = computed(() => {
    const vals = props.performanceChart.map(d => d.points);
    return Math.max(...vals, 100);
});

const chartCoords = computed(() => {
    return props.performanceChart.map((d, idx) => {
        const x = 50 + idx * (520 / 6);
        const y = 210 - ((d.points / maxPoints.value) * 180);
        return { x, y, points: d.points };
    });
});

const linePath = computed(() => {
    const coords = chartCoords.value;
    if (coords.length === 0) return '';
    return coords.reduce((acc, curr, idx) => {
        return idx === 0 ? `M ${curr.x} ${curr.y}` : `${acc} L ${curr.x} ${curr.y}`;
    }, '');
});

const areaPath = computed(() => {
    const coords = chartCoords.value;
    if (coords.length === 0) return '';
    const firstX = coords[0].x;
    const lastX = coords[coords.length - 1].x;
    return `${linePath.value} L ${lastX} 210 L ${firstX} 210 Z`;
});

const reportColumns = [
    { key: 'name', label: 'Nome', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Solicitado em', sortable: true },
    { key: 'actions', label: 'Ações', sortable: false, align: 'right' },
];

const studentColumns = [
    { key: 'rank', label: '#', sortable: false, align: 'center' },
    { key: 'name', label: 'Nome', sortable: true },
    { key: 'email', label: 'E-mail', sortable: true },
    { key: 'points', label: 'XP Total', sortable: true, align: 'right' },
];

// Polling Relativos a Relatórios
const pollInterval = ref(null);

const checkAndStartPolling = () => {
    const hasPending = props.reports.some(r => r.status === 'pending');
    if (hasPending) {
        if (!pollInterval.value) {
            pollInterval.value = setInterval(() => {
                router.reload({
                    only: ['reports', 'stats'],
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: () => {
                        const stillPending = props.reports.some(r => r.status === 'pending');
                        if (!stillPending) {
                            stopPolling();
                        }
                    }
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

watch(() => props.reports, () => {
    checkAndStartPolling();
}, { deep: true });

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

const activeTooltip = ref(null);
const showTooltip = (pt, label) => {
    activeTooltip.value = {
        left: `${(pt.x / 600) * 100}%`,
        top: `${(pt.y / 240) * 100}%`,
        label,
        value: `${pt.points} XP`
    };
};
const hideTooltip = () => {
    activeTooltip.value = null;
};
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
                <!-- Cards de Estatísticas com Estética Premium -->
                <div
                    class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <!-- Card Alunos -->
                    <MetricCard
                        title="Alunos Matriculados"
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
                            Gerenciar Alunos &rarr;
                        </Link>
                    </MetricCard>

                    <!-- Card Professores -->
                    <MetricCard
                        title="Corpo Docente (Professores)"
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
                            Gerenciar Professores &rarr;
                        </Link>
                    </MetricCard>

                    <!-- Card Matérias -->
                    <MetricCard
                        title="Matérias Ativas"
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
                            Gerenciar Matérias &rarr;
                        </Link>
                    </MetricCard>
                </div>

                <!-- Gráfico de Desempenho dos Alunos -->
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-white">Desempenho da Instituição</h3>
                            <p class="text-xs text-zinc-400">Total de XP acumulado pelos alunos nos últimos 7 dias</p>
                        </div>
                    </div>

                    <div class="relative h-64 w-full">
                        <svg class="h-full w-full" viewBox="0 0 600 240" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="chartGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#4f46e5" stop-opacity="0.4" />
                                    <stop offset="100%" stop-color="#4f46e5" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            
                            <!-- Grid lines -->
                            <line x1="50" y1="30" x2="570" y2="30" stroke="#27272a" stroke-dasharray="3" />
                            <line x1="50" y1="90" x2="570" y2="90" stroke="#27272a" stroke-dasharray="3" />
                            <line x1="50" y1="150" x2="570" y2="150" stroke="#27272a" stroke-dasharray="3" />
                            <line x1="50" y1="210" x2="570" y2="210" stroke="#27272a" />

                            <!-- Y-Axis Labels -->
                            <text x="15" y="34" fill="#71717a" class="text-[10px] font-bold font-mono">{{ Math.round(maxPoints) }}</text>
                            <text x="15" y="124" fill="#71717a" class="text-[10px] font-bold font-mono">{{ Math.round(maxPoints / 2) }}</text>
                            <text x="15" y="214" fill="#71717a" class="text-[10px] font-bold font-mono">0</text>

                            <!-- Area Path -->
                            <path :d="areaPath" fill="url(#chartGrad)" />

                            <!-- Line Path -->
                            <path :d="linePath" fill="none" stroke="#4f46e5" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

                            <!-- Dots with hover tooltip handlers -->
                            <g v-for="(pt, idx) in chartCoords" :key="idx">
                                <circle 
                                    :cx="pt.x" 
                                    :cy="pt.y" 
                                    r="4.5" 
                                    fill="#4f46e5" 
                                    stroke="#18181b" 
                                    stroke-width="2" 
                                    class="cursor-pointer transition-all duration-150 hover:r-6 hover:fill-white"
                                    @mouseenter="showTooltip(pt, 'Desempenho')"
                                    @mouseleave="hideTooltip"
                                />
                            </g>

                            <!-- X-Axis Labels -->
                            <text v-for="(d, idx) in performanceChart" :key="idx" :x="50 + idx * (520 / 6)" y="235" text-anchor="middle" fill="#71717a" class="text-[10px] font-bold font-mono">{{ d.day }}</text>
                        </svg>

                        <!-- Floating HTML Tooltip (No distortion) -->
                        <div 
                            v-if="activeTooltip" 
                            class="absolute z-30 pointer-events-none -translate-x-1/2 -translate-y-[calc(100%+12px)] bg-zinc-950/95 border border-indigo-500/30 px-2.5 py-1.5 rounded-xl shadow-xl text-center backdrop-blur-md transition-all duration-150"
                            :style="{ left: activeTooltip.left, top: activeTooltip.top }"
                        >
                            <div class="text-[9px] text-zinc-450 font-bold uppercase tracking-wider">{{ activeTooltip.label }}</div>
                            <div class="text-xs text-indigo-300 font-extrabold font-mono mt-0.5">{{ activeTooltip.value }}</div>
                        </div>
                    </div>
                </div>

                <!-- Fila de Relatórios Solicitados -->
                <div v-if="reports.length > 0" class="mb-8 rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md">
                    <h3 class="text-lg font-bold text-white mb-2">Relatórios Solicitados</h3>
                    <p class="text-xs text-zinc-400 mb-4">
                        Os relatórios são processados em segundo plano. Os arquivos baixados são removidos automaticamente do servidor após o download.
                    </p>
                    
                    <DataTable
                        :items="reports"
                        :columns="reportColumns"
                        searchPlaceholder="Buscar relatório..."
                    >
                        <template #name="{ item }">
                            <span class="font-semibold text-zinc-200">{{ item.name }}</span>
                        </template>
                        <template #status="{ item }">
                            <span v-if="item.status === 'pending'" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-amber-400 bg-amber-400/10 rounded-full">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                Processando
                            </span>
                            <span v-else class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-emerald-400 bg-emerald-400/10 rounded-full">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                Concluído
                            </span>
                        </template>
                        <template #created_at="{ item }">
                            <span class="text-zinc-400 font-mono text-xs">
                                {{ new Date(item.created_at).toLocaleString('pt-BR') }}
                            </span>
                        </template>
                        <template #actions="{ item }">
                            <a
                                v-if="item.status === 'completed'"
                                :href="route('reports.download', item.id)"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-800 bg-zinc-950/40 px-4 py-2 text-sm text-indigo-400 hover:bg-zinc-800 hover:text-zinc-200 transition-all font-semibold active:scale-98"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                <span class="hidden md:inline">Baixar Relatório (XLSX)</span>
                            </a>
                            <span v-else class="text-zinc-500 text-xs font-bold animate-pulse">Aguarde...</span>
                        </template>
                    </DataTable>
                </div>

                <!-- Painel de Relatório e Desempenho (Tabela de Alunos) -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <div
                        class="flex flex-col justify-between gap-4 md:flex-row md:items-center mb-6"
                    >
                        <div>
                            <h3 class="text-lg font-bold text-white">
                                Classificação de Alunos (Internal Ranking)
                            </h3>
                            <div class="flex items-center gap-3 mt-1">
                                <p class="text-xs text-zinc-400">
                                    Veja o desempenho geral e a pontuação XP dos alunos da sua instituição.
                                </p>
                                <Button
                                    variant="secondary"
                                    @click="router.post(route('admin.reports.performance'))"
                                    class="text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/10 !py-1.5 !px-3 text-xs"
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
                                    <span class="hidden md:inline">Exportar Relatório</span>
                                </Button>
                            </div>
                        </div>
                    </div>

                    <DataTable
                        :items="students"
                        :columns="studentColumns"
                        searchPlaceholder="Buscar aluno..."
                    >
                        <template #rank="{ item }">
                            <span
                                v-if="students.findIndex(s => s.id === item.id) === 0"
                                class="inline-block rounded bg-amber-500/10 px-2 py-0.5 text-xs font-bold text-amber-500"
                            >🥇 1º</span>
                            <span
                                v-else-if="students.findIndex(s => s.id === item.id) === 1"
                                class="inline-block rounded bg-zinc-300/10 px-2 py-0.5 text-xs font-bold text-zinc-300"
                            >🥈 2º</span>
                            <span
                                v-else-if="students.findIndex(s => s.id === item.id) === 2"
                                class="inline-block rounded bg-amber-700/10 px-2 py-0.5 text-xs font-bold text-amber-700"
                            >🥉 3º</span>
                            <span
                                v-else
                                class="text-xs font-semibold text-zinc-500"
                            >{{ students.findIndex(s => s.id === item.id) + 1 }}º</span>
                        </template>
                        <template #name="{ item }">
                            <span class="font-semibold text-white">{{ item.name }}</span>
                        </template>
                        <template #email="{ item }">
                            <span class="text-zinc-400">{{ item.email }}</span>
                        </template>
                        <template #points="{ item }">
                            <span class="font-bold text-emerald-450">{{ item.points }} XP</span>
                        </template>
                    </DataTable>
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
