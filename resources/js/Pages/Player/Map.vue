<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

onMounted(() => {
    // Leaflet initialization will go here
});
</script>

<template>
    <PlayerLayout title="Carte Interactive">
        <div class="h-[calc(100vh-12rem)] bg-white rounded-[3rem] shadow-xl shadow-blue-50 border border-blue-50 overflow-hidden relative">
            <!-- Map Placeholder -->
            <div class="absolute inset-0 flex flex-col items-center justify-center bg-blue-50/50">
                <div class="w-32 h-32 bg-white rounded-full flex items-center justify-center shadow-xl mb-8 animate-bounce">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-[#7C3AED]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </div>
                <h2 class="text-4xl font-black italic uppercase tracking-tighter text-slate-800">Chargement de la <span class="text-[#7C3AED]">Carte</span>...</h2>
                <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mt-4">Préparez-vous à explorer la ville</p>
            </div>
            
            <!-- Map UI Overlay (Example) -->
            <div class="absolute top-10 left-10 z-10 space-y-4">
                <div class="bg-white/90 backdrop-blur-md p-6 rounded-3xl shadow-2xl border border-white max-w-xs">
                    <h3 class="text-xl font-black italic uppercase text-slate-800 mb-2">Ma Position</h3>
                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Avenue des Champs-Élysées, Paris</p>
                </div>
            </div>
        </div>
    </PlayerLayout>
</template>

<style scoped>
h2, h3 {
    font-family: 'Fredoka', sans-serif;
}
</style>
