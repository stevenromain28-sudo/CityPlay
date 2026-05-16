<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { onMounted, onUnmounted, ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import gsap from 'gsap';
import { useGameStore } from '@/Stores/game';
import { webSocketService } from '@/Services/websocket';

const props = defineProps({
    session: Object,
    enigme: Object,
});

const gameStore = useGameStore();
const reponseTextuelle = ref('');
const loading = ref(false);

onMounted(() => {
    // Initialiser le store avec les données réelles
    gameStore.setSession(props.session);
    if (props.enigme) {
        gameStore.setEnigmeActive(props.enigme);
    }

    // Connexion WebSocket pour le temps réel
    webSocketService.joinSession(props.session.id, {
        onJoined: (users) => gameStore.updateJoueurs(users),
        onUserJoining: (user) => gameStore.updateJoueurs([...gameStore.joueursConnectes, user]),
        onUserLeaving: (user) => gameStore.updateJoueurs(gameStore.joueursConnectes.filter(u => u.id !== user.id)),
        onEnigmeResolue: (data) => {
            // Logique de notification ou mise à jour si un partenaire résout l'énigme
            console.log('Énigme résolue par un partenaire:', data);
        }
    });

    gsap.from('.game-card', {
        scale: 0.9,
        opacity: 0,
        duration: 0.8,
        ease: 'back.out(1.7)'
    });
});

onUnmounted(() => {
    webSocketService.leaveSession(props.session.id);
    gameStore.stopTimer();
});

const validerGPS = () => {
    loading.value = true;
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition((position) => {
            router.post(route('player.game.validate.gps', { 
                session: props.session.id, 
                enigme: props.enigme.id 
            }), {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            }, {
                onFinish: () => loading.value = false
            });
        }, (error) => {
            alert("Erreur GPS : " + error.message);
            loading.value = false;
        });
    }
};

const soumettreReponse = () => {
    router.post(route('player.game.submit.answer', { 
        session: props.session.id, 
        enigme: props.enigme.id 
    }), {
        reponse: reponseTextuelle.value
    });
};
</script>

<template>
    <PlayerLayout :title="enigme ? enigme.titre : 'En jeu'">
        <div v-if="enigme" class="max-w-4xl mx-auto py-10">
            <div class="game-card bg-white rounded-[3rem] shadow-2xl border border-blue-50 overflow-hidden relative">
                <!-- Image de l'énigme -->
                <div class="h-80 relative overflow-hidden">
                    <img :src="enigme.image || 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=800'" 
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent"></div>
                    
                    <div class="absolute top-10 left-10">
                        <span class="px-6 py-3 bg-yellow-400 text-white text-xs font-black uppercase rounded-2xl tracking-widest shadow-xl">
                            Niveau {{ enigme.niveau }}
                        </span>
                    </div>
                </div>

                <div class="p-12 -mt-20 relative z-10">
                    <h2 class="text-5xl font-black italic uppercase tracking-tighter text-slate-800 mb-6">{{ enigme.titre }}</h2>
                    
                    <div class="prose prose-slate max-w-none mb-12">
                        <p class="text-xl font-bold text-slate-600 leading-relaxed italic">
                            "{{ enigme.contenu }}"
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-8">
                        <!-- Validation GPS -->
                        <div class="bg-blue-50 p-8 rounded-[2.5rem] border border-blue-100">
                            <h3 class="text-xl font-black italic uppercase text-slate-800 mb-4">Étape 1 : Se rendre sur place</h3>
                            <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mb-6">Utilisez votre GPS pour confirmer votre présence</p>
                            
                            <button @click="validerGPS" :disabled="loading"
                                    class="w-full py-6 bg-[#7C3AED] text-white rounded-3xl font-black text-xl uppercase tracking-widest shadow-xl hover:scale-105 active:scale-95 transition-all flex items-center justify-center space-x-4">
                                <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <div v-else class="w-8 h-8 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
                                <span>{{ loading ? 'Vérification...' : 'Je suis arrivé !' }}</span>
                            </button>
                        </div>

                        <!-- Réponse textuelle (si applicable) -->
                        <div v-if="enigme.indices && enigme.indices.length > 0" class="bg-yellow-50 p-8 rounded-[2.5rem] border border-yellow-100">
                            <h3 class="text-xl font-black italic uppercase text-slate-800 mb-4">Étape 2 : Résoudre le mystère</h3>
                            <div class="relative">
                                <input v-model="reponseTextuelle" type="text" placeholder="VOTRE RÉPONSE ICI..." 
                                       class="w-full bg-white border-none rounded-2xl py-6 px-8 text-xs font-bold tracking-widest text-slate-600 focus:ring-2 focus:ring-yellow-400 transition-all placeholder:text-slate-300 shadow-inner">
                                <button @click="soumettreReponse"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-yellow-400 text-white rounded-xl flex items-center justify-center hover:scale-110 transition-transform shadow-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </button>
                            </div>
                            <p class="mt-4 text-yellow-600/60 text-[10px] font-black uppercase tracking-widest italic">Indice : {{ enigme.indices[0].contenu }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center h-[calc(100vh-12rem)]">
            <h2 class="text-4xl font-black italic uppercase tracking-tighter text-slate-800 mb-4">Aucune énigme active</h2>
            <p class="text-slate-400 text-sm font-bold uppercase tracking-widest">Commencez une session pour voir vos défis</p>
        </div>
    </PlayerLayout>
</template>

<style scoped>
h2, h3, button, span {
    font-family: 'Bangers', cursive;
}
</style>
