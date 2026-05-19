<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { onMounted } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    invitation: Object,
    auth: Object,
});

onMounted(() => {
    const tl = gsap.timeline({ defaults: { ease: 'back.out(1.7)' } });
    tl.from('.invite-title', { scale: 0, opacity: 0, duration: 0.8, rotation: -5 })
      .from('.choice-btn', { y: 50, opacity: 0, stagger: 0.2, duration: 0.6 }, '-=0.3');
});

const choisirSolo = () => {
    if (!props.auth?.user) {
        // Use Laravel's built-in guest redirect to set intended URL
        window.location.href = route('login');
        return;
    }
    router.post(route('invitation.accept', props.invitation.token), {
        choix: 'solo'
    });
};

const choisirEquipe = () => {
    if (!props.auth?.user) {
        // Use Laravel's built-in guest redirect to set intended URL
        window.location.href = route('login');
        return;
    }
    router.post(route('invitation.accept', props.invitation.token), {
        choix: 'equipe'
    });
};
</script>

<template>
    <GuestLayout title="Invitation">
        <div class="h-full flex flex-col items-center justify-center p-4 relative z-10">
            <div class="w-full max-w-2xl flex flex-col items-center gap-10">
                
                <div class="invite-title text-center">
                    <h1 class="text-6xl md:text-7xl font-black italic uppercase tracking-tighter drop-shadow-[0_10px_20px_rgba(0,0,0,0.5)]">
                        <span class="text-white">Tu es invité !</span>
                    </h1>
                    <p class="text-white/80 text-xl font-bold mt-4">
                        Par <span class="text-yellow-400">{{ invitation.inviteur.name }}</span>
                    </p>
                    <p v-if="!auth?.user" class="text-white/60 text-sm font-bold mt-2">
                        Connecte-toi d'abord !
                    </p>
                </div>

                <div class="flex flex-col md:flex-row gap-6 w-full">
                    
                    <!-- Bouton SOLO -->
                    <button 
                        @click="choisirSolo"
                        class="choice-btn flex-1 group relative overflow-hidden rounded-[2rem] bg-gradient-to-b from-slate-500 to-slate-700 p-[2px] shadow-lg hover:scale-105 active:scale-95 transition-all"
                    >
                        <div class="relative w-full rounded-[1.9rem] bg-gradient-to-b from-slate-600 to-slate-800 px-8 py-8 flex flex-col items-center justify-center border-t border-slate-500 gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-3xl font-black italic uppercase text-white tracking-widest">Solo</span>
                            <p class="text-white/70 text-center font-bold">Jouer seul, à ton rythme</p>
                        </div>
                    </button>

                    <!-- Bouton EQUIPE -->
                    <button 
                        @click="choisirEquipe"
                        class="choice-btn flex-1 group relative overflow-hidden rounded-[2rem] bg-gradient-to-b from-yellow-300 to-yellow-500 p-[2px] shadow-[0_10px_40px_-10px_rgba(250,204,21,0.6)] hover:scale-105 active:scale-95 transition-all"
                    >
                        <div class="relative w-full rounded-[1.9rem] bg-gradient-to-b from-yellow-400 to-yellow-600 px-8 py-8 flex flex-col items-center justify-center border-t border-yellow-200 gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="text-3xl font-black italic uppercase text-white tracking-widest">Équipe</span>
                            <p class="text-white/70 text-center font-bold">Partager points et progression</p>
                        </div>
                    </button>

                </div>

                <div v-if="!auth?.user" class="text-center">
                    <Link :href="route('login')" class="inline-block px-8 py-4 bg-yellow-400 text-slate-900 rounded-2xl font-black uppercase tracking-widest shadow-xl hover:scale-105 active:scale-95 transition-all text-xl">
                        Se Connecter
                    </Link>
                </div>

            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;700;900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }
h1, h2, h3, button, span { font-family: 'Bangers', cursive; }
/* Ensure buttons are visible even if animation fails */
.choice-btn, .invite-title {
    opacity: 1 !important;
}
</style>
