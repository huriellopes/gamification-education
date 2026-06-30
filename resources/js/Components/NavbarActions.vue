<script setup>
/**
 * Controles do lado direito do topbar (header navbar):
 * - Botão "Ir para o site público"
 * - Switcher de instituição (somente admin com mais de uma instituição)
 * - Menu do usuário logado (perfil / sair)
 *
 * Centralizado num componente para manter o padrão e ser reutilizável.
 */
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { __ } from '@/i18n';
import { router, usePage } from '@inertiajs/vue3';
import { ArrowLeftRight, Settings } from '@lucide/vue';
import { computed } from 'vue';

const page = usePage();

const formatShortName = (name) => {
    if (!name) return '';
    const parts = name.trim().split(/\s+/);
    if (parts.length <= 2) return name;
    return `${parts[0]} ${parts[parts.length - 1]}`;
};

const uniqueUserInstitutions = computed(() => {
    const user = page.props.auth.user;
    if (!user || !user.institutions) return [];
    const seen = new Set();
    return user.institutions.filter((inst) => {
        if (!inst || seen.has(inst.id)) return false;
        seen.add(inst.id);
        return true;
    });
});

const switchInstitution = (id) => {
    router.post(route('admin.institutions.switch', id));
};
</script>

<template>
    <div class="flex items-center gap-3">
        <!-- Ir para o site público -->
        <a
            href="/"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center justify-center rounded-xl border border-zinc-700/50 bg-zinc-800 px-3 py-2.5 text-xs font-bold text-zinc-300 transition-all hover:bg-zinc-700 md:px-3.5 md:py-2"
            :title="__('nav.navbar.go_public_site_title')"
        >
            <span>🌐</span>
            <span class="ml-1.5 hidden md:inline">{{
                __('nav.navbar.go_public_site')
            }}</span>
        </a>

        <!-- Switcher de instituição (admin) -->
        <div
            v-if="
                $page.props.auth.user.role === 'admin' &&
                uniqueUserInstitutions.length > 1
            "
            class="relative"
        >
            <Dropdown align="right" width="60">
                <template #trigger>
                    <button
                        type="button"
                        class="border-zinc-850 inline-flex items-center justify-center gap-1.5 rounded-xl border bg-zinc-950 px-3 py-2.5 text-xs font-bold text-zinc-300 transition-all hover:bg-zinc-900 md:px-3.5 md:py-2"
                        :title="__('nav.navbar.switch_unit')"
                    >
                        <span class="hidden md:inline">{{
                            __('nav.navbar.switch_unit')
                        }}</span>
                        <ArrowLeftRight
                            class="h-3.5 w-3.5 shrink-0 text-zinc-500"
                        />
                    </button>
                </template>
                <template #content>
                    <button
                        v-for="inst in uniqueUserInstitutions"
                        :key="inst.id"
                        @click="switchInstitution(inst.id)"
                        class="block w-full px-4 py-2.5 text-start text-xs font-bold transition-colors"
                        :class="
                            inst.id === $page.props.auth.user.institution_id
                                ? 'bg-indigo-600 font-extrabold text-white'
                                : 'text-zinc-300 hover:bg-zinc-800'
                        "
                    >
                        {{ inst.name }}
                    </button>
                </template>
            </Dropdown>
        </div>

        <!-- Menu do usuário logado -->
        <Dropdown align="right" width="48">
            <template #trigger>
                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2.5 text-xs font-bold text-zinc-300 transition-all hover:bg-zinc-800 md:px-3.5 md:py-2"
                    :title="__('nav.navbar.settings_profile')"
                >
                    <span class="hidden md:inline">{{
                        formatShortName($page.props.auth.user.name)
                    }}</span>
                    <Settings class="h-3.5 w-3.5 shrink-0 text-zinc-500" />
                </button>
            </template>
            <template #content>
                <DropdownLink :href="route('profile.edit')">
                    {{ __('nav.navbar.my_profile') }}
                </DropdownLink>
                <DropdownLink :href="route('logout')" method="post" as="button">
                    {{ __('nav.navbar.logout') }}
                </DropdownLink>
            </template>
        </Dropdown>
    </div>
</template>
