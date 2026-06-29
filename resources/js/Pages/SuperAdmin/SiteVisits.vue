<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Button from '@/Components/Button.vue';
import { Head } from '@inertiajs/vue3';
import { Eye, FileSpreadsheet } from '@lucide/vue';

const props = defineProps({
    siteVisits: {
        type: Array,
        default: () => [],
    },
});

const visitColumns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'visited_at', label: 'Data/Hora do Acesso', sortable: true },
    { key: 'ip_address', label: 'Endereço IP (Descriptografado)', sortable: true },
    { key: 'user_agent', label: 'Dispositivo / User Agent', sortable: true },
];

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
    <Head title="Visitas ao Site" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h2 class="text-xl font-bold leading-tight text-zinc-100">
                    Logs de Visitas ao Site Público
                </h2>
                <!-- Export to Excel (.xlsx) -->
                <a
                    :href="route('super-admin.visits.export')"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 px-4 py-2.5 text-xs font-bold text-white transition-all shadow-md active:scale-98"
                >
                    <FileSpreadsheet class="h-4 w-4" />
                    <span>Exportar para Excel (.xlsx)</span>
                </a>
            </div>
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md space-y-4">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <Eye class="h-5 w-5 text-indigo-400" />
                        Histórico de Acessos
                    </h3>
                    <p class="text-xs text-zinc-400">Relação completa de usuários e visitantes que acessaram a página inicial do site público. Os IPs são armazenados criptografados por segurança.</p>
                </div>

                <DataTable
                    :items="siteVisits"
                    :columns="visitColumns"
                    searchPlaceholder="Buscar por IP ou navegador..."
                >
                    <template #id="{ item }">
                        <span class="text-zinc-500 font-mono">#{{ item.id }}</span>
                    </template>
                    <template #visited_at="{ item }">
                        <span class="font-semibold text-zinc-300 font-mono text-xs">{{ formatDateTime(item.visited_at) }}</span>
                    </template>
                    <template #ip_address="{ item }">
                        <span class="inline-flex items-center rounded-lg bg-zinc-950 px-2 py-1 text-xs font-mono font-bold text-indigo-400 border border-zinc-850">
                            {{ item.ip_address }}
                        </span>
                    </template>
                    <template #user_agent="{ item }">
                        <span class="text-xs text-zinc-450 font-mono" :title="item.user_agent">
                            {{ item.user_agent }}
                        </span>
                    </template>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
