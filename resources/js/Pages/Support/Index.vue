<script setup>
import Button from '@/Components/Button.vue';
import PageHeader from '@/Components/PageHeader.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { HelpCircle, Mail, MessageSquare, Send } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps({
    support: {
        type: Object,
        default: () => ({ email: '', phone: '' }),
    },
});

const whatsappUrl = computed(
    () =>
        `https://wa.me/${props.support.phone}?text=${encodeURIComponent(
            'Olá! Estou na plataforma GamificaEdu e preciso de suporte técnico.',
        )}`,
);

const mailtoUrl = computed(
    () =>
        `mailto:${props.support.email}?subject=${encodeURIComponent(
            'Suporte Técnico GamificaEdu',
        )}`,
);

const form = useForm({
    subject: '',
    message: '',
});

const submitSupport = () => {
    form.post(route('support.send'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head :title="__('misc.support.head_title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="__('misc.support.header')" />
        </template>

        <div class="mx-auto max-w-4xl space-y-8 bg-zinc-950 py-6 text-zinc-100">
            <div
                class="space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
            >
                <h3
                    class="flex items-center gap-2 text-lg font-bold text-white"
                >
                    <HelpCircle class="h-5 w-5 text-indigo-400" />
                    {{ __('misc.support.help_title') }}
                </h3>
                <p class="text-sm text-zinc-400">
                    {{ __('misc.support.help_text') }}
                </p>

                <!-- WhatsApp & Email Direct Links -->
                <div class="grid grid-cols-1 gap-4 pt-2 sm:grid-cols-2">
                    <!-- WhatsApp -->
                    <a
                        :href="whatsappUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="active:scale-98 flex items-center justify-center gap-3 rounded-2xl bg-emerald-600 px-5 py-4 text-sm font-bold text-white shadow-lg transition-all hover:bg-emerald-500"
                        :title="__('misc.support.whatsapp')"
                    >
                        <MessageSquare class="h-5 w-5" />
                        <span class="hidden md:inline">{{
                            __('misc.support.whatsapp')
                        }}</span>
                    </a>

                    <!-- Email -->
                    <a
                        :href="mailtoUrl"
                        class="active:scale-98 flex items-center justify-center gap-3 rounded-2xl border border-zinc-700/50 bg-zinc-800 px-5 py-4 text-sm font-bold text-white shadow-md transition-all hover:bg-zinc-700"
                        :title="__('misc.support.email')"
                    >
                        <Mail class="text-zinc-450 h-5 w-5" />
                        <span class="hidden md:inline">{{
                            __('misc.support.email')
                        }}</span>
                    </a>
                </div>
            </div>

            <!-- Form para Chamado Direct -->
            <div
                class="space-y-6 rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
            >
                <div>
                    <h3 class="text-lg font-bold text-white">
                        {{ __('misc.support.open_ticket_title') }}
                    </h3>
                    <p class="text-xs text-zinc-400">
                        {{ __('misc.support.open_ticket_text') }}
                    </p>
                </div>

                <form @submit.prevent="submitSupport" class="space-y-4">
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >{{ __('misc.support.subject_label') }}</label
                        >
                        <input
                            v-model="form.subject"
                            type="text"
                            required
                            :placeholder="
                                __('misc.support.subject_placeholder')
                            "
                            class="placeholder-zinc-650 w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-zinc-200 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        <span
                            v-if="form.errors.subject"
                            class="mt-1 block text-xs text-rose-400"
                            >{{ form.errors.subject }}</span
                        >
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >{{ __('misc.support.message_label') }}</label
                        >
                        <textarea
                            v-model="form.message"
                            required
                            rows="5"
                            :placeholder="
                                __('misc.support.message_placeholder')
                            "
                            class="placeholder-zinc-650 w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-zinc-200 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        ></textarea>
                        <span
                            v-if="form.errors.message"
                            class="mt-1 block text-xs text-rose-400"
                            >{{ form.errors.message }}</span
                        >
                    </div>

                    <div class="flex justify-end pt-2">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="flex items-center gap-2 bg-indigo-600 font-bold hover:bg-indigo-500"
                            :title="__('misc.support.send_ticket')"
                        >
                            <Send class="h-4 w-4" />
                            <span class="hidden md:inline">{{
                                form.processing
                                    ? __('misc.support.sending_ticket')
                                    : __('misc.support.send_ticket')
                            }}</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
