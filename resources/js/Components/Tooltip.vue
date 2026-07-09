<script setup>
import { nextTick, ref } from 'vue';

const props = defineProps({
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
const wrapper = ref(null);
const floatingStyle = ref({});

// Transform de ancoragem por posição (o ponto calculado é a borda do gatilho).
const TRANSFORMS = {
    top: 'translate(-50%, -100%)',
    bottom: 'translate(-50%, 0)',
    left: 'translate(-100%, -50%)',
    right: 'translate(0, -50%)',
};

// Posiciona o tooltip via coordenadas de viewport (position: fixed) para que
// ele nunca seja recortado por um ancestral com overflow (ex.: a sidebar
// rolável no modo colapsado). Por isso usamos Teleport para o body.
const updatePosition = () => {
    const el = wrapper.value;
    if (!el) {
        return;
    }

    const r = el.getBoundingClientRect();
    const gap = 8;
    let top;
    let left;

    switch (props.position) {
        case 'bottom':
            top = r.bottom + gap;
            left = r.left + r.width / 2;
            break;
        case 'left':
            top = r.top + r.height / 2;
            left = r.left - gap;
            break;
        case 'right':
            top = r.top + r.height / 2;
            left = r.right + gap;
            break;
        default: // top
            top = r.top - gap;
            left = r.left + r.width / 2;
    }

    floatingStyle.value = {
        top: `${top}px`,
        left: `${left}px`,
        transform: TRANSFORMS[props.position] ?? TRANSFORMS.top,
    };
};

const show = async () => {
    if (props.disabled) {
        return;
    }
    isHovered.value = true;
    await nextTick();
    updatePosition();
};

const hide = () => {
    isHovered.value = false;
};
</script>

<template>
    <div
        ref="wrapper"
        :class="['relative', block ? 'block w-full' : 'inline-block']"
        @mouseenter="show"
        @mouseleave="hide"
    >
        <slot />
        <Teleport to="body">
            <div
                v-if="isHovered && !disabled"
                class="text-zinc-150 pointer-events-none fixed z-[100] whitespace-nowrap rounded-lg border border-zinc-850 bg-zinc-950 px-2.5 py-1.5 text-xs font-semibold shadow-2xl backdrop-blur-md"
                :style="floatingStyle"
            >
                {{ text }}
            </div>
        </Teleport>
    </div>
</template>
