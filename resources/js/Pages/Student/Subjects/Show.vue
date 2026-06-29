<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    subject: {
        type: Object,
        required: true,
    },
    materials: {
        type: Array,
        default: () => [],
    },
    tests: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head :title="subject.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('dashboard')"
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
                <div>
                    <h2 class="text-xl font-bold leading-tight text-zinc-100">
                        {{ subject.name }}
                    </h2>
                    <p class="text-xs text-zinc-500">
                        {{ __('student.subject.learning_track') }}
                    </p>
                </div>
            </div>
        </template>

        <div class="bg-zinc-955 min-h-[calc(100vh-64px)] py-12 text-zinc-100">
            <div class="mx-auto max-w-4xl space-y-10 px-4 sm:px-6 lg:px-8">
                <!-- Informações Flash -->
                <div
                    v-if="$page.props.flash?.success"
                    class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-400"
                >
                    {{ $page.props.flash.success }}
                </div>

                <!-- Resumo da Matéria -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/20 p-6"
                >
                    <h3
                        class="mb-1 text-xs font-bold uppercase tracking-wider text-zinc-500"
                    >
                        {{ __('student.subject.syllabus') }}
                    </h3>
                    <p class="text-sm leading-relaxed text-zinc-300">
                        {{
                            subject.description ||
                            __('student.subject.no_description')
                        }}
                    </p>
                </div>

                <!-- Bloco de Conteúdo (Materiais e Atividades Combinados em uma Timeline) -->
                <div class="space-y-6">
                    <h3
                        class="flex items-center gap-2 text-lg font-bold text-white"
                    >
                        <span class="text-indigo-400">🔥</span>
                        {{ __('student.subject.your_journey') }}
                    </h3>

                    <div
                        class="relative ml-3 space-y-8 border-l border-zinc-800 pl-6"
                    >
                        <!-- Materiais de Estudo na Timeline -->
                        <div
                            v-for="(material, index) in materials"
                            :key="`mat-${material.id}`"
                            class="relative"
                        >
                            <!-- Indicador na Linha da Timeline -->
                            <div
                                class="absolute -left-[31px] top-1 flex h-4 w-4 items-center justify-center rounded-full border-2 transition-all duration-300"
                                :class="
                                    material.completed
                                        ? 'border-emerald-400 bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]'
                                        : 'border-zinc-800 bg-zinc-950'
                                "
                            ></div>

                            <div
                                class="flex flex-col items-start justify-between gap-4 rounded-xl border border-zinc-800 p-5 transition-all duration-200 sm:flex-row sm:items-center"
                                :class="
                                    material.completed
                                        ? 'border-zinc-800/60 bg-zinc-900/10'
                                        : 'bg-zinc-900/40 hover:bg-zinc-900/70'
                                "
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs font-bold uppercase tracking-wider text-indigo-400"
                                            >{{
                                                __(
                                                    'student.subject.step_reading',
                                                    { step: index + 1 },
                                                )
                                            }}</span
                                        >
                                        <span
                                            v-if="material.completed"
                                            class="flex items-center gap-0.5 text-xs font-bold text-emerald-400"
                                        >
                                            {{ __('student.subject.read') }}
                                        </span>
                                    </div>
                                    <h4
                                        class="mt-1 text-base font-bold text-white"
                                    >
                                        {{ material.title }}
                                    </h4>
                                    <p class="mt-0.5 text-xs text-zinc-500">
                                        {{
                                            __(
                                                'student.subject.reward_points',
                                                {
                                                    points: material.points_reward,
                                                },
                                            )
                                        }}
                                    </p>
                                </div>

                                <Link
                                    :href="
                                        route('student.materials.show', [
                                            subject.id,
                                            material.id,
                                        ])
                                    "
                                    class="rounded-xl px-4 py-2 text-xs font-bold transition-all duration-150"
                                    :class="
                                        material.completed
                                            ? 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700 hover:text-white'
                                            : 'bg-indigo-600 text-white hover:bg-indigo-500'
                                    "
                                >
                                    {{
                                        material.completed
                                            ? __(
                                                  'student.subject.review_material',
                                              )
                                            : __(
                                                  'student.subject.start_reading',
                                              )
                                    }}
                                </Link>
                            </div>
                        </div>

                        <!-- Atividades / Testes na Timeline -->
                        <div
                            v-for="test in tests"
                            :key="`test-${test.id}`"
                            class="relative"
                        >
                            <!-- Indicador na Linha da Timeline -->
                            <div
                                class="absolute -left-[31px] top-1 flex h-4 w-4 items-center justify-center rounded-full border-2 transition-all duration-300"
                                :class="
                                    test.best_score !== null
                                        ? 'border-yellow-400 bg-yellow-500 shadow-[0_0_8px_rgba(234,179,8,0.5)]'
                                        : 'border-zinc-800 bg-zinc-950'
                                "
                            ></div>

                            <div
                                class="flex flex-col items-start justify-between gap-4 rounded-xl border border-zinc-800 bg-zinc-900/40 p-5 transition-all duration-200 hover:bg-zinc-900/70 sm:flex-row sm:items-center"
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs font-bold uppercase tracking-wider text-yellow-500"
                                            >{{
                                                __(
                                                    'student.subject.graded_challenge',
                                                )
                                            }}</span
                                        >
                                        <span
                                            v-if="test.best_score !== null"
                                            class="text-xs font-bold text-yellow-400"
                                        >
                                            {{
                                                __(
                                                    'student.subject.completed_max_score',
                                                    { score: test.best_score },
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <h4
                                        class="mt-1 text-base font-bold text-white"
                                    >
                                        {{ test.title }}
                                    </h4>
                                    <p
                                        class="mt-1 max-w-md text-xs text-zinc-400"
                                    >
                                        {{ test.description }}
                                    </p>

                                    <!-- Extrato de acertos -->
                                    <div
                                        v-if="test.best_score !== null"
                                        class="mt-2 flex items-center gap-2 text-xs text-zinc-500"
                                    >
                                        <span
                                            >{{
                                                __(
                                                    'student.subject.last_performance',
                                                )
                                            }}
                                            <strong
                                                >{{ test.correct_answers }} /
                                                {{
                                                    test.total_questions
                                                }}</strong
                                            >
                                            {{
                                                __(
                                                    'student.subject.correct_answers',
                                                )
                                            }}</span
                                        >
                                        <span
                                            class="h-1 w-1 rounded-full bg-zinc-700"
                                        ></span>
                                        <span class="text-yellow-500"
                                            >{{
                                                __(
                                                    'student.subject.xp_received',
                                                )
                                            }}
                                            <strong>{{
                                                __(
                                                    'student.subject.xp_received_value',
                                                    { score: test.best_score },
                                                )
                                            }}</strong></span
                                        >
                                    </div>
                                </div>

                                <Link
                                    :href="
                                        route('student.tests.show', [
                                            subject.id,
                                            test.id,
                                        ])
                                    "
                                    class="rounded-xl px-4 py-2 text-xs font-bold transition-all duration-150"
                                    :class="
                                        test.best_score !== null
                                            ? 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700'
                                            : 'bg-yellow-600 text-zinc-950 hover:bg-yellow-500'
                                    "
                                >
                                    {{
                                        test.best_score !== null
                                            ? __(
                                                  'student.subject.retake_challenge',
                                              )
                                            : __(
                                                  'student.subject.take_challenge',
                                              )
                                    }}
                                </Link>
                            </div>
                        </div>

                        <div
                            v-if="materials.length === 0 && tests.length === 0"
                            class="border-zinc-850 rounded-xl border border-dashed p-8 pl-0 text-center text-sm text-zinc-500"
                        >
                            {{ __('student.subject.empty') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
