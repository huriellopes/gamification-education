<script setup>
/**
 * Card de troca de instituição ativa para uso no corpo de um dashboard.
 * Reaproveita o mesmo mecanismo do switcher do navbar (NavbarActions), mas
 * num formato visível/destacado. Aparece apenas quando o usuário está
 * vinculado a mais de uma instituição. Funciona para admin e professor.
 */
import Dropdown from '@/Components/Dropdown.vue';
import { __ } from '@/i18n';
import { router, usePage } from '@inertiajs/vue3';
import { ArrowLeftRight, Building2, Check } from '@lucide/vue';
import { computed } from 'vue';

const page = usePage();

const institutions = computed(() => {
    const user = page.props.auth.user;
    if (!user || !user.institutions) return [];
    const seen = new Set();
    return user.institutions.filter((inst) => {
        if (!inst || seen.has(inst.id)) return false;
        seen.add(inst.id);
        return true;
    });
});

const currentId = computed(() => page.props.auth.user?.institution_id);
const currentName = computed(
    () =>
        institutions.value.find((inst) => inst.id === currentId.value)?.name ??
        '',
);

const switchInstitution = (id) => {
    if (id === currentId.value) return;
    const routeName =
        page.props.auth.user.role === 'teacher'
            ? 'teacher.institutions.switch'
            : 'admin.institutions.switch';
    router.post(route(routeName, id));
};
</script>

<template>
    <div
        v-if="institutions.length > 1"
        class="flex flex-col gap-4 rounded-2xl border border-zinc-800 bg-zinc-900/40 p-5 sm:flex-row sm:items-center sm:justify-between"
    >
        <div class="flex items-center gap-3">
            <span
                class="inline-flex shrink-0 rounded-xl border border-indigo-500/20 bg-indigo-500/10 p-2.5 text-indigo-400"
            >
                <Building2 class="h-5 w-5" />
            </span>
            <div>
                <p
                    class="text-[10px] font-bold uppercase tracking-wider text-zinc-400"
                >
                    {{ __('nav.navbar.active_institution') }}
                </p>
                <p class="text-sm font-bold text-white">{{ currentName }}</p>
            </div>
        </div>

        <Dropdown align="right" width="60">
            <template #trigger>
                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-xs font-bold text-zinc-200 transition-all hover:bg-zinc-800"
                >
                    <ArrowLeftRight class="h-4 w-4 shrink-0 text-zinc-400" />
                    <span>{{ __('nav.navbar.switch_unit') }}</span>
                </button>
            </template>
            <template #content>
                <button
                    v-for="inst in institutions"
                    :key="inst.id"
                    @click="switchInstitution(inst.id)"
                    class="flex w-full items-center justify-between gap-2 px-4 py-2.5 text-start text-xs font-bold transition-colors"
                    :class="
                        inst.id === currentId
                            ? 'bg-indigo-600 text-white'
                            : 'text-zinc-300 hover:bg-zinc-800'
                    "
                >
                    <span>{{ inst.name }}</span>
                    <Check
                        v-if="inst.id === currentId"
                        class="h-4 w-4 shrink-0"
                    />
                </button>
            </template>
        </Dropdown>
    </div>
</template>
