<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { onMounted, onUnmounted, ref, computed,watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import gsap from 'gsap';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { useGameStore } from '@/Stores/game';
import { webSocketService } from '@/Services/websocket';

const props = defineProps({
    session: Object,
    enigme: Object,
    indices_debloques: {
        type: Array,
        default: () => []
    },
    joueur_score: {
        type: Number,
        default: 0
    },
    progression: {
        type: Object,
        default: null
    }
});

const gameStore = useGameStore();
const reponseTextuelle = ref('');
const loading = ref(false);
const localScore = ref(props.joueur_score);
const localUnlockedIndices = ref(props.indices_debloques);
const unlockedIndicesContent = ref({});
const localProgression = ref(props.progression);
const bonusEnigmes = ref([]);

// Synchroniser les données locales quand les props changent (ex: après un router.reload)
watch(() => props.enigme, (newEnigme) => {
    if (newEnigme) {
        reponseTextuelle.value = '';
        localUnlockedIndices.value = props.indices_debloques;
        unlockedIndicesContent.value = {};
        localProgression.value = props.progression;
        if (newEnigme.is_bonus) {
            showBonusChoice.value = false;
        }
    }
}, { deep: true });

watch(() => props.progression, (newProg) => {
    localProgression.value = newProg;
}, { deep: true });

watch(() => props.joueur_score, (newScore) => {
    localScore.value = newScore;
});

const isTextValidated = computed(() => !!localProgression.value?.text_validated_at);
const isGpsValidated = computed(() => !!localProgression.value?.gps_validated_at);
const showBonusChoice = ref(false);

// Gestion du Temps (Déléguée au PlayerLayout, on ne garde que les refs réactives si besoin)
const tempsRestant = computed(() => props.session.temps_restant);
const sessionStatut = computed(() => props.session.statut);

// Carte Leaflet
const mapContainer = ref(null);
let map = null;
let playerMarker = null;
let targetMarker = null;
let validationCircle = null;
const watchId = ref(null);

const initGameMap = () => {
    if (!mapContainer.value || !props.enigme.latitude) return;

    if (map) {
        map.remove();
        map = null;
    }

    map = L.map(mapContainer.value, {
        zoomControl: false,
        attributionControl: false
    }).setView([props.enigme.latitude, props.enigme.longitude], 16);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(map);

    // Marqueur Cible (Lieu de l'énigme)
    const targetIcon = L.divIcon({
        html: `<div class="w-10 h-10 bg-[#7C3AED] rounded-full border-4 border-white shadow-lg flex items-center justify-center animate-pulse">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
               </div>`,
        className: '',
        iconSize: [40, 40],
        iconAnchor: [20, 20]
    });

    targetMarker = L.marker([props.enigme.latitude, props.enigme.longitude], { icon: targetIcon }).addTo(map);

    // Cercle de validation
    validationCircle = L.circle([props.enigme.latitude, props.enigme.longitude], {
        radius: props.enigme.rayon || 50,
        color: '#7C3AED',
        fillColor: '#7C3AED',
        fillOpacity: 0.15,
        weight: 2,
        dashArray: '5, 10'
    }).addTo(map);

    // Suivi du joueur
    if ("geolocation" in navigator) {
        watchId.value = navigator.geolocation.watchPosition((position) => {
            const { latitude, longitude } = position.coords;
            const playerPos = [latitude, longitude];
            
            console.log("Position joueur récupérée:", latitude, longitude); // Pour debug

            if (!playerMarker) {
                const playerIcon = L.divIcon({
                    html: `<div class="w-6 h-6 bg-blue-500 rounded-full border-2 border-white shadow-md">
                            <div class="absolute inset-0 bg-blue-500 rounded-full animate-ping opacity-75"></div>
                           </div>`,
                    className: '',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });
                playerMarker = L.marker(playerPos, { icon: playerIcon }).addTo(map);
            } else {
                playerMarker.setLatLng(playerPos);
            }

            // Ajuster la vue pour voir les deux marqueurs
            const bounds = L.latLngBounds([playerPos, [props.enigme.latitude, props.enigme.longitude]]);
            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 18 });
        }, (err) => console.error("Erreur GPS:", err), {
            enableHighAccuracy: true,
            maximumAge: 10000, // Maximum 10 secondes de cache
            timeout: 30000, // Timeout après 30 secondes
        });
    }

    setTimeout(() => map.invalidateSize(), 400);
};

// Observer les changements pour initialiser la carte quand on arrive à l'étape GPS
watch(isTextValidated, (newVal) => {
    if (newVal && !isGpsValidated.value) {
        setTimeout(initGameMap, 100);
    }
});

// Gestion du Modal
const modalState = ref({
    show: false,
    type: 'success',
    title: '',
    message: '',
    content: null,
    isChoice: false
});

const showModal = (type, title, message, content = null, isChoice = false) => {
    modalState.value = { show: true, type, title, message, content, isChoice };
    setTimeout(() => {
        gsap.fromTo('.game-modal-content',
            { scale: 0.5, opacity: 0, y: 50 },
            { scale: 1, opacity: 1, y: 0, duration: 0.5, ease: 'back.out(1.5)' }
        );
    }, 10);
};

const closeModal = (action = null) => {
    gsap.to('.game-modal-content', {
        scale: 0.8, opacity: 0, y: 30, duration: 0.3, ease: 'power2.in',
        onComplete: () => {
            modalState.value.show = false;
            if (action === 'reload') {
                router.reload();
            }
        }
    });
};

const faireChoixBonus = (wantsBonus) => {
    loading.value = true;
    router.post(route('player.game.bonus.choice', {
        session: props.session.id,
        enigme: props.enigme.id
    }), { wants_bonus: wantsBonus }, {
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
            modalState.value.show = false;
            // Si c'est un bonus, l'enigme change, les watchers feront le reste
            // Si c'est "Lieu suivant", on sera redirigé ou l'enigme sera null
        },
        onError: () => {
            loading.value = false;
            showModal('error', 'Erreur', "Une erreur est survenue lors du choix.");
        }
    });
};

onMounted(() => {
    gameStore.setSession(props.session);
    if (props.enigme) {
        gameStore.setEnigmeActive(props.enigme);
    }

    if (isTextValidated.value && !isGpsValidated.value) {
        setTimeout(initGameMap, 100);
    }

    webSocketService.joinSession(props.session.id, {
        onJoined: (users) => gameStore.updateJoueurs(users),
        onUserJoining: (user) => gameStore.updateJoueurs([...gameStore.joueursConnectes, user]),
        onUserLeaving: (user) => gameStore.updateJoueurs(gameStore.joueursConnectes.filter(u => u.id !== user.id)),
        onEnigmeResolue: (data) => {
            showModal('info', 'Progression', 'Un partenaire a résolu l\'énigme !');
            setTimeout(() => router.reload(), 3000);
        }
    });

    // Si la session est en attente, commencer la session !
    if (props.session.statut === 'en_attente') {
        axios.post(route('player.game.start', { session: props.session.id }))
            .then(() => {
                router.reload();
            })
            .catch(err => console.error(err));
    }

    gsap.from('.game-card', { scale: 0.9, opacity: 0, duration: 0.8, ease: 'back.out(1.7)' });
});

onUnmounted(() => {
    if (watchId.value) {
        navigator.geolocation.clearWatch(watchId.value);
    }
    webSocketService.leaveSession(props.session.id);
    gameStore.stopTimer();
});

const validerGPS = () => {
    loading.value = true;
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(async (position) => {
            try {
                const response = await axios.post(route('player.game.validate.gps', {
                    session: props.session.id,
                    enigme: props.enigme.id
                }), {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                });
                loading.value = false;
                if (response.data.success) {
                    localScore.value += response.data.score_gagne;
                    // Mettre à jour la progression locale pour changer l'UI immédiatement
                    localProgression.value = { ...localProgression.value, gps_validated_at: new Date() };

                    if (response.data.show_choice) {
                        showBonusChoice.value = true;
                        showModal('success', 'Énigme Complétée !', response.data.message, response.data.content, true);
                    } else {
                        showModal('success', 'Énigme Résolue !', response.data.message, response.data.content);
                    }
                }
            } catch (error) {
                loading.value = false;
                const msg = error.response?.data?.message || "Erreur de validation GPS.";
                showModal('error', 'Échec', msg);
            }
        }, (error) => {
            loading.value = false;
            showModal('error', 'Erreur GPS', "Impossible de récupérer votre position : " + error.message);
        }, {
            enableHighAccuracy: true,
            maximumAge: 10000,
            timeout: 30000,
        });
    } else {
        loading.value = false;
        showModal('error', 'Erreur', "Le GPS n'est pas supporté par votre navigateur.");
    }
};

const soumettreReponse = async () => {
    if (!reponseTextuelle.value.trim()) return;
    loading.value = true;
    try {
        const response = await axios.post(route('player.game.submit.answer', {
            session: props.session.id,
            enigme: props.enigme.id
        }), {
            reponse: reponseTextuelle.value
        });
        loading.value = false;
        if (response.data.success) {
            localProgression.value = { ...localProgression.value, text_validated_at: new Date() };
            localScore.value += response.data.score_gagne;
            showModal('success', 'Bonne Réponse !', response.data.message);
        }
    } catch (error) {
        loading.value = false;
        const msg = error.response?.data?.message || "Réponse incorrecte.";
        showModal('error', 'Faux !', msg);
    }
};

const debloquerIndice = async (indice) => {
    try {
        const response = await axios.post(route('player.game.unlock.indice', {
            session: props.session.id,
            enigme: props.enigme.id,
            indice: indice.id
        }));

        if (response.data.success) {
            localUnlockedIndices.value.push(indice.id);
            unlockedIndicesContent.value[indice.id] = response.data.contenu;
            localScore.value = response.data.nouveau_score;
            showModal('info', 'Indice Débloqué', response.data.message);
        }
    } catch (error) {
        const msg = error.response?.data?.message || "Erreur lors du déblocage de l'indice.";
        showModal('error', 'Impossible', msg);
    }
};

const isIndiceUnlocked = (indiceId) => {
    return localUnlockedIndices.value.includes(indiceId);
};
</script>

<template>
    <PlayerLayout :title="enigme ? enigme.titre : 'En jeu'">
        <!-- HUD Score Update local (si on veut forcer l'affichage) -->
        <div class="fixed top-24 right-6 z-40 bg-slate-900/80 backdrop-blur-md px-4 py-2 rounded-full border border-yellow-400/30 shadow-lg text-yellow-400 font-black text-xl italic drop-shadow-md pointer-events-none">
            {{ localScore }} XP
        </div>

        <div v-if="enigme" class="max-w-4xl mx-auto pt-32 pb-10 px-4 md:px-0">
            <div class="game-card bg-white/90 backdrop-blur-md rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-white/20 overflow-hidden relative">
                <!-- Image de l'énigme -->
                <div class="h-64 md:h-96 relative overflow-hidden">
                    <div v-if="!isTextValidated && !enigme.is_bonus" class="absolute inset-0 bg-slate-200 flex items-center justify-center">
                        <span class="text-9xl font-black text-slate-300">?</span>
                    </div>
                    <img v-else :src="enigme.image || 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=800'"
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-white/90 via-transparent to-transparent"></div>

                    <div class="absolute top-6 left-6 flex flex-col gap-3">
                        <span v-if="enigme.is_bonus" class="px-6 py-3 bg-gradient-to-b from-purple-500 to-purple-700 text-white text-xs font-black uppercase rounded-2xl tracking-widest shadow-xl border border-purple-300">
                            MODE BONUS
                        </span>
                        <span v-else class="px-6 py-3 bg-gradient-to-b from-yellow-400 to-yellow-600 text-white text-xs font-black uppercase rounded-2xl tracking-widest shadow-xl border border-yellow-200">
                            Niveau {{ enigme.niveau }}
                        </span>
                    </div>
                </div>

                <div class="p-8 md:p-12 -mt-16 md:-mt-24 relative z-10">
                    <h2 v-if="!isTextValidated && !enigme.is_bonus" class="text-4xl md:text-6xl font-black italic uppercase tracking-tighter text-slate-300 drop-shadow-sm mb-6">??? ??? ???</h2>
                    <h2 v-else class="text-4xl md:text-6xl font-black italic uppercase tracking-tighter text-slate-800 drop-shadow-sm mb-6">{{ enigme.titre }}</h2>

                    <div class="prose prose-slate max-w-none mb-10">
                        <p class="text-lg md:text-2xl font-bold text-slate-700 leading-relaxed italic bg-white/50 p-6 rounded-3xl border border-white shadow-inner">
                            "{{ enigme.contenu }}"
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-6">
                        <!-- Réponse textuelle (Étape 1) -->
                        <div v-if="!isTextValidated" class="bg-gradient-to-b from-yellow-50 to-white p-6 md:p-8 rounded-[2.5rem] border border-yellow-200 shadow-md">
                            <h3 class="text-xl font-black italic uppercase text-yellow-600 mb-2">Résoudre le mystère</h3>
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-6">Saisissez le mot clé pour révéler le lieu</p>

                            <div class="relative">
                                <input v-model="reponseTextuelle" type="text" placeholder="VOTRE RÉPONSE ICI..."
                                       class="w-full bg-white border-2 border-yellow-100 rounded-2xl py-5 px-8 text-sm md:text-base font-bold tracking-widest text-slate-700 focus:ring-4 focus:ring-yellow-400/30 focus:border-yellow-400 transition-all placeholder:text-slate-300 shadow-inner">
                                <button @click="soumettreReponse" :disabled="loading"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 w-12 h-12 bg-gradient-to-b from-yellow-400 to-yellow-600 text-white rounded-xl flex items-center justify-center hover:scale-110 active:scale-95 transition-transform shadow-lg border border-yellow-300">
                                    <div v-if="loading" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Validation GPS (Étape 2) -->
                        <div v-if="isTextValidated && !isGpsValidated" class="bg-gradient-to-b from-[#F0F7FF] to-white p-6 md:p-8 rounded-[2.5rem] border border-blue-100 shadow-md">
                            <h3 class="text-xl font-black italic uppercase text-[#7C3AED] mb-2">Se rendre sur place</h3>
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-6">Utilisez votre GPS pour gagner le reste des points !</p>

                            <!-- Carte de guidage -->
                            <div class="h-64 md:h-80 w-full bg-slate-100 rounded-3xl mb-8 overflow-hidden border-4 border-white shadow-inner relative">
                                <div ref="mapContainer" class="w-full h-full z-0"></div>
                                <div class="absolute bottom-4 left-4 z-10 bg-white/80 backdrop-blur-md px-3 py-1 rounded-full text-[10px] font-black uppercase text-[#7C3AED] shadow-sm">
                                    Rayon: {{ enigme.rayon || 50 }}m
                                </div>
                            </div>

                            <button @click="validerGPS" :disabled="loading"
                                    class="w-full py-5 bg-gradient-to-b from-[#7C3AED] to-purple-800 border-t border-purple-400 text-white rounded-3xl font-black text-xl md:text-2xl uppercase tracking-widest shadow-[0_10px_20px_-10px_rgba(124,58,237,0.8)] hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center space-x-4">
                                <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <div v-else class="w-8 h-8 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
                                <span>{{ loading ? 'Vérification...' : 'Je suis arrivé !' }}</span>
                            </button>
                        </div>

                        <!-- Choix Bonus (Étape 3) -->
                        <div v-if="isGpsValidated" class="text-center py-10">
                            <h3 class="text-3xl font-black italic uppercase text-[#7C3AED] mb-8">Bravo ! Vous avez terminé ce lieu.</h3>
                            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                                <button @click="faireChoixBonus(false)" class="px-10 py-5 bg-slate-800 text-white rounded-[2rem] font-black uppercase tracking-widest hover:bg-slate-900 transition-all shadow-xl">
                                    Lieu suivant
                                </button>
                                <button @click="faireChoixBonus(true)" class="px-10 py-5 bg-yellow-400 text-white rounded-[2rem] font-black uppercase tracking-widest hover:bg-yellow-500 transition-all shadow-xl">
                                    En savoir plus (Bonus)
                                </button>
                            </div>
                        </div>

                        <!-- Indices -->
                        <div v-if="enigme.indices && enigme.indices.length > 0" class="mt-8 pt-8 border-t border-slate-200">
                            <h3 class="text-xl font-black italic uppercase text-slate-800 mb-6 flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#7C3AED]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Besoin d'aide ?
                            </h3>
                            <div class="space-y-4">
                                <div v-for="(indice, index) in enigme.indices" :key="indice.id" class="relative">
                                    <!-- Indice Débloqué -->
                                    <div v-if="isIndiceUnlocked(indice.id)" class="p-6 bg-slate-100 rounded-3xl border border-slate-200 shadow-inner">
                                        <p class="text-[#7C3AED] text-xs font-black uppercase tracking-widest mb-2">Indice {{ index + 1 }}</p>
                                        <p class="text-slate-700 italic font-bold text-lg">{{ unlockedIndicesContent[indice.id] || indice.contenu }}</p>
                                    </div>
                                    <!-- Indice Bloqué -->
                                    <div v-else class="p-6 bg-slate-50 rounded-3xl border border-dashed border-slate-300 flex flex-col md:flex-row items-center justify-between gap-4">
                                        <div>
                                            <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">Indice {{ index + 1 }}</p>
                                            <p class="text-slate-400 font-bold text-sm">Contenu verrouillé</p>
                                        </div>
                                        <button @click="debloquerIndice(indice)"
                                                class="shrink-0 px-6 py-3 rounded-2xl font-black uppercase tracking-widest transition-all shadow-md active:scale-95 flex items-center gap-2"
                                                :class="(localUnlockedIndices.length === 0) ? 'bg-green-100 text-green-700 hover:bg-green-200 border border-green-200' : 'bg-orange-100 text-orange-700 hover:bg-orange-200 border border-orange-200'">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                            {{ localUnlockedIndices.length === 0 ? 'Gratuit' : `- ${indice.penalite} XP` }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center h-full pt-20 px-6">
            <div class="bg-black/50 backdrop-blur-md p-10 rounded-3xl border-2 border-white/10 text-center max-w-lg w-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-yellow-400 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <h2 class="text-3xl md:text-5xl font-black italic uppercase tracking-tighter text-white mb-4">Aucune quête active</h2>
                <p class="text-slate-300 text-sm md:text-base font-bold uppercase tracking-widest mb-8">Retournez au menu pour lancer une session</p>
                <Link :href="route('player.dashboard')" class="inline-block py-4 px-8 bg-gradient-to-b from-yellow-400 to-yellow-600 text-slate-900 rounded-2xl font-black uppercase tracking-widest shadow-xl hover:scale-105 active:scale-95 transition-all">
                    Menu Principal
                </Link>
            </div>
        </div>

        <!-- MODAL GLOBAL DE JEU -->
        <div v-if="modalState.show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <!-- Overlay sombre -->
            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" @click="closeModal"></div>

            <!-- Contenu Modal -->
            <div class="game-modal-content relative w-full max-w-lg rounded-[3rem] p-1 border-2 shadow-[0_30px_60px_rgba(0,0,0,0.6)]"
                 :class="{
                     'bg-gradient-to-br from-green-400 to-green-600 border-green-300': modalState.type === 'success',
                     'bg-gradient-to-br from-red-400 to-red-600 border-red-300': modalState.type === 'error',
                     'bg-gradient-to-br from-blue-400 to-blue-600 border-blue-300': modalState.type === 'info'
                 }">
                <div class="bg-white rounded-[2.8rem] p-8 md:p-10 text-center relative overflow-hidden">
                    <!-- Décoration fond -->
                    <div class="absolute -right-10 -top-10 w-40 h-40 opacity-10 rounded-full"
                         :class="{'bg-green-500': modalState.type === 'success', 'bg-red-500': modalState.type === 'error', 'bg-blue-500': modalState.type === 'info'}"></div>

                    <!-- Icône -->
                    <div class="w-24 h-24 mx-auto rounded-3xl flex items-center justify-center mb-6 shadow-xl relative z-10"
                         :class="{
                             'bg-green-100 text-green-500 rotate-3': modalState.type === 'success',
                             'bg-red-100 text-red-500 -rotate-3': modalState.type === 'error',
                             'bg-blue-100 text-blue-500 rotate-6': modalState.type === 'info'
                         }">
                        <svg v-if="modalState.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        <svg v-else-if="modalState.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>

                    <h3 class="text-4xl font-black italic uppercase tracking-tighter mb-4 relative z-10"
                        :class="{'text-green-600': modalState.type === 'success', 'text-red-600': modalState.type === 'error', 'text-blue-600': modalState.type === 'info'}">
                        {{ modalState.title }}
                    </h3>

                    <p class="text-slate-600 font-bold text-lg mb-8 relative z-10">{{ modalState.message }}</p>

                    <div v-if="modalState.content" class="text-left bg-slate-50 p-6 rounded-2xl mb-8 border border-slate-200 prose prose-sm max-w-none relative z-10 max-h-48 overflow-y-auto">
                        <div v-html="modalState.content"></div>
                    </div>

                    <div v-if="modalState.isChoice" class="flex flex-col sm:flex-row gap-4 relative z-10">
                        <button @click="faireChoixBonus(false)" class="flex-1 py-5 bg-slate-100 text-slate-600 rounded-2xl font-black uppercase tracking-widest hover:bg-slate-200 transition-all shadow-lg">
                            Lieu suivant
                        </button>
                        <button @click="faireChoixBonus(true)" class="flex-1 py-5 bg-[#7C3AED] text-white rounded-2xl font-black uppercase tracking-widest shadow-lg hover:bg-purple-700 transition-all">
                            En savoir plus
                        </button>
                    </div>
                    <button v-else @click="closeModal('reload')"
                            class="w-full py-5 text-white rounded-2xl font-black uppercase tracking-widest shadow-lg hover:scale-105 active:scale-95 transition-all relative z-10"
                            :class="{
                                'bg-green-500 hover:bg-green-600 shadow-green-500/30': modalState.type === 'success',
                                'bg-red-500 hover:bg-red-600 shadow-red-500/30': modalState.type === 'error',
                                'bg-blue-500 hover:bg-blue-600 shadow-blue-500/30': modalState.type === 'info'
                            }">
                        {{ modalState.type === 'success' ? 'Continuer' : 'Fermer' }}
                    </button>
                </div>
            </div>
        </div>

    </PlayerLayout>
</template>

<style scoped>
h2, h3, button, span {
    font-family: 'Bangers', cursive;
}
</style>
