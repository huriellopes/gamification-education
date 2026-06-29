<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Button from '@/Components/Button.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    Terminal, 
    RotateCcw, 
    Trash2, 
    Play, 
    FileText, 
    AlertCircle, 
    CheckCircle 
} from '@lucide/vue';

const props = defineProps({
    logs: {
        type: Array,
        default: () => [],
    },
    selectedLog: {
        type: Object,
        default: () => null,
    },
    failedJobs: {
        type: Array,
        default: () => [],
    },
});

const logColumns = [
    { key: 'name', label: 'Arquivo de Log', sortable: true },
    { key: 'size', label: 'Tamanho', sortable: true },
    { key: 'modified_at', label: 'Última Modificação', sortable: true },
    { key: 'actions', label: 'Ações', align: 'right' },
];

const failedJobColumns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'queue', label: 'Fila', sortable: true },
    { key: 'failed_at', label: 'Falhou em', sortable: true },
    { key: 'exception', label: 'Erro / Exceção', sortable: false },
    { key: 'actions', label: 'Ações', align: 'right' },
];

const pruneForm = useForm({});
const isPruning = ref(false);

const pruneLogs = () => {
    isPruning.value = true;
    pruneForm.post(route('super-admin.logs.prune'), {
        preserveScroll: true,
        onFinish: () => {
            isPruning.value = false;
        }
    });
};

const confirmState = ref({
    show: false,
    title: '',
    message: '',
    type: 'danger',
    onConfirm: () => {},
});

const triggerConfirm = (title, message, type, onConfirm) => {
    confirmState.value = {
        show: true,
        title,
        message,
        type,
        onConfirm: () => {
            onConfirm();
            confirmState.value.show = false;
        },
    };
};

const retryJob = (jobId) => {
    triggerConfirm(
        'Retentar Job',
        'Deseja reinserir este job falho de volta na fila de processamento?',
        'info',
        () => {
            router.post(route('super-admin.failed-jobs.retry', jobId));
        }
    );
};

const deleteJob = (jobId) => {
    triggerConfirm(
        'Remover Job Falho',
        'Tem certeza de que deseja excluir permanentemente este job falho?',
        'danger',
        () => {
            router.delete(route('super-admin.failed-jobs.destroy', jobId));
        }
    );
};

const isLogModalOpen = ref(!!props.selectedLog);

const openLogContent = (logName) => {
    router.visit(route('super-admin.logs.index', { log_file: logName }), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isLogModalOpen.value = true;
        }
    });
};

const closeLogModal = () => {
    isLogModalOpen.value = false;
    router.visit(route('super-admin.logs.index'), {
        preserveScroll: true,
        preserveState: true,
    });
};

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
    <Head title="Logs e Filas do Sistema" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h2 class="text-xl font-bold leading-tight text-zinc-100">
                    Logs do Laravel & Fila de Processamento
                </h2>
                <Button 
                    @click="pruneLogs" 
                    :disabled="isPruning"
                    class="bg-amber-600 hover:bg-amber-500 font-bold flex items-center gap-2"
                    title="Limpar Logs Antigos"
                >
                    <template #icon>
                        <span v-if="isPruning" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        <Trash2 v-else class="h-4 w-4" />
                    </template>
                    <span class="hidden md:inline">{{ isPruning ? 'Limpando...' : 'Limpar Logs Antigos' }}</span>
                </Button>
            </div>
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100 space-y-8">
            <!-- Arquivos de Log -->
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md space-y-4">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <FileText class="h-5 w-5 text-indigo-400" />
                        Arquivos de Log Recentes
                    </h3>
                    <p class="text-xs text-zinc-400">Clique em "Visualizar" para ler as últimas 300 linhas de cada arquivo de log do Laravel.</p>
                </div>

                <DataTable
                    :items="logs"
                    :columns="logColumns"
                    searchPlaceholder="Buscar arquivo de log..."
                >
                    <template #name="{ item }">
                        <span class="font-semibold text-zinc-200 font-mono text-xs">{{ item.name }}</span>
                    </template>
                    <template #size="{ item }">
                        <span class="text-zinc-400 font-medium font-mono text-xs">{{ item.size }}</span>
                    </template>
                    <template #modified_at="{ item }">
                        <span class="text-zinc-400 font-mono text-xs">{{ item.modified_at }}</span>
                    </template>
                    <template #actions="{ item }">
                        <div class="flex justify-end">
                            <Button 
                                size="sm" 
                                variant="secondary" 
                                @click="openLogContent(item.name)"
                            >
                                Visualizar
                            </Button>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- Fila de Jobs Falhos -->
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md space-y-4">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <Terminal class="h-5 w-5 text-indigo-400" />
                        Fila de Jobs Falhos (`failed_jobs`)
                    </h3>
                    <p class="text-xs text-zinc-400 font-semibold">Erros gerados durante o processamento assíncrono. Você pode reprocessar (retry) ou apagar os jobs falhos.</p>
                </div>

                <DataTable
                    :items="failedJobs"
                    :columns="failedJobColumns"
                    searchPlaceholder="Buscar na fila de erros..."
                >
                    <template #id="{ item }">
                        <span class="text-zinc-500 font-mono font-bold">#{{ item.id }}</span>
                    </template>
                    <template #queue="{ item }">
                        <span class="inline-flex items-center rounded-full bg-zinc-800 px-2.5 py-0.5 text-xs font-semibold text-zinc-300 font-mono">
                            {{ item.queue }}
                        </span>
                    </template>
                    <template #failed_at="{ item }">
                        <span class="text-zinc-400 font-mono text-xs">{{ formatDateTime(item.failed_at) }}</span>
                    </template>
                    <template #exception="{ item }">
                        <div class="max-w-md truncate text-xs text-rose-455 font-mono" :title="item.exception">
                            {{ item.exception }}
                        </div>
                    </template>
                    <template #actions="{ item }">
                        <div class="flex items-center justify-end gap-2">
                            <Tooltip text="Retentar Job">
                                <Button
                                    variant="icon"
                                    @click="retryJob(item.id)"
                                    class="text-indigo-400"
                                >
                                    <template #icon><Play class="h-4 w-4" /></template>
                                </Button>
                            </Tooltip>

                            <Tooltip text="Excluir Job">
                                <Button
                                    variant="icon"
                                    @click="deleteJob(item.id)"
                                    class="text-red-500 hover:text-red-400"
                                >
                                    <template #icon><Trash2 class="h-4 w-4" /></template>
                                </Button>
                            </Tooltip>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

        <!-- Modal Visualizar Detalhes do Log -->
        <BaseModal
            :show="isLogModalOpen && !!selectedLog"
            :title="selectedLog ? `Visualizando ${selectedLog.name}` : 'Log do Sistema'"
            maxWidth="6xl"
            @close="closeLogModal"
        >
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-zinc-450 font-bold">
                        Mostrando as últimas {{ selectedLog?.loaded_lines }} linhas (Total de {{ selectedLog?.total_lines }} linhas no arquivo).
                    </p>
                    <Button variant="secondary" size="sm" @click="closeLogModal">
                        Fechar
                    </Button>
                </div>

                <div class="bg-zinc-950 rounded-xl p-4 border border-zinc-800 overflow-auto max-h-[60vh] font-mono text-xs text-zinc-300 whitespace-pre scrollbar-thin">
                    {{ selectedLog?.content || 'Arquivo de log vazio.' }}
                </div>
            </div>
        </BaseModal>

        <ConfirmModal
            :show="confirmState.show"
            :title="confirmState.title"
            :message="confirmState.message"
            :type="confirmState.type"
            @close="confirmState.show = false"
            @confirm="confirmState.onConfirm"
        />
    </AuthenticatedLayout>
</template>
