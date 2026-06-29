<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const textarea = ref(null);

onMounted(() => {
    if (textarea.value && textarea.value.hasAttribute('autofocus')) {
        textarea.value.focus();
    }
});

defineExpose({ focus: () => textarea.value?.focus() });
</script>

<template>
    <textarea
        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-indigo-500 dark:focus:ring-indigo-500"
        v-model="model"
        ref="textarea"
    ></textarea>
</template>
