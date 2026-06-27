<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    subjects: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Painel do Professor" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-zinc-100">
                Painel do Professor — Minhas Matérias
            </h2>
        </template>

        <div class="min-h-[calc(100vh-64px)] bg-zinc-950 py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <div>
                    <h3 class="text-lg font-bold text-white">
                        Minhas Matérias Ativas
                    </h3>
                    <p class="text-sm text-zinc-400">
                        Selecione uma matéria para gerenciar os materiais de
                        estudo e questionários.
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="sub in subjects"
                        :key="sub.id"
                        class="group relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/40 p-6 transition-all duration-300 hover:border-indigo-500/50"
                    >
                        <div class="space-y-4">
                            <div>
                                <h4
                                    class="text-lg font-bold text-white transition-colors group-hover:text-indigo-400"
                                >
                                    {{ sub.name }}
                                </h4>
                                <p class="mt-1 text-xs text-zinc-500">
                                    {{
                                        sub.institution?.name ||
                                        'Minha Instituição'
                                    }}
                                </p>
                            </div>
                            <p class="line-clamp-3 text-sm text-zinc-400">
                                {{
                                    sub.description ||
                                    'Sem descrição cadastrada.'
                                }}
                            </p>
                            <div
                                class="flex items-center gap-4 text-xs font-semibold text-zinc-500"
                            >
                                <span class="flex items-center gap-1">
                                    📚 {{ sub.study_materials_count }} Materiais
                                </span>
                                <span class="flex items-center gap-1">
                                    ⚔️ {{ sub.tests_count }} Quizzes
                                </span>
                            </div>
                            <div class="pt-2">
                                <Link
                                    :href="
                                        route('teacher.subjects.show', sub.id)
                                    "
                                    class="hover:bg-indigo-650 inline-flex w-full justify-center rounded-xl bg-zinc-800 px-4 py-2.5 text-xs font-bold text-white transition-colors"
                                >
                                    Gerenciar Conteúdo &rarr;
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div
                        v-if="subjects.length === 0"
                        class="col-span-full py-12 text-center text-zinc-500"
                    >
                        Você não está associado a nenhuma matéria no momento.
                        Entre em contato com o administrador da sua instituição.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
