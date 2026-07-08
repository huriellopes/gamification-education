<script setup>
import { computed, ref } from 'vue';

/**
 * Gráfico de linha/área em SVG reutilizável.
 *
 * Extraído dos dashboards (Super Admin e Admin), que repetiam a mesma marcação
 * de ~150 linhas. A geometria (viewBox 600x240, grade e escala) é preservada
 * exatamente para manter a fidelidade visual.
 */
const props = defineProps({
    // Lista de pontos: cada item tem `day` (rótulo do eixo X) e a chave de valor.
    data: {
        type: Array,
        default: () => [],
    },
    // Nome da propriedade numérica em cada item (ex.: 'points', 'visits').
    valueKey: {
        type: String,
        default: 'value',
    },
    // Cor (hex) usada no traço, nos pontos e no gradiente da área.
    color: {
        type: String,
        default: '#4f46e5',
    },
    // Piso para o topo da escala, evitando divisão por zero em séries vazias.
    floor: {
        type: Number,
        default: 100,
    },
    // Identificador único do gradiente (evita colisão quando há vários no mesmo DOM).
    gradientId: {
        type: String,
        required: true,
    },
    // Variante de cor do tooltip flutuante (classes estáticas para o Tailwind).
    variant: {
        type: String,
        default: 'indigo',
    },
    tooltipLabel: {
        type: String,
        default: '',
    },
    valueSuffix: {
        type: String,
        default: '',
    },
});

const TOOLTIP_CLASSES = {
    indigo: { ring: 'border-indigo-500/30', text: 'text-indigo-300' },
    emerald: { ring: 'border-emerald-500/30', text: 'text-emerald-400' },
};

const tooltipClasses = computed(
    () => TOOLTIP_CLASSES[props.variant] ?? TOOLTIP_CLASSES.indigo,
);

const maxValue = computed(() => {
    const vals = props.data.map((d) => Number(d[props.valueKey]) || 0);
    return Math.max(...vals, props.floor);
});

const coords = computed(() =>
    props.data.map((d, idx) => {
        const value = Number(d[props.valueKey]) || 0;
        const x = 50 + idx * (520 / 6);
        const y = 210 - (value / maxValue.value) * 180;
        return { x, y, value };
    }),
);

const linePath = computed(() => {
    const pts = coords.value;
    if (pts.length === 0) return '';
    return pts.reduce(
        (acc, curr, idx) =>
            idx === 0 ? `M ${curr.x} ${curr.y}` : `${acc} L ${curr.x} ${curr.y}`,
        '',
    );
});

const areaPath = computed(() => {
    const pts = coords.value;
    if (pts.length === 0) return '';
    const firstX = pts[0].x;
    const lastX = pts[pts.length - 1].x;
    return `${linePath.value} L ${lastX} 210 L ${firstX} 210 Z`;
});

const activeTooltip = ref(null);
const showTooltip = (pt) => {
    activeTooltip.value = {
        left: `${(pt.x / 600) * 100}%`,
        top: `${(pt.y / 240) * 100}%`,
        value: `${pt.value} ${props.valueSuffix}`.trim(),
    };
};
const hideTooltip = () => {
    activeTooltip.value = null;
};
</script>

<template>
    <div class="relative h-64 w-full">
        <svg class="h-full w-full" viewBox="0 0 600 240" preserveAspectRatio="none">
            <defs>
                <linearGradient :id="gradientId" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" :stop-color="color" stop-opacity="0.4" />
                    <stop offset="100%" :stop-color="color" stop-opacity="0" />
                </linearGradient>
            </defs>

            <line x1="50" y1="30" x2="570" y2="30" stroke="#27272a" stroke-dasharray="3" />
            <line x1="50" y1="90" x2="570" y2="90" stroke="#27272a" stroke-dasharray="3" />
            <line x1="50" y1="150" x2="570" y2="150" stroke="#27272a" stroke-dasharray="3" />
            <line x1="50" y1="210" x2="570" y2="210" stroke="#27272a" />

            <text x="15" y="34" fill="#71717a" class="font-mono text-[10px] font-bold">
                {{ Math.round(maxValue) }}
            </text>
            <text x="15" y="124" fill="#71717a" class="font-mono text-[10px] font-bold">
                {{ Math.round(maxValue / 2) }}
            </text>
            <text x="15" y="214" fill="#71717a" class="font-mono text-[10px] font-bold">
                0
            </text>

            <path :d="areaPath" :fill="`url(#${gradientId})`" />
            <path
                :d="linePath"
                fill="none"
                :stroke="color"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
            />

            <g v-for="(pt, idx) in coords" :key="idx">
                <circle
                    :cx="pt.x"
                    :cy="pt.y"
                    r="4.5"
                    :fill="color"
                    stroke="#18181b"
                    stroke-width="2"
                    class="hover:r-6 cursor-pointer transition-all duration-150 hover:fill-white"
                    @mouseenter="showTooltip(pt)"
                    @mouseleave="hideTooltip"
                />
            </g>

            <text
                v-for="(d, idx) in data"
                :key="`label-${idx}`"
                :x="50 + idx * (520 / 6)"
                y="235"
                text-anchor="middle"
                fill="#71717a"
                class="font-mono text-[10px] font-bold"
            >
                {{ d.day }}
            </text>
        </svg>

        <!-- Tooltip flutuante em HTML (sem distorção do preserveAspectRatio) -->
        <div
            v-if="activeTooltip"
            class="pointer-events-none absolute z-30 -translate-x-1/2 -translate-y-[calc(100%+12px)] rounded-xl border bg-zinc-950/95 px-2.5 py-1.5 text-center shadow-xl backdrop-blur-md transition-all duration-150"
            :class="tooltipClasses.ring"
            :style="{ left: activeTooltip.left, top: activeTooltip.top }"
        >
            <div
                class="text-zinc-450 text-[9px] font-bold uppercase tracking-wider"
            >
                {{ tooltipLabel }}
            </div>
            <div
                class="mt-0.5 font-mono text-xs font-extrabold"
                :class="tooltipClasses.text"
            >
                {{ activeTooltip.value }}
            </div>
        </div>
    </div>
</template>
