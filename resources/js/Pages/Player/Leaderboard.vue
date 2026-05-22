<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    top_joueurs: {
        type: Array,
        default: () => []
    },
    top_equipes: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <PlayerLayout title="Classement Mondial">
        <div class="space-y-12 max-w-5xl mx-auto pt-24 md:pt-32 pb-12 px-4 md:px-8">
            <div class="text-center md:text-left mb-12 bg-black/40 backdrop-blur-md p-8 rounded-3xl border border-white/10 shadow-2xl">
                <h2 class="text-4xl md:text-6xl font-black italic uppercase tracking-tighter text-white drop-shadow-md">Le <span class="text-yellow-400">Classement</span></h2>
                <p class="text-slate-300 text-sm md:text-base font-bold uppercase tracking-widest mt-2">Qui sera le plus grand explorateur ?</p>
            </div>

            <!-- Classement ÉQUIPES -->
            <div v-if="top_equipes.length > 0" class="bg-black/40 backdrop-blur-md rounded-[3rem] p-8 border border-yellow-400/30 shadow-2xl">
                <h3 class="text-3xl font-black italic uppercase text-yellow-400 mb-8 flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    Classement des Équipes
                </h3>
                <div class="space-y-4">
                    <div v-for="(equipe, index) in top_equipes" :key="equipe.id" class="p-6 bg-white/10 rounded-2xl border border-white/20 flex items-center justify-between hover:bg-white/15 transition-colors">
                        <div class="flex items-center space-x-6">
                            <span class="text-3xl font-black italic text-yellow-400 w-10">{{ index + 1 }}</span>
                            <div>
                                <h5 class="text-xl font-black italic uppercase text-white">
                                    {{ equipe.nom }}
                                </h5>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-black italic text-yellow-400">{{ equipe.total_score }} XP</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top 3 Podium JOUEURS INDIVIDUELS -->
            <div v-if="top_joueurs.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-10 items-end py-10 px-4">
                <!-- Rank 2 -->
                <div v-if="top_joueurs[1]" class="bg-white/10 backdrop-blur-md p-8 rounded-[3rem] shadow-2xl border border-white/20 flex flex-col items-center relative order-2 md:order-1 h-80 justify-center transition-transform hover:scale-105">
                    <div class="absolute -top-10 w-24 h-24 rounded-3xl bg-slate-200 p-1 rotate-6 shadow-[0_10px_20px_rgba(0,0,0,0.5)] border-2 border-white/50">
                        <img :src="`https://ui-avatars.com/api/?name=${top_joueurs[1].name}&background=94a3b8&color=fff`" class="w-full h-full rounded-2xl object-cover">
                    </div>
                    <div class="mt-8 text-center">
                        <h3 class="text-2xl font-black italic uppercase text-white drop-shadow-sm">{{ top_joueurs[1].name }}</h3>
                        <p class="text-slate-300 font-black text-xl drop-shadow-sm">{{ top_joueurs[1].total_score }} XP</p>
                    </div>
                    <div class="mt-6 w-12 h-12 bg-slate-200 rounded-2xl flex items-center justify-center text-slate-800 font-black text-xl shadow-inner">2</div>
                </div>
                <div v-else class="order-2 md:order-1 h-80"></div>

                <!-- Rank 1 -->
                <div v-if="top_joueurs[0]" class="bg-gradient-to-b from-yellow-500/20 to-yellow-600/40 backdrop-blur-md p-10 rounded-[3rem] shadow-[0_20px_50px_rgba(250,204,21,0.2)] border-2 border-yellow-400/50 flex flex-col items-center relative order-1 md:order-2 h-96 justify-center scale-105 md:scale-110 z-10 transition-transform hover:scale-110">
                    <div class="absolute -top-12 w-32 h-32 rounded-[2.5rem] bg-yellow-400 p-1 -rotate-3 shadow-[0_10px_30px_rgba(250,204,21,0.6)] border-2 border-white">
                        <img :src="`https://ui-avatars.com/api/?name=${top_joueurs[0].name}&background=eab308&color=fff`" class="w-full h-full rounded-[2.2rem] object-cover">
                    </div>
                    <div class="absolute -top-16 text-yellow-300 drop-shadow-[0_0_15px_rgba(250,204,21,0.8)] animate-pulse">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                    </div>
                    <div class="mt-12 text-center">
                        <h3 class="text-3xl font-black italic uppercase text-white drop-shadow-md">{{ top_joueurs[0].name }}</h3>
                        <p class="text-yellow-400 font-black text-2xl drop-shadow-md">{{ top_joueurs[0].total_score }} XP</p>
                    </div>
                    <div class="mt-8 w-16 h-16 bg-yellow-400 rounded-3xl flex items-center justify-center text-slate-900 font-black text-3xl shadow-[0_5px_15px_rgba(250,204,21,0.5)] border border-yellow-200">1</div>
                </div>

                <!-- Rank 3 -->
                <div v-if="top_joueurs[2]" class="bg-white/10 backdrop-blur-md p-8 rounded-[3rem] shadow-2xl border border-white/20 flex flex-col items-center relative order-3 h-72 justify-center transition-transform hover:scale-105">
                    <div class="absolute -top-10 w-20 h-20 rounded-2xl bg-orange-400 p-1 -rotate-6 shadow-[0_10px_20px_rgba(0,0,0,0.5)] border-2 border-white/50">
                        <img :src="`https://ui-avatars.com/api/?name=${top_joueurs[2].name}&background=fb923c&color=fff`" class="w-full h-full rounded-xl object-cover">
                    </div>
                    <div class="mt-6 text-center">
                        <h3 class="text-xl font-black italic uppercase text-white drop-shadow-sm">{{ top_joueurs[2].name }}</h3>
                        <p class="text-orange-300 font-black text-lg drop-shadow-sm">{{ top_joueurs[2].total_score }} XP</p>
                    </div>
                    <div class="mt-4 w-10 h-10 bg-orange-400 rounded-xl flex items-center justify-center text-white font-black text-lg shadow-inner">3</div>
                </div>
                <div v-else class="order-3 h-72"></div>
            </div>

            <!-- Rest of Leaderboard Table (JOUEURS INDIVIDUELS) -->
            <div v-if="top_joueurs.length > 3" class="bg-black/40 backdrop-blur-md rounded-[3rem] shadow-2xl border border-white/10 overflow-hidden mb-12">
                <div class="divide-y divide-white/5">
                    <div v-for="(joueur, index) in top_joueurs.slice(3)" :key="joueur.id" class="p-6 md:p-8 flex items-center justify-between hover:bg-white/5 transition-colors group" :class="{ 'bg-white/5 border-l-4 border-yellow-400': $page.props.auth.user.id === joueur.id }">
                        <div class="flex items-center space-x-6 md:space-x-8">
                            <span class="text-2xl md:text-3xl font-black italic text-slate-500 w-8 group-hover:text-yellow-400 transition-colors">{{ index + 4 }}</span>
                            <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl overflow-hidden bg-slate-800 border border-white/10 shadow-lg">
                                <img :src="`https://ui-avatars.com/api/?name=${joueur.name}&background=random`" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                            </div>
                            <div>
                                <h5 class="text-lg md:text-xl font-black italic uppercase text-white group-hover:text-yellow-100 transition-colors">
                                    {{ joueur.name }}
                                    <span v-if="$page.props.auth.user.id === joueur.id" class="ml-2 text-[10px] bg-yellow-400 text-slate-900 px-2 py-1 rounded-md">MOI</span>
                                </h5>
                                <p class="text-slate-400 text-[10px] md:text-xs font-black uppercase tracking-widest mt-1">Sessions jouées : {{ joueur.sessions_jouees }} • {{ joueur.enigmes_resolues }} Énigmes résolues</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xl md:text-2xl font-black italic text-yellow-400/80 group-hover:text-yellow-400 transition-colors">{{ joueur.total_score }} XP</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-if="top_joueurs.length === 0 && top_equipes.length === 0" class="text-center bg-black/40 backdrop-blur-md p-10 rounded-3xl border border-white/10">
                <p class="text-white font-bold text-xl uppercase tracking-widest">Aucun explorateur dans le classement pour le moment.</p>
            </div>
        </div>
    </PlayerLayout>
</template>

<style scoped>
h2, h3, h5, span, p {
    font-family: 'Fredoka', sans-serif;
}
</style>
