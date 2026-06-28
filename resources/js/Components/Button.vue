<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

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
    title: {
        type: String,
        default: '',
    },
});

const isLink = computed(() => !!props.href);

const classes = computed(() => {
    const base = 'inline-flex items-center justify-center gap-1.5 rounded-xl font-semibold transition-all duration-200 focus:outline-none disabled:opacity-50 disabled:pointer-events-none';
    
    const variants = {
        primary: 'bg-indigo-600 px-4 py-2.5 text-sm text-white hover:bg-indigo-500 hover:shadow-lg hover:shadow-indigo-600/20 active:scale-98',
        secondary: 'border border-zinc-800 bg-zinc-950/40 px-4 py-2.5 text-sm text-zinc-300 hover:bg-zinc-800 hover:text-zinc-200 active:scale-98',
        danger: 'bg-rose-600 px-4 py-2.5 text-sm text-white hover:bg-rose-500 hover:shadow-lg hover:shadow-rose-600/20 active:scale-98',
        text: 'bg-transparent px-3 py-2 text-sm text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800/30',
        icon: 'border border-zinc-800 bg-zinc-900/50 p-2 text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200 rounded-lg',
    };

    return `${base} ${variants[props.variant] || variants.primary}`;
});
</script>

<template>
    <Link
        v-if="isLink"
        :href="href"
        :class="classes"
        :title="title"
    >
        <slot name="icon" />
        <slot />
    </Link>
    <button
        v-else
        :type="type"
        :class="classes"
        :disabled="disabled"
        :title="title"
    >
        <slot name="icon" />
        <slot />
    </button>
</template>
