<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, computed, ref, watch } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    ville: Object,
    lieu: Object,
    enigmes: Array
});

const page = usePage();
const activeSession = computed(() => page.props.active_session);

const mainEnigmes = computed(() => props.enigmes.filter(e => !e.is_bonus));

const showDurationModal = ref(false);
const selectedEnigme = ref(null);
const duration = ref(45);
const durationError = ref('');

watch(duration, (newVal) => {
    if (newVal >= 45) {
        durationError.value = '';
    }
});

const openDurationModal = (enigme) => {
    selectedEnigme.value = enigme;
    
    // Si une session est déjà active, on ne redemande pas la durée
    if (activeSession.value) {
        jouerEnigme();
    } else {
        showDurationModal.value = true;
    }
};

const scrollToChallenges = () => {
    const el = document.getElementById('challenges-section');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
};

const jouerEnigme = () => {
    if (!activeSession.value && duration.value < 45) {
        durationError.value = "La durée minimale est de 45 minutes.";
        return;
    }

    router.post(route('player.sessions.store'), {
        ville_id: props.ville.id,
        mode: 'cooperatif',
        enigme_id: selectedEnigme.value?.id,
        duree: duration.value
    }, {
        onSuccess: () => {
            showDurationModal.value = false;
        }
    });
};

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

            <div class="flex justify-center -mt-20 relative z-20">
                <button @click="mainEnigmes.length === 1 ? openDurationModal(mainEnigmes[0]) : scrollToChallenges()" 
                        v-if="mainEnigmes.length > 0"
                        class="px-12 py-6 bg-gradient-to-b from-yellow-400 to-yellow-600 text-white rounded-[2.5rem] font-black text-2xl uppercase tracking-widest shadow-[0_20px_40px_-10px_rgba(234,179,8,0.5)] hover:scale-105 active:scale-95 transition-all flex items-center gap-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ mainEnigmes.length === 1 ? 'Commencer l\'Aventure' : 'Choisir un Défi ci-dessous' }}
                </button>
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
                <p class="text-slate-600 text-lg font-bold leading-relaxed italic mb-10">
                    {{ lieu.description }}
                </p>

                <div id="challenges-section" class="space-y-6 scroll-mt-32">
                    <h4 class="text-xl font-black italic uppercase text-slate-400 tracking-widest">Choisissez votre défi</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="enigme in mainEnigmes" :key="enigme.id" 
                             @click="openDurationModal(enigme)"
                             class="enigme-card group bg-white p-8 rounded-[2rem] border-4 border-[#7C3AED]/20 hover:border-[#7C3AED] transition-all cursor-pointer relative overflow-hidden shadow-xl min-h-[250px] flex flex-col justify-between">
    
    <div class="absolute top-0 right-0 w-24 h-24 bg-[#7C3AED]/10 rounded-bl-[3rem] -mr-8 -mt-8"></div>
    
    <div class="relative z-10">
        <div class="flex justify-between items-start mb-6">
            <span class="px-5 py-2 bg-yellow-400 text-white text-xs font-black uppercase rounded-xl tracking-widest shadow-lg">
                NIVEAU {{ enigme.niveau }}
            </span>
            <div class="w-12 h-12 bg-[#7C3AED] text-white rounded-2xl flex items-center justify-center shadow-lg transform group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M9 5l7 7-7 7" /></svg>
            </div>
        </div>
        
        <h5 class="text-3xl font-black italic uppercase text-slate-800 mb-3 leading-tight group-hover:text-[#7C3AED] transition-colors">
            {{ enigme.titre }}
        </h5>
        <p class="text-slate-600 text-base font-bold leading-relaxed line-clamp-3 italic">
            "{{ enigme.contenu }}"
        </p>
    </div>

    <div class="relative z-10 mt-8 pt-6 border-t-2 border-slate-100 flex items-center justify-between">
        <span class="text-slate-400 font-black text-xs uppercase tracking-widest">
            Gain potentiel
        </span>
        <span class="text-[#7C3AED] font-black text-xl uppercase tracking-tighter italic">
            +{{ enigme.niveau * 1000 }} XP
        </span>
    </div>
</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Duration Selection Modal -->
        <div v-if="showDurationModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" @click="showDurationModal = false"></div>
            <div class="bg-white rounded-[3rem] p-10 max-w-md w-full relative z-10 text-center shadow-2xl border-4 border-yellow-400/20">
                <div class="w-24 h-24 bg-yellow-100 text-yellow-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                
                <h3 class="text-4xl font-black italic uppercase text-slate-800 mb-2 tracking-tighter">Préparatifs</h3>
                <p class="text-slate-500 font-bold mb-8 uppercase text-xs tracking-widest">Combien de temps durera votre quête ?</p>

                <div class="space-y-6 mb-10">
                    <div class="relative">
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest text-left ml-4">Durée de la session (min)</label>
                        <input type="number" v-model="duration" min="45"
                               class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl py-5 px-8 text-2xl font-black text-slate-800 focus:ring-4 focus:ring-yellow-400/20 focus:border-yellow-400 transition-all text-center">
                        <div class="absolute right-6 top-[55px] text-slate-300 font-black uppercase text-xs italic">min</div>
                    </div>
                    
                    <p v-if="durationError" class="text-red-500 font-black uppercase text-[10px] animate-bounce">{{ durationError }}</p>
                    <p v-else class="text-slate-400 font-bold text-[10px] uppercase">Minimum requis : 45 minutes</p>
                </div>

                <div class="flex gap-4">
                    <button @click="showDurationModal = false" class="flex-1 py-5 bg-slate-100 text-slate-600 rounded-2xl font-black uppercase tracking-widest hover:bg-slate-200 transition-all shadow-md">
                        Annuler
                    </button>
                    <button @click="jouerEnigme" class="flex-1 py-5 bg-gradient-to-b from-yellow-400 to-yellow-600 text-white rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-yellow-500/30 hover:scale-105 active:scale-95 transition-all">
                        C'est parti !
                    </button>
                </div>
            </div>
        </div>

    </PlayerLayout>
</template>

<style scoped>
h2, h3, h4, h5 { font-family: 'Bangers', cursive; }
</style>
