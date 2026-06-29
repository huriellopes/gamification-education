<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Mail, Lock, KeyRound, Sparkles } from '@lucide/vue';

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
        <Head title="Identificação" />

        <div class="mb-6 text-center">
            <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                Acesse sua Conta
            </h1>
            <p class="mt-2 text-sm text-zinc-400">
                Escolha a forma mais conveniente para entrar na plataforma.
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
                Entrar com Senha
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
                Login Mágico
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
        <form v-if="activeTab === 'password'" @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="E-mail" class="text-zinc-400 font-bold uppercase tracking-wider text-[10px]" />
                <div class="relative mt-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500">
                        <Mail class="h-5 w-5" />
                    </span>
                    <TextInput
                        id="email"
                        type="email"
                        class="pl-10 w-full"
                        v-model="form.email"
                        required
                        autofocus
                        placeholder="nome@instituicao.com"
                        autocomplete="username"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex justify-between items-center">
                    <InputLabel for="password" value="Senha" class="text-zinc-400 font-bold uppercase tracking-wider text-[10px]" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs text-zinc-500 hover:text-indigo-400 transition-colors"
                    >
                        Esqueceu sua senha?
                    </Link>
                </div>
                <div class="relative mt-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500">
                        <Lock class="h-5 w-5" />
                    </span>
                    <TextInput
                        id="password"
                        type="password"
                        class="pl-10 w-full"
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
                <span class="ms-2 text-xs text-zinc-400 select-none">Lembrar-me neste dispositivo</span>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-violet-650 py-3 text-sm font-bold text-white transition-all hover:brightness-110 disabled:opacity-50"
            >
                Entrar
            </button>
        </form>

        <!-- Formulário de Login Mágico -->
        <form v-else @submit.prevent="sendMagicLink" class="space-y-4">
            <div class="rounded-xl border border-zinc-800 bg-zinc-950/40 p-4 text-xs text-zinc-400 leading-relaxed">
                Insira o seu e-mail cadastrado na plataforma para receber um link de acesso instantâneo. O link é de uso único e expira em 15 minutos.
            </div>

            <div>
                <InputLabel for="magic_email" value="E-mail Cadastrado" class="text-zinc-400 font-bold uppercase tracking-wider text-[10px]" />
                <div class="relative mt-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500">
                        <Mail class="h-5 w-5" />
                    </span>
                    <TextInput
                        id="magic_email"
                        type="email"
                        class="pl-10 w-full"
                        v-model="magicForm.email"
                        required
                        autofocus
                        placeholder="nome@instituicao.com"
                    />
                </div>
                <InputError class="mt-2" :message="magicForm.errors.email" />
            </div>

            <div class="flex items-center">
                <Checkbox name="magic_remember" v-model:checked="magicForm.remember" />
                <span class="ms-2 text-xs text-zinc-400 select-none">Lembrar-me neste dispositivo</span>
            </div>

            <button
                type="submit"
                :disabled="magicForm.processing"
                class="w-full flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-650 py-3 text-sm font-bold text-white transition-all hover:brightness-110 disabled:opacity-50"
            >
                <Sparkles class="h-4 w-4 text-amber-400" />
                Enviar Link de Acesso
            </button>
        </form>
    </GuestLayout>
</template>
