<script setup>
import { computed } from 'vue';

const props = defineProps({
    points: {
        type: Number,
        required: true,
        default: 0,
    },
    size: {
        type: String,
        default: 'md', // 'sm', 'md', 'lg'
    },
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'px-2.5 py-1 text-xs';
        case 'lg':
            return 'px-5 py-2.5 text-lg';
        default:
            return 'px-4 py-2 text-sm';
    }
});
</script>

<template>
    <div
        class="inline-flex items-center gap-2 rounded-full border border-yellow-500/30 bg-gradient-to-r from-yellow-500/10 to-amber-500/20 font-bold text-yellow-400 shadow-[0_0_15px_rgba(234,179,8,0.1)] transition-all duration-300 hover:scale-105 hover:border-yellow-400/50 hover:shadow-[0_0_20px_rgba(234,179,8,0.2)]"
        :class="sizeClasses"
    >
        <!-- Ícone Estrela com Animação sutil de pulso -->
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="h-5 w-5 animate-pulse text-yellow-400"
            :class="{ 'h-4 w-4': size === 'sm', 'h-6 w-6': size === 'lg' }"
        >
            <path
                fill-rule="evenodd"
                d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.321 21.28c-1.002.607-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"
                clip-rule="evenodd"
            />
        </svg>
        <span
            >{{ points.toLocaleString() }}
            <span
                class="text-xs font-medium uppercase tracking-wider text-yellow-500/70"
                >PTS</span
            ></span
        >
    </div>
</template>
