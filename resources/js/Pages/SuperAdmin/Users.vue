<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Button from '@/Components/Button.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Pencil,
    Plus,
    Power,
    Trash2,
    CheckCircle,
    XCircle,
    UserCheck
} from '@lucide/vue';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    institutions: {
        type: Array,
        default: () => [],
    },
});

const userColumns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'name', label: 'Nome / E-mail', sortable: true },
    { key: 'role', label: 'Papel', sortable: true },
    { key: 'institution', label: 'Instituição(ões)', sortable: false },
    { key: 'points', label: 'XP', sortable: true, align: 'center' },
    { key: 'last_login_at', label: 'Último Acesso', sortable: true },
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

// User Modal & Form
const isUserModalOpen = ref(false);
const isEditingUser = ref(false);
const selectedUserId = ref(null);

const userForm = useForm({
    name: '',
    email: '',
    password: '',
    role: 'student',
    institution_id: '',
    institution_ids: [],
});

const openCreateUser = () => {
    isEditingUser.value = false;
    userForm.reset();
    userForm.institution_ids = [];
    isUserModalOpen.value = true;
};

const openEditUser = (user) => {
    isEditingUser.value = true;
    selectedUserId.value = user.id;
    userForm.name = user.name;
    userForm.email = user.email;
    userForm.password = '';
    userForm.role = user.role;
    userForm.institution_id = user.institution_id || '';
    userForm.institution_ids = user.institution_ids || [];
    isUserModalOpen.value = true;
};

const submitUser = () => {
    // If not admin, clear multi-select to satisfy backend rules
    if (userForm.role !== 'admin') {
        userForm.institution_ids = [];
    } else {
        userForm.institution_id = '';
    }

    if (isEditingUser.value) {
        userForm.put(route('super-admin.users.update', selectedUserId.value), {
            onSuccess: () => {
                isUserModalOpen.value = false;
                userForm.reset();
            },
        });
    } else {
        userForm.post(route('super-admin.users.store'), {
            onSuccess: () => {
                isUserModalOpen.value = false;
                userForm.reset();
            },
        });
    }
};

const confirmToggleUser = (user) => {
    const activeVal = user.is_active === 1 || user.is_active?.value === 1 || user.is_active === 'active';
    const actionText = activeVal ? 'desativar' : 'ativar';
    triggerConfirm(
        'Confirmar Alteração de Status',
        `Tem certeza de que deseja ${actionText} o usuário "${user.name}"?`,
        'warning',
        () => {
            router.post(route('super-admin.users.toggle', user.id));
        },
    );
};

const confirmDeleteUser = (user) => {
    triggerConfirm(
        'Excluir Usuário',
        `Tem certeza de que deseja excluir o usuário "${user.name}"? Ele será enviado para a lixeira.`,
        'danger',
        () => {
            router.delete(route('super-admin.users.destroy', user.id));
        },
    );
};

const confirmImpersonate = (user) => {
    triggerConfirm(
        'Personificar Usuário',
        `Deseja iniciar a personificação da conta de "${user.name}"? Você acessará temporariamente o painel com as permissões dele.`,
        'info',
        () => {
            router.post(route('super-admin.impersonate', user.id));
        },
    );
};

const roleLabel = (role) => {
    switch (role) {
        case 'super_admin': return 'Super Admin';
        case 'admin': return 'Gestor';
        case 'teacher': return 'Professor';
        case 'student': return 'Estudante';
        default: return role;
    }
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return 'Nunca acessou';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return 'Data Inválida';
        return d.toLocaleString('pt-BR');
    } catch (e) {
        return 'Data Inválida';
    }
};
</script>

<template>
    <Head title="Gerenciar Usuários" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold leading-tight text-zinc-100">
                    Gerenciamento de Usuários
                </h2>
                <Button @click="openCreateUser">
                    <template #icon><Plus class="h-4 w-4" /></template>
                    <span class="hidden md:inline">Cadastrar</span>
                </Button>
            </div>
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md">
                <DataTable
                    :items="users"
                    :columns="userColumns"
                    searchPlaceholder="Buscar por nome, e-mail ou instituição..."
                >
                    <template #id="{ item }">
                        <span class="text-zinc-500 font-mono">#{{ item.id }}</span>
                    </template>
                    <template #name="{ item }">
                        <div class="font-semibold text-zinc-150">{{ item.name }}</div>
                        <div class="text-xs text-zinc-500">{{ item.email }}</div>
                    </template>
                    <template #role="{ item }">
                        <span class="inline-flex items-center rounded-full bg-zinc-800 px-2.5 py-0.5 text-xs font-semibold text-zinc-300 border border-zinc-700">
                            {{ roleLabel(item.role) }}
                        </span>
                    </template>
                    <template #institution="{ item }">
                        <div v-if="item.role === 'admin' && item.institutions?.length" class="space-y-0.5 max-w-xs">
                            <span 
                                v-for="inst in item.institutions" 
                                :key="inst.id"
                                class="inline-block text-[10px] bg-indigo-950/40 text-indigo-400 border border-indigo-900/40 rounded px-1.5 py-0.5 mr-1 mb-1 font-semibold"
                            >
                                {{ inst.name }}
                            </span>
                        </div>
                        <div v-else class="text-xs text-zinc-400">
                            {{ item.institution?.name || 'Nenhuma' }}
                        </div>
                    </template>
                    <template #points="{ item }">
                        <span class="font-bold text-indigo-400">{{ item.points ?? 0 }} XP</span>
                    </template>
                    <template #last_login_at="{ item }">
                        <span class="text-xs font-mono text-zinc-450">{{ formatDateTime(item.last_login_at) }}</span>
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
                            <!-- Impersonate Button (Only for roles other than super_admin and if it is active) -->
                            <Tooltip text="Personificar" v-if="item.role !== 'super_admin' && (item.is_active === 1 || item.is_active?.value === 1)">
                                <Button
                                    variant="icon"
                                    @click="confirmImpersonate(item)"
                                    class="text-amber-500 hover:text-amber-400"
                                >
                                    <template #icon><UserCheck class="h-4 w-4" /></template>
                                </Button>
                            </Tooltip>

                            <Tooltip :text="(item.is_active === 1 || item.is_active?.value === 1) ? 'Desativar' : 'Ativar'">
                                <button
                                    @click="confirmToggleUser(item)"
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
                                    @click="openEditUser(item)"
                                    class="text-indigo-400"
                                >
                                    <template #icon><Pencil class="h-4 w-4" /></template>
                                </Button>
                            </Tooltip>

                            <Tooltip text="Excluir">
                                <button
                                    @click="confirmDeleteUser(item)"
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

        <!-- Base Modal: Cadastro / Edição de Usuário -->
        <BaseModal
            :show="isUserModalOpen"
            :title="isEditingUser ? 'Editar Usuário' : 'Novo Usuário'"
            maxWidth="2xl"
            @close="isUserModalOpen = false"
        >
            <form @submit.prevent="submitUser" class="space-y-4">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Nome Completo</label>
                    <input
                        v-model="userForm.name"
                        type="text"
                        required
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Endereço de E-mail</label>
                    <input
                        v-model="userForm.email"
                        type="email"
                        required
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">
                        Senha {{ isEditingUser ? '(Deixe em branco para manter a atual)' : '' }}
                    </label>
                    <input
                        v-model="userForm.password"
                        type="password"
                        :required="!isEditingUser"
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Perfil / Papel</label>
                    <select
                        v-model="userForm.role"
                        required
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none"
                    >
                        <option value="student">Estudante</option>
                        <option value="teacher">Professor</option>
                        <option value="admin">Gestor de Instituição (Admin)</option>
                    </select>
                </div>

                <!-- Single Institution (Student/Teacher) -->
                <div v-if="userForm.role !== 'admin'">
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Instituição</label>
                    <select
                        v-model="userForm.institution_id"
                        required
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none"
                    >
                        <option value="" disabled>Selecione uma instituição</option>
                        <option v-for="inst in institutions" :key="inst.id" :value="inst.id">
                            {{ inst.name }}
                        </option>
                    </select>
                </div>

                <!-- Multi-institution (Admin) -->
                <div v-else>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Associar Instituições</label>
                    <div class="rounded-xl border border-zinc-800 bg-zinc-950 p-4 max-h-48 overflow-y-auto space-y-2">
                        <label 
                            v-for="inst in institutions" 
                            :key="inst.id" 
                            class="flex items-center gap-2 text-sm text-zinc-300 hover:text-white cursor-pointer"
                        >
                            <input
                                type="checkbox"
                                :value="inst.id"
                                v-model="userForm.institution_ids"
                                class="rounded border-zinc-700 bg-zinc-900 text-indigo-600 focus:ring-indigo-500"
                            />
                            <span>{{ inst.name }}</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-850">
                    <Button variant="secondary" type="button" @click="isUserModalOpen = false">
                        Cancelar
                    </Button>
                    <Button type="submit" :disabled="userForm.processing">
                        {{ userForm.processing ? 'Salvando...' : 'Salvar Usuário' }}
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
