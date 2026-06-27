<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    subject: {
        type: Object,
        required: true
    },
    test: {
        type: Object,
        required: true
    }
});

// Respostas selecionadas: { [question_id]: selected_option_index }
const selectedAnswers = ref({});
const isSubmitting = ref(false);

const selectOption = (questionId, optionIndex) => {
    selectedAnswers.value[questionId] = optionIndex;
};

// Computa progresso das respostas
const answeredCount = computed(() => {
    return Object.keys(selectedAnswers.value).filter(id => selectedAnswers.value[id] !== null).length;
});

const isComplete = computed(() => {
    return answeredCount.value === props.test.questions.length;
});

const submitTest = () => {
    if (!isComplete.value) {
        if (!confirm('Você ainda não respondeu a todas as perguntas. Tem certeza de que deseja enviar?')) {
            return;
        }
    }

    isSubmitting.value = true;
    router.post(route('student.tests.submit', [props.subject.id, props.test.id]), {
        answers: selectedAnswers.value
    }, {
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};
</script>

<template>
    <Head :title="test.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('student.subjects.show', subject.id)"
                    class="text-zinc-400 hover:text-white transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <div>
                    <h2 class="text-xl font-bold leading-tight text-zinc-100">
                        {{ test.title }}
                    </h2>
                    <p class="text-xs text-zinc-500">Trilha: {{ subject.name }}</p>
                </div>
            </div>
        </template>

        <div class="py-12 bg-zinc-955 min-h-[calc(100vh-64px)] text-zinc-100 pb-32">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-8">
                
                <!-- Card de Instrução/Status -->
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900/20 p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1">Status da Atividade</h3>
                        <p class="text-xs text-zinc-400">Responda às questões e clique em "Enviar Atividade" para creditar seus pontos.</p>
                    </div>
                    <!-- Barra de progresso rápida -->
                    <div class="w-full md:w-48 text-xs font-semibold">
                        <div class="flex justify-between mb-1">
                            <span class="text-zinc-500">Respondido</span>
                            <span class="text-yellow-400">{{ answeredCount }} / {{ test.questions.length }}</span>
                        </div>
                        <div class="h-1.5 w-full bg-zinc-800 rounded-full overflow-hidden">
                            <div
                                class="h-full bg-yellow-500 transition-all duration-300"
                                :style="{ width: `${(answeredCount / test.questions.length) * 100}%` }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Questões List -->
                <div class="space-y-6">
                    <div
                        v-for="(q, qIdx) in test.questions"
                        :key="q.id"
                        class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 space-y-4"
                    >
                        <h4 class="text-sm font-bold text-zinc-400 uppercase tracking-wider flex gap-2">
                            <span>Questão {{ qIdx + 1 }}</span>
                        </h4>
                        <p class="text-base font-semibold text-white leading-relaxed">{{ q.question_text }}</p>

                        <!-- Opções de Múltipla Escolha -->
                        <div class="grid grid-cols-1 gap-3 pt-2">
                            <button
                                v-for="(opt, oIdx) in q.options"
                                :key="oIdx"
                                @click="selectOption(q.id, oIdx)"
                                type="button"
                                class="text-left w-full rounded-xl border p-4 text-sm font-semibold transition-all duration-150 flex items-center justify-between"
                                :class="selectedAnswers[q.id] === oIdx 
                                    ? 'border-indigo-500 bg-indigo-500/10 text-indigo-400 shadow-[0_0_15px_rgba(99,102,241,0.15)]' 
                                    : 'border-zinc-850 bg-zinc-900/40 text-zinc-300 hover:border-zinc-750 hover:bg-zinc-800/20'"
                            >
                                <span>{{ opt }}</span>
                                <!-- Bolinha Checkbox simulada -->
                                <div
                                    class="h-5 w-5 rounded-full border flex items-center justify-center shrink-0"
                                    :class="selectedAnswers[q.id] === oIdx ? 'border-indigo-500 bg-indigo-500/20' : 'border-zinc-700'"
                                >
                                    <div v-if="selectedAnswers[q.id] === oIdx" class="h-2 w-2 rounded-full bg-indigo-400"></div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Rodapé de Envio Fixo -->
        <div class="fixed bottom-0 inset-x-0 bg-zinc-900/90 border-t border-zinc-800 p-5 backdrop-blur-md z-40 flex items-center justify-between mx-auto max-w-7xl rounded-t-3xl shadow-[0_-10px_30px_rgba(0,0,0,0.5)]">
            <div class="flex flex-col">
                <span class="text-xs text-zinc-500 font-bold uppercase tracking-wider">Desafio Avaliativo</span>
                <span class="text-sm font-semibold text-zinc-200">Recompensa Máxima: <span class="text-yellow-400 font-bold">+{{ test.points_reward }} XP</span></span>
            </div>

            <div>
                <button
                    @click="submitTest"
                    :disabled="isSubmitting"
                    class="rounded-xl bg-yellow-500 hover:bg-yellow-400 hover:scale-105 hover:shadow-[0_0_20px_rgba(234,179,8,0.3)] text-zinc-950 px-6 py-3 text-xs font-extrabold transition-all duration-200 disabled:opacity-50 flex items-center gap-2"
                >
                    <svg v-if="isSubmitting" class="animate-spin h-4 w-4 text-zinc-950" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Enviar Atividade
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
