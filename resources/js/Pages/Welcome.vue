<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

onMounted(() => {
    const tl = gsap.timeline();
    
    // Animation du titre
    tl.from(".logo-part", {
        y: -100,
        opacity: 0,
        stagger: 0.1,
        duration: 1,
        ease: "bounce.out"
    });

    // Animation des explorateurs
    tl.from(".hero-explorer", {
        y: 300,
        opacity: 0,
        duration: 1.2,
        ease: "power4.out"
    }, "-=0.5");

    // Parallaxe sur le fond au scroll
    gsap.to(".hero-bg", {
        scrollTrigger: {
            trigger: ".hero-section",
            start: "top top",
            scrub: true
        },
        y: 150,
        scale: 1.1
    });
});
</script>

<template>
    <Head title="CITYPLAY - L'Exploration Nouvelle" />

    <div class="min-h-screen bg-[#1DA1F2] text-white selection:bg-yellow-400 font-sans">
        
        <!-- SECTION HERO (CITYPLAY) -->
        <section class="hero-section relative h-screen w-full flex flex-col items-center justify-center overflow-hidden">
            
            <!-- Image de fond (Ville Bleue) -->
            <div class="hero-bg absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1514565131-fce0801e5785?q=80&w=2112" 
                     class="w-full h-full object-cover opacity-60" alt="Cyber City">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-[#1DA1F2]/30 to-[#1DA1F2]"></div>
            </div>

            <!-- LOGO ET BOUTON -->
            <div class="relative z-20 flex flex-col items-center text-center px-4">
                <h1 class="flex items-center text-6xl md:text-9xl font-black italic tracking-tighter drop-shadow-[0_10px_20px_rgba(0,0,0,0.4)]">
                    <span class="logo-part text-white">CITY</span>
                    <!-- L'icône de presse-papier centrale -->
                    <div class="logo-part mx-2 bg-white p-2 md:p-4 rounded-2xl shadow-2xl rotate-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 md:w-20 md:h-20" viewBox="0 0 24 24" fill="none" stroke="#1DA1F2" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>
                    </div>
                    <span class="logo-part text-yellow-400">PLAY</span>
                </h1>

                <Link :href="route('login')" class="mt-8 px-12 py-5 bg-white text-[#1DA1F2] font-black text-2xl uppercase rounded-full shadow-2xl hover:scale-110 transition-transform active:scale-95">
                    Commencer l'aventure
                </Link>
            </div>

            <!-- LES DEUX EXPLORATEURS (Au pied de cette section) -->
            <div class="absolute bottom-0 w-full max-w-7xl flex justify-between items-end px-4 z-10 pointer-events-none">
                <!-- Exploratrice Dessin Animé -->
                <!-- <img src="/images/backgrounds/png1.png" 
                     class="hero-explorer w-[200px] md:w-[450px] object-contain drop-shadow-2xl" 
                     alt="Exploratrice"> -->
                
                <!-- Explorateur Dessin Animé (Flip horizontal) -->
                <img src="/images/backgrounds/png2.png" 
                     class="hero-explorer w-[200px] md:w-[450px] object-contain drop-shadow-2xl scale-x-[-1]" 
                     alt="Explorateur">
            </div>
        </section>

        <!-- SECTION RÈGLES (REMISE) -->
        <section class="relative z-30 py-24 px-6 bg-white text-slate-900">
            <div class="max-w-6xl mx-auto">
                <div class="grid md:grid-cols-2 gap-12">
                    <!-- Carte Règle -->
                    <div class="bg-slate-50 p-10 rounded-[40px] border-2 border-[#1DA1F2]/10 shadow-xl transform hover:-rotate-1 transition">
                        <div class="w-16 h-16 bg-yellow-400 rounded-2xl mb-6 flex items-center justify-center shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        </div>
                        <h3 class="text-4xl font-black italic mb-4 uppercase tracking-tighter text-[#1DA1F2]">Règle d'Or</h3>
                        <p class="text-lg text-slate-600 leading-relaxed italic">
                            "Ne laissez aucune trace, mais ramassez tous les trésors numériques. La ville est votre terrain de jeu, le temps est votre seul ennemi."
                        </p>
                    </div>

                    <!-- Carte Co-op -->
                    <div class="bg-[#1DA1F2] p-10 rounded-[40px] shadow-xl text-white transform hover:rotate-1 transition">
                        <div class="overflow-hidden rounded-2xl mb-6 h-48">
                            <img src="https://images.unsplash.com/photo-1551269901-5c5e14c25df7?q=80&w=2070" 
                                 class="w-full h-full object-cover" alt="Exploration group">
                        </div>
                        <h3 class="text-4xl font-black italic mb-4 uppercase tracking-tighter">Mode Co-op</h3>
                        <p class="text-white/80 leading-relaxed uppercase text-sm font-bold">
                            Formez des alliances pour débloquer des zones de haute sécurité et partager les points de prestige.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FOOTER (REMIS) -->
        <footer class="bg-slate-900 py-16 px-6">
            <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-10">
                <div class="text-center md:text-left">
                    <h4 class="text-4xl font-black italic text-[#1DA1F2]">CITYPLAY</h4>
                    <p class="text-slate-500 uppercase text-xs font-bold tracking-[0.3em]">The City is your Board Game.</p>
                </div>
                <div class="flex gap-8 text-sm font-bold uppercase">
                    <a href="#" class="hover:text-yellow-400 transition">Confidentialité</a>
                    <a href="#" class="hover:text-yellow-400 transition">Contact</a>
                </div>
            </div>
            <div class="mt-16 text-center text-slate-700 text-[10px] font-mono">
                PROJET CITYPLAY 2026 // SYSTEM_INITIALIZED
            </div>
        </footer>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bangers&display=swap');

h1, h3, h4 {
    font-family: 'Bangers', cursive;
}
</style>