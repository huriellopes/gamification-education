<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    subject: {
        type: Object,
        required: true,
    },
});

const isGenerating = ref(false);

const form = useForm({
    theme: '',
});

const selectPreset = (theme) => {
    form.theme = theme;
};

const generateContent = () => {
    isGenerating.value = true;
    form.post(route('teacher.subjects.generate', props.subject.id), {
        onFinish: () => {
            isGenerating.value = false;
        },
        onSuccess: () => {
            form.reset();
        },
    });
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
                <!-- Mensagem de Sucesso (Toast/Flash) -->
                <div
                    v-if="$page.props.flash?.success"
                    class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-400"
                >
                    {{ $page.props.flash.success }}
                </div>

                <!-- Painel Informativo da Matéria -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6"
                >
                    <h3
                        class="text-zinc-550 text-xs font-bold uppercase tracking-wider"
                    >
                        Descrição da Matéria
                    </h3>
                    <p class="mt-2 text-sm text-zinc-300">
                        {{
                            subject.description ||
                            'Sem descrição cadastrada para esta matéria.'
                        }}
                    </p>
                </div>

                <!-- Bloco do Gerador de Conteúdo Inteligente (AI Simulator) -->
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
                                <span
                                    class="flex h-6 w-6 items-center justify-center rounded bg-indigo-500/20 text-xs text-indigo-400"
                                    >✨</span
                                >
                                Gerador de Conteúdo Educacional por Tema
                            </h3>
                            <p class="mt-1 text-sm text-zinc-400">
                                Digite um tema de estudo ou selecione um atalho
                                abaixo. Nosso sistema gerará automaticamente um
                                material de leitura completo e um questionário
                                valendo pontuação para os alunos.
                            </p>
                        </div>

                        <!-- Temas Pré-definidos -->
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

                        <!-- Input de Geração -->
                        <form
                            @submit.prevent="generateContent"
                            class="flex flex-col gap-3 md:flex-row"
                        >
                            <div class="flex-grow">
                                <input
                                    v-model="form.theme"
                                    type="text"
                                    required
                                    placeholder="Ex: Introdução ao Docker, Git & Github, REST APIs..."
                                    class="w-full rounded-xl border border-zinc-800 bg-zinc-900/60 px-4 py-3 text-sm text-zinc-100 placeholder-zinc-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                />
                                <span
                                    v-if="form.errors.theme"
                                    class="mt-1 block text-xs text-red-400"
                                    >{{ form.errors.theme }}</span
                                >
                            </div>
                            <button
                                type="submit"
                                :disabled="form.processing || isGenerating"
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

                <!-- Listagem do Conteúdo Gerado -->
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Coluna de Materiais de Estudo -->
                    <div class="space-y-4">
                        <h3
                            class="flex items-center gap-2 text-lg font-bold text-white"
                        >
                            <span class="text-zinc-500">📚</span> Materiais de
                            Estudo ({{ subject.study_materials?.length || 0 }})
                        </h3>

                        <div class="space-y-3">
                            <div
                                v-for="mat in subject.study_materials"
                                :key="mat.id"
                                class="flex items-center justify-between rounded-xl border border-zinc-800 bg-zinc-900/20 p-5"
                            >
                                <div>
                                    <h4 class="text-sm font-bold text-zinc-100">
                                        {{ mat.title }}
                                    </h4>
                                    <p class="text-zinc-550 mt-1 text-xs">
                                        Concede +{{ mat.points_reward }} pontos
                                        por leitura
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="subject.study_materials?.length === 0"
                                class="rounded-xl border border-dashed border-zinc-800 p-8 text-center text-sm text-zinc-500"
                            >
                                Nenhum material didático gerado para esta
                                matéria ainda.
                            </div>
                        </div>
                    </div>

                    <!-- Coluna de Atividades e Quizzes -->
                    <div class="space-y-4">
                        <h3
                            class="flex items-center gap-2 text-lg font-bold text-white"
                        >
                            <span class="text-zinc-550">⚔️</span> Testes e
                            Quizzes ({{ subject.tests?.length || 0 }})
                        </h3>

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
                                    <span
                                        class="rounded-lg bg-indigo-500/10 px-2.5 py-1 text-xs font-bold text-indigo-400"
                                    >
                                        +{{ test.points_reward }} XP Máx
                                    </span>
                                </div>

                                <!-- Mini-Listagem de Questões do Teste -->
                                <div class="border-t border-zinc-800 pt-3">
                                    <h5 class="text-zinc-550 text-xs font-bold">
                                        Questões ({{ test.questions?.length }})
                                    </h5>
                                    <ol
                                        class="mt-2 list-inside list-decimal space-y-2 text-xs"
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
                                Nenhum teste ou atividade gerada para esta
                                matéria ainda.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
