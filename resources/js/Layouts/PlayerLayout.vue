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

    <div ref="dashboardContainer" class="min-h-screen bg-slate-900 text-slate-800 flex overflow-hidden font-sans relative">
        <!-- Animated Background Slides -->
        <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
            <div class="bg-slide flex w-[200%] h-full opacity-40 mix-blend-overlay">
                <img src="/images/backgrounds/img1.jpg" class="w-1/2 h-full object-cover">
                <img src="/images/backgrounds/img2.jpg" class="w-1/2 h-full object-cover">
            </div>
        </div>

        <!-- HUD Overlay -->
        <div class="absolute inset-0 pointer-events-none z-50 flex flex-col justify-between p-4 md:p-8">
            <!-- Top HUD -->
            <div class="flex justify-between items-start">
                <!-- Boutons (Menu + Logout) -->
                <div class="flex space-x-2">
                    <!-- Menu Button -->
                    <Link :href="route('player.dashboard')" class="pointer-events-auto w-12 h-12 md:w-16 md:h-16 bg-black/30 backdrop-blur-md border-2 border-white/20 rounded-2xl flex items-center justify-center text-white hover:bg-black/50 hover:scale-110 transition-all shadow-xl group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 group-hover:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 hidden group-hover:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    </Link>

                    <!-- Logout Button -->
                    <Link :href="route('logout')" method="post" as="button" class="pointer-events-auto w-12 h-12 md:w-16 md:h-16 bg-red-500/80 backdrop-blur-md border-2 border-red-400/50 rounded-2xl flex items-center justify-center text-white hover:bg-red-600 hover:scale-110 transition-all shadow-[0_5px_15px_rgba(239,68,68,0.5)]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 md:h-8 md:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </Link>
                </div>

                <!-- Player Stats -->
                <div class="pointer-events-auto flex items-center space-x-3 bg-black/30 backdrop-blur-md border-2 border-white/20 rounded-full p-2 pr-6 shadow-xl">
                    <div class="w-10 h-10 md:w-14 md:h-14 rounded-full bg-yellow-400 p-0.5 shadow-lg shadow-yellow-400/50">
                        <img :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user.name}&background=7C3AED&color=fff`" class="w-full h-full rounded-full object-cover" alt="Avatar">
                    </div>
                    <div class="text-right">
                        <p class="text-white font-black text-xs md:text-lg uppercase italic leading-none drop-shadow-md">{{ $page.props.auth.user.name }}</p>
                        <p class="text-yellow-400 text-[10px] md:text-xs font-black uppercase tracking-widest drop-shadow-md flex items-center justify-end gap-1 mt-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                            {{ $page.props.auth.user.score || 0 }} XP
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-screen overflow-hidden z-10 relative">
            <div class="flex-1 overflow-y-auto custom-scrollbar">
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
