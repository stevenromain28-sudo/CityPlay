<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
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
    invitation: Object
});

const detectant = ref(false);
const messageErreur = ref(null);
const copieReussi = ref(false);
const creationInvitation = ref(false);

onMounted(() => {
    if (props.localisation_requise) {
        obtenirLocalisation();
    } else {
        animerMenu();
    }
});

const animerMenu = () => {
    const tl = gsap.timeline({ defaults: { ease: 'back.out(1.7)' } });
    
    tl.from('.game-title', { scale: 0, opacity: 0, duration: 0.8, rotation: -10 })
      .from('.menu-btn', { 
          y: 50, 
          opacity: 0, 
          stagger: 0.15, 
          duration: 0.6 
      }, '-=0.4');
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

const lancerJeu = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const lat = urlParams.get('lat');
    const lng = urlParams.get('lng');

    router.post(route('player.game.auto-start'), {
        ville_id: props.ville_detectee?.id,
        lat: lat,
        lng: lng
    });
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
                <div class="w-32 h-32 mx-auto mb-8 relative">
                    <div class="absolute inset-0 border-8 border-[#7C3AED]/30 rounded-full"></div>
                    <div class="absolute inset-0 border-8 border-yellow-400 rounded-full border-t-transparent animate-spin"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-[#7C3AED]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                </div>
                <h2 class="text-3xl md:text-5xl font-black italic uppercase text-white drop-shadow-xl tracking-tighter">Scan de la zone...</h2>
                <p class="text-white/80 font-bold uppercase tracking-widest mt-4">Recherche d'énigmes à proximité</p>
            </div>

            <!-- Affichage Erreur GPS -->
            <div v-else-if="!ville_detectee && !detectant" class="text-center bg-black/50 backdrop-blur-md p-10 rounded-3xl border-2 border-red-500/50 max-w-lg">
                <h2 class="text-4xl font-black italic uppercase text-red-400 drop-shadow-xl mb-4">Hors Zone</h2>
                <p class="text-white font-bold uppercase tracking-widest mb-8">{{ messageErreur || "Aucune ville à proximité" }}</p>
                <button @click="obtenirLocalisation" class="px-8 py-4 bg-yellow-400 text-slate-900 rounded-2xl font-black uppercase tracking-widest shadow-xl hover:scale-105 active:scale-95 transition-all text-xl">
                    Réessayer
                </button>
            </div>

            <!-- MENU PRINCIPAL DU JEU -->
            <div v-else-if="ville_detectee" class="w-full max-w-4xl flex flex-col items-center justify-center gap-8">
                
                <!-- Titre du jeu animé -->
                <div class="game-title text-center mb-6">
                    <h1 class="text-7xl md:text-8xl font-black italic uppercase tracking-tighter drop-shadow-[0_10px_20px_rgba(0,0,0,0.5)]">
                        <span class="text-white">CITY</span><span class="text-yellow-400">PLAY</span>
                    </h1>
                    <div class="inline-block px-4 py-1 bg-white/20 backdrop-blur-md rounded-full mt-2 border border-white/30">
                        <p class="text-white font-bold text-sm tracking-[0.3em] uppercase">Zone : {{ ville_detectee.nom }}</p>
                    </div>
                </div>

                <!-- SECTION ÉQUIPE / INVITATION -->
                <div class="w-full bg-black/50 backdrop-blur-md p-6 rounded-3xl border-2 border-yellow-400/50">
                    <h3 class="text-2xl font-black italic uppercase text-yellow-400 tracking-widest mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-8 h-8 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {{ equipe ? equipe.nom : 'Inviter des amis' }}
                    </h3>

                    <!-- Si équipe existe -->
                    <div v-if="equipe">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-white/70 text-sm font-bold">
                                Score: {{ equipe.score_total }} XP
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <div v-for="membre in equipe.membres" :key="membre.id" 
                                 class="flex items-center gap-2 px-4 py-2 bg-white/10 rounded-full border border-white/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-white font-bold">{{ membre.name }}</span>
                                <span v-if="membre.id === equipe.chef_id" class="text-yellow-400 text-xs font-bold uppercase">Chef</span>
                            </div>
                        </div>
                    </div>

                    <!-- Lien d'invitation (seulement pour le chef ou si pas d'équipe) -->
                    <div v-if="!equipe || (equipe && equipe.chef_id === $page.props.auth.user.id)">
                        <div v-if="lien_invitation" class="flex gap-3">
                            <input type="text" readonly :value="lien_invitation" 
                                   class="flex-1 px-4 py-3 bg-white/10 border border-white/30 rounded-xl text-white font-mono text-sm">
                            <button @click="copierLien" 
                                    class="px-6 py-3 bg-yellow-400 text-slate-900 rounded-xl font-black uppercase tracking-wider hover:scale-105 transition-all">
                                {{ copieReussi ? 'Copié !' : 'Copier' }}
                            </button>
                        </div>
                        <button v-else @click="creerInvitation" 
                                class="w-full px-6 py-3 bg-yellow-400 text-slate-900 rounded-xl font-black uppercase tracking-wider hover:scale-105 active:scale-95 transition-all">
                            {{ creationInvitation ? 'Invitation Créée !' : 'Créer un lien d\'invitation' }}
                        </button>
                    </div>
                </div>

                <!-- Boutons du menu -->
                <div class="flex flex-col w-full max-w-md gap-5">
                    
                    <!-- Bouton JOUER (Principal) -->
                    <button @click="lancerJeu" class="menu-btn group relative w-full overflow-hidden rounded-[2rem] bg-gradient-to-b from-yellow-300 to-yellow-500 p-[2px] shadow-[0_10px_40px_-10px_rgba(250,204,21,0.6)] hover:scale-105 active:scale-95 transition-transform">
                        <div class="relative w-full rounded-[1.9rem] bg-gradient-to-b from-yellow-400 to-yellow-600 px-8 py-6 flex items-center justify-center border-t border-yellow-200">
                            <span class="text-4xl md:text-5xl font-black italic uppercase text-white drop-shadow-[0_2px_4px_rgba(0,0,0,0.3)] tracking-widest group-hover:text-yellow-50 transition-colors">Jouer</span>
                            <div class="absolute inset-0 rounded-[1.9rem] bg-white opacity-0 group-hover:opacity-20 transition-opacity"></div>
                        </div>
                    </button>

                    <!-- Bouton CARTE -->
                    <Link :href="route('player.map')" class="menu-btn group relative w-full overflow-hidden rounded-3xl bg-gradient-to-b from-blue-400 to-blue-600 p-[2px] shadow-lg hover:scale-105 active:scale-95 transition-transform">
                        <div class="relative w-full rounded-[1.4rem] bg-gradient-to-b from-[#7C3AED] to-purple-800 px-6 py-4 flex items-center justify-center border-t border-purple-400">
                            <span class="text-2xl md:text-3xl font-black italic uppercase text-white drop-shadow-md tracking-widest">Carte</span>
                        </div>
                    </Link>

                    <!-- Bouton STATISTIQUES -->
                    <Link :href="route('player.leaderboard')" class="menu-btn group relative w-full overflow-hidden rounded-3xl bg-gradient-to-b from-blue-400 to-blue-600 p-[2px] shadow-lg hover:scale-105 active:scale-95 transition-transform">
                        <div class="relative w-full rounded-[1.4rem] bg-gradient-to-b from-[#7C3AED] to-purple-800 px-6 py-4 flex items-center justify-center border-t border-purple-400">
                            <span class="text-2xl md:text-3xl font-black italic uppercase text-white drop-shadow-md tracking-widest">Statistiques</span>
                        </div>
                    </Link>

                </div>
            </div>
        </div>
    </PlayerLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;700;900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }
h1, h2, h3, button, span { font-family: 'Bangers', cursive; }
</style>
