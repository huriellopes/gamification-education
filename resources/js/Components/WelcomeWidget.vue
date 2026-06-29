<script setup>
import { __ } from '@/i18n';
import { usePage } from '@inertiajs/vue3';
import { Moon, Sun, Sunset } from '@lucide/vue';
import { computed } from 'vue';

const user = usePage().props.auth.user;

const firstName = computed(() => {
    const name = (user?.name || '').trim();
    return name ? name.split(/\s+/)[0] : '';
});

const period = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'morning';
    if (hour < 18) return 'afternoon';
    return 'evening';
});

const greeting = computed(() => __(`greeting.${period.value}`));

const periodIcon = computed(() => {
    if (period.value === 'morning') return Sun;
    if (period.value === 'afternoon') return Sunset;
    return Moon;
});

const roleMessage = computed(() => {
    const messages = {
        super_admin: 'greeting.msg_super_admin',
        admin: 'greeting.msg_admin',
        teacher: 'greeting.msg_teacher',
        student: 'greeting.msg_student',
    };

    return __(messages[user?.role] ?? 'greeting.msg_student');
});
</script>

<template>
    <div
        class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-gradient-to-r from-indigo-600/15 via-zinc-900/50 to-zinc-900/50 p-6"
    >
        <div
            class="pointer-events-none absolute -right-8 -top-10 h-40 w-40 rounded-full bg-indigo-600/10 blur-3xl"
        ></div>

        <div class="relative flex items-center gap-4">
            <div
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-lg shadow-indigo-600/25"
            >
                <component :is="periodIcon" class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <h2 class="truncate text-xl font-bold text-white sm:text-2xl">
                    {{ greeting }}<span v-if="firstName">, {{ firstName }}</span
                    >!
                </h2>
                <p class="mt-1 text-sm text-zinc-400">
                    {{ roleMessage }}
                </p>
            </div>
        </div>
    </div>
</template>
