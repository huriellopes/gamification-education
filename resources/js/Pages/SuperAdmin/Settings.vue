<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { BarChart3 } from '@lucide/vue';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({ public_fake_metrics: true }),
    },
});

const form = useForm({
    public_fake_metrics: props.settings.public_fake_metrics,
});

const toggleFakeMetrics = () => {
    form.public_fake_metrics = !form.public_fake_metrics;
    form.put(route('super-admin.settings.update'), { preserveScroll: true });
};
</script>

<template>
    <Head :title="__('superadmin.settings.head_title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="__('superadmin.settings.header')" />
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100">
            <div class="mx-auto max-w-3xl space-y-6">
                <!-- Métricas do site público -->
                <section
                    class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
                >
                    <div class="flex items-start gap-3">
                        <span
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400"
                        >
                            <BarChart3 class="h-6 w-6" />
                        </span>
                        <div>
                            <h3 class="text-lg font-bold text-white">
                                {{ __('superadmin.settings.public_metrics_title') }}
                            </h3>
                            <p class="mt-1 text-sm text-zinc-400">
                                {{ __('superadmin.settings.public_metrics_subtitle') }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-6 flex items-center justify-between gap-4 rounded-xl border border-zinc-800 bg-zinc-950/40 p-4"
                    >
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-zinc-100">
                                {{ __('superadmin.settings.fake_metrics_label') }}
                            </p>
                            <p class="mt-1 text-xs text-zinc-400">
                                {{ __('superadmin.settings.fake_metrics_hint') }}
                            </p>
                        </div>

                        <button
                            type="button"
                            role="switch"
                            :aria-checked="form.public_fake_metrics"
                            :disabled="form.processing"
                            @click="toggleFakeMetrics"
                            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-zinc-950 disabled:opacity-50"
                            :class="
                                form.public_fake_metrics
                                    ? 'bg-indigo-600'
                                    : 'bg-zinc-700'
                            "
                        >
                            <span
                                class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform"
                                :class="
                                    form.public_fake_metrics
                                        ? 'translate-x-5'
                                        : 'translate-x-0.5'
                                "
                            />
                        </button>
                    </div>

                    <p class="mt-3 text-xs text-zinc-500">
                        {{
                            form.public_fake_metrics
                                ? __('superadmin.settings.state_fake')
                                : __('superadmin.settings.state_real')
                        }}
                    </p>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
