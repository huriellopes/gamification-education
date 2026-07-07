<script setup>
import { Eye, EyeOff } from '@lucide/vue';
import { computed, onMounted, ref, useAttrs } from 'vue';

// Repassamos os atributos manualmente para poder envolver o <input> num
// wrapper posicionado (necessário para o botão de mostrar/ocultar senha).
defineOptions({ inheritAttrs: false });

const model = defineModel({
    type: String,
    required: true,
});

const attrs = useAttrs();
const input = ref(null);
const showPassword = ref(false);

// Quando o campo é de senha, alternamos o type entre 'password' e 'text'
// para revelar/esconder o conteúdo ao clicar no ícone de olho.
const isPassword = computed(() => attrs.type === 'password');
const resolvedType = computed(() =>
    isPassword.value ? (showPassword.value ? 'text' : 'password') : attrs.type,
);

// Atributos repassados ao <input>, exceto `class` e `type`, que tratamos
// explicitamente abaixo.
const inputAttrs = computed(() => {
    // eslint-disable-next-line no-unused-vars
    const { class: _class, type: _type, ...rest } = attrs;
    return rest;
});

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <div class="relative w-full">
        <input
            ref="input"
            v-bind="inputAttrs"
            :type="resolvedType"
            v-model="model"
            :class="[
                'w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-indigo-500 dark:focus:ring-indigo-500',
                attrs.class,
                isPassword ? 'pr-10' : '',
            ]"
        />
        <button
            v-if="isPassword"
            type="button"
            tabindex="-1"
            @click="showPassword = !showPassword"
            :aria-label="
                showPassword
                    ? __('common.hide_password')
                    : __('common.show_password')
            "
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-400 transition-colors hover:text-zinc-200 focus:outline-none"
        >
            <component :is="showPassword ? EyeOff : Eye" class="h-5 w-5" />
        </button>
    </div>
</template>
