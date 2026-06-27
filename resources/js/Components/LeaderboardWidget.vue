<script setup>
defineProps({
    users: {
        type: Array,
        required: true,
        default: () => []
    }
});
</script>

<template>
    <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 backdrop-blur-xl">
        <h3 class="flex items-center gap-2 text-lg font-bold text-white mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5 text-yellow-500">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-5.25a1.125 1.125 0 0 0-1.125 1.125v3.375m9 0h-9M12 2.25a2.25 2.25 0 0 0-2.25 2.25c0 1.18.91 2.164 2.09 2.245a.75.75 0 0 1 .66.745v3.375h2.25V7.49a.75.75 0 0 1 .66-.745 2.25 2.25 0 0 0 2.09-2.245A2.25 2.25 0 0 0 12 2.25Z" />
            </svg>
            Top Competidores
        </h3>

        <div v-if="users.length === 0" class="text-center py-6 text-sm text-zinc-500">
            Nenhum competidor registrado ainda.
        </div>

        <ul v-else class="divide-y divide-zinc-800/50">
            <li v-for="user in users" :key="user.position" class="flex items-center justify-between py-3 transition-colors duration-150 hover:bg-zinc-800/20 px-2 rounded-lg">
                <div class="flex items-center gap-3">
                    <!-- Medálhas ou Posição -->
                    <div class="flex h-7 w-7 items-center justify-center rounded-full font-bold text-xs">
                        <span v-if="user.position === 1" class="flex h-6 w-6 items-center justify-center rounded-full bg-yellow-500/10 text-yellow-400 border border-yellow-500/30 shadow-[0_0_10px_rgba(234,179,8,0.1)]">🥇</span>
                        <span v-else-if="user.position === 2" class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-400/10 text-zinc-300 border border-zinc-400/30">🥈</span>
                        <span v-else-if="user.position === 3" class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-700/10 text-amber-500 border border-amber-700/30">🥉</span>
                        <span v-else class="text-zinc-500 font-medium">#{{ user.position }}</span>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-zinc-200">{{ user.name }}</div>
                        <div class="text-[10px] text-zinc-500">{{ user.institution }}</div>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-sm font-bold text-yellow-400">{{ user.points.toLocaleString() }}</span>
                    <span class="text-[9px] text-zinc-500 block uppercase font-semibold">pts</span>
                </div>
            </li>
        </ul>
    </div>
</template>
