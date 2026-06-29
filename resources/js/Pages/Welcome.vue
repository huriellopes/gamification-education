<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { 
    Sparkles, 
    Trophy, 
    BookOpen, 
    Zap, 
    LogIn, 
    UserPlus, 
    ArrowRight, 
    GraduationCap, 
    TrendingUp, 
    CheckCircle2,
    Menu,
    X
} from '@lucide/vue';
import { ref, onMounted } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
        default: true
    },
    canRegister: {
        type: Boolean,
        default: true
    },
    stats: {
        type: Object,
        default: () => ({
            students: 0,
            subjects: 0,
            xp: '0'
        })
    }
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
        "@context": "https://schema.org",
        "@type": "EducationalApplication",
        "name": "GamificaEdu",
        "operatingSystem": "All",
        "applicationCategory": "EducationalApplication",
        "description": "Plataforma de aprendizado gamificado e gestão de ensino multidisciplinar.",
        "offers": {
            "@type": "Offer",
            "price": "0.00",
            "priceCurrency": "BRL"
        },
        "provider": {
            "@type": "Organization",
            "name": "GamificaEdu Inc.",
            "url": "https://gamificaedu.com.br"
        }
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
    console.log('[Google Ads] Cookies aceitos! Ativando rastreamento de anúncios e pixel de captação de dados.');
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
        <meta name="description" content="GamificaEdu é uma plataforma de aprendizado gamificado voltada ao ensino básico e superior, engajando alunos por meio de trilhas de aprendizagem, testes e pontuação (XP)." />
        <meta name="keywords" content="gamificação, educação, aprendizado gamificado, trilha de estudos, quiz educacional, ranking escolar, leaderboards" />
        <meta property="og:title" content="GamificaEdu — Aprenda Jogando e Conquiste o Topo" />
        <meta property="og:description" content="Aumente o engajamento de seus alunos por meio de pontos, trilhas visuais e testes dinâmicos com GamificaEdu." />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://gamificaedu.com.br" />
        <meta property="og:image" content="https://gamificaedu.com.br/assets/images/og-image.jpg" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="GamificaEdu — Aprenda Jogando e Conquiste o Topo" />
        <meta name="twitter:description" content="Aumente o engajamento de seus alunos por meio de pontos, trilhas visuais e testes dinâmicos com GamificaEdu." />
    </Head>

    <div class="min-h-screen bg-zinc-950 text-zinc-100 font-sans selection:bg-indigo-600 selection:text-white relative overflow-hidden">
        <!-- Background Glow Effects -->
        <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-900/10 blur-[150px] pointer-events-none"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-emerald-900/10 blur-[150px] pointer-events-none"></div>

        <!-- Navigation -->
        <header class="border-b border-zinc-800/80 bg-zinc-950/60 backdrop-blur-xl sticky top-0 z-50 transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 p-2 rounded-xl text-white shadow-lg shadow-indigo-600/20">
                        <GraduationCap class="h-6 w-6" />
                    </div>
                    <span class="font-extrabold text-xl tracking-tight bg-gradient-to-r from-white via-zinc-200 to-zinc-400 bg-clip-text text-transparent">
                        GamificaEdu
                    </span>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center gap-4">
                    <Link
                        v-if="user"
                        :href="route('dashboard')"
                        class="inline-flex items-center gap-2 rounded-xl bg-zinc-900 border border-zinc-800 px-4 py-2 text-sm font-semibold text-zinc-200 hover:bg-zinc-800 hover:text-white transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <span>Ir para o Painel</span>
                        <ArrowRight class="h-4 w-4" />
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-zinc-400 hover:text-white transition-colors focus:outline-none"
                        >
                            <LogIn class="h-4 w-4" />
                            <span>Entrar</span>
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-600/25 hover:bg-indigo-500 hover:shadow-indigo-500/35 transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <UserPlus class="h-4 w-4" />
                            <span>Criar Conta</span>
                        </Link>
                    </template>
                </nav>

                <!-- Hamburger Button -->
                <button
                    @click="isMobileMenuOpen = !isMobileMenuOpen"
                    class="md:hidden inline-flex items-center justify-center p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                >
                    <component :is="isMobileMenuOpen ? X : Menu" class="h-6 w-6" />
                </button>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div 
                v-if="isMobileMenuOpen" 
                class="md:hidden border-t border-zinc-800/80 bg-zinc-950/95 backdrop-blur-xl px-4 py-6 space-y-4 transition-all duration-300"
            >
                <div class="flex flex-col gap-3">
                    <Link
                        v-if="user"
                        :href="route('dashboard')"
                        class="flex items-center justify-center gap-2 rounded-xl bg-zinc-900 border border-zinc-800 py-3 text-sm font-bold text-zinc-200 hover:bg-zinc-800"
                    >
                        <span>Ir para o Painel</span>
                        <ArrowRight class="h-4 w-4" />
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="flex items-center justify-center gap-2 rounded-xl bg-zinc-900 border border-zinc-800 py-3 text-sm font-bold text-zinc-300 hover:bg-zinc-800"
                        >
                            <LogIn class="h-4 w-4" />
                            <span>Entrar</span>
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="flex items-center justify-center gap-2 rounded-xl bg-indigo-600 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/25"
                        >
                            <UserPlus class="h-4 w-4" />
                            <span>Criar Conta</span>
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-16 relative z-10">
            <div class="text-center max-w-3xl mx-auto space-y-6">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 bg-indigo-500/10 border border-indigo-500/30 rounded-full px-4 py-1.5 text-xs text-indigo-400 font-semibold uppercase tracking-wider backdrop-blur-md animate-pulse">
                    <Sparkles class="h-3.5 w-3.5" />
                    <span>Plataforma de Ensino Gamificado</span>
                </div>

                <!-- Main Title -->
                <h1 class="text-4xl sm:text-6xl font-black tracking-tight text-white leading-tight">
                    Transforme conhecimento em 
                    <span class="bg-gradient-to-r from-indigo-400 via-indigo-500 to-emerald-400 bg-clip-text text-transparent">
                        conquistas reais.
                    </span>
                </h1>

                <!-- Description -->
                <p class="text-base sm:text-lg text-zinc-400 max-w-2xl mx-auto font-medium">
                    Aprenda matérias geradas de forma inteligente, resolva desafios empolgantes, acumule pontos XP e dispute as melhores colocações no ranking da sua instituição de ensino.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                    <Link
                        v-if="user"
                        :href="route('dashboard')"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-8 py-3.5 text-base font-bold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-500 hover:shadow-indigo-500/40 transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <span>Acessar Meu Painel</span>
                        <ArrowRight class="h-5 w-5" />
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('register')"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-8 py-3.5 text-base font-bold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-500 hover:shadow-indigo-500/40 transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <span>Começar Agora</span>
                            <ArrowRight class="h-5 w-5" />
                        </Link>
                        <Link
                            :href="route('login')"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-900 border border-zinc-800 px-8 py-3.5 text-base font-bold text-zinc-300 hover:bg-zinc-800 hover:text-white transition-all focus:outline-none"
                        >
                            <LogIn class="h-5 w-5" />
                            <span>Entrar com Link Mágico</span>
                        </Link>
                    </template>
                </div>
            </div>

            <!-- Dashboard Preview UI Mockup -->
            <div class="mt-16 rounded-2xl border border-zinc-800 bg-zinc-900/20 p-4 sm:p-6 backdrop-blur-xl relative shadow-2xl">
                <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-transparent to-transparent z-10"></div>
                <!-- Mockup Header Controls -->
                <div class="flex items-center justify-between pb-4 border-b border-zinc-800/80 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                        <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    </div>
                    <div class="bg-zinc-950 px-6 py-1 rounded-lg border border-zinc-800 text-[10px] text-zinc-500 font-mono">
                        https://gamifica.edu/student/dashboard
                    </div>
                    <div class="w-12"></div>
                </div>

                <!-- Inner Layout Mockup -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">
                    <!-- Column 1: Progress card -->
                    <div class="bg-zinc-900/60 rounded-xl p-5 border border-zinc-800 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-zinc-400 font-bold uppercase tracking-wider">Seu Progresso</span>
                            <Zap class="h-5 w-5 text-indigo-400" />
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="font-semibold text-white">Nível 4 (Mestre Web)</span>
                                <span class="text-indigo-400 font-bold">1.250 XP</span>
                            </div>
                            <div class="w-full bg-zinc-950 rounded-full h-2 border border-zinc-800">
                                <div class="bg-indigo-500 h-2 rounded-full w-[70%]"></div>
                            </div>
                            <p class="text-[11px] text-zinc-500">Falta 250 XP para alcançar o Nível 5!</p>
                        </div>
                    </div>

                    <!-- Column 2: Leaderboard mockup -->
                    <div class="bg-zinc-900/60 rounded-xl p-5 border border-zinc-800 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-zinc-800">
                            <span class="text-xs text-zinc-400 font-bold uppercase tracking-wider">Top Alunos</span>
                            <Trophy class="h-5 w-5 text-yellow-500" />
                        </div>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between items-center bg-yellow-500/10 p-2 rounded-lg border border-yellow-500/20">
                                <span class="font-semibold text-zinc-200">1º Maria Silva</span>
                                <span class="text-yellow-400 font-extrabold">2.450 pts</span>
                            </div>
                            <div class="flex justify-between items-center bg-zinc-950 p-2 rounded-lg">
                                <span class="font-semibold text-zinc-300">2º João Souza</span>
                                <span class="text-zinc-400 font-extrabold">1.820 pts</span>
                            </div>
                            <div class="flex justify-between items-center bg-zinc-950 p-2 rounded-lg">
                                <span class="font-semibold text-zinc-300">3º Aluno Demonstrativo</span>
                                <span class="text-zinc-400 font-extrabold">1.250 pts</span>
                            </div>
                        </div>
                    </div>

                    <!-- Column 3: Subject card -->
                    <div class="bg-indigo-950/20 rounded-xl p-5 border border-indigo-900/30 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-indigo-400 font-bold uppercase tracking-wider">Matéria em Andamento</span>
                            <BookOpen class="h-5 w-5 text-emerald-400" />
                        </div>
                        <div class="space-y-2">
                            <h4 class="font-bold text-white text-sm">Desenvolvimento Web com Laravel</h4>
                            <p class="text-[11px] text-zinc-400">Estude Eloquent ORM, controle de rotas e integração com InertiaJS.</p>
                            <div class="pt-2">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full">
                                    <CheckCircle2 class="h-3 w-3" />
                                    <span>Material Lido (+50 XP)</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-zinc-900">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Feature 1 -->
                <div class="space-y-3 bg-zinc-900/30 p-6 rounded-2xl border border-zinc-800/80">
                    <div class="bg-indigo-500/10 p-3 rounded-xl text-indigo-400 w-fit">
                        <BookOpen class="h-6 w-6" />
                    </div>
                    <h3 class="text-lg font-bold text-white">Trilhas de Aprendizado</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed font-medium">
                        Acesse matérias cadastradas por professores, com descrições, slugs únicos e materiais dinâmicos.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="space-y-3 bg-zinc-900/30 p-6 rounded-2xl border border-zinc-800/80">
                    <div class="bg-emerald-500/10 p-3 rounded-xl text-emerald-400 w-fit">
                        <Zap class="h-6 w-6" />
                    </div>
                    <h3 class="text-lg font-bold text-white">Gamificação Integrada</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed font-medium">
                        Acumule pontos XP ao ler os materiais de estudo e gabaritar as atividades de múltipla escolha.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="space-y-3 bg-zinc-900/30 p-6 rounded-2xl border border-zinc-800/80">
                    <div class="bg-yellow-500/10 p-3 rounded-xl text-yellow-400 w-fit">
                        <Trophy class="h-6 w-6" />
                    </div>
                    <h3 class="text-lg font-bold text-white">Rankings em Tempo Real</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed font-medium">
                        Compare sua pontuação em âmbito global ou filtre para ver a disputa na sua própria instituição.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="space-y-3 bg-zinc-900/30 p-6 rounded-2xl border border-zinc-800/80">
                    <div class="bg-indigo-500/10 p-3 rounded-xl text-indigo-400 w-fit">
                        <Sparkles class="h-6 w-6" />
                    </div>
                    <h3 class="text-lg font-bold text-white">Login Sem Senha</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed font-medium">
                        Esqueça senhas complexas. Acesse a plataforma enviando um Link de Login Mágico direto para o seu e-mail.
                    </p>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="bg-zinc-900/20 border-t border-b border-zinc-900 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div>
                        <p class="text-4xl sm:text-5xl font-black text-white">{{ stats.students }}</p>
                        <p class="text-xs sm:text-sm text-zinc-500 mt-2 font-bold uppercase tracking-wider">Estudantes Ativos</p>
                    </div>
                    <div>
                        <p class="text-4xl sm:text-5xl font-black text-indigo-400">{{ stats.subjects }}</p>
                        <p class="text-xs sm:text-sm text-zinc-500 mt-2 font-bold uppercase tracking-wider">Matérias Oferecidas</p>
                    </div>
                    <div>
                        <p class="text-4xl sm:text-5xl font-black text-emerald-400">{{ stats.xp }}</p>
                        <p class="text-xs sm:text-sm text-zinc-500 mt-2 font-bold uppercase tracking-wider">Pontos XP Conquistados</p>
                    </div>
                    <div>
                        <p class="text-4xl sm:text-5xl font-black text-white">100%</p>
                        <p class="text-xs sm:text-sm text-zinc-500 mt-2 font-bold uppercase tracking-wider">Online e Responsivo</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center text-xs text-zinc-600 font-mono">
            <span>&copy; {{ new Date().getFullYear() }} GamificaEdu. Desenvolvido em Laravel, InertiaJS e VueJS.</span>
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
                class="fixed bottom-0 inset-x-0 z-50 p-4 sm:p-6 bg-zinc-950/90 border-t border-zinc-800 backdrop-blur-xl shadow-2xl"
            >
                <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-zinc-300 font-medium text-center md:text-left max-w-4xl">
                        <span class="text-indigo-400 font-bold block mb-1">Valorizamos sua privacidade!</span>
                        Nós usamos cookies para melhorar sua navegação e personalizar nosso conteúdo. Se você clicar em <strong>"Aceitar Todos"</strong>, nosso sistema de análise e pixel do Google Ads passará a captar dados anonimizados de navegação para fins estatísticos e de marketing.
                    </div>
                    <div class="flex items-center gap-3 w-full md:w-auto justify-center">
                        <button
                            @click="declineCookies"
                            class="flex-1 md:flex-none px-5 py-2.5 rounded-xl border border-zinc-800 text-xs font-bold text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all focus:outline-none"
                        >
                            Recusar
                        </button>
                        <button
                            @click="acceptCookies"
                            class="flex-1 md:flex-none px-5 py-2.5 rounded-xl bg-indigo-600 text-xs font-bold text-white shadow-lg shadow-indigo-600/25 hover:bg-indigo-500 transition-all focus:outline-none"
                        >
                            Aceitar Todos
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
