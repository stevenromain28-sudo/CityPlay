<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { gsap } from 'gsap';

// Formulaire Inertia original préservé
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
    // Animation d'entrée globale du décor
    gsap.from(".game-bg", { duration: 1.2, opacity: 0, ease: "power2.out" });
    
    // Entrée dynamique du panneau de connexion (Slide + rebond)
    gsap.from(".login-panel", { 
        duration: 0.8, 
        x: -150, 
        opacity: 0, 
        ease: "back.out(1.2)" 
    });

    // Entrée explosive des gros textes de droite
    gsap.from(".pop-text", {
        duration: 0.6,
        scale: 0.5,
        opacity: 0,
        stagger: 0.15,
        ease: "back.out(1.5)",
        delay: 0.3
    });

    // Lévitation GSAP ultra fluide du Logo SVG
    gsap.to(".logo-float", {
        y: -15,
        duration: 2.5,
        repeat: -1,
        yoyo: true,
        ease: "sine.inOut"
    });

    // Animation au survol des boutons gaming avec GSAP
    const buttons = document.querySelectorAll('.btn-game-hover');
    buttons.forEach(btn => {
        btn.addEventListener('mouseenter', () => gsap.to(btn, { scale: 1.03, duration: 0.2 }));
        btn.addEventListener('mouseleave', () => gsap.to(btn, { scale: 1, duration: 0.2 }));
    });
});
</script>

<template>
    <Head title="CityPlay - Connexion" />

    <component :is="'style'">
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap');
        .font-gaming {
            font-family: 'Fredoka', sans-serif;
        }
    </component>

    <div class="game-bg min-h-screen bg-gradient-to-b from-[#4fc3f7] to-[#1976d2] flex items-center justify-start p-4 md:p-0 overflow-hidden font-gaming relative select-none">
        
        <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:3rem_3rem] pointer-events-none"></div>

        <div class="absolute top-1/4 right-1/4 w-[600px] h-[600px] bg-white/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-10 left-10 text-8xl opacity-10 text-white font-black tracking-widest uppercase">CITYPLAY</div>

        <div class="w-full min-h-screen grid grid-cols-1 lg:grid-cols-12 relative z-10">
            
            <div class="lg:col-span-5 bg-white p-8 md:p-12 flex flex-col justify-center shadow-2xl border-r-8 border-blue-600/20 login-panel h-full">
                
                <div class="text-center mb-8">
                    <div class="inline-block transform -rotate-2 text-5xl font-black tracking-wider text-blue-600 drop-shadow-[0_5px_0_rgba(29,78,216,1)] uppercase">
                        <span class="text-yellow-400 drop-shadow-[0_5px_0_rgba(234,179,8,1)]">City</span>Play
                    </div>
                    <h1 class="text-3xl font-black text-slate-800 mt-6 tracking-wide">
                        Welcome Back! <br>
                        <span class="text-blue-600">Sign In</span>
                    </h1>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    
                    <div class="relative">
                        <input 
                            v-model="form.email"
                            type="email" 
                            placeholder="Email Address"
                            class="w-full px-6 py-4 bg-white border-2 border-slate-300 rounded-full text-slate-700 font-bold placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all text-base shadow-[inset_0_2px_4px_rgba(0,0,0,0.05)]"
                            required
                        />
                    </div>

                    <div class="relative">
                        <input 
                            v-model="form.password"
                            type="password" 
                            placeholder="Password"
                            class="w-full px-6 py-4 bg-white border-2 border-slate-300 rounded-full text-slate-700 font-bold placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all text-base shadow-[inset_0_2px_4px_rgba(0,0,0,0.05)]"
                            required
                        />
                    </div>

                    <div class="flex items-center justify-between px-2 pt-1 text-sm font-bold text-slate-600">
                        <label class="flex items-center cursor-pointer space-x-2">
                            <input type="checkbox" v-model="form.remember" class="w-4 h-4 text-blue-600 border-2 border-slate-300 rounded focus:ring-0 cursor-pointer">
                            <span>Remember Me</span>
                        </label>
                        <a href="#" class="text-blue-600 hover:underline">Forgot?</a>
                    </div>

                    <div class="pt-2">
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="btn-game-hover w-full py-4 bg-yellow-400 hover:bg-yellow-300 text-slate-900 font-black text-xl rounded-full border-b-4 border-yellow-600 active:border-b-0 active:translate-y-1 active:shadow-none shadow-md transition-all uppercase tracking-wide"
                        >
                            Lancer l'Aventure
                        </button>
                    </div>

                    <div class="relative flex py-1 items-center">
                        <div class="flex-grow border-t-2 border-slate-100"></div>
                        <span class="flex-shrink mx-4 text-slate-400 text-sm font-black tracking-wider">OR</span>
                        <div class="flex-grow border-t-2 border-slate-100"></div>
                    </div>

                    <button
                        type="button"
                        class="btn-game-hover w-full py-4 bg-blue-600 hover:bg-blue-500 text-white font-black text-xl rounded-full border-b-4 border-blue-800 active:border-b-0 active:translate-y-1 active:shadow-none shadow-md transition-all flex items-center justify-center space-x-3 uppercase tracking-wide"
                    >
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 127.14 96.36"><path d="M107.7,8.07A105.15,105.15,0,0,0,77.26,0a77.19,77.19,0,0,0-3.3,6.83A96.67,96.67,0,0,0,53.22,6.83,77.19,77.19,0,0,0,49.88,0,105.15,105.15,0,0,0,19.44,8.07C3.66,31.58-1.86,54.65,1,77.53A105.73,105.73,0,0,0,32,96.36a74.37,74.37,0,0,0,6.73-10.93,68.6,68.6,0,0,1-10.64-5.12c.91-.66,1.8-1.34,2.65-2a75.58,75.58,0,0,0,72.6,0c.85.69,1.74,1.37,2.65,2a68.42,68.42,0,0,1-10.63,5.11,75.13,75.13,0,0,0,6.73,10.94,105.54,105.54,0,0,0,31.05-18.83C129.87,48.24,123.83,25.42,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53S36.18,40.36,42.45,40.36,53.83,46,53.83,53,48.72,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.24,60,73.24,53S78.41,40.36,84.69,40.36,96.07,46,96.07,53,91,65.69,84.69,65.69Z"/></svg>
                        <span>Discord Sign In</span>
                    </button>
                </form>

                <div class="mt-8 text-center">
                    <p class="text-sm font-bold text-slate-500">
                        Pas encore de compte ? 
                        <Link :href="route('register')" class="text-blue-600 hover:text-blue-700 underline underline-offset-4 ml-1">
                            S'inscrire
                        </Link>
                    </p>
                </div>
            </div>

            <div class="hidden lg:flex lg:col-span-7 flex-col items-center justify-center p-12 text-center relative h-full">
                
                <div class="logo-float w-64 h-64 rounded-3xl overflow-hidden shadow-[0_25px_60px_rgba(234,179,8,0.4)] border-4 border-white mb-12">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300" width="100%" height="100%">
                        <rect width="300" height="300" rx="12" fill="#FFC107" />
                        <g transform="translate(60, 75) scale(4.5)">
                            <g fill="none" stroke="#1565C0" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M 0,33 L 0,26 L 6,23 L 11,25 L 11,33" />
                                <line x1="3" y1="27" x2="3" y2="28" />
                                <line x1="3" y1="30" x2="3" y2="31" />
                                <line x1="8" y1="27" x2="8" y2="28" />
                                <line x1="8" y1="30" x2="8" y2="31" />

                                <path d="M 11,33 L 11,4 L 20,0 L 26,2 L 26,33" />
                                <line x1="15" y1="6" x2="15" y2="7" />
                                <line x1="15" y1="10" x2="15" y2="11" />
                                <line x1="15" y1="14" x2="15" y2="15" />
                                <line x1="15" y1="18" x2="15" y2="19" />
                                <line x1="15" y1="22" x2="15" y2="23" />
                                <line x1="15" y1="26" x2="15" y2="27" />

                                <line x1="21" y1="7" x2="21" y2="8" />
                                <line x1="21" y1="11" x2="21" y2="12" />
                                <line x1="21" y1="15" x2="21" y2="16" />
                                <line x1="21" y1="19" x2="21" y2="20" />
                                <line x1="21" y1="23" x2="21" y2="24" />
                                <line x1="21" y1="27" x2="21" y2="28" />

                                <path d="M 26,33 L 26,14 L 33,10 L 41,13 L 41,29" />
                                <line x1="31" y1="15" x2="31" y2="16" />
                                <line x1="31" y1="19" x2="31" y2="20" />
                                <line x1="31" y1="23" x2="21" y2="24" />
                                
                                <line x1="36" y1="17" x2="36" y2="18" />
                                <line x1="36" y1="21" x2="36" y2="22" />
                                <line x1="36" y1="25" x2="36" y2="26" />
                            </g>
                        </g>
                    </svg>
                </div>

                <div class="space-y-3 pointer-events-none select-none">
                    <div class="pop-text text-7xl font-black text-white tracking-wide uppercase drop-shadow-[0_6px_0_#1d4ed8]">
                        WELCOME
                    </div>
                    <div class="pop-text text-6xl font-black text-yellow-400 tracking-wide uppercase drop-shadow-[0_5px_0_#b45309]">
                        EXPLORER
                    </div>
                    <div class="pop-text text-2xl font-bold text-cyan-100 tracking-widest uppercase opacity-90">
                        Ready for the next discovery?
                    </div>
                </div>

                <div class="mt-12 flex gap-4">
                    <button class="btn-game-hover bg-white/10 hover:bg-white/20 border-2 border-white/20 p-4 rounded-2xl shadow-[0_4px_0_rgba(0,0,0,0.2)] text-xl text-white">🏠</button>
                    <button class="btn-game-hover bg-white/10 hover:bg-white/20 border-2 border-white/20 p-4 rounded-2xl shadow-[0_4px_0_rgba(0,0,0,0.2)] text-xl text-white">🔊</button>
                    <button class="btn-game-hover bg-white/10 hover:bg-white/20 border-2 border-white/20 p-4 rounded-2xl shadow-[0_4px_0_rgba(0,0,0,0.2)] text-xl text-white">⚙️</button>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
/* Effet léger de scanline hérité pour l'immersion arcade */
.game-bg::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.04) 50%);
    background-size: 100% 4px;
    pointer-events: none;
    z-index: 1;
}
</style>