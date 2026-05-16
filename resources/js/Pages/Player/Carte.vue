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
        <div class="h-[calc(100vh-12rem)] bg-white rounded-[3rem] shadow-xl shadow-blue-50 border border-blue-50 overflow-hidden relative">
            <!-- Zone de la carte -->
            <div id="map" class="absolute inset-0 z-0 bg-blue-50">
                <div class="flex flex-col items-center justify-center h-full">
                    <div class="w-32 h-32 bg-white rounded-full flex items-center justify-center shadow-xl mb-8 animate-bounce">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-[#7C3AED]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <h2 class="text-4xl font-black italic uppercase tracking-tighter text-slate-800">Chargement de la <span class="text-[#7C3AED]">Carte</span>...</h2>
                    <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mt-4">Préparez-vous à explorer la ville</p>
                </div>
            </div>
            
            <!-- Overlay UI -->
            <div class="map-overlay absolute top-10 left-10 z-10 space-y-4">
                <div class="bg-white/90 backdrop-blur-md p-6 rounded-3xl shadow-2xl border border-white max-w-xs">
                    <h3 class="text-xl font-black italic uppercase text-slate-800 mb-2">Ma Position</h3>
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">GPS Actif</p>
                    </div>
                    <p v-if="lieu_actif" class="mt-4 text-[#7C3AED] font-black text-xs uppercase tracking-widest italic opacity-60">Destination masquée : {{ lieu_actif.nom }}</p>
                </div>

                <div class="bg-white/90 backdrop-blur-md p-6 rounded-3xl shadow-2xl border border-white max-w-xs">
                    <h3 class="text-xl font-black italic uppercase text-slate-800 mb-4">Légende</h3>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                            <span class="text-[10px] font-bold uppercase text-slate-600">Lieu Validé</span>
                        </div>
                        <div class="flex items-center space-x-3 opacity-30">
                            <div class="w-4 h-4 bg-[#7C3AED] rounded-full"></div>
                            <span class="text-[10px] font-bold uppercase text-slate-600">Lieu Masqué</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contrôles de zoom custom -->
            <div class="absolute bottom-10 right-10 z-10 flex flex-col space-y-4">
                <button @click="centerOnUser" class="w-14 h-14 bg-white text-[#7C3AED] rounded-2xl shadow-2xl flex items-center justify-center hover:bg-[#7C3AED] hover:text-white transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </button>
                <button class="w-14 h-14 bg-white text-[#7C3AED] rounded-2xl shadow-2xl flex items-center justify-center hover:bg-[#7C3AED] hover:text-white transition-all font-black text-2xl">+</button>
                <button class="w-14 h-14 bg-white text-[#7C3AED] rounded-2xl shadow-2xl flex items-center justify-center hover:bg-[#7C3AED] hover:text-white transition-all font-black text-2xl">-</button>
            </div>
        </div>
    </PlayerLayout>
</template>

<style scoped>
h2, h3, button {
    font-family: 'Bangers', cursive;
}
</style>
