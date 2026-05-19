<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { useGameStore } from '@/Stores/game.js'; // On importe ton store Pinia
import gsap from 'gsap';
import axios from 'axios';

const props = defineProps({
    title: String,
});

const page = usePage();
const gameStore = useGameStore(); // On initialise le store ici

const activeSession = computed(() => page.props.active_session);
const isDashboard = computed(() => {
    const dashboardUrl = route('player.dashboard');
    const currentUrl = page.url.split('?')[0]; 
    const dashboardPath = new URL(dashboardUrl).pathname;
    return currentUrl === dashboardPath;
});

// ON GARDE : Tes états importants pour l'affichage de tes modales
const dashboardContainer = ref(null);
const showLogoutModal = ref(false);
const showPauseModal = ref(false);
const showTimeUpModal = ref(false);

let heartbeatInterval = null;

// ON CENTRALISE : Un seul watcher intelligent pour piloter l'état du jeu et les modales
watch(() => activeSession.value, (newSession) => {
    // Si on est sur le dashboard ou qu'il n'y a pas de session, on coupe le chrono
    if (isDashboard.value || !newSession) {
        gameStore.stopTimer();
        showPauseModal.value = false;
        showTimeUpModal.value = false;
        return;
    }

    // On envoie la session à Pinia qui décide s'il faut lancer ou stopper le chronomètre
    gameStore.setSession(newSession);

    // ON GARDE : Ta logique de détection pour ouvrir les bonnes fenêtres modales
    if (newSession.statut === 'pause') {
        showPauseModal.value = true;
        showTimeUpModal.value = false;
    } else if (newSession.statut === 'temps_epuise' || gameStore.tempsRestant <= 0) {
        showPauseModal.value = false;
        showTimeUpModal.value = true;
    } else {
        showPauseModal.value = false;
        showTimeUpModal.value = false;
    }
}, { deep: true, immediate: true });

// Sécurité supplémentaire si l'utilisateur navigue vers le Dashboard
watch(() => isDashboard.value, (onDashboard) => {
    if (onDashboard) {
        gameStore.stopTimer();
    }
});

// ON GARDE : Ton Heartbeat de synchronisation serveur
const startHeartbeat = () => {
    if (heartbeatInterval) clearInterval(heartbeatInterval);
    heartbeatInterval = setInterval(async () => {
        if (activeSession.value && gameStore.session?.statut === 'actif') {
            try {
                const response = await axios.post(route('player.sessions.heartbeat', activeSession.value.id));
                
                // Si le serveur et le chronomètre local ont plus de 3s de décalage, on recale Pinia
                if (Math.abs(gameStore.tempsRestant - response.data.temps_restant) > 3) {
                    gameStore.syncTempsForce(response.data.temps_restant);
                }
                
                if (response.data.statut === 'temps_epuise') {
                    showTimeUpModal.value = true;
                    gameStore.stopTimer();
                }
            } catch (error) {
                console.error("Heartbeat error", error);
            }
        }
    }, 30000);
};

// ON GARDE : Toutes tes fonctions d'actionnement de l'interface
const togglePause = () => {
    const action = gameStore.session?.statut === 'actif' ? 'pause' : 'reprendre';
    router.post(route('player.sessions.status', activeSession.value.id), { action }, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['active_session'] });
        }
    });
};

const ajouterTemps = (minutes) => {
    router.post(route('player.sessions.add-time', activeSession.value.id), { minutes }, {
        onSuccess: () => {
            showTimeUpModal.value = false;
            router.reload({ only: ['active_session'] });
        }
    });
};

const terminerPartie = () => {
    router.post(route('player.sessions.status', activeSession.value.id), { action: 'terminer' }, {
        onSuccess: () => {
            showTimeUpModal.value = false;
            gameStore.stopTimer(); // On coupe proprement le timer Pinia
            router.visit(route('player.dashboard'));
        }
    });
};

const confirmLogout = () => {
    showLogoutModal.value = true;
};

const cancelLogout = () => {
    showLogoutModal.value = false;
};

onMounted(() => {
    startHeartbeat();

    // ON GARDE : Ton animation fluide d'arrière-plan avec GSAP
    gsap.to('.bg-slide', {
        xPercent: -20,
        duration: 20,
        repeat: -1,
        yoyo: true,
        ease: "linear"
    });
});

onUnmounted(() => {
    gameStore.stopTimer(); // Nettoyage de Pinia à la fermeture du composant
    if (heartbeatInterval) clearInterval(heartbeatInterval);
});
</script>

<template>
    <Head :title="title" />

    <div ref="dashboardContainer" class="min-h-screen bg-slate-900 text-slate-800 flex overflow-hidden font-sans relative">
        <!-- Animated Background Slides -->
        <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
            <div class="bg-slide flex w-[200%] h-full opacity-40 mix-blend-overlay">
                <img src="/images/backgrounds/img1.jpg" class="w-1/2 h-full object-cover">
                <img src="/images/backgrounds/img2.jpg" class="w-1/2 h-full object-cover">
            </div>
        </div>

        <!-- HUD Overlay -->
        <div class="absolute inset-0 pointer-events-none z-50 flex flex-col justify-between p-4 md:p-8">
            <!-- Top HUD -->
            <div class="flex justify-between items-start w-full">
                <!-- Boutons Gauche (Menu + Logout) -->
                <div class="flex items-center space-x-3">
                    <!-- Menu Button -->
                    <Link :href="route('player.dashboard')" class="pointer-events-auto w-12 h-12 md:w-16 md:h-16 bg-black/30 backdrop-blur-md border-2 border-white/20 rounded-2xl flex items-center justify-center text-white hover:bg-black/50 hover:scale-110 transition-all shadow-xl group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 group-hover:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 hidden group-hover:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    </Link>

                    <!-- Logout Button -->
                    <button @click="confirmLogout" class="pointer-events-auto w-12 h-12 md:w-16 md:h-16 bg-red-500/80 backdrop-blur-md border-2 border-red-400/50 rounded-2xl flex items-center justify-center text-white hover:bg-red-600 hover:scale-110 transition-all shadow-[0_5px_15px_rgba(239,68,68,0.5)]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 md:h-8 md:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </div>

                <!-- Right HUD (Unified Stats & Timer) -->
                <div class="flex items-center pointer-events-none">
                    <div v-if="activeSession || $page.props.auth.user" 
                         class="pointer-events-auto flex items-center bg-black/40 backdrop-blur-xl border-2 border-white/10 rounded-[2rem] p-1.5 shadow-2xl transition-all duration-500 hover:border-white/20">
                        
                        <!-- Player Stats -->
                        <div class="flex items-center space-x-3 pl-1 pr-4 py-1">
                            <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-gradient-to-tr from-yellow-400 to-yellow-600 p-0.5 shadow-lg">
                                <img :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user.name}&background=7C3AED&color=fff`" class="w-full h-full rounded-full object-cover" alt="Avatar">
                            </div>
                            <div class="text-right hidden sm:block">
                                <p class="text-white font-black text-xs md:text-sm uppercase italic leading-none drop-shadow-md">{{ $page.props.auth.user.name }}</p>
                                <p class="text-yellow-400 text-[10px] font-black uppercase tracking-widest drop-shadow-md flex items-center justify-end gap-1 mt-0.5">
                                    {{ $page.props.auth.user.score || 0 }} XP
                                </p>
                            </div>
                        </div>

                        <!-- Vertical Divider -->
                        <div v-if="activeSession && !isDashboard" class="h-10 w-[1px] bg-white/10 mx-1"></div>

                        <!-- Global Timer -->
                        <div v-if="activeSession && !isDashboard" 
     class="flex items-center gap-3 px-4 py-1 transition-all duration-300"
     :class="gameStore.tempsRestant < 300 ? 'text-red-500 animate-pulse' : 'text-blue-400'">
    
    <button @click="togglePause" class="hover:scale-110 active:scale-95 transition-transform text-white/80 hover:text-white">
        <!-- Utilisation du statut de session de Pinia -->
        <svg v-if="gameStore.session?.statut === 'actif'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/></svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
    </button>
    
    <div class="flex flex-col items-start leading-none">
        <span class="text-[8px] md:text-[10px] font-black uppercase tracking-[0.2em] opacity-50 text-white">Temps</span>
        <!-- Utilisation directe du Getter de formatage de Pinia (sans paramètres !) -->
        <span class="font-black text-lg md:text-2xl italic tracking-tighter tabular-nums">{{ gameStore.formatTemps }}</span>
    </div>
</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-screen overflow-hidden z-10 relative">
            <div class="flex-1 overflow-y-auto custom-scrollbar">
                <slot />
            </div>
        </main>

        <!-- Time Up Modal -->
        <div v-if="showTimeUpModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-md"></div>
            <div class="bg-white rounded-[3rem] p-10 max-w-md w-full relative z-10 text-center shadow-2xl border-4 border-yellow-400/30">
                <div class="w-24 h-24 bg-yellow-100 text-yellow-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="text-4xl font-black italic uppercase text-slate-800 mb-2 tracking-tighter">Temps Épuisé !</h3>
                <p class="text-slate-500 font-bold mb-8 uppercase text-xs tracking-widest">Votre quête est suspendue. Souhaitez-vous continuer ?</p>
                
                <div class="flex flex-col gap-4">
                    <button @click="ajouterTemps(15)" class="w-full py-5 bg-[#7C3AED] text-white rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-purple-500/30 hover:scale-105 transition-all">
                        Continuer (+15 min)
                    </button>
                    <button @click="terminerPartie" class="w-full py-5 bg-slate-100 text-slate-600 rounded-2xl font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                        Arrêter la partie
                    </button>
                </div>
            </div>
        </div>

        <!-- Pause Modal -->
        <div v-if="showPauseModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-md"></div>
            <div class="bg-white rounded-[3rem] p-12 max-w-md w-full relative z-10 text-center shadow-2xl border-4 border-blue-500/30">
                <div class="w-32 h-32 bg-blue-100 text-blue-500 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-inner relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                </div>
                <h3 class="text-5xl font-black italic uppercase text-slate-800 mb-4 tracking-tighter">PAUSE</h3>
                <p class="text-slate-500 font-bold mb-10 uppercase text-xs tracking-[0.2em]">Le temps est suspendu...</p>
                
                <button @click="togglePause" class="group relative w-full overflow-hidden rounded-[2rem] bg-gradient-to-b from-yellow-300 to-yellow-500 p-[2px] shadow-[0_10px_40px_-10px_rgba(250,204,21,0.6)] hover:scale-105 active:scale-95 transition-transform">
                    <div class="relative w-full rounded-[1.9rem] bg-gradient-to-b from-yellow-400 to-yellow-600 px-8 py-6 flex items-center justify-center border-t border-yellow-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white mr-4 drop-shadow-md" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        <span class="text-4xl font-black italic uppercase text-white drop-shadow-md tracking-widest">Reprendre la play</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Logout Confirmation Modal -->
        <div v-if="showLogoutModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" @click="cancelLogout"></div>
            <div class="bg-white rounded-[2.5rem] p-8 max-w-sm w-full relative z-10 text-center shadow-2xl border-4 border-red-500/20">
                <div class="w-20 h-20 bg-red-100 text-red-500 rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                </div>
                <h3 class="text-3xl font-black italic uppercase text-slate-800 mb-2 tracking-tighter">Déconnexion ?</h3>
                <p class="text-slate-500 font-bold mb-8 uppercase text-xs tracking-widest">Voulez-vous vraiment quitter l'aventure ?</p>
                
                <div class="flex gap-4">
                    <button @click="cancelLogout" class="flex-1 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                        Annuler
                    </button>
                    <Link :href="route('logout')" method="post" as="button" class="flex-1 py-4 bg-red-500 text-white rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-red-500/30 hover:bg-red-600 transition-all">
                        Oui, Quitter
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;700;900&display=swap');

.font-sans {
    font-family: 'Outfit', sans-serif;
}

h1, h2, h3, h4, h5, button, span {
    font-family: 'Bangers', cursive;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #F0F7FF;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #7C3AED33;
    border-radius: 10px;
}
</style>
