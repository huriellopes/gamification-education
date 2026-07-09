<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { __ } from '@/i18n';
import { useForm } from '@inertiajs/vue3';
import { TriangleAlert } from '@lucide/vue';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header class="flex items-start gap-3">
            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-500/10 text-rose-400"
            >
                <TriangleAlert class="h-5 w-5" />
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">
                    {{ __('profile.delete_title') }}
                </h2>
                <p class="mt-1 text-sm text-zinc-400">
                    {{ __('profile.delete_subtitle') }}
                </p>
            </div>
        </header>

        <DangerButton @click="confirmUserDeletion">
            {{ __('profile.delete_button') }}
        </DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-bold text-white">
                    {{ __('profile.delete_confirm_title') }}
                </h2>

                <p class="mt-1 text-sm text-zinc-400">
                    {{ __('profile.delete_confirm_subtitle') }}
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="password"
                        :value="__('profile.password')"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        :placeholder="__('profile.password')"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">
                        {{ __('common.cancel') }}
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        {{ __('profile.delete_button') }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
