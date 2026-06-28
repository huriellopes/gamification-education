<script setup>
import Tooltip from './Tooltip.vue';
import { HelpCircle } from '@lucide/vue';

defineProps({
    text: {
        type: String,
        required: true,
    },
    position: {
        type: String,
        default: 'top',
    },
});
</script>

<template>
    <Tooltip :text="text" :position="position">
        <HelpCircle class="h-4 w-4 text-zinc-500 hover:text-zinc-350 cursor-help transition-colors" />
    </Tooltip>
</template>
