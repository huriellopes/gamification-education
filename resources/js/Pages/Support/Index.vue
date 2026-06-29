<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/Button.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { HelpCircle, Mail, MessageSquare, Send } from '@lucide/vue';

const form = useForm({
    subject: '',
    message: '',
});

const submitSupport = () => {
    form.post(route('support.send'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Suporte Técnico" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-zinc-100">
                Suporte Técnico & Atendimento
            </h2>
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100 max-w-4xl mx-auto space-y-8">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md space-y-4">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <HelpCircle class="h-5 w-5 text-indigo-400" />
                    Como podemos ajudar?
                </h3>
                <p class="text-sm text-zinc-400">
                    Se você encontrou algum problema técnico, tem dúvidas sobre o funcionamento do ranking, ou precisa de suporte para gerenciar sua conta, utilize um dos canais oficiais abaixo.
                </p>

                <!-- WhatsApp & Email Direct Links -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <!-- WhatsApp -->
                    <a
                        href="https://wa.me/5511999999999?text=Olá! Estou na plataforma GamificaEdu e preciso de suporte técnico."
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center justify-center gap-3 px-5 py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-lg transition-all active:scale-98"
                        title="Falar via WhatsApp"
                    >
                        <MessageSquare class="h-5 w-5" />
                        <span class="hidden md:inline">Falar via WhatsApp</span>
                    </a>

                    <!-- Email -->
                    <a
                        href="mailto:suporte@gamificaedu.com.br?subject=Suporte Técnico GamificaEdu"
                        class="flex items-center justify-center gap-3 px-5 py-4 rounded-2xl bg-zinc-800 hover:bg-zinc-700 text-white font-bold text-sm shadow-md border border-zinc-700/50 transition-all active:scale-98"
                        title="Enviar E-mail Direto"
                    >
                        <Mail class="h-5 w-5 text-zinc-450" />
                        <span class="hidden md:inline">Enviar E-mail Direto</span>
                    </a>
                </div>
            </div>

            <!-- Form para Chamado Direct -->
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md space-y-6">
                <div>
                    <h3 class="text-lg font-bold text-white">Abrir um Chamado</h3>
                    <p class="text-xs text-zinc-400">Envie uma mensagem direta detalhando seu problema e o Super Admin retornará o contato o mais breve possível.</p>
                </div>

                <form @submit.prevent="submitSupport" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Assunto / Tópico</label>
                        <input
                            v-model="form.subject"
                            type="text"
                            required
                            placeholder="Ex: Erro ao iniciar matéria, Problema com pontos XP..."
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-zinc-200 placeholder-zinc-650 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Mensagem / Detalhes</label>
                        <textarea
                            v-model="form.message"
                            required
                            rows="5"
                            placeholder="Descreva com detalhes o que está acontecendo. Se possível, informe passos para reproduzir o problema."
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-zinc-200 placeholder-zinc-650 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        ></textarea>
                    </div>

                    <div class="flex justify-end pt-2">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-indigo-600 hover:bg-indigo-500 font-bold flex items-center gap-2"
                            title="Enviar Chamado"
                        >
                            <Send class="h-4 w-4" />
                            <span class="hidden md:inline">{{ form.processing ? 'Enviando chamado...' : 'Enviar Chamado' }}</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
