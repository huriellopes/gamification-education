<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue';
import TextInput from '@/Components/TextInput.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { slugify } from '@/Utils/mask';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { FileText, ListChecks, Pencil, Plus, Trash2 } from '@lucide/vue';
import { ref, watch } from 'vue';

defineProps({
    subjects: { type: Array, default: () => [] },
    classrooms: { type: Array, default: () => [] },
});

const isSubjectModalOpen = ref(false);
const isEditingSubject = ref(false);
const selectedSubjectId = ref(null);
const wasSlugManuallyEdited = ref(false);

const subForm = useForm({
    name: '',
    slug: '',
    description: '',
    duration: '',
    classroom_id: '',
});

watch(
    () => subForm.name,
    (newName) => {
        if (!isEditingSubject.value && !wasSlugManuallyEdited.value) {
            subForm.slug = slugify(newName);
        }
    },
);

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
    subForm.classroom_id = subject.classroom_id ?? '';
    isSubjectModalOpen.value = true;
};

const submitSubject = () => {
    const onSuccess = () => {
        isSubjectModalOpen.value = false;
        subForm.reset();
    };

    if (isEditingSubject.value) {
        subForm.put(route('teacher.subjects.update', selectedSubjectId.value), {
            onSuccess,
        });
    } else {
        subForm.post(route('teacher.subjects.store'), { onSuccess });
    }
};

const isConfirmDeleteOpen = ref(false);
const subjectToDelete = ref(null);

const confirmDeleteSubject = (id) => {
    subjectToDelete.value = id;
    isConfirmDeleteOpen.value = true;
};

const deleteSubject = () => {
    if (subjectToDelete.value) {
        subForm.delete(
            route('teacher.subjects.destroy', subjectToDelete.value),
            {
                onSuccess: () => {
                    isConfirmDeleteOpen.value = false;
                    subjectToDelete.value = null;
                },
            },
        );
    }
};
</script>

<template>
    <Head :title="__('nav.sidebar.subjects')" />

    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <h2 class="text-xl font-bold leading-tight text-zinc-100">
                    {{ __('nav.aria.subjects') }}
                </h2>
                <button
                    @click="openCreateModal"
                    class="to-violet-650 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 px-4 py-2.5 text-xs font-bold text-white transition-all hover:brightness-110"
                    :title="__('teacher.dashboard.new_subject')"
                >
                    <Plus class="h-4 w-4" />
                    <span class="hidden md:inline">{{
                        __('teacher.dashboard.new_subject')
                    }}</span>
                </button>
            </div>
        </template>

        <div class="min-h-[calc(100vh-80px)] bg-zinc-950 py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <div
                    v-if="$page.props.flash?.success"
                    class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-400"
                >
                    {{ $page.props.flash.success }}
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
                                    <p
                                        v-if="sub.classroom"
                                        class="mt-1 text-xs font-semibold text-indigo-400"
                                    >
                                        {{ sub.classroom.name }}
                                    </p>
                                    <p class="mt-1 text-xs text-zinc-500">
                                        {{
                                            __(
                                                'teacher.dashboard.duration',
                                            ).replace(
                                                ':value',
                                                sub.duration ||
                                                    __(
                                                        'teacher.dashboard.duration_not_informed',
                                                    ),
                                            )
                                        }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Tooltip
                                        :text="
                                            __('teacher.dashboard.edit_subject')
                                        "
                                    >
                                        <button
                                            @click="openEditModal(sub)"
                                            class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-800 hover:text-white"
                                            type="button"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                    </Tooltip>
                                    <Tooltip
                                        :text="
                                            __(
                                                'teacher.dashboard.delete_subject',
                                            )
                                        "
                                    >
                                        <button
                                            @click="
                                                confirmDeleteSubject(sub.id)
                                            "
                                            class="rounded-lg p-1.5 text-red-500 transition-colors hover:bg-red-500/10 hover:text-red-400"
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
                                    __('teacher.dashboard.no_description')
                                }}
                            </p>
                            <div
                                class="flex items-center gap-4 text-xs font-semibold text-zinc-500"
                            >
                                <span class="inline-flex items-center gap-1">
                                    <FileText class="h-4 w-4" />
                                    {{ sub.study_materials_count || 0 }}
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <ListChecks class="h-4 w-4" />
                                    {{ sub.tests_count || 0 }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 pt-4">
                            <Link
                                :href="route('teacher.subjects.show', sub.id)"
                                class="inline-flex w-full justify-center rounded-xl bg-zinc-800 px-4 py-2.5 text-xs font-bold text-white transition-colors hover:bg-indigo-600"
                            >
                                {{ __('teacher.dashboard.manage_content') }}
                                &rarr;
                            </Link>
                        </div>
                    </div>

                    <div
                        v-if="subjects.length === 0"
                        class="col-span-full py-16 text-center text-zinc-500"
                    >
                        {{ __('teacher.dashboard.empty') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Subject Modal -->
        <BaseModal
            :show="isSubjectModalOpen"
            :title="
                isEditingSubject
                    ? __('teacher.subject_form.edit_title')
                    : __('teacher.subject_form.new_title')
            "
            maxWidth="xl"
            @close="isSubjectModalOpen = false"
        >
            <form @submit.prevent="submitSubject" class="space-y-4">
                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('teacher.subject_form.name_label') }}</label
                    >
                    <TextInput
                        v-model="subForm.name"
                        type="text"
                        required
                        :placeholder="
                            __('teacher.subject_form.name_placeholder')
                        "
                    />
                    <span
                        v-if="subForm.errors.name"
                        class="mt-1 block text-xs text-red-500"
                        >{{ subForm.errors.name }}</span
                    >
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('teacher.subject_form.slug_label') }}</label
                    >
                    <TextInput
                        :model-value="subForm.slug"
                        @input="
                            wasSlugManuallyEdited = true;
                            subForm.slug = slugify($event.target.value);
                        "
                        type="text"
                        required
                        :placeholder="
                            __('teacher.subject_form.slug_placeholder')
                        "
                    />
                    <span
                        v-if="subForm.errors.slug"
                        class="mt-1 block text-xs text-red-500"
                        >{{ subForm.errors.slug }}</span
                    >
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('teacher.subject_form.duration_label') }}</label
                    >
                    <TextInput
                        v-model="subForm.duration"
                        type="text"
                        required
                        :placeholder="
                            __('teacher.subject_form.duration_placeholder')
                        "
                    />
                    <span
                        v-if="subForm.errors.duration"
                        class="mt-1 block text-xs text-red-500"
                        >{{ subForm.errors.duration }}</span
                    >
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('teacher.subject_form.classroom_label') }}</label
                    >
                    <SelectInput v-model="subForm.classroom_id">
                        <option value="">
                            {{ __('teacher.subject_form.classroom_none') }}
                        </option>
                        <option
                            v-for="classroom in classrooms"
                            :key="classroom.id"
                            :value="classroom.id"
                        >
                            {{ classroom.name }}
                        </option>
                    </SelectInput>
                    <span
                        v-if="subForm.errors.classroom_id"
                        class="mt-1 block text-xs text-red-500"
                        >{{ subForm.errors.classroom_id }}</span
                    >
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{
                            __('teacher.subject_form.description_label')
                        }}</label
                    >
                    <TextareaInput
                        v-model="subForm.description"
                        rows="3"
                        :placeholder="
                            __('teacher.subject_form.description_placeholder')
                        "
                    />
                    <span
                        v-if="subForm.errors.description"
                        class="mt-1 block text-xs text-red-500"
                        >{{ subForm.errors.description }}</span
                    >
                </div>

                <div class="flex justify-end gap-3 pt-3">
                    <button
                        type="button"
                        @click="isSubjectModalOpen = false"
                        class="rounded-xl bg-zinc-800 px-5 py-2.5 text-xs font-bold text-zinc-400 transition-colors hover:bg-zinc-700"
                    >
                        {{ __('common.cancel') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="subForm.processing"
                        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white transition-colors hover:bg-indigo-500 disabled:opacity-50"
                    >
                        {{
                            isEditingSubject
                                ? __('teacher.subject_form.save_changes')
                                : __('teacher.subject_form.create')
                        }}
                    </button>
                </div>
            </form>
        </BaseModal>

        <ConfirmModal
            :show="isConfirmDeleteOpen"
            :title="__('teacher.subject_delete.title')"
            :message="__('teacher.subject_delete.message')"
            @close="isConfirmDeleteOpen = false"
            @confirm="deleteSubject"
        />
    </AuthenticatedLayout>
</template>
