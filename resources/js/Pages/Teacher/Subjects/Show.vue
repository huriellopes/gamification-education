<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Pencil, Trash2, Plus, Sparkles } from '@lucide/vue';

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
        matForm.put(route('teacher.materials.update', selectedMaterialId.value), {
            onSuccess: () => {
                isMaterialModalOpen.value = false;
                matForm.reset();
            },
        });
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
        matForm.delete(route('teacher.materials.destroy', materialToDelete.value), {
            onSuccess: () => {
                isConfirmDeleteMatOpen.value = false;
                materialToDelete.value = null;
            },
        });
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
        questionForm.put(route('teacher.questions.update', selectedQuestionId.value), {
            onSuccess: () => {
                isQuestionModalOpen.value = false;
                questionForm.reset();
            },
        });
    } else {
        questionForm.post(route('teacher.questions.store', activeTestIdForQuestion.value), {
            onSuccess: () => {
                isQuestionModalOpen.value = false;
                questionForm.reset();
            },
        });
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
        questionForm.delete(route('teacher.questions.destroy', questionToDelete.value), {
            onSuccess: () => {
                isConfirmDeleteQuestionOpen.value = false;
                questionToDelete.value = null;
            },
        });
    }
};
</script>

<template>
    <Head :title="`Gerenciar - ${subject.name}`" />

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
                        Descrição da Matéria
                    </h3>
                    <p class="mt-2 text-sm text-zinc-300">
                        {{
                            subject.description ||
                            'Sem descrição cadastrada para esta matéria.'
                        }}
                    </p>
                    <p class="mt-2 text-xs text-zinc-500">
                        Duração Estimada: <strong class="text-zinc-400">{{ subject.duration || 'N/A' }}</strong>
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
                                Gerador de Conteúdo Educacional por Tema
                            </h3>
                            <p class="mt-1 text-sm text-zinc-400">
                                Digite um tema de estudo ou selecione um atalho
                                abaixo. Nosso sistema gerará automaticamente um
                                material de leitura completo e um questionário
                                valendo pontuação para os alunos.
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
                                    placeholder="Ex: Introdução ao Docker, Git & Github, REST APIs..."
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
                                        ? 'Gerando Conteúdo...'
                                        : 'Gerar Material e Atividade'
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
                                <span class="text-zinc-500">📚</span> Materiais de
                                Estudo ({{ subject.study_materials?.length || 0 }})
                            </h3>
                            <button
                                @click="openCreateMaterialModal"
                                class="inline-flex items-center gap-1 rounded-xl bg-zinc-800 hover:bg-zinc-700 px-3 py-1.5 text-xs font-bold text-white transition-colors"
                            >
                                <Plus class="h-3.5 w-3.5" /> Adicionar
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
                                        Concede +{{ mat.points_reward }} pontos por leitura
                                    </p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Tooltip text="Editar Material">
                                        <button
                                            @click="openEditMaterialModal(mat)"
                                            class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-800 hover:text-white"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                    </Tooltip>
                                    <Tooltip text="Excluir Material">
                                        <button
                                            @click="confirmDeleteMaterial(mat.id)"
                                            class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-red-500/20 hover:text-red-400"
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
                                Nenhum material didático cadastrado ainda.
                            </div>
                        </div>
                    </div>

                    <!-- Quizzes Column -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3
                                class="flex items-center gap-2 text-lg font-bold text-white"
                            >
                                <span class="text-zinc-555">⚔️</span> Testes e
                                Quizzes ({{ subject.tests?.length || 0 }})
                            </h3>
                            <button
                                @click="openCreateTestModal"
                                class="inline-flex items-center gap-1 rounded-xl bg-zinc-800 hover:bg-zinc-700 px-3 py-1.5 text-xs font-bold text-white transition-colors"
                            >
                                <Plus class="h-3.5 w-3.5" /> Adicionar
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
                                            +{{ test.points_reward }} XP Máx
                                        </span>
                                        <Tooltip text="Editar Teste">
                                            <button
                                                @click="openEditTestModal(test)"
                                                class="rounded-lg p-1 text-zinc-450 hover:bg-zinc-800 hover:text-white"
                                            >
                                                <Pencil class="h-3.5 w-3.5" />
                                            </button>
                                        </Tooltip>
                                        <Tooltip text="Excluir Teste">
                                            <button
                                                @click="confirmDeleteTest(test.id)"
                                                class="rounded-lg p-1 text-zinc-450 hover:bg-red-500/20 hover:text-red-400"
                                            >
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </button>
                                        </Tooltip>
                                    </div>
                                </div>

                                <!-- Questions Sublist -->
                                <div class="border-t border-zinc-800 pt-3">
                                    <div class="flex items-center justify-between mb-2">
                                        <h5 class="text-zinc-550 text-xs font-bold">
                                            Questões ({{ test.questions?.length || 0 }})
                                        </h5>
                                        <button
                                            @click="openCreateQuestionModal(test.id)"
                                            class="inline-flex items-center gap-1 rounded bg-zinc-800 hover:bg-zinc-700 px-2 py-0.5 text-[10px] font-bold text-zinc-300 transition-colors"
                                        >
                                            <Plus class="h-3 w-3" /> Add Questão
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
                                            <span class="font-medium">{{ q.question_text }}</span>
                                            <div class="inline-flex items-center gap-1 ml-2">
                                                <button @click="openEditQuestionModal(q, test.id)" class="text-zinc-550 hover:text-zinc-300 p-0.5">
                                                    <Pencil class="h-3 w-3" />
                                                </button>
                                                <button @click="confirmDeleteQuestion(q.id)" class="text-zinc-550 hover:text-red-400 p-0.5">
                                                    <Trash2 class="h-3 w-3" />
                                                </button>
                                            </div>
                                            <div
                                                class="ml-4 mt-1 flex flex-wrap gap-2"
                                            >
                                                <span
                                                    v-for="(opt, oIdx) in q.options"
                                                    :key="oIdx"
                                                    class="rounded px-2 py-0.5 text-[10px]"
                                                    :class="
                                                        oIdx === q.correct_option_index
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
                                Nenhum teste cadastrado ainda.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Study Material Modal -->
        <BaseModal
            :show="isMaterialModalOpen"
            :title="isEditingMaterial ? 'Editar Material de Estudo' : 'Novo Material de Estudo'"
            maxWidth="xl"
            @close="isMaterialModalOpen = false"
        >
            <form @submit.prevent="submitMaterial" class="space-y-4">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Título do Material</label>
                    <input v-model="matForm.title" type="text" required placeholder="Ex: Fundamentos de Git" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    <span v-if="matForm.errors.title" class="text-xs text-red-500 mt-1 block">{{ matForm.errors.title }}</span>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Recompensa (XP)</label>
                    <input v-model="matForm.points_reward" type="number" required min="1" class="w-full rounded-xl border border-zinc-800 bg-zinc-955 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    <span v-if="matForm.errors.points_reward" class="text-xs text-red-500 mt-1 block">{{ matForm.errors.points_reward }}</span>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Conteúdo do Material</label>
                    <textarea v-model="matForm.content" rows="6" required placeholder="Insira o texto explicativo ou tutorial..." class="w-full rounded-xl border border-zinc-800 bg-zinc-955 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                    <span v-if="matForm.errors.content" class="text-xs text-red-500 mt-1 block">{{ matForm.errors.content }}</span>
                </div>

                <div class="flex justify-end gap-3 pt-3">
                    <button type="button" @click="isMaterialModalOpen = false" class="rounded-xl bg-zinc-800 px-5 py-2.5 text-xs font-bold text-zinc-400 transition-colors hover:bg-zinc-700">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="matForm.processing" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white transition-colors hover:bg-indigo-500 disabled:opacity-50">
                        {{ isEditingMaterial ? 'Salvar Alterações' : 'Criar Material' }}
                    </button>
                </div>
            </form>
        </BaseModal>

        <!-- Test Modal -->
        <BaseModal
            :show="isTestModalOpen"
            :title="isEditingTest ? 'Editar Teste' : 'Novo Teste'"
            maxWidth="xl"
            @close="isTestModalOpen = false"
        >
            <form @submit.prevent="submitTest" class="space-y-4">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Título do Teste</label>
                    <input v-model="testForm.title" type="text" required placeholder="Ex: Quiz Prático sobre Git" class="w-full rounded-xl border border-zinc-800 bg-zinc-955 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    <span v-if="testForm.errors.title" class="text-xs text-red-500 mt-1 block">{{ testForm.errors.title }}</span>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Recompensa Máxima (XP)</label>
                    <input v-model="testForm.points_reward" type="number" required min="1" class="w-full rounded-xl border border-zinc-800 bg-zinc-955 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    <span v-if="testForm.errors.points_reward" class="text-xs text-red-500 mt-1 block">{{ testForm.errors.points_reward }}</span>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Descrição do Teste</label>
                    <textarea v-model="testForm.description" rows="3" required placeholder="Insira as instruções ou descrição deste teste..." class="w-full rounded-xl border border-zinc-800 bg-zinc-955 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                    <span v-if="testForm.errors.description" class="text-xs text-red-500 mt-1 block">{{ testForm.errors.description }}</span>
                </div>

                <div class="flex justify-end gap-3 pt-3">
                    <button type="button" @click="isTestModalOpen = false" class="rounded-xl bg-zinc-800 px-5 py-2.5 text-xs font-bold text-zinc-400 transition-colors hover:bg-zinc-700">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="testForm.processing" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white transition-colors hover:bg-indigo-500 disabled:opacity-50">
                        {{ isEditingTest ? 'Salvar Alterações' : 'Criar Teste' }}
                    </button>
                </div>
            </form>
        </BaseModal>

        <!-- Question Modal -->
        <BaseModal
            :show="isQuestionModalOpen"
            :title="isEditingQuestion ? 'Editar Questão' : 'Nova Questão'"
            maxWidth="xl"
            @close="isQuestionModalOpen = false"
        >
            <form @submit.prevent="submitQuestion" class="space-y-4">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Texto da Questão</label>
                    <textarea v-model="questionForm.question_text" rows="3" required placeholder="Digite a pergunta da questão..." class="w-full rounded-xl border border-zinc-800 bg-zinc-955 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                    <span v-if="questionForm.errors.question_text" class="text-xs text-red-500 mt-1 block">{{ questionForm.errors.question_text }}</span>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold uppercase text-zinc-400">Opções de Resposta</label>
                        <button type="button" @click="addOption" class="text-[10px] font-bold text-indigo-400 hover:text-indigo-300">
                            + Adicionar Opção
                        </button>
                    </div>
                    <div class="space-y-2">
                        <div v-for="(opt, idx) in questionForm.options" :key="idx" class="flex items-center gap-2">
                            <input type="radio" :value="idx" v-model="questionForm.correct_option_index" class="h-4 w-4 border-zinc-800 bg-zinc-950 text-indigo-600 focus:ring-indigo-500" />
                            <input v-model="questionForm.options[idx]" type="text" required :placeholder="`Opção ${idx + 1}`" class="flex-grow rounded-xl border border-zinc-800 bg-zinc-955 px-4 py-2 text-xs text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                            <button v-if="questionForm.options.length > 2" type="button" @click="removeOption(idx)" class="text-zinc-500 hover:text-red-400 p-1">
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                    <span v-if="questionForm.errors.options" class="text-xs text-red-500 mt-1 block">{{ questionForm.errors.options }}</span>
                    <p class="text-[10px] text-zinc-500 mt-2">
                        * Selecione a bolinha correspondente à opção correta.
                    </p>
                </div>

                <div class="flex justify-end gap-3 pt-3">
                    <button type="button" @click="isQuestionModalOpen = false" class="rounded-xl bg-zinc-800 px-5 py-2.5 text-xs font-bold text-zinc-400 transition-colors hover:bg-zinc-700">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="questionForm.processing" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white transition-colors hover:bg-indigo-500 disabled:opacity-50">
                        {{ isEditingQuestion ? 'Salvar Alterações' : 'Criar Questão' }}
                    </button>
                </div>
            </form>
        </BaseModal>

        <!-- Deletion Confirmation Modals -->
        <ConfirmModal
            :show="isConfirmDeleteMatOpen"
            title="Excluir Material?"
            message="Tem certeza que deseja excluir permanentemente este material de estudo?"
            @close="isConfirmDeleteMatOpen = false"
            @confirm="deleteMaterial"
        />

        <ConfirmModal
            :show="isConfirmDeleteTestOpen"
            title="Excluir Teste?"
            message="Tem certeza que deseja excluir permanentemente este teste? Todas as questões associadas e tentativas de alunos serão removidas."
            @close="isConfirmDeleteTestOpen = false"
            @confirm="deleteTest"
        />

        <ConfirmModal
            :show="isConfirmDeleteQuestionOpen"
            title="Excluir Questão?"
            message="Tem certeza que deseja remover esta questão deste quiz?"
            @close="isConfirmDeleteQuestionOpen = false"
            @confirm="deleteQuestion"
        />
    </AuthenticatedLayout>
</template>
