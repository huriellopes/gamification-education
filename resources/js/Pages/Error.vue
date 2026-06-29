<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    status: Number,
});

const title = computed(() => {
    return (
        {
            503: '503: Serviço Indisponível',
            500: '500: Erro no Servidor',
            404: '404: Página não encontrada',
            403: '403: Acesso Negado',
            401: '401: Não Autorizado',
            419: '419: Página Expirada',
        }[props.status] || 'Erro Desconhecido'
    );
});

const description = computed(() => {
    return (
        {
            503: 'Desculpe, estamos em manutenção no momento. Volte em breve.',
            500: 'Ops, algo deu errado em nossos servidores. Nossa equipe já foi notificada.',
            404: 'Desculpe, a página que você está procurando não existe ou foi movida.',
            403: 'Desculpe, você não tem permissão para acessar esta página.',
            401: 'Desculpe, você precisa estar autenticado para acessar esta página.',
            419: 'Sua sessão expirou. Por favor, atualize a página e tente novamente.',
        }[props.status] ||
        'Um erro inesperado ocorreu. Por favor, tente novamente.'
    );
});

const getGradientClass = computed(() => {
    if (props.status === 404 || props.status === 419)
        return 'from-blue-500 to-cyan-500';
    if (props.status === 403 || props.status === 401)
        return 'from-orange-500 to-amber-500';
    return 'from-red-500 to-rose-500';
});

const goBack = () => {
    if (typeof window !== 'undefined') {
        window.history.back();
    }
};
</script>

<template>
    <Head :title="title" />

    <div
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gray-100 p-4 transition-colors duration-300 sm:p-6 lg:p-8 dark:bg-gray-900"
    >
        <!-- Background Effects -->
        <div class="pointer-events-none absolute inset-0">
            <div
                class="animate-blob absolute left-1/4 top-1/4 h-96 w-96 rounded-full bg-indigo-500/10 mix-blend-multiply blur-3xl dark:bg-indigo-500/20 dark:mix-blend-lighten"
            ></div>
            <div
                class="animate-blob animation-delay-2000 absolute right-1/4 top-1/3 h-96 w-96 rounded-full bg-purple-500/10 mix-blend-multiply blur-3xl dark:bg-purple-500/20 dark:mix-blend-lighten"
            ></div>
            <div
                class="animate-blob animation-delay-4000 absolute bottom-1/4 left-1/3 h-96 w-96 rounded-full bg-pink-500/10 mix-blend-multiply blur-3xl dark:bg-pink-500/20 dark:mix-blend-lighten"
            ></div>
        </div>

        <div class="w-full max-w-xl">
            <div
                class="relative z-10 overflow-hidden rounded-3xl border border-gray-200/50 bg-white/80 shadow-2xl backdrop-blur-xl dark:border-gray-700/50 dark:bg-gray-800/80"
            >
                <div class="p-8 text-center sm:p-12">
                    <!-- Status Code / Icon -->
                    <div class="relative mb-8 inline-block">
                        <div
                            class="select-none bg-gradient-to-br bg-clip-text text-[8rem] font-black leading-none text-transparent opacity-20 sm:text-[10rem] dark:opacity-30"
                            :class="getGradientClass"
                        >
                            {{ status }}
                        </div>
                        <div
                            class="absolute inset-0 flex items-center justify-center"
                        >
                            <div
                                class="relative flex h-24 w-24 items-center justify-center rounded-full border border-gray-100 bg-white shadow-lg sm:h-32 sm:w-32 dark:border-gray-700 dark:bg-gray-800"
                            >
                                <div
                                    class="absolute inset-0 rounded-full bg-gradient-to-br opacity-10"
                                    :class="getGradientClass"
                                ></div>

                                <!-- 404 / 419 Search Icon -->
                                <svg
                                    v-if="status === 404 || status === 419"
                                    class="h-12 w-12 text-blue-500 sm:h-16 sm:w-16"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    />
                                </svg>

                                <!-- 403 / 401 Lock Icon -->
                                <svg
                                    v-else-if="status === 403 || status === 401"
                                    class="h-12 w-12 text-orange-500 sm:h-16 sm:w-16"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                    />
                                </svg>

                                <!-- 500 / 503 Server Icon -->
                                <svg
                                    v-else
                                    class="h-12 w-12 text-red-500 sm:h-16 sm:w-16"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <h1
                        class="mb-4 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white"
                    >
                        {{ title.split(': ')[1] || title }}
                    </h1>

                    <p
                        class="mx-auto mb-8 max-w-md text-lg leading-relaxed text-gray-600 dark:text-gray-400"
                    >
                        {{ description }}
                    </p>

                    <div
                        class="flex flex-col items-center justify-center gap-4 sm:flex-row"
                    >
                        <button
                            @click="goBack"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-3 font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:ring-2 focus:ring-gray-200 focus:ring-offset-2 sm:w-auto dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:focus:ring-offset-gray-900"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"
                                />
                            </svg>
                            Voltar
                        </button>

                        <Link
                            href="/"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r px-6 py-3 font-medium text-white shadow-lg transition-all hover:shadow-xl focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto dark:focus:ring-offset-gray-900"
                            :class="getGradientClass"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                />
                            </svg>
                            Página Inicial
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}
.animate-blob {
    animation: blob 7s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
