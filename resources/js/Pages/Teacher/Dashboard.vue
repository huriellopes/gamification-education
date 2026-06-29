<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Pencil, Trash2, Plus, Users as UsersIcon } from '@lucide/vue';
import { ref, watch } from 'vue';
import { slugify } from '@/Utils/mask';

const props = defineProps({
    subjects: {
        type: Array,
        default: () => [],
    },
});

// Subject Modal & Form
const isSubjectModalOpen = ref(false);
const isEditingSubject = ref(false);
const selectedSubjectId = ref(null);
const wasSlugManuallyEdited = ref(false);

const subForm = useForm({
    name: '',
    slug: '',
    description: '',
    duration: '',
});

watch(() => subForm.name, (newName) => {
    if (!isEditingSubject.value && !wasSlugManuallyEdited.value) {
        subForm.slug = slugify(newName);
    }
});

const openCreateModal = () => {
    isEditingSubject.value = false;
    selectedSubjectId.value = null;
    wasSlugManuallyEdited.value = false;
    subForm.reset();
    isSubjectModalOpen.value = true;
};

const openEditModal = (subject) => {
    isEditingSubject.value = true;
    selectedSubjectId.value = subject.id;
    wasSlugManuallyEdited.value = true;
    subForm.name = subject.name;
    subForm.slug = subject.slug;
    subForm.description = subject.description;
    subForm.duration = subject.duration;
    isSubjectModalOpen.value = true;
};

const submitSubject = () => {
    if (isEditingSubject.value) {
        subForm.put(route('teacher.subjects.update', selectedSubjectId.value), {
            onSuccess: () => {
                isSubjectModalOpen.value = false;
                subForm.reset();
            },
        });
    } else {
        subForm.post(route('teacher.subjects.store'), {
            onSuccess: () => {
                isSubjectModalOpen.value = false;
                subForm.reset();
            },
        });
    }
};

// Deletion
const isConfirmDeleteOpen = ref(false);
const subjectToDelete = ref(null);

const confirmDeleteSubject = (id) => {
    subjectToDelete.value = id;
    isConfirmDeleteOpen.value = true;
};

const deleteSubject = () => {
    if (subjectToDelete.value) {
        subForm.delete(route('teacher.subjects.destroy', subjectToDelete.value), {
            onSuccess: () => {
                isConfirmDeleteOpen.value = false;
                subjectToDelete.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Painel do Professor" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-bold leading-tight text-zinc-100">
                    Painel do Professor — Minhas Matérias
                </h2>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('teacher.students.index')"
                        class="inline-flex items-center gap-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 px-4 py-2.5 text-xs font-bold text-zinc-300 transition-colors"
                    >
                        <UsersIcon class="h-4 w-4" />
                        Gerenciar Alunos
                    </Link>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-650 px-4 py-2.5 text-xs font-bold text-white transition-all hover:brightness-110"
                        title="Nova Matéria"
                    >
                        <Plus class="h-4 w-4" />
                        <span class="hidden md:inline">Nova Matéria</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="min-h-[calc(100vh-64px)] bg-zinc-955 py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Toast/Flash Message -->
                <div
                    v-if="$page.props.flash?.success"
                    class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-400"
                >
                    {{ $page.props.flash.success }}
                </div>

                <div>
                    <h3 class="text-lg font-bold text-white">
                        Minhas Matérias Ativas
                    </h3>
                    <p class="text-sm text-zinc-400">
                        Selecione uma matéria para gerenciar os materiais de
                        estudo, testes ou realize edições básicas.
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="sub in subjects"
                        :key="sub.id"
                        class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/40 p-6 transition-all duration-300 hover:border-indigo-500/30"
                    >
                        <div class="space-y-4">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4
                                        class="text-lg font-bold text-white transition-colors group-hover:text-indigo-400"
                                    >
                                        {{ sub.name }}
                                    </h4>
                                    <p class="mt-1 text-xs text-zinc-550">
                                        Duração: {{ sub.duration || 'Não informada' }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Tooltip text="Editar Matéria">
                                        <button
                                            @click="openEditModal(sub)"
                                            class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-800 hover:text-white"
                                            type="button"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                    </Tooltip>
                                    <Tooltip text="Excluir Matéria">
                                        <button
                                            @click="confirmDeleteSubject(sub.id)"
                                            class="rounded-lg p-1.5 text-red-500 hover:text-red-400 hover:bg-red-500/10 transition-colors"
                                            type="button"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </Tooltip>
                                </div>
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
                                    📚 {{ sub.study_materials_count || 0 }} Materiais
                                </span>
                                <span class="flex items-center gap-1">
                                    ⚔️ {{ sub.tests_count || 0 }} Quizzes
                                </span>
                            </div>
                        </div>
                        <div class="pt-4 mt-2">
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
                    <div
                        v-if="subjects.length === 0"
                        class="col-span-full py-12 text-center text-zinc-500"
                    >
                        Você não está associado a nenhuma matéria no momento.
                        Clique em "Nova Matéria" para começar!
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Subject Modal -->
        <BaseModal
            :show="isSubjectModalOpen"
            :title="isEditingSubject ? 'Editar Matéria' : 'Nova Matéria'"
            maxWidth="xl"
            @close="isSubjectModalOpen = false"
        >
            <form @submit.prevent="submitSubject" class="space-y-4">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Nome da Matéria</label>
                    <input v-model="subForm.name" type="text" required placeholder="Ex: Algoritmos e Programação" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    <span v-if="subForm.errors.name" class="text-xs text-red-500 mt-1 block">{{ subForm.errors.name }}</span>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Slug</label>
                    <input :value="subForm.slug" @input="wasSlugManuallyEdited = true; subForm.slug = slugify($event.target.value)" type="text" required placeholder="ex: algoritmos-e-programacao" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    <span v-if="subForm.errors.slug" class="text-xs text-red-500 mt-1 block">{{ subForm.errors.slug }}</span>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Duração</label>
                    <input v-model="subForm.duration" type="text" required placeholder="Ex: 60 horas" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    <span v-if="subForm.errors.duration" class="text-xs text-red-500 mt-1 block">{{ subForm.errors.duration }}</span>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Descrição</label>
                    <textarea v-model="subForm.description" rows="3" placeholder="Insira a ementa ou resumo da matéria..." class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                    <span v-if="subForm.errors.description" class="text-xs text-red-500 mt-1 block">{{ subForm.errors.description }}</span>
                </div>

                <div class="flex justify-end gap-3 pt-3">
                    <button type="button" @click="isSubjectModalOpen = false" class="rounded-xl bg-zinc-800 px-5 py-2.5 text-xs font-bold text-zinc-400 transition-colors hover:bg-zinc-700">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="subForm.processing" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white transition-colors hover:bg-indigo-500 disabled:opacity-50">
                        {{ isEditingSubject ? 'Salvar Alterações' : 'Criar Matéria' }}
                    </button>
                </div>
            </form>
        </BaseModal>

        <!-- Delete Confirmation Modal -->
        <ConfirmModal
            :show="isConfirmDeleteOpen"
            title="Excluir Matéria?"
            message="Tem certeza que deseja enviar esta matéria para a lixeira? Todos os materiais associados também serão arquivados."
            @close="isConfirmDeleteOpen = false"
            @confirm="deleteSubject"
        />
    </AuthenticatedLayout>
</template>
