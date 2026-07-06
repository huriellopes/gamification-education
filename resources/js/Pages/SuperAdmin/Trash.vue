<script setup>
import Button from '@/Components/Button.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import DataTable from '@/Components/DataTable.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { RotateCcw } from '@lucide/vue';
import { ref } from 'vue';

defineProps({
    deletedModels: {
        type: Array,
        default: () => [],
    },
});

const trashColumns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'model', label: __('superadmin.trash.col_model'), sortable: true },
    {
        key: 'values',
        label: __('superadmin.trash.col_values'),
        sortable: false,
    },
    { key: 'actions', label: __('common.actions'), align: 'right' },
];

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

const confirmRestore = (del) => {
    triggerConfirm(
        __('superadmin.trash.confirm_restore_title'),
        __('superadmin.trash.confirm_restore_message', { model: del.model }),
        'info',
        () => {
            router.post(route('super-admin.deleted-models.restore', del.id));
        },
    );
};
</script>

<template>
    <Head :title="__('superadmin.trash.head_title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="__('superadmin.trash.header')" />
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100">
            <div
                class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
            >
                <DataTable
                    :items="deletedModels"
                    :columns="trashColumns"
                    :searchPlaceholder="
                        __('superadmin.trash.search_placeholder')
                    "
                >
                    <template #id="{ item }">
                        <span class="font-mono text-zinc-400"
                            >#{{ item.id }}</span
                        >
                    </template>
                    <template #model="{ item }">
                        <span
                            class="font-mono text-xs font-semibold text-zinc-300"
                            >{{ item.model }}</span
                        >
                    </template>
                    <template #values="{ item }">
                        <div
                            class="max-w-xl truncate font-mono text-xs text-zinc-400"
                        >
                            {{ JSON.stringify(item.values) }}
                        </div>
                    </template>
                    <template #actions="{ item }">
                        <div class="flex justify-end">
                            <Button
                                size="sm"
                                class="bg-indigo-600 font-bold text-white hover:bg-indigo-500"
                                @click="confirmRestore(item)"
                            >
                                <template #icon
                                    ><RotateCcw class="h-4 w-4"
                                /></template>
                                <span>{{
                                    __('superadmin.trash.restore')
                                }}</span>
                            </Button>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

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
