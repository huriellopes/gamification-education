<script setup>
import { __ } from '@/i18n';
import BaseModal from './BaseModal.vue';

defineProps({
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
});

const emit = defineEmits(['close', 'confirm']);
</script>

<template>
    <BaseModal :show="show" :title="title" maxWidth="md" @close="emit('close')">
        <div class="space-y-4">
            <p class="text-sm text-zinc-400">
                {{ message }}
            </p>

            <div class="flex justify-end gap-3 pt-2">
                <button
                    type="button"
                    @click="emit('close')"
                    class="rounded-xl border border-zinc-700 bg-transparent px-4 py-2.5 text-sm font-semibold text-zinc-300 transition-all hover:bg-zinc-800"
                >
                    {{ cancelText }}
                </button>
                <button
                    type="button"
                    @click="emit('confirm')"
                    class="rounded-xl px-4 py-2.5 text-sm font-bold text-white transition-all"
                    :class="{
                        'bg-rose-600 hover:bg-rose-500': type === 'danger',
                        'bg-amber-600 hover:bg-amber-500': type === 'warning',
                        'bg-indigo-600 hover:bg-indigo-500': type === 'info',
                    }"
                >
                    {{ confirmText }}
                </button>
            </div>
        </div>
    </BaseModal>
</template>
