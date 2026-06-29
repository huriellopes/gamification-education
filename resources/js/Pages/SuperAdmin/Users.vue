<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import Button from '@/Components/Button.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import DataTable from '@/Components/DataTable.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    CheckCircle,
    Pencil,
    Plus,
    Power,
    Trash2,
    UserCheck,
    XCircle,
} from '@lucide/vue';
import { computed, ref } from 'vue';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    institutions: {
        type: Array,
        default: () => [],
    },
    classrooms: {
        type: Array,
        default: () => [],
    },
});

const userColumns = [
    { key: 'id', label: 'ID', sortable: true },
    {
        key: 'name',
        label: __('superadmin.users.col_name_email'),
        sortable: true,
    },
    { key: 'role', label: __('superadmin.users.col_role'), sortable: true },
    {
        key: 'institution',
        label: __('superadmin.users.col_institution'),
        sortable: false,
    },
    {
        key: 'points',
        label: __('superadmin.users.col_xp'),
        sortable: true,
        align: 'center',
    },
    {
        key: 'last_login_at',
        label: __('superadmin.users.col_last_login'),
        sortable: true,
    },
    {
        key: 'is_active',
        label: __('common.status'),
        sortable: true,
        align: 'center',
    },
    { key: 'actions', label: __('common.actions'), align: 'right' },
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
    classroom_id: '',
});

// Turmas da instituição selecionada (para vincular um aluno).
const availableClassrooms = computed(() =>
    props.classrooms.filter(
        (c) => String(c.institution_id) === String(userForm.institution_id),
    ),
);

const openCreateUser = () => {
    isEditingUser.value = false;
    userForm.reset();
    userForm.institution_ids = [];
    userForm.classroom_id = '';
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
    userForm.classroom_id = user.classroom_ids?.[0] ?? '';
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
    const activeVal =
        user.is_active === 1 ||
        user.is_active?.value === 1 ||
        user.is_active === 'active';
    const actionText = activeVal
        ? __('superadmin.users.action_deactivate')
        : __('superadmin.users.action_activate');
    triggerConfirm(
        __('superadmin.users.confirm_status_title'),
        __('superadmin.users.confirm_status_message', {
            action: actionText,
            name: user.name,
        }),
        'warning',
        () => {
            router.post(route('super-admin.users.toggle', user.id));
        },
    );
};

const confirmDeleteUser = (user) => {
    triggerConfirm(
        __('superadmin.users.confirm_delete_title'),
        __('superadmin.users.confirm_delete_message', { name: user.name }),
        'danger',
        () => {
            router.delete(route('super-admin.users.destroy', user.id));
        },
    );
};

const confirmImpersonate = (user) => {
    triggerConfirm(
        __('superadmin.users.confirm_impersonate_title'),
        __('superadmin.users.confirm_impersonate_message', { name: user.name }),
        'info',
        () => {
            router.post(route('super-admin.impersonate', user.id));
        },
    );
};

const roleLabel = (role) => {
    switch (role) {
        case 'super_admin':
            return __('superadmin.users.role_super_admin');
        case 'admin':
            return __('superadmin.users.role_admin');
        case 'teacher':
            return __('superadmin.users.role_teacher');
        case 'student':
            return __('superadmin.users.role_student');
        default:
            return role;
    }
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return __('superadmin.users.never_logged_in');
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return __('superadmin.users.invalid_date');
        return d.toLocaleString('pt-BR');
    } catch (e) {
        return __('superadmin.users.invalid_date');
    }
};
</script>

<template>
    <Head :title="__('superadmin.users.head_title')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold leading-tight text-zinc-100">
                    {{ __('superadmin.users.header') }}
                </h2>
                <Button @click="openCreateUser">
                    <template #icon><Plus class="h-4 w-4" /></template>
                    <span class="hidden md:inline">{{
                        __('superadmin.users.register')
                    }}</span>
                </Button>
            </div>
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100">
            <div
                class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
            >
                <DataTable
                    :items="users"
                    :columns="userColumns"
                    :searchPlaceholder="
                        __('superadmin.users.search_placeholder')
                    "
                >
                    <template #id="{ item }">
                        <span class="font-mono text-zinc-500"
                            >#{{ item.id }}</span
                        >
                    </template>
                    <template #name="{ item }">
                        <div class="text-zinc-150 font-semibold">
                            {{ item.name }}
                        </div>
                        <div class="text-xs text-zinc-500">
                            {{ item.email }}
                        </div>
                    </template>
                    <template #role="{ item }">
                        <span
                            class="inline-flex items-center rounded-full border border-zinc-700 bg-zinc-800 px-2.5 py-0.5 text-xs font-semibold text-zinc-300"
                        >
                            {{ roleLabel(item.role) }}
                        </span>
                    </template>
                    <template #institution="{ item }">
                        <div
                            v-if="
                                item.role === 'admin' &&
                                item.institutions?.length
                            "
                            class="max-w-xs space-y-0.5"
                        >
                            <span
                                v-for="inst in item.institutions"
                                :key="inst.id"
                                class="mb-1 mr-1 inline-block rounded border border-indigo-900/40 bg-indigo-950/40 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-400"
                            >
                                {{ inst.name }}
                            </span>
                        </div>
                        <div v-else class="text-xs text-zinc-400">
                            {{
                                item.institution?.name ||
                                __('superadmin.users.none')
                            }}
                        </div>
                    </template>
                    <template #points="{ item }">
                        <span class="font-bold text-indigo-400"
                            >{{ item.points ?? 0 }} XP</span
                        >
                    </template>
                    <template #last_login_at="{ item }">
                        <span class="text-zinc-450 font-mono text-xs">{{
                            formatDateTime(item.last_login_at)
                        }}</span>
                    </template>
                    <template #is_active="{ item }">
                        <span
                            v-if="
                                item.is_active === 1 ||
                                item.is_active?.value === 1
                            "
                            class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-400"
                        >
                            <CheckCircle class="h-3 w-3" />
                            {{ __('superadmin.users.active') }}
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1 rounded-full bg-rose-500/10 px-2.5 py-1 text-xs font-bold text-rose-400"
                        >
                            <XCircle class="h-3 w-3" />
                            {{ __('superadmin.users.inactive') }}
                        </span>
                    </template>
                    <template #actions="{ item }">
                        <div class="flex items-center justify-end gap-2">
                            <!-- Impersonate Button (Only for roles other than super_admin and if it is active) -->
                            <Tooltip
                                :text="
                                    __('superadmin.users.tooltip_impersonate')
                                "
                                v-if="
                                    item.role !== 'super_admin' &&
                                    (item.is_active === 1 ||
                                        item.is_active?.value === 1)
                                "
                            >
                                <Button
                                    variant="icon"
                                    @click="confirmImpersonate(item)"
                                    class="text-amber-500 hover:text-amber-400"
                                >
                                    <template #icon
                                        ><UserCheck class="h-4 w-4"
                                    /></template>
                                </Button>
                            </Tooltip>

                            <Tooltip
                                :text="
                                    item.is_active === 1 ||
                                    item.is_active?.value === 1
                                        ? __(
                                              'superadmin.users.tooltip_deactivate',
                                          )
                                        : __(
                                              'superadmin.users.tooltip_activate',
                                          )
                                "
                            >
                                <button
                                    @click="confirmToggleUser(item)"
                                    class="rounded-lg border border-zinc-800 bg-zinc-900/50 p-1.5 transition-colors"
                                    :class="
                                        item.is_active === 1 ||
                                        item.is_active?.value === 1
                                            ? 'text-red-500 hover:bg-red-500/10 hover:text-red-400'
                                            : 'text-emerald-500 hover:bg-emerald-500/10 hover:text-emerald-400'
                                    "
                                    type="button"
                                >
                                    <Power class="h-4 w-4" />
                                </button>
                            </Tooltip>

                            <Tooltip
                                :text="__('superadmin.users.tooltip_edit')"
                            >
                                <Button
                                    variant="icon"
                                    @click="openEditUser(item)"
                                    class="text-indigo-400"
                                >
                                    <template #icon
                                        ><Pencil class="h-4 w-4"
                                    /></template>
                                </Button>
                            </Tooltip>

                            <Tooltip
                                :text="__('superadmin.users.tooltip_delete')"
                            >
                                <button
                                    @click="confirmDeleteUser(item)"
                                    class="rounded-lg border border-zinc-800 bg-zinc-900/50 p-1.5 text-red-500 transition-colors hover:bg-red-500/10 hover:text-red-400"
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
            :title="
                isEditingUser
                    ? __('superadmin.users.modal_edit_title')
                    : __('superadmin.users.modal_new_title')
            "
            maxWidth="2xl"
            @close="isUserModalOpen = false"
        >
            <form @submit.prevent="submitUser" class="space-y-4">
                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('superadmin.users.label_full_name') }}</label
                    >
                    <TextInput v-model="userForm.name" type="text" required />
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('superadmin.users.label_email') }}</label
                    >
                    <TextInput v-model="userForm.email" type="email" required />
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                    >
                        {{ __('superadmin.users.label_password') }}
                        {{
                            isEditingUser
                                ? __('superadmin.users.label_password_keep')
                                : ''
                        }}
                    </label>
                    <TextInput
                        v-model="userForm.password"
                        type="password"
                        :required="!isEditingUser"
                    />
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('superadmin.users.label_role') }}</label
                    >
                    <SelectInput v-model="userForm.role" required>
                        <option value="student">
                            {{ __('superadmin.users.option_student') }}
                        </option>
                        <option value="teacher">
                            {{ __('superadmin.users.option_teacher') }}
                        </option>
                        <option value="admin">
                            {{ __('superadmin.users.option_admin') }}
                        </option>
                    </SelectInput>
                </div>

                <!-- Single Institution (Student/Teacher) -->
                <div v-if="userForm.role !== 'admin'">
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('superadmin.users.label_institution') }}</label
                    >
                    <SelectInput v-model="userForm.institution_id" required>
                        <option value="" disabled>
                            {{ __('superadmin.users.select_institution') }}
                        </option>
                        <option
                            v-for="inst in institutions"
                            :key="inst.id"
                            :value="inst.id"
                        >
                            {{ inst.name }}
                        </option>
                    </SelectInput>
                </div>

                <!-- Turma do aluno (vínculo opcional) -->
                <div v-if="userForm.role === 'student'">
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('classrooms.enroll_label') }}</label
                    >
                    <SelectInput
                        v-model="userForm.classroom_id"
                        :disabled="!userForm.institution_id"
                    >
                        <option value="">
                            {{ __('classrooms.enroll_none') }}
                        </option>
                        <option
                            v-for="c in availableClassrooms"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.name }}
                        </option>
                    </SelectInput>
                </div>

                <!-- Multi-institution (Admin) -->
                <div v-else>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{
                            __('superadmin.users.label_associate_institutions')
                        }}</label
                    >
                    <div
                        class="max-h-48 space-y-2 overflow-y-auto rounded-xl border border-zinc-800 bg-zinc-950 p-4"
                    >
                        <label
                            v-for="inst in institutions"
                            :key="inst.id"
                            class="flex cursor-pointer items-center gap-2 text-sm text-zinc-300 hover:text-white"
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

                <div
                    class="border-zinc-850 flex justify-end gap-3 border-t pt-4"
                >
                    <Button
                        variant="secondary"
                        type="button"
                        @click="isUserModalOpen = false"
                    >
                        {{ __('common.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="userForm.processing">
                        {{
                            userForm.processing
                                ? __('superadmin.users.saving')
                                : __('superadmin.users.save_user')
                        }}
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
