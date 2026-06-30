<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    student: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head
        :title="__('teacher.performance.title').replace(':name', student.name)"
    />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                :title="
                    __('teacher.performance.header').replace(
                        ':name',
                        student.name,
                    )
                "
                :subtitle="student.email"
            >
                <template #leading>
                    <Link
                        :href="route('teacher.students.index')"
                        class="text-zinc-400 transition-colors hover:text-white"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2.5"
                            stroke="currentColor"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"
                            />
                        </svg>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="bg-zinc-955 min-h-[calc(100vh-80px)] py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Summary Card -->
                <div
                    class="flex flex-col items-start justify-between gap-4 rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 sm:flex-row sm:items-center"
                >
                    <div>
                        <h3 class="text-lg font-bold text-white">
                            {{ __('teacher.performance.xp_accumulated') }}
                        </h3>
                        <p class="text-sm text-zinc-400">
                            {{ __('teacher.performance.xp_subtitle') }}
                        </p>
                    </div>
                    <span class="text-3xl font-extrabold text-indigo-400"
                        >{{ student.points }} XP</span
                    >
                </div>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Attempts History -->
                    <div
                        class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6"
                    >
                        <h3 class="mb-4 text-lg font-bold text-white">
                            {{ __('teacher.performance.attempts_title') }}
                        </h3>
                        <div class="space-y-4">
                            <div
                                v-for="att in student.attempts"
                                :key="att.id"
                                class="flex items-center justify-between rounded-xl border border-zinc-800 bg-zinc-950/20 p-4"
                            >
                                <div>
                                    <h4 class="text-sm font-bold text-zinc-100">
                                        {{ att.test_title }}
                                    </h4>
                                    <p class="mt-1 text-xs text-zinc-500">
                                        {{
                                            __(
                                                'teacher.performance.submitted_at',
                                            ).replace(':date', att.completed_at)
                                        }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <div
                                        class="text-xs font-semibold text-zinc-300"
                                    >
                                        {{
                                            __(
                                                'teacher.performance.correct_answers',
                                            )
                                                .replace(
                                                    ':correct',
                                                    att.correct_answers,
                                                )
                                                .replace(
                                                    ':total',
                                                    att.total_questions,
                                                )
                                        }}
                                    </div>
                                    <div
                                        class="mt-0.5 text-sm font-bold text-indigo-400"
                                    >
                                        +{{ att.score }} XP
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="student.attempts.length === 0"
                                class="py-8 text-center text-sm text-zinc-500"
                            >
                                {{ __('teacher.performance.attempts_empty') }}
                            </div>
                        </div>
                    </div>

                    <!-- Score History -->
                    <div
                        class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6"
                    >
                        <h3 class="mb-4 text-lg font-bold text-white">
                            {{ __('teacher.performance.score_history_title') }}
                        </h3>
                        <div class="space-y-4">
                            <div
                                v-for="hist in student.score_history"
                                :key="hist.id"
                                class="bg-zinc-955/20 flex items-center justify-between rounded-xl border border-zinc-800 p-4"
                            >
                                <div>
                                    <h4
                                        class="text-sm font-semibold text-zinc-200"
                                    >
                                        {{ hist.description }}
                                    </h4>
                                    <p class="mt-1 text-xs text-zinc-500">
                                        {{
                                            __(
                                                'teacher.performance.granted_at',
                                            ).replace(':date', hist.created_at)
                                        }}
                                    </p>
                                </div>
                                <span
                                    class="text-sm font-bold"
                                    :class="
                                        hist.points >= 0
                                            ? 'text-emerald-400'
                                            : 'text-red-400'
                                    "
                                >
                                    {{ hist.points >= 0 ? '+' : ''
                                    }}{{ hist.points }} XP
                                </span>
                            </div>

                            <div
                                v-if="student.score_history.length === 0"
                                class="py-8 text-center text-sm text-zinc-500"
                            >
                                {{
                                    __(
                                        'teacher.performance.score_history_empty',
                                    )
                                }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
