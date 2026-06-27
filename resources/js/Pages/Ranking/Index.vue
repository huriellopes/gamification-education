<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    globalRanking: {
        type: Array,
        default: () => []
    },
    institutionRanking: {
        type: Array,
        default: () => []
    },
    subjectRanking: {
        type: Array,
        default: () => []
    },
    subjects: {
        type: Array,
        default: () => []
    },
    selectedSubjectId: {
        type: Number,
        default: null
    },
    selectedSubject: {
        type: Object,
        default: null
    }
});

// Abas de ranking: 'global', 'institution', 'subject'
const activeTab = ref(props.selectedSubjectId ? 'subject' : 'global');
const localSelectedSubjectId = ref(props.selectedSubjectId || '');

// Filtra a lista de acordo com a aba selecionada
const currentRanking = computed(() => {
    if (activeTab.value === 'institution') return props.institutionRanking;
    if (activeTab.value === 'subject') return props.subjectRanking;
    return props.globalRanking;
});

// Pega os 3 melhores do ranking atual para montar o Pódio
const podium = computed(() => {
    const list = currentRanking.value;
    const top3 = { first: null, second: null, third: null };
    
    list.forEach(user => {
        if (user.position === 1) top3.first = user;
        else if (user.position === 2) top3.second = user;
        else if (user.position === 3) top3.third = user;
    });
    
    return top3;
});

// Lista dos restantes (posição 4 em diante)
const remainingRanks = computed(() => {
    return currentRanking.value.filter(user => user.position > 3);
});

// Observa alteração de matéria para disparar filtro via Inertia
watch(localSelectedSubjectId, (newId) => {
    if (newId) {
        activeTab.value = 'subject';
        router.get(route('ranking.index'), { subject_id: newId }, { preserveState: true });
    }
});

// Navegação entre abas
const setTab = (tabName) => {
    activeTab.value = tabName;
    if (tabName !== 'subject') {
        localSelectedSubjectId.value = '';
        // Limpa o filtro de matéria da URL
        router.get(route('ranking.index'), {}, { preserveState: true });
    }
};
</script>

<template>
    <Head title="Ranking e Classificação" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-zinc-100">
                Arena de Competição — Leaderboard
            </h2>
        </template>

        <div class="py-12 bg-zinc-955 min-h-[calc(100vh-64px)] text-zinc-100 pb-20">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-10">
                
                <!-- Abas de Navegação (Tabs Premium) -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-zinc-800 pb-4 gap-4">
                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="setTab('global')"
                            class="rounded-xl px-4 py-2 text-xs font-bold transition-all"
                            :class="activeTab === 'global' ? 'bg-indigo-600 text-white shadow-[0_0_15px_rgba(99,102,241,0.3)]' : 'bg-zinc-900 text-zinc-400 border border-zinc-800 hover:text-white'"
                        >
                            Global
                        </button>
                        <button
                            v-if="$page.props.auth.user.institution_id"
                            @click="setTab('institution')"
                            class="rounded-xl px-4 py-2 text-xs font-bold transition-all"
                            :class="activeTab === 'institution' ? 'bg-indigo-600 text-white shadow-[0_0_15px_rgba(99,102,241,0.3)]' : 'bg-zinc-900 text-zinc-400 border border-zinc-800 hover:text-white'"
                        >
                            Minha Instituição
                        </button>
                    </div>

                    <!-- Filtro por Matéria -->
                    <div class="w-full sm:w-64">
                        <select
                            v-model="localSelectedSubjectId"
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-2 text-xs text-zinc-300 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                            <option value="">Filtrar por Matéria...</option>
                            <option v-for="subj in subjects" :key="subj.id" :value="subj.id">
                                {{ subj.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Título Informativo do Ranking Selecionado -->
                <div class="text-center">
                    <h3 class="text-xl font-extrabold text-white tracking-tight">
                        <span v-if="activeTab === 'global'">🏆 Classificação Geral</span>
                        <span v-else-if="activeTab === 'institution'">🏫 Ranking da {{ $page.props.auth.user.institution?.name }}</span>
                        <span v-else-if="activeTab === 'subject'">📚 Desempenho em: {{ selectedSubject?.name }}</span>
                    </h3>
                    <p class="text-xs text-zinc-500 mt-1">Os melhores estudantes baseados em XP e desempenho.</p>
                </div>

                <div v-if="currentRanking.length === 0" class="rounded-2xl border border-dashed border-zinc-850 p-16 text-center text-zinc-500 text-sm">
                    Nenhuma pontuação registrada nesta modalidade ainda.
                </div>

                <div v-else class="space-y-12">
                    <!-- PODIUM 3D / VISUAL (Exibe apenas se tiver posições de pódio) -->
                    <div v-if="podium.first || podium.second || podium.third" class="flex items-end justify-center gap-4 sm:gap-8 pt-8 max-w-2xl mx-auto min-h-[300px]">
                        
                        <!-- 2º LUGAR (Prata) -->
                        <div v-if="podium.second" class="flex flex-col items-center w-28 sm:w-36 text-center">
                            <span class="text-4xl mb-1">🥈</span>
                            <div class="text-xs font-bold text-zinc-200 truncate w-full px-1">{{ podium.second.name }}</div>
                            <div class="text-[9px] text-zinc-500 truncate w-full mb-2">{{ podium.second.institution }}</div>
                            <!-- Pilar -->
                            <div class="w-full bg-gradient-to-t from-zinc-900 to-zinc-800 border-x border-t border-zinc-700/50 rounded-t-2xl h-28 flex flex-col justify-center items-center shadow-lg">
                                <span class="text-2xl font-black text-zinc-400">2º</span>
                                <span class="text-[10px] font-bold text-yellow-500 mt-1">{{ podium.second.points.toLocaleString() }} XP</span>
                            </div>
                        </div>
                        <div v-else class="w-28 sm:w-36"></div>

                        <!-- 1º LUGAR (Ouro) -->
                        <div v-if="podium.first" class="flex flex-col items-center w-32 sm:w-44 text-center">
                            <span class="text-5xl mb-2 animate-bounce">🥇</span>
                            <div class="text-sm font-black text-white truncate w-full px-1">{{ podium.first.name }}</div>
                            <div class="text-[9px] text-indigo-400 truncate w-full mb-3">{{ podium.first.institution }}</div>
                            <!-- Pilar -->
                            <div class="w-full bg-gradient-to-t from-indigo-950/80 to-indigo-900 border-x border-t border-indigo-500/30 rounded-t-2xl h-40 flex flex-col justify-center items-center shadow-2xl relative border-indigo-500/20">
                                <div class="absolute inset-0 bg-yellow-500/5 rounded-t-2xl animate-pulse"></div>
                                <span class="text-3xl font-black text-yellow-400 relative z-10">1º</span>
                                <span class="text-xs font-extrabold text-yellow-400 mt-1 relative z-10">{{ podium.first.points.toLocaleString() }} XP</span>
                            </div>
                        </div>

                        <!-- 3º LUGAR (Bronze) -->
                        <div v-if="podium.third" class="flex flex-col items-center w-28 sm:w-36 text-center">
                            <span class="text-4xl mb-1">🥉</span>
                            <div class="text-xs font-bold text-zinc-200 truncate w-full px-1">{{ podium.third.name }}</div>
                            <div class="text-[9px] text-zinc-500 truncate w-full mb-2">{{ podium.third.institution }}</div>
                            <!-- Pilar -->
                            <div class="w-full bg-gradient-to-t from-zinc-900 to-amber-950/40 border-x border-t border-zinc-800 rounded-t-2xl h-20 flex flex-col justify-center items-center shadow-md">
                                <span class="text-2xl font-black text-amber-600">3º</span>
                                <span class="text-[10px] font-bold text-yellow-500 mt-1">{{ podium.third.points.toLocaleString() }} XP</span>
                            </div>
                        </div>
                        <div v-else class="w-28 sm:w-36"></div>
                    </div>

                    <!-- TABELA PARA O RESTO DOS INTEGRANTES (Posição 4 em diante) -->
                    <div v-if="remainingRanks.length > 0" class="rounded-2xl border border-zinc-800 bg-zinc-900/30 overflow-hidden shadow-xl">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-zinc-900/60 text-xs font-bold uppercase tracking-wider text-zinc-500 border-b border-zinc-850">
                                <tr>
                                    <th scope="col" class="px-6 py-4">Posição</th>
                                    <th scope="col" class="px-6 py-4">Estudante</th>
                                    <th scope="col" class="px-6 py-4">Instituição</th>
                                    <th scope="col" class="px-6 py-4 text-right">Pontuação (XP)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-850/50 text-zinc-300">
                                <tr
                                    v-for="user in remainingRanks"
                                    :key="user.position"
                                    class="transition-colors hover:bg-zinc-800/10"
                                >
                                    <td class="whitespace-nowrap px-6 py-4 font-bold text-zinc-400">#{{ user.position }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 font-semibold text-white">{{ user.name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-zinc-450">{{ user.institution }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right font-bold text-yellow-400">
                                        {{ user.points.toLocaleString() }} <span class="text-[10px] text-zinc-500 font-semibold uppercase">xp</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
