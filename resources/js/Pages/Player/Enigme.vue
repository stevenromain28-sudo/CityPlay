<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    lieu: Object,
    enigmes: Array
});

const jouerEnigme = (enigme) => {
    if (!props.lieu || !props.lieu.ville_id) {
        console.error("Erreur: Ville non définie pour ce lieu.");
        return;
    }
    
    // On cherche une session active pour cette ville
    router.post(route('player.sessions.store'), {
        ville_id: props.lieu.ville_id,
        mode: 'cooperatif'
    });
};
</script>

<template>
    <PlayerLayout :title="lieu ? 'Énigmes - ' + lieu.nom : 'Mes Énigmes'">
        <div class="space-y-10">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6">
                <div v-if="lieu">
                    <h2 class="text-4xl md:text-5xl font-black italic uppercase tracking-tighter text-slate-800">Énigmes : <span class="text-[#7C3AED]">{{ lieu.nom }}</span></h2>
                    <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mt-2">Défiez votre esprit et gagnez des points</p>
                </div>
                <div v-else>
                    <h2 class="text-4xl md:text-5xl font-black italic uppercase tracking-tighter text-slate-800">Mes <span class="text-[#7C3AED]">Énigmes</span></h2>
                    <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mt-2">Sélectionnez un lieu pour voir les énigmes</p>
                </div>
                <div class="flex bg-white p-2 rounded-2xl shadow-sm border border-blue-50">
                    <button class="px-6 py-3 bg-[#7C3AED] text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg">Toutes</button>
                    <button class="px-6 py-3 text-slate-400 rounded-xl text-xs font-black uppercase tracking-widest hover:text-[#7C3AED]">Résolues</button>
                </div>
            </div>

            <div v-if="enigmes && enigmes.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div v-for="enigme in enigmes" :key="enigme.id" 
                     @click="jouerEnigme(enigme)"
                     class="bg-white p-10 rounded-[3rem] shadow-xl shadow-blue-50 border border-blue-50 group hover:scale-[1.02] transition-all relative overflow-hidden cursor-pointer">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-400/10 rounded-bl-[5rem] -mr-10 -mt-10 group-hover:bg-yellow-400/20 transition-colors"></div>
                    
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-[#7C3AED]/10 text-[#7C3AED] rounded-2xl flex items-center justify-center mb-8">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" /></svg>
                        </div>
                        
                        <h3 class="text-3xl font-black italic uppercase text-slate-800 mb-4 group-hover:text-[#7C3AED] transition-colors">{{ enigme.titre }}</h3>
                        <p class="text-slate-400 text-sm font-bold leading-relaxed mb-8">{{ enigme.contenu }}</p>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <span class="px-4 py-2 bg-yellow-400 text-white text-[10px] font-black uppercase rounded-xl tracking-widest">+{{ enigme.niveau * 100 }} XP</span>
                                <span class="text-slate-300 font-bold text-xs uppercase tracking-widest">Niveau: {{ enigme.niveau }}</span>
                            </div>
                            <button class="w-12 h-12 bg-blue-50 text-[#7C3AED] rounded-xl flex items-center justify-center hover:bg-[#7C3AED] hover:text-white transition-all shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="py-20 text-center bg-white rounded-[3rem] shadow-xl border border-blue-50">
                <p class="text-slate-400 text-lg font-bold uppercase tracking-widest">Aucune énigme trouvée pour ce lieu.</p>
            </div>
        </div>
    </PlayerLayout>
</template>

<style scoped>
h2, h3, button, span {
    font-family: 'Fredoka', sans-serif;
}
</style>
