<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { __ } from '@/i18n';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    subject: {
        type: Object,
        required: true,
    },
    test: {
        type: Object,
        required: true,
    },
});

// Respostas selecionadas: { [question_id]: selected_option_index }
const selectedAnswers = ref({});
const isSubmitting = ref(false);

const selectOption = (questionId, optionIndex) => {
    selectedAnswers.value[questionId] = optionIndex;
};

// Computa progresso das respostas
const answeredCount = computed(() => {
    return Object.keys(selectedAnswers.value).filter(
        (id) => selectedAnswers.value[id] !== null,
    ).length;
});

const isComplete = computed(() => {
    return answeredCount.value === props.test.questions.length;
});

const submitTest = () => {
    if (!isComplete.value) {
        if (!confirm(__('student.test.confirm_incomplete'))) {
            return;
        }
    }

    isSubmitting.value = true;
    router.post(
        route('student.tests.submit', [props.subject.id, props.test.id]),
        {
            answers: selectedAnswers.value,
        },
        {
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};
</script>

<template>
    <Head :title="test.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('student.subjects.show', subject.id)"
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
                        {{ test.title }}
                    </h2>
                    <p class="text-xs text-zinc-500">
                        {{
                            __('student.material.track', {
                                name: subject.name,
                            })
                        }}
                    </p>
                </div>
            </div>
        </template>

        <div
            class="bg-zinc-955 min-h-[calc(100vh-64px)] py-12 pb-32 text-zinc-100"
        >
            <div class="mx-auto max-w-3xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Card de Instrução/Status -->
                <div
                    class="flex flex-col items-start justify-between gap-4 rounded-2xl border border-zinc-800 bg-zinc-900/20 p-6 md:flex-row md:items-center"
                >
                    <div>
                        <h3 class="mb-1 text-sm font-bold text-white">
                            {{ __('student.test.activity_status') }}
                        </h3>
                        <p class="text-xs text-zinc-400">
                            {{ __('student.test.instruction') }}
                        </p>
                    </div>
                    <!-- Barra de progresso rápida -->
                    <div class="w-full text-xs font-semibold md:w-48">
                        <div class="mb-1 flex justify-between">
                            <span class="text-zinc-500">{{
                                __('student.test.answered')
                            }}</span>
                            <span class="text-yellow-400">{{
                                __('student.test.answered_count', {
                                    count: answeredCount,
                                    total: test.questions.length,
                                })
                            }}</span>
                        </div>
                        <div
                            class="h-1.5 w-full overflow-hidden rounded-full bg-zinc-800"
                        >
                            <div
                                class="h-full bg-yellow-500 transition-all duration-300"
                                :style="{
                                    width: `${(answeredCount / test.questions.length) * 100}%`,
                                }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Questões List -->
                <div class="space-y-6">
                    <div
                        v-for="(q, qIdx) in test.questions"
                        :key="q.id"
                        class="space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6"
                    >
                        <h4
                            class="flex gap-2 text-sm font-bold uppercase tracking-wider text-zinc-400"
                        >
                            <span>{{
                                __('student.test.question', {
                                    number: qIdx + 1,
                                })
                            }}</span>
                        </h4>
                        <p
                            class="text-base font-semibold leading-relaxed text-white"
                        >
                            {{ q.question_text }}
                        </p>

                        <!-- Opções de Múltipla Escolha -->
                        <div class="grid grid-cols-1 gap-3 pt-2">
                            <button
                                v-for="(opt, oIdx) in q.options"
                                :key="oIdx"
                                @click="selectOption(q.id, oIdx)"
                                type="button"
                                class="flex w-full items-center justify-between rounded-xl border p-4 text-left text-sm font-semibold transition-all duration-150"
                                :class="
                                    selectedAnswers[q.id] === oIdx
                                        ? 'border-indigo-500 bg-indigo-500/10 text-indigo-400 shadow-[0_0_15px_rgba(99,102,241,0.15)]'
                                        : 'border-zinc-850 hover:border-zinc-750 bg-zinc-900/40 text-zinc-300 hover:bg-zinc-800/20'
                                "
                            >
                                <span>{{ opt }}</span>
                                <!-- Bolinha Checkbox simulada -->
                                <div
                                    class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border"
                                    :class="
                                        selectedAnswers[q.id] === oIdx
                                            ? 'border-indigo-500 bg-indigo-500/20'
                                            : 'border-zinc-700'
                                    "
                                >
                                    <div
                                        v-if="selectedAnswers[q.id] === oIdx"
                                        class="h-2 w-2 rounded-full bg-indigo-400"
                                    ></div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rodapé de Envio Fixo -->
        <div
            class="fixed inset-x-0 bottom-0 z-40 mx-auto flex max-w-7xl items-center justify-between rounded-t-3xl border-t border-zinc-800 bg-zinc-900/90 p-5 shadow-[0_-10px_30px_rgba(0,0,0,0.5)] backdrop-blur-md"
        >
            <div class="flex flex-col">
                <span
                    class="text-xs font-bold uppercase tracking-wider text-zinc-500"
                    >{{ __('student.test.graded_challenge') }}</span
                >
                <span class="text-sm font-semibold text-zinc-200"
                    >{{ __('student.test.max_reward') }}
                    <span class="font-bold text-yellow-400">{{
                        __('student.test.reward_xp', {
                            points: test.points_reward,
                        })
                    }}</span></span
                >
            </div>

            <div>
                <button
                    @click="submitTest"
                    :disabled="isSubmitting"
                    class="flex items-center gap-2 rounded-xl bg-yellow-500 px-6 py-3 text-xs font-extrabold text-zinc-950 transition-all duration-200 hover:scale-105 hover:bg-yellow-400 hover:shadow-[0_0_20px_rgba(234,179,8,0.3)] disabled:opacity-50"
                >
                    <svg
                        v-if="isSubmitting"
                        class="h-4 w-4 animate-spin text-zinc-950"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                    {{ __('student.test.submit') }}
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
