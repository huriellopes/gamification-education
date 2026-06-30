<script setup>
import Button from '@/Components/Button.vue';
import DataTable from '@/Components/DataTable.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { FileSpreadsheet, TrendingUp, Users } from '@lucide/vue';
import { onMounted, onUnmounted, ref, watch } from 'vue';

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
    {
        key: 'name',
        label: __('superadmin.reports.col_report_name'),
        sortable: true,
    },
    { key: 'status', label: __('common.status'), sortable: true },
    {
        key: 'created_at',
        label: __('superadmin.reports.col_requested_at'),
        sortable: true,
    },
    { key: 'actions', label: __('common.actions'), align: 'right' },
];

const reportForm = useForm({
    institution_id: '',
});

const requestMembersReport = () => {
    reportForm.post(route('super-admin.reports.members'), {
        onSuccess: () => {
            reportForm.reset();
        },
    });
};

const requestPerformanceReport = () => {
    reportForm.post(route('super-admin.reports.performance'), {
        onSuccess: () => {
            reportForm.reset();
        },
    });
};

// Polling for pending reports
const pollInterval = ref(null);

const checkAndStartPolling = () => {
    const hasPending = props.reports.some((r) => r.status === 'pending');
    if (hasPending) {
        if (!pollInterval.value) {
            pollInterval.value = setInterval(() => {
                router.reload({
                    only: ['reports'],
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
        if (isNaN(d.getTime())) return __('superadmin.reports.invalid_date');
        return d.toLocaleString('pt-BR');
    } catch (e) {
        return __('superadmin.reports.invalid_date');
    }
};
</script>

<template>
    <Head :title="__('superadmin.reports.head_title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="__('superadmin.reports.header')" />
        </template>

        <div class="space-y-6 bg-zinc-950 py-6 text-zinc-100">
            <!-- Solicitar Relatório Card -->
            <div
                class="space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
            >
                <h3 class="text-lg font-bold text-white">
                    {{ __('superadmin.reports.request_title') }}
                </h3>
                <p class="text-sm text-zinc-400">
                    {{ __('superadmin.reports.request_subtitle') }}
                </p>

                <div class="flex flex-wrap items-end gap-4">
                    <div class="w-full sm:w-64">
                        <label
                            class="text-zinc-450 mb-2 block text-xs font-bold uppercase"
                            >{{
                                __('superadmin.reports.filter_by_institution')
                            }}</label
                        >
                        <select
                            v-model="reportForm.institution_id"
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none"
                        >
                            <option value="">
                                {{ __('superadmin.reports.all_institutions') }}
                            </option>
                            <option
                                v-for="inst in institutions"
                                :key="inst.id"
                                :value="inst.id"
                            >
                                {{ inst.name }}
                            </option>
                        </select>
                    </div>

                    <div class="flex gap-3">
                        <Button
                            @click="requestMembersReport"
                            :disabled="reportForm.processing"
                            class="flex items-center gap-2 bg-indigo-600 px-3 py-2.5 font-bold hover:bg-indigo-500 md:px-4"
                            :title="
                                __('superadmin.reports.members_report_title')
                            "
                        >
                            <Users class="h-4 w-4 shrink-0" />
                            <span class="hidden md:inline">{{
                                __('superadmin.reports.members_report')
                            }}</span>
                        </Button>
                        <Button
                            @click="requestPerformanceReport"
                            :disabled="reportForm.processing"
                            class="flex items-center gap-2 bg-emerald-600 px-3 py-2.5 font-bold hover:bg-emerald-500 md:px-4"
                            :title="
                                __(
                                    'superadmin.reports.performance_report_title',
                                )
                            "
                        >
                            <TrendingUp class="h-4 w-4 shrink-0" />
                            <span class="hidden md:inline">{{
                                __('superadmin.reports.performance_report')
                            }}</span>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Fila de Relatórios -->
            <div
                class="space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
            >
                <div>
                    <h3 class="text-lg font-bold text-white">
                        {{ __('superadmin.reports.queue_title') }}
                    </h3>
                    <p class="text-xs text-zinc-400">
                        {{ __('superadmin.reports.queue_subtitle') }}
                    </p>
                </div>

                <DataTable
                    :items="reports"
                    :columns="reportColumns"
                    :searchPlaceholder="
                        __('superadmin.reports.search_placeholder')
                    "
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
                            {{ __('superadmin.reports.processing') }}
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/10 px-2.5 py-1 text-xs font-semibold text-emerald-400"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-emerald-400"
                            ></span>
                            {{ __('superadmin.reports.completed') }}
                        </span>
                    </template>
                    <template #created_at="{ item }">
                        <span class="font-mono text-xs text-zinc-400">
                            {{ formatDateTime(item.created_at) }}
                        </span>
                    </template>
                    <template #actions="{ item }">
                        <div class="flex justify-end">
                            <a
                                v-if="item.status === 'completed'"
                                :href="route('reports.download', item.id)"
                                class="active:scale-98 inline-flex items-center justify-center gap-2 rounded-xl border border-amber-500/20 bg-amber-500/5 px-3 py-2.5 text-sm font-semibold text-amber-400 transition-all hover:bg-amber-500/10 md:px-4 md:py-2"
                                :title="__('superadmin.reports.download_title')"
                            >
                                <FileSpreadsheet class="h-4 w-4 shrink-0" />
                                <span class="hidden md:inline">{{
                                    __('superadmin.reports.download')
                                }}</span>
                            </a>
                            <span
                                v-else
                                class="text-zinc-550 animate-pulse text-xs font-bold"
                                >{{
                                    __('superadmin.reports.processing_ellipsis')
                                }}</span
                            >
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
