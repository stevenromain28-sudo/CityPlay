<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    title: String,
});
</script>

<template>
    <Head :title="title" />
    <div class="min-h-screen bg-slate-900 text-slate-800 flex overflow-hidden font-sans relative">
        <!-- Animated Background Slides -->
        <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
            <div class="bg-slide flex w-[200%] h-full opacity-40 mix-blend-overlay">
                <img src="/images/backgrounds/img1.jpg" class="w-1/2 h-full object-cover">
                <img src="/images/backgrounds/img2.jpg" class="w-1/2 h-full object-cover">
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
.font-sans { font-family: 'Outfit', sans-serif; }
h1, h2, h3, button, span { font-family: 'Bangers', cursive; }
</style>
