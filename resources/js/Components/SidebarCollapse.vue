<script setup>
/**
 * Container de colapso animado para as seções da sidebar.
 *
 * Usa o truque de `grid-template-rows: 0fr → 1fr`, que anima suavemente a
 * altura mesmo com conteúdo de altura automática (sem precisar calcular px).
 * O filho precisa de `overflow-hidden` para o `0fr` conseguir encolher — por
 * isso o `clip`. Quando a sidebar está no modo ícone (colapsada), passamos
 * `:clip="false"` para não recortar os tooltips que escapam para a direita.
 */
defineProps({
    open: { type: Boolean, default: true },
    clip: { type: Boolean, default: true },
});
</script>

<template>
    <div
        class="grid transition-[grid-template-rows] duration-300 ease-out"
        :class="open ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
    >
        <div :class="clip ? 'overflow-hidden' : 'overflow-visible'">
            <slot />
        </div>
    </div>
</template>
