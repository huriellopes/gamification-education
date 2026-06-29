<script setup>
import { HelpCircle } from '@lucide/vue';
import Tooltip from './Tooltip.vue';

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
        <HelpCircle
            class="hover:text-zinc-350 h-4 w-4 cursor-help text-zinc-500 transition-colors"
        />
    </Tooltip>
</template>
