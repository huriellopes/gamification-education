<script setup>
import ConfirmModal from '@/Components/ConfirmModal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Button from '@/Components/Button.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { RotateCcw } from '@lucide/vue';

const props = defineProps({
    deletedModels: {
        type: Array,
        default: () => [],
    },
});

const trashColumns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'model', label: 'Modelo', sortable: true },
    { key: 'values', label: 'Dados do Registro', sortable: false },
    { key: 'actions', label: 'Ações', align: 'right' },
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
        'Restaurar Registro',
        `Deseja restaurar este registro apagado (${del.model})?`,
        'info',
        () => {
            router.post(route('super-admin.deleted-models.restore', del.id));
        },
    );
};
</script>

<template>
    <Head title="Lixeira do Sistema" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-zinc-100">
                Lixeira do Sistema (Soft Deletes)
            </h2>
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md">
                <DataTable
                    :items="deletedModels"
                    :columns="trashColumns"
                    searchPlaceholder="Buscar na lixeira..."
                >
                    <template #id="{ item }">
                        <span class="text-zinc-500 font-mono">#{{ item.id }}</span>
                    </template>
                    <template #model="{ item }">
                        <span class="font-semibold text-zinc-300 font-mono text-xs">{{ item.model }}</span>
                    </template>
                    <template #values="{ item }">
                        <div class="text-xs text-zinc-400 font-mono max-w-xl truncate">
                            {{ JSON.stringify(item.values) }}
                        </div>
                    </template>
                    <template #actions="{ item }">
                        <div class="flex justify-end">
                            <Button
                                size="sm"
                                class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold"
                                @click="confirmRestore(item)"
                            >
                                <template #icon><RotateCcw class="h-4 w-4" /></template>
                                <span>Restaurar</span>
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
