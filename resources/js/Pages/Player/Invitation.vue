<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    session: Object, // Peut être null si pas de session active
});

const copiedMode = ref(null);

const copyLink = (mode) => {
    // Dans une implémentation réelle, on génèrerait un lien unique lié à la session
    const link = `${window.location.origin}/join?mode=${mode}&session=${props.session?.id || 'new'}`;
    navigator.clipboard.writeText(link);
    copiedMode.value = mode;
    setTimeout(() => { copiedMode.value = null; }, 2000);
};
</script>

<template>
    <PlayerLayout title="Inviter des joueurs">
        <div class="max-w-4xl mx-auto pt-28 pb-10 px-4 md:px-0">
            <div class="text-center mb-10">
                <h2 class="text-4xl md:text-6xl font-black italic uppercase tracking-tighter text-white drop-shadow-md">Recruter votre <span class="text-yellow-400">Équipe</span></h2>
                <p class="text-slate-300 text-sm md:text-base font-bold uppercase tracking-widest mt-2">Choisissez comment vous voulez jouer</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Mode Ami (Coopératif) -->
                <div class="bg-white/10 backdrop-blur-md p-8 rounded-[3rem] border-2 border-green-400/50 shadow-[0_10px_30px_rgba(74,222,128,0.2)] relative overflow-hidden group hover:scale-[1.02] transition-transform">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-green-500/20 rounded-full blur-3xl group-hover:bg-green-500/30 transition-colors"></div>
                    
                    <div class="w-16 h-16 bg-green-400 rounded-2xl flex items-center justify-center shadow-lg mb-6 text-white rotate-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>

                    <h3 class="text-3xl font-black italic uppercase text-white mb-2 drop-shadow-sm">Mode Allié (Co-op)</h3>
                    <p class="text-slate-300 text-sm font-bold leading-relaxed mb-8">
                        Invitez vos amis à rejoindre votre groupe. Vous partagerez la même progression, les mêmes indices, et une seule victoire commune pour le collectif !
                    </p>

                    <button @click="copyLink('coop')" class="w-full py-5 bg-gradient-to-b from-green-400 to-green-600 text-slate-900 rounded-2xl font-black uppercase tracking-widest shadow-xl hover:shadow-green-500/50 active:scale-95 transition-all flex items-center justify-center gap-3">
                        <svg v-if="copiedMode !== 'coop'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        {{ copiedMode === 'coop' ? 'Lien Copié !' : 'Copier le lien Allié' }}
                    </button>
                </div>

                <!-- Mode Mercenaire (Compétitif) -->
                <div class="bg-white/10 backdrop-blur-md p-8 rounded-[3rem] border-2 border-red-400/50 shadow-[0_10px_30px_rgba(248,113,113,0.2)] relative overflow-hidden group hover:scale-[1.02] transition-transform">
                    <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-red-500/20 rounded-full blur-3xl group-hover:bg-red-500/30 transition-colors"></div>
                    
                    <div class="w-16 h-16 bg-red-400 rounded-2xl flex items-center justify-center shadow-lg mb-6 text-white -rotate-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>

                    <h3 class="text-3xl font-black italic uppercase text-white mb-2 drop-shadow-sm">Mode Mercenaire</h3>
                    <p class="text-slate-300 text-sm font-bold leading-relaxed mb-8">
                        Défiez d'autres joueurs en temps réel ! Chaque mercenaire joue pour lui-même. Débloquez les niveaux plus vite que les autres pour prendre la tête du classement.
                    </p>

                    <button @click="copyLink('competitif')" class="w-full py-5 bg-gradient-to-b from-red-400 to-red-600 text-white rounded-2xl font-black uppercase tracking-widest shadow-xl hover:shadow-red-500/50 active:scale-95 transition-all flex items-center justify-center gap-3">
                        <svg v-if="copiedMode !== 'competitif'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        {{ copiedMode === 'competitif' ? 'Lien Copié !' : 'Copier le lien Mercenaire' }}
                    </button>
                </div>
            </div>
            
            <div class="mt-12 text-center">
                <Link :href="route('player.dashboard')" class="inline-block py-3 px-8 bg-white/10 hover:bg-white/20 text-white border border-white/30 rounded-2xl font-black uppercase tracking-widest transition-colors">
                    Retour au menu
                </Link>
            </div>
        </div>
    </PlayerLayout>
</template>

<style scoped>
h2, h3, button { font-family: 'Fredoka', sans-serif; }
</style>
