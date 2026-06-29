<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpen,
    CheckCircle2,
    Cookie,
    GraduationCap,
    LogIn,
    Menu,
    Sparkles,
    Trophy,
    UserPlus,
    X,
    Zap,
} from '@lucide/vue';
import { onMounted, ref } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
    stats: {
        type: Object,
        default: () => ({
            students: 0,
            subjects: 0,
            xp: '0',
        }),
    },
});

const page = usePage();
const user = page.props.auth?.user;

const isMobileMenuOpen = ref(false);
const showCookieConsent = ref(false);
const consentStatus = ref('');

onMounted(() => {
    const savedConsent = localStorage.getItem('gamificaedu_cookie_consent');
    if (!savedConsent) {
        showCookieConsent.value = true;
    } else {
        consentStatus.value = savedConsent;
        if (savedConsent === 'accepted') {
            enableAds();
        }
    }

    // Injeta Schema.org JSON-LD estruturado programaticamente para SEO
    const schemaScript = document.createElement('script');
    schemaScript.type = 'application/ld+json';
    schemaScript.id = 'schema-org-jsonld';
    schemaScript.text = JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'EducationalApplication',
        name: 'GamificaEdu',
        operatingSystem: 'All',
        applicationCategory: 'EducationalApplication',
        description:
            'Plataforma de aprendizado gamificado e gestão de ensino multidisciplinar.',
        offers: {
            '@type': 'Offer',
            price: '0.00',
            priceCurrency: 'BRL',
        },
        provider: {
            '@type': 'Organization',
            name: 'GamificaEdu Inc.',
            url: 'https://gamificaedu.com.br',
        },
    });
    document.head.appendChild(schemaScript);
});

const acceptCookies = () => {
    localStorage.setItem('gamificaedu_cookie_consent', 'accepted');
    consentStatus.value = 'accepted';
    showCookieConsent.value = false;
    enableAds();
};

const declineCookies = () => {
    localStorage.setItem('gamificaedu_cookie_consent', 'rejected');
    consentStatus.value = 'rejected';
    showCookieConsent.value = false;
    console.log('[Google Ads] Cookies recusados pelo usuário.');
};

const enableAds = () => {
    console.log(
        '[Google Ads] Cookies aceitos! Ativando rastreamento de anúncios e pixel de captação de dados.',
    );
    const script = document.createElement('script');
    script.type = 'text/javascript';
    script.id = 'google-ads-mock';
    script.text = `
        window.googleAdsActive = true;
        console.log("[Google Ads Pixel] Coletando cookies e dados demográficos do usuário para otimização de campanhas.");
    `;
    document.body.appendChild(script);
};
</script>

<template>
    <Head>
        <title>GamificaEdu — Aprenda Jogando e Conquiste o Topo</title>
        <meta
            name="description"
            content="GamificaEdu é uma plataforma de aprendizado gamificado voltada ao ensino básico e superior, engajando alunos por meio de trilhas de aprendizagem, testes e pontuação (XP)."
        />
        <meta
            name="keywords"
            content="gamificação, educação, aprendizado gamificado, trilha de estudos, quiz educacional, ranking escolar, leaderboards"
        />
        <meta
            property="og:title"
            content="GamificaEdu — Aprenda Jogando e Conquiste o Topo"
        />
        <meta
            property="og:description"
            content="Aumente o engajamento de seus alunos por meio de pontos, trilhas visuais e testes dinâmicos com GamificaEdu."
        />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://gamificaedu.com.br" />
        <meta
            property="og:image"
            content="https://gamificaedu.com.br/assets/images/og-image.jpg"
        />
        <meta name="twitter:card" content="summary_large_image" />
        <meta
            name="twitter:title"
            content="GamificaEdu — Aprenda Jogando e Conquiste o Topo"
        />
        <meta
            name="twitter:description"
            content="Aumente o engajamento de seus alunos por meio de pontos, trilhas visuais e testes dinâmicos com GamificaEdu."
        />
    </Head>

    <div
        class="relative min-h-screen overflow-hidden bg-zinc-950 font-sans text-zinc-100 selection:bg-indigo-600 selection:text-white"
    >
        <!-- Background Glow Effects -->
        <div
            class="pointer-events-none absolute left-[-10%] top-[-10%] h-[50%] w-[50%] rounded-full bg-indigo-900/10 blur-[150px]"
        ></div>
        <div
            class="pointer-events-none absolute bottom-[-10%] right-[-10%] h-[50%] w-[50%] rounded-full bg-emerald-900/10 blur-[150px]"
        ></div>

        <!-- Navigation -->
        <header
            class="sticky top-0 z-50 border-b border-zinc-800/80 bg-zinc-950/60 backdrop-blur-xl transition-all duration-300"
        >
            <div
                class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="rounded-xl bg-indigo-600 p-2 text-white shadow-lg shadow-indigo-600/20"
                    >
                        <GraduationCap class="h-6 w-6" />
                    </div>
                    <span
                        class="bg-gradient-to-r from-white via-zinc-200 to-zinc-400 bg-clip-text text-xl font-extrabold tracking-tight text-transparent"
                    >
                        GamificaEdu
                    </span>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden items-center gap-4 md:flex">
                    <Link
                        v-if="user"
                        :href="route('dashboard')"
                        class="inline-flex items-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-2 text-sm font-semibold text-zinc-200 transition-all hover:bg-zinc-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <span>{{ __('welcome.nav.go_dashboard') }}</span>
                        <ArrowRight class="h-4 w-4" />
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-zinc-400 transition-colors hover:text-white focus:outline-none"
                        >
                            <LogIn class="h-4 w-4" />
                            <span>{{ __('welcome.nav.login') }}</span>
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-600/25 transition-all hover:bg-indigo-500 hover:shadow-indigo-500/35 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <UserPlus class="h-4 w-4" />
                            <span>{{ __('welcome.nav.create_account') }}</span>
                        </Link>
                    </template>
                </nav>

                <!-- Hamburger Button -->
                <button
                    @click="isMobileMenuOpen = !isMobileMenuOpen"
                    class="inline-flex items-center justify-center rounded-xl p-2 text-zinc-400 transition-all hover:bg-zinc-900 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 md:hidden"
                >
                    <component
                        :is="isMobileMenuOpen ? X : Menu"
                        class="h-6 w-6"
                    />
                </button>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div
                v-if="isMobileMenuOpen"
                class="space-y-4 border-t border-zinc-800/80 bg-zinc-950/95 px-4 py-6 backdrop-blur-xl transition-all duration-300 md:hidden"
            >
                <div class="flex flex-col gap-3">
                    <Link
                        v-if="user"
                        :href="route('dashboard')"
                        class="flex items-center justify-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900 py-3 text-sm font-bold text-zinc-200 hover:bg-zinc-800"
                    >
                        <span>{{ __('welcome.nav.go_dashboard') }}</span>
                        <ArrowRight class="h-4 w-4" />
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="flex items-center justify-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900 py-3 text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                        >
                            <LogIn class="h-4 w-4" />
                            <span>{{ __('welcome.nav.login') }}</span>
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="flex items-center justify-center gap-2 rounded-xl bg-indigo-600 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/25"
                        >
                            <UserPlus class="h-4 w-4" />
                            <span>{{ __('welcome.nav.create_account') }}</span>
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section
            class="relative z-10 mx-auto max-w-7xl px-4 pb-16 pt-20 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-3xl space-y-6 text-center">
                <!-- Badge -->
                <div
                    class="inline-flex animate-pulse items-center gap-2 rounded-full border border-indigo-500/30 bg-indigo-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-indigo-400 backdrop-blur-md"
                >
                    <Sparkles class="h-3.5 w-3.5" />
                    <span>{{ __('welcome.hero.badge') }}</span>
                </div>

                <!-- Main Title -->
                <h1
                    class="text-4xl font-black leading-tight tracking-tight text-white sm:text-6xl"
                >
                    {{ __('welcome.hero.title') }}
                    <span
                        class="bg-gradient-to-r from-indigo-400 via-indigo-500 to-emerald-400 bg-clip-text text-transparent"
                    >
                        {{ __('welcome.hero.title_highlight') }}
                    </span>
                </h1>

                <!-- Description -->
                <p
                    class="mx-auto max-w-2xl text-base font-medium text-zinc-400 sm:text-lg"
                >
                    {{ __('welcome.hero.description') }}
                </p>

                <!-- CTA Buttons -->
                <div
                    class="flex flex-col items-center justify-center gap-4 pt-4 sm:flex-row"
                >
                    <Link
                        v-if="user"
                        :href="route('dashboard')"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-8 py-3.5 text-base font-bold text-white shadow-lg shadow-indigo-600/30 transition-all hover:bg-indigo-500 hover:shadow-indigo-500/40 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:w-auto"
                    >
                        <span>{{ __('welcome.hero.access_panel') }}</span>
                        <ArrowRight class="h-5 w-5" />
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('register')"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-8 py-3.5 text-base font-bold text-white shadow-lg shadow-indigo-600/30 transition-all hover:bg-indigo-500 hover:shadow-indigo-500/40 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:w-auto"
                        >
                            <span>{{ __('welcome.hero.start_now') }}</span>
                            <ArrowRight class="h-5 w-5" />
                        </Link>
                        <Link
                            :href="route('login')"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900 px-8 py-3.5 text-base font-bold text-zinc-300 transition-all hover:bg-zinc-800 hover:text-white focus:outline-none sm:w-auto"
                        >
                            <LogIn class="h-5 w-5" />
                            <span>{{ __('welcome.hero.magic_login') }}</span>
                        </Link>
                    </template>
                </div>
            </div>

            <!-- Dashboard Preview UI Mockup -->
            <div
                class="relative mt-16 rounded-2xl border border-zinc-800 bg-zinc-900/20 p-4 shadow-2xl backdrop-blur-xl sm:p-6"
            >
                <div
                    class="absolute inset-0 z-10 bg-gradient-to-t from-zinc-950 via-transparent to-transparent"
                ></div>
                <!-- Mockup Header Controls -->
                <div
                    class="mb-6 flex items-center justify-between border-b border-zinc-800/80 pb-4"
                >
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full bg-red-500"></span>
                        <span class="h-3 w-3 rounded-full bg-yellow-500"></span>
                        <span class="h-3 w-3 rounded-full bg-green-500"></span>
                    </div>
                    <div
                        class="rounded-lg border border-zinc-800 bg-zinc-950 px-6 py-1 font-mono text-[10px] text-zinc-500"
                    >
                        https://gamifica.edu/student/dashboard
                    </div>
                    <div class="w-12"></div>
                </div>

                <!-- Inner Layout Mockup -->
                <div class="relative grid grid-cols-1 gap-6 md:grid-cols-3">
                    <!-- Column 1: Progress card -->
                    <div
                        class="space-y-4 rounded-xl border border-zinc-800 bg-zinc-900/60 p-5"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs font-bold uppercase tracking-wider text-zinc-400"
                                >{{ __('welcome.mockup.progress') }}</span
                            >
                            <Zap class="h-5 w-5 text-indigo-400" />
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="font-semibold text-white">{{
                                    __('welcome.mockup.level')
                                }}</span>
                                <span class="font-bold text-indigo-400"
                                    >1.250 XP</span
                                >
                            </div>
                            <div
                                class="h-2 w-full rounded-full border border-zinc-800 bg-zinc-950"
                            >
                                <div
                                    class="h-2 w-[70%] rounded-full bg-indigo-500"
                                ></div>
                            </div>
                            <p class="text-[11px] text-zinc-500">
                                {{ __('welcome.mockup.level_hint') }}
                            </p>
                        </div>
                    </div>

                    <!-- Column 2: Leaderboard mockup -->
                    <div
                        class="space-y-3 rounded-xl border border-zinc-800 bg-zinc-900/60 p-5"
                    >
                        <div
                            class="flex items-center justify-between border-b border-zinc-800 pb-2"
                        >
                            <span
                                class="text-xs font-bold uppercase tracking-wider text-zinc-400"
                                >{{ __('welcome.mockup.top_students') }}</span
                            >
                            <Trophy class="h-5 w-5 text-yellow-500" />
                        </div>
                        <div class="space-y-2 text-xs">
                            <div
                                class="flex items-center justify-between rounded-lg border border-yellow-500/20 bg-yellow-500/10 p-2"
                            >
                                <span class="font-semibold text-zinc-200"
                                    >1º Maria Silva</span
                                >
                                <span class="font-extrabold text-yellow-400"
                                    >2.450 pts</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between rounded-lg bg-zinc-950 p-2"
                            >
                                <span class="font-semibold text-zinc-300"
                                    >2º João Souza</span
                                >
                                <span class="font-extrabold text-zinc-400"
                                    >1.820 pts</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between rounded-lg bg-zinc-950 p-2"
                            >
                                <span class="font-semibold text-zinc-300"
                                    >3º Aluno Demonstrativo</span
                                >
                                <span class="font-extrabold text-zinc-400"
                                    >1.250 pts</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Column 3: Subject card -->
                    <div
                        class="space-y-4 rounded-xl border border-indigo-900/30 bg-indigo-950/20 p-5"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs font-bold uppercase tracking-wider text-indigo-400"
                                >{{
                                    __('welcome.mockup.subject_in_progress')
                                }}</span
                            >
                            <BookOpen class="h-5 w-5 text-emerald-400" />
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-sm font-bold text-white">
                                {{ __('welcome.mockup.subject_title') }}
                            </h4>
                            <p class="text-[11px] text-zinc-400">
                                {{ __('welcome.mockup.subject_desc') }}
                            </p>
                            <div class="pt-2">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-bold text-emerald-400"
                                >
                                    <CheckCircle2 class="h-3 w-3" />
                                    <span>{{
                                        __('welcome.mockup.material_read')
                                    }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section
            class="mx-auto max-w-7xl border-t border-zinc-900 px-4 py-16 sm:px-6 lg:px-8"
        >
            <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                <!-- Feature 1 -->
                <div
                    class="space-y-3 rounded-2xl border border-zinc-800/80 bg-zinc-900/30 p-6"
                >
                    <div
                        class="w-fit rounded-xl bg-indigo-500/10 p-3 text-indigo-400"
                    >
                        <BookOpen class="h-6 w-6" />
                    </div>
                    <h3 class="text-lg font-bold text-white">
                        {{ __('welcome.features.tracks_title') }}
                    </h3>
                    <p
                        class="text-sm font-medium leading-relaxed text-zinc-400"
                    >
                        {{ __('welcome.features.tracks_desc') }}
                    </p>
                </div>

                <!-- Feature 2 -->
                <div
                    class="space-y-3 rounded-2xl border border-zinc-800/80 bg-zinc-900/30 p-6"
                >
                    <div
                        class="w-fit rounded-xl bg-emerald-500/10 p-3 text-emerald-400"
                    >
                        <Zap class="h-6 w-6" />
                    </div>
                    <h3 class="text-lg font-bold text-white">
                        {{ __('welcome.features.gamification_title') }}
                    </h3>
                    <p
                        class="text-sm font-medium leading-relaxed text-zinc-400"
                    >
                        {{ __('welcome.features.gamification_desc') }}
                    </p>
                </div>

                <!-- Feature 3 -->
                <div
                    class="space-y-3 rounded-2xl border border-zinc-800/80 bg-zinc-900/30 p-6"
                >
                    <div
                        class="w-fit rounded-xl bg-yellow-500/10 p-3 text-yellow-400"
                    >
                        <Trophy class="h-6 w-6" />
                    </div>
                    <h3 class="text-lg font-bold text-white">
                        {{ __('welcome.features.rankings_title') }}
                    </h3>
                    <p
                        class="text-sm font-medium leading-relaxed text-zinc-400"
                    >
                        {{ __('welcome.features.rankings_desc') }}
                    </p>
                </div>

                <!-- Feature 4 -->
                <div
                    class="space-y-3 rounded-2xl border border-zinc-800/80 bg-zinc-900/30 p-6"
                >
                    <div
                        class="w-fit rounded-xl bg-indigo-500/10 p-3 text-indigo-400"
                    >
                        <Sparkles class="h-6 w-6" />
                    </div>
                    <h3 class="text-lg font-bold text-white">
                        {{ __('welcome.features.passwordless_title') }}
                    </h3>
                    <p
                        class="text-sm font-medium leading-relaxed text-zinc-400"
                    >
                        {{ __('welcome.features.passwordless_desc') }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="border-b border-t border-zinc-900 bg-zinc-900/20 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-8 text-center md:grid-cols-4">
                    <div>
                        <p class="text-4xl font-black text-white sm:text-5xl">
                            {{ stats.students }}
                        </p>
                        <p
                            class="mt-2 text-xs font-bold uppercase tracking-wider text-zinc-500 sm:text-sm"
                        >
                            {{ __('welcome.stats.active_students') }}
                        </p>
                    </div>
                    <div>
                        <p
                            class="text-4xl font-black text-indigo-400 sm:text-5xl"
                        >
                            {{ stats.subjects }}
                        </p>
                        <p
                            class="mt-2 text-xs font-bold uppercase tracking-wider text-zinc-500 sm:text-sm"
                        >
                            {{ __('welcome.stats.subjects_offered') }}
                        </p>
                    </div>
                    <div>
                        <p
                            class="text-4xl font-black text-emerald-400 sm:text-5xl"
                        >
                            {{ stats.xp }}
                        </p>
                        <p
                            class="mt-2 text-xs font-bold uppercase tracking-wider text-zinc-500 sm:text-sm"
                        >
                            {{ __('welcome.stats.xp_earned') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-white sm:text-5xl">
                            100%
                        </p>
                        <p
                            class="mt-2 text-xs font-bold uppercase tracking-wider text-zinc-500 sm:text-sm"
                        >
                            {{ __('welcome.stats.online_responsive') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer
            class="mx-auto flex max-w-7xl flex-col items-center gap-4 px-4 py-12 text-center font-mono text-xs text-zinc-600 sm:px-6 lg:px-8"
        >
            <div class="flex items-center gap-4">
                <Link
                    :href="route('legal.privacy')"
                    class="font-semibold transition-colors hover:text-indigo-400"
                >
                    {{ __('welcome.legal.privacy') }}
                </Link>
                <span class="text-zinc-800" aria-hidden="true">/</span>
                <Link
                    :href="route('legal.guidelines')"
                    class="font-semibold transition-colors hover:text-indigo-400"
                >
                    {{ __('welcome.legal.guidelines') }}
                </Link>
            </div>
            <span>{{
                __('welcome.footer', { year: new Date().getFullYear() })
            }}</span>
        </footer>

        <!-- Cookie Consent Banner -->
        <transition
            enter-active-class="transition ease-out duration-300 transform"
            enter-from-class="translate-y-full opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-200 transform"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-full opacity-0"
        >
            <div
                v-if="showCookieConsent"
                class="fixed inset-x-0 bottom-0 z-50 px-3 pb-3 sm:px-4 sm:pb-4"
                role="dialog"
                aria-modal="false"
                :aria-label="__('welcome.cookie.title')"
            >
                <div
                    class="mx-auto flex max-w-5xl flex-col gap-5 rounded-2xl border border-zinc-800 bg-zinc-900/95 p-5 shadow-2xl shadow-black/40 backdrop-blur-xl sm:p-6 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex items-start gap-4">
                        <span
                            class="hidden shrink-0 rounded-xl border border-indigo-500/20 bg-indigo-500/10 p-2.5 text-indigo-400 sm:inline-flex"
                            aria-hidden="true"
                        >
                            <Cookie class="h-6 w-6" />
                        </span>
                        <div
                            class="max-w-2xl text-center text-sm font-medium text-zinc-300 sm:text-left"
                        >
                            <span
                                class="mb-1 block text-base font-bold text-white"
                                >{{ __('welcome.cookie.title') }}</span
                            >
                            {{ __('welcome.cookie.body') }}
                            <span
                                class="mt-2 flex flex-wrap items-center justify-center gap-x-2 gap-y-1 text-xs text-zinc-500 sm:justify-start"
                            >
                                <span>{{
                                    __('welcome.cookie.learn_more')
                                }}</span>
                                <Link
                                    :href="route('legal.privacy')"
                                    class="font-semibold text-indigo-400 underline-offset-2 hover:underline"
                                    >{{ __('welcome.legal.privacy') }}</Link
                                >
                                <span class="text-zinc-700" aria-hidden="true"
                                    >·</span
                                >
                                <Link
                                    :href="route('legal.guidelines')"
                                    class="font-semibold text-indigo-400 underline-offset-2 hover:underline"
                                    >{{ __('welcome.legal.guidelines') }}</Link
                                >
                            </span>
                        </div>
                    </div>
                    <div
                        class="flex w-full shrink-0 items-center justify-center gap-3 lg:w-auto"
                    >
                        <button
                            type="button"
                            @click="declineCookies"
                            class="flex-1 rounded-xl border border-zinc-800 px-5 py-2.5 text-xs font-bold text-zinc-400 transition-all hover:bg-zinc-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-zinc-700 lg:flex-none"
                        >
                            {{ __('welcome.cookie.decline') }}
                        </button>
                        <button
                            type="button"
                            @click="acceptCookies"
                            class="flex-1 rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-indigo-600/25 transition-all hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400 lg:flex-none"
                        >
                            {{ __('welcome.cookie.accept') }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
