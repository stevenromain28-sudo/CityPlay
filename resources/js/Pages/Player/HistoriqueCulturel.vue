<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    lieux_completes: Array,
});

onMounted(() => {
    gsap.from('.history-card', {
        y: 40,
        opacity: 0,
        stagger: 0.15,
        duration: 0.8,
        ease: 'back.out(1.2)'
    });
});

// Immersive story player states
const activeStory = ref(null);
const scrollContainer = ref(null);
const audioPlayer = ref(null);

const isScrolling = ref(false);
const scrollSpeed = ref(1.0);
const isAudioPlaying = ref(false);
let animationFrameId = null;

const startAutoScroll = () => {
    isScrolling.value = true;
    
    const scroll = () => {
        if (!isScrolling.value || !scrollContainer.value) return;
        
        scrollContainer.value.scrollTop += scrollSpeed.value;
        
        // Check if we hit the bottom (with a small buffer)
        if (scrollContainer.value.scrollTop + scrollContainer.value.clientHeight >= scrollContainer.value.scrollHeight - 2) {
            isScrolling.value = false;
        } else {
            animationFrameId = requestAnimationFrame(scroll);
        }
    };
    
    animationFrameId = requestAnimationFrame(scroll);
};

const pauseAutoScroll = () => {
    isScrolling.value = false;
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
    }
};

const toggleAutoScroll = () => {
    if (isScrolling.value) {
        pauseAutoScroll();
    } else {
        startAutoScroll();
    }
};

const setSpeed = (speed) => {
    scrollSpeed.value = speed;
};

const rewindScroll = () => {
    if (scrollContainer.value) {
        scrollContainer.value.scrollTop = 0;
    }
    pauseAutoScroll();
    setTimeout(() => {
        startAutoScroll();
    }, 100);
};

const toggleAudio = () => {
    if (!audioPlayer.value) return;
    if (isAudioPlaying.value) {
        audioPlayer.value.pause();
        isAudioPlaying.value = false;
    } else {
        audioPlayer.value.play().then(() => {
            isAudioPlaying.value = true;
        }).catch(err => {
            console.error("Playback error:", err);
        });
    }
};

const openStory = (lieu) => {
    activeStory.value = lieu;
    isScrolling.value = false;
    isAudioPlaying.value = false;
    
    // Animate grimoire opening
    setTimeout(() => {
        if (scrollContainer.value) {
            scrollContainer.value.scrollTop = 0;
        }
        
        gsap.fromTo('.grimoire-parchment', 
            { scaleY: 0, opacity: 0 },
            { scaleY: 1, opacity: 1, duration: 0.6, ease: 'power3.out' }
        );
        
        startAutoScroll();
        
        // Auto play audio if available
        setTimeout(() => {
            if (audioPlayer.value) {
                audioPlayer.value.play().then(() => {
                    isAudioPlaying.value = true;
                }).catch(err => console.log("Audio autoplay prevented"));
            }
        }, 800);
    }, 50);
};

const closeStory = () => {
    pauseAutoScroll();
    isAudioPlaying.value = false;
    if (audioPlayer.value) {
        audioPlayer.value.pause();
    }
    
    gsap.to('.grimoire-parchment', {
        scaleY: 0,
        opacity: 0,
        duration: 0.4,
        ease: 'power3.in',
        onComplete: () => {
            activeStory.value = null;
        }
    });
};

onUnmounted(() => {
    pauseAutoScroll();
});
</script>

<template>
    <PlayerLayout title="Grimoire Culturel">
        <div class="min-h-screen p-6 md:p-12 relative pb-24">
            
            <!-- Header -->
            <div class="text-center mb-12 pt-10">
                <h1 class="text-6xl md:text-8xl font-black italic uppercase tracking-tighter text-white drop-shadow-[0_10px_20px_rgba(0,0,0,0.6)] mb-4 select-none">
                    <span class="text-yellow-400">GRIMOIRE</span> <span class="text-white">CULTUREL</span>
                </h1>
                <p class="text-yellow-100/80 font-bold uppercase tracking-[0.25em] text-sm md:text-lg select-none">
                    Chroniques et légendes des édifices du Royaume
                </p>
                <div class="w-32 h-1.5 bg-gradient-to-r from-transparent via-yellow-400 to-transparent mx-auto mt-4 rounded-full"></div>
            </div>

            <!-- Liste des lieux complétés -->
            <div v-if="lieux_completes.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                
                <div v-for="lieu in lieux_completes" :key="lieu.id" 
                     class="history-card history-card-scroll overflow-hidden shadow-2xl relative flex flex-col justify-between group">
                    
                    <!-- Cover Image preview -->
                    <div class="relative h-48 overflow-hidden border-b-2 border-yellow-500/30">
                        <img :src="lieu.image_principale || 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=800'" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-purple-950/80 to-transparent"></div>
                        
                        <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between">
                            <span class="px-3 py-1 bg-yellow-500 text-purple-950 text-[9px] font-black uppercase rounded-lg tracking-widest shadow-md">
                                ✓ DÉCOUVERT
                            </span>
                            <span class="text-white/95 text-[10px] font-black uppercase tracking-wider bg-purple-950/60 px-2.5 py-1 rounded-md border border-white/10">
                                {{ lieu.ville?.nom }}
                            </span>
                        </div>
                    </div>

                    <!-- Inner parchment header -->
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div class="space-y-2">
                            <h2 class="text-3xl font-black italic uppercase text-purple-950 leading-none drop-shadow-sm select-none">{{ lieu.nom }}</h2>
                            
                            <!-- Subtitle content -->
                            <p v-if="lieu.contenu_culturel" class="text-yellow-700 font-bold uppercase text-xs tracking-wider line-clamp-1 italic">
                                "{{ lieu.contenu_culturel.titre }}"
                            </p>
                            <p v-else class="text-slate-400 font-bold uppercase text-[10px] tracking-wider italic">
                                Aucun récit de conté pour ce monument
                            </p>
                            
                            <p v-if="lieu.contenu_culturel" class="text-slate-700 text-xs font-bold leading-relaxed line-clamp-3 italic pt-2">
                                {{ lieu.contenu_culturel.description }}
                            </p>
                        </div>

                        <!-- Action Button inside card -->
                        <div class="pt-6 flex justify-end">
                            <button v-if="lieu.contenu_culturel" 
                                    @click="openStory(lieu)" 
                                    class="w-full py-3 bg-gradient-to-b from-purple-600 to-purple-800 text-white rounded-xl font-black uppercase tracking-widest text-xs border-b-4 border-purple-950 shadow-md hover:brightness-110 active:translate-y-0.5 active:border-b-2 transition-all flex items-center justify-center gap-2">
                                📖 Lecture Immersive
                            </button>
                            <div v-else class="w-full py-3 bg-slate-200/50 text-slate-400 text-center rounded-xl font-black uppercase tracking-widest text-[10px]">
                                Incomplet
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Si aucun lieu complété -->
            <div v-else class="max-w-md mx-auto text-center parchment-scroll-violet p-12 rounded-[2rem] border-2 shadow-2xl relative z-10">
                <div class="text-yellow-500 text-7xl mb-6">🗺️</div>
                <h2 class="text-4xl font-black italic uppercase text-purple-950 mb-4 select-none">
                    Grimoire Vierge
                </h2>
                <p class="text-slate-600 font-bold mb-8 uppercase tracking-widest text-xs leading-relaxed">
                    Vous n'avez pas encore résolu d'énigmes sur un lieu ! Partez à l'aventure pour graver ses chroniques secrètes dans votre grimoire.
                </p>
                <Link :href="route('player.dashboard')" 
                      class="inline-block px-8 py-4 bg-gradient-to-b from-yellow-400 to-yellow-600 text-white rounded-xl font-black uppercase tracking-widest shadow-lg hover:scale-105 active:scale-95 transition-transform border-b-4 border-yellow-800">
                    Lancer la Quête
                </Link>
            </div>

            <!-- IMMERSIVE STORY CINEMATIC OVERLAY -->
            <div v-if="activeStory" class="fixed inset-0 z-[1000] flex flex-col items-center justify-center p-2 md:p-6 bg-slate-950/95 backdrop-blur-md">
                
                <!-- Majestic ancient vertical scroll roll wrapper -->
                <div class="grimoire-parchment relative w-full max-w-2xl h-[78vh] flex flex-col justify-between overflow-hidden origin-center">
                    
                    <!-- Close Button (à l'intérieur du conteneur du parchemin) -->
                    <button @click="closeStory" 
                            class="absolute top-8 right-2 z-[1020] bg-amber-700 hover:bg-amber-800 text-white rounded-full w-10 h-10 flex items-center justify-center shadow-lg transition-transform hover:scale-105 active:scale-95 border-2 border-amber-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    
                    <!-- Top Scroll Cylinder Roll -->
                    <div class="h-6 bg-gradient-to-r from-amber-900 via-amber-600 to-amber-900 rounded-full shadow-lg border-2 border-amber-950 flex justify-between px-6 items-center relative z-20">
                        <div class="w-3 h-3 bg-yellow-400 rounded-full border border-yellow-200"></div>
                        <div class="text-[8px] font-black tracking-[0.25em] text-yellow-300 uppercase">RÉCIT DU MONUMENT DÉVERROUILLÉ</div>
                        <div class="w-3 h-3 bg-yellow-400 rounded-full border border-yellow-200"></div>
                    </div>

                    <!-- Scroll Parchment Paper body -->
                    <div class="flex-1 bg-gradient-to-b from-[#fffcf5] via-[#f7eed8] to-[#fffcf5] my-1 border-x-4 border-amber-900/20 shadow-inner relative flex flex-col overflow-hidden">
                        
                        <!-- Top & Bottom smooth content fading gradients -->
                        <div class="absolute top-0 left-0 right-0 h-16 bg-gradient-to-b from-[#fffcf5] to-transparent z-10 pointer-events-none"></div>
                        <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-[#fffcf5] to-transparent z-10 pointer-events-none"></div>

                        <!-- Auto scrolling area -->
                        <div ref="scrollContainer" 
                             class="flex-1 overflow-y-auto hide-scrollbar px-6 py-12 md:px-12 md:py-16 space-y-8 select-none">
                            
                            <!-- Story Header -->
                            <div class="text-center space-y-3 pt-6">
                                <span class="px-4 py-1 bg-amber-900/10 text-amber-900 rounded-full text-xs font-black uppercase tracking-widest">{{ activeStory.ville?.nom }}</span>
                                <h2 class="text-4xl md:text-5xl font-black italic uppercase text-amber-950 tracking-tighter leading-none select-none">{{ activeStory.nom }}</h2>
                                <h3 class="text-xl md:text-2xl font-black italic text-yellow-600 uppercase select-none">{{ activeStory.contenu_culturel.titre }}</h3>
                                <div class="w-20 h-1 bg-yellow-600/70 mx-auto rounded-full"></div>
                            </div>

                            <!-- Showcase image inside scroll -->
                            <div v-if="activeStory.image_principale" 
                                 class="w-full h-48 md:h-64 rounded-2xl overflow-hidden shadow-lg border-4 border-amber-950/20 my-4">
                                <img :src="activeStory.image_principale" class="w-full h-full object-cover" />
                            </div>

                            <!-- Story narration text with drops caps -->
                            <div class="text-amber-950 font-sans text-base md:text-xl font-bold leading-relaxed text-justify px-2 italic pt-4">
                                <span class="float-left text-5xl md:text-7xl font-black text-amber-950 leading-none mr-2 font-serif select-none">
                                    {{ activeStory.contenu_culturel.description.charAt(0) }}
                                </span>
                                {{ activeStory.contenu_culturel.description.substring(1) }}
                            </div>

                            <!-- Extra images (if defined) -->
                            <div v-if="activeStory.contenu_culturel.images && activeStory.contenu_culturel.images.length > 0" 
                                 class="grid grid-cols-2 md:grid-cols-3 gap-4 my-6">
                                <img v-for="(img, idx) in activeStory.contenu_culturel.images" :key="idx" 
                                     :src="img" 
                                     class="w-full h-24 object-cover rounded-xl border border-amber-900/10 shadow-md" />
                            </div>

                            <!-- Final scroll spacer to allow total credit roll scrolling -->
                            <div class="h-48"></div>
                        </div>

                        <!-- Invisible Audio Player -->
                        <audio v-if="activeStory.contenu_culturel.audio" 
                               ref="audioPlayer" 
                               :src="activeStory.contenu_culturel.audio" 
                               @ended="isAudioPlaying = false"></audio>
                    </div>

                    <!-- Bottom Scroll Cylinder Roll -->
                    <div class="h-6 bg-gradient-to-r from-amber-900 via-amber-600 to-amber-900 rounded-full shadow-lg border-2 border-amber-950 flex justify-between px-6 items-center relative z-20">
                        <div class="w-3 h-3 bg-yellow-400 rounded-full border border-yellow-200"></div>
                        <div class="text-[8px] font-black tracking-[0.25em] text-yellow-300 uppercase">CHRONIQUES ET LÉGENDES</div>
                        <div class="w-3 h-3 bg-yellow-400 rounded-full border border-yellow-200"></div>
                    </div>

                </div>

                <!-- CONTROLLER HUD PANEL -->
                <div class="w-full max-w-2xl mt-4 bg-slate-900/90 rounded-[2rem] p-4 flex flex-wrap items-center justify-between gap-4 border border-white/10 relative z-30 shadow-2xl">
                    <div class="flex items-center space-x-3">
                        <!-- Play/Pause Autoscroll -->
                        <button @click="toggleAutoScroll" 
                                class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg transition-transform hover:scale-105 active:scale-95 border-b-4"
                                :class="isScrolling ? 'bg-yellow-500 text-purple-950 border-yellow-700' : 'bg-purple-600 text-white border-purple-900'">
                            <svg v-if="isScrolling" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6" /></svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                        </button>

                        <!-- Speed selector options -->
                        <div class="flex items-center space-x-1 bg-white/5 rounded-lg p-1.5 border border-white/10">
                            <span class="text-[9px] text-white/50 uppercase font-black tracking-wider px-2 select-none">Vitesse :</span>
                            <button @click="setSpeed(0.4)" class="px-2 py-1 rounded text-xs font-black uppercase transition-all" :class="scrollSpeed === 0.4 ? 'bg-yellow-500 text-purple-950' : 'text-white hover:bg-white/10'">Lente</button>
                            <button @click="setSpeed(0.8)" class="px-2 py-1 rounded text-xs font-black uppercase transition-all" :class="scrollSpeed === 0.8 ? 'bg-yellow-500 text-purple-950' : 'text-white hover:bg-white/10'">Normal</button>
                            <button @click="setSpeed(1.6)" class="px-2 py-1 rounded text-xs font-black uppercase transition-all" :class="scrollSpeed === 1.6 ? 'bg-yellow-500 text-purple-950' : 'text-white hover:bg-white/10'">Rapide</button>
                        </div>
                    </div>

                    <!-- Audio Narrative player toggle -->
                    <div v-if="activeStory.contenu_culturel.audio" class="flex items-center space-x-3">
                        <button @click="toggleAudio" 
                                class="px-5 py-3 rounded-xl flex items-center space-x-2 text-xs font-black uppercase tracking-widest transition-all shadow-md active:translate-y-1" 
                                :class="isAudioPlaying ? 'bg-green-500 text-white' : 'bg-[#1DA1F2] text-white'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                            </svg>
                            <span>{{ isAudioPlaying ? 'NARRATION ACTIVE' : 'NARRATION AUDIO' }}</span>
                        </button>
                    </div>

                    <!-- Rewind / Start Over -->
                    <button @click="rewindScroll" 
                            class="px-4 py-3 bg-white/5 hover:bg-white/10 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all border border-white/10">
                        Réécouter / Relire
                    </button>
                </div>
            </div>

        </div>
    </PlayerLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;700;900&display=swap');

h1, h2, h3, button, span {
    font-family: 'Fredoka', sans-serif;
}

/* Elegant RPG scroll design for history card list */
.history-card-scroll {
    background: linear-gradient(135deg, #fffcf5 0%, #f7eed8 100%);
    border: 4px double #7c3aed; /* Violet border */
    outline: 2px solid #fbbf24; /* Golden borders */
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3), inset 0 0 50px rgba(139, 94, 26, 0.2);
    border-radius: 20px;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}
.history-card-scroll:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 45px rgba(124, 58, 237, 0.3), inset 0 0 50px rgba(139, 94, 26, 0.1);
    border-color: #fbbf24;
    outline-color: #7c3aed;
}

/* Hide scrollbar of horizontal/vertical automatic grimoire contents */
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}

/* Majestic parchment vertical scroll effect */
.parchment-scroll-violet {
    background: linear-gradient(135deg, #fffbf2 0%, #f7ebd3 100%);
    border: 6px double #7c3aed;
    outline: 3px solid #fbbf24;
    box-shadow: 
        0 20px 40px rgba(0,0,0,0.5), 
        inset 0 0 80px rgba(139, 94, 26, 0.25);
    border-radius: 12px;
}
</style>