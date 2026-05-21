<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, computed, ref } from 'vue';
import gsap from 'gsap';
import { useGameStore } from '@/Stores/game';

const props = defineProps({
    ville: Object,
    lieu: Object,
    enigmes: Array,
    deja_complete: {
        type: Boolean,
        default: false
    },
    equipe: Object,
});

const page = usePage();
const activeSession = computed(() => page.props.active_session);
const mainEnigmes = computed(() => props.enigmes.filter(e => !e.is_bonus));
const gameStore = useGameStore();

const scrollToChallenges = () => {
    const el = document.getElementById('challenges-section');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
};

const jouerEnigme = (enigme) => {
    console.log('=== jouerEnigme ===');
    console.log('activeSession.value:', activeSession.value);
    console.log('enigme:', enigme);
    
    // Démarrer le timer du lieu
    gameStore.entrerDansLieu(props.lieu);
    
    const urlParams = new URLSearchParams(window.location.search);
    const lat = urlParams.get('lat');
    const lng = urlParams.get('lng');

    // Si une session existe déjà : utiliser notre endpoint pour définir l'énigme
    if (activeSession.value) {
        console.log('→ Session existante, définir énigme:', enigme.id);
        let url = route('player.game.choisir-enigme', {
            session: activeSession.value.id,
            enigme: enigme.id
        });
        if (lat && lng) {
            url += `?lat=${lat}&lng=${lng}`;
        }
        console.log('→ URL:', url);
        window.location.href = url;
        return;
    }

    // Sinon : créer une nouvelle session avec l'énigme_id
    console.log('→ Créer nouvelle session avec énigme:', enigme.id);
    router.post(route('player.game.auto-start'), {
        ville_id: props.ville.id,
        lat: lat,
        lng: lng,
        duree: 45,
        enigme_id: enigme.id
    });
};

onMounted(() => {
    gsap.from('.rpg-medallion', { scale: 0.8, opacity: 0, stagger: 0.15, duration: 0.6, ease: 'back.out(1.5)' });
    gsap.from('.parchment-scroll-violet', { y: 30, opacity: 0, duration: 0.8, delay: 0.3, ease: 'power2.out' });
});
</script>

<template>
    <PlayerLayout :title="deja_complete ? lieu.nom : 'Lieu Mystère'">
        <div class="space-y-8 max-w-5xl mx-auto pt-32 pb-16 px-4 md:px-0">
            
            <!-- RPG Medallions / Stats Grid (Maintenant au sommet de la vue) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Difficulté Medallion -->
                <div class="rpg-medallion p-6 flex flex-col items-center text-center relative overflow-hidden group">
                    <div class="absolute -right-6 -bottom-6 w-16 h-16 bg-purple-500/10 rounded-full"></div>
                    <span class="text-[9px] font-black uppercase tracking-[0.25em] text-purple-400 mb-3">DIFFICULTÉ DU LIEU</span>
                    <div class="flex space-x-2">
                        <div v-for="i in 3" :key="i" 
                             class="w-10 h-10 rounded-lg flex items-center justify-center font-black text-xl shadow-md transition-all duration-300 border-2"
                             :class="i <= lieu.difficulte 
                                ? 'bg-gradient-to-b from-purple-400 to-purple-600 border-yellow-400 text-white scale-110 shadow-purple-500/30' 
                                : 'bg-slate-800/80 border-slate-700 text-slate-500'">
                            {{ i }}
                        </div>
                    </div>
                </div>

                <!-- Énigmes Medallion -->
                <div class="rpg-medallion p-6 flex flex-col items-center text-center relative overflow-hidden group">
                    <div class="absolute -right-6 -bottom-6 w-16 h-16 bg-purple-500/10 rounded-full"></div>
                    <span class="text-[9px] font-black uppercase tracking-[0.25em] text-purple-400 mb-2">NOMBRE DE DÉFIS</span>
                    <p class="text-3xl font-black italic text-yellow-400 tracking-tighter uppercase mt-1">
                        {{ lieu.enigmes_count }} Énigme{{ lieu.enigmes_count > 1 ? 's' : '' }}
                    </p>
                </div>

                <!-- Temps Estimé Medallion -->
                <div class="rpg-medallion p-6 flex flex-col items-center text-center relative overflow-hidden group">
                    <div class="absolute -right-6 -bottom-6 w-16 h-16 bg-purple-500/10 rounded-full"></div>
                    <span class="text-[9px] font-black uppercase tracking-[0.25em] text-purple-400 mb-2">DURÉE ESTIMÉE</span>
                    <p class="text-3xl font-black italic text-purple-400 tracking-tighter uppercase mt-1">
                        {{ lieu.duree_estimee }} Minutes
                    </p>
                </div>
            </div>

            <!-- Grand Parchemin Ancien -->
            <div class="parchment-scroll-violet p-8 md:p-12">
                <div class="relative z-10 space-y-8">
                    <!-- Title with gold ribbons -->
                    <div class="text-center relative">
                        <span v-if="deja_complete" class="px-4 py-1.5 bg-green-500 text-white text-[9px] font-black uppercase rounded border border-green-400 shadow-md inline-block mb-3 animate-pulse">
                            ✓ LIEU DÉJÀ COMPLÉTÉ
                        </span>
                        <span v-else class="px-4 py-1.5 bg-yellow-400 text-white text-[9px] font-black uppercase rounded border border-yellow-300 shadow-md inline-block mb-3">
                            QUÊTE INCONNUE
                        </span>
                        
                        <h3 class="text-3xl md:text-5xl font-black italic uppercase text-purple-950 tracking-tight leading-none">
                            {{ deja_complete ? lieu.nom : 'LIEU MYSTÈRE' }}
                        </h3>
                        <div class="w-32 h-1 bg-yellow-500 mx-auto mt-4 rounded-full"></div>
                    </div>
                    
                    <p class="text-slate-800 text-lg md:text-xl font-bold leading-relaxed italic text-center px-4">
                        " {{ lieu.description }} "
                    </p>

                    <!-- Liste des Énigmes / Avis de Recherche -->
                    <div id="challenges-section" class="scroll-mt-32 pt-4" v-if="!deja_complete">
                        <h4 class="text-center text-xs font-black italic uppercase text-slate-400 tracking-[0.3em] mb-8">
                            — DÉFIS DISPONIBLES —
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="enigme in mainEnigmes" :key="enigme.id" 
                                 @click="jouerEnigme(enigme)"
                                 class="enigma-scroll-card p-6 md:p-8 flex flex-col justify-between min-h-[260px] group">
                                
                                <!-- Decorative star seal -->
                                <div class="absolute top-2 right-2 w-10 h-10 border-2 border-yellow-500/30 rounded-full flex items-center justify-center text-yellow-600/30 text-xs font-black select-none pointer-events-none">
                                    ★
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="flex justify-between items-start">
                                        <span class="px-3 py-1.5 bg-purple-600 text-white text-[9px] font-black uppercase rounded border border-purple-400 shadow-md">
                                            NIVEAU {{ enigme.niveau }}
                                        </span>
                                        <div class="w-8 h-8 rounded-lg bg-yellow-500 text-white flex items-center justify-center shadow-md transform group-hover:scale-110 group-hover:bg-purple-600 transition-all border border-yellow-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M9 5l7 7-7 7" /></svg>
                                        </div>
                                    </div>
                                    
                                    <h5 class="text-2xl font-black italic uppercase text-purple-950 leading-tight group-hover:text-purple-700 transition-colors">
                                        {{ enigme.titre }}
                                    </h5>
                                    
                                    <p class="text-slate-700 text-sm font-bold leading-relaxed line-clamp-3 italic">
                                        "{{ enigme.contenu }}"
                                    </p>
                                </div>

                                <div class="pt-4 mt-6 border-t-2 border-slate-200/50 flex items-center justify-between text-xs">
                                    <span class="text-slate-400 font-black uppercase tracking-wider">
                                        Gain
                                    </span>
                                    <span class="text-purple-700 font-black text-lg italic tracking-tight">
                                        +{{ enigme.niveau * 1000 }} XP
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Completed display -->
                    <div id="challenges-section" class="scroll-mt-32 text-center py-10" v-else>
                        <div class="w-20 h-20 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-green-300 shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <h4 class="text-2xl font-black italic uppercase text-green-700 tracking-wider">
                            FÉLICITATIONS, TOUS LES DÉFIS ONT ÉTÉ RÉSOLUS !
                        </h4>
                    </div>

                    <!-- RPG Button Start (Placé tout au bas du parchemin) -->
                    <div class="flex justify-center pt-8 border-t-2 border-slate-200/50">
                        <button v-if="deja_complete" 
                                class="w-full sm:w-auto px-10 py-5 bg-slate-700/80 text-slate-400 rounded-xl font-black text-xl uppercase tracking-wider border-2 border-slate-600 cursor-not-allowed flex items-center justify-center gap-3"
                                disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                            Lieu déjà complété
                        </button>
                        <button v-else-if="mainEnigmes.length > 0" 
                                @click="mainEnigmes.length === 1 ? jouerEnigme(mainEnigmes[0]) : scrollToChallenges()"
                                class="w-full sm:w-auto px-12 py-6 rpg-btn-yellow text-white rounded-xl font-black text-2xl uppercase tracking-widest flex items-center justify-center gap-4 shadow-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ mainEnigmes.length === 1 ? 'Commencer l\'Aventure' : 'Lancer le Défi' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </PlayerLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;700;900&display=swap');

h2, h3, h4, h5, button, span {
    font-family: 'Bangers', cursive;
}

/* Parchment Scroll effect */
.parchment-scroll-violet {
    background: linear-gradient(135deg, #fffbf2 0%, #f7ebd3 100%);
    border: 6px double #7c3aed; /* Violet */
    outline: 3px solid #fbbf24; /* Or */
    box-shadow: 
        0 20px 40px rgba(0,0,0,0.5), 
        inset 0 0 80px rgba(139, 94, 26, 0.25),
        0 0 25px rgba(124, 58, 237, 0.25); /* Violet glow */
    border-radius: 12px;
    position: relative;
}

/* Golden Rivets at the corners */
.parchment-scroll-violet::before, .parchment-scroll-violet::after {
    content: '';
    position: absolute;
    width: 14px;
    height: 14px;
    background: radial-gradient(circle, #fef08a 0%, #ca8a04 100%);
    border: 2px solid #78350f;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.4);
    z-index: 10;
}
.parchment-scroll-violet::before { top: 12px; left: 12px; }
.parchment-scroll-violet::after { top: 12px; right: 12px; }

/* 3D RPG Violet & Or Buttons */
.rpg-btn-violet {
    background: linear-gradient(to bottom, #a855f7 0%, #7c3aed 100%);
    border-top: 3px solid #f3e8ff;
    border-bottom: 6px solid #4c1d95; /* Deep purple base shadow */
    border-left: 3px solid #6b21a8;
    border-right: 3px solid #6b21a8;
    text-shadow: 2px 2px 0px #4c1d95;
    box-shadow: 0 8px 16px rgba(0,0,0,0.4), inset 0 2px 4px rgba(255,255,255,0.4);
    transition: all 0.1s ease;
}
.rpg-btn-violet:hover {
    filter: brightness(1.1);
    transform: scale(1.02);
}
.rpg-btn-violet:active {
    border-bottom-width: 2px;
    transform: translateY(4px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.4), inset 0 2px 4px rgba(255,255,255,0.4);
}

.rpg-btn-yellow {
    background: linear-gradient(to bottom, #fbbf24 0%, #d97706 100%);
    border-top: 3px solid #fef3c7;
    border-bottom: 6px solid #78350f; /* Deep gold/amber base shadow */
    border-left: 3px solid #b45309;
    border-right: 3px solid #b45309;
    text-shadow: 2px 2px 0px #78350f;
    box-shadow: 0 8px 16px rgba(0,0,0,0.4), inset 0 2px 4px rgba(255,255,255,0.4);
    transition: all 0.1s ease;
}
.rpg-btn-yellow:hover {
    filter: brightness(1.1);
    transform: scale(1.02);
}
.rpg-btn-yellow:active {
    border-bottom-width: 2px;
    transform: translateY(4px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.4), inset 0 2px 4px rgba(255,255,255,0.4);
}

/* RPG Medallion / Stat badges */
.rpg-medallion {
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(12px);
    border: 4px solid #7c3aed; /* Violet border */
    outline: 2px solid #fbbf24; /* Golden highlight */
    box-shadow: 0 12px 30px rgba(0,0,0,0.6), 0 0 15px rgba(124, 58, 237, 0.4);
    border-radius: 16px;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}
.rpg-medallion:hover {
    border-color: #a855f7;
    box-shadow: 0 20px 40px rgba(0,0,0,0.7), 0 0 25px rgba(168, 85, 247, 0.6);
    transform: translateY(-4px) scale(1.02);
}

/* Enigma Mini-scroll card */
.enigma-scroll-card {
    background: linear-gradient(135deg, #fffef9 0%, #f4ebd6 100%);
    border: 3px solid #7c3aed;
    box-shadow: 0 10px 25px rgba(0,0,0,0.4), inset 0 0 40px rgba(139, 94, 26, 0.15);
    border-radius: 12px;
    position: relative;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}
.enigma-scroll-card:hover {
    border-color: #fbbf24;
    box-shadow: 0 15px 35px rgba(124, 58, 237, 0.35), inset 0 0 40px rgba(139, 94, 26, 0.1);
    transform: translateY(-6px) scale(1.02);
}
</style>
