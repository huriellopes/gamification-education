<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    color: {
        type: String,
        default: 'text-indigo-500',
    },
});
</script>

<template>
    <div
        class="relative flex flex-col items-center justify-center overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 text-center shadow-xl backdrop-blur-md"
    >
        <!-- Icon slot for decorative background icon -->
        <div class="absolute right-4 top-4 text-white opacity-5">
            <slot name="icon" />
        </div>

        <slot name="icon-header" />

        <h3 class="mt-2 text-3xl font-extrabold" :class="color">
            {{ value }}
        </h3>

        <p
            class="mt-1 text-xs font-bold uppercase tracking-wider text-zinc-400"
        >
            {{ title }}
        </p>
    </div>
</template>
