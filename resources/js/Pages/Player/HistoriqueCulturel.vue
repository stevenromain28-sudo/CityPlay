<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    lieux_completes: Array,
});

onMounted(() => {
    gsap.from('.history-card', {
        y: 30,
        opacity: 0,
        stagger: 0.1,
        duration: 0.6,
        delay: 0.2
    });
});
</script>

<template>
    <PlayerLayout title="Historique Culturel">
        <div class="min-h-screen p-6 md:p-12">
            
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-6xl md:text-8xl font-black italic uppercase tracking-tighter text-white drop-shadow-[0_10px_20px_rgba(0,0,0,0.5)] mb-4">
                    <span class="text-yellow-400">HISTORIQUE</span>
                </h1>
                <p class="text-white/80 font-bold uppercase tracking-widest text-lg">
                    Lieux que vous avez explorés
                </p>
            </div>

            <!-- Liste des lieux -->
            <div v-if="lieux_completes.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-6xl mx-auto">
                
                <div v-for="lieu in lieux_completes" :key="lieu.id" class="history-card bg-white rounded-[3rem] overflow-hidden shadow-2xl border-4 border-yellow-400/20">
                    
                    <!-- Image du lieu -->
                    <div class="relative h-64 overflow-hidden">
                        <img :src="lieu.image_principale || 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=800'" 
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 to-transparent"></div>
                        
                        <div class="absolute bottom-6 left-6">
                            <span class="px-4 py-2 bg-green-500 text-white text-xs font-black uppercase rounded-xl tracking-widest shadow-lg">
                                ✓ EXPLORÉ
                            </span>
                            <h2 class="text-4xl font-black italic uppercase text-white mt-3">{{ lieu.nom }}</h2>
                            <p class="text-white/80 font-bold text-sm">{{ lieu.ville?.nom }}</p>
                        </div>
                    </div>

                    <!-- Contenu Culturel -->
                    <div v-if="lieu.contenu_culturel" class="p-8">
                        <h3 class="text-2xl font-black italic uppercase text-slate-800 mb-4 flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            {{ lieu.contenu_culturel.titre }}
                        </h3>
                        
                        <p class="text-slate-600 text-lg font-bold leading-relaxed italic mb-6">
                            {{ lieu.contenu_culturel.description }}
                        </p>

                        <!-- Images du contenu culturel -->
                        <div v-if="lieu.contenu_culturel.images && lieu.contenu_culturel.images.length > 0" class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                            <img v-for="(img, index) in lieu.contenu_culturel.images" :key="index" 
                                 :src="img" 
                                 class="w-full h-32 object-cover rounded-2xl shadow-lg">
                        </div>

                        <!-- Audio -->
                        <div v-if="lieu.contenu_culturel.audio" class="bg-yellow-50 p-4 rounded-2xl border-2 border-yellow-200">
                            <div class="flex items-center gap-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                                <audio controls class="flex-1">
                                    <source :src="lieu.contenu_culturel.audio" type="audio/mpeg">
                                </audio>
                            </div>
                        </div>
                    </div>

                    <!-- Si pas de contenu culturel -->
                    <div v-else class="p-8 text-center">
                        <div class="text-slate-400 text-6xl mb-4">📜</div>
                        <p class="text-slate-500 font-black italic uppercase text-lg">
                            Aucun contenu culturel pour ce lieu
                        </p>
                    </div>
                </div>

            </div>

            <!-- Si pas de lieux complétés -->
            <div v-else class="max-w-md mx-auto text-center bg-white/10 backdrop-blur-md p-12 rounded-[3rem] border-2 border-white/20">
                <div class="text-yellow-400 text-8xl mb-6">🗺️</div>
                <h2 class="text-4xl font-black italic uppercase text-white mb-4">
                    Aucun lieu exploré
                </h2>
                <p class="text-white/70 font-bold mb-8 uppercase tracking-widest text-sm">
                    Commencez une aventure pour découvrir l'histoire des lieux !
                </p>
                <Link :href="route('player.dashboard')" 
                      class="inline-block px-8 py-4 bg-gradient-to-b from-yellow-400 to-yellow-600 text-white rounded-2xl font-black uppercase tracking-widest shadow-lg hover:scale-105 transition-transform">
                    Retour au Dashboard
                </Link>
            </div>

        </div>
    </PlayerLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;700;900&display=swap');
h1, h2, h3 { font-family: 'Bangers', cursive; }
</style>
