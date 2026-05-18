<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import gsap from 'gsap';

const isNavigating = ref(false);
const loaderRef = ref(null);

onMounted(() => {
    router.on('start', () => {
        isNavigating.value = true;
        // Need to wait for next tick for the DOM element to be present before animating
        setTimeout(() => {
            if (loaderRef.value) {
                gsap.fromTo(loaderRef.value, 
                    { opacity: 0 }, 
                    { opacity: 1, duration: 0.3 }
                );
            }
        }, 10);
    });

    router.on('finish', () => {
        if (loaderRef.value) {
            gsap.to(loaderRef.value, {
                opacity: 0,
                duration: 0.5,
                onComplete: () => {
                    isNavigating.value = false;
                }
            });
        } else {
            isNavigating.value = false;
        }
    });
});
</script>

<template>
    <!-- Custom Loader Luxury -->
    <div v-if="isNavigating" ref="loaderRef" class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/90 backdrop-blur-sm">
        <div class="flex flex-col items-center">
            <div class="relative w-24 h-24 mb-6">
                <!-- Cercle de progression -->
                <svg class="w-full h-full rotate-[-90deg]">
                    <circle cx="48" cy="48" r="45" stroke="#F1F5F9" stroke-width="6" fill="transparent" />
                    <circle cx="48" cy="48" r="45" stroke="#1DA1F2" stroke-width="6" fill="transparent" 
                        stroke-dasharray="283" class="animate-loader-circle" />
                </svg>
                <!-- Logo central -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <ApplicationLogo class="w-12 h-12 text-[#1DA1F2] animate-pulse" />
                </div>
            </div>
            <div class="text-2xl font-black italic uppercase tracking-tighter text-[#1DA1F2]">
                City<span class="text-yellow-400">Play</span>
            </div>
            <div class="mt-2 text-[10px] font-bold uppercase tracking-[0.5em] text-slate-400 animate-pulse">
                Chargement de l'aventure...
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes loader-circle {
    0% { stroke-dashoffset: 283; }
    50% { stroke-dashoffset: 70; }
    100% { stroke-dashoffset: 283; }
}
.animate-loader-circle {
    animation: loader-circle 2s ease-in-out infinite;
    stroke-linecap: round;
}
</style>
