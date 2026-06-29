<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Button from '@/Components/Button.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    Pencil,
    Plus,
    Power,
    Trash2,
    CheckCircle,
    XCircle
} from '@lucide/vue';
import { slugify } from '@/Utils/mask';

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

const subjectColumns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'name', label: 'Nome / Descrição', sortable: true },
    { key: 'duration', label: 'Duração', sortable: true },
    { key: 'institution', label: 'Instituição', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true, align: 'center' },
    { key: 'actions', label: 'Ações', align: 'right' },
];

const confirmState = ref({
    show: false,
    title: '',
    message: '',
    type: 'danger',
    onConfirm: () => {},
});

const triggerConfirm = (title, message, type, onConfirm) => {
    confirmState.value = {
        show: true,
        title,
        message,
        type,
        onConfirm: () => {
            onConfirm();
            confirmState.value.show = false;
        },
    };
};

// Subject Modal & Form
const isSubjectModalOpen = ref(false);
const isEditingSubject = ref(false);
const selectedSubjectId = ref(null);

const subjectForm = useForm({
    name: '',
    slug: '',
    description: '',
    duration: '',
    institution_id: '',
});

watch(() => subjectForm.name, (newName) => {
    if (!isEditingSubject.value) {
        subjectForm.slug = slugify(newName);
    }
});

const openCreateSubject = () => {
    isEditingSubject.value = false;
    subjectForm.reset();
    isSubjectModalOpen.value = true;
};

const openEditSubject = (sub) => {
    isEditingSubject.value = true;
    selectedSubjectId.value = sub.id;
    subjectForm.name = sub.name;
    subjectForm.slug = sub.slug || '';
    subjectForm.description = sub.description || '';
    subjectForm.duration = sub.duration || '';
    subjectForm.institution_id = sub.institution_id || '';
    isSubjectModalOpen.value = true;
};

const submitSubject = () => {
    if (isEditingSubject.value) {
        subjectForm.put(route('super-admin.subjects.update', selectedSubjectId.value), {
            onSuccess: () => {
                isSubjectModalOpen.value = false;
                subjectForm.reset();
            },
        });
    } else {
        subjectForm.post(route('super-admin.subjects.store'), {
            onSuccess: () => {
                isSubjectModalOpen.value = false;
                subjectForm.reset();
            },
        });
    }
};

const confirmToggleSubject = (sub) => {
    const activeVal = sub.is_active === 1 || sub.is_active?.value === 1 || sub.is_active === 'active';
    const actionText = activeVal ? 'desativar' : 'ativar';
    triggerConfirm(
        'Confirmar Alteração de Status',
        `Tem certeza de que deseja ${actionText} a matéria "${sub.name}"?`,
        'warning',
        () => {
            router.post(route('super-admin.subjects.toggle', sub.id));
        },
    );
};

const confirmDeleteSubject = (sub) => {
    triggerConfirm(
        'Excluir Matéria',
        `Tem certeza de que deseja excluir a matéria "${sub.name}"? Ela será enviada para a lixeira.`,
        'danger',
        () => {
            router.delete(route('super-admin.subjects.destroy', sub.id));
        },
    );
};
</script>

<template>
    <Head title="Gerenciar Matérias" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold leading-tight text-zinc-100">
                    Gerenciamento de Matérias
                </h2>
                <Button @click="openCreateSubject">
                    <template #icon><Plus class="h-4 w-4" /></template>
                    <span class="hidden md:inline">Cadastrar</span>
                </Button>
            </div>
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md">
                <DataTable
                    :items="subjects"
                    :columns="subjectColumns"
                    searchPlaceholder="Buscar por matéria ou instituição..."
                >
                    <template #id="{ item }">
                        <span class="text-zinc-500 font-mono">#{{ item.id }}</span>
                    </template>
                    <template #name="{ item }">
                        <div class="font-semibold text-zinc-150">{{ item.name }}</div>
                        <div class="max-w-xs truncate text-xs text-zinc-500">{{ item.description || 'Sem descrição' }}</div>
                    </template>
                    <template #duration="{ item }">
                        <span class="text-zinc-400 font-medium">{{ item.duration || 'N/A' }}</span>
                    </template>
                    <template #institution="{ item }">
                        <span class="text-xs text-indigo-400 font-semibold">{{ item.institution?.name || 'Comum' }}</span>
                    </template>
                    <template #is_active="{ item }">
                        <span v-if="item.is_active === 1 || item.is_active?.value === 1" class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-400">
                            <CheckCircle class="h-3 w-3" /> Ativo
                        </span>
                        <span v-else class="inline-flex items-center gap-1 rounded-full bg-rose-500/10 px-2.5 py-1 text-xs font-bold text-rose-400">
                            <XCircle class="h-3 w-3" /> Inativo
                        </span>
                    </template>
                    <template #actions="{ item }">
                        <div class="flex items-center justify-end gap-2">
                            <Tooltip :text="(item.is_active === 1 || item.is_active?.value === 1) ? 'Desativar' : 'Ativar'">
                                <button
                                    @click="confirmToggleSubject(item)"
                                    class="rounded-lg p-1.5 transition-colors border border-zinc-800 bg-zinc-900/50"
                                    :class="(item.is_active === 1 || item.is_active?.value === 1) ? 'text-red-500 hover:text-red-400 hover:bg-red-500/10' : 'text-emerald-500 hover:text-emerald-400 hover:bg-emerald-500/10'"
                                    type="button"
                                >
                                    <Power class="h-4 w-4" />
                                </button>
                            </Tooltip>

                            <Tooltip text="Editar">
                                <Button
                                    variant="icon"
                                    @click="openEditSubject(item)"
                                    class="text-indigo-400"
                                >
                                    <template #icon><Pencil class="h-4 w-4" /></template>
                                </Button>
                            </Tooltip>

                            <Tooltip text="Excluir">
                                <button
                                    @click="confirmDeleteSubject(item)"
                                    class="rounded-lg p-1.5 text-red-500 hover:text-red-400 hover:bg-red-500/10 border border-zinc-800 bg-zinc-900/50 transition-colors"
                                    type="button"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </Tooltip>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

        <!-- Base Modal: Cadastro / Edição de Matéria -->
        <BaseModal
            :show="isSubjectModalOpen"
            :title="isEditingSubject ? 'Editar Matéria' : 'Nova Matéria'"
            maxWidth="2xl"
            @close="isSubjectModalOpen = false"
        >
            <form @submit.prevent="submitSubject" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Nome da Matéria</label>
                        <input
                            v-model="subjectForm.name"
                            type="text"
                            required
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Slug</label>
                        <input
                            v-model="subjectForm.slug"
                            type="text"
                            required
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none"
                        />
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Descrição</label>
                    <textarea
                        v-model="subjectForm.description"
                        rows="3"
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none"
                    ></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Instituição</label>
                        <select
                            v-model="subjectForm.institution_id"
                            required
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none"
                        >
                            <option value="" disabled>Selecione uma instituição</option>
                            <option v-for="inst in institutions" :key="inst.id" :value="inst.id">
                                {{ inst.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Duração Estimada</label>
                        <input
                            v-model="subjectForm.duration"
                            type="text"
                            required
                            placeholder="Ex: 80 horas, 3 meses"
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-850">
                    <Button variant="secondary" type="button" @click="isSubjectModalOpen = false">
                        Cancelar
                    </Button>
                    <Button type="submit" :disabled="subjectForm.processing">
                        {{ subjectForm.processing ? 'Salvando...' : 'Salvar Matéria' }}
                    </Button>
                </div>
            </form>
        </BaseModal>

        <ConfirmModal
            :show="confirmState.show"
            :title="confirmState.title"
            :message="confirmState.message"
            :type="confirmState.type"
            @close="confirmState.show = false"
            @confirm="confirmState.onConfirm"
        />
    </AuthenticatedLayout>
</template>
