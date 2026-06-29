<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import Tooltip from '@/Components/Tooltip.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Pencil, Trash2, Plus, Eye, Power } from '@lucide/vue';

const props = defineProps({
    students: {
        type: Array,
        default: () => [],
    },
});

const studentHeaders = [
    { key: 'name', label: 'Nome', sortable: true },
    { key: 'email', label: 'E-mail', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true, align: 'center' },
    { key: 'points', label: 'XP Acumulado', sortable: true, align: 'center' },
    { key: 'actions', label: 'Ações', sortable: false, align: 'center' },
];

const isModalOpen = ref(false);
const isEditing = ref(false);
const selectedStudentId = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'student',
});

const isActive = (item) => {
    if (!item) return false;
    const val = typeof item === 'object' && 'is_active' in item ? item.is_active : item;
    if (typeof val === 'object' && val !== null) {
        return val.value === 1 || val.value === true || String(val.value) === '1' || val.value === 'active';
    }
    return val === 1 || val === true || String(val) === '1' || val === 'active';
};

const openCreateModal = () => {
    isEditing.value = false;
    selectedStudentId.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (student) => {
    isEditing.value = true;
    selectedStudentId.value = student.id;
    form.name = student.name;
    form.email = student.email;
    form.password = '';
    isModalOpen.value = true;
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

const submit = () => {
    triggerConfirm(
        isEditing.value ? 'Salvar Alterações' : 'Cadastrar Aluno',
        'Deseja salvar as informações deste aluno?',
        () => {
            if (isEditing.value) {
                form.put(route('teacher.students.update', selectedStudentId.value), {
                    preserveScroll: true,
                    onSuccess: () => {
                        isModalOpen.value = false;
                        form.reset();
                        confirmState.value.show = false;
                    },
                });
            } else {
                form.post(route('teacher.students.store'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        isModalOpen.value = false;
                        form.reset();
                        confirmState.value.show = false;
                    },
                });
            }
        }
    );
};

const confirmDeleteStudent = (id) => {
    triggerConfirm(
        'Excluir Aluno',
        'Tem certeza que deseja excluir este aluno? Esta ação enviará o usuário para a lixeira.',
        () => {
            router.delete(route('teacher.students.destroy', id), {
                preserveScroll: true,
                onSuccess: () => {
                    confirmState.value.show = false;
                },
            });
        }
    );
};

const toggleStatus = (student) => {
    const actionText = isActive(student) ? 'desativar' : 'ativar';
    triggerConfirm(
        'Alterar Status',
        `Tem certeza de que deseja ${actionText} o aluno "${student.name}"?`,
        () => {
            router.post(route('teacher.students.toggle', student.id), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    confirmState.value.show = false;
                },
            });
        }
    );
};
</script>

<template>
    <Head title="Gerenciar Alunos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold leading-tight text-zinc-100">
                    Gerenciar Meus Alunos
                </h2>
                <button
                    @click="openCreateModal"
                    class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-900/30 transition-all duration-200 hover:from-violet-500 hover:to-indigo-500 flex items-center gap-2"
                    title="Cadastrar Aluno"
                >
                    <Plus class="h-4 w-4 shrink-0" />
                    <span class="hidden md:inline">Cadastrar Aluno</span>
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

                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <h3 class="mb-4 text-lg font-bold text-white">
                        Alunos Matriculados
                    </h3>
                    <DataTable
                        :items="students"
                        :columns="studentHeaders"
                        searchPlaceholder="Pesquisar alunos..."
                    >
                        <template #is_active="{ item }">
                            <span
                                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                :class="
                                    isActive(item)
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : 'bg-red-100 text-red-800'
                                "
                            >
                                {{ isActive(item) ? 'Ativo' : 'Inativo' }}
                            </span>
                        </template>

                        <template #points="{ item }">
                            <span class="font-bold text-indigo-400">
                                {{ item.points }} XP
                            </span>
                        </template>

                        <template #actions="{ item }">
                            <div class="flex items-center justify-center gap-1">
                                <Tooltip text="Desempenho">
                                    <Link
                                        :href="route('teacher.students.performance', item.id)"
                                        class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-850 hover:text-white"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                </Tooltip>
                                <Tooltip text="Editar Aluno">
                                    <button
                                        @click="openEditModal(item)"
                                        class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-850 hover:text-white"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                </Tooltip>
                                 <Tooltip :text="isActive(item) ? 'Desativar Aluno' : 'Ativar Aluno'">
                                     <button
                                         @click="toggleStatus(item)"
                                         class="rounded-lg p-1.5 transition-colors"
                                         :class="isActive(item) ? 'text-red-500 hover:text-red-400 hover:bg-red-500/10' : 'text-emerald-500 hover:text-emerald-400 hover:bg-emerald-500/10'"
                                     >
                                         <Power class="h-4 w-4" />
                                     </button>
                                 </Tooltip>
                                 <Tooltip text="Excluir Aluno">
                                     <button
                                         @click="confirmDeleteStudent(item.id)"
                                         class="rounded-lg p-1.5 text-red-500 hover:text-red-400 hover:bg-red-500/10 transition-colors"
                                     >
                                         <Trash2 class="h-4 w-4" />
                                     </button>
                                 </Tooltip>
                            </div>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Student Form Modal -->
        <BaseModal
            :show="isModalOpen"
            :title="isEditing ? 'Editar Aluno' : 'Cadastrar Aluno'"
            maxWidth="md"
            @close="isModalOpen = false"
        >
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Nome</label>
                    <TextInput v-model="form.name" type="text" required placeholder="Ex: João da Silva" />
                    <span v-if="form.errors.name" class="text-xs text-red-500 mt-1 block">{{ form.errors.name }}</span>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">E-mail</label>
                    <TextInput v-model="form.email" type="email" required placeholder="Ex: joao@gmail.com" />
                    <span v-if="form.errors.email" class="text-xs text-red-500 mt-1 block">{{ form.errors.email }}</span>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Senha {{ isEditing ? '(Deixe em branco para não alterar)' : '' }}</label>
                    <TextInput v-model="form.password" type="password" :required="!isEditing" placeholder="••••••••" />
                    <span v-if="form.errors.password" class="text-xs text-red-500 mt-1 block">{{ form.errors.password }}</span>
                </div>

                <div class="flex justify-end gap-3 pt-3">
                    <button type="button" @click="isModalOpen = false" class="rounded-xl bg-zinc-800 px-5 py-2.5 text-xs font-bold text-zinc-400 transition-colors hover:bg-zinc-700">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="form.processing" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white transition-colors hover:bg-indigo-500 disabled:opacity-50">
                        {{ isEditing ? 'Salvar Alterações' : 'Cadastrar' }}
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
