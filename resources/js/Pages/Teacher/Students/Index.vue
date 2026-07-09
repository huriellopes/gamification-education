<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import DataTable from '@/Components/DataTable.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Power, Trash2 } from '@lucide/vue';
import { ref } from 'vue';

defineProps({
    students: {
        type: Array,
        default: () => [],
    },
    classrooms: {
        type: Array,
        default: () => [],
    },
});

const studentHeaders = [
    {
        key: 'name',
        label: __('teacher.students.header_student'),
        sortable: true,
    },
    { key: 'classroom', label: __('classrooms.title'), sortable: true },
    {
        key: 'is_active',
        label: __('teacher.students.header_status'),
        sortable: true,
        align: 'center',
    },
    {
        key: 'points',
        label: __('teacher.students.header_xp'),
        sortable: true,
        align: 'center',
    },
    {
        key: 'actions',
        label: __('teacher.students.header_actions'),
        sortable: false,
        align: 'center',
    },
];

const isModalOpen = ref(false);
const isEditing = ref(false);
const selectedStudentId = ref(null);

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
    form.classroom_id = student.classroom_ids?.[0] ?? '';
    isModalOpen.value = true;
};

const confirmState = ref({
    show: false,
    title: '',
    message: '',
    onConfirm: null,
});

// Processamento das ações do modal feitas via router (delete/toggle), que —
// diferente do useForm — não expõem um flag reativo próprio.
const confirmProcessing = ref(false);

const triggerConfirm = (title, message, onConfirm) => {
    confirmState.value.title = title;
    confirmState.value.message = message;
    confirmState.value.onConfirm = onConfirm;
    confirmState.value.show = true;
};

const submit = () => {
    triggerConfirm(
        isEditing.value
            ? __('teacher.student_confirm.save_changes_title')
            : __('teacher.student_confirm.register_title'),
        __('teacher.student_confirm.save_message'),
        () => {
            if (isEditing.value) {
                form.put(
                    route('teacher.students.update', selectedStudentId.value),
                    {
                        preserveScroll: true,
                        onSuccess: () => {
                            isModalOpen.value = false;
                            form.reset();
                            confirmState.value.show = false;
                        },
                    },
                );
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
        },
    );
};

const confirmDeleteStudent = (id) => {
    triggerConfirm(
        __('teacher.student_confirm.delete_title'),
        __('teacher.student_confirm.delete_message'),
        () => {
            router.delete(route('teacher.students.destroy', id), {
                preserveScroll: true,
                onStart: () => (confirmProcessing.value = true),
                onFinish: () => (confirmProcessing.value = false),
                onSuccess: () => {
                    confirmState.value.show = false;
                },
            });
        },
    );
};

const toggleStatus = (student) => {
    const actionText = isActive(student)
        ? __('teacher.student_confirm.toggle_action_deactivate')
        : __('teacher.student_confirm.toggle_action_activate');
    triggerConfirm(
        __('teacher.student_confirm.toggle_title'),
        __('teacher.student_confirm.toggle_message')
            .replace(':action', actionText)
            .replace(':name', student.name),
        () => {
            router.post(
                route('teacher.students.toggle', student.id),
                {},
                {
                    preserveScroll: true,
                    onStart: () => (confirmProcessing.value = true),
                    onFinish: () => (confirmProcessing.value = false),
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
    <Head :title="__('teacher.students.title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="__('teacher.students.header')">
                <template #actions>
                    <button
                        @click="openCreateModal"
                        class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-900/30 transition-all duration-200 hover:from-violet-500 hover:to-indigo-500"
                        :title="__('teacher.students.register_student')"
                    >
                        <Plus class="h-4 w-4 shrink-0" />
                        <span class="hidden md:inline">{{
                            __('teacher.students.register_student')
                        }}</span>
                    </button>
                </template>
            </PageHeader>
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

                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <h3 class="mb-4 text-lg font-bold text-white">
                        {{ __('teacher.students.enrolled_title') }}
                    </h3>
                    <DataTable
                        :items="students"
                        :columns="studentHeaders"
                        :searchKeys="['email']"
                        :searchPlaceholder="
                            __('teacher.students.search_placeholder')
                        "
                    >
                        <template #name="{ item }">
                            <div class="font-semibold text-zinc-100">
                                {{ item.name }}
                            </div>
                            <div class="text-xs text-zinc-400">
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
                            <span v-else class="text-xs italic text-zinc-400">{{
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
                                        ? __('teacher.students.active')
                                        : __('teacher.students.inactive')
                                }}
                            </span>
                        </template>

                        <template #points="{ item }">
                            <span class="font-bold text-indigo-400">
                                {{ item.points }} XP
                            </span>
                        </template>

                        <template #actions="{ item }">
                            <div class="flex items-center justify-center gap-1">
                                <Tooltip
                                    :text="
                                        __(
                                            'teacher.students.tooltip_performance',
                                        )
                                    "
                                >
                                    <Link
                                        :href="
                                            route(
                                                'teacher.students.performance',
                                                item.id,
                                            )
                                        "
                                        class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-850 hover:text-white"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                </Tooltip>
                                <Tooltip
                                    :text="__('teacher.students.tooltip_edit')"
                                >
                                    <button
                                        @click="openEditModal(item)"
                                        class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-850 hover:text-white"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                </Tooltip>
                                <Tooltip
                                    :text="
                                        isActive(item)
                                            ? __(
                                                  'teacher.students.tooltip_deactivate',
                                              )
                                            : __(
                                                  'teacher.students.tooltip_activate',
                                              )
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
                                    :text="
                                        __('teacher.students.tooltip_delete')
                                    "
                                >
                                    <button
                                        @click="confirmDeleteStudent(item.id)"
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

        <!-- Student Form Modal -->
        <BaseModal
            :show="isModalOpen"
            :title="
                isEditing
                    ? __('teacher.student_form.edit_title')
                    : __('teacher.student_form.register_title')
            "
            maxWidth="md"
            @close="isModalOpen = false"
        >
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('teacher.student_form.name_label') }}</label
                    >
                    <TextInput
                        v-model="form.name"
                        type="text"
                        required
                        :placeholder="
                            __('teacher.student_form.name_placeholder')
                        "
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
                        >{{ __('teacher.student_form.email_label') }}</label
                    >
                    <TextInput
                        v-model="form.email"
                        type="email"
                        required
                        :placeholder="
                            __('teacher.student_form.email_placeholder')
                        "
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
                        >{{ __('teacher.student_form.password_label') }}
                        {{
                            isEditing
                                ? __('teacher.student_form.password_hint')
                                : ''
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

                <div>
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
                            form.processing
                                ? isEditing
                                    ? __('common.saving')
                                    : __('common.registering')
                                : isEditing
                                  ? __('teacher.student_form.save_changes')
                                  : __('teacher.student_form.register')
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
            :processing="form.processing || confirmProcessing"
            @close="confirmState.show = false"
            @confirm="confirmState.onConfirm"
        />
    </AuthenticatedLayout>
</template>
