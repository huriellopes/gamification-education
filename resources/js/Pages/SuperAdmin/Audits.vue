<script setup>
import DataTable from '@/Components/DataTable.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ShieldCheck } from '@lucide/vue';

defineProps({
    audits: {
        type: Array,
        default: () => [],
    },
});

const columns = [
    { key: 'created_at', label: __('superadmin.audits.col_when'), sortable: true },
    { key: 'user', label: __('superadmin.audits.col_user'), sortable: true },
    { key: 'event', label: __('superadmin.audits.col_event'), sortable: true },
    { key: 'entity', label: __('superadmin.audits.col_entity'), sortable: false },
    { key: 'changes', label: __('superadmin.audits.col_changes'), sortable: false },
    { key: 'ip_address', label: __('superadmin.audits.col_ip'), sortable: false },
];

const EVENT_CLASSES = {
    created: 'bg-emerald-500/10 text-emerald-400',
    updated: 'bg-amber-500/10 text-amber-400',
    deleted: 'bg-red-500/10 text-red-400',
    restored: 'bg-indigo-500/10 text-indigo-400',
};

const eventClass = (event) =>
    EVENT_CLASSES[event] ?? 'bg-zinc-700/40 text-zinc-300';

const eventLabel = (event) =>
    __(`superadmin.audits.event_${event}`) !== `superadmin.audits.event_${event}`
        ? __(`superadmin.audits.event_${event}`)
        : event;

// Campos alterados: chaves dos novos valores (ou antigos, em exclusões).
const changedKeys = (audit) => {
    const source =
        audit.new_values && Object.keys(audit.new_values).length
            ? audit.new_values
            : (audit.old_values ?? {});
    return Object.keys(source);
};

const changeTitle = (audit) => {
    try {
        return JSON.stringify(
            {
                old: audit.old_values ?? {},
                new: audit.new_values ?? {},
            },
            null,
            2,
        );
    } catch (e) {
        return '';
    }
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return isNaN(d.getTime()) ? '' : d.toLocaleString('pt-BR');
};
</script>

<template>
    <Head :title="__('superadmin.audits.head_title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="__('superadmin.audits.header')" />
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100">
            <div
                class="space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
            >
                <div>
                    <h3
                        class="flex items-center gap-2 text-lg font-bold text-white"
                    >
                        <ShieldCheck class="h-5 w-5 text-indigo-400" />
                        {{ __('superadmin.audits.history_title') }}
                    </h3>
                    <p class="text-xs text-zinc-400">
                        {{ __('superadmin.audits.history_subtitle') }}
                    </p>
                </div>

                <DataTable
                    :items="audits"
                    :columns="columns"
                    :search-placeholder="
                        __('superadmin.audits.search_placeholder')
                    "
                >
                    <template #created_at="{ item }">
                        <span
                            class="whitespace-nowrap font-mono text-xs font-semibold text-zinc-300"
                            >{{ formatDateTime(item.created_at) }}</span
                        >
                    </template>

                    <template #user="{ item }">
                        <span class="text-sm text-zinc-200">{{ item.user }}</span>
                    </template>

                    <template #event="{ item }">
                        <span
                            class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold uppercase"
                            :class="eventClass(item.event)"
                        >
                            {{ eventLabel(item.event) }}
                        </span>
                    </template>

                    <template #entity="{ item }">
                        <span class="text-sm text-zinc-300">
                            {{ item.auditable_type }}
                            <span class="font-mono text-zinc-500"
                                >#{{ item.auditable_id }}</span
                            >
                        </span>
                    </template>

                    <template #changes="{ item }">
                        <div
                            class="flex max-w-xs flex-wrap gap-1"
                            :title="changeTitle(item)"
                        >
                            <span
                                v-for="key in changedKeys(item)"
                                :key="key"
                                class="rounded-md bg-zinc-800 px-1.5 py-0.5 font-mono text-[10px] text-zinc-300"
                                >{{ key }}</span
                            >
                            <span
                                v-if="!changedKeys(item).length"
                                class="text-xs text-zinc-500"
                                >{{ __('superadmin.audits.no_changes') }}</span
                            >
                        </div>
                    </template>

                    <template #ip_address="{ item }">
                        <span class="font-mono text-xs text-zinc-400">{{
                            item.ip_address ?? '—'
                        }}</span>
                    </template>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
