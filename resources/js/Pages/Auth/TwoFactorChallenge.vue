<script setup>
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { __ } from '@/i18n';
import { Head, useForm } from '@inertiajs/vue3';
import { KeyRound, ShieldCheck } from '@lucide/vue';
import { nextTick, ref } from 'vue';

const useRecovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const toggleRecovery = async () => {
    useRecovery.value = !useRecovery.value;
    form.clearErrors();
    await nextTick();
};

const submit = () => {
    form.transform((data) =>
        useRecovery.value
            ? { recovery_code: data.recovery_code }
            : { code: data.code },
    ).post(route('two-factor.login.store'), {
        onFinish: () => form.reset('code', 'recovery_code'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="__('auth.two_factor.title')" />

        <div class="mb-6 text-center">
            <span
                class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600/10 text-indigo-400"
            >
                <ShieldCheck class="h-7 w-7" />
            </span>
            <h1
                class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl"
            >
                {{ __('auth.two_factor.title') }}
            </h1>
            <p class="mt-2 text-sm text-zinc-400">
                {{
                    useRecovery
                        ? __('auth.two_factor.recovery_subtitle')
                        : __('auth.two_factor.subtitle')
                }}
            </p>
        </div>

        <form @submit.prevent="submit" novalidate class="space-y-4">
            <div v-if="!useRecovery">
                <label
                    class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-400"
                    >{{ __('auth.two_factor.code_label') }}</label
                >
                <TextInput
                    v-model="form.code"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    autofocus
                    placeholder="123456"
                />
            </div>

            <div v-else>
                <label
                    class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-400"
                    >{{ __('auth.two_factor.recovery_label') }}</label
                >
                <TextInput
                    v-model="form.recovery_code"
                    type="text"
                    autocomplete="one-time-code"
                    autofocus
                    placeholder="XXXXXXXXXX-XXXXXXXXXX"
                />
            </div>

            <InputError :message="form.errors.code" />

            <button
                type="submit"
                :disabled="form.processing"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-650 py-3 text-sm font-bold text-white transition-all hover:brightness-110 disabled:opacity-50"
            >
                <ShieldCheck class="h-4 w-4" />
                {{
                    form.processing
                        ? __('common.processing')
                        : __('auth.two_factor.verify')
                }}
            </button>
        </form>

        <button
            type="button"
            @click="toggleRecovery"
            class="mx-auto mt-6 flex items-center gap-1.5 text-xs font-semibold text-zinc-400 transition-colors hover:text-white"
        >
            <KeyRound class="h-3.5 w-3.5" />
            {{
                useRecovery
                    ? __('auth.two_factor.use_code')
                    : __('auth.two_factor.use_recovery')
            }}
        </button>
    </GuestLayout>
</template>
