<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Toast from '@/Components/Toast.vue';
import Button from '@/Components/Button.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    Home, 
    BookOpen, 
    Users, 
    Trophy, 
    HelpCircle, 
    Settings, 
    LogOut, 
    ChevronLeft, 
    ChevronRight, 
    Menu, 
    X, 
    GraduationCap, 
    ArrowLeftRight, 
    Terminal, 
    School,
    FileText,
    Eye,
    Trash2
} from '@lucide/vue';

const page = usePage();
const showingNavigationDropdown = ref(false);
const isSidebarCollapsed = ref(typeof window !== 'undefined' && localStorage.getItem('sidebar_collapsed') === 'true');

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    if (typeof window !== 'undefined') {
        localStorage.setItem('sidebar_collapsed', isSidebarCollapsed.value ? 'true' : 'false');
    }
};

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
    return user.institutions.filter(inst => {
        if (!inst || seen.has(inst.id)) return false;
        seen.add(inst.id);
        return true;
    });
});

const switchInstitution = (id) => {
    router.post(route('admin.institutions.switch', id));
};

const breadcrumbs = computed(() => {
    const list = [{ label: 'Início', href: route('dashboard') }];
    
    if (route().current('super-admin.dashboard')) {
        list.push({ label: 'Super Admin', href: null });
        list.push({ label: 'Painel Geral', href: null });
    } else if (route().current('super-admin.institutions.index')) {
        list.push({ label: 'Super Admin', href: route('super-admin.dashboard') });
        list.push({ label: 'Instituições', href: null });
    } else if (route().current('super-admin.users.index')) {
        list.push({ label: 'Super Admin', href: route('super-admin.dashboard') });
        list.push({ label: 'Usuários', href: null });
    } else if (route().current('super-admin.subjects.index')) {
        list.push({ label: 'Super Admin', href: route('super-admin.dashboard') });
        list.push({ label: 'Matérias', href: null });
    } else if (route().current('super-admin.trash.index')) {
        list.push({ label: 'Super Admin', href: route('super-admin.dashboard') });
        list.push({ label: 'Lixeira', href: null });
    } else if (route().current('super-admin.reports.index')) {
        list.push({ label: 'Super Admin', href: route('super-admin.dashboard') });
        list.push({ label: 'Relatórios', href: null });
    } else if (route().current('super-admin.logs.index')) {
        list.push({ label: 'Super Admin', href: route('super-admin.dashboard') });
        list.push({ label: 'Logs e Fila', href: null });
    } else if (route().current('super-admin.supports.index')) {
        list.push({ label: 'Super Admin', href: route('super-admin.dashboard') });
        list.push({ label: 'Suporte', href: null });
    } else if (route().current('super-admin.visits.index')) {
        list.push({ label: 'Super Admin', href: route('super-admin.dashboard') });
        list.push({ label: 'Visitas do Site', href: null });
    } else if (route().current('admin.dashboard')) {
        list.push({ label: 'Admin', href: null });
        list.push({ label: 'Painel da Instituição', href: null });
    } else if (route().current('admin.subjects.index')) {
        list.push({ label: 'Admin', href: route('dashboard') });
        list.push({ label: 'Matérias', href: null });
    } else if (route().current('admin.users.index')) {
        list.push({ label: 'Admin', href: route('dashboard') });
        list.push({ label: 'Membros', href: null });
    } else if (route().current('teacher.dashboard')) {
        list.push({ label: 'Professor', href: null });
        list.push({ label: 'Minhas Matérias', href: null });
    } else if (route().current('student.dashboard')) {
        list.push({ label: 'Estudante', href: null });
        list.push({ label: 'Meu Painel', href: null });
    } else if (route().current('ranking.index')) {
        list.push({ label: 'Competição', href: null });
        list.push({ label: 'Ranking Global', href: null });
    } else if (route().current('support.index')) {
        list.push({ label: 'Suporte', href: null });
        list.push({ label: 'Suporte Técnico', href: null });
    } else if (route().current('profile.edit')) {
        list.push({ label: 'Usuário', href: null });
        list.push({ label: 'Configurações de Perfil', href: null });
    } else {
        list.push({ label: 'Dashboard', href: null });
    }
    
    return list;
});
</script>

<template>
    <div>
        <Toast />
        <!-- Impersonation Banner -->
        <div
            v-if="$page.props.auth.is_impersonating"
            class="bg-indigo-600 px-4 py-3 text-white sm:flex sm:items-center sm:justify-between sm:px-6 lg:px-8"
            role="banner"
        >
            <p class="text-center text-sm font-medium sm:text-left">
                Você está personificando a conta de
                <strong>{{ $page.props.auth.user.name }}</strong> ({{
                    $page.props.auth.user.email
                }}).
            </p>
            <div class="mt-4 flex justify-center sm:mt-0 sm:shrink-0">
                <Link
                    :href="route('impersonate.leave')"
                    method="post"
                    as="button"
                    class="flex items-center justify-center rounded-md border border-transparent bg-white px-4 py-2 text-sm font-medium text-indigo-600 shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-indigo-600"
                >
                    Voltar ao Super Admin
                </Link>
            </div>
        </div>

        <div class="min-h-screen bg-zinc-950 text-zinc-100 flex relative overflow-hidden">
            <!-- Left Sidebar (Desktop Only) -->
            <aside
                :class="isSidebarCollapsed ? 'w-20 overflow-visible' : 'w-64'"
                class="hidden md:flex flex-col border-r border-zinc-800/80 bg-zinc-900/60 backdrop-blur-xl transition-all duration-300 select-none shrink-0 no-scrollbar"
                aria-label="Menu de Navegação Principal"
                role="complementary"
            >
                <!-- Sidebar Header / Logo -->
                <div class="h-16 border-b border-zinc-800/80 flex items-center px-4 justify-between">
                    <!-- Collapsed Version (Clicking expands sidebar) -->
                    <div 
                        v-if="isSidebarCollapsed" 
                        @click="toggleSidebar"
                        class="flex items-center gap-1.5 px-1.5 py-1 rounded-xl hover:bg-zinc-800/50 cursor-pointer transition-all w-full justify-center"
                        title="Expandir barra lateral"
                    >
                        <div class="bg-indigo-600 p-1.5 rounded-lg text-white shrink-0">
                            <GraduationCap class="h-5 w-5" />
                        </div>
                        <ChevronRight class="h-3.5 w-3.5 text-zinc-400 shrink-0" />
                    </div>

                    <!-- Expanded Version -->
                    <template v-else>
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="bg-indigo-600 p-1.5 rounded-lg text-white shrink-0">
                                <GraduationCap class="h-5 w-5" />
                            </div>
                            <span class="font-extrabold text-sm text-white tracking-wider truncate">GamificaEdu</span>
                        </div>

                        <button
                            @click="toggleSidebar"
                            class="p-1 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors shrink-0"
                            aria-label="Recolher barra lateral"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </button>
                    </template>
                </div>

                <nav 
                    :class="isSidebarCollapsed ? 'overflow-visible' : 'overflow-y-auto'"
                    class="flex-1 py-4 px-3 space-y-1.5 no-scrollbar" 
                    aria-label="Navegação Lateral"
                >
                    <!-- Link Dashboard -->
                    <Tooltip text="Dashboard" position="right" :disabled="!isSidebarCollapsed" block>
                        <Link
                            :href="route('dashboard')"
                            :class="[
                                route().current('dashboard') || (route().current('super-admin.dashboard') && !route().current('super-admin.institutions.*') && !route().current('super-admin.users.*')) 
                                    ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10' 
                                    : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                isSidebarCollapsed ? 'justify-center px-0' : 'justify-start px-3 gap-3'
                            ]"
                            class="flex items-center py-2.5 rounded-xl text-xs font-bold transition-all w-full"
                            aria-label="Dashboard"
                        >
                            <Home class="h-4 w-4 shrink-0" />
                            <span v-if="!isSidebarCollapsed" class="truncate">Dashboard</span>
                        </Link>
                    </Tooltip>

                    <!-- SUPER ADMIN SPECIFIC PAGES -->
                    <template v-if="$page.props.auth.user.role === 'super_admin'">
                        <!-- Instituições -->
                        <Tooltip text="Instituições" position="right" :disabled="!isSidebarCollapsed" block>
                            <Link
                                :href="route('super-admin.institutions.index')"
                                :class="[
                                    route().current('super-admin.institutions.*') 
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10' 
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed ? 'justify-center px-0' : 'justify-start px-3 gap-3'
                                ]"
                                class="flex items-center py-2.5 rounded-xl text-xs font-bold transition-all w-full"
                                aria-label="Gerenciar Instituições"
                            >
                                <School class="h-4 w-4 shrink-0" />
                                <span v-if="!isSidebarCollapsed" class="truncate">Instituições</span>
                            </Link>
                        </Tooltip>

                        <!-- Usuários -->
                        <Tooltip text="Usuários" position="right" :disabled="!isSidebarCollapsed" block>
                            <Link
                                :href="route('super-admin.users.index')"
                                :class="[
                                    route().current('super-admin.users.*') 
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10' 
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed ? 'justify-center px-0' : 'justify-start px-3 gap-3'
                                ]"
                                class="flex items-center py-2.5 rounded-xl text-xs font-bold transition-all w-full"
                                aria-label="Gerenciar Usuários"
                            >
                                <Users class="h-4 w-4 shrink-0" />
                                <span v-if="!isSidebarCollapsed" class="truncate">Usuários</span>
                            </Link>
                        </Tooltip>

                        <!-- Matérias -->
                        <Tooltip text="Matérias" position="right" :disabled="!isSidebarCollapsed" block>
                            <Link
                                :href="route('super-admin.subjects.index')"
                                :class="[
                                    route().current('super-admin.subjects.*') 
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10' 
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed ? 'justify-center px-0' : 'justify-start px-3 gap-3'
                                ]"
                                class="flex items-center py-2.5 rounded-xl text-xs font-bold transition-all w-full"
                                aria-label="Gerenciar Matérias"
                            >
                                <BookOpen class="h-4 w-4 shrink-0" />
                                <span v-if="!isSidebarCollapsed" class="truncate">Matérias</span>
                            </Link>
                        </Tooltip>

                        <!-- Relatórios -->
                        <Tooltip text="Relatórios" position="right" :disabled="!isSidebarCollapsed" block>
                            <Link
                                :href="route('super-admin.reports.index')"
                                :class="[
                                    route().current('super-admin.reports.*') 
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10' 
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed ? 'justify-center px-0' : 'justify-start px-3 gap-3'
                                ]"
                                class="flex items-center py-2.5 rounded-xl text-xs font-bold transition-all w-full"
                                aria-label="Relatórios do Sistema"
                            >
                                <FileText class="h-4 w-4 shrink-0" />
                                <span v-if="!isSidebarCollapsed" class="truncate">Relatórios</span>
                            </Link>
                        </Tooltip>

                        <!-- Logs do Sistema -->
                        <Tooltip text="Logs e Fila" position="right" :disabled="!isSidebarCollapsed" block>
                            <Link
                                :href="route('super-admin.logs.index')"
                                :class="[
                                    route().current('super-admin.logs.*') 
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10' 
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed ? 'justify-center px-0' : 'justify-start px-3 gap-3'
                                ]"
                                class="flex items-center py-2.5 rounded-xl text-xs font-bold transition-all w-full"
                                aria-label="Logs e Filas do Sistema"
                            >
                                <Terminal class="h-4 w-4 shrink-0" />
                                <span v-if="!isSidebarCollapsed" class="truncate">Logs e Fila</span>
                            </Link>
                        </Tooltip>

                        <!-- Chamados -->
                        <Tooltip text="Chamados de Suporte" position="right" :disabled="!isSidebarCollapsed" block>
                            <Link
                                :href="route('super-admin.supports.index')"
                                :class="[
                                    route().current('super-admin.supports.*') 
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10' 
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed ? 'justify-center px-0' : 'justify-start px-3 gap-3'
                                ]"
                                class="flex items-center py-2.5 rounded-xl text-xs font-bold transition-all w-full"
                                aria-label="Suporte e Chamados"
                            >
                                <HelpCircle class="h-4 w-4 shrink-0" />
                                <span v-if="!isSidebarCollapsed" class="truncate">Suporte</span>
                            </Link>
                        </Tooltip>

                        <!-- Visitas do Site -->
                        <Tooltip text="Visitas do Site" position="right" :disabled="!isSidebarCollapsed" block>
                            <Link
                                :href="route('super-admin.visits.index')"
                                :class="[
                                    route().current('super-admin.visits.*') 
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10' 
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed ? 'justify-center px-0' : 'justify-start px-3 gap-3'
                                ]"
                                class="flex items-center py-2.5 rounded-xl text-xs font-bold transition-all w-full"
                                aria-label="Logs de Visitas do Site"
                            >
                                <Eye class="h-4 w-4 shrink-0" />
                                <span v-if="!isSidebarCollapsed" class="truncate">Visitas do Site</span>
                            </Link>
                        </Tooltip>

                        <!-- Lixeira -->
                        <Tooltip text="Lixeira" position="right" :disabled="!isSidebarCollapsed" block>
                            <Link
                                :href="route('super-admin.trash.index')"
                                :class="[
                                    route().current('super-admin.trash.*') 
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10' 
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed ? 'justify-center px-0' : 'justify-start px-3 gap-3'
                                ]"
                                class="flex items-center py-2.5 rounded-xl text-xs font-bold transition-all w-full"
                                aria-label="Lixeira do Sistema"
                            >
                                <Trash2 class="h-4 w-4 shrink-0" />
                                <span v-if="!isSidebarCollapsed" class="truncate">Lixeira</span>
                            </Link>
                        </Tooltip>
                    </template>

                    <!-- ADMIN SPECIFIC PAGES -->
                    <template v-if="$page.props.auth.user.role === 'admin'">
                        <!-- Matérias -->
                        <Tooltip text="Matérias" position="right" :disabled="!isSidebarCollapsed" block>
                            <Link
                                :href="route('admin.subjects.index')"
                                :class="[
                                    route().current('admin.subjects.*') 
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10' 
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed ? 'justify-center px-0' : 'justify-start px-3 gap-3'
                                ]"
                                class="flex items-center py-2.5 rounded-xl text-xs font-bold transition-all w-full"
                                aria-label="Matérias"
                            >
                                <BookOpen class="h-4 w-4 shrink-0" />
                                <span v-if="!isSidebarCollapsed" class="truncate">Matérias</span>
                            </Link>
                        </Tooltip>

                        <!-- Membros -->
                        <Tooltip text="Membros" position="right" :disabled="!isSidebarCollapsed" block>
                            <Link
                                :href="route('admin.users.index')"
                                :class="[
                                    route().current('admin.users.*') 
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10' 
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed ? 'justify-center px-0' : 'justify-start px-3 gap-3'
                                ]"
                                class="flex items-center py-2.5 rounded-xl text-xs font-bold transition-all w-full"
                                aria-label="Membros da Instituição"
                            >
                                <Users class="h-4 w-4 shrink-0" />
                                <span v-if="!isSidebarCollapsed" class="truncate">Membros</span>
                            </Link>
                        </Tooltip>
                    </template>

                    <!-- Ranking (All except Super Admin) -->
                    <Tooltip text="Ranking" position="right" :disabled="!isSidebarCollapsed" block v-if="$page.props.auth.user.role !== 'super_admin'">
                        <Link
                            :href="route('ranking.index')"
                            :class="[
                                route().current('ranking.index') 
                                    ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10' 
                                    : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                isSidebarCollapsed ? 'justify-center px-0' : 'justify-start px-3 gap-3'
                            ]"
                            class="flex items-center py-2.5 rounded-xl text-xs font-bold transition-all w-full"
                            aria-label="Ranking de XP"
                        >
                            <Trophy class="h-4 w-4 shrink-0" />
                            <span v-if="!isSidebarCollapsed" class="truncate">Ranking</span>
                        </Link>
                    </Tooltip>

                    <!-- Suporte Técnico (Except Super Admin) - Dedicated Page link -->
                    <Tooltip text="Suporte Técnico" position="right" :disabled="!isSidebarCollapsed" block v-if="$page.props.auth.user.role !== 'super_admin'">
                        <Link
                            :href="route('support.index')"
                            :class="[
                                route().current('support.index') 
                                    ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10' 
                                    : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                isSidebarCollapsed ? 'justify-center px-0' : 'justify-start px-3 gap-3'
                            ]"
                            class="flex items-center py-2.5 rounded-xl text-xs font-bold transition-all w-full"
                            aria-label="Suporte Técnico"
                        >
                            <HelpCircle class="h-4 w-4 shrink-0" />
                            <span v-if="!isSidebarCollapsed" class="truncate">Suporte Técnico</span>
                        </Link>
                    </Tooltip>
                </nav>

                <!-- Sidebar Footer (Profile / Logout) -->
                <div class="p-3 border-t border-zinc-800/80 bg-zinc-950/20">
                    <div class="flex items-center justify-between" v-if="!isSidebarCollapsed">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <div class="h-8 w-8 rounded-lg bg-zinc-800 border border-zinc-700 flex items-center justify-center font-bold text-xs text-white uppercase shrink-0">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                            <div class="overflow-hidden">
                                <span class="block text-xs font-bold text-zinc-200 truncate">{{ $page.props.auth.user.name }}</span>
                                <span class="block text-[10px] text-zinc-500 truncate">{{ $page.props.auth.user.email }}</span>
                            </div>
                        </div>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="p-1 rounded-lg text-zinc-500 hover:text-red-400 hover:bg-red-500/10 transition-colors shrink-0"
                            aria-label="Sair da Conta"
                        >
                            <LogOut class="h-4 w-4" />
                        </Link>
                    </div>
                    <div v-else class="flex flex-col gap-2">
                        <Tooltip text="Meu Perfil" position="right" block>
                            <Link
                                :href="route('profile.edit')"
                                :class="[
                                    route().current('profile.edit') 
                                        ? 'bg-indigo-650 text-white shadow-lg' 
                                        : 'text-zinc-450 hover:text-white hover:bg-zinc-800',
                                    'flex justify-center p-2 rounded-xl transition-colors w-full'
                                ]"
                                aria-label="Ir para configurações do Perfil"
                            >
                                <Settings class="h-4.5 w-4.5" />
                            </Link>
                        </Tooltip>
                        <Tooltip text="Sair" position="right" block>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="flex justify-center p-2 rounded-xl text-zinc-550 hover:text-red-400 hover:bg-red-500/10 transition-colors w-full"
                                aria-label="Sair da Conta"
                            >
                                <LogOut class="h-4.5 w-4.5" />
                            </Link>
                        </Tooltip>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 min-h-screen flex flex-col overflow-hidden bg-zinc-950">
                <!-- Top Navbar -->
                <nav class="h-16 border-b border-zinc-800 bg-zinc-900 flex items-center px-4 sm:px-6 lg:px-8 justify-between relative z-40" role="navigation" aria-label="Navegação Superior">
                    <div class="flex items-center gap-4">
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="p-2 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 md:hidden transition-colors"
                            aria-label="Abrir menu de navegação"
                        >
                            <Menu class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Right Controls -->
                    <div class="flex items-center gap-3">
                        <Link
                            href="/"
                            class="inline-flex items-center justify-center rounded-xl bg-zinc-800 hover:bg-zinc-700 px-3 py-2.5 md:px-3.5 md:py-1.5 text-xs font-bold text-zinc-300 transition-all border border-zinc-700/50"
                            title="Ir para o Site Público"
                        >
                            <span>🌐</span>
                            <span class="hidden md:inline ml-1.5">Ir para Site Público</span>
                        </Link>

                        <!-- Institution Switcher for Admin (Desktop/Mobile) -->
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
                                        class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-zinc-850 bg-zinc-950 px-3 py-2.5 md:px-3.5 md:py-2 text-xs font-bold text-zinc-300 hover:bg-zinc-900 transition-all"
                                        title="Alternar Unidade"
                                    >
                                        <span class="hidden md:inline">Alternar Unidade</span>
                                        <ArrowLeftRight class="h-3.5 w-3.5 text-zinc-500 shrink-0" />
                                    </button>
                                </template>
                                <template #content>
                                    <button
                                        v-for="inst in uniqueUserInstitutions"
                                        :key="inst.id"
                                        @click="switchInstitution(inst.id)"
                                        class="block w-full px-4 py-2.5 text-start text-xs font-bold transition-colors"
                                        :class="
                                            inst.id ===
                                            $page.props.auth.user.institution_id
                                                ? 'bg-indigo-600 text-white font-extrabold'
                                                : 'text-zinc-300 hover:bg-zinc-800'
                                        "
                                    >
                                        {{ inst.name }}
                                    </button>
                                </template>
                            </Dropdown>
                        </div>

                        <!-- Settings / Profile Dropdown -->
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900 px-3 py-2.5 md:px-3.5 md:py-2 text-xs font-bold text-zinc-300 hover:bg-zinc-800 transition-all"
                                    title="Configurações e Perfil"
                                >
                                    <span class="hidden md:inline">{{ formatShortName($page.props.auth.user.name) }}</span>
                                    <Settings class="h-3.5 w-3.5 text-zinc-500 shrink-0" />
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    Meu Perfil
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Sair da Conta
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </nav>

                <!-- Mobile Navigation Drawer -->
                <div
                    v-if="showingNavigationDropdown"
                    class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm md:hidden"
                    @click="showingNavigationDropdown = false"
                    role="dialog"
                    aria-modal="true"
                >
                    <div
                        class="w-64 h-full bg-zinc-900 border-r border-zinc-800 p-4 space-y-6 flex flex-col"
                        @click.stop
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="bg-indigo-600 p-1.5 rounded-lg text-white">
                                    <GraduationCap class="h-5 w-5" />
                                </div>
                                <span class="font-extrabold text-sm text-white tracking-wider">GamificaEdu</span>
                            </div>
                            <button
                                @click="showingNavigationDropdown = false"
                                class="p-1 rounded-lg text-zinc-400 hover:text-white"
                                aria-label="Fechar menu"
                            >
                                <X class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Mobile Menu Items -->
                        <nav class="flex-grow space-y-2" aria-label="Navegação Móvel">
                            <Link
                                :href="route('dashboard')"
                                @click="showingNavigationDropdown = false"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                            >
                                <Home class="h-4.5 w-4.5" />
                                Dashboard
                            </Link>

                            <template v-if="$page.props.auth.user.role === 'super_admin'">
                                <Link
                                    :href="route('super-admin.institutions.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <School class="h-4.5 w-4.5" />
                                    Instituições
                                </Link>

                                <Link
                                    :href="route('super-admin.users.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <Users class="h-4.5 w-4.5" />
                                    Usuários
                                </Link>

                                <Link
                                    :href="route('super-admin.subjects.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <BookOpen class="h-4.5 w-4.5" />
                                    Matérias
                                </Link>

                                <Link
                                    :href="route('super-admin.reports.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <FileText class="h-4.5 w-4.5" />
                                    Relatórios
                                </Link>

                                <Link
                                    :href="route('super-admin.logs.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <Terminal class="h-4.5 w-4.5" />
                                    Logs e Fila
                                </Link>

                                <Link
                                    :href="route('super-admin.supports.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <HelpCircle class="h-4.5 w-4.5" />
                                    Suporte
                                </Link>

                                <Link
                                    :href="route('super-admin.visits.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <Eye class="h-4.5 w-4.5" />
                                    Visitas do Site
                                </Link>

                                <Link
                                    :href="route('super-admin.trash.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <Trash2 class="h-4.5 w-4.5" />
                                    Lixeira
                                </Link>
                            </template>

                            <template v-if="$page.props.auth.user.role === 'admin'">
                                <Link
                                    :href="route('admin.subjects.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <BookOpen class="h-4.5 w-4.5" />
                                    Matérias
                                </Link>

                                <Link
                                    :href="route('admin.users.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <Users class="h-4.5 w-4.5" />
                                    Membros
                                </Link>
                            </template>

                            <template v-if="$page.props.auth.user.role !== 'super_admin'">
                                <Link
                                    :href="route('ranking.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <Trophy class="h-4.5 w-4.5" />
                                    Ranking
                                </Link>

                                <Link
                                    :href="route('support.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <HelpCircle class="h-4.5 w-4.5" />
                                    Suporte Técnico
                                </Link>
                            </template>
                        </nav>
                    </div>
                </div>

                <!-- Main Content Area Scrollable -->
                <main class="flex-grow overflow-y-auto p-4 sm:p-6 lg:p-8" id="main-content" role="main">
                    <!-- BREADCRUMBS -->
                    <nav class="flex mb-5" aria-label="Caminho de Navegação (Breadcrumb)">
                        <ol class="inline-flex items-center space-x-1.5 text-xs font-semibold text-zinc-500">
                            <li v-for="(crumb, idx) in breadcrumbs" :key="idx" class="inline-flex items-center">
                                <span v-if="idx > 0" class="mx-2 text-zinc-800" aria-hidden="true">/</span>
                                <Link v-if="crumb.href" :href="crumb.href" class="hover:text-indigo-400 transition-colors">{{ crumb.label }}</Link>
                                <span v-else class="text-zinc-300 font-bold" aria-current="page">{{ crumb.label }}</span>
                            </li>
                        </ol>
                    </nav>

                    <!-- Header Slot -->
                    <header class="mb-6" v-if="$slots.header">
                        <slot name="header" />
                    </header>

                    <!-- Inner Page Slot -->
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
