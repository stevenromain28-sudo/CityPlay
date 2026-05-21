<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { useGameStore } from '@/Stores/game.js';
import gsap from 'gsap';
import axios from 'axios';

const props = defineProps({
    title: String,
});

const page = usePage();
const gameStore = useGameStore();

const activeSession = computed(() => page.props.active_session);
const isDashboard = computed(() => {
    const dashboardUrl = route('player.dashboard');
    const currentUrl = page.url.split('?')[0]; 
    const dashboardPath = new URL(dashboardUrl).pathname;
    return currentUrl === dashboardPath;
});

const dashboardContainer = ref(null);
const showLogoutModal = ref(false);
const showPauseModal = ref(false);
const showTimeUpSessionModal = ref(false);
const showTimeUpLieuModal = ref(false);
const showSettingsModal = ref(false);
const soundEffectsActive = ref(true);
const ambientMusicActive = ref(false);

let heartbeatInterval = null;

watch(() => activeSession.value, (newSession) => {
    if (isDashboard.value || !newSession) {
        gameStore.stopSessionTimer();
        showPauseModal.value = false;
        showTimeUpSessionModal.value = false;
        return;
    }

    gameStore.setSession(newSession);

    if (newSession.statut === 'pause') {
        showPauseModal.value = true;
        showTimeUpSessionModal.value = false;
    } else if (newSession.statut === 'temps_epuise' || gameStore.tempsSessionRestant <= 0) {
        showPauseModal.value = false;
        showTimeUpSessionModal.value = true;
    } else {
        showPauseModal.value = false;
        showTimeUpSessionModal.value = false;
    }
}, { deep: true, immediate: true });

watch(() => isDashboard.value, (onDashboard) => {
    if (onDashboard) {
        gameStore.stopSessionTimer();
    }
});

watch(() => gameStore.tempsSessionRestant, (newSeconds) => {
    if (activeSession.value && !isDashboard.value && newSeconds <= 0 && gameStore.session?.statut === 'actif') {
        showTimeUpSessionModal.value = true;
        showPauseModal.value = false;
        gameStore.stopSessionTimer();

        axios.post(route('player.sessions.status', activeSession.value.id), { action: 'temps_epuise' })
            .then(() => {
                router.reload({ only: ['active_session'] });
            })
            .catch(err => console.error("Erreur d'expiration du temps", err));
    }
});

watch(() => gameStore.lieuDepasse, (isDepasse) => {
    if (isDepasse && !isDashboard.value) {
        showTimeUpLieuModal.value = true;
    }
});

const startHeartbeat = () => {
    if (heartbeatInterval) clearInterval(heartbeatInterval);
    heartbeatInterval = setInterval(async () => {
        if (activeSession.value && gameStore.session?.statut === 'actif') {
            try {
                const response = await axios.post(route('player.sessions.heartbeat', activeSession.value.id));
                
                if (Math.abs(gameStore.tempsSessionRestant - response.data.temps_restant) > 3) {
                    gameStore.syncTempsForce(response.data.temps_restant);
                }
                
                if (response.data.statut === 'temps_epuise') {
                    showTimeUpSessionModal.value = true;
                    gameStore.stopSessionTimer();
                }
            } catch (error) {
                console.error("Heartbeat error", error);
            }
        }
    }, 30000);
};

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
            showTimeUpSessionModal.value = false;
            router.reload({ only: ['active_session'] });
        }
    });
};

const terminerPartie = () => {
    router.post(route('player.sessions.status', activeSession.value.id), { action: 'terminer' }, {
        onSuccess: () => {
            showTimeUpSessionModal.value = false;
            gameStore.stopSessionTimer();
            gameStore.stopLieuTimer();
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

    gsap.to('.bg-slide', {
        xPercent: -20,
        duration: 20,
        repeat: -1,
        yoyo: true,
        ease: "linear"
    });
});

onUnmounted(() => {
    gameStore.stopSessionTimer();
    gameStore.stopLieuTimer();
    if (heartbeatInterval) clearInterval(heartbeatInterval);
});
</script>

<template>
    <Head :title="title" />

    <div ref="dashboardContainer" class="min-h-screen bg-slate-900 text-slate-800 flex overflow-hidden font-sans relative">
        <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
            <div class="bg-slide flex w-[200%] h-full opacity-40 mix-blend-overlay">
                <img src="/images/backgrounds/img1.jpg" class="w-1/2 h-full object-cover">
                <img src="/images/backgrounds/img2.jpg" class="w-1/2 h-full object-cover">
            </div>
        </div>

        <div class="absolute inset-0 pointer-events-none z-50 flex flex-col justify-between p-4 md:p-8">
            <div class="flex justify-between items-start w-full">
                <div class="flex items-center space-x-3">
                    <Link :href="route('player.dashboard')" class="pointer-events-auto w-12 h-12 md:w-16 md:h-16 bg-black/30 backdrop-blur-md border-2 border-white/20 rounded-2xl flex items-center justify-center text-white hover:bg-black/50 hover:scale-110 transition-all shadow-xl group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 group-hover:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 hidden group-hover:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    </Link>
                </div>

                <div class="flex items-center pointer-events-none">
                    <div v-if="activeSession || $page.props.auth.user" 
                         class="pointer-events-auto flex items-center bg-black/40 backdrop-blur-xl border-2 border-white/10 rounded-2xl p-1.5 shadow-2xl transition-all duration-500 hover:border-white/20">
                        
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

                        <div v-if="activeSession && !isDashboard" class="h-10 w-[1px] bg-white/10 mx-1"></div>

                        <div v-if="gameStore.lieuActuel && !isDashboard" class="flex flex-col items-start gap-1 px-3">
                            <span class="text-[8px] font-black uppercase tracking-[0.2em] opacity-50 text-white">Lieu</span>
                            <div class="flex items-center gap-2">
                                <span class="font-black text-sm italic tracking-tighter tabular-nums" 
                                      :class="gameStore.lieuDepasse ? 'text-red-400 animate-pulse' : 'text-amber-400'">
                                    {{ gameStore.formatTempsLieu }}
                                </span>
                            </div>
                        </div>

                        <div v-if="activeSession && !isDashboard" class="h-10 w-[1px] bg-white/10 mx-1"></div>

                        <div v-if="activeSession && !isDashboard" 
                             class="flex items-center gap-3 px-4 py-1 transition-all duration-300"
                             :class="gameStore.tempsSessionRestant < 300 ? 'text-red-500 animate-pulse' : 'text-purple-400'">
                            
                            <button @click="togglePause" class="hover:scale-110 active:scale-95 transition-transform text-white/80 hover:text-white">
                                <svg v-if="gameStore.session?.statut === 'actif'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </button>
                            
                            <div class="flex flex-col items-start leading-none">
                                <span class="text-[8px] md:text-[10px] font-black uppercase tracking-[0.2em] opacity-50 text-white">Session</span>
                                <span class="font-black text-lg md:text-2xl italic tracking-tighter tabular-nums">{{ gameStore.formatTempsSession }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <main class="flex-1 flex flex-col h-screen overflow-hidden z-10 relative">
            <div class="flex-1 overflow-y-auto custom-scrollbar">
                <slot />
            </div>
        </main>

        <div v-if="showTimeUpSessionModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-md"></div>
            <div class="bg-white rounded-2xl p-10 max-w-md w-full relative z-10 text-center shadow-2xl border-4 border-yellow-400/30">
                <div class="w-24 h-24 bg-yellow-100 text-yellow-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="text-4xl font-black italic uppercase text-slate-800 mb-2 tracking-tighter">Temps de Session Épuisé !</h3>
                <p class="text-slate-500 font-bold mb-8 uppercase text-xs tracking-widest">Votre temps de jeu choisi est terminé. Souhaitez-vous continuer ?</p>
                
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

        <div v-if="showTimeUpLieuModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md" @click="showTimeUpLieuModal = false"></div>
            <div class="bg-white rounded-2xl p-10 max-w-md w-full relative z-10 text-center shadow-2xl border-4 border-red-400/30">
                <div class="w-24 h-24 bg-red-100 text-red-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h3 class="text-4xl font-black italic uppercase text-slate-800 mb-2 tracking-tighter">Temps du Lieu Dépassé !</h3>
                <p class="text-slate-500 font-bold mb-8 uppercase text-xs tracking-widest">Vous pouvez continuer, mais vous perdrez des XP !</p>
                
                <button @click="showTimeUpLieuModal = false" class="w-full py-5 bg-[#F59E0B] text-white rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-amber-500/30 hover:scale-105 transition-all">
                    Continuer
                </button>
            </div>
        </div>

        <div v-if="showPauseModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-md"></div>
            <div class="bg-white rounded-2xl p-12 max-w-md w-full relative z-10 text-center shadow-2xl border-4 border-blue-500/30">
                <div class="w-32 h-32 bg-blue-100 text-blue-500 rounded-xl flex items-center justify-center mx-auto mb-8 shadow-inner relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                </div>
                <h3 class="text-5xl font-black italic uppercase text-slate-800 mb-4 tracking-tighter">PAUSE</h3>
                <p class="text-slate-500 font-bold mb-10 uppercase text-xs tracking-[0.2em]">Le temps est suspendu...</p>
                
                <button @click="togglePause" class="group relative w-full overflow-hidden rounded-2xl bg-gradient-to-b from-yellow-300 to-yellow-500 p-[2px] shadow-[0_10px_40px_-10px_rgba(250,204,21,0.6)] hover:scale-105 active:scale-95 transition-transform">
                    <div class="relative w-full rounded-xl bg-gradient-to-b from-yellow-400 to-yellow-600 px-8 py-6 flex items-center justify-center border-t border-yellow-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white mr-4 drop-shadow-md" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        <span class="text-4xl font-black italic uppercase text-white drop-shadow-md tracking-widest">Reprendre la play</span>
                    </div>
                </button>
            </div>
        </div>

        <div v-if="showLogoutModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" @click="cancelLogout"></div>
            <div class="bg-white rounded-2xl p-8 max-w-sm w-full relative z-10 text-center shadow-2xl border-4 border-red-500/20">
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

        <div class="fixed bottom-6 left-6 z-50 pointer-events-auto flex items-center space-x-3">
            <button @click="showSettingsModal = true" class="w-12 h-12 md:w-16 md:h-16 bg-purple-600/80 backdrop-blur-md border-2 border-purple-400/50 rounded-2xl flex items-center justify-center text-white hover:bg-purple-700 hover:scale-110 transition-all shadow-[0_5px_15px_rgba(124,58,237,0.5)]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 md:h-8 md:w-8 animate-[spin_10s_linear_infinite]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>

            <button @click="confirmLogout" class="w-12 h-12 md:w-16 md:h-16 bg-red-500/80 backdrop-blur-md border-2 border-red-400/50 rounded-2xl flex items-center justify-center text-white hover:bg-red-600 hover:scale-110 transition-all shadow-[0_5px_15px_rgba(239,68,68,0.5)]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 md:h-8 md:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </button>
        </div>

        <div v-if="showSettingsModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" @click="showSettingsModal = false"></div>
            <div class="bg-white rounded-2xl p-8 max-w-sm w-full relative z-10 text-center shadow-2xl border-4 border-purple-500/20">
                <div class="w-20 h-20 bg-purple-100 text-purple-600 rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-3xl font-black italic uppercase text-slate-800 mb-6 tracking-tighter">OPTIONS DE JEU</h3>
                
                <div class="space-y-4 mb-8">
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-sm font-bold uppercase text-slate-600">Effets Sonores</span>
                        <button @click="soundEffectsActive = !soundEffectsActive" class="px-4 py-2 font-bold rounded-lg text-xs uppercase tracking-widest transition-all"
                                :class="soundEffectsActive ? 'bg-purple-600 text-white hover:bg-purple-700' : 'bg-slate-200 text-slate-600 hover:bg-slate-300'">
                            {{ soundEffectsActive ? 'Actifs' : 'Désactivés' }}
                        </button>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-sm font-bold uppercase text-slate-600">Musique d'ambiance</span>
                        <button @click="ambientMusicActive = !ambientMusicActive" class="px-4 py-2 font-bold rounded-lg text-xs uppercase tracking-widest transition-all"
                                :class="ambientMusicActive ? 'bg-purple-600 text-white hover:bg-purple-700' : 'bg-slate-200 text-slate-600 hover:bg-slate-300'">
                            {{ ambientMusicActive ? 'Active' : 'Désactivée' }}
                        </button>
                    </div>
                </div>

                <button @click="showSettingsModal = false" class="w-full py-4 bg-slate-100 text-slate-600 rounded-xl font-black uppercase tracking-widest hover:bg-slate-200 transition-all text-xs">
                    Retour au jeu
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;700;90&display=swap');

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
