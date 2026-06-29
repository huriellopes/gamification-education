<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Lock, Mail, User, UserPlus } from '@lucide/vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="__('auth.register_title')" />

        <Link
            href="/"
            class="mb-6 inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-400 transition-colors hover:text-white"
        >
            <ArrowLeft class="h-4 w-4" />
            {{ __('common.back_home') }}
        </Link>

        <div class="mb-6 text-center">
            <h1
                class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl"
            >
                {{ __('auth.register_title') }}
            </h1>
            <p class="mt-2 text-sm text-zinc-400">
                {{ __('auth.register_subtitle') }}
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Nome -->
            <div>
                <InputLabel
                    for="name"
                    :value="__('auth.full_name')"
                    class="text-[10px] font-bold uppercase tracking-wider text-zinc-400"
                />
                <div class="relative mt-1">
                    <span
                        class="absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500"
                    >
                        <User class="h-5 w-5" />
                    </span>
                    <TextInput
                        id="name"
                        type="text"
                        class="w-full pl-10"
                        v-model="form.name"
                        required
                        autofocus
                        :placeholder="__('auth.full_name_placeholder')"
                        autocomplete="name"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- E-mail -->
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
                        :placeholder="__('auth.email_placeholder')"
                        autocomplete="username"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Senha -->
            <div>
                <InputLabel
                    for="password"
                    :value="__('auth.password')"
                    class="text-[10px] font-bold uppercase tracking-wider text-zinc-400"
                />
                <div class="relative mt-1">
                    <span
                        class="absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500"
                    >
                        <Lock class="h-5 w-5" />
                    </span>
                    <TextInput
                        id="password"
                        type="password"
                        class="w-full pl-10"
                        v-model="form.password"
                        required
                        placeholder="••••••••"
                        autocomplete="new-password"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Confirmação de Senha -->
            <div>
                <InputLabel
                    for="password_confirmation"
                    :value="__('auth.confirm_password')"
                    class="text-[10px] font-bold uppercase tracking-wider text-zinc-400"
                />
                <div class="relative mt-1">
                    <span
                        class="absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500"
                    >
                        <Lock class="h-5 w-5" />
                    </span>
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="w-full pl-10"
                        v-model="form.password_confirmation"
                        required
                        placeholder="••••••••"
                        autocomplete="new-password"
                    />
                </div>
                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="to-violet-650 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 py-3 text-sm font-bold text-white transition-all hover:brightness-110 disabled:opacity-50"
            >
                <UserPlus class="h-4 w-4" />
                {{ __('auth.create_account') }}
            </button>
        </form>

        <!-- Referência para login -->
        <p class="mt-8 text-center text-xs text-zinc-400">
            {{ __('auth.have_account') }}
            <Link
                :href="route('login')"
                class="font-bold text-indigo-400 transition-colors hover:text-indigo-300"
            >
                {{ __('auth.sign_in') }}
            </Link>
        </p>
    </GuestLayout>
</template>
