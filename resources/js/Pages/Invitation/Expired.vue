<script setup>
import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { onMounted } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    message: String,
});

onMounted(() => {
    gsap.from('.expired-content', { 
        scale: 0.8, 
        opacity: 0, 
        duration: 0.8,
        ease: 'back.out(1.7)'
    });
});
</script>

<template>
    <GuestLayout title="Invitation Expirée">
        <div class="h-full flex flex-col items-center justify-center p-4 relative z-10">
            <div class="expired-content w-full max-w-lg text-center bg-black/50 backdrop-blur-md p-10 rounded-3xl border-2 border-red-500/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-red-500 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                
                <h1 class="text-5xl font-black italic uppercase text-red-400 drop-shadow-xl mb-4">
                    Invitation Expirée
                </h1>
                
                <p class="text-white font-bold text-lg mb-8">
                    {{ message || "Cette invitation n'est plus valide." }}
                </p>

                <Link :href="route('login')" class="inline-block px-8 py-4 bg-yellow-400 text-slate-900 rounded-2xl font-black uppercase tracking-widest shadow-xl hover:scale-105 active:scale-95 transition-all text-xl">
                    Retour à l'accueil
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;700;900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }
h1, h2, h3, button, span { font-family: 'Bangers', cursive; }
</style>
