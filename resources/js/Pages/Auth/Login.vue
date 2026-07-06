<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Lock, Mail, Sparkles, UserPlus } from '@lucide/vue';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const activeTab = ref('password'); // 'password' ou 'magic'

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const magicForm = useForm({
    email: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const sendMagicLink = () => {
    magicForm.post(route('magic-login.send'), {
        onSuccess: () => {
            magicForm.reset();
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="__('auth.login_title')" />

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
                {{ __('auth.login_title') }}
            </h1>
            <p class="mt-2 text-sm text-zinc-400">
                {{ __('auth.login_subtitle') }}
            </p>
        </div>

        <!-- Tabs de login -->
        <div class="mb-6 flex rounded-xl bg-zinc-950 p-1">
            <button
                type="button"
                @click="activeTab = 'password'"
                class="flex w-full items-center justify-center gap-2 rounded-lg py-2.5 text-xs font-bold transition-all"
                :class="
                    activeTab === 'password'
                        ? 'bg-zinc-800 text-white shadow-sm'
                        : 'text-zinc-400 hover:text-white'
                "
            >
                <Lock class="h-4 w-4" />
                {{ __('auth.tab_password') }}
            </button>
            <button
                type="button"
                @click="activeTab = 'magic'"
                class="flex w-full items-center justify-center gap-2 rounded-lg py-2.5 text-xs font-bold transition-all"
                :class="
                    activeTab === 'magic'
                        ? 'bg-zinc-800 text-white shadow-sm'
                        : 'text-zinc-400 hover:text-white'
                "
            >
                <Sparkles class="h-4 w-4 text-amber-400" />
                {{ __('auth.tab_magic') }}
            </button>
        </div>

        <!-- Mensagem de Status (ex: link enviado com sucesso) -->
        <div
            v-if="status"
            class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-xs text-emerald-400"
        >
            {{ status }}
        </div>

        <!-- Formulário com Senha -->
        <form
            v-if="activeTab === 'password'"
            @submit.prevent="submit"
            class="space-y-4"
        >
            <div>
                <InputLabel
                    for="email"
                    :value="__('auth.email')"
                    class="text-[10px] font-bold uppercase tracking-wider text-zinc-400"
                />
                <div class="relative mt-1">
                    <span
                        class="absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400"
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

            <div>
                <div class="flex items-center justify-between">
                    <InputLabel
                        for="password"
                        :value="__('auth.password')"
                        class="text-[10px] font-bold uppercase tracking-wider text-zinc-400"
                    />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs text-zinc-400 transition-colors hover:text-indigo-400"
                    >
                        {{ __('auth.forgot_password') }}
                    </Link>
                </div>
                <div class="relative mt-1">
                    <span
                        class="absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400"
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
                        autocomplete="current-password"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center">
                <Checkbox name="remember" v-model:checked="form.remember" />
                <span class="ms-2 select-none text-xs text-zinc-400">{{
                    __('auth.remember_me')
                }}</span>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="to-violet-650 w-full rounded-xl bg-gradient-to-r from-indigo-600 py-3 text-sm font-bold text-white transition-all hover:brightness-110 disabled:opacity-50"
            >
                {{ __('auth.login') }}
            </button>
        </form>

        <!-- Formulário de Login Mágico -->
        <form v-else @submit.prevent="sendMagicLink" class="space-y-4">
            <div
                class="rounded-xl border border-zinc-800 bg-zinc-950/40 p-4 text-xs leading-relaxed text-zinc-400"
            >
                {{ __('auth.magic_hint') }}
            </div>

            <div>
                <InputLabel
                    for="magic_email"
                    :value="__('auth.magic_email')"
                    class="text-[10px] font-bold uppercase tracking-wider text-zinc-400"
                />
                <div class="relative mt-1">
                    <span
                        class="absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400"
                    >
                        <Mail class="h-5 w-5" />
                    </span>
                    <TextInput
                        id="magic_email"
                        type="email"
                        class="w-full pl-10"
                        v-model="magicForm.email"
                        required
                        autofocus
                        :placeholder="__('auth.email_placeholder')"
                    />
                </div>
                <InputError class="mt-2" :message="magicForm.errors.email" />
            </div>

            <div class="flex items-center">
                <Checkbox
                    name="magic_remember"
                    v-model:checked="magicForm.remember"
                />
                <span class="ms-2 select-none text-xs text-zinc-400">{{
                    __('auth.remember_me')
                }}</span>
            </div>

            <button
                type="submit"
                :disabled="magicForm.processing"
                class="to-violet-650 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 py-3 text-sm font-bold text-white transition-all hover:brightness-110 disabled:opacity-50"
            >
                <Sparkles class="h-4 w-4 text-amber-400" />
                {{ __('auth.send_magic_link') }}
            </button>
        </form>

        <!-- Referência para cadastro -->
        <p class="mt-8 text-center text-xs text-zinc-400">
            {{ __('auth.no_account') }}
            <Link
                :href="route('register')"
                class="inline-flex items-center gap-1 font-bold text-indigo-400 transition-colors hover:text-indigo-300"
            >
                <UserPlus class="h-3.5 w-3.5" />
                {{ __('auth.sign_up') }}
            </Link>
        </p>
    </GuestLayout>
</template>
