<script setup>
import TextInput from '@/Components/TextInput.vue';
import { __ } from '@/i18n';
import { useForm, usePage } from '@inertiajs/vue3';
import {
    ChevronDown,
    FileImage,
    FileText,
    KeyRound,
    RefreshCw,
    ShieldCheck,
    ShieldOff,
    Smartphone,
} from '@lucide/vue';
import { computed, ref } from 'vue';

const page = usePage();
const twoFactor = computed(() => page.props.twoFactor ?? {});
const recoveryCodes = computed(() => twoFactor.value.recovery_codes ?? []);

const enableForm = useForm({});
const confirmForm = useForm({ code: '' });
const disableForm = useForm({ current_password: '' });
const recoveryForm = useForm({ current_password: '' });

const showRecovery = ref(false);

// Senha de reconfirmação para as ações sensíveis (desativar / regenerar) sobre
// um 2FA ativo. Um único campo alimenta ambas as ações.
const managePassword = ref('');
const manageError = computed(
    () =>
        disableForm.errors.current_password ||
        recoveryForm.errors.current_password,
);

const enable = () =>
    enableForm.post(route('two-factor.enable'), { preserveScroll: true });

const confirm = () =>
    confirmForm.post(route('two-factor.confirm'), {
        preserveScroll: true,
        onSuccess: () => confirmForm.reset(),
    });

const disable = () => {
    disableForm.current_password = managePassword.value;
    disableForm.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            managePassword.value = '';
        },
    });
};

const regenerate = () => {
    recoveryForm.current_password = managePassword.value;
    recoveryForm.post(route('two-factor.recovery-codes'), {
        preserveScroll: true,
        onSuccess: () => {
            managePassword.value = '';
        },
    });
};

// Downloads (client-side) dos códigos de recuperação.
const triggerDownload = (href, filename) => {
    const link = document.createElement('a');
    link.href = href;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
};

const downloadTxt = () => {
    const blob = new Blob([recoveryCodes.value.join('\n')], {
        type: 'text/plain;charset=utf-8',
    });
    const url = URL.createObjectURL(blob);
    triggerDownload(url, 'gamificaedu-codigos-recuperacao.txt');
    URL.revokeObjectURL(url);
};

const downloadPng = () => {
    const codes = recoveryCodes.value;
    const padding = 28;
    const lineHeight = 30;
    const titleGap = 46;

    const canvas = document.createElement('canvas');
    canvas.width = 460;
    canvas.height = titleGap + padding * 2 + codes.length * lineHeight;

    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#09090b';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = '#a5b4fc';
    ctx.font = 'bold 18px monospace';
    ctx.fillText('GamificaEdu - Codigos de recuperacao', padding, padding + 14);

    ctx.fillStyle = '#e4e4e7';
    ctx.font = '16px monospace';
    codes.forEach((code, index) => {
        ctx.fillText(code, padding, titleGap + padding + index * lineHeight);
    });

    triggerDownload(
        canvas.toDataURL('image/png'),
        'gamificaedu-codigos-recuperacao.png',
    );
};
</script>

<template>
    <section class="space-y-6">
        <header class="flex items-start gap-3">
            <span
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400"
            >
                <ShieldCheck class="h-6 w-6" />
            </span>
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ __('profile.two_factor.title') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('profile.two_factor.subtitle') }}
                </p>
            </div>
            <span
                v-if="twoFactor.enabled"
                class="ml-auto inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-400"
            >
                <ShieldCheck class="h-3.5 w-3.5" />
                {{ __('profile.two_factor.status_enabled') }}
            </span>
        </header>

        <!-- Estado: desativado -->
        <div v-if="!twoFactor.enabled && !twoFactor.confirming">
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                {{ __('profile.two_factor.disabled_hint') }}
            </p>
            <button
                type="button"
                :disabled="enableForm.processing"
                @click="enable"
                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white transition-all hover:bg-indigo-500 disabled:opacity-50"
            >
                <ShieldCheck class="h-4 w-4" />
                {{
                    enableForm.processing
                        ? __('common.processing')
                        : __('profile.two_factor.enable')
                }}
            </button>
        </div>

        <!-- Estado: configurando (escanear QR + confirmar) -->
        <div v-else-if="twoFactor.confirming" class="space-y-5">
            <div
                class="flex flex-col gap-5 rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-900/40 sm:flex-row"
            >
                <div
                    class="shrink-0 rounded-lg bg-white p-3"
                    v-html="twoFactor.qr_svg"
                ></div>
                <div class="space-y-3">
                    <p
                        class="flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-200"
                    >
                        <Smartphone class="h-4 w-4 text-indigo-400" />
                        {{ __('profile.two_factor.scan_hint') }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('profile.two_factor.manual_key') }}
                    </p>
                    <code
                        class="block break-all rounded-lg bg-gray-100 px-3 py-2 font-mono text-xs text-gray-700 dark:bg-gray-950 dark:text-gray-300"
                        >{{ twoFactor.secret }}</code
                    >
                </div>
            </div>

            <form @submit.prevent="confirm" class="max-w-xs space-y-2">
                <label
                    class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                    >{{ __('profile.two_factor.confirm_label') }}</label
                >
                <TextInput
                    v-model="confirmForm.code"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    placeholder="123456"
                />
                <span
                    v-if="confirmForm.errors.code"
                    class="block text-xs text-rose-500"
                    >{{ confirmForm.errors.code }}</span
                >
                <div class="flex gap-2 pt-1">
                    <button
                        type="submit"
                        :disabled="confirmForm.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white transition-all hover:bg-indigo-500 disabled:opacity-50"
                    >
                        <ShieldCheck class="h-4 w-4" />
                        {{
                            confirmForm.processing
                                ? __('common.processing')
                                : __('profile.two_factor.confirm')
                        }}
                    </button>
                    <button
                        type="button"
                        @click="disable"
                        class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-600 transition-colors hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        {{ __('common.cancel') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Estado: ativado (ações) -->
        <div v-else class="space-y-3">
            <div class="max-w-xs space-y-1">
                <label
                    class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                    >{{ __('profile.two_factor.current_password_label') }}</label
                >
                <TextInput
                    v-model="managePassword"
                    type="password"
                    autocomplete="current-password"
                    :placeholder="
                        __('profile.two_factor.current_password_placeholder')
                    "
                />
                <span v-if="manageError" class="block text-xs text-rose-500">{{
                    manageError
                }}</span>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    :disabled="recoveryForm.processing"
                    @click="regenerate"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-600 transition-colors hover:bg-gray-100 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
            >
                <RefreshCw class="h-4 w-4" />
                {{
                    recoveryForm.processing
                        ? __('common.processing')
                        : __('profile.two_factor.regenerate')
                }}
            </button>
            <button
                type="button"
                :disabled="disableForm.processing"
                @click="disable"
                class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-bold text-white transition-all hover:bg-rose-500 disabled:opacity-50"
            >
                <ShieldOff class="h-4 w-4" />
                {{
                    disableForm.processing
                        ? __('common.processing')
                        : __('profile.two_factor.disable')
                }}
                </button>
            </div>
        </div>

        <!-- Códigos de recuperação: collapse + downloads (quando há códigos) -->
        <div
            v-if="recoveryCodes.length"
            class="rounded-xl border border-gray-200 dark:border-gray-700"
        >
            <button
                type="button"
                @click="showRecovery = !showRecovery"
                class="flex w-full items-center justify-between gap-2 px-4 py-3 text-sm font-bold text-gray-800 dark:text-gray-200"
                :aria-expanded="showRecovery"
            >
                <span class="flex items-center gap-2">
                    <KeyRound class="h-4 w-4 text-indigo-400" />
                    {{ __('profile.two_factor.recovery_title') }}
                </span>
                <ChevronDown
                    class="h-4 w-4 transition-transform duration-200"
                    :class="showRecovery ? 'rotate-180' : ''"
                />
            </button>

            <div
                v-show="showRecovery"
                class="space-y-4 border-t border-gray-200 px-4 py-4 dark:border-gray-700"
            >
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('profile.two_factor.recovery_hint') }}
                </p>
                <div
                    class="grid grid-cols-1 gap-1.5 rounded-lg bg-gray-100 p-3 font-mono text-sm text-gray-800 dark:bg-gray-950 dark:text-gray-200 sm:grid-cols-2"
                >
                    <span v-for="code in recoveryCodes" :key="code">{{
                        code
                    }}</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        @click="downloadTxt"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2 text-xs font-bold text-gray-600 transition-colors hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        <FileText class="h-4 w-4" />
                        {{ __('profile.two_factor.download_txt') }}
                    </button>
                    <button
                        type="button"
                        @click="downloadPng"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2 text-xs font-bold text-gray-600 transition-colors hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        <FileImage class="h-4 w-4" />
                        {{ __('profile.two_factor.download_png') }}
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>
