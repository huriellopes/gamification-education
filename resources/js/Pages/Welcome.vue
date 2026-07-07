<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import HurvionSignature from '@/Components/HurvionSignature.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    CheckCircle2,
    ChevronRight,
    Cookie,
    LogIn,
    Menu,
    Quote,
    Sparkles,
    Star,
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

// Depoimentos ilustrativos (dados fictícios) usados na landing page para
// exibir prova social. As iniciais alimentam o avatar gerado por CSS.
const testimonials = [
    {
        name: 'Maria Silva',
        role: 'Aluna do 3º ano',
        initials: 'MS',
        color: 'bg-indigo-500/15 text-indigo-300 ring-indigo-500/30',
        rating: 5,
        quote: 'Nunca imaginei que estudar pudesse ser tão viciante. Subir no ranking da turma virou minha meta toda semana!',
    },
    {
        name: 'Prof. Carlos Mendes',
        role: 'Professor de Matemática',
        initials: 'CM',
        color: 'bg-emerald-500/15 text-emerald-300 ring-emerald-500/30',
        rating: 5,
        quote: 'Meus alunos entregam as atividades sem eu precisar cobrar. O sistema de XP fez a participação disparar em sala.',
    },
    {
        name: 'João Souza',
        role: 'Aluno do 2º ano',
        initials: 'JS',
        color: 'bg-yellow-500/15 text-yellow-300 ring-yellow-500/30',
        rating: 5,
        quote: 'O login por link mágico é sensacional, nunca mais esqueci senha. E adoro acompanhar meus pontos crescendo.',
    },
    {
        name: 'Ana Beatriz',
        role: 'Coordenadora Pedagógica',
        initials: 'AB',
        color: 'bg-violet-500/15 text-violet-300 ring-violet-500/30',
        rating: 5,
        quote: 'Conseguimos acompanhar o engajamento de cada turma em tempo real. Uma virada de chave na nossa gestão.',
    },
    {
        name: 'Lucas Ferreira',
        role: 'Aluno do 1º ano',
        initials: 'LF',
        color: 'bg-sky-500/15 text-sky-300 ring-sky-500/30',
        rating: 4,
        quote: 'As trilhas de aprendizado são bem organizadas e os desafios me mantêm focado até terminar cada matéria.',
    },
    {
        name: 'Fernanda Rocha',
        role: 'Professora de Ciências',
        initials: 'FR',
        color: 'bg-rose-500/15 text-rose-300 ring-rose-500/30',
        rating: 5,
        quote: 'A gamificação transformou a leitura dos materiais em algo que os alunos realmente querem fazer. Recomendo demais!',
    },
];

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
    // O JSON-LD (Schema.org) é renderizado server-side em app.blade.php.
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
    <!--
      Título de página (client-side). As demais metatags de SEO (description,
      Open Graph, Twitter e JSON-LD) são renderizadas server-side em
      resources/views/app.blade.php para que crawlers sem JS as leiam.
    -->
    <Head>
        <title>GamificaEdu — Aprenda Jogando e Conquiste o Topo</title>
    </Head>

    <div
        class="relative min-h-screen overflow-hidden bg-zinc-950 font-sans text-zinc-100 selection:bg-indigo-600 selection:text-white"
    >
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-indigo-600 focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-white"
        >
            {{ __('nav.skip_to_content') }}
        </a>
        <!-- Background Glow Effects -->
        <div
            class="pointer-events-none absolute left-[-10%] top-[-10%] h-[50%] w-[50%] rounded-full bg-indigo-900/10 blur-[150px]"
        ></div>
        <div
            class="pointer-events-none absolute bottom-[-10%] right-[-10%] h-[50%] w-[50%] rounded-full bg-emerald-900/10 blur-[150px]"
        ></div>

        <!-- Navigation -->
        <header
            class="sticky top-0 z-50 h-20 border-b border-zinc-800/80 bg-zinc-950/60 backdrop-blur-xl transition-all duration-300"
        >
            <div
                class="mx-auto flex h-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <div class="flex items-center gap-3">
                    <ApplicationLogo
                        class="h-10 w-10 drop-shadow-lg"
                        aria-hidden="true"
                    />
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
                        class="group inline-flex items-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-2 text-sm font-semibold text-zinc-200 transition-all hover:bg-zinc-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <span>{{ __('welcome.nav.go_dashboard') }}</span>
                        <ChevronRight
                            class="h-4 w-4 animate-chevron-right motion-reduce:animate-none"
                        />
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
                        class="group flex items-center justify-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900 py-3 text-sm font-bold text-zinc-200 hover:bg-zinc-800"
                    >
                        <span>{{ __('welcome.nav.go_dashboard') }}</span>
                        <ChevronRight
                            class="h-4 w-4 animate-chevron-right motion-reduce:animate-none"
                        />
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
            id="main-content"
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
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-8 py-3.5 text-base font-bold text-white shadow-lg shadow-indigo-600/30 transition-all hover:bg-indigo-500 hover:shadow-indigo-500/40 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:w-auto"
                    >
                        <span>{{ __('welcome.hero.access_panel') }}</span>
                        <ChevronRight
                            class="h-5 w-5 animate-chevron-right motion-reduce:animate-none"
                        />
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('register')"
                            class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-8 py-3.5 text-base font-bold text-white shadow-lg shadow-indigo-600/30 transition-all hover:bg-indigo-500 hover:shadow-indigo-500/40 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:w-auto"
                        >
                            <span>{{ __('welcome.hero.start_now') }}</span>
                            <ChevronRight
                                class="h-5 w-5 animate-chevron-right motion-reduce:animate-none"
                            />
                        </Link>
                        <Link
                            :href="route('login', { tab: 'magic' })"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900 px-8 py-3.5 text-base font-bold text-zinc-300 transition-all hover:bg-zinc-800 hover:text-white focus:outline-none sm:w-auto"
                        >
                            <Sparkles class="h-5 w-5 text-amber-400" />
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
                        class="rounded-lg border border-zinc-800 bg-zinc-950 px-6 py-1 font-mono text-[10px] text-zinc-400"
                    >
                        https://gamificaedu.com/student/dashboard
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
                            <p class="text-[11px] text-zinc-400">
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
                            class="mt-2 text-xs font-bold uppercase tracking-wider text-zinc-400 sm:text-sm"
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
                            class="mt-2 text-xs font-bold uppercase tracking-wider text-zinc-400 sm:text-sm"
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
                            class="mt-2 text-xs font-bold uppercase tracking-wider text-zinc-400 sm:text-sm"
                        >
                            {{ __('welcome.stats.xp_earned') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-white sm:text-5xl">
                            100%
                        </p>
                        <p
                            class="mt-2 text-xs font-bold uppercase tracking-wider text-zinc-400 sm:text-sm"
                        >
                            {{ __('welcome.stats.online_responsive') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-indigo-500/30 bg-indigo-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-indigo-400"
                >
                    <Sparkles class="h-3.5 w-3.5" />
                    {{ __('welcome.testimonials.badge') }}
                </span>
                <h2
                    class="mt-5 text-3xl font-black tracking-tight text-white sm:text-4xl"
                >
                    {{ __('welcome.testimonials.title') }}
                </h2>
                <p class="mt-4 text-base font-medium text-zinc-400">
                    {{ __('welcome.testimonials.subtitle') }}
                </p>
            </div>

            <div
                class="mt-14 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <figure
                    v-for="(testimonial, index) in testimonials"
                    :key="index"
                    class="relative flex flex-col rounded-2xl border border-zinc-800/80 bg-zinc-900/30 p-6 transition-all hover:-translate-y-1 hover:border-zinc-700 hover:bg-zinc-900/60"
                >
                    <Quote
                        class="absolute right-5 top-5 h-8 w-8 text-zinc-800"
                        aria-hidden="true"
                    />

                    <!-- Rating -->
                    <div
                        class="flex items-center gap-0.5"
                        :aria-label="`${testimonial.rating} de 5 estrelas`"
                    >
                        <Star
                            v-for="star in 5"
                            :key="star"
                            class="h-4 w-4"
                            :class="
                                star <= testimonial.rating
                                    ? 'fill-yellow-400 text-yellow-400'
                                    : 'text-zinc-700'
                            "
                            aria-hidden="true"
                        />
                    </div>

                    <!-- Quote -->
                    <blockquote
                        class="mt-4 flex-1 text-sm font-medium leading-relaxed text-zinc-300"
                    >
                        “{{ testimonial.quote }}”
                    </blockquote>

                    <!-- Author -->
                    <figcaption class="mt-6 flex items-center gap-3">
                        <span
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-bold ring-1 ring-inset"
                            :class="testimonial.color"
                            aria-hidden="true"
                        >
                            {{ testimonial.initials }}
                        </span>
                        <span class="flex flex-col">
                            <span class="text-sm font-bold text-white">{{
                                testimonial.name
                            }}</span>
                            <span class="text-xs font-medium text-zinc-400">{{
                                testimonial.role
                            }}</span>
                        </span>
                    </figcaption>
                </figure>
            </div>
        </section>

        <!-- Footer -->
        <footer
            class="mx-auto flex max-w-7xl flex-col items-center gap-4 border-t border-zinc-900 px-4 py-12 text-center font-mono text-xs text-zinc-400 sm:px-6 lg:px-8"
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
            <HurvionSignature class="mt-2" />
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
                                class="mt-2 flex flex-wrap items-center justify-center gap-x-2 gap-y-1 text-xs text-zinc-400 sm:justify-start"
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
