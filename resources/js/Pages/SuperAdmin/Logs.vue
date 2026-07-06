<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import Button from '@/Components/Button.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import DataTable from '@/Components/DataTable.vue';
import PageHeader from '@/Components/PageHeader.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { FileText, Play, Terminal, Trash2 } from '@lucide/vue';
import { ref } from 'vue';

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
    { key: 'name', label: __('superadmin.logs.col_log_file'), sortable: true },
    { key: 'size', label: __('superadmin.logs.col_size'), sortable: true },
    {
        key: 'modified_at',
        label: __('superadmin.logs.col_last_modified'),
        sortable: true,
    },
    { key: 'actions', label: __('common.actions'), align: 'right' },
];

const failedJobColumns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'queue', label: __('superadmin.logs.col_queue'), sortable: true },
    {
        key: 'failed_at',
        label: __('superadmin.logs.col_failed_at'),
        sortable: true,
    },
    {
        key: 'exception',
        label: __('superadmin.logs.col_exception'),
        sortable: false,
    },
    { key: 'actions', label: __('common.actions'), align: 'right' },
];

const pruneForm = useForm({});
const isPruning = ref(false);

const pruneLogs = () => {
    isPruning.value = true;
    pruneForm.post(route('super-admin.logs.prune'), {
        preserveScroll: true,
        onFinish: () => {
            isPruning.value = false;
        },
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
        __('superadmin.logs.confirm_retry_title'),
        __('superadmin.logs.confirm_retry_message'),
        'info',
        () => {
            router.post(route('super-admin.failed-jobs.retry', jobId));
        },
    );
};

const deleteJob = (jobId) => {
    triggerConfirm(
        __('superadmin.logs.confirm_delete_job_title'),
        __('superadmin.logs.confirm_delete_job_message'),
        'danger',
        () => {
            router.delete(route('super-admin.failed-jobs.destroy', jobId));
        },
    );
};

const isLogModalOpen = ref(!!props.selectedLog);

const openLogContent = (logName) => {
    router.visit(route('super-admin.logs.index', { log_file: logName }), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isLogModalOpen.value = true;
        },
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
        if (isNaN(d.getTime())) return __('superadmin.logs.invalid_date');
        return d.toLocaleString('pt-BR');
    } catch (e) {
        return __('superadmin.logs.invalid_date');
    }
};
</script>

<template>
    <Head :title="__('superadmin.logs.head_title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="__('superadmin.logs.header')">
                <template #actions>
                    <Button
                        @click="pruneLogs"
                        :disabled="isPruning"
                        class="flex items-center gap-2 bg-amber-600 font-bold hover:bg-amber-500"
                        :title="__('superadmin.logs.prune_title')"
                    >
                        <template #icon>
                            <span
                                v-if="isPruning"
                                class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                            ></span>
                            <Trash2 v-else class="h-4 w-4" />
                        </template>
                        <span class="hidden md:inline">{{
                            isPruning
                                ? __('superadmin.logs.pruning')
                                : __('superadmin.logs.prune_title')
                        }}</span>
                    </Button>
                </template>
            </PageHeader>
        </template>

        <div class="space-y-8 bg-zinc-950 py-6 text-zinc-100">
            <!-- Arquivos de Log -->
            <div
                class="space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
            >
                <div>
                    <h3
                        class="flex items-center gap-2 text-lg font-bold text-white"
                    >
                        <FileText class="h-5 w-5 text-indigo-400" />
                        {{ __('superadmin.logs.files_title') }}
                    </h3>
                    <p class="text-xs text-zinc-400">
                        {{ __('superadmin.logs.files_subtitle') }}
                    </p>
                </div>

                <DataTable
                    :items="logs"
                    :columns="logColumns"
                    :searchPlaceholder="__('superadmin.logs.search_log')"
                >
                    <template #name="{ item }">
                        <span
                            class="font-mono text-xs font-semibold text-zinc-200"
                            >{{ item.name }}</span
                        >
                    </template>
                    <template #size="{ item }">
                        <span
                            class="font-mono text-xs font-medium text-zinc-400"
                            >{{ item.size }}</span
                        >
                    </template>
                    <template #modified_at="{ item }">
                        <span class="font-mono text-xs text-zinc-400">{{
                            item.modified_at
                        }}</span>
                    </template>
                    <template #actions="{ item }">
                        <div class="flex justify-end">
                            <Button
                                size="sm"
                                variant="secondary"
                                @click="openLogContent(item.name)"
                            >
                                {{ __('superadmin.logs.view') }}
                            </Button>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- Fila de Jobs Falhos -->
            <div
                class="space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
            >
                <div>
                    <h3
                        class="flex items-center gap-2 text-lg font-bold text-white"
                    >
                        <Terminal class="h-5 w-5 text-indigo-400" />
                        {{ __('superadmin.logs.failed_jobs_title') }}
                    </h3>
                    <p class="text-xs font-semibold text-zinc-400">
                        {{ __('superadmin.logs.failed_jobs_subtitle') }}
                    </p>
                </div>

                <DataTable
                    :items="failedJobs"
                    :columns="failedJobColumns"
                    :searchPlaceholder="
                        __('superadmin.logs.search_error_queue')
                    "
                >
                    <template #id="{ item }">
                        <span class="font-mono font-bold text-zinc-400"
                            >#{{ item.id }}</span
                        >
                    </template>
                    <template #queue="{ item }">
                        <span
                            class="inline-flex items-center rounded-full bg-zinc-800 px-2.5 py-0.5 font-mono text-xs font-semibold text-zinc-300"
                        >
                            {{ item.queue }}
                        </span>
                    </template>
                    <template #failed_at="{ item }">
                        <span class="font-mono text-xs text-zinc-400">{{
                            formatDateTime(item.failed_at)
                        }}</span>
                    </template>
                    <template #exception="{ item }">
                        <div
                            class="text-rose-455 max-w-md truncate font-mono text-xs"
                            :title="item.exception"
                        >
                            {{ item.exception }}
                        </div>
                    </template>
                    <template #actions="{ item }">
                        <div class="flex items-center justify-end gap-2">
                            <Tooltip
                                :text="__('superadmin.logs.tooltip_retry')"
                            >
                                <Button
                                    variant="icon"
                                    @click="retryJob(item.id)"
                                    class="text-indigo-400"
                                >
                                    <template #icon
                                        ><Play class="h-4 w-4"
                                    /></template>
                                </Button>
                            </Tooltip>

                            <Tooltip
                                :text="__('superadmin.logs.tooltip_delete_job')"
                            >
                                <Button
                                    variant="icon"
                                    @click="deleteJob(item.id)"
                                    class="text-red-500 hover:text-red-400"
                                >
                                    <template #icon
                                        ><Trash2 class="h-4 w-4"
                                    /></template>
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
            :title="
                selectedLog
                    ? __('superadmin.logs.modal_viewing', {
                          name: selectedLog.name,
                      })
                    : __('superadmin.logs.modal_system_log')
            "
            maxWidth="6xl"
            @close="closeLogModal"
        >
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-zinc-450 text-xs font-bold">
                        {{
                            __('superadmin.logs.showing_lines', {
                                loaded: selectedLog?.loaded_lines,
                                total: selectedLog?.total_lines,
                            })
                        }}
                    </p>
                    <Button
                        variant="secondary"
                        size="sm"
                        @click="closeLogModal"
                    >
                        {{ __('superadmin.logs.close') }}
                    </Button>
                </div>

                <div
                    class="scrollbar-thin max-h-[60vh] overflow-auto whitespace-pre rounded-xl border border-zinc-800 bg-zinc-950 p-4 font-mono text-xs text-zinc-300"
                >
                    {{
                        selectedLog?.content || __('superadmin.logs.empty_log')
                    }}
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
