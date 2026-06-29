<script setup>
import { __ } from '@/i18n';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, GraduationCap, LogIn } from '@lucide/vue';

const user = usePage().props.auth?.user;

defineProps({
    content: {
        type: Object,
        required: true,
    },
    backToSite: {
        type: String,
        default: 'Voltar ao site',
    },
    updatedPrefix: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <Head>
        <title>{{ content.title }} — GamificaEdu</title>
        <meta name="description" :content="content.meta_description" />
    </Head>

    <div class="flex min-h-screen flex-col bg-zinc-950 text-zinc-100">
        <!-- Top bar -->
        <header
            class="sticky top-0 z-30 border-b border-zinc-800/80 bg-zinc-950/80 backdrop-blur-xl"
        >
            <div
                class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8"
            >
                <Link
                    href="/"
                    class="inline-flex items-center gap-2 font-black tracking-tight"
                >
                    <span class="rounded-xl bg-indigo-600 p-1.5 text-white">
                        <GraduationCap class="h-5 w-5" />
                    </span>
                    <span class="text-lg">GamificaEdu</span>
                </Link>

                <div class="flex items-center gap-2 sm:gap-3">
                    <Link
                        href="/"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-800 px-3 py-2 text-xs font-bold text-zinc-300 transition-all hover:bg-zinc-900 hover:text-white"
                    >
                        <ArrowLeft class="h-3.5 w-3.5" />
                        <span class="hidden sm:inline">{{ backToSite }}</span>
                    </Link>
                    <Link
                        v-if="user"
                        :href="route('dashboard')"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-bold text-white shadow-lg shadow-indigo-600/25 transition-all hover:bg-indigo-500"
                    >
                        {{ __('welcome.nav.go_dashboard') }}
                        <ArrowRight class="h-3.5 w-3.5" />
                    </Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-bold text-white shadow-lg shadow-indigo-600/25 transition-all hover:bg-indigo-500"
                    >
                        <LogIn class="h-3.5 w-3.5" />
                        {{ __('welcome.nav.login') }}
                    </Link>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1">
            <article
                class="mx-auto max-w-3xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8"
            >
                <header class="border-b border-zinc-800/70 pb-8">
                    <h1
                        class="text-3xl font-black tracking-tight text-white sm:text-4xl"
                    >
                        {{ content.title }}
                    </h1>
                    <p
                        v-if="content.updated"
                        class="mt-3 inline-flex items-center rounded-full border border-zinc-800 bg-zinc-900/60 px-3 py-1 text-xs font-semibold text-zinc-400"
                    >
                        {{ updatedPrefix }} {{ content.updated }}
                    </p>
                    <p
                        v-if="content.intro"
                        class="mt-6 text-base leading-relaxed text-zinc-300"
                    >
                        {{ content.intro }}
                    </p>
                </header>

                <div class="mt-10 space-y-10">
                    <section
                        v-for="(section, index) in content.sections"
                        :key="index"
                        class="space-y-3"
                    >
                        <h2 class="text-lg font-bold text-white sm:text-xl">
                            {{ section.heading }}
                        </h2>
                        <p
                            v-if="section.body"
                            class="text-sm leading-relaxed text-zinc-400 sm:text-base"
                        >
                            {{ section.body }}
                        </p>
                        <ul
                            v-if="section.items"
                            class="ml-1 space-y-2 text-sm leading-relaxed text-zinc-400 sm:text-base"
                        >
                            <li
                                v-for="(item, itemIndex) in section.items"
                                :key="itemIndex"
                                class="flex gap-2.5"
                            >
                                <span
                                    class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-500"
                                    aria-hidden="true"
                                />
                                <span>{{ item }}</span>
                            </li>
                        </ul>
                    </section>
                </div>
            </article>
        </main>

        <!-- Footer -->
        <footer
            class="border-t border-zinc-900 px-4 py-10 text-center font-mono text-xs text-zinc-600 sm:px-6 lg:px-8"
        >
            <div
                class="mx-auto flex max-w-5xl flex-col items-center justify-center gap-3"
            >
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('legal.privacy')"
                        class="transition-colors hover:text-indigo-400"
                    >
                        {{ __('welcome.legal.privacy') }}
                    </Link>
                    <span class="text-zinc-800">/</span>
                    <Link
                        :href="route('legal.guidelines')"
                        class="transition-colors hover:text-indigo-400"
                    >
                        {{ __('welcome.legal.guidelines') }}
                    </Link>
                </div>
                <span>{{
                    __('welcome.footer', { year: new Date().getFullYear() })
                }}</span>
            </div>
        </footer>
    </div>
</template>
