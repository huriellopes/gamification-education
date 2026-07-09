<script setup>
import { __ } from '@/i18n';
import { Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CheckCircle2,
    ChevronDown,
    ChevronRight,
    ShieldAlert,
    ShieldCheck,
} from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    report: {
        type: Object,
        default: () => ({
            checks: [],
            summary: {
                status: 'ok',
                alerts: 0,
                critical: 0,
                warning: 0,
                ok: 0,
                total: 0,
            },
        }),
    },
    // Namespace i18n do painel — permite reaproveitar o componente por papel
    // (superadmin.health, admin.health, teacher.health).
    i18nBase: {
        type: String,
        default: 'superadmin.health',
    },
});

// Tradução relativa ao namespace do painel.
const t = (key) => __(`${props.i18nBase}.${key}`);

// Classes estáticas por status — evita string interpolada de classe Tailwind
// (que o purge removeria). Cada status mapeia o conjunto completo de utilitários.
const STATUS_CLASSES = {
    ok: {
        dot: 'bg-emerald-500',
        text: 'text-emerald-400',
        ring: 'border-emerald-500/30',
        soft: 'bg-emerald-500/10',
    },
    warning: {
        dot: 'bg-amber-500',
        text: 'text-amber-400',
        ring: 'border-amber-500/30',
        soft: 'bg-amber-500/10',
    },
    critical: {
        dot: 'bg-red-500',
        text: 'text-red-400',
        ring: 'border-red-500/40',
        soft: 'bg-red-500/10',
    },
};

const classesFor = (status) => STATUS_CLASSES[status] ?? STATUS_CLASSES.ok;

const summary = computed(() => props.report?.summary ?? {});
const checks = computed(() => props.report?.checks ?? []);
const isHealthy = computed(() => (summary.value.alerts ?? 0) === 0);

const checkLabel = (key) => t(`checks.${key}.label`);

const checkDescription = (check) => {
    if (check.status === 'ok') {
        return t(`checks.${check.key}.ok`);
    }

    return `${check.value} ${t(`checks.${check.key}.problem`)}`;
};

const statusLabel = (status) => t(`status.${status}`);

// Estado colapsável, persistido entre visitas (mesmo padrão da sidebar).
const STORAGE_KEY = `${props.i18nBase}.open`;
const open = ref(true);

onMounted(() => {
    if (typeof window === 'undefined') {
        return;
    }

    const saved = window.localStorage.getItem(STORAGE_KEY);
    if (saved !== null) {
        open.value = saved === 'true';
    }
});

const toggle = () => {
    open.value = !open.value;
    if (typeof window !== 'undefined') {
        window.localStorage.setItem(STORAGE_KEY, String(open.value));
    }
};
</script>

<template>
    <section
        class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
        aria-labelledby="system-health-title"
    >
        <!-- Cabeçalho clicável (abre/fecha) + resumo geral -->
        <button
            type="button"
            class="flex w-full flex-col gap-4 text-left sm:flex-row sm:items-center sm:justify-between"
            :aria-expanded="open"
            aria-controls="system-health-body"
            @click="toggle"
        >
            <div class="flex items-start gap-3">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border"
                    :class="[
                        classesFor(summary.status).ring,
                        classesFor(summary.status).soft,
                    ]"
                >
                    <ShieldCheck
                        v-if="isHealthy"
                        class="h-6 w-6"
                        :class="classesFor(summary.status).text"
                    />
                    <ShieldAlert
                        v-else
                        class="h-6 w-6"
                        :class="classesFor(summary.status).text"
                    />
                </div>
                <div>
                    <h3
                        id="system-health-title"
                        class="text-lg font-bold text-white"
                    >
                        {{ t('title') }}
                    </h3>
                    <p class="text-xs text-zinc-400">
                        {{ t('subtitle') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 self-start sm:self-auto">
                <!-- Pílula de resumo (sempre visível, mesmo colapsado) -->
                <span
                    class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 text-xs font-bold"
                    :class="[
                        classesFor(summary.status).ring,
                        classesFor(summary.status).soft,
                        classesFor(summary.status).text,
                    ]"
                >
                    <CheckCircle2 v-if="isHealthy" class="h-4 w-4" />
                    <AlertTriangle v-else class="h-4 w-4" />
                    <span v-if="isHealthy">{{
                        t('all_healthy')
                    }}</span>
                    <span v-else>
                        {{ summary.alerts }}
                        {{ t('alerts_label') }}
                        <template v-if="summary.critical > 0">
                            · {{ summary.critical }}
                            {{ t('critical_label') }}
                        </template>
                    </span>
                </span>

                <ChevronDown
                    class="h-5 w-5 shrink-0 text-zinc-400 transition-transform duration-200"
                    :class="open ? 'rotate-180' : ''"
                    aria-hidden="true"
                />
            </div>
        </button>

        <!-- Corpo colapsável (truque grid-rows 1fr/0fr para animar a altura) -->
        <div
            id="system-health-body"
            class="grid transition-[grid-template-rows] duration-300 ease-out"
            :class="open ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
        >
            <div class="overflow-hidden">
                <!-- Estado saudável -->
                <div
                    v-if="isHealthy"
                    class="mt-6 flex items-center gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/5 px-4 py-3"
                >
                    <CheckCircle2 class="h-5 w-5 shrink-0 text-emerald-400" />
                    <p class="text-sm text-zinc-300">
                        {{ t('all_healthy_detail') }}
                    </p>
                </div>

                <!-- Grade de indicadores -->
                <div
                    v-else
                    class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <component
                        :is="check.route ? Link : 'div'"
                        v-for="check in checks"
                        :key="check.key"
                        :href="check.route ? route(check.route) : undefined"
                        class="group flex items-start justify-between gap-3 rounded-xl border bg-zinc-950/40 p-4 transition-all"
                        :class="[
                            classesFor(check.status).ring,
                            check.route
                                ? 'cursor-pointer hover:bg-zinc-900/60'
                                : 'cursor-default',
                        ]"
                    >
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span
                                    class="h-2 w-2 shrink-0 rounded-full"
                                    :class="classesFor(check.status).dot"
                                    aria-hidden="true"
                                />
                                <span
                                    class="truncate text-sm font-bold text-zinc-100"
                                    >{{ checkLabel(check.key) }}</span
                                >
                            </div>
                            <p class="mt-1 text-xs leading-relaxed text-zinc-400">
                                {{ checkDescription(check) }}
                            </p>
                        </div>

                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <span
                                class="rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                :class="[
                                    classesFor(check.status).soft,
                                    classesFor(check.status).text,
                                ]"
                                >{{ statusLabel(check.status) }}</span
                            >
                            <ChevronRight
                                v-if="check.route"
                                class="h-4 w-4 text-zinc-600 transition-colors group-hover:text-zinc-300"
                            />
                        </div>
                    </component>
                </div>
            </div>
        </div>
    </section>
</template>
