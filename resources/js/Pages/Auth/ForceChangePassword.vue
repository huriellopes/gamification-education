<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Lock, KeyRound } from '@lucide/vue';

const form = useForm({
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.force-change.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Alteração de Senha Obrigatória" />

        <div class="mb-6 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-amber-500/10 text-amber-400">
                <KeyRound class="h-6 w-6" />
            </div>
            <h1 class="text-xl font-extrabold tracking-tight text-white sm:text-2xl">
                Nova Senha Obrigatória
            </h1>
            <p class="mt-2 text-sm text-zinc-400">
                Por motivos de segurança, você deve cadastrar uma nova senha no seu primeiro acesso.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="password" value="Nova Senha" class="text-zinc-400 font-bold uppercase tracking-wider text-[10px]" />
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
                        autocomplete="new-password"
                        autofocus
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirmar Nova Senha" class="text-zinc-400 font-bold uppercase tracking-wider text-[10px]" />
                <div class="relative mt-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500">
                        <Lock class="h-5 w-5" />
                    </span>
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="pl-10 w-full"
                        v-model="form.password_confirmation"
                        required
                        placeholder="••••••••"
                        autocomplete="new-password"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 py-3 text-sm font-bold text-white transition-all hover:brightness-110 disabled:opacity-50"
            >
                Atualizar Senha & Acessar
            </button>
        </form>
    </GuestLayout>
</template>
