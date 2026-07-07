<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue';
import TextInput from '@/Components/TextInput.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { onlyDigits, slugify } from '@/Utils/mask';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Power, Trash2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';

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

const uniqueInstitutions = computed(() => {
    const seen = new Set();
    return props.institutions.filter((inst) => {
        if (!inst || seen.has(inst.id)) return false;
        seen.add(inst.id);
        return true;
    });
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const selectedSubjectId = ref(null);
const wasSlugManuallyEdited = ref(false);

const form = useForm({
    institution_id: '',
    name: '',
    slug: '',
    description: '',
    duration: '',
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

watch(
    () => form.name,
    (newName) => {
        if (!isEditing.value && !wasSlugManuallyEdited.value) {
            form.slug = slugify(newName);
        }
    },
);

// Duração deve ser numérica (em horas): remove qualquer caractere não-dígito.
watch(
    () => form.duration,
    (value) => {
        const digits = onlyDigits(value, 4);
        if (digits !== value) {
            form.duration = digits;
        }
    },
);

const openCreateModal = () => {
    isEditing.value = false;
    selectedSubjectId.value = null;
    wasSlugManuallyEdited.value = false;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (subject) => {
    isEditing.value = true;
    selectedSubjectId.value = subject.id;
    wasSlugManuallyEdited.value = true;
    form.institution_id = subject.institution_id;
    form.name = subject.name;
    form.slug = subject.slug;
    form.description = subject.description;
    form.duration = subject.duration;
    isModalOpen.value = true;
};

const submit = () => {
    triggerConfirm(
        isEditing.value
            ? __('admin.subjects.confirm_save_title')
            : __('admin.subjects.confirm_create_title'),
        __('admin.subjects.confirm_save_message'),
        () => {
            if (isEditing.value) {
                form.put(
                    route('admin.subjects.update', selectedSubjectId.value),
                    {
                        preserveScroll: true,
                        onSuccess: () => {
                            form.reset();
                            isModalOpen.value = false;
                            confirmState.value.show = false;
                        },
                    },
                );
            } else {
                form.post(route('admin.subjects.store'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        form.reset();
                        isModalOpen.value = false;
                        confirmState.value.show = false;
                    },
                });
            }
        },
    );
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

const confirmDeleteSubject = (id) => {
    triggerConfirm(
        __('admin.subjects.confirm_delete_title'),
        __('admin.subjects.confirm_delete_message'),
        () => {
            router.delete(route('admin.subjects.destroy', id), {
                preserveScroll: true,
                onSuccess: () => {
                    confirmState.value.show = false;
                },
            });
        },
    );
};

const toggleStatus = (sub) => {
    const actionText = isActive(sub)
        ? __('admin.subjects.confirm_toggle_deactivate')
        : __('admin.subjects.confirm_toggle_activate');
    triggerConfirm(
        __('admin.subjects.confirm_toggle_title'),
        __('admin.subjects.confirm_toggle_message', {
            action: actionText,
            name: sub.name,
        }),
        () => {
            router.post(
                route('admin.subjects.toggle', sub.id),
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
    <Head :title="__('admin.subjects.title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="__('admin.subjects.header')">
                <template #actions>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-650 px-4 py-2.5 text-xs font-bold text-white transition-all hover:brightness-110"
                    >
                        <Plus class="h-4 w-4" />
                        {{ __('admin.subjects.new_subject') }}
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

                <!-- Lista de Matérias -->
                <div
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="sub in subjects"
                        :key="sub.id"
                        class="group relative flex h-full flex-col justify-between rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 transition-all duration-300 hover:border-indigo-500/30 hover:bg-zinc-900/50 hover:shadow-xl"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <span
                                    class="mb-3 inline-block rounded-md border border-indigo-500/20 bg-indigo-500/10 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-indigo-400"
                                >
                                    {{
                                        sub.institution
                                            ? sub.institution.name
                                            : __('admin.subjects.na')
                                    }}
                                </span>
                                <div class="flex items-center gap-1">
                                    <Tooltip
                                        :text="
                                            __('admin.subjects.edit_subject')
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
                                            isActive(sub)
                                                ? __(
                                                      'admin.subjects.deactivate_subject',
                                                  )
                                                : __(
                                                      'admin.subjects.activate_subject',
                                                  )
                                        "
                                    >
                                        <button
                                            @click="toggleStatus(sub)"
                                            class="rounded-lg p-1.5 transition-colors"
                                            :class="
                                                isActive(sub)
                                                    ? 'text-red-500 hover:bg-red-500/10 hover:text-red-400'
                                                    : 'text-emerald-500 hover:bg-emerald-500/10 hover:text-emerald-400'
                                            "
                                            type="button"
                                        >
                                            <Power class="h-4 w-4" />
                                        </button>
                                    </Tooltip>
                                    <Tooltip
                                        :text="
                                            __('admin.subjects.delete_subject')
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
                            <h4
                                class="text-lg font-bold text-white transition-colors group-hover:text-indigo-400"
                            >
                                {{ sub.name }}
                            </h4>
                            <p class="mt-1 text-xs text-zinc-400">
                                {{ __('admin.subjects.status_label') }}
                                <span
                                    class="font-semibold"
                                    :class="
                                        isActive(sub)
                                            ? 'text-emerald-400'
                                            : 'text-red-400'
                                    "
                                >
                                    {{
                                        isActive(sub)
                                            ? __('admin.subjects.status_active')
                                            : __(
                                                  'admin.subjects.status_inactive',
                                              )
                                    }}
                                </span>
                            </p>
                            <p
                                class="mt-2 line-clamp-3 min-h-[60px] text-sm text-zinc-400"
                            >
                                {{
                                    sub.description ||
                                    __('admin.subjects.no_description')
                                }}
                            </p>
                        </div>

                        <div
                            class="mt-6 flex flex-col gap-3 border-t border-zinc-850 pt-4"
                        >
                            <div
                                class="flex items-center justify-between text-xs text-zinc-400"
                            >
                                <span
                                    >{{ __('admin.subjects.materials') }}
                                    <strong>{{
                                        sub.study_materials_count || 0
                                    }}</strong></span
                                >
                                <span
                                    >{{ __('admin.subjects.tests') }}
                                    <strong>{{
                                        sub.tests_count || 0
                                    }}</strong></span
                                >
                            </div>

                            <Link
                                :href="route('admin.subjects.show', sub.id)"
                                class="block w-full rounded-xl bg-zinc-800 py-2.5 text-center text-xs font-bold text-white transition-all hover:bg-indigo-650"
                            >
                                {{ __('admin.subjects.manage_content') }} &rarr;
                            </Link>
                        </div>
                    </div>

                    <div
                        v-if="subjects.length === 0"
                        class="col-span-full rounded-2xl border border-dashed border-zinc-800 p-12 text-center text-zinc-400"
                    >
                        {{ __('admin.subjects.empty_state') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Subject Modal -->
        <BaseModal
            :show="isModalOpen"
            :title="
                isEditing
                    ? __('admin.subjects.edit_subject')
                    : __('admin.subjects.new_subject')
            "
            maxWidth="xl"
            @close="isModalOpen = false"
        >
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >{{ __('admin.subjects.institution_label') }}</label
                        >
                        <SelectInput v-model="form.institution_id" required>
                            <option value="" disabled>
                                {{
                                    __('admin.subjects.institution_placeholder')
                                }}
                            </option>
                            <option
                                v-for="inst in uniqueInstitutions"
                                :key="inst.id"
                                :value="inst.id"
                            >
                                {{ inst.name }}
                            </option>
                        </SelectInput>
                        <span
                            v-if="form.errors.institution_id"
                            class="mt-1 block text-xs text-red-500"
                            >{{ form.errors.institution_id }}</span
                        >
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >{{ __('admin.subjects.duration_label') }}</label
                        >
                        <TextInput
                            v-model="form.duration"
                            type="text"
                            inputmode="numeric"
                            required
                            :placeholder="
                                __('admin.subjects.duration_placeholder')
                            "
                        />
                        <span
                            v-if="form.errors.duration"
                            class="mt-1 block text-xs text-red-500"
                            >{{ form.errors.duration }}</span
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >{{ __('admin.subjects.name_label') }}</label
                        >
                        <TextInput
                            v-model="form.name"
                            type="text"
                            required
                            :placeholder="__('admin.subjects.name_placeholder')"
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
                            >{{ __('admin.subjects.slug_label') }}</label
                        >
                        <TextInput
                            v-model="form.slug"
                            @input="
                                wasSlugManuallyEdited = true;
                                form.slug = slugify($event.target.value);
                            "
                            type="text"
                            required
                            :placeholder="__('admin.subjects.slug_placeholder')"
                        />
                        <span
                            v-if="form.errors.slug"
                            class="mt-1 block text-xs text-red-500"
                            >{{ form.errors.slug }}</span
                        >
                    </div>
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{ __('common.description') }}</label
                    >
                    <TextareaInput
                        v-model="form.description"
                        rows="3"
                        :placeholder="
                            __('admin.subjects.description_placeholder')
                        "
                    />
                    <span
                        v-if="form.errors.description"
                        class="mt-1 block text-xs text-red-500"
                        >{{ form.errors.description }}</span
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
                                  ? __('admin.subjects.confirm_save_title')
                                  : __('admin.subjects.confirm_create_title')
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
