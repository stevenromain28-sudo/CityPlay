<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { gsap } from 'gsap';

// Formulaire Inertia
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

onMounted(() => {
    // Animation d'entrée du décor
    gsap.from(".game-bg", { duration: 1.5, opacity: 0, ease: "power2.out" });
    
    // Animation du parchemin (Sign In)
    gsap.from(".parchment-container", { 
        duration: 1, 
        y: 100, 
        opacity: 0, 
        delay: 0.5, 
        ease: "back.out(1.7)" 
    });

    // Animation des personnages (flottement)
    gsap.to(".character-float", {
        y: -20,
        duration: 2,
        repeat: -1,
        yoyo: true,
        ease: "sine.inOut",
        stagger: 0.5
    });

    // Animation des boutons au survol (via GSAP pour plus de punch)
    const buttons = document.querySelectorAll('.btn-game');
    buttons.forEach(btn => {
        btn.addEventListener('mouseenter', () => gsap.to(btn, { scale: 1.05, duration: 0.2 }));
        btn.addEventListener('mouseleave', () => gsap.to(btn, { scale: 1, duration: 0.2 }));
    });
});
</script>

<template>
    <Head title="CityPlay - Connexion" />

    <div class="game-bg min-h-screen bg-gradient-to-b from-[#4fc3f7] to-[#1976d2] flex items-center justify-center p-4 overflow-hidden font-mono relative">
        
        <div class="absolute top-10 left-10 text-6xl opacity-30 character-float">☁️</div>
        <div class="absolute bottom-20 right-20 text-8xl opacity-20 character-float italic text-white font-black">CITYPLAY</div>

        <div class="max-w-6xl w-full grid grid-cols-1 lg:grid-cols-2 gap-8 items-center relative z-10">
            
            <div class="parchment-container relative flex justify-center">
                <div class="bg-[#fcf3cf] border-x-[12px] border-[#a0522d] shadow-[0_20px_50px_rgba(0,0,0,0.5)] p-8 md:p-12 w-full max-w-md relative rounded-[2rem]">
                    <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-[#5d4037] text-white px-8 py-3 rounded-xl border-4 border-[#3e2723] shadow-lg transform -rotate-2">
                        <h1 class="text-3xl font-black tracking-tighter uppercase italic">Sign In</h1>
                    </div>

                    <form @submit.prevent="submit" class="mt-8 space-y-6">
                        <div class="relative">
                            <input 
                                v-model="form.email"
                                type="email" 
                                placeholder="Utilisateur / Email"
                                class="w-full bg-[#d4efdf] border-4 border-[#27ae60] p-4 rounded-2xl text-[#1b5e20] font-bold placeholder:text-[#27ae60]/60 focus:ring-0 focus:border-[#2ecc71] transition-all shadow-[inset_0_4px_0_rgba(0,0,0,0.1)]"
                                required
                            />
                            <span class="absolute right-4 top-4 text-2xl">👤</span>
                        </div>

                        <div class="relative">
                            <input 
                                v-model="form.password"
                                type="password" 
                                placeholder="Mot de Passe"
                                class="w-full bg-[#d4efdf] border-4 border-[#27ae60] p-4 rounded-2xl text-[#1b5e20] font-bold placeholder:text-[#27ae60]/60 focus:ring-0 focus:border-[#2ecc71] transition-all shadow-[inset_0_4px_0_rgba(0,0,0,0.1)]"
                                required
                            />
                            <span class="absolute right-4 top-4 text-2xl">🔒</span>
                        </div>

                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="btn-game w-full bg-[#e67e22] hover:bg-[#d35400] text-white text-2xl font-black py-4 rounded-2xl border-b-[8px] border-[#a04000] active:border-b-0 active:translate-y-2 transition-all shadow-lg uppercase italic tracking-widest"
                        >
                            Lancer
                        </button>

                        <div class="text-center space-y-4">
                            <p class="text-[#a0522d] font-black uppercase text-sm italic">Ou se connecter avec</p>
                            <div class="flex justify-center gap-4">
                                <button type="button" class="bg-white p-3 rounded-xl border-4 border-[#bdc3c7] shadow-md hover:-translate-y-1 transition-transform">📧</button>
                                <button type="button" class="bg-[#3b5998] p-3 rounded-xl border-4 border-[#2d4373] shadow-md hover:-translate-y-1 transition-transform text-white">f</button>
                                <button type="button" class="bg-white p-3 rounded-xl border-4 border-[#bdc3c7] shadow-md hover:-translate-y-1 transition-transform text-red-500 font-bold text-xl">G</button>
                            </div>
                        </div>

                        <div class="text-center pt-4">
                            <p class="text-[#e67e22] font-bold">Pas encore de compte ?</p>
                            <Link :href="route('register')" class="text-[#a0522d] font-black underline uppercase text-lg hover:text-black">S'inscrire</Link>
                        </div>
                    </form>
                </div>
            </div>

            <div class="hidden lg:flex flex-col items-center justify-center relative">
                <div class="absolute w-[500px] h-[500px] bg-cyan-400/20 rounded-full blur-[100px] animate-pulse"></div>
                
                <div class="relative z-10 character-float">
                    <div class="relative">
                        <span class="text-[12rem] md:text-[18rem] drop-shadow-[0_20px_0_rgba(0,0,0,0.2)]">🐗</span>
                        <span class="absolute -top-10 -right-10 text-[8rem] rotate-12 drop-shadow-xl">🧒🏾</span>
                    </div>
                    
                    <div class="mt-4 bg-[#5d4037] border-4 border-[#3e2723] p-4 rounded-2xl transform -rotate-3 shadow-[10px_10px_0px_#212121]">
                        <h2 class="text-4xl font-black text-white italic tracking-tighter uppercase leading-none">
                            CityPlay:<br>
                            <span class="text-[#f1c40f]">L'aventure</span>
                        </h2>
                    </div>
                </div>

                <div class="mt-12 flex gap-4">
                    <button class="bg-[#9e9e9e] border-4 border-[#616161] p-4 rounded-2xl shadow-[0_6px_0_#424242] hover:translate-y-1 active:shadow-none transition-all">🏠</button>
                    <button class="bg-[#9e9e9e] border-4 border-[#616161] p-4 rounded-2xl shadow-[0_6px_0_#424242] hover:translate-y-1 active:shadow-none transition-all">🔊</button>
                    <button class="bg-[#9e9e9e] border-4 border-[#616161] p-4 rounded-2xl shadow-[0_6_0_#424242] hover:translate-y-1 active:shadow-none transition-all">⚙️</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Ajout d'une police "Gaming" si nécessaire */
@import url('https://fonts.googleapis.com/css2?family=Luckiest+Guy&display=swap');

h1, h2, button, Link {
    font-family: 'Luckiest Guy', cursive;
}

/* Texture de papier pour le parchemin */
.parchment-container > div {
    background-image: url('https://www.transparenttextures.com/patterns/paper-fibers.png');
}

/* Effet de scanline/vibration léger sur le fond bleu */
.game-bg::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.05) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.02), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.02));
    background-size: 100% 4px, 3px 100%;
    pointer-events: none;
}
</style>