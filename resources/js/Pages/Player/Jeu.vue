<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { onMounted, onUnmounted, ref, computed, watch ,nextTick } from 'vue';
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
    },
    autres_lieux: {
        type: Array,
        default: () => []
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
const showChangerLieuModal = ref(false); // Gardé pour compatibilité si nécessaire

const changerLieu = () => {
    loading.value = true;
    router.post(route('player.game.changer-lieu', { session: props.session.id }), {}, {
        onFinish: () => {
            loading.value = false;
        }
    });
};

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

// Gestion du Temps (Déléguée au PlayerLayout)
const tempsRestant = computed(() => props.session.temps_restant);
const sessionStatut = computed(() => props.session.statut);

// Carte Leaflet
const mapContainer = ref(null);
let map = null;
let playerMarker = null;
let targetMarker = null;
let validationCircle = null;
let connectionLine = null;
const watchId = ref(null);

const initGameMap = () => {
    if (!mapContainer.value || !props.enigme.latitude) return;

    const targetLat = parseFloat(props.enigme.latitude);
    const targetLng = parseFloat(props.enigme.longitude);

    if (map) {
        map.remove();
        map = null;
    }

    map = L.map(mapContainer.value, {
        zoomControl: false,
        attributionControl: false
    }).setView([targetLat, targetLng], 16);

    // Utilisation des tuiles OpenStreetMap standard, validées et fonctionnelles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    // Marqueur Cible (Lieu de l'énigme)
    const targetIcon = L.divIcon({
        html: `<div class="w-10 h-10 bg-[#7C3AED] rounded-full border-4 border-white shadow-lg flex items-center justify-center animate-pulse">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
               </div>`,
        className: '',
        iconSize: [40, 40],
        iconAnchor: [20, 20]
    });

    targetMarker = L.marker([targetLat, targetLng], { icon: targetIcon }).addTo(map);

    // Cercle de validation
    validationCircle = L.circle([targetLat, targetLng], {
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
            const playerLat = parseFloat(position.coords.latitude);
            const playerLng = parseFloat(position.coords.longitude);
            const playerPos = [playerLat, playerLng];
            
            console.log("Position joueur récupérée:", playerLat, playerLng); // Pour debug

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

            // Dessiner ou mettre à jour la ligne de liaison pointillée entre le joueur et le monument mystère
            if (connectionLine) {
                connectionLine.setLatLngs([playerPos, [targetLat, targetLng]]);
            } else {
                connectionLine = L.polyline([playerPos, [targetLat, targetLng]], {
                    color: '#7C3AED',
                    weight: 4,
                    dashArray: '8, 8',
                    opacity: 0.8
                }).addTo(map);
            }

            // Ajuster la vue pour voir le joueur et la cible avec la ligne tracée
            const bounds = L.latLngBounds([playerPos, [targetLat, targetLng]]);
            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 18 });
        }, (err) => {
            console.error("Erreur GPS:", err);
            // En cas d'erreur de géolocalisation, la carte reste au moins sur le monument cible
            map.setView([targetLat, targetLng], 16);
        }, {
            enableHighAccuracy: true,
            maximumAge: 10000,
            timeout: 30000,
        });
    }

    // Multi-stage size invalidation pour corriger les bugs de rendu gris de Leaflet
    setTimeout(() => { if (map) map.invalidateSize(); }, 50);
    setTimeout(() => { if (map) map.invalidateSize(); }, 300);
    setTimeout(() => { if (map) map.invalidateSize(); }, 600);
    setTimeout(() => { if (map) map.invalidateSize(); }, 1200);
};

// Observer les changements pour initialiser la carte quand on arrive à l'étape GPS
watch(
    () => isTextValidated.value,
    async (newVal) => {
        if (newVal && !isGpsValidated.value) {

            await nextTick();

            setTimeout(() => {

                if (map) {
                    map.invalidateSize();
                } else {
                    initGameMap();
                }

            }, 500);
        }
    }
);

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
            // Forcer l'invalidation de la taille de la carte pour éviter le bug de l'écran gris
            nextTick(() => {
                if (map) {
                    setTimeout(() => { if (map) map.invalidateSize(); }, 50);
                    setTimeout(() => { if (map) map.invalidateSize(); }, 300);
                } else if (isTextValidated.value && !isGpsValidated.value) {
                    initGameMap();
                }
            });
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
    <PlayerLayout :title="enigme ? (isTextValidated ? enigme.titre : 'Énigme Mystère') : 'En jeu'">

        <div v-if="enigme" class="max-w-4xl mx-auto pt-32 pb-10 px-4 md:px-0">
            <!-- Cadre de Jeu Parchemin RPG Royal -->
            <div class="game-card parchment-scroll-violet overflow-hidden relative">
                
                <!-- Image de l'énigme / Sceau Cire Mystère Doré -->
                <div class="h-64 md:h-96 relative overflow-hidden">
                    <!-- Sceau de cire mystère si non validé textuellement -->
                    <div v-if="!isTextValidated && !enigme.is_bonus" 
                         class="absolute inset-0 bg-gradient-to-b from-purple-950 to-purple-900 flex flex-col items-center justify-center border-b-4 border-yellow-500 shadow-inner">
                        <!-- Emblème Cire & Dorures -->
                        <div class="w-32 h-32 rounded-full bg-gradient-to-tr from-yellow-400 to-yellow-600 border-4 border-yellow-200 flex items-center justify-center shadow-[0_0_30px_rgba(250,204,21,0.6)] animate-pulse">
                            <span class="text-7xl font-black text-purple-950 select-none">?</span>
                        </div>
                        <span class="text-yellow-400 text-xs font-black uppercase tracking-[0.3em] mt-4 drop-shadow">LIEU À RECONNAÎTRE</span>
                    </div>
                    
                    <img v-else :src="enigme.image || 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=800'"
                         class="w-full h-full object-cover border-b-4 border-yellow-500 shadow-lg">
                         
                    <div class="absolute top-6 left-6 flex flex-col gap-3">
                        <span v-if="enigme.is_bonus" class="px-5 py-2.5 bg-gradient-to-b from-purple-600 to-purple-800 text-white text-xs font-black uppercase rounded-lg tracking-widest shadow-xl border border-purple-400">
                             MODE BONUS
                        </span>
                        <span v-else class="px-5 py-2.5 bg-gradient-to-b from-yellow-400 to-yellow-600 text-white text-xs font-black uppercase rounded-lg tracking-widest shadow-xl border border-yellow-300">
                             Niveau {{ enigme.niveau }}
                        </span>
                    </div>
                </div>

                <!-- Section de Contenu -->
                <div class="p-8 md:p-12 relative z-10 space-y-8">
                    <!-- Titre Secret ou Révélé -->
                    <div class="text-center">
                        <h2 v-if="!isTextValidated && !enigme.is_bonus" 
                            class="text-4xl md:text-6xl font-black italic uppercase tracking-tighter text-purple-950 drop-shadow-sm leading-none">
                            ??? ??? ???
                        </h2>
                        <h2 v-else 
                            class="text-4xl md:text-6xl font-black italic uppercase tracking-tighter text-purple-950 drop-shadow-sm leading-none">
                            {{ enigme.titre }}
                        </h2>
                        <div class="w-24 h-1 bg-yellow-500 mx-auto mt-4 rounded-full"></div>
                    </div>

                    <!-- Grimoire text / Énoncé de l'Énigme -->
                    <div class="prose prose-slate max-w-none">
                        <p class="text-lg md:text-2xl font-bold text-slate-800 leading-relaxed italic bg-yellow-50/50 p-6 rounded-2xl border border-yellow-200/80 shadow-inner text-center">
                            " {{ enigme.contenu }} "
                        </p>
                    </div>

                    <!-- Actions Principales (Étapes de validation) -->
                    <div class="space-y-6">
                        
                        <!-- Changer de lieu (Passer) -->
                        <div v-if="autres_lieux && autres_lieux.length > 0 && !isGpsValidated" class="flex justify-end pr-2">
                            <button @click="changerLieu" class="text-purple-700 hover:text-purple-900 font-black uppercase text-sm tracking-widest flex items-center gap-2 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                Passer au lieu suivant
                            </button>
                        </div>

                        <!-- Réponse textuelle (Étape 1) -->
                        <div v-if="!isTextValidated" 
                             class="p-6 md:p-8 rounded-2xl border-2 border-yellow-300 bg-gradient-to-b from-yellow-50/40 to-yellow-100/30 shadow-inner space-y-4">
                            <div class="text-center">
                                <h3 class="text-xl font-black italic uppercase text-purple-900">RÉSOUDRE LE MYSTÈRE</h3>
                                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mt-1">Saisissez le mot-clé pour révéler l'identité du lieu</p>
                            </div>

                            <div class="relative max-w-xl mx-auto">
                                <input v-model="reponseTextuelle" type="text" placeholder="VOTRE RÉPONSE ICI..."
                                       class="w-full bg-yellow-50/70 border-2 border-yellow-400/50 rounded-xl py-5 px-8 text-sm md:text-base font-bold tracking-widest text-purple-950 focus:ring-4 focus:ring-purple-400/30 focus:border-purple-600 transition-all placeholder:text-purple-950/20 shadow-inner">
                                <button @click="soumettreReponse" :disabled="loading"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 w-12 h-12 rpg-btn-yellow text-white rounded-xl flex items-center justify-center hover:scale-110 active:scale-95 transition-transform shadow-lg">
                                    <div v-if="loading" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Validation GPS (Étape 2) -->
                        <div v-show="isTextValidated && !isGpsValidated" 
                             class="p-6 md:p-8 rounded-2xl border-2 border-purple-300/60 bg-gradient-to-b from-purple-50/20 to-purple-100/20 shadow-inner space-y-6">
                            <div class="text-center">
                                <h3 class="text-xl font-black italic uppercase text-purple-950">SE RENDRE SUR PLACE</h3>
                                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mt-1">Gagnez la position pour débloquer les points GPS !</p>
                            </div>

                            <!-- Carte de guidage -->
                            <div class="h-64 md:h-80 w-full bg-slate-100 rounded-xl overflow-hidden border-4 border-yellow-500 shadow-lg relative z-0">
                                <div
                                    ref="mapContainer"
                                    class="w-full"
                                    style="height:100%; min-height:320px;"
                                ></div>
                                <div class="absolute bottom-4 left-4 z-10 bg-purple-950 text-yellow-400 px-4 py-1.5 rounded-full text-[10px] font-black uppercase shadow-md border border-yellow-500/50">
                                    Rayon de validation: {{ enigme.rayon || 50 }}m
                                </div>
                            </div>

                            <button @click="validerGPS" :disabled="loading"
                                    class="w-full py-5 rpg-btn-violet text-white rounded-xl font-black text-xl md:text-2xl uppercase tracking-widest flex items-center justify-center space-x-4">
                                <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <div v-else class="w-8 h-8 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
                                <span>{{ loading ? 'Vérification en cours...' : 'Valider ma position !' }}</span>
                            </button>
                        </div>

                        <!-- Énigme résolue / Choix Bonus (Étape 3) -->
                        <div v-if="isGpsValidated" class="text-center py-8 space-y-6">
                            <h3 class="text-3xl font-black italic uppercase text-purple-950">Félicitations aventurier !</h3>
                            <p class="text-slate-600 font-bold uppercase text-xs tracking-widest">Vous avez triomphé des secrets de ce lieu mystique.</p>
                            
                            <div class="flex flex-col sm:flex-row gap-6 justify-center pt-4">
                                <button @click="faireChoixBonus(false)" class="px-10 py-5 rpg-btn-violet text-white rounded-xl font-black uppercase tracking-widest shadow-xl">
                                    Lieu suivant
                                </button>
                                <button @click="faireChoixBonus(true)" class="px-10 py-5 rpg-btn-yellow text-white rounded-xl font-black uppercase tracking-widest shadow-xl">
                                    En savoir plus (Bonus)
                                </button>
                            </div>
                        </div>

                        <!-- Section Indices (Uniquement visible AVANT que le lieu soit deviné) -->
                        <div v-if="!isTextValidated && enigme.indices && enigme.indices.length > 0" class="mt-8 pt-8 border-t-2 border-purple-200/50">
                            <h3 class="text-xl font-black italic uppercase text-purple-950 mb-6 flex items-center justify-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                BESOIN D'UNE PAROLE SAGE ?
                            </h3>
                            <div class="space-y-4 max-w-2xl mx-auto">
                                <div v-for="(indice, index) in enigme.indices" :key="indice.id" class="relative">
                                    <!-- Indice Débloqué -->
                                    <div v-if="isIndiceUnlocked(indice.id)" class="p-6 bg-yellow-50/50 rounded-xl border-2 border-yellow-300 shadow-inner">
                                        <p class="text-purple-900 text-xs font-black uppercase tracking-widest mb-2">Message révélé — Indice {{ index + 1 }}</p>
                                        <p class="text-slate-800 italic font-bold text-lg">" {{ unlockedIndicesContent[indice.id] || indice.contenu }} "</p>
                                    </div>
                                    <!-- Indice Bloqué -->
                                    <div v-else class="p-6 bg-purple-950/10 rounded-xl border-2 border-dashed border-purple-300 flex flex-col md:flex-row items-center justify-between gap-4">
                                        <div>
                                            <p class="text-purple-800 text-xs font-black uppercase tracking-widest mb-1">Indice {{ index + 1 }}</p>
                                            <p class="text-slate-500 font-bold text-sm">Contenu verrouillé dans les cryptes</p>
                                        </div>
                                        <button @click="debloquerIndice(indice)"
                                                class="shrink-0 px-6 py-4 rpg-btn-yellow text-white rounded-xl font-black uppercase tracking-widest flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
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

        <!-- Aucun défi actif -->
        <div v-else class="flex flex-col items-center justify-center h-full pt-20 px-6">
            <div class="parchment-scroll-violet p-10 text-center max-w-lg w-full relative z-10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-yellow-500 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <h2 class="text-3xl md:text-5xl font-black italic uppercase tracking-tighter text-purple-950 mb-4">Aucune quête active</h2>
                <p class="text-slate-600 text-sm md:text-base font-bold uppercase tracking-widest mb-8">Retournez au grimoire principal pour lancer une session</p>
                <Link :href="route('player.dashboard')" class="inline-block py-4 px-8 rpg-btn-yellow text-white rounded-xl font-black uppercase tracking-widest shadow-xl">
                    Menu Principal
                </Link>
            </div>
        </div>

        <!-- MODAL GLOBAL DE JEU -->
        <div v-if="modalState.show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" @click="closeModal"></div>
            <div class="game-modal-content relative w-full max-w-lg rounded-2xl p-1 border-2 shadow-[0_30px_60px_rgba(0,0,0,0.6)]"
                 :class="{
                     'bg-gradient-to-br from-green-400 to-green-600 border-green-300': modalState.type === 'success',
                     'bg-gradient-to-br from-red-400 to-red-600 border-red-300': modalState.type === 'error',
                     'bg-gradient-to-br from-blue-400 to-blue-600 border-blue-300': modalState.type === 'info'
                 }">
                <div class="bg-white rounded-xl p-8 md:p-10 text-center relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 w-40 h-40 opacity-10 rounded-full"
                          :class="{'bg-green-500': modalState.type === 'success', 'bg-red-500': modalState.type === 'error', 'bg-blue-500': modalState.type === 'info'}"></div>

                    <div class="w-24 h-24 mx-auto rounded-xl flex items-center justify-center mb-6 shadow-xl relative z-10"
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

                    <div v-if="modalState.content" class="text-left bg-slate-50 p-6 rounded-xl mb-8 border border-slate-200 prose prose-sm max-w-none relative z-10 max-h-48 overflow-y-auto">
                        <div v-html="modalState.content"></div>
                    </div>

                    <div v-if="modalState.isChoice" class="flex flex-col sm:flex-row gap-4 relative z-10">
                        <button @click="faireChoixBonus(false)" class="flex-1 py-5 bg-slate-100 text-slate-600 rounded-xl font-black uppercase tracking-widest hover:bg-slate-200 transition-all shadow-lg">
                            Lieu suivant
                        </button>
                        <button @click="faireChoixBonus(true)" class="flex-1 py-5 bg-[#7C3AED] text-white rounded-xl font-black uppercase tracking-widest shadow-lg hover:bg-purple-700 transition-all">
                            En savoir plus
                        </button>
                    </div>
                    <button v-else @click="closeModal('reload')"
                            class="w-full py-5 text-white rounded-xl font-black uppercase tracking-widest shadow-lg hover:scale-105 active:scale-95 transition-all relative z-10"
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
@import url('https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;700;900&display=swap');

h2, h3, button, span {
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
    border-bottom: 6px solid #4c1d95;
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
    border-bottom: 6px solid #78350f;
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
</style>
