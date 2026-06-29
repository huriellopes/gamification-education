<script setup>
import { __ } from '@/i18n';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    subject: {
        type: Object,
        required: true,
    },
});

const progressColor = computed(() => {
    const percentage = props.subject.progress_percentage || 0;
    if (percentage === 100)
        return 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.3)]';
    if (percentage > 50) return 'bg-indigo-500';
    if (percentage > 0) return 'bg-blue-500';
    return 'bg-zinc-700';
});
</script>

<template>
    <div
        class="group relative rounded-2xl border border-zinc-800 bg-zinc-900/40 p-6 transition-all duration-300 hover:-translate-y-1 hover:border-zinc-700 hover:bg-zinc-900/80 hover:shadow-[0_10px_30px_-10px_rgba(0,0,0,0.5)]"
    >
        <!-- Detalhe decorativo de gradiente superior -->
        <div
            class="absolute inset-x-0 -top-px h-px bg-gradient-to-r from-transparent via-indigo-500/50 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
        ></div>

        <div class="flex h-full flex-col justify-between">
            <div>
                <h4
                    class="text-lg font-bold text-white transition-colors duration-200 group-hover:text-indigo-400"
                >
                    {{ subject.name }}
                </h4>
                <p class="mt-2 line-clamp-2 text-sm text-zinc-400">
                    {{
                        subject.description || __('misc.subject.no_description')
                    }}
                </p>
            </div>

            <div class="mt-6 space-y-4">
                <!-- Barra de Progresso de Leitura -->
                <div>
                    <div class="mb-1 flex justify-between text-xs">
                        <span class="text-zinc-500">{{
                            __('misc.subject.materials_read')
                        }}</span>
                        <span class="font-bold text-zinc-300"
                            >{{ subject.completed_materials }} /
                            {{ subject.total_materials }}</span
                        >
                    </div>
                    <div
                        class="h-2 w-full overflow-hidden rounded-full bg-zinc-800"
                    >
                        <div
                            class="h-full rounded-full transition-all duration-500"
                            :class="progressColor"
                            :style="{
                                width: `${subject.progress_percentage}%`,
                            }"
                        ></div>
                    </div>
                </div>

                <!-- Estatísticas de Atividade -->
                <div
                    class="border-zinc-850 flex items-center justify-between border-t pt-3 text-xs"
                >
                    <div class="flex items-center gap-1.5 text-zinc-500">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            class="h-4 w-4 text-emerald-500"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                            />
                        </svg>
                        {{ __('misc.subject.best_score') }}
                    </div>
                    <span
                        class="rounded px-1.5 py-0.5 font-bold"
                        :class="
                            subject.best_test_score > 0
                                ? subject.best_test_score >= 40
                                    ? 'bg-emerald-500/10 text-emerald-400'
                                    : 'bg-yellow-500/10 text-yellow-400'
                                : 'text-zinc-500'
                        "
                    >
                        {{
                            subject.best_test_score > 0
                                ? `${subject.best_test_score} pts`
                                : __('misc.subject.pending')
                        }}
                    </span>
                </div>

                <!-- Botão de Acesso -->
                <Link
                    :href="route('student.subjects.show', subject.id)"
                    class="hover:bg-indigo-650 mt-2 block w-full rounded-xl bg-zinc-800 py-2.5 text-center text-xs font-bold text-white transition-all duration-200 hover:shadow-[0_0_15px_rgba(99,102,241,0.2)]"
                >
                    {{ __('misc.subject.access_track') }}
                </Link>
            </div>
        </div>
    </div>
</template>
