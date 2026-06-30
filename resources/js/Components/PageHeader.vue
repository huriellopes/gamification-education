<script setup>
/**
 * Cabeçalho de página padrão (usado no slot #header do AuthenticatedLayout).
 * Centraliza o tamanho/estilo do título para manter consistência entre as
 * dashboards e demais páginas. Suporta título via prop ou slot, subtítulo
 * opcional e um slot de ações (botões, badges) alinhado à direita.
 */
defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});
</script>

<template>
    <div
        class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
    >
        <div class="flex min-w-0 items-center gap-3">
            <!-- Slot opcional à esquerda do título (ex.: link de voltar). -->
            <slot name="leading" />
            <div class="min-w-0">
                <h1
                    class="text-2xl font-bold leading-tight text-zinc-100 sm:text-3xl"
                >
                    <slot>{{ title }}</slot>
                </h1>
                <p
                    v-if="$slots.subtitle || subtitle"
                    class="mt-1 text-sm text-zinc-400"
                >
                    <slot name="subtitle">{{ subtitle }}</slot>
                </p>
            </div>
        </div>

        <div
            v-if="$slots.actions"
            class="flex w-full shrink-0 items-center gap-3 sm:w-auto"
        >
            <slot name="actions" />
        </div>
    </div>
</template>
