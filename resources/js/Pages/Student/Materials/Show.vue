<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    subject: {
        type: Object,
        required: true,
    },
    material: {
        type: Object,
        required: true,
    },
    completed: {
        type: Boolean,
        default: false,
    },
});

const isSubmitting = ref(false);

const completeLeitura = () => {
    isSubmitting.value = true;
    router.post(
        route('student.materials.complete', [
            props.subject.id,
            props.material.id,
        ]),
        {},
        {
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};

// Simples parser local de Markdown para formatar o texto didático de forma estilizada
const parseMarkdown = (text) => {
    if (!text) return '';
    let html = text;
    // Substitui títulos
    html = html.replace(
        /^#\s+(.+)$/gm,
        '<h1 class="text-2xl font-black text-white mt-6 mb-4 border-b border-zinc-800 pb-2">$1</h1>',
    );
    html = html.replace(
        /^##\s+(.+)$/gm,
        '<h2 class="text-xl font-bold text-indigo-400 mt-6 mb-3">$1</h2>',
    );
    html = html.replace(
        /^###\s+(.+)$/gm,
        '<h3 class="text-lg font-bold text-zinc-200 mt-4 mb-2">$1</h3>',
    );
    // Substitui listas
    html = html.replace(
        /^\*\s+(.+)$/gm,
        '<li class="ml-4 list-disc text-zinc-300 my-1">$1</li>',
    );
    // Substitui negrito
    html = html.replace(
        /\*\*(.+?)\*\*/g,
        '<strong class="text-white font-bold">$1</strong>',
    );
    // Substitui blocos de código
    html = html.replace(
        /```php([\s\S]+?)```/g,
        '<pre class="bg-zinc-950 p-4 rounded-xl border border-zinc-850 my-4 overflow-x-auto text-xs text-indigo-300 font-mono"><code>$1</code></pre>',
    );
    html = html.replace(
        /```javascript([\s\S]+?)```/g,
        '<pre class="bg-zinc-950 p-4 rounded-xl border border-zinc-850 my-4 overflow-x-auto text-xs text-emerald-300 font-mono"><code>$1</code></pre>',
    );
    html = html.replace(
        /```html([\s\S]+?)```/g,
        '<pre class="bg-zinc-950 p-4 rounded-xl border border-zinc-850 my-4 overflow-x-auto text-xs text-yellow-300 font-mono"><code>$1</code></pre>',
    );
    return html;
};
</script>

<template>
    <Head :title="material.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('student.subjects.show', subject.id)"
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
                        {{ material.title }}
                    </h2>
                    <p class="text-xs text-zinc-500">
                        Trilha: {{ subject.name }}
                    </p>
                </div>
            </div>
        </template>

        <div
            class="bg-zinc-955 min-h-[calc(100vh-64px)] py-12 pb-32 text-zinc-100"
        >
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <!-- Bloco do Conteúdo formatado -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/20 p-8 shadow-xl backdrop-blur-sm"
                >
                    <div
                        class="markdown-body space-y-4 text-sm leading-relaxed text-zinc-300"
                        v-html="parseMarkdown(material.content)"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Barra Inferior Fixa para Conclusão de Leitura (Premium Sticky Bar) -->
        <div
            class="fixed inset-x-0 bottom-0 z-40 mx-auto flex max-w-7xl items-center justify-between rounded-t-3xl border-t border-zinc-800 bg-zinc-900/90 p-5 shadow-[0_-10px_30px_rgba(0,0,0,0.5)] backdrop-blur-md"
        >
            <div class="flex flex-col">
                <span
                    class="text-xs font-bold uppercase tracking-wider text-zinc-500"
                    >Ações de Aprendizado</span
                >
                <span class="text-sm font-semibold text-zinc-200"
                    >Recompensa da leitura:
                    <span class="font-bold text-yellow-400"
                        >+{{ material.points_reward }} XP</span
                    ></span
                >
            </div>

            <div>
                <!-- Se já completou -->
                <div
                    v-if="completed"
                    class="flex items-center gap-2 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-5 py-3 text-xs font-bold text-emerald-400"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="3"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m4.5 12.75 6 6 9-13.5"
                        />
                    </svg>
                    Você já concluiu este material!
                </div>

                <!-- Se ainda não completou -->
                <button
                    v-else
                    @click="completeLeitura"
                    :disabled="isSubmitting"
                    class="bg-indigo-650 flex items-center gap-2 rounded-xl px-6 py-3 text-xs font-bold text-white shadow-[0_0_15px_rgba(99,102,241,0.3)] transition-all duration-200 hover:scale-105 hover:bg-indigo-700 disabled:opacity-50"
                >
                    <svg
                        v-if="isSubmitting"
                        class="h-4 w-4 animate-spin text-white"
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
                    Marcar como Lido e Ganhar +{{ material.points_reward }} XP
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Scoped overrides para formatar listas e espaçamento gerado */
:deep(h1) {
    margin-top: 1.5rem;
}
:deep(h2) {
    margin-top: 1.5rem;
}
:deep(li) {
    list-style-type: disc;
    margin-left: 1.25rem;
}
:deep(pre) {
    white-space: pre;
    word-wrap: normal;
}
</style>
