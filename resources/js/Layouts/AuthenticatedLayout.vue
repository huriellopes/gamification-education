<script setup>
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import NavbarActions from '@/Components/NavbarActions.vue';
import Toast from '@/Components/Toast.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { __ } from '@/i18n';
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    ChevronLeft,
    ChevronRight,
    Eye,
    FileText,
    GraduationCap,
    HelpCircle,
    Home,
    LogOut,
    Menu,
    School,
    Settings,
    Terminal,
    Trash2,
    Trophy,
    Users,
    X,
} from '@lucide/vue';
import { ref } from 'vue';

const showingNavigationDropdown = ref(false);
const isSidebarCollapsed = ref(
    typeof window !== 'undefined' &&
        localStorage.getItem('sidebar_collapsed') === 'true',
);

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    if (typeof window !== 'undefined') {
        localStorage.setItem(
            'sidebar_collapsed',
            isSidebarCollapsed.value ? 'true' : 'false',
        );
    }
};
</script>

<template>
    <div class="flex h-screen flex-col overflow-hidden">
        <Toast />
        <!-- Impersonation Banner -->
        <div
            v-if="$page.props.auth.is_impersonating"
            class="bg-indigo-600 px-4 py-3 text-white sm:flex sm:items-center sm:justify-between sm:px-6 lg:px-8"
            role="banner"
        >
            <p class="text-center text-sm font-medium sm:text-left">
                {{ __('nav.impersonation.banner') }}
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
                    {{ __('nav.impersonation.leave') }}
                </Link>
            </div>
        </div>

        <div
            class="relative flex min-h-0 flex-1 overflow-hidden bg-zinc-950 text-zinc-100"
        >
            <!-- Left Sidebar (Desktop Only) -->
            <aside
                :class="isSidebarCollapsed ? 'w-20 overflow-visible' : 'w-64'"
                class="no-scrollbar relative z-30 hidden h-full shrink-0 select-none flex-col border-r border-zinc-800/80 bg-zinc-900/60 backdrop-blur-xl transition-all duration-300 md:flex"
                :aria-label="__('nav.aria.main_nav')"
                role="complementary"
            >
                <!-- Sidebar Header / Logo -->
                <div
                    class="flex h-20 items-center justify-between border-b border-zinc-800/80 px-4"
                >
                    <!-- Collapsed Version (Clicking expands sidebar) -->
                    <div
                        v-if="isSidebarCollapsed"
                        @click="toggleSidebar"
                        class="flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-xl px-1.5 py-1 transition-all hover:bg-zinc-800/50"
                        :title="__('nav.aria.expand_sidebar')"
                    >
                        <div
                            class="shrink-0 rounded-lg bg-indigo-600 p-1.5 text-white"
                        >
                            <GraduationCap class="h-5 w-5" />
                        </div>
                        <ChevronRight
                            class="h-3.5 w-3.5 shrink-0 text-zinc-400"
                        />
                    </div>

                    <!-- Expanded Version -->
                    <template v-else>
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div
                                class="shrink-0 rounded-lg bg-indigo-600 p-1.5 text-white"
                            >
                                <GraduationCap class="h-5 w-5" />
                            </div>
                            <span
                                class="truncate text-sm font-extrabold tracking-wider text-white"
                                >GamificaEdu</span
                            >
                        </div>

                        <button
                            @click="toggleSidebar"
                            class="shrink-0 rounded-lg p-1 text-zinc-400 transition-colors hover:bg-zinc-800 hover:text-white"
                            :aria-label="__('nav.aria.collapse_sidebar')"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </button>
                    </template>
                </div>

                <nav
                    :class="
                        isSidebarCollapsed
                            ? 'overflow-visible'
                            : 'overflow-y-auto'
                    "
                    class="no-scrollbar flex-1 space-y-1.5 px-3 py-4"
                    :aria-label="__('nav.aria.sidebar_nav')"
                >
                    <!-- Link Dashboard -->
                    <Tooltip
                        :text="__('nav.tooltip.dashboard')"
                        position="right"
                        :disabled="!isSidebarCollapsed"
                        block
                    >
                        <Link
                            :href="route('dashboard')"
                            :class="[
                                route().current('dashboard') ||
                                (route().current('super-admin.dashboard') &&
                                    !route().current(
                                        'super-admin.institutions.*',
                                    ) &&
                                    !route().current('super-admin.users.*'))
                                    ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                    : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                isSidebarCollapsed
                                    ? 'justify-center px-0'
                                    : 'justify-start gap-3 px-3',
                            ]"
                            class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                            :aria-label="__('nav.aria.dashboard')"
                        >
                            <Home class="h-4 w-4 shrink-0" />
                            <span v-if="!isSidebarCollapsed" class="truncate">{{
                                __('nav.sidebar.dashboard')
                            }}</span>
                        </Link>
                    </Tooltip>

                    <!-- SUPER ADMIN SPECIFIC PAGES -->
                    <template
                        v-if="$page.props.auth.user.role === 'super_admin'"
                    >
                        <!-- Instituições -->
                        <Tooltip
                            :text="__('nav.tooltip.institutions')"
                            position="right"
                            :disabled="!isSidebarCollapsed"
                            block
                        >
                            <Link
                                :href="route('super-admin.institutions.index')"
                                :class="[
                                    route().current(
                                        'super-admin.institutions.*',
                                    )
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed
                                        ? 'justify-center px-0'
                                        : 'justify-start gap-3 px-3',
                                ]"
                                class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                                :aria-label="__('nav.aria.manage_institutions')"
                            >
                                <School class="h-4 w-4 shrink-0" />
                                <span
                                    v-if="!isSidebarCollapsed"
                                    class="truncate"
                                    >{{ __('nav.sidebar.institutions') }}</span
                                >
                            </Link>
                        </Tooltip>

                        <!-- Usuários -->
                        <Tooltip
                            :text="__('nav.tooltip.users')"
                            position="right"
                            :disabled="!isSidebarCollapsed"
                            block
                        >
                            <Link
                                :href="route('super-admin.users.index')"
                                :class="[
                                    route().current('super-admin.users.*')
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed
                                        ? 'justify-center px-0'
                                        : 'justify-start gap-3 px-3',
                                ]"
                                class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                                :aria-label="__('nav.aria.manage_users')"
                            >
                                <Users class="h-4 w-4 shrink-0" />
                                <span
                                    v-if="!isSidebarCollapsed"
                                    class="truncate"
                                    >{{ __('nav.sidebar.users') }}</span
                                >
                            </Link>
                        </Tooltip>

                        <!-- Matérias -->
                        <Tooltip
                            :text="__('nav.tooltip.subjects')"
                            position="right"
                            :disabled="!isSidebarCollapsed"
                            block
                        >
                            <Link
                                :href="route('super-admin.subjects.index')"
                                :class="[
                                    route().current('super-admin.subjects.*')
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed
                                        ? 'justify-center px-0'
                                        : 'justify-start gap-3 px-3',
                                ]"
                                class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                                :aria-label="__('nav.aria.manage_subjects')"
                            >
                                <BookOpen class="h-4 w-4 shrink-0" />
                                <span
                                    v-if="!isSidebarCollapsed"
                                    class="truncate"
                                    >{{ __('nav.sidebar.subjects') }}</span
                                >
                            </Link>
                        </Tooltip>

                        <!-- Classrooms -->
                        <Tooltip
                            :text="__('nav.tooltip.classrooms')"
                            position="right"
                            :disabled="!isSidebarCollapsed"
                            block
                        >
                            <Link
                                :href="route('super-admin.classrooms.index')"
                                :class="[
                                    route().current('super-admin.classrooms.*')
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed
                                        ? 'justify-center px-0'
                                        : 'justify-start gap-3 px-3',
                                ]"
                                class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                                :aria-label="__('nav.aria.manage_classrooms')"
                            >
                                <GraduationCap class="h-4 w-4 shrink-0" />
                                <span
                                    v-if="!isSidebarCollapsed"
                                    class="truncate"
                                    >{{ __('nav.sidebar.classrooms') }}</span
                                >
                            </Link>
                        </Tooltip>

                        <!-- Relatórios -->
                        <Tooltip
                            :text="__('nav.tooltip.reports')"
                            position="right"
                            :disabled="!isSidebarCollapsed"
                            block
                        >
                            <Link
                                :href="route('super-admin.reports.index')"
                                :class="[
                                    route().current('super-admin.reports.*')
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed
                                        ? 'justify-center px-0'
                                        : 'justify-start gap-3 px-3',
                                ]"
                                class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                                :aria-label="__('nav.aria.system_reports')"
                            >
                                <FileText class="h-4 w-4 shrink-0" />
                                <span
                                    v-if="!isSidebarCollapsed"
                                    class="truncate"
                                    >{{ __('nav.sidebar.reports') }}</span
                                >
                            </Link>
                        </Tooltip>

                        <!-- Logs do Sistema -->
                        <Tooltip
                            :text="__('nav.tooltip.logs_queue')"
                            position="right"
                            :disabled="!isSidebarCollapsed"
                            block
                        >
                            <Link
                                :href="route('super-admin.logs.index')"
                                :class="[
                                    route().current('super-admin.logs.*')
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed
                                        ? 'justify-center px-0'
                                        : 'justify-start gap-3 px-3',
                                ]"
                                class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                                :aria-label="__('nav.aria.system_logs')"
                            >
                                <Terminal class="h-4 w-4 shrink-0" />
                                <span
                                    v-if="!isSidebarCollapsed"
                                    class="truncate"
                                    >{{ __('nav.sidebar.logs_queue') }}</span
                                >
                            </Link>
                        </Tooltip>

                        <!-- Chamados -->
                        <Tooltip
                            :text="__('nav.tooltip.support_tickets')"
                            position="right"
                            :disabled="!isSidebarCollapsed"
                            block
                        >
                            <Link
                                :href="route('super-admin.supports.index')"
                                :class="[
                                    route().current('super-admin.supports.*')
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed
                                        ? 'justify-center px-0'
                                        : 'justify-start gap-3 px-3',
                                ]"
                                class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                                :aria-label="__('nav.aria.support_tickets')"
                            >
                                <HelpCircle class="h-4 w-4 shrink-0" />
                                <span
                                    v-if="!isSidebarCollapsed"
                                    class="truncate"
                                    >{{ __('nav.sidebar.support') }}</span
                                >
                            </Link>
                        </Tooltip>

                        <!-- Visitas do Site -->
                        <Tooltip
                            :text="__('nav.tooltip.site_visits')"
                            position="right"
                            :disabled="!isSidebarCollapsed"
                            block
                        >
                            <Link
                                :href="route('super-admin.visits.index')"
                                :class="[
                                    route().current('super-admin.visits.*')
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed
                                        ? 'justify-center px-0'
                                        : 'justify-start gap-3 px-3',
                                ]"
                                class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                                :aria-label="__('nav.aria.site_visits')"
                            >
                                <Eye class="h-4 w-4 shrink-0" />
                                <span
                                    v-if="!isSidebarCollapsed"
                                    class="truncate"
                                    >{{ __('nav.sidebar.site_visits') }}</span
                                >
                            </Link>
                        </Tooltip>

                        <!-- Lixeira -->
                        <Tooltip
                            :text="__('nav.tooltip.trash')"
                            position="right"
                            :disabled="!isSidebarCollapsed"
                            block
                        >
                            <Link
                                :href="route('super-admin.trash.index')"
                                :class="[
                                    route().current('super-admin.trash.*')
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed
                                        ? 'justify-center px-0'
                                        : 'justify-start gap-3 px-3',
                                ]"
                                class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                                :aria-label="__('nav.aria.system_trash')"
                            >
                                <Trash2 class="h-4 w-4 shrink-0" />
                                <span
                                    v-if="!isSidebarCollapsed"
                                    class="truncate"
                                    >{{ __('nav.sidebar.trash') }}</span
                                >
                            </Link>
                        </Tooltip>
                    </template>

                    <!-- ADMIN SPECIFIC PAGES -->
                    <template v-if="$page.props.auth.user.role === 'admin'">
                        <!-- Matérias -->
                        <Tooltip
                            :text="__('nav.tooltip.subjects')"
                            position="right"
                            :disabled="!isSidebarCollapsed"
                            block
                        >
                            <Link
                                :href="route('admin.subjects.index')"
                                :class="[
                                    route().current('admin.subjects.*')
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed
                                        ? 'justify-center px-0'
                                        : 'justify-start gap-3 px-3',
                                ]"
                                class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                                :aria-label="__('nav.aria.subjects')"
                            >
                                <BookOpen class="h-4 w-4 shrink-0" />
                                <span
                                    v-if="!isSidebarCollapsed"
                                    class="truncate"
                                    >{{ __('nav.sidebar.subjects') }}</span
                                >
                            </Link>
                        </Tooltip>

                        <!-- Classrooms -->
                        <Tooltip
                            :text="__('nav.tooltip.classrooms')"
                            position="right"
                            :disabled="!isSidebarCollapsed"
                            block
                        >
                            <Link
                                :href="route('admin.classrooms.index')"
                                :class="[
                                    route().current('admin.classrooms.*')
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed
                                        ? 'justify-center px-0'
                                        : 'justify-start gap-3 px-3',
                                ]"
                                class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                                :aria-label="__('nav.aria.manage_classrooms')"
                            >
                                <GraduationCap class="h-4 w-4 shrink-0" />
                                <span
                                    v-if="!isSidebarCollapsed"
                                    class="truncate"
                                    >{{ __('nav.sidebar.classrooms') }}</span
                                >
                            </Link>
                        </Tooltip>

                        <!-- Membros -->
                        <Tooltip
                            :text="__('nav.tooltip.members')"
                            position="right"
                            :disabled="!isSidebarCollapsed"
                            block
                        >
                            <Link
                                :href="route('admin.users.index')"
                                :class="[
                                    route().current('admin.users.*')
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed
                                        ? 'justify-center px-0'
                                        : 'justify-start gap-3 px-3',
                                ]"
                                class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                                :aria-label="__('nav.aria.institution_members')"
                            >
                                <Users class="h-4 w-4 shrink-0" />
                                <span
                                    v-if="!isSidebarCollapsed"
                                    class="truncate"
                                    >{{ __('nav.sidebar.members') }}</span
                                >
                            </Link>
                        </Tooltip>
                    </template>

                    <!-- TEACHER SPECIFIC PAGES -->
                    <template v-if="$page.props.auth.user.role === 'teacher'">
                        <!-- Minhas Classrooms -->
                        <Tooltip
                            :text="__('nav.tooltip.my_classrooms')"
                            position="right"
                            :disabled="!isSidebarCollapsed"
                            block
                        >
                            <Link
                                :href="route('teacher.classrooms.index')"
                                :class="[
                                    route().current('teacher.classrooms.*')
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed
                                        ? 'justify-center px-0'
                                        : 'justify-start gap-3 px-3',
                                ]"
                                class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                                :aria-label="__('nav.aria.manage_classrooms')"
                            >
                                <GraduationCap class="h-4 w-4 shrink-0" />
                                <span
                                    v-if="!isSidebarCollapsed"
                                    class="truncate"
                                    >{{ __('nav.sidebar.my_classrooms') }}</span
                                >
                            </Link>
                        </Tooltip>

                        <!-- Meus Alunos -->
                        <Tooltip
                            :text="__('nav.tooltip.students')"
                            position="right"
                            :disabled="!isSidebarCollapsed"
                            block
                        >
                            <Link
                                :href="route('teacher.students.index')"
                                :class="[
                                    route().current('teacher.students.*')
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed
                                        ? 'justify-center px-0'
                                        : 'justify-start gap-3 px-3',
                                ]"
                                class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                                :aria-label="__('nav.aria.manage_students')"
                            >
                                <Users class="h-4 w-4 shrink-0" />
                                <span
                                    v-if="!isSidebarCollapsed"
                                    class="truncate"
                                    >{{ __('nav.sidebar.students') }}</span
                                >
                            </Link>
                        </Tooltip>

                        <!-- Matérias -->
                        <Tooltip
                            :text="__('nav.tooltip.subjects')"
                            position="right"
                            :disabled="!isSidebarCollapsed"
                            block
                        >
                            <Link
                                :href="route('teacher.subjects.index')"
                                :class="[
                                    route().current('teacher.subjects.index')
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                    isSidebarCollapsed
                                        ? 'justify-center px-0'
                                        : 'justify-start gap-3 px-3',
                                ]"
                                class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                                :aria-label="__('nav.aria.subjects')"
                            >
                                <BookOpen class="h-4 w-4 shrink-0" />
                                <span
                                    v-if="!isSidebarCollapsed"
                                    class="truncate"
                                    >{{ __('nav.sidebar.subjects') }}</span
                                >
                            </Link>
                        </Tooltip>
                    </template>

                    <!-- Ranking (All except Super Admin) -->
                    <Tooltip
                        :text="__('nav.tooltip.ranking')"
                        position="right"
                        :disabled="!isSidebarCollapsed"
                        block
                        v-if="$page.props.auth.user.role !== 'super_admin'"
                    >
                        <Link
                            :href="route('ranking.index')"
                            :class="[
                                route().current('ranking.index')
                                    ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                    : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                isSidebarCollapsed
                                    ? 'justify-center px-0'
                                    : 'justify-start gap-3 px-3',
                            ]"
                            class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                            :aria-label="__('nav.aria.xp_ranking')"
                        >
                            <Trophy class="h-4 w-4 shrink-0" />
                            <span v-if="!isSidebarCollapsed" class="truncate">{{
                                __('nav.sidebar.ranking')
                            }}</span>
                        </Link>
                    </Tooltip>

                    <!-- Suporte Técnico (Except Super Admin) - Dedicated Page link -->
                    <Tooltip
                        :text="__('nav.tooltip.tech_support')"
                        position="right"
                        :disabled="!isSidebarCollapsed"
                        block
                        v-if="$page.props.auth.user.role !== 'super_admin'"
                    >
                        <Link
                            :href="route('support.index')"
                            :class="[
                                route().current('support.index')
                                    ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'
                                    : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                isSidebarCollapsed
                                    ? 'justify-center px-0'
                                    : 'justify-start gap-3 px-3',
                            ]"
                            class="flex w-full items-center rounded-xl py-2.5 text-xs font-bold transition-all"
                            :aria-label="__('nav.aria.tech_support')"
                        >
                            <HelpCircle class="h-4 w-4 shrink-0" />
                            <span v-if="!isSidebarCollapsed" class="truncate">{{
                                __('nav.sidebar.tech_support')
                            }}</span>
                        </Link>
                    </Tooltip>
                </nav>

                <!-- Sidebar Footer (Profile / Logout) -->
                <div class="border-t border-zinc-800/80 bg-zinc-950/20 p-3">
                    <div
                        class="flex items-center justify-between"
                        v-if="!isSidebarCollapsed"
                    >
                        <div class="flex items-center gap-2 overflow-hidden">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-xs font-bold uppercase text-white"
                            >
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                            <div class="overflow-hidden">
                                <span
                                    class="block truncate text-xs font-bold text-zinc-200"
                                    >{{ $page.props.auth.user.name }}</span
                                >
                                <span
                                    class="block truncate text-[10px] text-zinc-500"
                                    >{{ $page.props.auth.user.email }}</span
                                >
                            </div>
                        </div>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="shrink-0 rounded-lg p-1 text-zinc-500 transition-colors hover:bg-red-500/10 hover:text-red-400"
                            :aria-label="__('nav.aria.logout')"
                        >
                            <LogOut class="h-4 w-4" />
                        </Link>
                    </div>
                    <div v-else class="flex flex-col gap-2">
                        <Tooltip
                            :text="__('nav.tooltip.my_profile')"
                            position="right"
                            block
                        >
                            <Link
                                :href="route('profile.edit')"
                                :class="[
                                    route().current('profile.edit')
                                        ? 'bg-indigo-650 text-white shadow-lg'
                                        : 'text-zinc-450 hover:bg-zinc-800 hover:text-white',
                                    'flex w-full justify-center rounded-xl p-2 transition-colors',
                                ]"
                                :aria-label="__('nav.aria.profile_settings')"
                            >
                                <Settings class="h-4.5 w-4.5" />
                            </Link>
                        </Tooltip>
                        <Tooltip
                            :text="__('nav.tooltip.logout')"
                            position="right"
                            block
                        >
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="text-zinc-550 flex w-full justify-center rounded-xl p-2 transition-colors hover:bg-red-500/10 hover:text-red-400"
                                :aria-label="__('nav.aria.logout')"
                            >
                                <LogOut class="h-4.5 w-4.5" />
                            </Link>
                        </Tooltip>
                    </div>

                    <div
                        v-if="$page.props.version"
                        class="mt-3 text-center font-mono text-[10px] text-zinc-600"
                    >
                        v{{ $page.props.version }}
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div
                class="flex min-h-0 flex-1 flex-col overflow-hidden bg-zinc-950"
            >
                <!-- Top Navbar -->
                <nav
                    class="relative z-40 flex h-20 items-center justify-between border-b border-zinc-800 bg-zinc-900 px-4 sm:px-6 lg:px-8"
                    role="navigation"
                    :aria-label="__('nav.aria.top_nav')"
                >
                    <div class="flex items-center gap-4">
                        <button
                            @click="
                                showingNavigationDropdown =
                                    !showingNavigationDropdown
                            "
                            class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-800 hover:text-white md:hidden"
                            :aria-label="__('nav.aria.open_menu')"
                        >
                            <Menu class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Right Controls -->
                    <NavbarActions />
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
                        class="flex h-full w-64 flex-col space-y-6 border-r border-zinc-800 bg-zinc-900 p-4"
                        @click.stop
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="rounded-lg bg-indigo-600 p-1.5 text-white"
                                >
                                    <GraduationCap class="h-5 w-5" />
                                </div>
                                <span
                                    class="text-sm font-extrabold tracking-wider text-white"
                                    >GamificaEdu</span
                                >
                            </div>
                            <button
                                @click="showingNavigationDropdown = false"
                                class="rounded-lg p-1 text-zinc-400 hover:text-white"
                                :aria-label="__('nav.aria.close_menu')"
                            >
                                <X class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Mobile Menu Items -->
                        <nav
                            class="flex-grow space-y-2"
                            :aria-label="__('nav.aria.mobile_nav')"
                        >
                            <Link
                                :href="route('dashboard')"
                                @click="showingNavigationDropdown = false"
                                class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                            >
                                <Home class="h-4.5 w-4.5" />
                                {{ __('nav.sidebar.dashboard') }}
                            </Link>

                            <template
                                v-if="
                                    $page.props.auth.user.role === 'super_admin'
                                "
                            >
                                <Link
                                    :href="
                                        route('super-admin.institutions.index')
                                    "
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <School class="h-4.5 w-4.5" />
                                    {{ __('nav.sidebar.institutions') }}
                                </Link>

                                <Link
                                    :href="route('super-admin.users.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <Users class="h-4.5 w-4.5" />
                                    {{ __('nav.sidebar.users') }}
                                </Link>

                                <Link
                                    :href="route('super-admin.subjects.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <BookOpen class="h-4.5 w-4.5" />
                                    {{ __('nav.sidebar.subjects') }}
                                </Link>

                                <Link
                                    :href="route('super-admin.reports.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <FileText class="h-4.5 w-4.5" />
                                    {{ __('nav.sidebar.reports') }}
                                </Link>

                                <Link
                                    :href="route('super-admin.logs.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <Terminal class="h-4.5 w-4.5" />
                                    {{ __('nav.sidebar.logs_queue') }}
                                </Link>

                                <Link
                                    :href="route('super-admin.supports.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <HelpCircle class="h-4.5 w-4.5" />
                                    {{ __('nav.sidebar.support') }}
                                </Link>

                                <Link
                                    :href="route('super-admin.visits.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <Eye class="h-4.5 w-4.5" />
                                    {{ __('nav.sidebar.site_visits') }}
                                </Link>

                                <Link
                                    :href="route('super-admin.trash.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <Trash2 class="h-4.5 w-4.5" />
                                    {{ __('nav.sidebar.trash') }}
                                </Link>
                            </template>

                            <template
                                v-if="$page.props.auth.user.role === 'admin'"
                            >
                                <Link
                                    :href="route('admin.subjects.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <BookOpen class="h-4.5 w-4.5" />
                                    {{ __('nav.sidebar.subjects') }}
                                </Link>

                                <Link
                                    :href="route('admin.users.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <Users class="h-4.5 w-4.5" />
                                    {{ __('nav.sidebar.members') }}
                                </Link>
                            </template>

                            <template
                                v-if="
                                    $page.props.auth.user.role !== 'super_admin'
                                "
                            >
                                <Link
                                    :href="route('ranking.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <Trophy class="h-4.5 w-4.5" />
                                    {{ __('nav.sidebar.ranking') }}
                                </Link>

                                <Link
                                    :href="route('support.index')"
                                    @click="showingNavigationDropdown = false"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                                >
                                    <HelpCircle class="h-4.5 w-4.5" />
                                    {{ __('nav.sidebar.tech_support') }}
                                </Link>
                            </template>
                        </nav>
                    </div>
                </div>

                <!-- Main Content Area Scrollable -->
                <main
                    class="flex-grow overflow-y-auto p-4 sm:p-6 lg:p-8"
                    id="main-content"
                    role="main"
                >
                    <!-- BREADCRUMBS -->
                    <Breadcrumbs />

                    <!-- Header Slot -->
                    <header
                        v-if="$slots.header"
                        class="-mx-4 mb-6 border-b border-zinc-800/70 px-4 pb-5 pt-1 sm:-mx-6 sm:mb-8 sm:px-6 sm:pb-6 lg:-mx-8 lg:px-8"
                    >
                        <slot name="header" />
                    </header>

                    <!-- Inner Page Slot -->
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
