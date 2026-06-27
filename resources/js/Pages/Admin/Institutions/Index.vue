<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    institutions: {
        type: Array,
        default: () => [],
    },
});

const isFormOpen = ref(false);

const form = useForm({
    name: '',
    description: '',
});

const submit = () => {
    form.post(route('admin.institutions.store'), {
        onSuccess: () => {
            form.reset();
            isFormOpen.value = false;
        },
    });
};
</script>

<template>
    <Head title="Gerenciar Instituições" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold leading-tight text-zinc-100">
                    Instituições de Ensino
                </h2>
                <button
                    @click="isFormOpen = !isFormOpen"
                    class="bg-indigo-650 rounded-xl px-4 py-2 text-xs font-bold text-white transition-all hover:bg-indigo-700"
                >
                    {{ isFormOpen ? 'Fechar Formulário' : 'Nova Instituição' }}
                </button>
            </div>
        </template>

        <div class="min-h-[calc(100vh-64px)] bg-zinc-950 py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Formulário de Criação -->
                <div
                    v-if="isFormOpen"
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 shadow-xl backdrop-blur-md"
                >
                    <h3 class="mb-4 text-lg font-bold text-white">
                        Cadastrar Nova Instituição
                    </h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label
                                for="name"
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-zinc-400"
                                >Nome da Instituição</label
                            >
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                placeholder="Ex: Escola Técnica de TI"
                                class="bg-zinc-955 w-full rounded-xl border border-zinc-800 px-4 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            />
                            <span
                                v-if="form.errors.name"
                                class="mt-1 block text-xs text-red-400"
                                >{{ form.errors.name }}</span
                            >
                        </div>

                        <div>
                            <label
                                for="description"
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-zinc-400"
                                >Descrição</label
                            >
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                placeholder="Breve resumo da instituição ou localização..."
                                class="bg-zinc-955 w-full rounded-xl border border-zinc-800 px-4 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            ></textarea>
                            <span
                                v-if="form.errors.description"
                                class="mt-1 block text-xs text-red-400"
                                >{{ form.errors.description }}</span
                            >
                        </div>

                        <div class="flex gap-3">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-indigo-650 rounded-xl px-5 py-2.5 text-xs font-bold text-white transition-all hover:bg-indigo-700 disabled:opacity-50"
                            >
                                Salvar Instituição
                            </button>
                            <button
                                type="button"
                                @click="isFormOpen = false"
                                class="rounded-xl bg-zinc-800 px-5 py-2.5 text-xs font-bold text-zinc-400 transition-all hover:bg-zinc-700"
                            >
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Lista de Instituições -->
                <div
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="inst in institutions"
                        :key="inst.id"
                        class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 transition-all duration-300 hover:border-zinc-700 hover:bg-zinc-900/50"
                    >
                        <h4 class="text-lg font-bold text-white">
                            {{ inst.name }}
                        </h4>
                        <p
                            class="mt-2 line-clamp-3 min-h-[60px] text-sm text-zinc-400"
                        >
                            {{
                                inst.description || 'Sem descrição cadastrada.'
                            }}
                        </p>

                        <div
                            class="border-zinc-850 mt-6 flex items-center justify-between border-t pt-4 text-xs"
                        >
                            <div class="flex items-center gap-1 text-zinc-500">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    class="h-4 w-4 text-indigo-400"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A9.342 9.342 0 0 1 12.625 19.5a9.379 9.379 0 0 1-4.12-.952 4.125 4.125 0 0 1-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M12.625 19.5a9.38 9.38 0 0 1-2.625.372 9.337 9.337 0 0 1-4.121-.952 4.125 4.125 0 0 1 7.533-2.493M12.625 19.5v-.003a9.38 9.38 0 0 0 2.625-.372"
                                    />
                                </svg>
                                <span
                                    >Alunos:
                                    <strong>{{
                                        inst.users_count || 0
                                    }}</strong></span
                                >
                            </div>
                            <div class="flex items-center gap-1 text-zinc-500">
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
                                        d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"
                                    />
                                </svg>
                                <span
                                    >Matérias:
                                    <strong>{{
                                        inst.subjects_count || 0
                                    }}</strong></span
                                >
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="institutions.length === 0"
                        class="col-span-full rounded-2xl border border-dashed border-zinc-800 p-12 text-center text-zinc-500"
                    >
                        Nenhuma instituição de ensino cadastrada ainda. Clique
                        em "Nova Instituição" para começar!
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
