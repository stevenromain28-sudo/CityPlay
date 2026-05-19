<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    adminRequest: {
        type: Boolean,
        default: false
    }
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    admin_request: props.adminRequest ? 1 : 0,
    requested_city: '',
});

const isLoaded = ref(false);

onMounted(() => {
    isLoaded.value = true;
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <component :is="'style'">
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap');
        .font-gaming {
            font-family: 'Fredoka', sans-serif;
        }
    </component>

    <div class="relative min-h-screen flex items-center justify-start bg-[#0f172a] overflow-hidden font-gaming select-none">
        
        <div 
            class="hidden lg:block absolute inset-y-0 right-0 left-[32rem] bg-cover bg-center overflow-hidden"
            style="background-image: url('https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?auto=format&fit=crop&w=1200&q=80');"
        >
            <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-[1px]"></div>
            
            <div class="absolute top-1/4 right-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse"></div>
            <div class="absolute bottom-1/4 right-1/3 w-[400px] h-[400px] bg-yellow-500/20 rounded-full blur-[100px]"></div>

            <div class="absolute inset-0 flex flex-col items-center justify-center p-12 text-center">
                
                <div class="w-64 h-64 rounded-3xl overflow-hidden shadow-[0_20px_50px_rgba(234,179,8,0.4)] border-4 border-white transform animate-float mb-10">
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
                                <line x1="31" y1="23" x2="31" y2="24" />
                                
                                <line x1="36" y1="17" x2="36" y2="18" />
                                <line x1="36" y1="21" x2="36" y2="22" />
                                <line x1="36" y1="25" x2="36" y2="26" />
                            </g>
                        </g>
                    </svg>
                </div>

                <div class="space-y-3 select-none pointer-events-none">
                    <div class="text-7xl font-black text-white tracking-wide uppercase drop-shadow-[0_6px_0_#1d4ed8] animate-pop-in-1">
                        EXPLORE
                    </div>
                    <div class="text-6xl font-black text-yellow-400 tracking-wide uppercase drop-shadow-[0_5px_0_#b45309] animate-pop-in-2">
                        YOUR CITY
                    </div>
                    <div class="text-3xl font-bold text-slate-300 tracking-wider uppercase opacity-90 animate-pop-in-3">
                        & Unlock History
                    </div>
                </div>

                <div class="mt-12 flex flex-col items-center space-y-2 animate-fade-in-delayed">
                    <div class="flex -space-x-2">
                        <img class="w-10 h-10 rounded-full border-2 border-yellow-400 bg-slate-700" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80" alt="Avatar">
                        <img class="w-10 h-10 rounded-full border-2 border-blue-500 bg-slate-700" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80" alt="Avatar">
                        <img class="w-10 h-10 rounded-full border-2 border-yellow-400 bg-slate-700" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=100&q=80" alt="Avatar">
                    </div>
                    <div class="text-3xl font-black text-white tracking-wide drop-shadow-md">
                        12,200+
                    </div>
                    <div class="bg-green-500 text-white font-black text-[10px] px-3 py-0.5 rounded-full uppercase tracking-widest animate-pulse">
                        now online
                    </div>
                </div>

            </div>
        </div>

        <div 
            class="relative z-10 w-full max-w-lg min-h-screen bg-white p-8 md:p-12 flex flex-col justify-center shadow-2xl transition-all duration-700 ease-out transform border-r-4 border-blue-600/10"
            :class="isLoaded ? 'translate-x-0 opacity-100' : '-translate-x-full opacity-0'"
        >
            
            <div class="text-center mb-8">
                <div class="inline-block transform -rotate-2 text-5xl font-black tracking-wider text-blue-600 drop-shadow-[0_5px_0_rgba(29,78,216,1)] uppercase">
                    <span class="text-yellow-400 drop-shadow-[0_5px_0_rgba(234,179,8,1)]">City</span>Play
                </div>
                <h1 class="text-3xl font-black text-slate-800 mt-6 tracking-wide">
                    <template v-if="adminRequest">
                        Demande d'adhésion <br>
                        <span class="text-blue-600">Administration</span>
                    </template>
                    <template v-else>
                        Sign Up and <br>
                        <span class="text-blue-600">Play Free</span>
                    </template>
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                
                <div>
                    <input
                        id="name"
                        type="text"
                        v-model="form.name"
                        required
                        autofocus
                        placeholder="Name"
                        class="w-full px-6 py-3.5 bg-white border-2 border-slate-300 rounded-full text-slate-700 font-bold placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-200 text-base"
                    />
                    <InputError class="mt-1 px-4 text-xs font-bold text-red-500" :message="form.errors.name" />
                </div>

                <div>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        placeholder="Email Address"
                        class="w-full px-6 py-3.5 bg-white border-2 border-slate-300 rounded-full text-slate-700 font-bold placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-200 text-base"
                    />
                    <InputError class="mt-1 px-4 text-xs font-bold text-red-500" :message="form.errors.email" />
                </div>

                <div v-if="adminRequest">
                    <input
                        id="requested_city"
                        type="text"
                        v-model="form.requested_city"
                        required
                        placeholder="Ville à gérer"
                        class="w-full px-6 py-3.5 bg-white border-2 border-slate-300 rounded-full text-slate-700 font-bold placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-200 text-base"
                    />
                    <InputError class="mt-1 px-4 text-xs font-bold text-red-500" :message="form.errors.requested_city" />
                </div>

                <div>
                    <input
                        id="password"
                        type="password"
                        v-model="form.password"
                        required
                        placeholder="Password"
                        class="w-full px-6 py-3.5 bg-white border-2 border-slate-300 rounded-full text-slate-700 font-bold placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-200 text-base"
                    />
                    <InputError class="mt-1 px-4 text-xs font-bold text-red-500" :message="form.errors.password" />
                </div>

                <div>
                    <input
                        id="password_confirmation"
                        type="password"
                        v-model="form.password_confirmation"
                        required
                        placeholder="Confirm Password"
                        class="w-full px-6 py-3.5 bg-white border-2 border-slate-300 rounded-full text-slate-700 font-bold placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-200 text-base"
                    />
                    <InputError class="mt-1 px-4 text-xs font-bold text-red-500" :message="form.errors.password_confirmation" />
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-4 bg-yellow-400 hover:bg-yellow-300 text-slate-900 font-black text-xl rounded-full border-b-4 border-yellow-600 active:border-b-0 active:translate-y-1 active:shadow-none shadow-md transition-all duration-100 uppercase tracking-wide"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    >
                        {{ adminRequest ? 'SOUMETTRE LA DEMANDE' : 'CREATE AN ACCOUNT' }}
                    </button>
                </div>

                <div class="relative flex py-1 items-center">
                    <div class="flex-grow border-t-2 border-slate-100"></div>
                    <span class="flex-shrink mx-4 text-slate-400 text-sm font-black tracking-wider">OR</span>
                    <div class="flex-grow border-t-2 border-slate-100"></div>
                </div>

                <button
                    type="button"
                    class="w-full py-4 bg-blue-600 hover:bg-blue-500 text-white font-black text-xl rounded-full border-b-4 border-blue-800 active:border-b-0 active:translate-y-1 active:shadow-none shadow-md transition-all duration-100 flex items-center justify-center space-x-3 uppercase tracking-wide"
                >
                    <svg class="w-6 h-6 fill-current" viewBox="0 0 127.14 96.36"><path d="M107.7,8.07A105.15,105.15,0,0,0,77.26,0a77.19,77.19,0,0,0-3.3,6.83A96.67,96.67,0,0,0,53.22,6.83,77.19,77.19,0,0,0,49.88,0,105.15,105.15,0,0,0,19.44,8.07C3.66,31.58-1.86,54.65,1,77.53A105.73,105.73,0,0,0,32,96.36a74.37,74.37,0,0,0,6.73-10.93,68.6,68.6,0,0,1-10.64-5.12c.91-.66,1.8-1.34,2.65-2a75.58,75.58,0,0,0,72.6,0c.85.69,1.74,1.37,2.65,2a68.42,68.42,0,0,1-10.63,5.11,75.13,75.13,0,0,0,6.73,10.94,105.54,105.54,0,0,0,31.05-18.83C129.87,48.24,123.83,25.42,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53S36.18,40.36,42.45,40.36,53.83,46,53.83,53,48.72,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.24,60,73.24,53S78.41,40.36,84.69,40.36,96.07,46,96.07,53,91,65.69,84.69,65.69Z"/></svg>
                    <span>CONTINUE WITH DISCORD</span>
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm font-bold text-slate-500">
                    Already Have an Account? 
                    <Link :href="route('login')" class="text-blue-600 hover:text-blue-700 underline underline-offset-4 ml-1">
                        Log In
                    </Link>
                </p>
            </div>
        </div>

    </div>
</template>

<style scoped>
/* Animation de lévitation fluide du logo SVG */
.animate-float {
    animation: floatingLogo 4s ease-in-out infinite;
}

@keyframes floatingLogo {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-12px) rotate(1.5deg); }
}

/* Animations d'apparition séquentielles en pop pour les textes */
.animate-pop-in-1 { animation: popIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s forwards; opacity: 0; transform: scale(0.8); }
.animate-pop-in-2 { animation: popIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.4s forwards; opacity: 0; transform: scale(0.8); }
.animate-pop-in-3 { animation: popIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.6s forwards; opacity: 0; transform: scale(0.8); }

@keyframes popIn {
    to { opacity: 1; transform: scale(1); }
}

.animate-fade-in-delayed {
    animation: fadeIn 0.8s ease-out 0.8s forwards;
    opacity: 0;
}
@keyframes fadeIn {
    to { opacity: 1; }
}
</style>