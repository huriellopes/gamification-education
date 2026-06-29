<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    required: true,
});

const select = ref(null);

onMounted(() => {
    if (select.value && select.value.hasAttribute('autofocus')) {
        select.value.focus();
    }
});

defineExpose({ focus: () => select.value?.focus() });
</script>

<template>
    <select
        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-indigo-500 dark:focus:ring-indigo-500"
        v-model="model"
        ref="select"
    >
        <slot />
    </select>
</template>
