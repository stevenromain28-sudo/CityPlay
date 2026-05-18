<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

// On garde STRICTEMENT tes champs d'origine
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
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
    <div 
        class="relative min-h-screen flex items-center justify-start bg-cover bg-center overflow-hidden font-sans select-none p-4 md:p-0"
        style="background-image: url('https://images.unsplash.com/photo-1548013146-72479768bada?auto=format&fit=crop&w=1920&q=80');"
    >
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-[2px]"></div>

        <div class="absolute top-10 right-10 w-72 h-72 bg-yellow-400/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-10 right-1/3 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl animate-bounce" style="animation-duration: 10s;"></div>

        <div 
            class="relative z-10 w-full max-w-lg min-h-screen bg-white p-8 md:p-12 flex flex-col justify-center shadow-2xl border-r-4 border-blue-600/20 transition-all duration-700 ease-out transform"
            :class="isLoaded ? 'translate-x-0 opacity-100' : '-translate-x-full opacity-0'"
        >
            
            <div class="text-center mb-8">
                <div class="inline-block transform -rotate-2 text-4xl font-black tracking-wider text-blue-600 drop-shadow-[0_4px_0_rgba(29,78,216,1)] uppercase select-none">
                    <span class="text-yellow-400 drop-shadow-[0_4px_0_rgba(234,179,8,1)]">City</span>Play
                </div>
                
                <h1 class="text-3xl font-extrabold text-slate-800 mt-6 tracking-tight">
                    Sign Up and <br>
                    <span class="text-blue-600">Play Free</span>
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                
                <div>
                    <div class="relative">
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            autofocus
                            placeholder="Name"
                            class="w-full px-6 py-3.5 bg-white border-2 border-slate-300 rounded-full text-slate-700 font-medium placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-200"
                        />
                    </div>
                    <InputError class="mt-1 px-4 text-xs font-bold text-red-500" :message="form.errors.name" />
                </div>

                <div>
                    <div class="relative">
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            placeholder="Email Address"
                            class="w-full px-6 py-3.5 bg-white border-2 border-slate-300 rounded-full text-slate-700 font-medium placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-200"
                        />
                    </div>
                    <InputError class="mt-1 px-4 text-xs font-bold text-red-500" :message="form.errors.email" />
                </div>

                <div>
                    <div class="relative">
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            required
                            placeholder="Password"
                            class="w-full px-6 py-3.5 bg-white border-2 border-slate-300 rounded-full text-slate-700 font-medium placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-200"
                        />
                    </div>
                    <InputError class="mt-1 px-4 text-xs font-bold text-red-500" :message="form.errors.password" />
                </div>

                <div>
                    <div class="relative">
                        <input
                            id="password_confirmation"
                            type="password"
                            v-model="form.password_confirmation"
                            required
                            placeholder="Confirm Password"
                            class="w-full px-6 py-3.5 bg-white border-2 border-slate-300 rounded-full text-slate-700 font-medium placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-200"
                        />
                    </div>
                    <InputError class="mt-1 px-4 text-xs font-bold text-red-500" :message="form.errors.password_confirmation" />
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-4 bg-yellow-400 hover:bg-yellow-300 text-slate-900 font-black text-lg rounded-full border-b-4 border-yellow-600 active:border-b-0 active:mt-1 shadow-md transition-all duration-100 uppercase tracking-wider"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    >
                        CREATE AN ACCOUNT
                    </button>
                </div>

                <div class="relative flex py-1 items-center">
                    <div class="flex-grow border-t-2 border-slate-100"></div>
                    <span class="flex-shrink mx-4 text-slate-400 text-xs font-bold tracking-wider">OR</span>
                    <div class="flex-grow border-t-2 border-slate-100"></div>
                </div>

                <button
                    type="button"
                    class="w-full py-4 bg-blue-600 hover:bg-blue-500 text-white font-black text-lg rounded-full border-b-4 border-blue-800 active:border-b-0 active:mt-1 shadow-md transition-all duration-100 flex items-center justify-center space-x-3 uppercase tracking-wider"
                >
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 127.14 96.36"><path d="M107.7,8.07A105.15,105.15,0,0,0,77.26,0a77.19,77.19,0,0,0-3.3,6.83A96.67,96.67,0,0,0,53.22,6.83,77.19,77.19,0,0,0,49.88,0,105.15,105.15,0,0,0,19.44,8.07C3.66,31.58-1.86,54.65,1,77.53A105.73,105.73,0,0,0,32,96.36a74.37,74.37,0,0,0,6.73-10.93,68.6,68.6,0,0,1-10.64-5.12c.91-.66,1.8-1.34,2.65-2a75.58,75.58,0,0,0,72.6,0c.85.69,1.74,1.37,2.65,2a68.42,68.42,0,0,1-10.63,5.11,75.13,75.13,0,0,0,6.73,10.94,105.54,105.54,0,0,0,31.05-18.83C129.87,48.24,123.83,25.42,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53S36.18,40.36,42.45,40.36,53.83,46,53.83,53,48.72,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.24,60,73.24,53S78.41,40.36,84.69,40.36,96.07,46,96.07,53,91,65.69,84.69,65.69Z"/></svg>
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

        <div class="hidden lg:flex absolute right-16 bottom-16 z-10 flex-col items-end space-y-3 animate-bounce-slow">
            <div class="flex -space-x-3">
                <img class="w-12 h-12 rounded-full border-4 border-yellow-400 bg-slate-200" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80" alt="Avatar">
                <img class="w-12 h-12 rounded-full border-4 border-blue-500 bg-slate-200" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80" alt="Avatar">
                <img class="w-12 h-12 rounded-full border-4 border-yellow-400 bg-slate-200" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=100&q=80" alt="Avatar">
            </div>
            <div class="text-right">
                <div class="text-5xl font-black text-white tracking-tight drop-shadow-[0_4px_4px_rgba(0,0,0,0.6)]">
                    12,200+
                </div>
                <div class="mt-1 inline-block bg-green-500 text-white font-black text-xs px-3 py-1 rounded-full uppercase tracking-widest shadow-md">
                    now online
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Effet d'oscillation fluide pour le badge de droite (style élément de jeu en lévitation) */
.animate-bounce-slow {
    animation: floating 4s ease-in-out infinite;
}

@keyframes floating {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
</style>