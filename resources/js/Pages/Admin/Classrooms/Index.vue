<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import DataTable from '@/Components/DataTable.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue';
import Tooltip from '@/Components/Tooltip.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { __ } from '@/i18n';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Power, Trash2 } from '@lucide/vue';
import { ref } from 'vue';

defineProps({
    classrooms: { type: Array, default: () => [] },
    teachers: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
});

const columns = [
    { key: 'name', label: __('classrooms.col_name'), sortable: true },
    { key: 'teacher', label: __('classrooms.col_teacher'), sortable: false },
    {
        key: 'subjects_count',
        label: __('classrooms.col_subjects'),
        sortable: true,
        align: 'center',
    },
    {
        key: 'is_active',
        label: __('common.status'),
        sortable: true,
        align: 'center',
    },
    {
        key: 'actions',
        label: __('common.actions'),
        sortable: false,
        align: 'right',
    },
];

const isModalOpen = ref(false);
const isEditing = ref(false);
const selectedId = ref(null);

const form = useForm({
    name: '',
    description: '',
    teacher_id: '',
    subject_ids: [],
});

const isActive = (item) => {
    const val = item?.is_active;
    if (typeof val === 'object' && val !== null) return val.value === 1;
    return val === 1 || val === true || val === 'active';
};

const openCreate = () => {
    isEditing.value = false;
    selectedId.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEdit = (classroom) => {
    isEditing.value = true;
    selectedId.value = classroom.id;
    form.name = classroom.name;
    form.description = classroom.description || '';
    form.teacher_id = classroom.teacher_id || '';
    form.subject_ids = (classroom.subjects || []).map((s) => s.id);
    isModalOpen.value = true;
};

const toggleSubject = (id) => {
    const i = form.subject_ids.indexOf(id);
    if (i === -1) form.subject_ids.push(id);
    else form.subject_ids.splice(i, 1);
};

const submit = () => {
    const onSuccess = () => {
        isModalOpen.value = false;
        form.reset();
    };

    if (isEditing.value) {
        form.put(route('admin.classrooms.update', selectedId.value), {
            preserveScroll: true,
            onSuccess,
        });
    } else {
        form.post(route('admin.classrooms.store'), {
            preserveScroll: true,
            onSuccess,
        });
    }
};

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

const confirmDelete = (classroom) => {
    triggerConfirm(
        __('classrooms.confirm_delete_title'),
        __('classrooms.confirm_delete_message', { name: classroom.name }),
        'danger',
        () =>
            router.delete(route('admin.classrooms.destroy', classroom.id), {
                preserveScroll: true,
            }),
    );
};

const confirmToggle = (classroom) => {
    const action = isActive(classroom)
        ? __('classrooms.action_deactivate')
        : __('classrooms.action_activate');
    triggerConfirm(
        __('classrooms.confirm_toggle_title'),
        __('classrooms.confirm_toggle_message', {
            action,
            name: classroom.name,
        }),
        'warning',
        () =>
            router.post(
                route('admin.classrooms.toggle', classroom.id),
                {},
                { preserveScroll: true },
            ),
    );
};
</script>

<template>
    <Head :title="__('classrooms.title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="__('classrooms.admin_header')">
                <template #actions>
                    <button
                        @click="openCreate"
                        class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-bold text-white transition-all hover:brightness-110"
                    >
                        <Plus class="h-4 w-4" />
                        <span class="hidden md:inline">{{
                            __('classrooms.new')
                        }}</span>
                    </button>
                </template>
            </PageHeader>
        </template>

        <div class="min-h-[calc(100vh-80px)] bg-zinc-950 py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <DataTable
                        :items="classrooms"
                        :columns="columns"
                        :searchPlaceholder="__('classrooms.search')"
                    >
                        <template #name="{ item }">
                            <div class="font-semibold text-zinc-100">
                                {{ item.name }}
                            </div>
                            <div
                                v-if="item.description"
                                class="max-w-xs truncate text-xs text-zinc-400"
                            >
                                {{ item.description }}
                            </div>
                        </template>

                        <template #teacher="{ item }">
                            <span
                                v-if="item.teacher"
                                class="text-sm text-zinc-300"
                                >{{ item.teacher.name }}</span
                            >
                            <span v-else class="text-xs italic text-zinc-400">{{
                                __('classrooms.no_teacher')
                            }}</span>
                        </template>

                        <template #subjects_count="{ item }">
                            <span
                                class="inline-flex rounded-full bg-indigo-500/10 px-2.5 py-0.5 text-xs font-bold text-indigo-400"
                            >
                                {{
                                    __('classrooms.subjects_count', {
                                        count: item.subjects_count ?? 0,
                                    })
                                }}
                            </span>
                        </template>

                        <template #is_active="{ item }">
                            <span
                                class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold"
                                :class="
                                    isActive(item)
                                        ? 'bg-emerald-500/10 text-emerald-400'
                                        : 'bg-rose-500/10 text-rose-400'
                                "
                            >
                                {{
                                    isActive(item)
                                        ? __('common.active')
                                        : __('common.inactive')
                                }}
                            </span>
                        </template>

                        <template #actions="{ item }">
                            <div class="flex items-center justify-end gap-2">
                                <Tooltip :text="__('common.edit')">
                                    <button
                                        @click="openEdit(item)"
                                        class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-800 hover:text-white"
                                        type="button"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                </Tooltip>
                                <Tooltip
                                    :text="
                                        isActive(item)
                                            ? __('classrooms.action_deactivate')
                                            : __('classrooms.action_activate')
                                    "
                                >
                                    <button
                                        @click="confirmToggle(item)"
                                        class="rounded-lg border border-zinc-800 bg-zinc-900/50 p-1.5 transition-colors"
                                        :class="
                                            isActive(item)
                                                ? 'text-red-500 hover:bg-red-500/10'
                                                : 'text-emerald-500 hover:bg-emerald-500/10'
                                        "
                                        type="button"
                                    >
                                        <Power class="h-4 w-4" />
                                    </button>
                                </Tooltip>
                                <Tooltip :text="__('common.delete')">
                                    <button
                                        @click="confirmDelete(item)"
                                        class="rounded-lg p-1.5 text-red-500 transition-colors hover:bg-red-500/10 hover:text-red-400"
                                        type="button"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </Tooltip>
                            </div>
                        </template>

                        <template #empty>
                            <div
                                class="py-10 text-center text-sm text-zinc-400"
                            >
                                {{ __('classrooms.empty') }}
                            </div>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Modal Criar/Editar Classroom -->
        <BaseModal
            :show="isModalOpen"
            :title="isEditing ? __('classrooms.edit') : __('classrooms.new')"
            maxWidth="2xl"
            @close="isModalOpen = false"
        >
            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <InputLabel
                        for="name"
                        :value="__('classrooms.form_name')"
                    />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        :placeholder="__('classrooms.form_name_placeholder')"
                        required
                    />
                    <span
                        v-if="form.errors.name"
                        class="mt-1 block text-xs text-rose-400"
                        >{{ form.errors.name }}</span
                    >
                </div>

                <div>
                    <InputLabel
                        for="description"
                        :value="__('classrooms.form_description')"
                    />
                    <TextareaInput
                        id="description"
                        v-model="form.description"
                        class="mt-1 block w-full"
                        rows="2"
                    />
                    <span
                        v-if="form.errors.description"
                        class="mt-1 block text-xs text-rose-400"
                        >{{ form.errors.description }}</span
                    >
                </div>

                <div>
                    <InputLabel
                        for="teacher_id"
                        :value="__('classrooms.form_teacher')"
                    />
                    <SelectInput id="teacher_id" v-model="form.teacher_id">
                        <option value="">
                            {{ __('classrooms.form_no_teacher') }}
                        </option>
                        <option v-for="t in teachers" :key="t.id" :value="t.id">
                            {{ t.name }}
                        </option>
                    </SelectInput>
                    <span
                        v-if="form.errors.teacher_id"
                        class="mt-1 block text-xs text-rose-400"
                        >{{ form.errors.teacher_id }}</span
                    >
                </div>

                <div>
                    <InputLabel :value="__('classrooms.form_subjects')" />
                    <p class="mb-2 text-xs text-zinc-400">
                        {{ __('classrooms.form_subjects_hint') }}
                    </p>
                    <div
                        v-if="subjects.length"
                        class="grid max-h-48 grid-cols-1 gap-2 overflow-y-auto rounded-xl border border-zinc-800 bg-zinc-950/40 p-3 sm:grid-cols-2"
                    >
                        <label
                            v-for="s in subjects"
                            :key="s.id"
                            class="flex cursor-pointer items-center gap-2 text-sm text-zinc-300"
                        >
                            <input
                                type="checkbox"
                                :checked="form.subject_ids.includes(s.id)"
                                @change="toggleSubject(s.id)"
                                class="rounded border-zinc-600 bg-zinc-800 text-indigo-600 focus:ring-indigo-500"
                            />
                            <span class="truncate">{{ s.name }}</span>
                        </label>
                    </div>
                    <p v-else class="text-xs italic text-zinc-400">
                        {{ __('classrooms.form_no_subjects_available') }}
                    </p>
                    <span
                        v-if="form.errors.subject_ids"
                        class="mt-1 block text-xs text-rose-400"
                        >{{ form.errors.subject_ids }}</span
                    >
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="isModalOpen = false"
                        class="rounded-xl border border-zinc-700 px-4 py-2.5 text-sm font-semibold text-zinc-300 transition-all hover:bg-zinc-800"
                    >
                        {{ __('common.cancel') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-bold text-white transition-all hover:brightness-110 disabled:opacity-50"
                    >
                        {{
                            form.processing
                                ? __('common.saving')
                                : __('common.save')
                        }}
                    </button>
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
