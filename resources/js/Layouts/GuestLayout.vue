<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Toast from '@/Components/Toast.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 py-10 dark:bg-gray-900 sm:justify-center sm:py-12"
    >
        <Toast />
        <div>
            <Link href="/" class="flex flex-col items-center gap-3">
                <ApplicationLogo class="h-20 w-20 drop-shadow-xl" />
                <span class="text-lg font-extrabold tracking-wider text-white">
                    Gamifica<span class="text-indigo-400">Edu</span>
                </span>
            </Link>
        </div>

        <div
            class="mb-2 mt-8 w-full overflow-hidden bg-white px-6 py-8 shadow-md dark:bg-gray-800 sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
