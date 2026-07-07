<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    href: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'primary', // 'primary' | 'secondary' | 'danger' | 'text' | 'icon'
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    // Estado de carregamento: bloqueia o botão e exibe um spinner + texto.
    loading: {
        type: Boolean,
        default: false,
    },
    loadingText: {
        type: String,
        default: '',
    },
    title: {
        type: String,
        default: '',
    },
});

const isLink = computed(() => !!props.href);
const isDisabled = computed(() => props.disabled || props.loading);

const classes = computed(() => {
    const base =
        'inline-flex items-center justify-center gap-1.5 rounded-xl font-semibold transition-all duration-200 focus:outline-none disabled:opacity-50 disabled:pointer-events-none';

    const variants = {
        primary:
            'bg-indigo-600 px-4 py-2.5 text-sm text-white hover:bg-indigo-500 hover:shadow-lg hover:shadow-indigo-600/20 active:scale-98',
        secondary:
            'border border-zinc-800 bg-zinc-950/40 px-4 py-2.5 text-sm text-zinc-300 hover:bg-zinc-800 hover:text-zinc-200 active:scale-98',
        danger: 'bg-rose-600 px-4 py-2.5 text-sm text-white hover:bg-rose-500 hover:shadow-lg hover:shadow-rose-600/20 active:scale-98',
        text: 'bg-transparent px-3 py-2 text-sm text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800/30',
        icon: 'border border-zinc-800 bg-zinc-900/50 p-2 text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200 rounded-lg',
    };

    return `${base} ${variants[props.variant] || variants.primary}`;
});
</script>

<template>
    <Link v-if="isLink" :href="href" :class="classes" :title="title">
        <slot name="icon" />
        <slot />
    </Link>
    <button
        v-else
        :type="type"
        :class="classes"
        :disabled="isDisabled"
        :title="title"
    >
        <svg
            v-if="loading"
            class="h-4 w-4 animate-spin"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            />
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
        </svg>
        <slot v-else name="icon" />
        <span v-if="loading && loadingText">{{ loadingText }}</span>
        <slot v-else />
    </button>
</template>
