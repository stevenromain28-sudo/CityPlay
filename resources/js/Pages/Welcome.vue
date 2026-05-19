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

    <div class="min-h-screen bg-[#1DA1F2] text-white selection:bg-yellow-400 font-sans overflow-x-hidden">
        
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
                <h1 class="flex items-center text-5xl sm:text-6xl md:text-9xl font-black italic tracking-tighter drop-shadow-[0_10px_20px_rgba(0,0,0,0.4)]">
                    <span class="logo-part text-white">CITY</span>
                    <!-- L'icône de presse-papier centrale -->
                    <div class="logo-part mx-2 bg-white p-2 md:p-4 rounded-2xl shadow-2xl rotate-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 md:w-20 md:h-20" viewBox="0 0 24 24" fill="none" stroke="#1DA1F2" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>
                    </div>
                    <span class="logo-part text-yellow-400">PLAY</span>
                </h1>

                <Link :href="route('login')" class="mt-8 px-8 py-4 md:px-12 md:py-5 bg-white text-[#1DA1F2] font-black text-xl md:text-2xl uppercase rounded-full shadow-2xl hover:scale-110 transition-transform active:scale-95 text-center">
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
        <section class="relative z-30 py-16 md:py-24 px-4 sm:px-6 bg-white text-slate-900">
            <div class="max-w-6xl mx-auto">
                <div class="grid md:grid-cols-2 gap-8 md:gap-12">
                    <!-- Carte Règle -->
                    <div class="bg-slate-50 p-8 md:p-10 rounded-[2rem] md:rounded-[40px] border-2 border-[#1DA1F2]/10 shadow-xl transform md:hover:-rotate-1 transition">
                        <div class="w-16 h-16 bg-yellow-400 rounded-2xl mb-6 flex items-center justify-center shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        </div>
                        <h3 class="text-3xl md:text-4xl font-black italic mb-4 uppercase tracking-tighter text-[#1DA1F2]">Règle d'Or</h3>
                        <p class="text-base md:text-lg text-slate-600 leading-relaxed italic">
                            "Ne laissez aucune trace, mais ramassez tous les trésors numériques. La ville est votre terrain de jeu, le temps est votre seul ennemi."
                        </p>
                    </div>

                    <!-- Carte Co-op -->
                    <div class="bg-[#1DA1F2] p-8 md:p-10 rounded-[2rem] md:rounded-[40px] shadow-xl text-white transform md:hover:rotate-1 transition">
                        <div class="overflow-hidden rounded-2xl mb-6 h-40 md:h-48">
                            <img src="https://images.unsplash.com/photo-1551269901-5c5e14c25df7?q=80&w=2070" 
                                 class="w-full h-full object-cover" alt="Exploration group">
                        </div>
                        <h3 class="text-3xl md:text-4xl font-black italic mb-4 uppercase tracking-tighter">Mode Co-op</h3>
                        <p class="text-white/80 leading-relaxed uppercase text-xs md:text-sm font-bold">
                            Formez des alliances pour débloquer des zones de haute sécurité et partager les points de prestige.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative z-30 py-24 bg-slate-950 text-white overflow-hidden font-outfit">
            <!-- Futuristic cyber background image with light blend -->
            <div class="absolute inset-0 z-0 opacity-25 mix-blend-screen pointer-events-none">
                <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?q=80&w=2000" class="w-full h-full object-cover filter saturate-150 brightness-75" alt="Cyber Grid Map" />
            </div>
            <div class="absolute inset-0 bg-gradient-to-b from-[#1DA1F2]/10 via-slate-950/95 to-slate-950 z-0"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(29,161,242,0.25)_0%,transparent_70%)] pointer-events-none z-0"></div>
            
            <div class="max-w-7xl mx-auto px-4 relative z-10">
                <div class="text-center mb-16">
                    <span class="px-5 py-2 bg-[#1DA1F2]/20 text-[#1DA1F2] rounded-full text-xs font-black uppercase tracking-widest">
                        GRAVITATION INTERACTIVE
                    </span>
                    <h2 class="text-4xl md:text-6xl font-black uppercase tracking-tight mt-4 mb-4 font-outfit">
                        L'Écosystème Actif <span class="text-yellow-400">CityPlay</span>
                    </h2>
                    <p class="text-slate-400 text-xs md:text-sm font-bold uppercase tracking-widest max-w-2xl mx-auto leading-relaxed">
                        Visualisez comment les joueurs, leurs véhicules et les énigmes gravitent en permanence autour des monuments historiques pour débloquer les récits culturels de la cité.
                    </p>
                </div>

                <!-- ZONE D'ANIMATION DE LÉVITATION DE PRESTIGE -->
                <div class="relative w-full h-[650px] flex items-center justify-center">
                    
                    <!-- SVG Lignes Connectrices Rayonnantes -->
                    <svg class="absolute inset-0 w-full h-full pointer-events-none opacity-30" xmlns="http://www.w3.org/2000/svg">
                        <line class="connector-line" x1="50%" y1="50%" x2="15%" y2="25%" stroke="#1DA1F2" stroke-width="3" stroke-dasharray="8,8" />
                        <line class="connector-line" x1="50%" y1="50%" x2="85%" y2="25%" stroke="#1DA1F2" stroke-width="3" stroke-dasharray="8,8" />
                        <line class="connector-line" x1="50%" y1="50%" x2="90%" y2="50%" stroke="#1DA1F2" stroke-width="3" stroke-dasharray="8,8" />
                        <line class="connector-line" x1="50%" y1="50%" x2="85%" y2="75%" stroke="#1DA1F2" stroke-width="3" stroke-dasharray="8,8" />
                        <line class="connector-line" x1="50%" y1="50%" x2="50%" y2="88%" stroke="#1DA1F2" stroke-width="3" stroke-dasharray="8,8" />
                        <line class="connector-line" x1="50%" y1="50%" x2="15%" y2="75%" stroke="#1DA1F2" stroke-width="3" stroke-dasharray="8,8" />
                        <line class="connector-line" x1="50%" y1="50%" x2="10%" y2="50%" stroke="#1DA1F2" stroke-width="3" stroke-dasharray="8,8" />
                    </svg>

                    <!-- LE PÔLE CENTRAL : LA CITÉ / LE CONTINENT -->
                    <div class="relative z-20 w-48 h-48 md:w-64 md:h-64 rounded-full bg-gradient-to-tr from-[#1DA1F2] to-cyan-400 p-2 shadow-[0_0_80px_rgba(29,161,242,0.5)] flex items-center justify-center animate-pulse-slow">
                        <div class="w-full h-full rounded-full overflow-hidden relative flex items-center justify-center border border-white/10">
                            <!-- Image de fond du Globe -->
                            <img src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=600" class="absolute inset-0 w-full h-full object-cover opacity-45 scale-110" />
                            <div class="absolute inset-0 bg-gradient-to-b from-[#1DA1F2]/20 to-slate-950/80"></div>
                            
                            <!-- Contenu -->
                            <div class="relative z-10 text-center p-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 md:w-14 md:h-14 mx-auto stroke-yellow-400 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                                <h4 class="text-xl md:text-2xl font-black uppercase text-yellow-400 tracking-tight mt-2">LA CITÉ</h4>
                                <p class="text-[9px] text-slate-300 font-bold uppercase tracking-wider">Centre de Gravité</p>
                            </div>
                        </div>
                    </div>

                    <!-- BULLES EN ORBITE AVEC IMAGES D'ILLUSTRATION ET LÉVITATION GSAP -->
                    <!-- Bulle 1 : Joueurs -->
                    <div class="absolute orbit-bubble w-28 h-28 md:w-40 md:h-40 rounded-full p-1 bg-gradient-to-tr from-yellow-400 to-amber-500 shadow-xl shadow-yellow-500/10 flex items-center justify-center overflow-hidden" style="top: 15%; left: 10%;">
                        <div class="w-full h-full rounded-full overflow-hidden relative flex items-center justify-center">
                            <img src="https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?q=80&w=300" class="absolute inset-0 w-full h-full object-cover opacity-50" />
                            <div class="absolute inset-0 bg-slate-950/70"></div>
                            <div class="relative z-10 text-center p-2 flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 md:w-10 md:h-10 mx-auto stroke-yellow-400 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <span class="block text-[10px] md:text-xs font-black uppercase text-yellow-400 mt-1">Joueurs</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bulle 2 : Énigmes -->
                    <div class="absolute orbit-bubble w-28 h-28 md:w-40 md:h-40 rounded-full p-1 bg-gradient-to-tr from-[#1DA1F2] to-blue-600 shadow-xl shadow-blue-500/10 flex items-center justify-center overflow-hidden" style="top: 15%; right: 10%;">
                        <div class="w-full h-full rounded-full overflow-hidden relative flex items-center justify-center">
                            <img src="https://images.unsplash.com/photo-1509228468518-180dd4864904?q=80&w=300" class="absolute inset-0 w-full h-full object-cover opacity-50" />
                            <div class="absolute inset-0 bg-slate-950/70"></div>
                            <div class="relative z-10 text-center p-2 flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 md:w-10 md:h-10 mx-auto stroke-[#1DA1F2] mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <span class="block text-[10px] md:text-xs font-black uppercase text-[#1DA1F2] mt-1">Énigmes</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bulle 3 : GPS -->
                    <div class="absolute orbit-bubble w-28 h-28 md:w-40 md:h-40 rounded-full p-1 bg-gradient-to-tr from-yellow-400 to-amber-500 shadow-xl shadow-yellow-500/10 flex items-center justify-center overflow-hidden" style="top: 40%; right: 4%;">
                        <div class="w-full h-full rounded-full overflow-hidden relative flex items-center justify-center">
                            <img src="https://images.unsplash.com/photo-1548345680-f5475ea5df84?q=80&w=300" class="absolute inset-0 w-full h-full object-cover opacity-50" />
                            <div class="absolute inset-0 bg-slate-950/70"></div>
                            <div class="relative z-10 text-center p-2 flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 md:w-10 md:h-10 mx-auto stroke-yellow-400 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                <span class="block text-[10px] md:text-xs font-black uppercase text-yellow-400 mt-1">Coordonnées</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bulle 4 : Véhicules -->
                    <div class="absolute orbit-bubble w-28 h-28 md:w-40 md:h-40 rounded-full p-1 bg-gradient-to-tr from-[#1DA1F2] to-blue-600 shadow-xl shadow-blue-500/10 flex items-center justify-center overflow-hidden" style="bottom: 15%; right: 10%;">
                        <div class="w-full h-full rounded-full overflow-hidden relative flex items-center justify-center">
                            <img src="https://images.unsplash.com/photo-1511512578047-dfb367046420?q=80&w=300" class="absolute inset-0 w-full h-full object-cover opacity-50" />
                            <div class="absolute inset-0 bg-slate-950/70"></div>
                            <div class="relative z-10 text-center p-2 flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 md:w-10 md:h-10 mx-auto stroke-[#1DA1F2] mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/><path d="M9 17h6"/></svg>
                                <span class="block text-[10px] md:text-xs font-black uppercase text-[#1DA1F2] mt-1">Mobiles</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bulle 5 : Monuments -->
                    <div class="absolute orbit-bubble w-28 h-28 md:w-40 md:h-40 rounded-full p-1 bg-gradient-to-tr from-yellow-400 to-amber-500 shadow-xl shadow-yellow-500/10 flex items-center justify-center overflow-hidden" style="bottom: 5%; left: 40%;">
                        <div class="w-full h-full rounded-full overflow-hidden relative flex items-center justify-center">
                            <img src="https://images.unsplash.com/photo-1507504038482-7621c518ce5d?q=80&w=300" class="absolute inset-0 w-full h-full object-cover opacity-50" />
                            <div class="absolute inset-0 bg-slate-950/70"></div>
                            <div class="relative z-10 text-center p-2 flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 md:w-10 md:h-10 mx-auto stroke-yellow-400 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 20H3"/><path d="M17 20V8a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v12"/><path d="M11 10h2"/><path d="M11 14h2"/></svg>
                                <span class="block text-[10px] md:text-xs font-black uppercase text-yellow-400 mt-1">Lieux</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bulle 6 : Récits -->
                    <div class="absolute orbit-bubble w-28 h-28 md:w-40 md:h-40 rounded-full p-1 bg-gradient-to-tr from-[#1DA1F2] to-blue-600 shadow-xl shadow-blue-500/10 flex items-center justify-center overflow-hidden" style="bottom: 15%; left: 10%;">
                        <div class="w-full h-full rounded-full overflow-hidden relative flex items-center justify-center">
                            <img src="https://images.unsplash.com/photo-1506880018603-83d5b814b5a6?q=80&w=300" class="absolute inset-0 w-full h-full object-cover opacity-50" />
                            <div class="absolute inset-0 bg-slate-950/70"></div>
                            <div class="relative z-10 text-center p-2 flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 md:w-10 md:h-10 mx-auto stroke-[#1DA1F2] mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                <span class="block text-[10px] md:text-xs font-black uppercase text-[#1DA1F2] mt-1">Récits</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bulle 7 : Indices -->
                    <div class="absolute orbit-bubble w-28 h-28 md:w-40 md:h-40 rounded-full p-1 bg-gradient-to-tr from-yellow-400 to-amber-500 shadow-xl shadow-yellow-500/10 flex items-center justify-center overflow-hidden" style="top: 40%; left: 3%;">
                        <div class="w-full h-full rounded-full overflow-hidden relative flex items-center justify-center">
                            <img src="https://images.unsplash.com/photo-1505330622279-bf7d7fc918f4?q=80&w=300" class="absolute inset-0 w-full h-full object-cover opacity-50" />
                            <div class="absolute inset-0 bg-slate-950/70"></div>
                            <div class="relative z-10 text-center p-2 flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 md:w-10 md:h-10 mx-auto stroke-yellow-400 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <span class="block text-[10px] md:text-xs font-black uppercase text-yellow-400 mt-1">Indices</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- SECTION COMPARTIMENTS DÉTAILLÉS (PRESTIGE OVERHAUL AVEC IMAGES ET TYPOGRAPHIE DROITE) -->
        <section class="relative z-30 py-20 md:py-32 px-4 sm:px-6 bg-slate-950 text-white font-outfit overflow-hidden">
            <!-- Background Image Map/Tech Overlay -->
            <div class="absolute inset-0 z-0 opacity-[0.08] pointer-events-none mix-blend-overlay">
                <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?q=80&w=2000" class="w-full h-full object-cover filter brightness-75 contrast-125 scale-110" alt="Map Tech Overlay" />
            </div>
            <!-- Radial glowing lights for premium ambiance -->
            <div class="absolute top-1/4 left-1/4 w-[500px] h-[500px] bg-[#1DA1F2]/10 rounded-full blur-[150px] pointer-events-none"></div>
            <div class="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] bg-yellow-400/5 rounded-full blur-[150px] pointer-events-none"></div>

            <div class="max-w-7xl mx-auto relative z-10">
                
                <!-- Section Header -->
                <div class="text-center mb-24">
                    <span class="px-5 py-2 bg-[#1DA1F2]/20 text-[#1DA1F2] border border-[#1DA1F2]/30 rounded-full text-xs font-black uppercase tracking-widest">
                        GUIDE DE L'EXPLORATEUR
                    </span>
                    <h2 class="text-4xl md:text-6xl font-black uppercase text-white tracking-tight mt-4 mb-4 font-outfit">
                        Comment Jouer à <span class="text-yellow-400">CityPlay</span> ?
                    </h2>
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-xs max-w-2xl mx-auto leading-relaxed">
                        Découvrez une ville sous un autre angle à travers des parcours géolocalisés, des énigmes immersives et une compétition sans merci.
                    </p>
                </div>

                <!-- GRILLE PRINCIPALE : LE CONCEPT & LA VISION -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 mb-24">
                    
                    <!-- Carte 1 : Vision -->
                    <div class="info-card bg-slate-900/60 backdrop-blur-md rounded-[2.5rem] shadow-2xl border border-white/10 hover:border-[#1DA1F2] hover:shadow-[#1DA1F2]/10 hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between overflow-hidden">
                        <div>
                            <div class="h-48 overflow-hidden relative">
                                <img src="https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?q=80&w=800" class="w-full h-full object-cover opacity-80" alt="Vision" />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/20 to-transparent"></div>
                            </div>
                            <div class="p-8 pt-4">
                                <div class="w-14 h-14 bg-[#1DA1F2]/20 text-[#1DA1F2] border border-[#1DA1F2]/30 rounded-[1.2rem] flex items-center justify-center mb-6 shadow-md shadow-[#1DA1F2]/5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </div>
                                <h3 class="text-2xl font-black uppercase text-white tracking-tight mb-3 font-outfit">Vision du Produit</h3>
                                <p class="text-slate-400 font-medium text-xs leading-relaxed">
                                    CITYPLAY transforme chaque ville en un véritable terrain de jeu grandeur nature. Munis de votre smartphone, vous devenez l'acteur d'une aventure interactive hors du commun, naviguant entre passé culturel et défis modernes.
                                </p>
                            </div>
                        </div>
                        <div class="px-8 pb-8">
                            <ul class="space-y-2.5 pt-4 border-t border-slate-800 text-[10px] font-black uppercase text-[#1DA1F2] tracking-wider">
                                <li class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-2 stroke-[#1DA1F2]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Exploration active</li>
                                <li class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-2 stroke-[#1DA1F2]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Énigmes géolocalisées</li>
                                <li class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-2 stroke-[#1DA1F2]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Coopération Active</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Carte 2 : Objectifs principaux -->
                    <div class="info-card bg-slate-900/60 backdrop-blur-md rounded-[2.5rem] shadow-2xl border border-white/10 hover:border-yellow-400 hover:shadow-yellow-400/5 hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between overflow-hidden">
                        <div>
                            <div class="h-48 overflow-hidden relative">
                                <img src="https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?q=80&w=800" class="w-full h-full object-cover opacity-80" alt="Objectifs" />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/20 to-transparent"></div>
                            </div>
                            <div class="p-8 pt-4">
                                <div class="w-14 h-14 bg-yellow-400/20 text-yellow-400 border border-yellow-400/30 rounded-[1.2rem] flex items-center justify-center mb-6 shadow-md shadow-yellow-400/5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg>
                                </div>
                                <h3 class="text-2xl font-black uppercase text-white tracking-tight mb-3 font-outfit">Objectifs du Projet</h3>
                                <p class="text-slate-400 font-medium text-xs leading-relaxed">
                                    Notre vocation est de valoriser le patrimoine matériel de nos communes tout en encourageant le mouvement et les interactions physiques, créant ainsi une dynamique moderne pour les résidents comme pour les visiteurs.
                                </p>
                            </div>
                        </div>
                        <div class="px-8 pb-8">
                            <ul class="space-y-2.5 pt-4 border-t border-slate-800 text-[10px] font-black uppercase text-yellow-400 tracking-wider">
                                <li class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-2 stroke-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Valorisation touristique active</li>
                                <li class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-2 stroke-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Mobilité douce et dynamique</li>
                                <li class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-2 stroke-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Immersion totale garantie</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Carte 3 : Validation GPS -->
                    <div class="info-card bg-slate-900/60 backdrop-blur-md rounded-[2.5rem] shadow-2xl border border-white/10 hover:border-[#1DA1F2] hover:shadow-[#1DA1F2]/10 hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between overflow-hidden">
                        <div>
                            <div class="h-48 overflow-hidden relative">
                                <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?q=80&w=800" class="w-full h-full object-cover opacity-80" alt="Validation GPS" />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/20 to-transparent"></div>
                            </div>
                            <div class="p-8 pt-4">
                                <div class="w-14 h-14 bg-[#1DA1F2]/20 text-[#1DA1F2] border border-[#1DA1F2]/30 rounded-[1.2rem] flex items-center justify-center mb-6 shadow-md shadow-[#1DA1F2]/5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </div>
                                <h3 class="text-2xl font-black uppercase text-white tracking-tight mb-3 font-outfit">Validation GPS Immersive</h3>
                                <p class="text-slate-400 font-medium text-xs leading-relaxed">
                                    Pas de triche possible ! Pour résoudre une énigme et en apprendre plus sur un lieu, l'application compare en temps réel votre position physique avec les coordonnées GPS de la zone via votre puce de localisation.
                                </p>
                            </div>
                        </div>
                        <div class="px-8 pb-8">
                            <ul class="space-y-2.5 pt-4 border-t border-slate-800 text-[10px] font-black uppercase text-[#1DA1F2] tracking-wider">
                                <li class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-2 stroke-[#1DA1F2]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Comparaison GPS temps réel</li>
                                <li class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-2 stroke-[#1DA1F2]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Rayon de validation configurable</li>
                                <li class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-2 stroke-[#1DA1F2]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Option simplifiée par Lieu</li>
                            </ul>
                        </div>
                    </div>

                </div>

                <!-- SUBSECTION : LES ROLES & LES MODES DE JEU -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 mb-24">
                    
                    <!-- BLOC DE GAUCHE : LES MODES DE JEU (8 COLONNES) -->
                    <div class="lg:col-span-8 space-y-10">
                        <div class="info-card bg-slate-900/60 backdrop-blur-md p-8 md:p-10 rounded-[3rem] shadow-2xl border border-white/10 flex flex-col justify-between">
                            <div class="flex flex-col md:flex-row md:items-center items-start gap-4 mb-8">
                                <span class="px-4 py-2 bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-black uppercase rounded-xl tracking-wider shrink-0">MODALITÉS DE COMBAT</span>
                                <h3 class="text-2xl sm:text-3xl md:text-4xl font-black uppercase text-white tracking-tight font-outfit">Sélectionnez Votre Mode</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                
                                <!-- Mode Collectif -->
                                <div class="bg-slate-950/50 rounded-[2rem] border border-white/5 hover:border-[#1DA1F2]/60 transition-all duration-300 overflow-hidden flex flex-col justify-between">
                                    <div class="h-40 overflow-hidden relative">
                                        <img src="https://images.unsplash.com/photo-1551269901-5c5e14c25df7?q=80&w=800" class="w-full h-full object-cover opacity-80" alt="Mode Collectif" />
                                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent"></div>
                                    </div>
                                    <div class="p-6 pt-2">
                                        <div class="w-10 h-10 bg-[#1DA1F2]/20 text-[#1DA1F2] border border-[#1DA1F2]/30 rounded-[0.8rem] flex items-center justify-center mb-4 shadow-md">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                        </div>
                                        <h4 class="text-xl font-black uppercase text-white tracking-tight mb-2 font-outfit">Mode Collectif</h4>
                                        <p class="text-slate-400 font-medium text-xs leading-relaxed mb-4">
                                            Unissez vos forces en équipe. Toutes les découvertes et réponses sont partagées en temps réel. Le score est global et collectif : si un seul explorateur résout l'énigme, l'équipe entière progresse !
                                        </p>
                                        <span class="text-[9px] font-black uppercase text-[#1DA1F2] bg-slate-900 border border-[#1DA1F2]/30 px-3 py-1.5 rounded-full tracking-wider inline-block">🎯 Coopération Totale</span>
                                    </div>
                                </div>

                                <!-- Mode Mercenaire -->
                                <div class="bg-slate-950/50 rounded-[2rem] border border-white/5 hover:border-yellow-400/60 transition-all duration-300 overflow-hidden flex flex-col justify-between">
                                    <div class="h-40 overflow-hidden relative">
                                        <img src="https://images.unsplash.com/photo-1511512578047-dfb367046420?q=80&w=800" class="w-full h-full object-cover opacity-80" alt="Mode Mercenaire" />
                                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent"></div>
                                    </div>
                                    <div class="p-6 pt-2">
                                        <div class="w-10 h-10 bg-yellow-400/20 text-yellow-400 border border-yellow-400/30 rounded-[0.8rem] flex items-center justify-center mb-4 shadow-md">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                        </div>
                                        <h4 class="text-xl font-black uppercase text-white tracking-tight mb-2 font-outfit">Mode Mercenaire</h4>
                                        <p class="text-slate-400 font-medium text-xs leading-relaxed mb-4">
                                            Chacun pour soi et que le meilleur gagne ! Les joueurs gardent leurs découvertes secrètes. Résoudre une énigme la bloque pour les autres explorateurs. Classement par vitesse, précision et temps.
                                        </p>
                                        <span class="text-[9px] font-black uppercase text-yellow-500 bg-slate-900 border border-yellow-400/30 px-3 py-1.5 rounded-full tracking-wider inline-block">⚔️ Compétition Solo</span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- BLOC DE DROITE : PROFIL DU JOUEUR (4 COLONNES) -->
                    <div class="info-card lg:col-span-4 bg-slate-900/60 backdrop-blur-md rounded-[3rem] shadow-2xl border border-white/10 hover:border-[#1DA1F2]/60 hover:shadow-[#1DA1F2]/5 transition-all duration-300 flex flex-col justify-between overflow-hidden">
                        <div>
                            <div class="h-44 overflow-hidden relative">
                                <img src="https://images.unsplash.com/photo-1488646953014-85cb44e25828?q=80&w=800" class="w-full h-full object-cover opacity-80" alt="Profil explorateur" />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/20 to-transparent"></div>
                            </div>
                            <div class="p-8 pt-2">
                                <div class="w-12 h-12 bg-[#1DA1F2]/20 text-[#1DA1F2] border border-[#1DA1F2]/30 rounded-[1rem] flex items-center justify-center mb-4 shadow-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </div>
                                <h3 class="text-2xl font-black uppercase text-white tracking-tight mb-3 font-outfit">Incarnez un Explorateur</h3>
                                <p class="text-slate-400 font-medium text-xs leading-relaxed mb-4">
                                    Lorsque vous rejoignez l'aventure via un lien d'invitation, vous configurez votre profil de jeu pour adapter l'algorithme de parcours à vos contraintes.
                                </p>
                            </div>
                        </div>
                        
                        <div class="px-8 pb-8 space-y-4 text-xs font-bold text-slate-300">
                            <div class="pt-4 border-t border-slate-800">
                                <span class="block text-[9px] font-black uppercase text-[#1DA1F2] tracking-wider mb-2">Moyens de Locomotion</span>
                                <div class="flex flex-wrap gap-1.5 font-black uppercase">
                                    <span class="px-2.5 py-1 bg-slate-950/80 border border-white/5 rounded-lg text-[10px]">Marche</span>
                                    <span class="px-2.5 py-1 bg-slate-950/80 border border-white/5 rounded-lg text-[10px]">Vélo</span>
                                    <span class="px-2.5 py-1 bg-slate-950/80 border border-white/5 rounded-lg text-[10px]">Moto</span>
                                    <span class="px-2.5 py-1 bg-slate-950/80 border border-white/5 rounded-lg text-[10px]">Voiture</span>
                                </div>
                            </div>
                            <div>
                                <span class="block text-[9px] font-black uppercase text-[#1DA1F2] tracking-wider mb-2">Niveaux de Difficulté</span>
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="px-2.5 py-1 bg-slate-950/80 border border-emerald-500/30 rounded-lg text-[10px] text-emerald-400 font-black uppercase">Facile</span>
                                    <span class="px-2.5 py-1 bg-slate-950/80 border border-yellow-500/30 rounded-lg text-[10px] text-yellow-400 font-black uppercase">Moyen</span>
                                    <span class="px-2.5 py-1 bg-slate-950/80 border border-rose-500/30 rounded-lg text-[10px] text-rose-400 font-black uppercase">Difficile</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- CARD HORIZONTALE DÉDIÉE MAIRIE ET CRÉATION DE JEU -->
                <div class="info-card bg-slate-900/60 backdrop-blur-md border border-white/10 rounded-[3rem] shadow-2xl text-white relative overflow-hidden flex flex-col lg:flex-row items-center justify-between overflow-hidden">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-[#1DA1F2]/10 rounded-full blur-[100px] pointer-events-none"></div>
                    <div class="absolute bottom-0 left-0 w-96 h-96 bg-yellow-400/5 rounded-full blur-[100px] pointer-events-none"></div>

                    <!-- Image Section gauche/haut sur mobile -->
                    <div class="w-full lg:w-1/3 h-64 lg:h-full min-h-[300px] relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=800" class="w-full h-full object-cover opacity-80" alt="Mairies" />
                        <div class="absolute inset-0 bg-gradient-to-r lg:bg-gradient-to-l from-slate-950 via-slate-950/30 to-transparent"></div>
                    </div>

                    <!-- Content Section droite -->
                    <div class="w-full lg:w-2/3 p-10 md:p-14 relative z-10 flex flex-col justify-between font-outfit">
                        <div>
                            <span class="px-4 py-2 bg-yellow-400 text-slate-900 text-xs font-black uppercase rounded-xl tracking-wider inline-block">OUTILS DE CRÉATION</span>
                            <h3 class="text-3xl md:text-4xl font-black uppercase tracking-tight mt-4 mb-4 font-outfit">
                                Pour les Mairies & Administrateurs
                            </h3>
                            <p class="text-slate-300 font-medium text-xs md:text-sm leading-relaxed mb-6">
                                Vous souhaitez dynamiser l'activité de votre commune et faire rayonner votre patrimoine culturel ? CITYPLAY offre aux mairies et créateurs un espace complet d'édition. Modélisez vos villes, tracez des parcours originaux, créez des énigmes, paramétrez les coordonnées GPS de validation, gérez les parties et étudiez le trafic grâce à nos tableaux analytiques d'affluence.
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4">
                            <Link :href="route('login')" class="px-8 py-4 bg-[#1DA1F2] hover:bg-[#1DA1F2]/90 text-white text-center font-black uppercase tracking-wider rounded-[1.5rem] transition-all hover:scale-105 active:scale-95 shadow-lg shadow-blue-500/25">
                                Rejoindre l'administration
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- FOOTER (REMIS) -->
        <footer class="bg-slate-900 py-10 md:py-16 px-4 sm:px-6">
            <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8 md:gap-10">
                <div class="text-center md:text-left">
                    <h4 class="text-3xl md:text-4xl font-black italic text-[#1DA1F2]">CITYPLAY</h4>
                    <p class="text-slate-500 uppercase text-[10px] md:text-xs font-bold tracking-[0.3em]">The City is your Board Game.</p>
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