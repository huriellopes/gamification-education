<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import Button from '@/Components/Button.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import DataTable from '@/Components/DataTable.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue';
import Tooltip from '@/Components/Tooltip.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { slugify } from '@/Utils/mask';
import { __ } from '@/i18n';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CheckCircle, Pencil, Plus, Power, Trash2, XCircle } from '@lucide/vue';
import { ref, watch } from 'vue';

defineProps({
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
    {
        key: 'name',
        label: __('superadmin.subjects.col_name_description'),
        sortable: true,
    },
    {
        key: 'duration',
        label: __('superadmin.subjects.col_duration'),
        sortable: true,
    },
    {
        key: 'institution',
        label: __('superadmin.subjects.col_institution'),
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

watch(
    () => subjectForm.name,
    (newName) => {
        if (!isEditingSubject.value) {
            subjectForm.slug = slugify(newName);
        }
    },
);

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
        subjectForm.put(
            route('super-admin.subjects.update', selectedSubjectId.value),
            {
                onSuccess: () => {
                    isSubjectModalOpen.value = false;
                    subjectForm.reset();
                },
            },
        );
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
    const activeVal =
        sub.is_active === 1 ||
        sub.is_active?.value === 1 ||
        sub.is_active === 'active';
    const actionText = activeVal
        ? __('superadmin.subjects.action_deactivate')
        : __('superadmin.subjects.action_activate');
    triggerConfirm(
        __('superadmin.subjects.confirm_status_title'),
        __('superadmin.subjects.confirm_status_message', {
            action: actionText,
            name: sub.name,
        }),
        'warning',
        () => {
            router.post(route('super-admin.subjects.toggle', sub.id));
        },
    );
};

const confirmDeleteSubject = (sub) => {
    triggerConfirm(
        __('superadmin.subjects.confirm_delete_title'),
        __('superadmin.subjects.confirm_delete_message', { name: sub.name }),
        'danger',
        () => {
            router.delete(route('super-admin.subjects.destroy', sub.id));
        },
    );
};
</script>

<template>
    <Head :title="__('superadmin.subjects.head_title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="__('superadmin.subjects.header')">
                <template #actions>
                    <Button @click="openCreateSubject">
                        <template #icon><Plus class="h-4 w-4" /></template>
                        <span class="hidden md:inline">{{
                            __('superadmin.subjects.register')
                        }}</span>
                    </Button>
                </template>
            </PageHeader>
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100">
            <div
                class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
            >
                <DataTable
                    :items="subjects"
                    :columns="subjectColumns"
                    :searchPlaceholder="
                        __('superadmin.subjects.search_placeholder')
                    "
                >
                    <template #id="{ item }">
                        <span class="font-mono text-zinc-400"
                            >#{{ item.id }}</span
                        >
                    </template>
                    <template #name="{ item }">
                        <div class="text-zinc-150 font-semibold">
                            {{ item.name }}
                        </div>
                        <div class="max-w-xs truncate text-xs text-zinc-400">
                            {{
                                item.description ||
                                __('superadmin.subjects.no_description')
                            }}
                        </div>
                    </template>
                    <template #duration="{ item }">
                        <span class="font-medium text-zinc-400">{{
                            item.duration ||
                            __('superadmin.subjects.duration_na')
                        }}</span>
                    </template>
                    <template #institution="{ item }">
                        <span class="text-xs font-semibold text-indigo-400">{{
                            item.institution?.name ||
                            __('superadmin.subjects.common_subject')
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
                            {{ __('superadmin.subjects.active') }}
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1 rounded-full bg-rose-500/10 px-2.5 py-1 text-xs font-bold text-rose-400"
                        >
                            <XCircle class="h-3 w-3" />
                            {{ __('superadmin.subjects.inactive') }}
                        </span>
                    </template>
                    <template #actions="{ item }">
                        <div class="flex items-center justify-end gap-2">
                            <Tooltip
                                :text="
                                    item.is_active === 1 ||
                                    item.is_active?.value === 1
                                        ? __(
                                              'superadmin.subjects.tooltip_deactivate',
                                          )
                                        : __(
                                              'superadmin.subjects.tooltip_activate',
                                          )
                                "
                            >
                                <button
                                    @click="confirmToggleSubject(item)"
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
                                :text="__('superadmin.subjects.tooltip_edit')"
                            >
                                <Button
                                    variant="icon"
                                    @click="openEditSubject(item)"
                                    class="text-indigo-400"
                                >
                                    <template #icon
                                        ><Pencil class="h-4 w-4"
                                    /></template>
                                </Button>
                            </Tooltip>

                            <Tooltip
                                :text="__('superadmin.subjects.tooltip_delete')"
                            >
                                <button
                                    @click="confirmDeleteSubject(item)"
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

        <!-- Base Modal: Cadastro / Edição de Matéria -->
        <BaseModal
            :show="isSubjectModalOpen"
            :title="
                isEditingSubject
                    ? __('superadmin.subjects.modal_edit_title')
                    : __('superadmin.subjects.modal_new_title')
            "
            maxWidth="2xl"
            @close="isSubjectModalOpen = false"
        >
            <form @submit.prevent="submitSubject" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >{{ __('superadmin.subjects.label_name') }}</label
                        >
                        <TextInput
                            v-model="subjectForm.name"
                            type="text"
                            required
                        />
                    </div>
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >{{ __('superadmin.subjects.label_slug') }}</label
                        >
                        <TextInput
                            v-model="subjectForm.slug"
                            type="text"
                            required
                        />
                    </div>
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{
                            __('superadmin.subjects.label_description')
                        }}</label
                    >
                    <TextareaInput v-model="subjectForm.description" rows="3" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >{{
                                __('superadmin.subjects.label_institution')
                            }}</label
                        >
                        <SelectInput
                            v-model="subjectForm.institution_id"
                            required
                        >
                            <option value="" disabled>
                                {{
                                    __('superadmin.subjects.select_institution')
                                }}
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
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >{{
                                __('superadmin.subjects.label_duration')
                            }}</label
                        >
                        <TextInput
                            v-model="subjectForm.duration"
                            type="text"
                            required
                            :placeholder="
                                __('superadmin.subjects.duration_placeholder')
                            "
                        />
                    </div>
                </div>

                <div
                    class="border-zinc-850 flex justify-end gap-3 border-t pt-4"
                >
                    <Button
                        variant="secondary"
                        type="button"
                        @click="isSubjectModalOpen = false"
                    >
                        {{ __('common.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="subjectForm.processing">
                        {{
                            subjectForm.processing
                                ? __('superadmin.subjects.saving')
                                : __('superadmin.subjects.save_subject')
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
