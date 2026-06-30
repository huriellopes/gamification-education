<script setup>
import DataTable from '@/Components/DataTable.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Eye, FileSpreadsheet } from '@lucide/vue';

defineProps({
    siteVisits: {
        type: Array,
        default: () => [],
    },
});

const visitColumns = [
    { key: 'id', label: 'ID', sortable: true },
    {
        key: 'visited_at',
        label: __('superadmin.site_visits.col_visited_at'),
        sortable: true,
    },
    {
        key: 'ip_address',
        label: __('superadmin.site_visits.col_ip'),
        sortable: true,
    },
    {
        key: 'user_agent',
        label: __('superadmin.site_visits.col_user_agent'),
        sortable: true,
    },
];

const formatDateTime = (dateStr) => {
    if (!dateStr) return '';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime()))
            return __('superadmin.site_visits.invalid_date');
        return d.toLocaleString('pt-BR');
    } catch (e) {
        return __('superadmin.site_visits.invalid_date');
    }
};
</script>

<template>
    <Head :title="__('superadmin.site_visits.head_title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="__('superadmin.site_visits.header')">
                <template #actions>
                    <!-- Export to Excel (.xlsx) -->
                    <a
                        :href="route('super-admin.visits.export')"
                        class="active:scale-98 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:bg-emerald-500"
                    >
                        <FileSpreadsheet class="h-4 w-4" />
                        <span>{{
                            __('superadmin.site_visits.export_excel')
                        }}</span>
                    </a>
                </template>
            </PageHeader>
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100">
            <div
                class="space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
            >
                <div>
                    <h3
                        class="flex items-center gap-2 text-lg font-bold text-white"
                    >
                        <Eye class="h-5 w-5 text-indigo-400" />
                        {{ __('superadmin.site_visits.history_title') }}
                    </h3>
                    <p class="text-xs text-zinc-400">
                        {{ __('superadmin.site_visits.history_subtitle') }}
                    </p>
                </div>

                <DataTable
                    :items="siteVisits"
                    :columns="visitColumns"
                    :searchPlaceholder="
                        __('superadmin.site_visits.search_placeholder')
                    "
                >
                    <template #id="{ item }">
                        <span class="font-mono text-zinc-500"
                            >#{{ item.id }}</span
                        >
                    </template>
                    <template #visited_at="{ item }">
                        <span
                            class="font-mono text-xs font-semibold text-zinc-300"
                            >{{ formatDateTime(item.visited_at) }}</span
                        >
                    </template>
                    <template #ip_address="{ item }">
                        <span
                            class="border-zinc-850 inline-flex items-center rounded-lg border bg-zinc-950 px-2 py-1 font-mono text-xs font-bold text-indigo-400"
                        >
                            {{ item.ip_address }}
                        </span>
                    </template>
                    <template #user_agent="{ item }">
                        <span
                            class="text-zinc-450 font-mono text-xs"
                            :title="item.user_agent"
                        >
                            {{ item.user_agent }}
                        </span>
                    </template>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
