<script setup>
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, Users as UsersIcon } from '@lucide/vue';

defineProps({
    classrooms: { type: Array, default: () => [] },
});
</script>

<template>
    <Head :title="__('classrooms.teacher_header')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-zinc-100">
                {{ __('classrooms.teacher_header') }}
            </h2>
        </template>

        <div class="min-h-[calc(100vh-80px)] bg-zinc-950 py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div
                    v-if="classrooms.length"
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="classroom in classrooms"
                        :key="classroom.id"
                        class="flex flex-col overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/40 p-6"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white"
                            >
                                <UsersIcon class="h-5 w-5" />
                            </div>
                            <span
                                class="inline-flex rounded-full bg-indigo-500/10 px-2.5 py-0.5 text-xs font-bold text-indigo-400"
                            >
                                {{
                                    __('classrooms.subjects_count', {
                                        count: classroom.subjects_count ?? 0,
                                    })
                                }}
                            </span>
                        </div>

                        <h3 class="mt-4 text-lg font-bold text-white">
                            {{ classroom.name }}
                        </h3>
                        <p
                            v-if="classroom.description"
                            class="mt-1 text-sm text-zinc-400"
                        >
                            {{ classroom.description }}
                        </p>

                        <div class="mt-4 space-y-2">
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-zinc-500"
                            >
                                {{ __('classrooms.col_subjects') }}
                            </p>
                            <div
                                v-if="classroom.subjects?.length"
                                class="flex flex-col gap-1.5"
                            >
                                <Link
                                    v-for="subject in classroom.subjects"
                                    :key="subject.id"
                                    :href="
                                        route(
                                            'teacher.subjects.show',
                                            subject.id,
                                        )
                                    "
                                    class="flex items-center gap-2 rounded-lg border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm text-zinc-300 transition-colors hover:border-indigo-500/30 hover:text-white"
                                >
                                    <BookOpen
                                        class="h-4 w-4 shrink-0 text-indigo-400"
                                    />
                                    <span class="truncate">{{
                                        subject.name
                                    }}</span>
                                </Link>
                            </div>
                            <p v-else class="text-xs italic text-zinc-500">
                                {{ __('classrooms.no_subjects') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 py-16 text-center text-sm text-zinc-500"
                >
                    {{ __('classrooms.teacher_empty') }}
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
