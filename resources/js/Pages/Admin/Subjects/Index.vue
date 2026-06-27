<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    subjects: {
        type: Array,
        default: () => [],
    },
    institutions: {
        type: Array,
        default: () => [],
    },
});

const isFormOpen = ref(false);

const form = useForm({
    institution_id: '',
    name: '',
    description: '',
});

const submit = () => {
    form.post(route('admin.subjects.store'), {
        onSuccess: () => {
            form.reset();
            isFormOpen.value = false;
        },
    });
};
</script>

<template>
    <Head title="Gerenciar Matérias" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold leading-tight text-zinc-100">
                    Matérias Cadastradas
                </h2>
                <button
                    @click="isFormOpen = !isFormOpen"
                    class="bg-indigo-650 rounded-xl px-4 py-2 text-xs font-bold text-white transition-all hover:bg-indigo-700"
                >
                    {{ isFormOpen ? 'Fechar Formulário' : 'Nova Matéria' }}
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
                        Cadastrar Nova Matéria
                    </h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label
                                    for="institution_id"
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-zinc-400"
                                    >Instituição de Ensino</label
                                >
                                <select
                                    id="institution_id"
                                    v-model="form.institution_id"
                                    required
                                    class="bg-zinc-955 w-full rounded-xl border border-zinc-800 px-4 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                >
                                    <option value="" disabled>
                                        Selecione a instituição...
                                    </option>
                                    <option
                                        v-for="inst in institutions"
                                        :key="inst.id"
                                        :value="inst.id"
                                    >
                                        {{ inst.name }}
                                    </option>
                                </select>
                                <span
                                    v-if="form.errors.institution_id"
                                    class="mt-1 block text-xs text-red-400"
                                    >{{ form.errors.institution_id }}</span
                                >
                            </div>

                            <div>
                                <label
                                    for="name"
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-zinc-400"
                                    >Nome da Matéria</label
                                >
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    placeholder="Ex: Banco de Dados Relacionais"
                                    class="bg-zinc-955 w-full rounded-xl border border-zinc-800 px-4 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                />
                                <span
                                    v-if="form.errors.name"
                                    class="mt-1 block text-xs text-red-400"
                                    >{{ form.errors.name }}</span
                                >
                            </div>
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
                                placeholder="Descreva os objetivos principais ou ementa desta matéria..."
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
                                Salvar Matéria
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

                <!-- Lista de Matérias -->
                <div
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="sub in subjects"
                        :key="sub.id"
                        class="group relative flex h-full flex-col justify-between rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 transition-all duration-300 hover:border-zinc-700 hover:bg-zinc-900/50 hover:shadow-xl"
                    >
                        <div>
                            <span
                                class="mb-3 inline-block rounded-md border border-indigo-500/20 bg-indigo-500/10 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-indigo-400"
                            >
                                {{
                                    sub.institution
                                        ? sub.institution.name
                                        : 'N/A'
                                }}
                            </span>
                            <h4
                                class="text-lg font-bold text-white transition-colors group-hover:text-indigo-400"
                            >
                                {{ sub.name }}
                            </h4>
                            <p
                                class="mt-2 line-clamp-3 min-h-[60px] text-sm text-zinc-400"
                            >
                                {{
                                    sub.description ||
                                    'Sem descrição cadastrada.'
                                }}
                            </p>
                        </div>

                        <div
                            class="border-zinc-850 mt-6 flex flex-col gap-3 border-t pt-4"
                        >
                            <div
                                class="flex items-center justify-between text-xs text-zinc-500"
                            >
                                <span
                                    >Materiais de Estudo:
                                    <strong>{{
                                        sub.study_materials_count || 0
                                    }}</strong></span
                                >
                                <span
                                    >Atividades/Testes:
                                    <strong>{{
                                        sub.tests_count || 0
                                    }}</strong></span
                                >
                            </div>

                            <Link
                                :href="route('admin.subjects.show', sub.id)"
                                class="hover:bg-indigo-650 block w-full rounded-xl bg-zinc-800 py-2.5 text-center text-xs font-bold text-white transition-all"
                            >
                                Gerenciar Conteúdo &rarr;
                            </Link>
                        </div>
                    </div>

                    <div
                        v-if="subjects.length === 0"
                        class="col-span-full rounded-2xl border border-dashed border-zinc-800 p-12 text-center text-zinc-500"
                    >
                        Nenhuma matéria cadastrada ainda. Clique em "Nova
                        Matéria" para começar!
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
