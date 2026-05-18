<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    ville: Object,
    lieu: Object,
    enigmes: Array
});

onMounted(() => {
    gsap.from('.lieu-header', { y: -30, opacity: 0, duration: 0.8 });
    gsap.from('.stat-card', { scale: 0.9, opacity: 0, stagger: 0.2, duration: 0.6, delay: 0.3 });
});
</script>

<template>
    <PlayerLayout :title="lieu.nom">
        <div class="space-y-12">
            <!-- Header du Lieu -->
            <div class="lieu-header relative h-96 rounded-[3rem] overflow-hidden shadow-2xl">
                <img :src="lieu.image_principale || 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=800'" 
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/20 to-transparent"></div>
                
                <div class="absolute bottom-12 left-12 right-12">
                    <span class="px-6 py-3 bg-yellow-400 text-white text-xs font-black uppercase rounded-2xl tracking-widest shadow-xl mb-6 inline-block">
                        DÉCOUVRIR LE LIEU
                    </span>
                    <h2 class="text-6xl md:text-8xl font-black italic uppercase tracking-tighter text-white leading-none">{{ lieu.nom }}</h2>
                </div>
            </div>

            <!-- Stats & Infos -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <div class="stat-card bg-white p-8 rounded-[2.5rem] shadow-xl border border-blue-50">
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Difficulté</h4>
                    <div class="flex space-x-2">
                        <div v-for="i in 3" :key="i" 
                             class="w-8 h-8 rounded-lg flex items-center justify-center font-black text-xl"
                             :class="i <= lieu.difficulte ? 'bg-[#7C3AED] text-white' : 'bg-slate-100 text-slate-300'">
                            {{ i }}
                        </div>
                    </div>
                </div>

                <div class="stat-card bg-white p-8 rounded-[2.5rem] shadow-xl border border-blue-50">
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Énigmes</h4>
                    <p class="text-4xl font-black italic text-[#7C3AED]">{{ lieu.enigmes_count }} DÉFIS</p>
                </div>

                <div class="stat-card bg-white p-8 rounded-[2.5rem] shadow-xl border border-blue-50">
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Temps Estimé</h4>
                    <p class="text-4xl font-black italic text-yellow-400">{{ lieu.duree_estimee }} MIN</p>
                </div>
            </div>

            <!-- Description -->
            <div class="bg-white p-12 rounded-[3rem] shadow-xl border border-blue-50">
                <h3 class="text-3xl font-black italic uppercase text-slate-800 mb-6">À propos de <span class="text-[#7C3AED]">ce lieu</span></h3>
                <p class="text-slate-600 text-lg font-bold leading-relaxed italic">
                    {{ lieu.description }}
                </p>
            </div>
        </div>
    </PlayerLayout>
</template>

<style scoped>
h2, h3, h4, span { font-family: 'Bangers', cursive; }
</style>
