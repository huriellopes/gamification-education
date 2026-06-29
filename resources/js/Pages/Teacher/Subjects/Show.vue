<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import Tooltip from '@/Components/Tooltip.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Sparkles, Trash2 } from '@lucide/vue';
import { ref } from 'vue';

const props = defineProps({
    subject: {
        type: Object,
        required: true,
    },
});

// AI simulation generator
const isGenerating = ref(false);
const aiForm = useForm({
    theme: '',
});

const selectPreset = (theme) => {
    aiForm.theme = theme;
};

const generateContent = () => {
    isGenerating.value = true;
    aiForm.post(route('teacher.subjects.generate', props.subject.id), {
        onFinish: () => {
            isGenerating.value = false;
        },
        onSuccess: () => {
            aiForm.reset();
        },
    });
};

// Study Material CRUD
const isMaterialModalOpen = ref(false);
const isEditingMaterial = ref(false);
const selectedMaterialId = ref(null);
const matForm = useForm({
    title: '',
    content: '',
    points_reward: 10,
});

const openCreateMaterialModal = () => {
    isEditingMaterial.value = false;
    selectedMaterialId.value = null;
    matForm.reset();
    isMaterialModalOpen.value = true;
};

const openEditMaterialModal = (mat) => {
    isEditingMaterial.value = true;
    selectedMaterialId.value = mat.id;
    matForm.title = mat.title;
    matForm.content = mat.content;
    matForm.points_reward = mat.points_reward;
    isMaterialModalOpen.value = true;
};

const submitMaterial = () => {
    if (isEditingMaterial.value) {
        matForm.put(
            route('teacher.materials.update', selectedMaterialId.value),
            {
                onSuccess: () => {
                    isMaterialModalOpen.value = false;
                    matForm.reset();
                },
            },
        );
    } else {
        matForm.post(route('teacher.materials.store', props.subject.id), {
            onSuccess: () => {
                isMaterialModalOpen.value = false;
                matForm.reset();
            },
        });
    }
};

const isConfirmDeleteMatOpen = ref(false);
const materialToDelete = ref(null);
const confirmDeleteMaterial = (id) => {
    materialToDelete.value = id;
    isConfirmDeleteMatOpen.value = true;
};
const deleteMaterial = () => {
    if (materialToDelete.value) {
        matForm.delete(
            route('teacher.materials.destroy', materialToDelete.value),
            {
                onSuccess: () => {
                    isConfirmDeleteMatOpen.value = false;
                    materialToDelete.value = null;
                },
            },
        );
    }
};

// Test CRUD
const isTestModalOpen = ref(false);
const isEditingTest = ref(false);
const selectedTestId = ref(null);
const testForm = useForm({
    title: '',
    description: '',
    points_reward: 50,
});

const openCreateTestModal = () => {
    isEditingTest.value = false;
    selectedTestId.value = null;
    testForm.reset();
    isTestModalOpen.value = true;
};

const openEditTestModal = (test) => {
    isEditingTest.value = true;
    selectedTestId.value = test.id;
    testForm.title = test.title;
    testForm.description = test.description;
    testForm.points_reward = test.points_reward;
    isTestModalOpen.value = true;
};

const submitTest = () => {
    if (isEditingTest.value) {
        testForm.put(route('teacher.tests.update', selectedTestId.value), {
            onSuccess: () => {
                isTestModalOpen.value = false;
                testForm.reset();
            },
        });
    } else {
        testForm.post(route('teacher.tests.store', props.subject.id), {
            onSuccess: () => {
                isTestModalOpen.value = false;
                testForm.reset();
            },
        });
    }
};

const isConfirmDeleteTestOpen = ref(false);
const testToDelete = ref(null);
const confirmDeleteTest = (id) => {
    testToDelete.value = id;
    isConfirmDeleteTestOpen.value = true;
};
const deleteTest = () => {
    if (testToDelete.value) {
        testForm.delete(route('teacher.tests.destroy', testToDelete.value), {
            onSuccess: () => {
                isConfirmDeleteTestOpen.value = false;
                testToDelete.value = null;
            },
        });
    }
};

// Question CRUD
const isQuestionModalOpen = ref(false);
const isEditingQuestion = ref(false);
const selectedQuestionId = ref(null);
const activeTestIdForQuestion = ref(null);
const questionForm = useForm({
    question_text: '',
    options: ['', ''],
    correct_option_index: 0,
});

const openCreateQuestionModal = (testId) => {
    isEditingQuestion.value = false;
    selectedQuestionId.value = null;
    activeTestIdForQuestion.value = testId;
    questionForm.reset();
    isQuestionModalOpen.value = true;
};

const openEditQuestionModal = (q, testId) => {
    isEditingQuestion.value = true;
    selectedQuestionId.value = q.id;
    activeTestIdForQuestion.value = testId;
    questionForm.question_text = q.question_text;
    questionForm.options = [...q.options];
    questionForm.correct_option_index = q.correct_option_index;
    isQuestionModalOpen.value = true;
};

const addOption = () => {
    questionForm.options.push('');
};

const removeOption = (index) => {
    if (questionForm.options.length > 2) {
        questionForm.options.splice(index, 1);
        if (questionForm.correct_option_index >= questionForm.options.length) {
            questionForm.correct_option_index = questionForm.options.length - 1;
        }
    }
};

const submitQuestion = () => {
    if (isEditingQuestion.value) {
        questionForm.put(
            route('teacher.questions.update', selectedQuestionId.value),
            {
                onSuccess: () => {
                    isQuestionModalOpen.value = false;
                    questionForm.reset();
                },
            },
        );
    } else {
        questionForm.post(
            route('teacher.questions.store', activeTestIdForQuestion.value),
            {
                onSuccess: () => {
                    isQuestionModalOpen.value = false;
                    questionForm.reset();
                },
            },
        );
    }
};

const isConfirmDeleteQuestionOpen = ref(false);
const questionToDelete = ref(null);
const confirmDeleteQuestion = (id) => {
    questionToDelete.value = id;
    isConfirmDeleteQuestionOpen.value = true;
};
const deleteQuestion = () => {
    if (questionToDelete.value) {
        questionForm.delete(
            route('teacher.questions.destroy', questionToDelete.value),
            {
                onSuccess: () => {
                    isConfirmDeleteQuestionOpen.value = false;
                    questionToDelete.value = null;
                },
            },
        );
    }
};
</script>

<template>
    <Head
        :title="__('teacher.subject_show.title').replace(':name', subject.name)"
    />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('teacher.dashboard')"
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
                    <p class="text-zinc-550 text-xs">
                        {{ subject.institution?.name }}
                    </p>
                </div>
            </div>
        </template>

        <div class="bg-zinc-955 min-h-[calc(100vh-64px)] py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Success Flash Messages -->
                <div
                    v-if="$page.props.flash?.success"
                    class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-400"
                >
                    {{ $page.props.flash.success }}
                </div>

                <!-- Subject Information Panel -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6"
                >
                    <h3
                        class="text-zinc-555 text-xs font-bold uppercase tracking-wider"
                    >
                        {{ __('teacher.subject_show.description_label') }}
                    </h3>
                    <p class="mt-2 text-sm text-zinc-300">
                        {{
                            subject.description ||
                            __('teacher.subject_show.no_description')
                        }}
                    </p>
                    <p class="mt-2 text-xs text-zinc-500">
                        {{ __('teacher.subject_show.estimated_duration') }}
                        <strong class="text-zinc-400">{{
                            subject.duration || 'N/A'
                        }}</strong>
                    </p>
                </div>

                <!-- AI Content Generator Panel -->
                <div
                    class="relative overflow-hidden rounded-2xl border border-indigo-500/20 bg-gradient-to-br from-indigo-950/20 via-zinc-900/50 to-zinc-900/30 p-8 shadow-xl"
                >
                    <div
                        class="absolute right-0 top-0 -mr-16 -mt-16 h-48 w-48 rounded-full bg-indigo-500/10 blur-3xl"
                    ></div>

                    <div class="relative z-10 space-y-6">
                        <div>
                            <h3
                                class="flex items-center gap-2 text-lg font-bold text-white"
                            >
                                <Sparkles class="h-5 w-5 text-indigo-400" />
                                {{ __('teacher.subject_show.ai_title') }}
                            </h3>
                            <p class="mt-1 text-sm text-zinc-400">
                                {{ __('teacher.subject_show.ai_subtitle') }}
                            </p>
                        </div>

                        <!-- Presets -->
                        <div class="flex flex-wrap gap-2">
                            <button
                                @click="selectPreset('Eloquent ORM')"
                                type="button"
                                class="rounded-xl border border-zinc-800 bg-zinc-900 px-3.5 py-1.5 text-xs font-semibold text-zinc-300 transition-all hover:border-indigo-500 hover:text-white"
                            >
                                🐘 Eloquent ORM
                            </button>
                            <button
                                @click="selectPreset('Vue Composition API')"
                                type="button"
                                class="rounded-xl border border-zinc-800 bg-zinc-900 px-3.5 py-1.5 text-xs font-semibold text-zinc-300 transition-all hover:border-indigo-500 hover:text-white"
                            >
                                🟢 Vue 3 Composition
                            </button>
                            <button
                                @click="selectPreset('Tailwind CSS')"
                                type="button"
                                class="rounded-xl border border-zinc-800 bg-zinc-900 px-3.5 py-1.5 text-xs font-semibold text-zinc-300 transition-all hover:border-indigo-500 hover:text-white"
                            >
                                🎨 Tailwind Layouts
                            </button>
                        </div>

                        <!-- Input form -->
                        <form
                            @submit.prevent="generateContent"
                            class="flex flex-col gap-3 md:flex-row"
                        >
                            <div class="flex-grow">
                                <input
                                    v-model="aiForm.theme"
                                    type="text"
                                    required
                                    :placeholder="
                                        __(
                                            'teacher.subject_show.ai_input_placeholder',
                                        )
                                    "
                                    class="w-full rounded-xl border border-zinc-800 bg-zinc-900/60 px-4 py-3 text-sm text-zinc-100 placeholder-zinc-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                />
                                <span
                                    v-if="aiForm.errors.theme"
                                    class="mt-1 block text-xs text-red-400"
                                    >{{ aiForm.errors.theme }}</span
                                >
                            </div>
                            <button
                                type="submit"
                                :disabled="aiForm.processing || isGenerating"
                                class="flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white transition-all hover:bg-indigo-500 hover:shadow-[0_0_20px_rgba(99,102,241,0.3)] disabled:opacity-50"
                            >
                                <svg
                                    v-if="isGenerating"
                                    class="-ml-1 mr-3 h-5 w-5 animate-spin text-white"
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
                                {{
                                    isGenerating
                                        ? __(
                                              'teacher.subject_show.ai_generating',
                                          )
                                        : __('teacher.subject_show.ai_generate')
                                }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Study Materials and Quizzes lists -->
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Study Materials Column -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3
                                class="flex items-center gap-2 text-lg font-bold text-white"
                            >
                                <span class="text-zinc-500">📚</span>
                                {{
                                    __(
                                        'teacher.subject_show.materials_title',
                                    ).replace(
                                        ':count',
                                        subject.study_materials?.length || 0,
                                    )
                                }}
                            </h3>
                            <button
                                @click="openCreateMaterialModal"
                                class="inline-flex items-center gap-1 rounded-xl bg-zinc-800 px-3 py-1.5 text-xs font-bold text-white transition-colors hover:bg-zinc-700"
                            >
                                <Plus class="h-3.5 w-3.5" />
                                {{ __('common.add') }}
                            </button>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="mat in subject.study_materials"
                                :key="mat.id"
                                class="flex items-center justify-between rounded-xl border border-zinc-800 bg-zinc-900/20 p-5 transition-all hover:border-zinc-800"
                            >
                                <div>
                                    <h4 class="text-sm font-bold text-zinc-100">
                                        {{ mat.title }}
                                    </h4>
                                    <p class="text-zinc-550 mt-1 text-xs">
                                        {{
                                            __(
                                                'teacher.subject_show.material_reward',
                                            ).replace(
                                                ':points',
                                                mat.points_reward,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Tooltip
                                        :text="
                                            __(
                                                'teacher.subject_show.edit_material',
                                            )
                                        "
                                    >
                                        <button
                                            @click="openEditMaterialModal(mat)"
                                            class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-800 hover:text-white"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                    </Tooltip>
                                    <Tooltip
                                        :text="
                                            __(
                                                'teacher.subject_show.delete_material',
                                            )
                                        "
                                    >
                                        <button
                                            @click="
                                                confirmDeleteMaterial(mat.id)
                                            "
                                            class="rounded-lg p-1.5 text-red-500 transition-colors hover:bg-red-500/10 hover:text-red-400"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </Tooltip>
                                </div>
                            </div>

                            <div
                                v-if="subject.study_materials?.length === 0"
                                class="rounded-xl border border-dashed border-zinc-800 p-8 text-center text-sm text-zinc-500"
                            >
                                {{ __('teacher.subject_show.materials_empty') }}
                            </div>
                        </div>
                    </div>

                    <!-- Quizzes Column -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3
                                class="flex items-center gap-2 text-lg font-bold text-white"
                            >
                                <span class="text-zinc-555">⚔️</span>
                                {{
                                    __(
                                        'teacher.subject_show.tests_title',
                                    ).replace(
                                        ':count',
                                        subject.tests?.length || 0,
                                    )
                                }}
                            </h3>
                            <button
                                @click="openCreateTestModal"
                                class="inline-flex items-center gap-1 rounded-xl bg-zinc-800 px-3 py-1.5 text-xs font-bold text-white transition-colors hover:bg-zinc-700"
                            >
                                <Plus class="h-3.5 w-3.5" />
                                {{ __('common.add') }}
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="test in subject.tests"
                                :key="test.id"
                                class="space-y-4 rounded-xl border border-zinc-800 bg-zinc-900/20 p-5"
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h4
                                            class="text-sm font-bold text-zinc-100"
                                        >
                                            {{ test.title }}
                                        </h4>
                                        <p class="mt-1 text-xs text-zinc-400">
                                            {{ test.description }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="rounded-lg bg-indigo-500/10 px-2.5 py-1 text-xs font-bold text-indigo-400"
                                        >
                                            {{
                                                __(
                                                    'teacher.subject_show.xp_max',
                                                ).replace(
                                                    ':points',
                                                    test.points_reward,
                                                )
                                            }}
                                        </span>
                                        <Tooltip
                                            :text="
                                                __(
                                                    'teacher.subject_show.edit_test',
                                                )
                                            "
                                        >
                                            <button
                                                @click="openEditTestModal(test)"
                                                class="text-zinc-450 rounded-lg p-1 hover:bg-zinc-800 hover:text-white"
                                            >
                                                <Pencil class="h-3.5 w-3.5" />
                                            </button>
                                        </Tooltip>
                                        <Tooltip
                                            :text="
                                                __(
                                                    'teacher.subject_show.delete_test',
                                                )
                                            "
                                        >
                                            <button
                                                @click="
                                                    confirmDeleteTest(test.id)
                                                "
                                                class="rounded-lg p-1 text-red-500 transition-colors hover:bg-red-500/10 hover:text-red-400"
                                            >
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </button>
                                        </Tooltip>
                                    </div>
                                </div>

                                <!-- Questions Sublist -->
                                <div class="border-t border-zinc-800 pt-3">
                                    <div
                                        class="mb-2 flex items-center justify-between"
                                    >
                                        <h5
                                            class="text-zinc-550 text-xs font-bold"
                                        >
                                            {{
                                                __(
                                                    'teacher.subject_show.questions_count',
                                                ).replace(
                                                    ':count',
                                                    test.questions?.length || 0,
                                                )
                                            }}
                                        </h5>
                                        <button
                                            @click="
                                                openCreateQuestionModal(test.id)
                                            "
                                            class="inline-flex items-center gap-1 rounded bg-zinc-800 px-2 py-0.5 text-[10px] font-bold text-zinc-300 transition-colors hover:bg-zinc-700"
                                        >
                                            <Plus class="h-3 w-3" />
                                            {{
                                                __(
                                                    'teacher.subject_show.add_question',
                                                )
                                            }}
                                        </button>
                                    </div>
                                    <ol
                                        class="list-inside list-decimal space-y-3 text-xs"
                                    >
                                        <li
                                            v-for="q in test.questions"
                                            :key="q.id"
                                            class="text-zinc-300"
                                        >
                                            <span class="font-medium">{{
                                                q.question_text
                                            }}</span>
                                            <div
                                                class="ml-2 inline-flex items-center gap-1"
                                            >
                                                <button
                                                    @click="
                                                        openEditQuestionModal(
                                                            q,
                                                            test.id,
                                                        )
                                                    "
                                                    class="text-zinc-550 p-0.5 hover:text-zinc-300"
                                                >
                                                    <Pencil class="h-3 w-3" />
                                                </button>
                                                <button
                                                    @click="
                                                        confirmDeleteQuestion(
                                                            q.id,
                                                        )
                                                    "
                                                    class="p-0.5 text-red-500 hover:text-red-400"
                                                >
                                                    <Trash2 class="h-3 w-3" />
                                                </button>
                                            </div>
                                            <div
                                                class="ml-4 mt-1 flex flex-wrap gap-2"
                                            >
                                                <span
                                                    v-for="(
                                                        opt, oIdx
                                                    ) in q.options"
                                                    :key="oIdx"
                                                    class="rounded px-2 py-0.5 text-[10px]"
                                                    :class="
                                                        oIdx ===
                                                        q.correct_option_index
                                                            ? 'border border-emerald-500/20 bg-emerald-500/10 font-semibold text-emerald-400'
                                                            : 'text-zinc-550 border-zinc-850 border bg-zinc-950'
                                                    "
                                                >
                                                    {{ opt }}
                                                </span>
                                            </div>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                            <div
                                v-if="subject.tests?.length === 0"
                                class="rounded-xl border border-dashed border-zinc-800 p-8 text-center text-sm text-zinc-500"
                            >
                                {{ __('teacher.subject_show.tests_empty') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Study Material Modal -->
        <BaseModal
            :show="isMaterialModalOpen"
            :title="
                isEditingMaterial
                    ? __('teacher.material_form.edit_title')
                    : __('teacher.material_form.new_title')
            "
            maxWidth="xl"
            @close="isMaterialModalOpen = false"
        >
            <form @submit.prevent="submitMaterial" class="space-y-4">
                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('teacher.material_form.title_label') }}</label
                    >
                    <input
                        v-model="matForm.title"
                        type="text"
                        required
                        :placeholder="
                            __('teacher.material_form.title_placeholder')
                        "
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <span
                        v-if="matForm.errors.title"
                        class="mt-1 block text-xs text-red-500"
                        >{{ matForm.errors.title }}</span
                    >
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('teacher.material_form.reward_label') }}</label
                    >
                    <input
                        v-model="matForm.points_reward"
                        type="number"
                        required
                        min="1"
                        class="bg-zinc-955 w-full rounded-xl border border-zinc-800 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <span
                        v-if="matForm.errors.points_reward"
                        class="mt-1 block text-xs text-red-500"
                        >{{ matForm.errors.points_reward }}</span
                    >
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('teacher.material_form.content_label') }}</label
                    >
                    <textarea
                        v-model="matForm.content"
                        rows="6"
                        required
                        :placeholder="
                            __('teacher.material_form.content_placeholder')
                        "
                        class="bg-zinc-955 w-full rounded-xl border border-zinc-800 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    ></textarea>
                    <span
                        v-if="matForm.errors.content"
                        class="mt-1 block text-xs text-red-500"
                        >{{ matForm.errors.content }}</span
                    >
                </div>

                <div class="flex justify-end gap-3 pt-3">
                    <button
                        type="button"
                        @click="isMaterialModalOpen = false"
                        class="rounded-xl bg-zinc-800 px-5 py-2.5 text-xs font-bold text-zinc-400 transition-colors hover:bg-zinc-700"
                    >
                        {{ __('common.cancel') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="matForm.processing"
                        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white transition-colors hover:bg-indigo-500 disabled:opacity-50"
                    >
                        {{
                            isEditingMaterial
                                ? __('teacher.material_form.save_changes')
                                : __('teacher.material_form.create')
                        }}
                    </button>
                </div>
            </form>
        </BaseModal>

        <!-- Test Modal -->
        <BaseModal
            :show="isTestModalOpen"
            :title="
                isEditingTest
                    ? __('teacher.test_form.edit_title')
                    : __('teacher.test_form.new_title')
            "
            maxWidth="xl"
            @close="isTestModalOpen = false"
        >
            <form @submit.prevent="submitTest" class="space-y-4">
                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('teacher.test_form.title_label') }}</label
                    >
                    <input
                        v-model="testForm.title"
                        type="text"
                        required
                        :placeholder="__('teacher.test_form.title_placeholder')"
                        class="bg-zinc-955 w-full rounded-xl border border-zinc-800 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <span
                        v-if="testForm.errors.title"
                        class="mt-1 block text-xs text-red-500"
                        >{{ testForm.errors.title }}</span
                    >
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('teacher.test_form.reward_label') }}</label
                    >
                    <input
                        v-model="testForm.points_reward"
                        type="number"
                        required
                        min="1"
                        class="bg-zinc-955 w-full rounded-xl border border-zinc-800 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <span
                        v-if="testForm.errors.points_reward"
                        class="mt-1 block text-xs text-red-500"
                        >{{ testForm.errors.points_reward }}</span
                    >
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('teacher.test_form.description_label') }}</label
                    >
                    <textarea
                        v-model="testForm.description"
                        rows="3"
                        required
                        :placeholder="
                            __('teacher.test_form.description_placeholder')
                        "
                        class="bg-zinc-955 w-full rounded-xl border border-zinc-800 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    ></textarea>
                    <span
                        v-if="testForm.errors.description"
                        class="mt-1 block text-xs text-red-500"
                        >{{ testForm.errors.description }}</span
                    >
                </div>

                <div class="flex justify-end gap-3 pt-3">
                    <button
                        type="button"
                        @click="isTestModalOpen = false"
                        class="rounded-xl bg-zinc-800 px-5 py-2.5 text-xs font-bold text-zinc-400 transition-colors hover:bg-zinc-700"
                    >
                        {{ __('common.cancel') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="testForm.processing"
                        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white transition-colors hover:bg-indigo-500 disabled:opacity-50"
                    >
                        {{
                            isEditingTest
                                ? __('teacher.test_form.save_changes')
                                : __('teacher.test_form.create')
                        }}
                    </button>
                </div>
            </form>
        </BaseModal>

        <!-- Question Modal -->
        <BaseModal
            :show="isQuestionModalOpen"
            :title="
                isEditingQuestion
                    ? __('teacher.question_form.edit_title')
                    : __('teacher.question_form.new_title')
            "
            maxWidth="xl"
            @close="isQuestionModalOpen = false"
        >
            <form @submit.prevent="submitQuestion" class="space-y-4">
                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('teacher.question_form.text_label') }}</label
                    >
                    <textarea
                        v-model="questionForm.question_text"
                        rows="3"
                        required
                        :placeholder="
                            __('teacher.question_form.text_placeholder')
                        "
                        class="bg-zinc-955 w-full rounded-xl border border-zinc-800 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    ></textarea>
                    <span
                        v-if="questionForm.errors.question_text"
                        class="mt-1 block text-xs text-red-500"
                        >{{ questionForm.errors.question_text }}</span
                    >
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label
                            class="block text-xs font-bold uppercase text-zinc-400"
                            >{{
                                __('teacher.question_form.options_label')
                            }}</label
                        >
                        <button
                            type="button"
                            @click="addOption"
                            class="text-[10px] font-bold text-indigo-400 hover:text-indigo-300"
                        >
                            {{ __('teacher.question_form.add_option') }}
                        </button>
                    </div>
                    <div class="space-y-2">
                        <div
                            v-for="(opt, idx) in questionForm.options"
                            :key="idx"
                            class="flex items-center gap-2"
                        >
                            <input
                                type="radio"
                                :value="idx"
                                v-model="questionForm.correct_option_index"
                                class="h-4 w-4 border-zinc-800 bg-zinc-950 text-indigo-600 focus:ring-indigo-500"
                            />
                            <input
                                v-model="questionForm.options[idx]"
                                type="text"
                                required
                                :placeholder="
                                    __(
                                        'teacher.question_form.option_placeholder',
                                    ).replace(':number', idx + 1)
                                "
                                class="bg-zinc-955 flex-grow rounded-xl border border-zinc-800 px-4 py-2 text-xs text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            />
                            <button
                                v-if="questionForm.options.length > 2"
                                type="button"
                                @click="removeOption(idx)"
                                class="p-1 text-red-500 hover:text-red-400"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                    <span
                        v-if="questionForm.errors.options"
                        class="mt-1 block text-xs text-red-500"
                        >{{ questionForm.errors.options }}</span
                    >
                    <p class="mt-2 text-[10px] text-zinc-500">
                        {{ __('teacher.question_form.correct_hint') }}
                    </p>
                </div>

                <div class="flex justify-end gap-3 pt-3">
                    <button
                        type="button"
                        @click="isQuestionModalOpen = false"
                        class="rounded-xl bg-zinc-800 px-5 py-2.5 text-xs font-bold text-zinc-400 transition-colors hover:bg-zinc-700"
                    >
                        {{ __('common.cancel') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="questionForm.processing"
                        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white transition-colors hover:bg-indigo-500 disabled:opacity-50"
                    >
                        {{
                            isEditingQuestion
                                ? __('teacher.question_form.save_changes')
                                : __('teacher.question_form.create')
                        }}
                    </button>
                </div>
            </form>
        </BaseModal>

        <!-- Deletion Confirmation Modals -->
        <ConfirmModal
            :show="isConfirmDeleteMatOpen"
            :title="__('teacher.material_delete.title')"
            :message="__('teacher.material_delete.message')"
            @close="isConfirmDeleteMatOpen = false"
            @confirm="deleteMaterial"
        />

        <ConfirmModal
            :show="isConfirmDeleteTestOpen"
            :title="__('teacher.test_delete.title')"
            :message="__('teacher.test_delete.message')"
            @close="isConfirmDeleteTestOpen = false"
            @confirm="deleteTest"
        />

        <ConfirmModal
            :show="isConfirmDeleteQuestionOpen"
            :title="__('teacher.question_delete.title')"
            :message="__('teacher.question_delete.message')"
            @close="isConfirmDeleteQuestionOpen = false"
            @confirm="deleteQuestion"
        />
    </AuthenticatedLayout>
</template>
