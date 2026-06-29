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
    disabled: {
        type: Boolean,
        default: false,
    },
    block: {
        type: Boolean,
        default: false,
    },
});

const isHovered = ref(false);
</script>

<template>
    <div
        :class="['relative', block ? 'block w-full' : 'inline-block']"
        @mouseenter="isHovered = true"
        @mouseleave="isHovered = false"
    >
        <slot />
        <div
            class="border-zinc-850 text-zinc-150 pointer-events-none absolute z-50 whitespace-nowrap rounded-lg border bg-zinc-950 px-2.5 py-1.5 text-xs font-semibold shadow-2xl backdrop-blur-md transition-all duration-150 ease-out"
            :class="[
                isHovered && !disabled
                    ? 'visible scale-100 opacity-100'
                    : 'invisible scale-95 opacity-0',
                {
                    'bottom-full left-1/2 mb-2 -translate-x-1/2':
                        position === 'top',
                    'left-1/2 top-full mt-2 -translate-x-1/2':
                        position === 'bottom',
                    'right-full top-1/2 mr-2 -translate-y-1/2':
                        position === 'left',
                    'left-full top-1/2 ml-2 -translate-y-1/2':
                        position === 'right',
                },
            ]"
        >
            {{ text }}
        </div>
    </div>
</template>
