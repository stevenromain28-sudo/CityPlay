<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    villes: Array
});

onMounted(() => {
    gsap.from('.adventure-card', {
        scale: 0.9,
        opacity: 0,
        stagger: 0.2,
        duration: 0.8,
        ease: 'back.out(1.7)'
    });
});
</script>

<template>
    <Head title="Joueur Dashboard" />

    <div class="min-h-screen bg-[#0f1123] text-white font-sans">
        <AuthenticatedLayout>
            <template #header>
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-black tracking-tight text-white uppercase">
                        Prêt pour l'aventure ?
                    </h2>
                    <div class="flex space-x-2">
                        <span class="px-3 py-1 bg-purple-500/20 text-purple-400 rounded-full text-xs font-bold border border-purple-500/30">
                            1250 Points
                        </span>
                    </div>
                </div>
            </template>

            <div class="py-8 md:py-12 px-4 md:px-6 lg:px-8">
                <div class="max-w-7xl mx-auto">
                    <div class="mb-10">
                        <h3 class="text-gray-400 text-sm font-bold uppercase tracking-widest mb-4">Destinations Populaires</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            <div v-for="ville in villes" :key="ville.id"
                                 class="adventure-card group relative overflow-hidden rounded-[2rem] aspect-[4/5] cursor-pointer shadow-2xl">
                                <img :src="'https://source.unsplash.com/featured/?city,' + ville.nom"
                                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-[#0f1123] via-[#0f1123]/20 to-transparent"></div>

                                <div class="absolute bottom-8 left-8 right-8">
                                    <h4 class="text-3xl font-black mb-2">{{ ville.nom }}</h4>
                                    <p class="text-gray-300 text-sm line-clamp-2 mb-6">{{ ville.description }}</p>

                                    <div class="flex items-center justify-between">
                                        <div class="flex -space-x-2">
                                            <div v-for="i in 3" :key="i" class="w-8 h-8 rounded-full border-2 border-[#0f1123] bg-gray-800 flex items-center justify-center text-[10px] font-bold">
                                                {{ ['A', 'B', 'C'][i-1] }}
                                            </div>
                                            <div class="w-8 h-8 rounded-full border-2 border-[#0f1123] bg-purple-600 flex items-center justify-center text-[10px] font-bold">
                                                +12
                                            </div>
                                        </div>
                                        <button class="px-6 py-3 bg-white text-[#0f1123] rounded-2xl font-black text-sm transition-transform hover:scale-105 active:scale-95">
                                            EXPLORER
                                        </button>
                                    </div>
                                </div>

                                <div class="absolute top-8 left-8">
                                    <span class="px-4 py-2 bg-black/40 backdrop-blur-md rounded-xl text-xs font-bold border border-white/10">
                                        {{ ville.pays }}
                                    </span>
                                </div>
                            </div>

                            <!-- Placeholder for more adventures -->
                            <div class="adventure-card border-4 border-dashed border-white/5 rounded-[2rem] flex flex-col items-center justify-center text-white/20 hover:text-white/40 hover:border-white/10 transition-all cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span class="font-black tracking-widest uppercase">Bientôt disponible</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    </div>
</template>

<style scoped>
:deep(nav) {
    background-color: #161931 !important;
    border-bottom: 1px solid rgba(255,255,255,0.05) !important;
}
:deep(header) {
    background-color: transparent !important;
    box-shadow: none !important;
}
:deep(.responsive-menu-container) {
    background-color: #0f1123 !important;
    border-top: 1px solid rgba(255,255,255,0.05) !important;
}
:deep(.responsive-menu-container *) {
    color: white !important;
    border-color: rgba(255,255,255,0.1) !important;
    background-color: transparent !important;
}
:deep(.hamburger-btn) {
    color: white !important;
}
:deep(.hamburger-btn:hover), :deep(.hamburger-btn:focus) {
    background-color: rgba(255,255,255,0.1) !important;
}
</style>
