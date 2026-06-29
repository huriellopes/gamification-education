<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import DataTable from '@/Components/DataTable.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, Power, Trash2 } from '@lucide/vue';
import { ref } from 'vue';

const studentHeaders = [
    {
        key: 'name',
        label: __('teacher.students.header_student'),
        sortable: true,
    },
    { key: 'classroom', label: __('classrooms.title'), sortable: true },
    {
        key: 'is_active',
        label: __('common.status'),
        sortable: true,
        align: 'center',
    },
    {
        key: 'points',
        label: __('admin.users.col_xp_accumulated'),
        sortable: true,
        align: 'center',
    },
    {
        key: 'last_login_at',
        label: __('admin.users.col_last_access'),
        sortable: true,
        align: 'center',
    },
    {
        key: 'actions',
        label: __('common.actions'),
        sortable: false,
        align: 'center',
    },
];

const teacherHeaders = [
    { key: 'name', label: __('admin.users.role_teacher'), sortable: true },
    {
        key: 'is_active',
        label: __('common.status'),
        sortable: true,
        align: 'center',
    },
    {
        key: 'last_login_at',
        label: __('admin.users.col_last_access'),
        sortable: true,
        align: 'center',
    },
    {
        key: 'actions',
        label: __('common.actions'),
        sortable: false,
        align: 'center',
    },
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
    classrooms: {
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
    classroom_id: '',
});

const isActive = (item) => {
    if (!item) return false;
    const val =
        typeof item === 'object' && 'is_active' in item ? item.is_active : item;
    if (typeof val === 'object' && val !== null) {
        return (
            val.value === 1 ||
            val.value === true ||
            String(val.value) === '1' ||
            val.value === 'active'
        );
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
    form.classroom_id = user.classroom_ids?.[0] ?? '';
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
        isEditing.value
            ? __('admin.users.confirm_save_title')
            : __('admin.users.confirm_create_title'),
        __('admin.users.confirm_save_message'),
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
        },
    );
};

const confirmDeleteUser = (id) => {
    triggerConfirm(
        __('admin.users.confirm_delete_title'),
        __('admin.users.confirm_delete_message'),
        () => {
            router.delete(route('admin.users.destroy', id), {
                preserveScroll: true,
                onSuccess: () => {
                    confirmState.value.show = false;
                },
            });
        },
    );
};

const toggleStatus = (user) => {
    const actionText = isActive(user)
        ? __('admin.users.confirm_toggle_deactivate')
        : __('admin.users.confirm_toggle_activate');
    triggerConfirm(
        __('admin.users.confirm_toggle_title'),
        __('admin.users.confirm_toggle_message', {
            action: actionText,
            name: user.name,
        }),
        () => {
            router.post(
                route('admin.users.toggle', user.id),
                {},
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        confirmState.value.show = false;
                    },
                },
            );
        },
    );
};
</script>

<template>
    <Head :title="__('admin.users.title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-zinc-100">
                {{ __('admin.users.header') }}
            </h2>
        </template>

        <div class="bg-zinc-955 min-h-[calc(100vh-80px)] py-12 text-zinc-100">
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
                            {{ __('admin.users.tab_students') }} ({{
                                students.length
                            }})
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
                            {{ __('admin.users.tab_teachers') }} ({{
                                teachers.length
                            }})
                        </button>
                    </div>

                    <!-- Botão Novo -->
                    <button
                        @click="openCreateModal"
                        class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-900/30 transition-all duration-200 hover:from-violet-500 hover:to-indigo-500"
                    >
                        + {{ __('admin.users.register_member') }}
                    </button>
                </div>

                <!-- Lista de Alunos -->
                <div
                    v-if="activeTab === 'students'"
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <h3 class="mb-4 text-lg font-bold text-white">
                        {{ __('admin.users.enrolled_students') }}
                    </h3>
                    <DataTable
                        :items="students"
                        :columns="studentHeaders"
                        :searchKeys="['email']"
                        :searchPlaceholder="__('admin.users.search_students')"
                    >
                        <template #name="{ item }">
                            <div class="font-semibold text-zinc-100">
                                {{ item.name }}
                            </div>
                            <div class="text-xs text-zinc-500">
                                {{ item.email }}
                            </div>
                        </template>

                        <template #classroom="{ item }">
                            <span
                                v-if="item.classroom"
                                class="inline-flex rounded-full bg-zinc-800 px-2.5 py-0.5 text-xs font-semibold text-zinc-300"
                            >
                                {{ item.classroom }}
                            </span>
                            <span v-else class="text-xs italic text-zinc-600">{{
                                __('classrooms.enroll_none')
                            }}</span>
                        </template>

                        <template #is_active="{ item }">
                            <span
                                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                :class="
                                    isActive(item)
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : 'bg-red-100 text-red-800'
                                "
                            >
                                {{
                                    isActive(item)
                                        ? __('common.active')
                                        : __('common.inactive')
                                }}
                            </span>
                        </template>

                        <template #points="{ item }">
                            <span class="font-bold text-emerald-400">
                                {{ item.points }} XP
                            </span>
                        </template>

                        <template #last_login_at="{ item }">
                            <span class="text-xs font-medium text-zinc-400">
                                {{
                                    item.last_login_at
                                        ? new Date(
                                              item.last_login_at,
                                          ).toLocaleString('pt-BR')
                                        : __('admin.users.never')
                                }}
                            </span>
                        </template>

                        <template #actions="{ item }">
                            <div class="flex items-center justify-center gap-1">
                                <Tooltip :text="__('admin.users.edit_student')">
                                    <button
                                        @click="openEditModal(item)"
                                        class="hover:bg-zinc-850 rounded-lg p-1.5 text-zinc-400 transition-colors hover:text-white"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                </Tooltip>
                                <Tooltip
                                    :text="
                                        isActive(item)
                                            ? __(
                                                  'admin.users.deactivate_student',
                                              )
                                            : __('admin.users.activate_student')
                                    "
                                >
                                    <button
                                        @click="toggleStatus(item)"
                                        class="rounded-lg p-1.5 transition-colors"
                                        :class="
                                            isActive(item)
                                                ? 'text-red-500 hover:bg-red-500/10 hover:text-red-400'
                                                : 'text-emerald-500 hover:bg-emerald-500/10 hover:text-emerald-400'
                                        "
                                    >
                                        <Power class="h-4 w-4" />
                                    </button>
                                </Tooltip>
                                <Tooltip
                                    :text="__('admin.users.delete_student')"
                                >
                                    <button
                                        @click="confirmDeleteUser(item.id)"
                                        class="rounded-lg p-1.5 text-red-500 transition-colors hover:bg-red-500/10 hover:text-red-400"
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
                        {{ __('admin.users.institution_teachers') }}
                    </h3>
                    <DataTable
                        :items="teachers"
                        :columns="teacherHeaders"
                        :searchKeys="['email']"
                        :searchPlaceholder="__('admin.users.search_teachers')"
                    >
                        <template #name="{ item }">
                            <div class="font-semibold text-zinc-100">
                                {{ item.name }}
                            </div>
                            <div class="text-xs text-zinc-500">
                                {{ item.email }}
                            </div>
                        </template>

                        <template #is_active="{ item }">
                            <span
                                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                :class="
                                    isActive(item)
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : 'bg-red-100 text-red-800'
                                "
                            >
                                {{
                                    isActive(item)
                                        ? __('common.active')
                                        : __('common.inactive')
                                }}
                            </span>
                        </template>

                        <template #last_login_at="{ item }">
                            <span class="text-xs font-medium text-zinc-400">
                                {{
                                    item.last_login_at
                                        ? new Date(
                                              item.last_login_at,
                                          ).toLocaleString('pt-BR')
                                        : __('admin.users.never')
                                }}
                            </span>
                        </template>

                        <template #actions="{ item }">
                            <div class="flex items-center justify-center gap-1">
                                <Tooltip :text="__('admin.users.edit_teacher')">
                                    <button
                                        @click="openEditModal(item)"
                                        class="hover:bg-zinc-850 rounded-lg p-1.5 text-zinc-400 transition-colors hover:text-white"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                </Tooltip>
                                <Tooltip
                                    :text="
                                        isActive(item)
                                            ? __(
                                                  'admin.users.deactivate_teacher',
                                              )
                                            : __('admin.users.activate_teacher')
                                    "
                                >
                                    <button
                                        @click="toggleStatus(item)"
                                        class="rounded-lg p-1.5 transition-colors"
                                        :class="
                                            isActive(item)
                                                ? 'text-red-500 hover:bg-red-500/10 hover:text-red-400'
                                                : 'text-emerald-500 hover:bg-emerald-500/10 hover:text-emerald-400'
                                        "
                                    >
                                        <Power class="h-4 w-4" />
                                    </button>
                                </Tooltip>
                                <Tooltip
                                    :text="__('admin.users.delete_teacher')"
                                >
                                    <button
                                        @click="confirmDeleteUser(item.id)"
                                        class="rounded-lg p-1.5 text-red-500 transition-colors hover:bg-red-500/10 hover:text-red-400"
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
            :title="
                isEditing
                    ? __('admin.users.modal_edit')
                    : __('admin.users.register_member')
            "
            maxWidth="md"
            @close="isModalOpen = false"
        >
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('admin.users.role_label') }}</label
                    >
                    <SelectInput
                        v-model="form.role"
                        required
                        :disabled="isEditing"
                    >
                        <option value="student">
                            {{ __('admin.users.role_student') }}
                        </option>
                        <option value="teacher">
                            {{ __('admin.users.role_teacher') }}
                        </option>
                    </SelectInput>
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('admin.users.full_name') }}</label
                    >
                    <TextInput
                        v-model="form.name"
                        type="text"
                        required
                        :placeholder="__('admin.users.name_placeholder')"
                    />
                    <span
                        v-if="form.errors.name"
                        class="mt-1 block text-xs text-red-500"
                        >{{ form.errors.name }}</span
                    >
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('common.email') }}</label
                    >
                    <TextInput
                        v-model="form.email"
                        type="email"
                        required
                        :placeholder="__('admin.users.email_placeholder')"
                    />
                    <span
                        v-if="form.errors.email"
                        class="mt-1 block text-xs text-red-500"
                        >{{ form.errors.email }}</span
                    >
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('admin.users.password_label') }}
                        {{
                            isEditing ? __('admin.users.password_hint') : ''
                        }}</label
                    >
                    <TextInput
                        v-model="form.password"
                        type="password"
                        :required="!isEditing"
                        placeholder="••••••••"
                    />
                    <span
                        v-if="form.errors.password"
                        class="mt-1 block text-xs text-red-500"
                        >{{ form.errors.password }}</span
                    >
                </div>

                <div v-if="form.role === 'student'">
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('classrooms.enroll_label') }}</label
                    >
                    <SelectInput v-model="form.classroom_id">
                        <option value="">
                            {{ __('classrooms.enroll_none') }}
                        </option>
                        <option
                            v-for="c in classrooms"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.name }}
                        </option>
                    </SelectInput>
                    <span
                        v-if="form.errors.classroom_id"
                        class="mt-1 block text-xs text-red-500"
                        >{{ form.errors.classroom_id }}</span
                    >
                </div>

                <div class="flex justify-end gap-3 pt-3">
                    <button
                        type="button"
                        @click="isModalOpen = false"
                        class="rounded-xl bg-zinc-800 px-5 py-2.5 text-xs font-bold text-zinc-400 transition-colors hover:bg-zinc-700"
                    >
                        {{ __('common.cancel') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white transition-colors hover:bg-indigo-500 disabled:opacity-50"
                    >
                        {{
                            isEditing
                                ? __('admin.users.confirm_save_title')
                                : __('common.save')
                        }}
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
