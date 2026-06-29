<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import Tooltip from '@/Components/Tooltip.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Pencil, Trash2, Plus, Power } from '@lucide/vue';

const studentHeaders = [
    { key: 'name', label: 'Nome', sortable: true },
    { key: 'email', label: 'E-mail', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true, align: 'center' },
    { key: 'points', label: 'XP Acumulado', sortable: true, align: 'center' },
    { key: 'last_login_at', label: 'Último Acesso', sortable: true, align: 'center' },
    { key: 'actions', label: 'Ações', sortable: false, align: 'center' },
];

const teacherHeaders = [
    { key: 'name', label: 'Nome', sortable: true },
    { key: 'email', label: 'E-mail', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true, align: 'center' },
    { key: 'last_login_at', label: 'Último Acesso', sortable: true, align: 'center' },
    { key: 'actions', label: 'Ações', sortable: false, align: 'center' },
];

defineProps({
    teachers: {
        type: Array,
        default: () => [],
    },
    students: {
        type: Array,
        default: () => [],
    },
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const selectedUserId = ref(null);
const activeTab = ref('students'); // 'students' ou 'teachers'

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
    selectedUserId.value = null;
    form.reset();
    form.role = activeTab.value === 'teachers' ? 'teacher' : 'student';
    isModalOpen.value = true;
};

const openEditModal = (user) => {
    isEditing.value = true;
    selectedUserId.value = user.id;
    form.name = user.name;
    form.email = user.email;
    form.role = user.role;
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
        isEditing.value ? 'Salvar Alterações' : 'Cadastrar Membro',
        'Deseja salvar as informações deste membro?',
        () => {
            if (isEditing.value) {
                form.put(route('admin.users.update', selectedUserId.value), {
                    preserveScroll: true,
                    onSuccess: () => {
                        isModalOpen.value = false;
                        form.reset();
                        confirmState.value.show = false;
                    },
                });
            } else {
                form.post(route('admin.users.store'), {
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

const confirmDeleteUser = (id) => {
    triggerConfirm(
        'Excluir Membro',
        'Tem certeza que deseja enviar este membro para a lixeira?',
        () => {
            router.delete(route('admin.users.destroy', id), {
                preserveScroll: true,
                onSuccess: () => {
                    confirmState.value.show = false;
                },
            });
        }
    );
};

const toggleStatus = (user) => {
    const actionText = isActive(user) ? 'desativar' : 'ativar';
    triggerConfirm(
        'Alterar Status',
        `Tem certeza de que deseja ${actionText} o usuário "${user.name}"?`,
        () => {
            router.post(route('admin.users.toggle', user.id), {}, {
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
    <Head title="Gerenciar Membros" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-zinc-100">
                Gerenciar Alunos & Professores da Instituição
            </h2>
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

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <!-- Abas -->
                    <div
                        class="flex rounded-xl border border-zinc-800 bg-zinc-900 p-1"
                    >
                        <button
                            @click="activeTab = 'students'"
                            :class="[
                                'rounded-lg px-4 py-2 text-sm font-semibold transition-all',
                                activeTab === 'students'
                                    ? 'bg-zinc-800 text-white shadow-md'
                                    : 'text-zinc-400 hover:text-zinc-200',
                            ]"
                        >
                            Alunos ({{ students.length }})
                        </button>
                        <button
                            @click="activeTab = 'teachers'"
                            :class="[
                                'rounded-lg px-4 py-2 text-sm font-semibold transition-all',
                                activeTab === 'teachers'
                                    ? 'bg-zinc-800 text-white shadow-md'
                                    : 'text-zinc-400 hover:text-zinc-200',
                            ]"
                        >
                            Professores ({{ teachers.length }})
                        </button>
                    </div>

                    <!-- Botão Novo -->
                    <button
                        @click="openCreateModal"
                        class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-900/30 transition-all duration-200 hover:from-violet-500 hover:to-indigo-500"
                    >
                        + Cadastrar Membro
                    </button>
                </div>

                <!-- Lista de Alunos -->
                <div
                    v-if="activeTab === 'students'"
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <h3 class="mb-4 text-lg font-bold text-white">
                        Estudantes Matriculados
                    </h3>
                    <DataTable
                        :items="students"
                        :columns="studentHeaders"
                        searchPlaceholder="Pesquisar estudantes..."
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
                            <span class="font-bold text-emerald-400">
                                {{ item.points }} XP
                            </span>
                        </template>

                        <template #last_login_at="{ item }">
                            <span class="text-xs text-zinc-400 font-medium">
                                {{ item.last_login_at ? new Date(item.last_login_at).toLocaleString('pt-BR') : 'Nunca' }}
                            </span>
                        </template>

                        <template #actions="{ item }">
                            <div class="flex items-center justify-center gap-1">
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
                                         @click="confirmDeleteUser(item.id)"
                                         class="rounded-lg p-1.5 text-red-500 hover:text-red-400 hover:bg-red-500/10 transition-colors"
                                     >
                                         <Trash2 class="h-4 w-4" />
                                     </button>
                                 </Tooltip>
                            </div>
                        </template>
                    </DataTable>
                </div>

                <!-- Lista de Professores -->
                <div
                    v-if="activeTab === 'teachers'"
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <h3 class="mb-4 text-lg font-bold text-white">
                        Professores da Instituição
                    </h3>
                    <DataTable
                        :items="teachers"
                        :columns="teacherHeaders"
                        searchPlaceholder="Pesquisar professores..."
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

                        <template #last_login_at="{ item }">
                            <span class="text-xs text-zinc-400 font-medium">
                                {{ item.last_login_at ? new Date(item.last_login_at).toLocaleString('pt-BR') : 'Nunca' }}
                            </span>
                        </template>

                        <template #actions="{ item }">
                            <div class="flex items-center justify-center gap-1">
                                <Tooltip text="Editar Professor">
                                    <button
                                        @click="openEditModal(item)"
                                        class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-850 hover:text-white"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                </Tooltip>
                                <Tooltip :text="isActive(item) ? 'Desativar Professor' : 'Ativar Professor'">
                                     <button
                                         @click="toggleStatus(item)"
                                         class="rounded-lg p-1.5 transition-colors"
                                         :class="isActive(item) ? 'text-red-500 hover:text-red-400 hover:bg-red-500/10' : 'text-emerald-500 hover:text-emerald-400 hover:bg-emerald-500/10'"
                                     >
                                         <Power class="h-4 w-4" />
                                     </button>
                                 </Tooltip>
                                 <Tooltip text="Excluir Professor">
                                     <button
                                         @click="confirmDeleteUser(item.id)"
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

        <!-- Member Form Modal -->
        <BaseModal
            :show="isModalOpen"
            :title="isEditing ? 'Editar Membro' : 'Cadastrar Membro'"
            maxWidth="md"
            @close="isModalOpen = false"
        >
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Função / Perfil</label>
                    <SelectInput v-model="form.role" required :disabled="isEditing">
                        <option value="student">Estudante (Aluno)</option>
                        <option value="teacher">Professor</option>
                    </SelectInput>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Nome Completo</label>
                    <TextInput v-model="form.name" type="text" required placeholder="Ex: João Silva" />
                    <span v-if="form.errors.name" class="text-xs text-red-500 mt-1 block">{{ form.errors.name }}</span>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">E-mail</label>
                    <TextInput v-model="form.email" type="email" required placeholder="Ex: joao@instituicao.com" />
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
                        {{ isEditing ? 'Salvar Alterações' : 'Salvar' }}
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
