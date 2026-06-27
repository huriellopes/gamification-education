<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    globalRanking: {
        type: Array,
        default: () => [],
    },
    institutionRanking: {
        type: Array,
        default: () => [],
    },
    subjectRanking: {
        type: Array,
        default: () => [],
    },
    subjects: {
        type: Array,
        default: () => [],
    },
    selectedSubjectId: {
        type: Number,
        default: null,
    },
    selectedSubject: {
        type: Object,
        default: null,
    },
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

    list.forEach((user) => {
        if (user.position === 1) top3.first = user;
        else if (user.position === 2) top3.second = user;
        else if (user.position === 3) top3.third = user;
    });

    return top3;
});

// Lista dos restantes (posição 4 em diante)
const remainingRanks = computed(() => {
    return currentRanking.value.filter((user) => user.position > 3);
});

// Observa alteração de matéria para disparar filtro via Inertia
watch(localSelectedSubjectId, (newId) => {
    if (newId) {
        activeTab.value = 'subject';
        router.get(
            route('ranking.index'),
            { subject_id: newId },
            { preserveState: true },
        );
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

        <div
            class="bg-zinc-955 min-h-[calc(100vh-64px)] py-12 pb-20 text-zinc-100"
        >
            <div class="mx-auto max-w-5xl space-y-10 px-4 sm:px-6 lg:px-8">
                <!-- Abas de Navegação (Tabs Premium) -->
                <div
                    class="flex flex-col items-start justify-between gap-4 border-b border-zinc-800 pb-4 sm:flex-row sm:items-center"
                >
                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="setTab('global')"
                            class="rounded-xl px-4 py-2 text-xs font-bold transition-all"
                            :class="
                                activeTab === 'global'
                                    ? 'bg-indigo-600 text-white shadow-[0_0_15px_rgba(99,102,241,0.3)]'
                                    : 'border border-zinc-800 bg-zinc-900 text-zinc-400 hover:text-white'
                            "
                        >
                            Global
                        </button>
                        <button
                            v-if="$page.props.auth.user.institution_id"
                            @click="setTab('institution')"
                            class="rounded-xl px-4 py-2 text-xs font-bold transition-all"
                            :class="
                                activeTab === 'institution'
                                    ? 'bg-indigo-600 text-white shadow-[0_0_15px_rgba(99,102,241,0.3)]'
                                    : 'border border-zinc-800 bg-zinc-900 text-zinc-400 hover:text-white'
                            "
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
                            <option
                                v-for="subj in subjects"
                                :key="subj.id"
                                :value="subj.id"
                            >
                                {{ subj.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Título Informativo do Ranking Selecionado -->
                <div class="text-center">
                    <h3
                        class="text-xl font-extrabold tracking-tight text-white"
                    >
                        <span v-if="activeTab === 'global'"
                            >🏆 Classificação Geral</span
                        >
                        <span v-else-if="activeTab === 'institution'"
                            >🏫 Ranking da
                            {{ $page.props.auth.user.institution?.name }}</span
                        >
                        <span v-else-if="activeTab === 'subject'"
                            >📚 Desempenho em: {{ selectedSubject?.name }}</span
                        >
                    </h3>
                    <p class="mt-1 text-xs text-zinc-500">
                        Os melhores estudantes baseados em XP e desempenho.
                    </p>
                </div>

                <div
                    v-if="currentRanking.length === 0"
                    class="border-zinc-850 rounded-2xl border border-dashed p-16 text-center text-sm text-zinc-500"
                >
                    Nenhuma pontuação registrada nesta modalidade ainda.
                </div>

                <div v-else class="space-y-12">
                    <!-- PODIUM 3D / VISUAL (Exibe apenas se tiver posições de pódio) -->
                    <div
                        v-if="podium.first || podium.second || podium.third"
                        class="mx-auto flex min-h-[300px] max-w-2xl items-end justify-center gap-4 pt-8 sm:gap-8"
                    >
                        <!-- 2º LUGAR (Prata) -->
                        <div
                            v-if="podium.second"
                            class="flex w-28 flex-col items-center text-center sm:w-36"
                        >
                            <span class="mb-1 text-4xl">🥈</span>
                            <div
                                class="w-full truncate px-1 text-xs font-bold text-zinc-200"
                            >
                                {{ podium.second.name }}
                            </div>
                            <div
                                class="mb-2 w-full truncate text-[9px] text-zinc-500"
                            >
                                {{ podium.second.institution }}
                            </div>
                            <!-- Pilar -->
                            <div
                                class="flex h-28 w-full flex-col items-center justify-center rounded-t-2xl border-x border-t border-zinc-700/50 bg-gradient-to-t from-zinc-900 to-zinc-800 shadow-lg"
                            >
                                <span class="text-2xl font-black text-zinc-400"
                                    >2º</span
                                >
                                <span
                                    class="mt-1 text-[10px] font-bold text-yellow-500"
                                    >{{
                                        podium.second.points.toLocaleString()
                                    }}
                                    XP</span
                                >
                            </div>
                        </div>
                        <div v-else class="w-28 sm:w-36"></div>

                        <!-- 1º LUGAR (Ouro) -->
                        <div
                            v-if="podium.first"
                            class="flex w-32 flex-col items-center text-center sm:w-44"
                        >
                            <span class="mb-2 animate-bounce text-5xl">🥇</span>
                            <div
                                class="w-full truncate px-1 text-sm font-black text-white"
                            >
                                {{ podium.first.name }}
                            </div>
                            <div
                                class="mb-3 w-full truncate text-[9px] text-indigo-400"
                            >
                                {{ podium.first.institution }}
                            </div>
                            <!-- Pilar -->
                            <div
                                class="relative flex h-40 w-full flex-col items-center justify-center rounded-t-2xl border-x border-t border-indigo-500/20 border-indigo-500/30 bg-gradient-to-t from-indigo-950/80 to-indigo-900 shadow-2xl"
                            >
                                <div
                                    class="absolute inset-0 animate-pulse rounded-t-2xl bg-yellow-500/5"
                                ></div>
                                <span
                                    class="relative z-10 text-3xl font-black text-yellow-400"
                                    >1º</span
                                >
                                <span
                                    class="relative z-10 mt-1 text-xs font-extrabold text-yellow-400"
                                    >{{
                                        podium.first.points.toLocaleString()
                                    }}
                                    XP</span
                                >
                            </div>
                        </div>

                        <!-- 3º LUGAR (Bronze) -->
                        <div
                            v-if="podium.third"
                            class="flex w-28 flex-col items-center text-center sm:w-36"
                        >
                            <span class="mb-1 text-4xl">🥉</span>
                            <div
                                class="w-full truncate px-1 text-xs font-bold text-zinc-200"
                            >
                                {{ podium.third.name }}
                            </div>
                            <div
                                class="mb-2 w-full truncate text-[9px] text-zinc-500"
                            >
                                {{ podium.third.institution }}
                            </div>
                            <!-- Pilar -->
                            <div
                                class="flex h-20 w-full flex-col items-center justify-center rounded-t-2xl border-x border-t border-zinc-800 bg-gradient-to-t from-zinc-900 to-amber-950/40 shadow-md"
                            >
                                <span class="text-2xl font-black text-amber-600"
                                    >3º</span
                                >
                                <span
                                    class="mt-1 text-[10px] font-bold text-yellow-500"
                                    >{{
                                        podium.third.points.toLocaleString()
                                    }}
                                    XP</span
                                >
                            </div>
                        </div>
                        <div v-else class="w-28 sm:w-36"></div>
                    </div>

                    <!-- TABELA PARA O RESTO DOS INTEGRANTES (Posição 4 em diante) -->
                    <div
                        v-if="remainingRanks.length > 0"
                        class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/30 shadow-xl"
                    >
                        <table class="min-w-full text-left text-sm">
                            <thead
                                class="border-zinc-850 border-b bg-zinc-900/60 text-xs font-bold uppercase tracking-wider text-zinc-500"
                            >
                                <tr>
                                    <th scope="col" class="px-6 py-4">
                                        Posição
                                    </th>
                                    <th scope="col" class="px-6 py-4">
                                        Estudante
                                    </th>
                                    <th scope="col" class="px-6 py-4">
                                        Instituição
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-4 text-right"
                                    >
                                        Pontuação (XP)
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-zinc-850/50 divide-y text-zinc-300"
                            >
                                <tr
                                    v-for="user in remainingRanks"
                                    :key="user.position"
                                    class="transition-colors hover:bg-zinc-800/10"
                                >
                                    <td
                                        class="whitespace-nowrap px-6 py-4 font-bold text-zinc-400"
                                    >
                                        #{{ user.position }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 font-semibold text-white"
                                    >
                                        {{ user.name }}
                                    </td>
                                    <td
                                        class="text-zinc-450 whitespace-nowrap px-6 py-4"
                                    >
                                        {{ user.institution }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-right font-bold text-yellow-400"
                                    >
                                        {{ user.points.toLocaleString() }}
                                        <span
                                            class="text-[10px] font-semibold uppercase text-zinc-500"
                                            >xp</span
                                        >
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
