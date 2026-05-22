<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { Link, Head } from '@inertiajs/vue3';

const props = defineProps({
    session: Object,
    equipe: Object,
});
</script>

<template>
    <PlayerLayout title="Tous les lieux visités">
        <div class="flex flex-col items-center justify-center min-h-screen pt-32 pb-12 px-4">
            <div class="bg-black/40 backdrop-blur-md p-10 rounded-3xl border border-yellow-400/30 shadow-2xl text-center max-w-2xl w-full">
                <div class="text-yellow-400 text-9xl mb-6 animate-bounce">🎉</div>
                <h2 class="text-4xl md:text-5xl font-black italic uppercase tracking-tighter text-white drop-shadow-md mb-4">
                    Félicitations !
                </h2>
                <p class="text-xl md:text-2xl font-bold text-slate-300 mb-8">
                    {{ equipe ? 'Votre équipe a' : 'Vous avez' }} déjà visité TOUS les lieux enregistrés dans cette ville !
                </p>
                <Link :href="route('player.dashboard')" class="inline-block px-10 py-5 bg-gradient-to-b from-yellow-400 to-yellow-600 text-slate-900 rounded-3xl font-black text-xl uppercase tracking-widest shadow-[0_10px_30px_rgba(250,204,21,0.4)] hover:scale-105 active:scale-95 transition-all">
                    Retour au menu principal
                </Link>
            </div>
        </div>
    </PlayerLayout>
</template>

<style scoped>
h2, p {
    font-family: 'Fredoka', sans-serif;
}
</style>
