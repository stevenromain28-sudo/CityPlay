<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import gsap from 'gsap';
import PlayerLayout from '@/Layouts/PlayerLayout.vue';

const props = defineProps({
    stats: Object,
    recent_sessions: Array,
    villes_disponibles: Array,
    ville_detectee: Object,
    localisation_requise: Boolean,
    equipe: Object,
    lien_invitation: String,
    invitation: Object,
    active_session: Object
});

const detectant = ref(false);
const messageErreur = ref(null);
const copieReussi = ref(false);
const creationInvitation = ref(false);
const showDurationModal = ref(false);
const duration = ref(45);
const durationError = ref('');
const currentStep = ref(1); // 1: choix temps, 2: choix déplacement
const selectedTransport = ref(null);

onMounted(() => {
    if (props.localisation_requise) {
        obtenirLocalisation();
    } else {
        animerMenu();
    }
});

watch(duration, (newVal) => {
    if (newVal >= 45) {
        durationError.value = '';
    }
});

const animerMenu = () => {
    // Évite les conflits d'animations en tuant les tweens existants
    gsap.killTweensOf('.game-title');
    gsap.killTweensOf('.parchment-scroll-violet');
    gsap.killTweensOf('.rpg-btn-game');

    const tl = gsap.timeline({ defaults: { ease: 'back.out(1.7)' } });
    
    tl.fromTo('.game-title', 
        { scale: 0, opacity: 0, rotation: -10 },
        { scale: 1, opacity: 1, rotation: 0, duration: 0.8 }
    )
    .fromTo('.parchment-scroll-violet', 
        { scale: 0.9, opacity: 0 },
        { scale: 1, opacity: 1, duration: 0.8 }, 
        '-=0.3'
    )
    .fromTo('.rpg-btn-game', 
        { y: 20, opacity: 0 },
        { y: 0, opacity: 1, stagger: 0.08, duration: 0.4 }, 
        '-=0.4'
    );
};

const obtenirLocalisation = () => {
    detectant.value = true;
    messageErreur.value = null;
    
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition((position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            router.get(route('player.dashboard'), {
                lat: lat,
                lng: lng
            }, {
                preserveState: true,
                onSuccess: () => {
                    animerMenu();
                },
                onError: () => {
                    messageErreur.value = "Erreur lors du chargement des données.";
                },
                onFinish: () => detectant.value = false
            });
        }, (error) => {
            messageErreur.value = "Veuillez activer la géolocalisation pour jouer.";
            detectant.value = false;
        }, {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        });
    } else {
        messageErreur.value = "Votre navigateur ne supporte pas la géolocalisation.";
        detectant.value = false;
    }
};

const showInviteModal = ref(false);

const openStartModal = () => {
    showDurationModal.value = true;
    currentStep.value = 1; // 1: choix déplacement, 2: choix temps
    selectedTransport.value = null;
};

const selectTransport = (transport) => {
    selectedTransport.value = transport;
    currentStep.value = 2; // Aller à la saisie de la durée
};

const lancerJeu = () => {
    if (duration.value < 45) {
        durationError.value = "La durée minimale est de 45 minutes.";
        return;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const lat = urlParams.get('lat');
    const lng = urlParams.get('lng');

    router.post(route('player.game.auto-start'), {
        ville_id: props.ville_detectee?.id,
        lat: lat,
        lng: lng,
        duree: duration.value,
        moyen_transport: selectedTransport.value,
        nouvelle_session: true
    });
};

const continuerSession = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const lat = urlParams.get('lat');
    const lng = urlParams.get('lng');

    router.get(route('player.game.jeu', {
        session: props.active_session.id,
        lat: lat,
        lng: lng
    }));
};

const copierLien = async () => {
    if (props.lien_invitation) {
        try {
            await navigator.clipboard.writeText(props.lien_invitation);
            copieReussi.value = true;
            setTimeout(() => copieReussi.value = false, 2000);
        } catch (err) {
            console.error('Erreur lors de la copie:', err);
        }
    }
};

const creerInvitation = () => {
    router.post(route('player.invitations.store'), {
        duree_minutes: 60,
        max_utilisations: 10
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            creationInvitation.value = true;
            setTimeout(() => creationInvitation.value = false, 2000);
        }
    });
};
</script>

<template>
    <PlayerLayout title="Menu Principal">
        <div class="h-full flex flex-col items-center justify-center p-4 relative z-10">
            
            <!-- Affichage Pendant la détection GPS -->
            <div v-if="!ville_detectee && detectant" class="text-center">
                <div class="w-24 h-24 mx-auto mb-6 relative">
                    <div class="absolute inset-0 border-6 border-purple-600/30 rounded-full"></div>
                    <div class="absolute inset-0 border-6 border-yellow-400 rounded-full border-t-transparent animate-spin"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                </div>
                <h2 class="text-2xl md:text-4xl font-black italic uppercase text-white drop-shadow-xl tracking-tighter">Scan de la zone...</h2>
                <p class="text-white/80 font-bold uppercase tracking-widest mt-2 text-xs">Recherche d'énigmes à proximité</p>
            </div>

            <!-- Affichage Erreur GPS -->
            <div v-else-if="!ville_detectee && !detectant" class="text-center bg-black/60 backdrop-blur-md p-8 rounded-2xl border-2 border-red-500/50 max-w-sm">
                <h2 class="text-3xl font-black italic uppercase text-red-400 drop-shadow-xl mb-3">Hors Zone</h2>
                <p class="text-white font-bold uppercase tracking-widest mb-6 text-sm">{{ messageErreur || "Aucune ville à proximité" }}</p>
                <button @click="obtenirLocalisation" class="w-full py-4 rpg-btn-yellow text-white rounded-xl font-black uppercase tracking-widest shadow-xl text-lg">
                    Réessayer
                </button>
            </div>

            <!-- MENU PRINCIPAL DU JEU -->
            <div v-else-if="ville_detectee" class="w-full max-w-md flex flex-col items-center justify-center gap-5">
                
                <!-- Titre du jeu animé -->
                <div class="game-title text-center mb-1">
                    <h1 class="text-6xl md:text-7xl font-black italic uppercase tracking-tighter drop-shadow-[0_8px_16px_rgba(0,0,0,0.6)] leading-none">
                        <span class="text-white">CITY</span><span class="text-yellow-400">PLAY</span>
                    </h1>
                    <div class="inline-block px-4 py-1 bg-purple-950/80 backdrop-blur-md rounded-full mt-2.5 border border-yellow-500/40">
                        <p class="text-yellow-400 font-bold text-[10px] tracking-[0.3em] uppercase">Zone : {{ ville_detectee.nom }}</p>
                    </div>
                </div>

                <!-- Grand Parchemin du Menu -->
                <div class="parchment-scroll-violet w-full space-y-3.5">
                    <div class="text-center">
                        <span class="text-slate-400/90 font-black text-[10px] tracking-[0.25em]">— GRIMOIRE DE L'EXPLORATEUR —</span>
                    </div>

                    <!-- Bouton CONTINUER (si session en cours) -->
                    <button v-if="active_session" @click="continuerSession" 
                            class="rpg-btn-game rpg-btn-green w-full py-3 rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-xl md:text-2xl font-black italic uppercase text-white tracking-widest">Continuer</span>
                    </button>

                    <!-- Bouton JOUER (Principal) -->
                    <button @click="openStartModal" 
                            class="rpg-btn-game rpg-btn-yellow w-full py-3.5 rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-2xl md:text-3xl font-black italic uppercase text-white tracking-widest">{{ active_session ? 'Nouvelle Partie' : 'Jouer' }}</span>
                    </button>

                    <!-- Bouton INVITER DES AMIS -->
                    <button @click="showInviteModal = true" 
                            class="rpg-btn-game rpg-btn-purple w-full py-3 rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-lg md:text-xl font-black italic uppercase text-white tracking-widest">Inviter des Amis</span>
                    </button>

                    <!-- Bouton CARTE -->
                    <Link :href="route('player.map')" 
                          class="rpg-btn-game rpg-btn-purple w-full py-3 rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-lg md:text-xl font-black italic uppercase text-white tracking-widest">Carte</span>
                    </Link>

                    <!-- Bouton HISTORIQUE CULTUREL -->
                    <Link :href="route('player.historique-culturel')" 
                          class="rpg-btn-game rpg-btn-purple w-full py-3 rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-lg md:text-xl font-black italic uppercase text-white tracking-widest">Historique Culturel</span>
                    </Link>

                    <!-- Bouton STATISTIQUES -->
                    <Link :href="route('player.leaderboard')" 
                          class="rpg-btn-game rpg-btn-purple w-full py-3 rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-lg md:text-xl font-black italic uppercase text-white tracking-widest">Statistiques</span>
                    </Link>
                </div>
            </div>

            <!-- Duration & Transport Selection Modal -->
            <div v-if="showDurationModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" @click="showDurationModal = false"></div>
                <div class="bg-white rounded-2xl p-8 max-w-sm w-full relative z-10 text-center shadow-2xl border-4 border-yellow-400/20">
                    
                    <!-- STEP 1: CHOIX DEPLACEMENT -->
                    <div v-if="currentStep === 1">
                        <div class="w-20 h-20 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>

                        <h3 class="text-3xl font-black italic uppercase text-slate-800 mb-1 tracking-tighter">Déplacement</h3>
                        <p class="text-slate-500 font-bold mb-6 uppercase text-[9px] tracking-widest">Comment allez-vous vous déplacer ?</p>

                        <div class="grid grid-cols-1 gap-3.5 mb-6">
                            <button @click="selectTransport('pied')" class="flex items-center gap-4 p-3 bg-slate-50 border-2 border-slate-100 rounded-xl hover:border-purple-400 hover:bg-purple-50 transition-all group text-left">
                                <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform border border-slate-200 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 4a1 1 0 1 0 2 0 1 1 0 0 0-2 0"/><path d="M4 17l3-2 3 1 1-2"/><path d="M14 7l-2 2v3l2 2"/><path d="M15 21l-2-4-3 1"/><path d="M9 21l2-4"/></svg>
                                </div>
                                <div>
                                    <span class="block text-lg font-black uppercase text-slate-800 italic">À Pied</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Pour les explorateurs</span>
                                </div>
                            </button>

                            <button @click="selectTransport('moto')" class="flex items-center gap-4 p-3 bg-slate-50 border-2 border-slate-100 rounded-xl hover:border-purple-400 hover:bg-purple-50 transition-all group text-left">
                                <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform border border-slate-200 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/><path d="M12 17h1.5l3-3.5"/><path d="M8.5 17l2-5h5.5l1.5 5"/><path d="M11.5 12l.5-4h3l.5 4"/><path d="M13 8l1-3h3"/></svg>
                                </div>
                                <div>
                                    <span class="block text-lg font-black uppercase text-slate-800 italic">En Moto</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Pour les rapides</span>
                                </div>
                            </button>

                            <button @click="selectTransport('voiture')" class="flex items-center gap-4 p-3 bg-slate-50 border-2 border-slate-100 rounded-xl hover:border-purple-400 hover:bg-purple-50 transition-all group text-left">
                                <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform border border-slate-200 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                                </div>
                                <div>
                                    <span class="block text-lg font-black uppercase text-slate-800 italic">En Voiture</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Pour le confort</span>
                                </div>
                            </button>
                        </div>

                        <button @click="showDurationModal = false" class="w-full py-3 text-slate-400 font-black uppercase text-[10px] tracking-[0.3em] hover:text-slate-600 transition-colors">
                            Annuler
                        </button>
                    </div>

                    <!-- STEP 2: CHOIX TEMPS -->
                    <div v-if="currentStep === 2">
                        <div class="w-20 h-20 bg-yellow-100 text-yellow-500 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-inner border border-yellow-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        
                        <h3 class="text-3xl font-black italic uppercase text-slate-800 mb-1 tracking-tighter">Durée</h3>
                        <p class="text-slate-500 font-bold mb-6 uppercase text-[9px] tracking-widest">Combien de temps durera votre aventure ?</p>

                        <div class="space-y-4 mb-8">
                            <div class="relative">
                                <label class="block text-[9px] font-black uppercase text-slate-400 mb-1.5 tracking-widest text-left ml-3">Durée de la session (min)</label>
                                <input type="number" v-model="duration" min="45"
                                       class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl py-4 px-6 text-xl font-black text-slate-800 focus:ring-4 focus:ring-yellow-400/20 focus:border-yellow-400 transition-all text-center">
                                <div class="absolute right-5 top-[48px] text-slate-300 font-black uppercase text-[10px] italic">min</div>
                            </div>
                            
                            <p v-if="durationError" class="text-red-500 font-black uppercase text-[9px] animate-bounce">{{ durationError }}</p>
                            <p v-else class="text-slate-400 font-bold text-[9px] uppercase">Minimum requis : 45 minutes</p>
                        </div>

                        <div class="flex gap-3">
                            <button @click="currentStep = 1" class="flex-1 py-4 bg-slate-100 text-slate-600 rounded-xl font-black uppercase tracking-widest hover:bg-slate-200 transition-all shadow-md text-xs">
                                Retour
                            </button>
                            <button @click="lancerJeu" class="flex-1 py-4 rpg-btn-yellow text-white rounded-xl font-black uppercase tracking-widest shadow-xl text-sm">
                                Démarrer
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Modal Invitation dédié -->
            <div v-if="showInviteModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" @click="showInviteModal = false"></div>
                <div class="bg-white rounded-2xl p-8 max-w-sm w-full relative z-10 shadow-2xl border-4 border-yellow-400/20">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black italic uppercase text-slate-800 tracking-tighter">Inviter des amis</h3>
                        <p class="text-slate-500 font-bold uppercase text-[9px] tracking-widest mt-1">Recrutez des explorateurs dans votre équipe</p>
                    </div>

                    <!-- Si équipe existante -->
                    <div v-if="equipe" class="mb-5 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <h4 class="text-slate-700 font-black uppercase text-[10px] mb-2.5 flex items-center gap-2">
                            <span class="w-2 h-2 bg-yellow-400 rounded-full animate-ping"></span>
                            Équipe : {{ equipe.nom }}
                        </h4>
                        <div class="flex flex-wrap gap-2">
                            <div v-for="membre in equipe.membres" :key="membre.id" 
                                 class="flex items-center gap-1.5 px-3 py-1.5 bg-white rounded-full border border-slate-200 text-[10px] font-bold text-slate-700 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>{{ membre.name }}</span>
                                <span v-if="membre.id === equipe.chef_id" class="text-yellow-500 font-black uppercase text-[7px] tracking-wider ml-1">Chef</span>
                            </div>
                        </div>
                    </div>

                    <!-- Lien d'invitation -->
                    <div v-if="!equipe || (equipe && equipe.chef_id === $page.props.auth.user.id)" class="space-y-3.5">
                        <p class="text-slate-500 font-bold text-[9px] uppercase tracking-wider">Partagez ce lien unique avec vos partenaires :</p>
                        <div v-if="lien_invitation" class="flex gap-2">
                            <input type="text" readonly :value="lien_invitation" 
                                   class="flex-1 px-3.5 py-2.5 bg-slate-50 border-2 border-slate-100 rounded-xl text-slate-700 font-mono text-[10px] focus:outline-none">
                            <button @click="copierLien" 
                                    class="px-4 py-2.5 bg-yellow-400 text-slate-900 rounded-xl font-black uppercase text-xs tracking-wider hover:scale-105 transition-all shadow-md">
                                {{ copieReussi ? 'Copié !' : 'Copier' }}
                             </button>
                        </div>
                        <button v-else @click="creerInvitation" 
                                class="w-full py-3.5 bg-yellow-400 text-slate-900 rounded-xl font-black uppercase tracking-wider hover:scale-105 active:scale-95 transition-all shadow-md text-xs">
                            {{ creationInvitation ? 'Création...' : 'Créer un lien d\'invitation' }}
                        </button>
                    </div>
                    <div class="text-center bg-slate-50 p-4 rounded-xl border border-dashed border-slate-200" v-else>
                        <p class="text-slate-400 font-bold text-[10px] uppercase">Seul le chef d'équipe peut générer des invitations.</p>
                    </div>

                    <button @click="showInviteModal = false" class="w-full mt-5 py-3.5 bg-slate-100 text-slate-600 rounded-xl font-black uppercase tracking-widest hover:bg-slate-200 transition-all text-[10px]">
                        Fermer
                    </button>
                </div>
            </div>

        </div>
    </PlayerLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;700;900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }
h1, h2, h3, button, span { font-family: 'Bangers', cursive; }

/* Elegant Hand-drawn Medieval Parchment Scroll (Sans clip-path) */
.parchment-scroll-violet {
    background: linear-gradient(135deg, #fffbf2 0%, #f7ebd3 100%);
    border: 4px solid #7c3aed; /* Violet */
    outline: 3px solid #fbbf24; /* Or */
    
    /* Wavy, handcrafted organic curves for the edges */
    border-radius: 16px 28px 20px 32px / 28px 20px 32px 16px;

    box-shadow: 
        0 15px 35px rgba(0,0,0,0.55), 
        inset 0 0 60px rgba(139, 94, 26, 0.25),
        0 0 20px rgba(124, 58, 237, 0.2);
    position: relative;
    padding: 2.25rem 1.75rem;
    max-width: 360px; /* Highly compact & aesthetic */
    margin: 0 auto;
    
    /* Subtle tilt for natural paper effect */
    transform: rotate(0.4deg);
}

/* Rolled parchment pseudo-borders */
.parchment-scroll-violet::before {
    content: '';
    position: absolute;
    top: -8px;
    left: 8%;
    right: 8%;
    height: 5px;
    background: linear-gradient(to right, #7c3aed, #fbbf24, #7c3aed);
    border-radius: 3px;
    opacity: 0.85;
}
.parchment-scroll-violet::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 8%;
    right: 8%;
    height: 5px;
    background: linear-gradient(to right, #7c3aed, #fbbf24, #7c3aed);
    border-radius: 3px;
    opacity: 0.85;
}

/* 3D RPG Buttons with Compact Sizes */
.rpg-btn-green {
    background: linear-gradient(to bottom, #4ade80 0%, #16a34a 100%);
    border-top: 3px solid #bbf7d0;
    border-bottom: 5px solid #14532d; /* Deep green base shadow */
    border-left: 2px solid #15803d;
    border-right: 2px solid #15803d;
    text-shadow: 2px 2px 0px #14532d;
    box-shadow: 0 6px 12px rgba(0,0,0,0.4), inset 0 2px 4px rgba(255,255,255,0.4);
    transition: all 0.1s ease;
}
.rpg-btn-green:hover {
    filter: brightness(1.1);
    transform: scale(1.02);
}
.rpg-btn-green:active {
    border-bottom-width: 2px;
    transform: translateY(3px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.4), inset 0 2px 4px rgba(255,255,255,0.4);
}

.rpg-btn-yellow {
    background: linear-gradient(to bottom, #fbbf24 0%, #d97706 100%);
    border-top: 3px solid #fef3c7;
    border-bottom: 5px solid #78350f; /* Deep gold base shadow */
    border-left: 2px solid #b45309;
    border-right: 2px solid #b45309;
    text-shadow: 2px 2px 0px #78350f;
    box-shadow: 0 6px 12px rgba(0,0,0,0.4), inset 0 2px 4px rgba(255,255,255,0.4);
    transition: all 0.1s ease;
}
.rpg-btn-yellow:hover {
    filter: brightness(1.1);
    transform: scale(1.02);
}
.rpg-btn-yellow:active {
    border-bottom-width: 2px;
    transform: translateY(3px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.4), inset 0 2px 4px rgba(255,255,255,0.4);
}

.rpg-btn-purple {
    background: linear-gradient(to bottom, #a855f7 0%, #7c3aed 100%);
    border-top: 3px solid #f3e8ff;
    border-bottom: 5px solid #4c1d95; /* Deep purple base shadow */
    border-left: 2px solid #6b21a8;
    border-right: 2px solid #6b21a8;
    text-shadow: 2px 2px 0px #4c1d95;
    box-shadow: 0 6px 12px rgba(0,0,0,0.4), inset 0 2px 4px rgba(255,255,255,0.4);
    transition: all 0.1s ease;
}
.rpg-btn-purple:hover {
    filter: brightness(1.1);
    transform: scale(1.02);
}
.rpg-btn-purple:active {
    border-bottom-width: 2px;
    transform: translateY(3px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.4), inset 0 2px 4px rgba(255,255,255,0.4);
}
</style>
