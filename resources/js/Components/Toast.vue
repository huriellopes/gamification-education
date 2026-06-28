<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle, XCircle, X } from '@lucide/vue';

const page = usePage();
const toasts = ref([]);

const addToast = (message, type = 'success') => {
    if (!message) return;
    const id = Date.now() + Math.random();
    toasts.value.push({ id, message, type });
    
    setTimeout(() => {
        removeToast(id);
    }, 4000);
};

const removeToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};

// Monitora mensagens flash do Inertia
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            addToast(flash.success, 'success');
            page.props.flash.success = null;
        }
        if (flash?.error) {
            addToast(flash.error, 'error');
            page.props.flash.error = null;
        }
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <div class="fixed right-4 top-4 z-50 flex w-full max-w-sm flex-col gap-3 sm:right-6 sm:top-6">
        <TransitionGroup
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto flex w-full items-center gap-3 overflow-hidden rounded-xl border p-4 shadow-2xl backdrop-blur-xl transition-all duration-300"
                :class="
                    toast.type === 'success'
                        ? 'border-emerald-500/30 bg-zinc-900/90 text-emerald-400'
                        : 'border-rose-500/30 bg-zinc-900/90 text-rose-400'
                "
            >
                <div class="flex-shrink-0">
                    <CheckCircle v-if="toast.type === 'success'" class="h-5 w-5 text-emerald-400" />
                    <XCircle v-else class="h-5 w-5 text-rose-400" />
                </div>
                
                <div class="flex-1 text-sm font-semibold text-zinc-100">
                    {{ toast.message }}
                </div>
                
                <button
                    type="button"
                    @click="removeToast(toast.id)"
                    class="inline-flex rounded-md p-1.5 text-zinc-400 hover:text-zinc-200 focus:outline-none"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
