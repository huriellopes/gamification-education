<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import PageHeader from '@/Components/PageHeader.vue';
import TextInput from '@/Components/TextInput.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { BookOpen, Search, UserPlus, Users as UsersIcon } from '@lucide/vue';
import { computed, ref } from 'vue';

const props = defineProps({
    classrooms: { type: Array, default: () => [] },
    students: { type: Array, default: () => [] },
});

const isEnrollOpen = ref(false);
const selectedClassroom = ref(null);
const search = ref('');

const form = useForm({
    student_ids: [],
});

const openEnrollModal = (classroom) => {
    selectedClassroom.value = classroom;
    search.value = '';
    form.reset();
    form.clearErrors();
    isEnrollOpen.value = true;
};

// Alunos já matriculados na turma selecionada.
const enrolledIds = computed(() => selectedClassroom.value?.student_ids ?? []);

// Filtro por nome ou e-mail.
const filteredStudents = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) return props.students;
    return props.students.filter(
        (s) =>
            s.name.toLowerCase().includes(term) ||
            s.email.toLowerCase().includes(term),
    );
});

const submitEnroll = () => {
    form.post(
        route('teacher.classrooms.students.store', selectedClassroom.value.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                isEnrollOpen.value = false;
                form.reset();
            },
        },
    );
};
</script>

<template>
    <Head :title="__('classrooms.teacher_header')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="__('classrooms.teacher_header')" />
        </template>

        <div class="min-h-[calc(100vh-80px)] bg-zinc-950 py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div
                    v-if="classrooms.length"
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="classroom in classrooms"
                        :key="classroom.id"
                        class="flex flex-col overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/40 p-6"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white"
                            >
                                <UsersIcon class="h-5 w-5" />
                            </div>
                            <span
                                class="inline-flex rounded-full bg-indigo-500/10 px-2.5 py-0.5 text-xs font-bold text-indigo-400"
                            >
                                {{
                                    __('classrooms.subjects_count', {
                                        count: classroom.subjects_count ?? 0,
                                    })
                                }}
                            </span>
                        </div>

                        <h3 class="mt-4 text-lg font-bold text-white">
                            {{ classroom.name }}
                        </h3>
                        <p
                            v-if="classroom.description"
                            class="mt-1 text-sm text-zinc-400"
                        >
                            {{ classroom.description }}
                        </p>

                        <div class="mt-4 space-y-2">
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-zinc-400"
                            >
                                {{ __('classrooms.col_subjects') }}
                            </p>
                            <div
                                v-if="classroom.subjects?.length"
                                class="flex flex-col gap-1.5"
                            >
                                <Link
                                    v-for="subject in classroom.subjects"
                                    :key="subject.id"
                                    :href="
                                        route(
                                            'teacher.subjects.show',
                                            subject.id,
                                        )
                                    "
                                    class="flex items-center gap-2 rounded-lg border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm text-zinc-300 transition-colors hover:border-indigo-500/30 hover:text-white"
                                >
                                    <BookOpen
                                        class="h-4 w-4 shrink-0 text-indigo-400"
                                    />
                                    <span class="truncate">{{
                                        subject.name
                                    }}</span>
                                </Link>
                            </div>
                            <p v-else class="text-xs italic text-zinc-400">
                                {{ __('classrooms.no_subjects') }}
                            </p>
                        </div>

                        <!-- Alunos: contagem + adicionar -->
                        <div
                            class="mt-5 flex items-center justify-between border-t border-zinc-800 pt-4"
                        >
                            <span
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-400"
                            >
                                <UsersIcon class="h-4 w-4" />
                                {{
                                    __('classrooms.students_count', {
                                        count: classroom.students_count ?? 0,
                                    })
                                }}
                            </span>
                            <button
                                type="button"
                                @click="openEnrollModal(classroom)"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-bold text-white transition-all hover:bg-indigo-500"
                            >
                                <UserPlus class="h-4 w-4" />
                                {{ __('classrooms.add_students') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 py-16 text-center text-sm text-zinc-400"
                >
                    {{ __('classrooms.teacher_empty') }}
                </div>
            </div>
        </div>

        <!-- Modal: filtrar alunos e matricular na turma -->
        <BaseModal
            :show="isEnrollOpen"
            :title="
                __('classrooms.enroll_title', {
                    name: selectedClassroom?.name ?? '',
                })
            "
            maxWidth="lg"
            @close="isEnrollOpen = false"
        >
            <form @submit.prevent="submitEnroll" class="space-y-4">
                <div class="relative">
                    <span
                        class="absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400"
                    >
                        <Search class="h-4 w-4" />
                    </span>
                    <TextInput
                        v-model="search"
                        type="text"
                        class="w-full pl-9"
                        :placeholder="__('classrooms.enroll_search')"
                    />
                </div>

                <div
                    class="max-h-72 space-y-1.5 overflow-y-auto rounded-xl border border-zinc-800 bg-zinc-950/40 p-2"
                >
                    <p
                        v-if="filteredStudents.length === 0"
                        class="px-2 py-6 text-center text-xs text-zinc-500"
                    >
                        {{ __('classrooms.enroll_no_students') }}
                    </p>

                    <label
                        v-for="student in filteredStudents"
                        :key="student.id"
                        class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-2 hover:bg-zinc-800/60"
                        :class="{
                            'opacity-60': enrolledIds.includes(student.id),
                        }"
                    >
                        <input
                            type="checkbox"
                            :value="student.id"
                            :checked="enrolledIds.includes(student.id)"
                            :disabled="enrolledIds.includes(student.id)"
                            v-model="form.student_ids"
                            class="h-4 w-4 rounded border-zinc-700 bg-zinc-900 text-indigo-600 focus:ring-indigo-500"
                        />
                        <span class="flex min-w-0 flex-col">
                            <span
                                class="truncate text-sm font-semibold text-white"
                                >{{ student.name }}</span
                            >
                            <span class="truncate text-xs text-zinc-400">{{
                                student.email
                            }}</span>
                        </span>
                        <span
                            v-if="enrolledIds.includes(student.id)"
                            class="ml-auto shrink-0 text-[10px] font-bold uppercase text-emerald-400"
                        >
                            {{ __('classrooms.enroll_already') }}
                        </span>
                    </label>
                </div>

                <span
                    v-if="form.errors.student_ids"
                    class="block text-xs text-rose-400"
                    >{{ form.errors.student_ids }}</span
                >

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="isEnrollOpen = false"
                        class="rounded-xl bg-zinc-800 px-5 py-2.5 text-xs font-bold text-zinc-300 transition-colors hover:bg-zinc-700"
                    >
                        {{ __('common.cancel') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="
                            form.processing || form.student_ids.length === 0
                        "
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white transition-all hover:bg-indigo-500 disabled:opacity-50"
                    >
                        <UserPlus class="h-4 w-4" />
                        {{
                            form.processing
                                ? __('common.registering')
                                : __('classrooms.enroll_submit')
                        }}
                    </button>
                </div>
            </form>
        </BaseModal>
    </AuthenticatedLayout>
</template>
