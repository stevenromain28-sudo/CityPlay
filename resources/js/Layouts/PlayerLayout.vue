<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import gsap from 'gsap';

const props = defineProps({
    title: String,
});

const dashboardContainer = ref(null);

onMounted(() => {
    // Background Sliding Animation
    gsap.to('.bg-slide', {
        xPercent: -20,
        duration: 20,
        repeat: -1,
        yoyo: true,
        ease: "linear"
    });
});
</script>

<template>
    <Head :title="title" />

    <div ref="dashboardContainer" class="min-h-screen bg-[#F0F7FF] text-slate-800 flex overflow-hidden font-sans relative">
        <!-- Animated Background Slides -->
        <div class="absolute inset-0 z-0 opacity-5 pointer-events-none overflow-hidden">
            <div class="bg-slide flex w-[200%] h-full">
                <img src="/images/backgrounds/img1.jpg" class="w-1/2 h-full object-cover">
                <img src="/images/backgrounds/img2.jpg" class="w-1/2 h-full object-cover">
            </div>
        </div>

        <!-- Sidebar -->
        <aside class="hidden md:flex w-24 lg:w-32 bg-[#7C3AED] flex-col items-center py-10 shadow-[5px_0_30px_rgba(124,58,237,0.1)] z-50">
            <div class="mb-16">
                <div class="w-16 h-16 bg-yellow-400 rounded-3xl flex items-center justify-center shadow-xl rotate-3 hover:rotate-0 transition-transform duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,2L4.5,20.29L5.21,21L12,18L18.79,21L19.5,20.29L12,2Z" />
                    </svg>
                </div>
            </div>

            <nav class="flex-1 flex flex-col space-y-8">
                <template v-for="(item, i) in [
                    {icon: 'home', label: 'Dashboard', route: 'player.lieu.dashboard', params: {ville: $page.props.ville?.id || $page.props.ville, lieu: $page.props.lieu?.id || $page.props.lieu}},
                    {icon: 'map', label: 'Carte', route: 'player.map', params: {lieu: $page.props.lieu?.id || $page.props.lieu}},
                    {icon: 'puzzle', label: 'Énigmes', route: 'player.enigme', params: {lieu: $page.props.lieu?.id || $page.props.lieu}},
                    {icon: 'leaderboard', label: 'Classement', route: 'player.leaderboard', params: {}},
                    {icon: 'user', label: 'Profil', route: 'profile.edit', params: {}},
                    {icon: 'wifi', label: 'Websocket', route: 'player.websocket', params: {lieu: $page.props.lieu?.id || $page.props.lieu}}
                ]" :key="i">
                    <Link v-if="item.route !== 'player.lieu.dashboard' || (item.params.ville && item.params.lieu)" 
                          :href="route(item.route, item.params)" class="sidebar-item group relative">
                        <div class="p-4 rounded-3xl transition-all duration-300 group-hover:scale-110" :class="route().current(item.route) ? 'bg-white text-[#7C3AED] shadow-xl' : 'text-white/80 hover:text-white'">
                            <svg v-if="item.icon==='home'" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                            <svg v-if="item.icon==='map'" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9" /></svg>
                            <svg v-if="item.icon==='puzzle'" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" /></svg>
                            <svg v-if="item.icon==='leaderboard'" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            <svg v-if="item.icon==='user'" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            <svg v-if="item.icon==='wifi'" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071a9.9 9.9 0 0114.142 0M2.05 8.05a15.554 15.554 0 0121.9 0" /></svg>
                        </div>
                        <span class="absolute left-full ml-6 px-3 py-2 bg-slate-800 text-white text-xs font-bold rounded-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50 shadow-xl">
                            {{ item.label }}
                        </span>
                    </Link>
                </template>
            </nav>

            <div class="mt-auto">
                <Link :href="route('logout')" method="post" as="button" class="p-4 text-white/60 hover:text-white hover:scale-110 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-screen overflow-hidden z-10">
            <!-- Header -->
            <header class="h-20 md:h-28 flex items-center justify-between px-6 md:px-12 shrink-0 bg-white/80 backdrop-blur-md border-b border-blue-100">
                <div class="flex items-center">
                    <button class="md:hidden mr-4 p-2 text-[#7C3AED]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <div>
                        <h1 class="text-2xl md:text-4xl font-black italic tracking-tighter text-[#7C3AED] uppercase">
                            City<span class="text-yellow-400">Play</span> <span class="text-slate-800 hidden sm:inline">Joueur</span>
                        </h1>
                        <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.3em] mt-1 hidden sm:block">Prêt pour l'aventure, {{ $page.props.auth.user.name }} ?</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4 md:space-x-10">
                    <div class="hidden xl:flex items-center bg-blue-50 px-6 py-3 rounded-2xl border border-blue-100">
                        <span class="text-[#7C3AED] font-black text-xl mr-2">{{ $page.props.auth.user.score || 0 }}</span>
                        <span class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Points d'XP</span>
                    </div>
                    
                    <div class="flex items-center space-x-3 md:space-x-5 md:pl-10 md:border-l-2 md:border-blue-50">
                        <div class="text-right hidden sm:block">
                            <p class="text-slate-800 font-black text-sm md:text-lg uppercase italic leading-tight">{{ $page.props.auth.user.name }}</p>
                            <p class="text-[#7C3AED] text-[10px] font-black uppercase tracking-widest">Explorateur Niveau 1</p>
                        </div>
                        <div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl md:rounded-[2rem] bg-yellow-400 p-0.5 md:p-1 shadow-lg shadow-yellow-200 rotate-3">
                            <img :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user.name}&background=7C3AED&color=fff`" class="w-full h-full rounded-xl md:rounded-[1.8rem] object-cover" alt="Avatar">
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto px-6 md:px-12 py-8 md:py-12 custom-scrollbar">
                <slot />
            </div>
        </main>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;700;900&display=swap');

.font-sans {
    font-family: 'Outfit', sans-serif;
}

h1, h2, h3, h4, h5, button, span {
    font-family: 'Bangers', cursive;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #F0F7FF;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #7C3AED33;
    border-radius: 10px;
}
</style>
