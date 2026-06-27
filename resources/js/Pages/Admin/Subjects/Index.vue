<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    subjects: {
        type: Array,
        default: () => []
    },
    institutions: {
        type: Array,
        default: () => []
    }
});

const isFormOpen = ref(false);

const form = useForm({
    institution_id: '',
    name: '',
    description: ''
});

const submit = () => {
    form.post(route('admin.subjects.store'), {
        onSuccess: () => {
            form.reset();
            isFormOpen.value = false;
        }
    });
};
</script>

<template>
    <Head title="Gerenciar Matérias" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold leading-tight text-zinc-100">
                    Matérias Cadastradas
                </h2>
                <button
                    @click="isFormOpen = !isFormOpen"
                    class="rounded-xl bg-indigo-650 px-4 py-2 text-xs font-bold text-white transition-all hover:bg-indigo-700"
                >
                    {{ isFormOpen ? 'Fechar Formulário' : 'Nova Matéria' }}
                </button>
            </div>
        </template>

        <div class="py-12 bg-zinc-950 min-h-[calc(100vh-64px)] text-zinc-100">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">
                
                <!-- Formulário de Criação -->
                <div v-if="isFormOpen" class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 shadow-xl backdrop-blur-md">
                    <h3 class="text-lg font-bold text-white mb-4">Cadastrar Nova Matéria</h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="institution_id" class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1">Instituição de Ensino</label>
                                <select
                                    id="institution_id"
                                    v-model="form.institution_id"
                                    required
                                    class="w-full rounded-xl border border-zinc-800 bg-zinc-955 px-4 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                >
                                    <option value="" disabled>Selecione a instituição...</option>
                                    <option v-for="inst in institutions" :key="inst.id" :value="inst.id">
                                        {{ inst.name }}
                                    </option>
                                </select>
                                <span v-if="form.errors.institution_id" class="text-xs text-red-400 block mt-1">{{ form.errors.institution_id }}</span>
                            </div>

                            <div>
                                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1">Nome da Matéria</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    placeholder="Ex: Banco de Dados Relacionais"
                                    class="w-full rounded-xl border border-zinc-800 bg-zinc-955 px-4 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                />
                                <span v-if="form.errors.name" class="text-xs text-red-400 block mt-1">{{ form.errors.name }}</span>
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1">Descrição</label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                placeholder="Descreva os objetivos principais ou ementa desta matéria..."
                                class="w-full rounded-xl border border-zinc-800 bg-zinc-955 px-4 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            ></textarea>
                            <span v-if="form.errors.description" class="text-xs text-red-400 block mt-1">{{ form.errors.description }}</span>
                        </div>

                        <div class="flex gap-3">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-xl bg-indigo-650 px-5 py-2.5 text-xs font-bold text-white transition-all hover:bg-indigo-700 disabled:opacity-50"
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
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="sub in subjects"
                        :key="sub.id"
                        class="group relative rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 transition-all duration-300 hover:border-zinc-700 hover:bg-zinc-900/50 hover:shadow-xl flex flex-col justify-between h-full"
                    >
                        <div>
                            <span class="inline-block rounded-md bg-indigo-500/10 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-indigo-400 border border-indigo-500/20 mb-3">
                                {{ sub.institution ? sub.institution.name : 'N/A' }}
                            </span>
                            <h4 class="text-lg font-bold text-white group-hover:text-indigo-400 transition-colors">{{ sub.name }}</h4>
                            <p class="mt-2 text-sm text-zinc-400 line-clamp-3 min-h-[60px]">
                                {{ sub.description || 'Sem descrição cadastrada.' }}
                            </p>
                        </div>
                        
                        <div class="mt-6 border-t border-zinc-850 pt-4 flex flex-col gap-3">
                            <div class="flex items-center justify-between text-xs text-zinc-500">
                                <span>Materiais de Estudo: <strong>{{ sub.study_materials_count || 0 }}</strong></span>
                                <span>Atividades/Testes: <strong>{{ sub.tests_count || 0 }}</strong></span>
                            </div>
                            
                            <Link
                                :href="route('admin.subjects.show', sub.id)"
                                class="block w-full text-center rounded-xl bg-zinc-800 py-2.5 text-xs font-bold text-white transition-all hover:bg-indigo-650"
                            >
                                Gerenciar Conteúdo &rarr;
                            </Link>
                        </div>
                    </div>

                    <div v-if="subjects.length === 0" class="col-span-full rounded-2xl border border-dashed border-zinc-800 p-12 text-center text-zinc-500">
                        Nenhuma matéria cadastrada ainda. Clique em "Nova Matéria" para começar!
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
