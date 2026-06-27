<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    subject: {
        type: Object,
        required: true
    }
});

const isGenerating = ref(false);

const form = useForm({
    theme: ''
});

const selectPreset = (theme) => {
    form.theme = theme;
};

const generateContent = () => {
    isGenerating.value = true;
    form.post(route('admin.subjects.generate', props.subject.id), {
        onFinish: () => {
            isGenerating.value = false;
        },
        onSuccess: () => {
            form.reset();
        }
    });
};
</script>

<template>
    <Head :title="`Gerenciar - ${subject.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.subjects.index')"
                    class="text-zinc-400 hover:text-white transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <div>
                    <h2 class="text-xl font-bold leading-tight text-zinc-100">
                        {{ subject.name }}
                    </h2>
                    <p class="text-xs text-zinc-500">{{ subject.institution?.name }}</p>
                </div>
            </div>
        </template>

        <div class="py-12 bg-zinc-955 min-h-[calc(100vh-64px)] text-zinc-100">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">
                
                <!-- Mensagem de Sucesso (Toast/Flash) -->
                <div v-if="$page.props.flash?.success" class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-400">
                    {{ $page.props.flash.success }}
                </div>

                <!-- Painel Informativo da Matéria -->
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500">Descrição da Matéria</h3>
                    <p class="mt-2 text-zinc-300 text-sm">
                        {{ subject.description || 'Sem descrição cadastrada para esta matéria.' }}
                    </p>
                </div>

                <!-- Bloco do Gerador de Conteúdo Inteligente (AI Simulator) -->
                <div class="relative overflow-hidden rounded-2xl border border-indigo-500/20 bg-gradient-to-br from-indigo-950/20 via-zinc-900/50 to-zinc-900/30 p-8 shadow-xl">
                    <div class="absolute top-0 right-0 -mr-16 -mt-16 h-48 w-48 rounded-full bg-indigo-500/10 blur-3xl"></div>
                    
                    <div class="relative z-10 space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded bg-indigo-500/20 text-indigo-400 text-xs">✨</span>
                                Gerador de Conteúdo Educacional por Tema
                            </h3>
                            <p class="mt-1 text-sm text-zinc-400">
                                Digite um tema de estudo ou selecione um atalho abaixo. Nosso sistema gerará automaticamente um material de leitura completo e um questionário valendo pontuação para os alunos.
                            </p>
                        </div>

                        <!-- Temas Pré-definidos -->
                        <div class="flex flex-wrap gap-2">
                            <button
                                @click="selectPreset('Eloquent ORM')"
                                type="button"
                                class="rounded-xl border border-zinc-800 bg-zinc-900 px-3.5 py-1.5 text-xs font-semibold text-zinc-300 hover:border-indigo-500 hover:text-white transition-all"
                            >
                                🐘 Eloquent ORM
                            </button>
                            <button
                                @click="selectPreset('Vue Composition API')"
                                type="button"
                                class="rounded-xl border border-zinc-800 bg-zinc-900 px-3.5 py-1.5 text-xs font-semibold text-zinc-300 hover:border-indigo-500 hover:text-white transition-all"
                            >
                                🟢 Vue 3 Composition
                            </button>
                            <button
                                @click="selectPreset('Tailwind CSS')"
                                type="button"
                                class="rounded-xl border border-zinc-800 bg-zinc-900 px-3.5 py-1.5 text-xs font-semibold text-zinc-300 hover:border-indigo-500 hover:text-white transition-all"
                            >
                                🎨 Tailwind Layouts
                            </button>
                        </div>

                        <!-- Input de Geração -->
                        <form @submit.prevent="generateContent" class="flex flex-col md:flex-row gap-3">
                            <div class="flex-grow">
                                <input
                                    v-model="form.theme"
                                    type="text"
                                    required
                                    placeholder="Ex: Introdução ao Docker, Git & Github, REST APIs..."
                                    class="w-full rounded-xl border border-zinc-800 bg-zinc-900/60 px-4 py-3 text-sm text-zinc-100 placeholder-zinc-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                />
                                <span v-if="form.errors.theme" class="text-xs text-red-400 block mt-1">{{ form.errors.theme }}</span>
                            </div>
                            <button
                                type="submit"
                                :disabled="form.processing || isGenerating"
                                class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white transition-all hover:bg-indigo-500 hover:shadow-[0_0_20px_rgba(99,102,241,0.3)] disabled:opacity-50 flex items-center justify-center gap-2"
                            >
                                <svg v-if="isGenerating" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ isGenerating ? 'Gerando Conteúdo...' : 'Gerar Material e Atividade' }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Listagem do Conteúdo Gerado -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <!-- Coluna de Materiais de Estudo -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="text-zinc-500">📚</span> Materiais de Estudo ({{ subject.study_materials?.length || 0 }})
                        </h3>

                        <div class="space-y-3">
                            <div
                                v-for="mat in subject.study_materials"
                                :key="mat.id"
                                class="rounded-xl border border-zinc-800 bg-zinc-900/20 p-5 flex justify-between items-center"
                            >
                                <div>
                                    <h4 class="font-bold text-sm text-zinc-100">{{ mat.title }}</h4>
                                    <p class="text-xs text-zinc-500 mt-1">Concede +{{ mat.points_reward }} pontos por leitura</p>
                                </div>
                                <span class="rounded bg-indigo-500/10 px-2 py-1 text-[10px] font-bold text-indigo-400 border border-indigo-500/20">
                                    {{ mat.points_reward }} pts
                                </span>
                            </div>
                            <div v-if="subject.study_materials?.length === 0" class="rounded-xl border border-dashed border-zinc-800 p-8 text-center text-zinc-500 text-sm">
                                Nenhum material de estudo gerado para esta matéria ainda.
                            </div>
                        </div>
                    </div>

                    <!-- Coluna de Atividades e Testes -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="text-zinc-500">📝</span> Atividades Avaliativas ({{ subject.tests?.length || 0 }})
                        </h3>

                        <div class="space-y-4">
                            <div
                                v-for="test in subject.tests"
                                :key="test.id"
                                class="rounded-xl border border-zinc-800 bg-zinc-900/20 p-5 space-y-3"
                            >
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h4 class="font-bold text-sm text-zinc-100">{{ test.title }}</h4>
                                        <p class="text-xs text-zinc-500 mt-1">{{ test.description }}</p>
                                    </div>
                                    <span class="rounded bg-yellow-500/10 px-2 py-1 text-[10px] font-bold text-yellow-400 border border-yellow-500/20">
                                        {{ test.points_reward }} pts
                                    </span>
                                </div>

                                <!-- Questões -->
                                <div class="border-t border-zinc-800/80 pt-3 space-y-2.5">
                                    <h5 class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Questões do Teste</h5>
                                    <ol class="list-decimal list-inside text-xs space-y-2 text-zinc-450">
                                        <li v-for="q in test.questions" :key="q.id" class="pl-1">
                                            <span class="font-medium text-zinc-300">{{ q.question_text }}</span>
                                            <div class="mt-1 ml-4 flex flex-wrap gap-2">
                                                <span
                                                    v-for="(opt, oIdx) in q.options"
                                                    :key="oIdx"
                                                    class="px-2 py-0.5 rounded text-[10px]"
                                                    :class="oIdx === q.correct_option_index ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-semibold' : 'bg-zinc-950 text-zinc-550 border border-zinc-850'"
                                                >
                                                    {{ opt }}
                                                </span>
                                            </div>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                            <div v-if="subject.tests?.length === 0" class="rounded-xl border border-dashed border-zinc-800 p-8 text-center text-zinc-500 text-sm">
                                Nenhum teste ou atividade gerada para esta matéria ainda.
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
