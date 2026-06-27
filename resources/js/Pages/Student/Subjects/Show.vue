<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    subject: {
        type: Object,
        required: true
    },
    materials: {
        type: Array,
        default: () => []
    },
    tests: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <Head :title="subject.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('dashboard')"
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
                    <p class="text-xs text-zinc-500">Trilha de Aprendizado</p>
                </div>
            </div>
        </template>

        <div class="py-12 bg-zinc-955 min-h-[calc(100vh-64px)] text-zinc-100">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-10">
                
                <!-- Informações Flash -->
                <div v-if="$page.props.flash?.success" class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-400">
                    {{ $page.props.flash.success }}
                </div>

                <!-- Resumo da Matéria -->
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900/20 p-6">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500 mb-1">Ementa / Detalhes</h3>
                    <p class="text-sm text-zinc-300 leading-relaxed">
                        {{ subject.description || 'Sem descrição cadastrada.' }}
                    </p>
                </div>

                <!-- Bloco de Conteúdo (Materiais e Atividades Combinados em uma Timeline) -->
                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="text-indigo-400">🔥</span> Sua Jornada de Aprendizado
                    </h3>

                    <div class="relative pl-6 border-l border-zinc-800 space-y-8 ml-3">
                        
                        <!-- Materiais de Estudo na Timeline -->
                        <div
                            v-for="(material, index) in materials"
                            :key="`mat-${material.id}`"
                            class="relative"
                        >
                            <!-- Indicador na Linha da Timeline -->
                            <div
                                class="absolute -left-[31px] top-1 flex h-4 w-4 items-center justify-center rounded-full border-2 transition-all duration-300"
                                :class="material.completed ? 'bg-emerald-500 border-emerald-400 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-zinc-950 border-zinc-800'"
                            ></div>

                            <div
                                class="rounded-xl border border-zinc-800 p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transition-all duration-200"
                                :class="material.completed ? 'bg-zinc-900/10 border-zinc-800/60' : 'bg-zinc-900/40 hover:bg-zinc-900/70'"
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Passo {{ index + 1 }}: Leitura</span>
                                        <span v-if="material.completed" class="text-emerald-400 text-xs font-bold flex items-center gap-0.5">
                                            ✓ Lido
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-base text-white mt-1">{{ material.title }}</h4>
                                    <p class="text-xs text-zinc-500 mt-0.5">Recompensa: +{{ material.points_reward }} pontos XP</p>
                                </div>

                                <Link
                                    :href="route('student.materials.show', [subject.id, material.id])"
                                    class="rounded-xl px-4 py-2 text-xs font-bold transition-all duration-150"
                                    :class="material.completed ? 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700 hover:text-white' : 'bg-indigo-600 text-white hover:bg-indigo-500'"
                                >
                                    {{ material.completed ? 'Rever Material' : 'Iniciar Leitura' }}
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
                                :class="test.best_score !== null ? 'bg-yellow-500 border-yellow-400 shadow-[0_0_8px_rgba(234,179,8,0.5)]' : 'bg-zinc-950 border-zinc-800'"
                            ></div>

                            <div class="rounded-xl border border-zinc-800 bg-zinc-900/40 p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 hover:bg-zinc-900/70 transition-all duration-200">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-yellow-500 uppercase tracking-wider">Desafio Avaliativo</span>
                                        <span v-if="test.best_score !== null" class="text-yellow-400 text-xs font-bold">
                                            ★ Realizado (Nota Máxima: {{ test.best_score }} pts)
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-base text-white mt-1">{{ test.title }}</h4>
                                    <p class="text-xs text-zinc-400 mt-1 max-w-md">{{ test.description }}</p>
                                    
                                    <!-- Extrato de acertos -->
                                    <div v-if="test.best_score !== null" class="mt-2 text-xs text-zinc-500 flex items-center gap-2">
                                        <span>Último Rendimento: <strong>{{ test.correct_answers }} / {{ test.total_questions }}</strong> acertos</span>
                                        <span class="h-1 w-1 bg-zinc-700 rounded-full"></span>
                                        <span class="text-yellow-500">XP Recebido: <strong>+{{ test.best_score }} pts</strong></span>
                                    </div>
                                </div>

                                <Link
                                    :href="route('student.tests.show', [subject.id, test.id])"
                                    class="rounded-xl px-4 py-2 text-xs font-bold transition-all duration-150"
                                    :class="test.best_score !== null ? 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700' : 'bg-yellow-600 text-zinc-950 hover:bg-yellow-500'"
                                >
                                    {{ test.best_score !== null ? 'Refazer Desafio' : 'Fazer Desafio' }}
                                </Link>
                            </div>
                        </div>

                        <div v-if="materials.length === 0 && tests.length === 0" class="rounded-xl border border-dashed border-zinc-850 p-8 text-center text-zinc-500 text-sm pl-0">
                            Nenhum material de estudo ou atividade publicada para esta trilha ainda. Retorne em breve!
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
