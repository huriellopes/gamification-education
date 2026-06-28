<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import Tooltip from '@/Components/Tooltip.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { slugify } from '@/Utils/mask';
import { Pencil, Trash2, Plus, Power } from '@lucide/vue';

const props = defineProps({
    subjects: {
        type: Array,
        default: () => [],
    },
    institutions: {
        type: Array,
        default: () => [],
    },
});

const uniqueInstitutions = computed(() => {
    const seen = new Set();
    return props.institutions.filter(inst => {
        if (!inst || seen.has(inst.id)) return false;
        seen.add(inst.id);
        return true;
    });
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const selectedSubjectId = ref(null);
const wasSlugManuallyEdited = ref(false);

const form = useForm({
    institution_id: '',
    name: '',
    slug: '',
    description: '',
    duration: '',
});

watch(() => form.name, (newName) => {
    if (!isEditing.value && !wasSlugManuallyEdited.value) {
        form.slug = slugify(newName);
    }
});

const openCreateModal = () => {
    isEditing.value = false;
    selectedSubjectId.value = null;
    wasSlugManuallyEdited.value = false;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (subject) => {
    isEditing.value = true;
    selectedSubjectId.value = subject.id;
    wasSlugManuallyEdited.value = true;
    form.institution_id = subject.institution_id;
    form.name = subject.name;
    form.slug = subject.slug;
    form.description = subject.description;
    form.duration = subject.duration;
    isModalOpen.value = true;
};

const submit = () => {
    triggerConfirm(
        isEditing.value ? 'Salvar Alterações' : 'Salvar Matéria',
        'Deseja gravar as informações desta matéria?',
        () => {
            if (isEditing.value) {
                form.put(route('admin.subjects.update', selectedSubjectId.value), {
                    onSuccess: () => {
                        form.reset();
                        isModalOpen.value = false;
                        confirmState.value.show = false;
                    },
                });
            } else {
                form.post(route('admin.subjects.store'), {
                    onSuccess: () => {
                        form.reset();
                        isModalOpen.value = false;
                        confirmState.value.show = false;
                    },
                });
            }
        }
    );
};

const confirmState = ref({
    show: false,
    title: '',
    message: '',
    onConfirm: null,
});

const triggerConfirm = (title, message, onConfirm) => {
    confirmState.value.title = title;
    confirmState.value.message = message;
    confirmState.value.onConfirm = onConfirm;
    confirmState.value.show = true;
};

const confirmDeleteSubject = (id) => {
    triggerConfirm(
        'Excluir Matéria',
        'Tem certeza que deseja enviar esta matéria para a lixeira? Todos os materiais associados também serão arquivados.',
        () => {
            form.delete(route('admin.subjects.destroy', id), {
                onSuccess: () => {
                    confirmState.value.show = false;
                },
            });
        }
    );
};

const toggleStatus = (id) => {
    triggerConfirm(
        'Alterar Status',
        'Deseja alterar o status de ativação desta matéria?',
        () => {
            form.post(route('admin.subjects.toggle', id), {
                onSuccess: () => {
                    confirmState.value.show = false;
                },
            });
        }
    );
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
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-650 px-4 py-2.5 text-xs font-bold text-white transition-all hover:brightness-110"
                >
                    <Plus class="h-4 w-4" />
                    Nova Matéria
                </button>
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

                <!-- Lista de Matérias -->
                <div
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="sub in subjects"
                        :key="sub.id"
                        class="group relative flex h-full flex-col justify-between rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 transition-all duration-300 hover:border-indigo-500/30 hover:bg-zinc-900/50 hover:shadow-xl"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <span
                                    class="mb-3 inline-block rounded-md border border-indigo-500/20 bg-indigo-500/10 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-indigo-400"
                                >
                                    {{
                                        sub.institution
                                            ? sub.institution.name
                                            : 'N/A'
                                    }}
                                </span>
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
                                    <Tooltip text="Alterar Status">
                                        <button
                                            @click="toggleStatus(sub.id)"
                                            class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-800 hover:text-white"
                                            type="button"
                                        >
                                            <Power class="h-4 w-4" />
                                        </button>
                                    </Tooltip>
                                    <Tooltip text="Excluir Matéria">
                                        <button
                                            @click="confirmDeleteSubject(sub.id)"
                                            class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-red-500/20 hover:text-red-400"
                                            type="button"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </Tooltip>
                                </div>
                            </div>
                            <h4
                                class="text-lg font-bold text-white transition-colors group-hover:text-indigo-400"
                            >
                                {{ sub.name }}
                            </h4>
                            <p class="text-xs text-zinc-500 mt-1">
                                Status:
                                <span
                                    class="font-semibold"
                                    :class="
                                        sub.is_active === 'active'
                                            ? 'text-emerald-400'
                                            : 'text-red-400'
                                    "
                                >
                                    {{ sub.is_active === 'active' ? 'Ativa' : 'Inativa' }}
                                </span>
                            </p>
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
                                    >Materiais:
                                    <strong>{{
                                        sub.study_materials_count || 0
                                    }}</strong></span
                                >
                                <span
                                    >Testes:
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
                        Nenhuma matéria cadastrada ainda. Clique em "Nova Matéria" para começar!
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Subject Modal -->
        <BaseModal
            :show="isModalOpen"
            :title="isEditing ? 'Editar Matéria' : 'Nova Matéria'"
            maxWidth="xl"
            @close="isModalOpen = false"
        >
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Instituição de Ensino</label>
                        <SelectInput v-model="form.institution_id" required>
                            <option value="" disabled>Selecione a instituição...</option>
                            <option v-for="inst in uniqueInstitutions" :key="inst.id" :value="inst.id">{{ inst.name }}</option>
                        </SelectInput>
                        <span v-if="form.errors.institution_id" class="text-xs text-red-500 mt-1 block">{{ form.errors.institution_id }}</span>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Duração</label>
                        <TextInput v-model="form.duration" type="text" required placeholder="Ex: 80 horas" />
                        <span v-if="form.errors.duration" class="text-xs text-red-500 mt-1 block">{{ form.errors.duration }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Nome da Matéria</label>
                        <TextInput v-model="form.name" type="text" required placeholder="Ex: Algoritmos e Programação" />
                        <span v-if="form.errors.name" class="text-xs text-red-500 mt-1 block">{{ form.errors.name }}</span>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Slug</label>
                        <TextInput v-model="form.slug" @input="wasSlugManuallyEdited = true; form.slug = slugify($event.target.value)" type="text" required placeholder="ex: algoritmos-e-programacao" />
                        <span v-if="form.errors.slug" class="text-xs text-red-500 mt-1 block">{{ form.errors.slug }}</span>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Descrição</label>
                    <TextareaInput v-model="form.description" rows="3" placeholder="Insira a ementa ou resumo da matéria..." />
                    <span v-if="form.errors.description" class="text-xs text-red-500 mt-1 block">{{ form.errors.description }}</span>
                </div>

                <div class="flex justify-end gap-3 pt-3">
                    <button type="button" @click="isModalOpen = false" class="rounded-xl bg-zinc-800 px-5 py-2.5 text-xs font-bold text-zinc-400 transition-colors hover:bg-zinc-700">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="form.processing" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white transition-colors hover:bg-indigo-500 disabled:opacity-50">
                        {{ isEditing ? 'Salvar Alterações' : 'Salvar Matéria' }}
                    </button>
                </div>
            </form>
        </BaseModal>

        <!-- Dynamic Confirmation Modal -->
        <ConfirmModal
            :show="confirmState.show"
            :title="confirmState.title"
            :message="confirmState.message"
            @close="confirmState.show = false"
            @confirm="confirmState.onConfirm"
        />
    </AuthenticatedLayout>
</template>
