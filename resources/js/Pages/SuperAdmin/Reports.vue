<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Button from '@/Components/Button.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Users, TrendingUp, FileSpreadsheet } from '@lucide/vue';

const props = defineProps({
    reports: {
        type: Array,
        default: () => [],
    },
    institutions: {
        type: Array,
        default: () => [],
    },
});

const reportColumns = [
    { key: 'name', label: 'Nome do Relatório', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Solicitado em', sortable: true },
    { key: 'actions', label: 'Ações', align: 'right' },
];

const reportForm = useForm({
    institution_id: '',
});

const requestMembersReport = () => {
    reportForm.post(route('super-admin.reports.members'), {
        onSuccess: () => {
            reportForm.reset();
        }
    });
};

const requestPerformanceReport = () => {
    reportForm.post(route('super-admin.reports.performance'), {
        onSuccess: () => {
            reportForm.reset();
        }
    });
};

// Polling for pending reports
const pollInterval = ref(null);

const checkAndStartPolling = () => {
    const hasPending = props.reports.some(r => r.status === 'pending');
    if (hasPending) {
        if (!pollInterval.value) {
            pollInterval.value = setInterval(() => {
                router.reload({
                    only: ['reports'],
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

onMounted(() => {
    checkAndStartPolling();
});

onUnmounted(() => {
    stopPolling();
});

const formatDateTime = (dateStr) => {
    if (!dateStr) return '';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return 'Data Inválida';
        return d.toLocaleString('pt-BR');
    } catch (e) {
        return 'Data Inválida';
    }
};
</script>

<template>
    <Head title="Relatórios do Sistema" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-zinc-100">
                Relatórios e Exportações
            </h2>
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100 space-y-6">
            <!-- Solicitar Relatório Card -->
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md space-y-4">
                <h3 class="text-lg font-bold text-white">Solicitar Novo Relatório</h3>
                <p class="text-sm text-zinc-400">
                    Selecione uma instituição específica para filtrar o relatório, ou deixe em branco para gerar um relatório consolidado com dados de todas as instituições.
                </p>

                <div class="flex flex-wrap gap-4 items-end">
                    <div class="w-full sm:w-64">
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-450">Filtrar por Instituição</label>
                        <select
                            v-model="reportForm.institution_id"
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none"
                        >
                            <option value="">Todas as Instituições</option>
                            <option v-for="inst in institutions" :key="inst.id" :value="inst.id">
                                {{ inst.name }}
                            </option>
                        </select>
                    </div>

                    <div class="flex gap-3">
                        <Button 
                            @click="requestMembersReport" 
                            :disabled="reportForm.processing"
                            class="bg-indigo-600 hover:bg-indigo-500 font-bold flex items-center gap-2 px-3 py-2.5 md:px-4"
                            title="Gerar Relatório de Membros"
                        >
                            <Users class="h-4 w-4 shrink-0" />
                            <span class="hidden md:inline">Relatório de Membros</span>
                        </Button>
                        <Button 
                            @click="requestPerformanceReport" 
                            :disabled="reportForm.processing"
                            class="bg-emerald-600 hover:bg-emerald-500 font-bold flex items-center gap-2 px-3 py-2.5 md:px-4"
                            title="Gerar Relatório de Desempenho"
                        >
                            <TrendingUp class="h-4 w-4 shrink-0" />
                            <span class="hidden md:inline">Relatório de Desempenho</span>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Fila de Relatórios -->
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md space-y-4">
                <div>
                    <h3 class="text-lg font-bold text-white">Histórico e Fila de Relatórios</h3>
                    <p class="text-xs text-zinc-400">Os relatórios são processados em fila de forma assíncrona. Assim que concluídos, o botão para download ficará disponível.</p>
                </div>

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
                            {{ formatDateTime(item.created_at) }}
                        </span>
                    </template>
                    <template #actions="{ item }">
                        <div class="flex justify-end">
                            <a
                                v-if="item.status === 'completed'"
                                :href="route('reports.download', item.id)"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-amber-500/20 bg-amber-500/5 px-3 py-2.5 md:px-4 md:py-2 text-sm text-amber-400 hover:bg-amber-500/10 transition-all font-semibold active:scale-98"
                                title="Baixar Relatório (XLSX)"
                            >
                                <FileSpreadsheet class="h-4 w-4 shrink-0" />
                                <span class="hidden md:inline">Download (XLSX)</span>
                            </a>
                            <span v-else class="text-zinc-550 text-xs font-bold animate-pulse">Processando...</span>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
