<script setup>
/**
 * Cabeçalho de seção retrátil da sidebar.
 *
 * - Expandida: botão em caixa alta (ícone + rótulo) com chevron que gira,
 *   clicável para recolher/expandir os itens da seção (emite `toggle`).
 * - Colapsada (sidebar icon-only): vira um divisor sutil, já que não há
 *   rótulo nem espaço para o toggle — os itens ficam sempre visíveis.
 */
import { ChevronDown } from '@lucide/vue';

defineProps({
    label: { type: String, required: true },
    icon: { type: [Object, Function], default: null },
    collapsed: { type: Boolean, default: false },
    open: { type: Boolean, default: true },
});

defineEmits(['toggle']);
</script>

<template>
    <button
        v-if="!collapsed"
        type="button"
        class="group mt-4 flex w-full items-center gap-2 rounded-lg px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-zinc-500 transition-colors hover:text-zinc-300"
        :aria-expanded="open"
        @click="$emit('toggle')"
    >
        <component :is="icon" v-if="icon" class="h-3 w-3 shrink-0" />
        <span class="truncate">{{ label }}</span>
        <ChevronDown
            class="ml-auto h-3.5 w-3.5 shrink-0 transition-transform duration-200"
            :class="open ? '' : '-rotate-90'"
        />
    </button>
    <hr
        v-else
        class="mx-2 my-2 border-t border-zinc-800/70"
        aria-hidden="true"
    />
</template>
