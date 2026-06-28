<script setup>
import { ref } from 'vue';

defineProps({
    text: {
        type: String,
        required: true,
    },
    position: {
        type: String,
        default: 'top', // 'top' | 'bottom' | 'left' | 'right'
    },
});

const isHovered = ref(false);
</script>

<template>
    <div
        class="relative inline-block"
        @mouseenter="isHovered = true"
        @mouseleave="isHovered = false"
    >
        <slot />
        <div
            class="pointer-events-none absolute z-50 transition-all duration-150 ease-out whitespace-nowrap rounded-lg border border-zinc-800 bg-zinc-950 px-2.5 py-1.5 text-xs font-semibold text-zinc-150 shadow-2xl backdrop-blur-md"
            :class="[
                isHovered ? 'scale-100 opacity-100 visible' : 'scale-95 opacity-0 invisible',
                {
                    'bottom-full left-1/2 -translate-x-1/2 mb-2': position === 'top',
                    'top-full left-1/2 -translate-x-1/2 mt-2': position === 'bottom',
                    'right-full top-1/2 -translate-y-1/2 mr-2': position === 'left',
                    'left-full top-1/2 -translate-y-1/2 ml-2': position === 'right',
                }
            ]"
        >
            {{ text }}
        </div>
    </div>
</template>
