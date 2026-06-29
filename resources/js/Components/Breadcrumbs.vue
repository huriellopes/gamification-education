<script setup>
import { __ } from '@/i18n';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Mapeia a rota atual para a trilha de navegação (breadcrumb).
 * Cada item: { label, href|null }.
 */
const breadcrumbs = computed(() => {
    const list = [
        { label: __('nav.breadcrumb.home'), href: route('dashboard') },
    ];

    if (route().current('super-admin.dashboard')) {
        list.push({ label: __('nav.breadcrumb.super_admin'), href: null });
        list.push({ label: __('nav.breadcrumb.general_panel'), href: null });
    } else if (route().current('super-admin.institutions.index')) {
        list.push({
            label: __('nav.breadcrumb.super_admin'),
            href: route('super-admin.dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.institutions'), href: null });
    } else if (route().current('super-admin.users.index')) {
        list.push({
            label: __('nav.breadcrumb.super_admin'),
            href: route('super-admin.dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.users'), href: null });
    } else if (route().current('super-admin.subjects.index')) {
        list.push({
            label: __('nav.breadcrumb.super_admin'),
            href: route('super-admin.dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.subjects'), href: null });
    } else if (route().current('super-admin.classrooms.*')) {
        list.push({
            label: __('nav.breadcrumb.super_admin'),
            href: route('super-admin.dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.classrooms'), href: null });
    } else if (route().current('super-admin.trash.index')) {
        list.push({
            label: __('nav.breadcrumb.super_admin'),
            href: route('super-admin.dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.trash'), href: null });
    } else if (route().current('super-admin.reports.index')) {
        list.push({
            label: __('nav.breadcrumb.super_admin'),
            href: route('super-admin.dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.reports'), href: null });
    } else if (route().current('super-admin.logs.index')) {
        list.push({
            label: __('nav.breadcrumb.super_admin'),
            href: route('super-admin.dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.logs_queue'), href: null });
    } else if (route().current('super-admin.supports.index')) {
        list.push({
            label: __('nav.breadcrumb.super_admin'),
            href: route('super-admin.dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.support'), href: null });
    } else if (route().current('super-admin.visits.index')) {
        list.push({
            label: __('nav.breadcrumb.super_admin'),
            href: route('super-admin.dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.site_visits'), href: null });
    } else if (route().current('admin.dashboard')) {
        list.push({ label: __('nav.breadcrumb.admin'), href: null });
        list.push({
            label: __('nav.breadcrumb.institution_panel'),
            href: null,
        });
    } else if (route().current('admin.subjects.*')) {
        list.push({
            label: __('nav.breadcrumb.admin'),
            href: route('dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.subjects'), href: null });
    } else if (route().current('admin.classrooms.*')) {
        list.push({
            label: __('nav.breadcrumb.admin'),
            href: route('dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.classrooms'), href: null });
    } else if (route().current('admin.users.index')) {
        list.push({
            label: __('nav.breadcrumb.admin'),
            href: route('dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.members'), href: null });
    } else if (route().current('teacher.dashboard')) {
        list.push({ label: __('nav.breadcrumb.teacher'), href: null });
        list.push({ label: __('nav.breadcrumb.panel'), href: null });
    } else if (route().current('teacher.classrooms.*')) {
        list.push({
            label: __('nav.breadcrumb.teacher'),
            href: route('dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.classrooms'), href: null });
    } else if (route().current('teacher.students.*')) {
        list.push({
            label: __('nav.breadcrumb.teacher'),
            href: route('dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.students'), href: null });
    } else if (route().current('teacher.subjects.*')) {
        list.push({
            label: __('nav.breadcrumb.teacher'),
            href: route('dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.subjects'), href: null });
    } else if (route().current('student.dashboard')) {
        list.push({ label: __('nav.breadcrumb.student'), href: null });
        list.push({ label: __('nav.breadcrumb.my_panel'), href: null });
    } else if (route().current('student.materials.*')) {
        list.push({
            label: __('nav.breadcrumb.student'),
            href: route('dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.material'), href: null });
    } else if (route().current('student.tests.*')) {
        list.push({
            label: __('nav.breadcrumb.student'),
            href: route('dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.test'), href: null });
    } else if (route().current('student.subjects.*')) {
        list.push({
            label: __('nav.breadcrumb.student'),
            href: route('dashboard'),
        });
        list.push({ label: __('nav.breadcrumb.subjects'), href: null });
    } else if (route().current('ranking.index')) {
        list.push({ label: __('nav.breadcrumb.competition'), href: null });
        list.push({ label: __('nav.breadcrumb.global_ranking'), href: null });
    } else if (route().current('support.index')) {
        list.push({ label: __('nav.breadcrumb.support'), href: null });
        list.push({ label: __('nav.breadcrumb.tech_support'), href: null });
    } else if (route().current('profile.edit')) {
        list.push({ label: __('nav.breadcrumb.user'), href: null });
        list.push({ label: __('nav.breadcrumb.profile_settings'), href: null });
    } else {
        list.push({ label: __('nav.breadcrumb.dashboard'), href: null });
    }

    return list;
});
</script>

<template>
    <nav class="mb-5 flex" :aria-label="__('nav.aria.breadcrumb')">
        <ol
            class="inline-flex items-center space-x-1.5 text-xs font-semibold text-zinc-500"
        >
            <li
                v-for="(crumb, idx) in breadcrumbs"
                :key="idx"
                class="inline-flex items-center"
            >
                <span
                    v-if="idx > 0"
                    class="mx-2 text-zinc-800"
                    aria-hidden="true"
                    >/</span
                >
                <Link
                    v-if="crumb.href"
                    :href="crumb.href"
                    class="transition-colors hover:text-indigo-400"
                    >{{ crumb.label }}</Link
                >
                <span
                    v-else
                    class="font-bold text-zinc-300"
                    aria-current="page"
                    >{{ crumb.label }}</span
                >
            </li>
        </ol>
    </nav>
</template>
