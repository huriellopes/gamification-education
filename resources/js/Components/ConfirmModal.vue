<script setup>
import { __ } from '@/i18n';
import { LoaderCircle } from '@lucide/vue';
import { computed } from 'vue';
import BaseModal from './BaseModal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: () => __('misc.confirm.title'),
    },
    message: {
        type: String,
        default: () => __('misc.confirm.message'),
    },
    confirmText: {
        type: String,
        default: () => __('common.confirm'),
    },
    cancelText: {
        type: String,
        default: () => __('common.cancel'),
    },
    type: {
        type: String,
        default: 'danger', // 'danger', 'warning', 'info'
    },
    // Estado de processamento (ex.: form.processing do Inertia). Quando ativo,
    // o botão de confirmar exibe feedback e ambos os botões ficam desabilitados,
    // evitando duplo envio.
    processing: {
        type: Boolean,
        default: false,
    },
    processingText: {
        type: String,
        default: () => __('common.processing'),
    },
});

const emit = defineEmits(['close', 'confirm']);

const close = () => {
    if (props.processing) {
        return;
    }
    emit('close');
};

const confirm = () => {
    if (props.processing) {
        return;
    }
    emit('confirm');
};

const confirmLabel = computed(() =>
    props.processing ? props.processingText : props.confirmText,
);
</script>

<template>
    <BaseModal :show="show" :title="title" maxWidth="md" @close="close">
        <div class="space-y-4">
            <p class="text-sm text-zinc-400">
                {{ message }}
            </p>

            <div class="flex justify-end gap-3 pt-2">
                <button
                    type="button"
                    :disabled="processing"
                    @click="close"
                    class="rounded-xl border border-zinc-700 bg-transparent px-4 py-2.5 text-sm font-semibold text-zinc-300 transition-all hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ cancelText }}
                </button>
                <button
                    type="button"
                    :disabled="processing"
                    @click="confirm"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white transition-all disabled:cursor-not-allowed disabled:opacity-70"
                    :class="{
                        'bg-rose-600 hover:bg-rose-500': type === 'danger',
                        'bg-amber-600 hover:bg-amber-500': type === 'warning',
                        'bg-indigo-600 hover:bg-indigo-500': type === 'info',
                    }"
                >
                    <LoaderCircle
                        v-if="processing"
                        class="h-4 w-4 animate-spin"
                        aria-hidden="true"
                    />
                    {{ confirmLabel }}
                </button>
            </div>
        </div>
    </BaseModal>
</template>
