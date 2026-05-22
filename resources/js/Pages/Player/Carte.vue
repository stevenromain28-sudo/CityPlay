<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { onMounted, ref } from 'vue';
import gsap from 'gsap';
import { useMap } from '@/Composables/useMap';

const props = defineProps({
    lieu_actif: Object,
    lieux_valides: Array,
    ville: Object
});

const mapContainer = ref(null);
const { initMap, startTracking, centerOnUser, addPlaceMarker } = useMap();
const isFirstFix = ref(true);

onMounted(() => {
    // Initialisation de la carte Leaflet
    const map = initMap('map');
    
    // Ajouter les marqueurs pour les lieux déjà validés
    if (props.lieux_valides) {
        props.lieux_valides.forEach(lieu => {
            addPlaceMarker(lieu.latitude, lieu.longitude, lieu.nom, 'validated');
        });
    }

    // Démarrer le suivi GPS en temps réel et centrer sur le joueur
    startTracking((lat, lng) => {
        // Au premier fix GPS, on centre la carte sur l'utilisateur
        if (isFirstFix.value) {
            centerOnUser();
            isFirstFix.value = false;
        }
        console.log(`Position mise à jour : ${lat}, ${lng}`);
    });

    gsap.from('.map-overlay', {
        x: -100,
        opacity: 0,
        duration: 0.8,
        delay: 0.5,
        ease: 'power3.out'
    });
});
</script>

<template>
    <PlayerLayout :title="lieu_actif ? 'Carte - ' + lieu_actif.nom : 'Carte Interactive'">
        <!-- Zone de la carte Plein Écran -->
        <div class="absolute inset-0 z-0">
            <div id="map" class="w-full h-full bg-slate-900">
                <div class="flex flex-col items-center justify-center h-full">
                    <div class="w-32 h-32 bg-white/10 backdrop-blur-md rounded-full flex items-center justify-center shadow-xl mb-8 animate-bounce border border-white/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <h2 class="text-4xl font-black italic uppercase tracking-tighter text-white drop-shadow-md">Initialisation du <span class="text-yellow-400">GPS</span>...</h2>
                    <p class="text-slate-300 text-sm font-bold uppercase tracking-widest mt-4">Préparez-vous à explorer la zone</p>
                </div>
            </div>
        </div>
        
        <!-- Overlay UI (Décalé vers le bas à cause du HUD) -->
        <div class="map-overlay absolute top-24 md:top-28 left-4 md:left-8 z-10 space-y-4 pointer-events-none">
            <div class="bg-black/40 backdrop-blur-md p-5 md:p-6 rounded-3xl shadow-[0_10px_30px_rgba(0,0,0,0.5)] border border-white/10 max-w-xs pointer-events-auto">
                <h3 class="text-lg md:text-xl font-black italic uppercase text-white mb-2 drop-shadow-sm">Signal Actif</h3>
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse shadow-[0_0_10px_rgba(74,222,128,1)]"></div>
                    <p class="text-slate-300 text-[10px] font-black uppercase tracking-widest">Localisation en temps réel</p>
                </div>
                <p v-if="lieu_actif" class="mt-4 text-yellow-400 font-black text-xs uppercase tracking-widest italic opacity-80 border-t border-white/10 pt-3">Cible : {{ lieu_actif.nom }}</p>
            </div>

            <div class="bg-black/40 backdrop-blur-md p-5 md:p-6 rounded-3xl shadow-[0_10px_30px_rgba(0,0,0,0.5)] border border-white/10 max-w-xs pointer-events-auto">
                <h3 class="text-lg md:text-xl font-black italic uppercase text-white mb-4 drop-shadow-sm">Légende</h3>
                <div class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-4 h-4 bg-green-400 rounded-full border border-white shadow-[0_0_5px_rgba(74,222,128,0.8)]"></div>
                        <span class="text-[10px] font-bold uppercase text-slate-200">Zone Sécurisée (Validée)</span>
                    </div>
                    <div class="flex items-center space-x-3 opacity-50">
                        <div class="w-4 h-4 bg-[#7C3AED] rounded-full border border-white"></div>
                        <span class="text-[10px] font-bold uppercase text-slate-300">Zone Inconnue</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contrôles de zoom custom -->
        <div class="absolute bottom-8 md:bottom-10 right-4 md:right-10 z-10 flex flex-col space-y-4">
            <button @click="centerOnUser" class="w-12 h-12 md:w-16 md:h-16 bg-white/10 backdrop-blur-md border border-white/20 text-yellow-400 rounded-[1.5rem] shadow-xl flex items-center justify-center hover:bg-yellow-400 hover:text-slate-900 transition-all group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 md:h-8 md:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </button>
            <button class="w-12 h-12 md:w-16 md:h-16 bg-white/10 backdrop-blur-md border border-white/20 text-white rounded-[1.5rem] shadow-xl flex items-center justify-center hover:bg-white hover:text-slate-900 transition-all font-black text-2xl md:text-3xl pb-1">+</button>
            <button class="w-12 h-12 md:w-16 md:h-16 bg-white/10 backdrop-blur-md border border-white/20 text-white rounded-[1.5rem] shadow-xl flex items-center justify-center hover:bg-white hover:text-slate-900 transition-all font-black text-2xl md:text-4xl pb-1">-</button>
        </div>
    </PlayerLayout>
</template>

<style scoped>
h2, h3, button {
    font-family: 'Fredoka', sans-serif;
}
</style>
