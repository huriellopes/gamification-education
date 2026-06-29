<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, KeyRound, Mail } from '@lucide/vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head :title="__('auth.forgot_title')" />

        <Link
            :href="route('login')"
            class="mb-6 inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-400 transition-colors hover:text-white"
        >
            <ArrowLeft class="h-4 w-4" />
            {{ __('auth.back_to_login') }}
        </Link>

        <div class="mb-6 text-center">
            <div
                class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-lg"
            >
                <KeyRound class="h-6 w-6" />
            </div>
            <h1
                class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl"
            >
                {{ __('auth.forgot_title') }}
            </h1>
            <p class="mt-2 text-sm text-zinc-400">
                {{ __('auth.forgot_subtitle') }}
            </p>
        </div>

        <div
            v-if="status"
            class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-xs font-medium text-emerald-400"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel
                    for="email"
                    :value="__('auth.email')"
                    class="text-[10px] font-bold uppercase tracking-wider text-zinc-400"
                />
                <div class="relative mt-1">
                    <span
                        class="absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500"
                    >
                        <Mail class="h-5 w-5" />
                    </span>
                    <TextInput
                        id="email"
                        type="email"
                        class="w-full pl-10"
                        v-model="form.email"
                        required
                        autofocus
                        :placeholder="__('auth.email_placeholder')"
                        autocomplete="username"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="to-violet-650 w-full rounded-xl bg-gradient-to-r from-indigo-600 py-3 text-sm font-bold text-white transition-all hover:brightness-110 disabled:opacity-50"
            >
                {{ __('auth.send_reset_link') }}
            </button>
        </form>
    </GuestLayout>
</template>
