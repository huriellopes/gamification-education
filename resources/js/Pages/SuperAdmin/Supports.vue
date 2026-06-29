<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Button from '@/Components/Button.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { HelpCircle, Mail, CheckCircle, Eye } from '@lucide/vue';

const props = defineProps({
    supports: {
        type: Array,
        default: () => [],
    },
});

const supportColumns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'user_name', label: 'Solicitante', sortable: true },
    { key: 'institution_name', label: 'Instituição', sortable: true },
    { key: 'subject', label: 'Assunto', sortable: true },
    { key: 'status', label: 'Status', sortable: true, align: 'center' },
    { key: 'created_at', label: 'Criado em', sortable: true },
    { key: 'actions', label: 'Ações', align: 'right' },
];

const selectedSupport = ref(null);
const isReplyModalOpen = ref(false);

const replyForm = useForm({
    reply: '',
});

const openReplyModal = (support) => {
    selectedSupport.value = support;
    replyForm.reply = support.reply || '';
    isReplyModalOpen.value = true;
};

const submitReply = () => {
    replyForm.post(route('super-admin.supports.reply', selectedSupport.value.id), {
        onSuccess: () => {
            isReplyModalOpen.value = false;
            replyForm.reset();
            selectedSupport.value = null;
        }
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
    <Head title="Chamados de Suporte" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-zinc-100">
                Suporte Técnico & Chamados
            </h2>
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md">
                <DataTable
                    :items="supports"
                    :columns="supportColumns"
                    searchPlaceholder="Buscar por solicitante, assunto ou instituição..."
                >
                    <template #id="{ item }">
                        <span class="text-zinc-500 font-mono">#{{ item.id }}</span>
                    </template>
                    <template #user_name="{ item }">
                        <div class="font-semibold text-zinc-150">{{ item.user_name }}</div>
                        <div class="text-[10px] text-zinc-500">{{ item.user_email }}</div>
                    </template>
                    <template #institution_name="{ item }">
                        <span class="text-xs text-indigo-400 font-semibold">{{ item.institution_name || 'N/A' }}</span>
                    </template>
                    <template #subject="{ item }">
                        <div class="font-medium text-zinc-200">{{ item.subject }}</div>
                        <div class="max-w-xs truncate text-[10px] text-zinc-500">{{ item.message }}</div>
                    </template>
                    <template #status="{ item }">
                        <span v-if="item.status === 'answered'" class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-400">
                            <CheckCircle class="h-3 w-3" /> Respondido
                        </span>
                        <span v-else class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-bold text-amber-400">
                            <HelpCircle class="h-3 w-3" /> Pendente
                        </span>
                    </template>
                    <template #created_at="{ item }">
                        <span class="text-xs font-mono text-zinc-450">
                            {{ formatDateTime(item.created_at) }}
                        </span>
                    </template>
                    <template #actions="{ item }">
                        <div class="flex justify-end">
                            <Button 
                                v-if="item.status === 'answered'"
                                size="sm" 
                                variant="secondary" 
                                class="flex items-center gap-1 bg-zinc-800 hover:bg-zinc-700 text-zinc-300"
                                @click="openReplyModal(item)"
                                aria-label="Visualizar Chamado Respondido"
                            >
                                <Eye class="h-3.5 w-3.5" />
                                <span>Visualizar</span>
                            </Button>
                            <Button 
                                v-else
                                size="sm" 
                                class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold"
                                @click="openReplyModal(item)"
                                aria-label="Responder Chamado Pendente"
                            >
                                Responder
                            </Button>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

        <!-- Base Modal: Detalhes do Chamado e Resposta -->
        <BaseModal
            :show="isReplyModalOpen && !!selectedSupport"
            :title="selectedSupport?.status === 'answered' ? 'Visualizar Chamado' : 'Responder Chamado de Suporte'"
            maxWidth="3xl"
            @close="isReplyModalOpen = false"
        >
            <div class="space-y-6" v-if="selectedSupport">
                <!-- Informações Originais -->
                <div class="rounded-xl border border-zinc-800 bg-zinc-950 p-4 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:justify-between border-b border-zinc-850 pb-2 text-xs font-semibold text-zinc-400 gap-1">
                        <div>
                            Solicitante: <span class="text-zinc-250 font-bold">{{ selectedSupport.user_name }}</span> ({{ selectedSupport.user_email }})
                        </div>
                        <div>
                            Unidade: <span class="text-indigo-400 font-bold">{{ selectedSupport.institution_name || 'Geral' }}</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold uppercase text-zinc-450">Assunto</h4>
                        <p class="text-sm font-semibold text-white">{{ selectedSupport.subject }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold uppercase text-zinc-450">Mensagem do Usuário</h4>
                        <p class="text-sm text-zinc-300 whitespace-pre-line">{{ selectedSupport.message }}</p>
                    </div>
                    <div class="text-[10px] text-zinc-550 font-mono">
                        Criado em: {{ formatDateTime(selectedSupport.created_at) }}
                    </div>
                </div>

                <!-- Se o chamado já está respondido -->
                <div v-if="selectedSupport.status === 'answered'" class="space-y-4">
                    <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/5 p-4 space-y-2">
                        <h4 class="text-xs font-bold uppercase text-emerald-400 flex items-center gap-1.5">
                            <CheckCircle class="h-3.5 w-3.5" />
                            Resposta Enviada
                        </h4>
                        <p class="text-sm text-zinc-200 whitespace-pre-line">{{ selectedSupport.reply }}</p>
                        <div class="text-[10px] text-zinc-500 font-mono pt-2 border-t border-emerald-500/10">
                            Respondido em: {{ formatDateTime(selectedSupport.replied_at) }}
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-zinc-850">
                        <Button variant="secondary" type="button" @click="isReplyModalOpen = false">
                            Fechar
                        </Button>
                    </div>
                </div>

                <!-- Campo de Resposta (Se pendente) -->
                <form v-else @submit.prevent="submitReply" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-450">Sua Resposta Técnica</label>
                        <textarea
                            v-model="replyForm.reply"
                            required
                            rows="5"
                            placeholder="Escreva aqui a solução, instrução ou retorno para o usuário..."
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-zinc-850">
                        <Button variant="secondary" type="button" @click="isReplyModalOpen = false">
                            Cancelar
                        </Button>
                        <Button type="submit" :disabled="replyForm.processing" class="bg-indigo-600 hover:bg-indigo-500 font-bold">
                            {{ replyForm.processing ? 'Respondendo...' : 'Enviar Resposta' }}
                        </Button>
                    </div>
                </form>
            </div>
        </BaseModal>
    </AuthenticatedLayout>
</template>
