<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import Button from '@/Components/Button.vue';
import DataTable from '@/Components/DataTable.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { CheckCircle, Eye, HelpCircle } from '@lucide/vue';
import { ref } from 'vue';

defineProps({
    supports: {
        type: Array,
        default: () => [],
    },
});

const supportColumns = [
    { key: 'id', label: 'ID', sortable: true },
    {
        key: 'user_name',
        label: __('superadmin.supports.col_requester'),
        sortable: true,
    },
    {
        key: 'institution_name',
        label: __('superadmin.supports.col_institution'),
        sortable: true,
    },
    {
        key: 'subject',
        label: __('superadmin.supports.col_subject'),
        sortable: true,
    },
    {
        key: 'status',
        label: __('common.status'),
        sortable: true,
        align: 'center',
    },
    {
        key: 'created_at',
        label: __('superadmin.supports.col_created_at'),
        sortable: true,
    },
    { key: 'actions', label: __('common.actions'), align: 'right' },
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
    replyForm.post(
        route('super-admin.supports.reply', selectedSupport.value.id),
        {
            onSuccess: () => {
                isReplyModalOpen.value = false;
                replyForm.reset();
                selectedSupport.value = null;
            },
        },
    );
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return '';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return __('superadmin.supports.invalid_date');
        return d.toLocaleString('pt-BR');
    } catch (e) {
        return __('superadmin.supports.invalid_date');
    }
};
</script>

<template>
    <Head :title="__('superadmin.supports.head_title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-zinc-100">
                {{ __('superadmin.supports.header') }}
            </h2>
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100">
            <div
                class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
            >
                <DataTable
                    :items="supports"
                    :columns="supportColumns"
                    :searchPlaceholder="
                        __('superadmin.supports.search_placeholder')
                    "
                >
                    <template #id="{ item }">
                        <span class="font-mono text-zinc-500"
                            >#{{ item.id }}</span
                        >
                    </template>
                    <template #user_name="{ item }">
                        <div class="text-zinc-150 font-semibold">
                            {{ item.user_name }}
                        </div>
                        <div class="text-[10px] text-zinc-500">
                            {{ item.user_email }}
                        </div>
                    </template>
                    <template #institution_name="{ item }">
                        <span class="text-xs font-semibold text-indigo-400">{{
                            item.institution_name ||
                            __('superadmin.supports.na')
                        }}</span>
                    </template>
                    <template #subject="{ item }">
                        <div class="font-medium text-zinc-200">
                            {{ item.subject }}
                        </div>
                        <div
                            class="max-w-xs truncate text-[10px] text-zinc-500"
                        >
                            {{ item.message }}
                        </div>
                    </template>
                    <template #status="{ item }">
                        <span
                            v-if="item.status === 'answered'"
                            class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-400"
                        >
                            <CheckCircle class="h-3 w-3" />
                            {{ __('superadmin.supports.answered') }}
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-bold text-amber-400"
                        >
                            <HelpCircle class="h-3 w-3" />
                            {{ __('superadmin.supports.pending') }}
                        </span>
                    </template>
                    <template #created_at="{ item }">
                        <span class="text-zinc-450 font-mono text-xs">
                            {{ formatDateTime(item.created_at) }}
                        </span>
                    </template>
                    <template #actions="{ item }">
                        <div class="flex justify-end">
                            <Button
                                v-if="item.status === 'answered'"
                                size="sm"
                                variant="secondary"
                                class="flex items-center gap-1 bg-zinc-800 text-zinc-300 hover:bg-zinc-700"
                                @click="openReplyModal(item)"
                                :aria-label="
                                    __('superadmin.supports.aria_view_answered')
                                "
                            >
                                <Eye class="h-3.5 w-3.5" />
                                <span>{{
                                    __('superadmin.supports.view')
                                }}</span>
                            </Button>
                            <Button
                                v-else
                                size="sm"
                                class="bg-indigo-600 font-bold text-white hover:bg-indigo-500"
                                @click="openReplyModal(item)"
                                :aria-label="
                                    __('superadmin.supports.aria_reply_pending')
                                "
                            >
                                {{ __('superadmin.supports.reply') }}
                            </Button>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

        <!-- Base Modal: Detalhes do Chamado e Resposta -->
        <BaseModal
            :show="isReplyModalOpen && !!selectedSupport"
            :title="
                selectedSupport?.status === 'answered'
                    ? __('superadmin.supports.modal_view_title')
                    : __('superadmin.supports.modal_reply_title')
            "
            maxWidth="3xl"
            @close="isReplyModalOpen = false"
        >
            <div class="space-y-6" v-if="selectedSupport">
                <!-- Informações Originais -->
                <div
                    class="space-y-3 rounded-xl border border-zinc-800 bg-zinc-950 p-4"
                >
                    <div
                        class="border-zinc-850 flex flex-col gap-1 border-b pb-2 text-xs font-semibold text-zinc-400 sm:flex-row sm:justify-between"
                    >
                        <div>
                            {{ __('superadmin.supports.requester_label') }}
                            <span class="text-zinc-250 font-bold">{{
                                selectedSupport.user_name
                            }}</span>
                            ({{ selectedSupport.user_email }})
                        </div>
                        <div>
                            {{ __('superadmin.supports.unit_label') }}
                            <span class="font-bold text-indigo-400">{{
                                selectedSupport.institution_name ||
                                __('superadmin.supports.general')
                            }}</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-zinc-450 text-xs font-bold uppercase">
                            {{ __('superadmin.supports.subject_label') }}
                        </h4>
                        <p class="text-sm font-semibold text-white">
                            {{ selectedSupport.subject }}
                        </p>
                    </div>
                    <div>
                        <h4 class="text-zinc-450 text-xs font-bold uppercase">
                            {{ __('superadmin.supports.user_message_label') }}
                        </h4>
                        <p class="whitespace-pre-line text-sm text-zinc-300">
                            {{ selectedSupport.message }}
                        </p>
                    </div>
                    <div class="text-zinc-550 font-mono text-[10px]">
                        {{ __('superadmin.supports.created_at_label') }}
                        {{ formatDateTime(selectedSupport.created_at) }}
                    </div>
                </div>

                <!-- Se o chamado já está respondido -->
                <div
                    v-if="selectedSupport.status === 'answered'"
                    class="space-y-4"
                >
                    <div
                        class="space-y-2 rounded-xl border border-emerald-500/20 bg-emerald-500/5 p-4"
                    >
                        <h4
                            class="flex items-center gap-1.5 text-xs font-bold uppercase text-emerald-400"
                        >
                            <CheckCircle class="h-3.5 w-3.5" />
                            {{ __('superadmin.supports.reply_sent_label') }}
                        </h4>
                        <p class="whitespace-pre-line text-sm text-zinc-200">
                            {{ selectedSupport.reply }}
                        </p>
                        <div
                            class="border-t border-emerald-500/10 pt-2 font-mono text-[10px] text-zinc-500"
                        >
                            {{ __('superadmin.supports.replied_at_label') }}
                            {{ formatDateTime(selectedSupport.replied_at) }}
                        </div>
                    </div>

                    <div class="border-zinc-850 flex justify-end border-t pt-4">
                        <Button
                            variant="secondary"
                            type="button"
                            @click="isReplyModalOpen = false"
                        >
                            {{ __('superadmin.supports.close') }}
                        </Button>
                    </div>
                </div>

                <!-- Campo de Resposta (Se pendente) -->
                <form v-else @submit.prevent="submitReply" class="space-y-4">
                    <div>
                        <label
                            class="text-zinc-450 mb-2 block text-xs font-bold uppercase"
                            >{{
                                __('superadmin.supports.your_reply_label')
                            }}</label
                        >
                        <textarea
                            v-model="replyForm.reply"
                            required
                            rows="5"
                            :placeholder="
                                __('superadmin.supports.reply_placeholder')
                            "
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div
                        class="border-zinc-850 flex justify-end gap-3 border-t pt-4"
                    >
                        <Button
                            variant="secondary"
                            type="button"
                            @click="isReplyModalOpen = false"
                        >
                            {{ __('common.cancel') }}
                        </Button>
                        <Button
                            type="submit"
                            :disabled="replyForm.processing"
                            class="bg-indigo-600 font-bold hover:bg-indigo-500"
                        >
                            {{
                                replyForm.processing
                                    ? __('superadmin.supports.replying')
                                    : __('superadmin.supports.send_reply')
                            }}
                        </Button>
                    </div>
                </form>
            </div>
        </BaseModal>
    </AuthenticatedLayout>
</template>
