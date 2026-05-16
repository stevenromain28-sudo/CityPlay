<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    stats: Object,
    recent_sessions: Array,
    villes_disponibles: Array,
    ville_detectee: Object,
    lieux: Array,
    localisation_requise: Boolean
});

const detectant = ref(false);
const messageErreur = ref(null);

onMounted(() => {
    if (props.localisation_requise) {
        obtenirLocalisation();
    }

    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
    tl.from('.city-header', { y: -50, opacity: 0, duration: 0.8 })
      .from('.lieu-card', { scale: 0.8, opacity: 0, stagger: 0.1, duration: 0.6 }, '-=0.4');
});

const obtenirLocalisation = () => {
    detectant.value = true;
    messageErreur.value = null;
    
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition((position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            console.log("Position détectée:", lat, lng);
            
            router.get(route('player.dashboard'), {
                lat: lat,
                lng: lng
            }, {
                preserveState: true,
                onSuccess: () => {
                    console.log("Dashboard rechargé avec succès");
                },
                onError: (errors) => {
                    console.error("Erreur Inertia:", errors);
                    messageErreur.value = "Erreur lors du rechargement des données.";
                },
                onFinish: () => detectant.value = false
            });
        }, (error) => {
            console.error("Erreur Geolocation:", error);
            if (error.code === 1) {
                messageErreur.value = "Vous devez autoriser l'accès à votre position pour jouer.";
            } else if (error.code === 2) {
                messageErreur.value = "Position non disponible. Vérifiez votre signal GPS.";
            } else {
                messageErreur.value = "Délai d'attente dépassé ou erreur inconnue.";
            }
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

const allerAuLieu = (lieuId) => {
    router.get(route('player.lieu.dashboard', { 
        ville: props.ville_detectee.id, 
        lieu: lieuId 
    }));
};
</script>

<template>
    <Head title="Exploration" />

    <div class="min-h-screen bg-[#F0F7FF] font-sans relative overflow-x-hidden">
        <!-- Background Decor -->
        <div class="absolute inset-0 z-0 opacity-5 pointer-events-none">
            <img src="/images/backgrounds/img1.jpg" class="w-full h-full object-cover">
        </div>

        <main class="relative z-10 max-w-7xl mx-auto px-6 py-12">
            <!-- Header Ville -->
            <header class="city-header mb-16 text-center md:text-left flex flex-col md:flex-row items-center justify-between gap-8">
                <div v-if="ville_detectee">
                    <span class="px-4 py-2 bg-yellow-400 text-white text-[10px] font-black uppercase rounded-xl tracking-widest mb-4 inline-block shadow-lg shadow-yellow-200">BIENVENUE À</span>
                    <h1 class="text-6xl md:text-8xl font-black italic uppercase tracking-tighter text-[#7C3AED] leading-none">{{ ville_detectee.nom }}</h1>
                    <p class="text-slate-400 text-lg font-bold italic mt-4 max-w-2xl">{{ ville_detectee.description }}</p>
                </div>
                <div v-else class="text-center w-full py-20">
                    <div v-if="detectant" class="animate-pulse">
                        <h2 class="text-4xl font-black italic uppercase text-slate-300">Détection de votre ville...</h2>
                    </div>
                    <div v-else>
                        <h2 class="text-4xl font-black italic uppercase text-slate-800">Choisissez une destination</h2>
                    </div>
                </div>

                <!-- Stats Rapides -->
                <div v-if="ville_detectee" class="flex gap-6">
                    <div class="bg-white p-6 rounded-[2rem] shadow-xl border border-blue-50 text-center">
                        <p class="text-3xl font-black italic text-[#7C3AED]">{{ lieux.length }}</p>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Lieux</p>
                    </div>
                    <div class="bg-white p-6 rounded-[2rem] shadow-xl border border-blue-50 text-center">
                        <p class="text-3xl font-black italic text-yellow-400">{{ stats.total_score }}</p>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Votre XP</p>
                    </div>
                </div>
            </header>

            <!-- Liste des Lieux -->
            <div v-if="ville_detectee" class="space-y-10">
                <h2 class="text-4xl font-black italic uppercase tracking-tighter text-slate-800">Lieux à <span class="text-[#7C3AED]">Découvrir</span></h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                    <div v-for="lieu in lieux" :key="lieu.id" 
                         @click="allerAuLieu(lieu.id)"
                         class="lieu-card group bg-white rounded-[3rem] overflow-hidden shadow-2xl border-2 border-white hover:border-[#7C3AED] transition-all cursor-pointer relative h-[30rem]">
                        
                        <img :src="lieu.image_principale || 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=800'" 
                             class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/20 to-transparent"></div>

                        <div class="absolute bottom-10 left-10 right-10">
                            <div class="flex items-center space-x-3 mb-4">
                                <span class="px-4 py-2 bg-yellow-400 text-white text-[10px] font-black uppercase rounded-xl tracking-widest">
                                    {{ lieu.enigmes_count }} ÉNIGMES
                                </span>
                                <span class="px-4 py-2 bg-white/20 backdrop-blur-md text-white text-[10px] font-black uppercase rounded-xl tracking-widest border border-white/10">
                                    {{ lieu.difficulte }}/3 DIFFICLE
                                </span>
                            </div>
                            <h3 class="text-4xl font-black italic uppercase text-white tracking-tighter leading-none group-hover:text-yellow-400 transition-colors">
                                {{ lieu.nom }}
                            </h3>
                            <p class="text-white/70 text-sm font-bold mt-3 line-clamp-2 leading-tight italic">
                                {{ lieu.description }}
                            </p>
                        </div>

                        <div class="absolute top-10 right-10 opacity-0 group-hover:opacity-100 transition-all duration-300 -translate-y-4 group-hover:translate-y-0">
                            <div class="w-14 h-14 bg-white text-[#7C3AED] rounded-2xl flex items-center justify-center shadow-2xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State / Erreurs -->
            <div v-else-if="!detectant" class="bg-white rounded-[3rem] p-20 text-center shadow-xl border border-blue-50">
                <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h3 class="text-3xl font-black italic uppercase text-slate-800 mb-4">
                    {{ messageErreur ? 'Erreur de Localisation' : 'Hors Zone de Jeu' }}
                </h3>
                <p class="text-slate-400 text-lg font-bold uppercase tracking-widest max-w-md mx-auto mb-10">
                    {{ messageErreur || "Vous n'êtes à proximité d'aucune ville enregistrée." }}
                </p>
                
                <div v-if="messageErreur" class="space-y-4">
                    <button @click="obtenirLocalisation" class="px-8 py-4 bg-[#7C3AED] text-white rounded-2xl font-black uppercase tracking-widest shadow-lg hover:scale-105 transition-all">
                        Réessayer la détection
                    </button>
                </div>

                <Link :href="route('logout')" method="post" as="button" class="text-[#7C3AED] font-black uppercase tracking-widest hover:underline mt-8 block mx-auto">Se déconnecter</Link>
            </div>
        </main>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;700;900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }
h1, h2, h3, button, span { font-family: 'Bangers', cursive; }
</style>
