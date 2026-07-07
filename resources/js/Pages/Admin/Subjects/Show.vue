<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    subject: {
        type: Object,
        required: true,
    },
    availableTeachers: {
        type: Array,
        default: () => [],
    },
});

const isModalOpen = ref(false);

const form = useForm({
    teacher_ids: props.subject.teachers
        ? props.subject.teachers.map((t) => t.id)
        : [],
});

const saveTeachers = () => {
    form.post(route('admin.subjects.teachers', props.subject.id), {
        onSuccess: () => {
            isModalOpen.value = false;
        },
    });
};
</script>

<template>
    <Head :title="__('admin.subject_show.title', { name: subject.name })" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                :title="subject.name"
                :subtitle="subject.institution?.name"
            >
                <template #leading>
                    <Link
                        :href="route('admin.subjects.index')"
                        class="text-zinc-400 transition-colors hover:text-white"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2.5"
                            stroke="currentColor"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"
                            />
                        </svg>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="bg-zinc-955 min-h-[calc(100vh-80px)] py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Mensagem de Sucesso (Toast/Flash) -->
                <div
                    v-if="$page.props.flash?.success"
                    class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-400"
                >
                    {{ $page.props.flash.success }}
                </div>

                <!-- Painel Informativo da Matéria -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6"
                >
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-zinc-400"
                    >
                        {{ __('admin.subject_show.subject_description') }}
                    </h3>
                    <p class="mt-2 text-sm text-zinc-300">
                        {{
                            subject.description ||
                            __('admin.subject_show.no_description')
                        }}
                    </p>
                </div>

                <!-- Bloco de Professores Responsáveis -->
                <div
                    class="space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-4"
                    >
                        <div>
                            <h3 class="text-lg font-bold text-white">
                                {{
                                    __(
                                        'admin.subject_show.responsible_teachers',
                                    )
                                }}
                            </h3>
                            <p class="text-sm text-zinc-400">
                                {{
                                    __(
                                        'admin.subject_show.responsible_teachers_desc',
                                    )
                                }}
                            </p>
                        </div>
                        <button
                            @click="isModalOpen = true"
                            class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white transition-all hover:bg-indigo-500"
                        >
                            {{ __('admin.subject_show.manage_teachers') }}
                        </button>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <div
                            v-for="tchr in subject.teachers"
                            :key="tchr.id"
                            class="flex items-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-2 text-sm text-zinc-200"
                        >
                            <span
                                class="h-2 w-2 rounded-full bg-indigo-500"
                            ></span>
                            <span>{{ tchr.name }}</span>
                            <span class="text-xs text-zinc-400"
                                >({{ tchr.email }})</span
                            >
                        </div>
                        <div
                            v-if="
                                !subject.teachers ||
                                subject.teachers.length === 0
                            "
                            class="py-2 text-sm text-zinc-400"
                        >
                            {{ __('admin.subject_show.no_teachers') }}
                        </div>
                    </div>
                </div>

                <!-- Listagem do Conteúdo Existente -->
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Coluna de Materiais de Estudo -->
                    <div class="space-y-4">
                        <h3
                            class="flex items-center gap-2 text-lg font-bold text-white"
                        >
                            <span class="text-zinc-400">📚</span>
                            {{ __('admin.subject_show.study_materials') }} ({{
                                subject.study_materials?.length || 0
                            }})
                        </h3>

                        <div class="space-y-3">
                            <div
                                v-for="mat in subject.study_materials"
                                :key="mat.id"
                                class="flex items-center justify-between rounded-xl border border-zinc-800 bg-zinc-900/20 p-5"
                            >
                                <div>
                                    <h4 class="text-sm font-bold text-zinc-100">
                                        {{ mat.title }}
                                    </h4>
                                    <p class="mt-1 text-xs text-zinc-400">
                                        {{
                                            __(
                                                'admin.subject_show.points_per_reading',
                                                { points: mat.points_reward },
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="subject.study_materials?.length === 0"
                                class="rounded-xl border border-dashed border-zinc-800 p-8 text-center text-sm text-zinc-400"
                            >
                                {{ __('admin.subject_show.no_materials') }}
                            </div>
                        </div>
                    </div>

                    <!-- Coluna de Atividades e Quizzes -->
                    <div class="space-y-4">
                        <h3
                            class="flex items-center gap-2 text-lg font-bold text-white"
                        >
                            <span class="text-zinc-400">⚔️</span>
                            {{ __('admin.subject_show.tests_quizzes') }} ({{
                                subject.tests?.length || 0
                            }})
                        </h3>

                        <div class="space-y-4">
                            <div
                                v-for="test in subject.tests"
                                :key="test.id"
                                class="space-y-4 rounded-xl border border-zinc-800 bg-zinc-900/20 p-5"
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h4
                                            class="text-sm font-bold text-zinc-100"
                                        >
                                            {{ test.title }}
                                        </h4>
                                        <p class="mt-1 text-xs text-zinc-400">
                                            {{ test.description }}
                                        </p>
                                    </div>
                                    <span
                                        class="rounded-lg bg-indigo-500/10 px-2.5 py-1 text-xs font-bold text-indigo-400"
                                    >
                                        +{{ test.points_reward }}
                                        {{ __('admin.subject_show.xp_max') }}
                                    </span>
                                </div>

                                <!-- Mini-Listagem de Questões do Teste -->
                                <div class="border-t border-zinc-800 pt-3">
                                    <h5 class="text-xs font-bold text-zinc-400">
                                        {{ __('admin.subject_show.questions') }}
                                        ({{ test.questions?.length }})
                                    </h5>
                                    <ol
                                        class="mt-2 list-inside list-decimal space-y-2 text-xs"
                                    >
                                        <li
                                            v-for="q in test.questions"
                                            :key="q.id"
                                            class="text-zinc-300"
                                        >
                                            <span class="font-medium">{{
                                                q.question_text
                                            }}</span>
                                            <div
                                                class="ml-4 mt-1 flex flex-wrap gap-2"
                                            >
                                                <span
                                                    v-for="(
                                                        opt, oIdx
                                                    ) in q.options"
                                                    :key="oIdx"
                                                    class="rounded px-2 py-0.5 text-[10px]"
                                                    :class="
                                                        oIdx ===
                                                        q.correct_option_index
                                                            ? 'border border-emerald-500/20 bg-emerald-500/10 font-semibold text-emerald-400'
                                                            : 'border border-zinc-800 bg-zinc-950 text-zinc-400'
                                                    "
                                                >
                                                    {{ opt }}
                                                </span>
                                            </div>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                            <div
                                v-if="subject.tests?.length === 0"
                                class="rounded-xl border border-dashed border-zinc-800 p-8 text-center text-sm text-zinc-400"
                            >
                                {{ __('admin.subject_show.no_tests') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Gerenciar Professores -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900 p-6 shadow-2xl"
            >
                <h3 class="text-lg font-bold text-white">
                    {{ __('admin.subject_show.associate_teachers') }}
                </h3>
                <form @submit.prevent="saveTeachers" class="space-y-4">
                    <p class="text-xs text-zinc-400">
                        {{ __('admin.subject_show.select_teachers') }}
                    </p>
                    <div class="max-h-60 space-y-2 overflow-y-auto pr-2">
                        <label
                            v-for="tchr in availableTeachers"
                            :key="tchr.id"
                            class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-zinc-200 transition-all hover:border-zinc-700"
                        >
                            <input
                                type="checkbox"
                                :value="tchr.id"
                                v-model="form.teacher_ids"
                                class="rounded border-zinc-800 bg-zinc-900 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-zinc-900"
                            />
                            <div>
                                <div class="font-medium text-white">
                                    {{ tchr.name }}
                                </div>
                                <div class="text-xs text-zinc-400">
                                    {{ tchr.email }}
                                </div>
                            </div>
                        </label>
                        <div
                            v-if="availableTeachers.length === 0"
                            class="py-4 text-center text-sm text-zinc-400"
                        >
                            {{
                                __('admin.subject_show.no_teachers_registered')
                            }}
                        </div>
                    </div>
                    <span
                        v-if="form.errors.teacher_ids"
                        class="mt-1 block text-xs text-red-500"
                        >{{ form.errors.teacher_ids }}</span
                    >
                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="isModalOpen = false"
                            class="rounded-xl border border-zinc-700 bg-transparent px-4 py-2.5 text-sm font-semibold text-zinc-300 transition-all hover:bg-zinc-800"
                        >
                            {{ __('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white transition-all hover:bg-indigo-500 disabled:opacity-55"
                        >
                            {{ __('common.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
