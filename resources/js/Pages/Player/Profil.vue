<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    user: Object,
    historique: Array
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
});

onMounted(() => {
    gsap.from('.profile-section', {
        y: 20,
        opacity: 0,
        stagger: 0.2,
        duration: 0.8,
        ease: 'power3.out'
    });
});

const updateProfile = () => {
    form.patch(route('profile.update'));
};
</script>

<template>
    <PlayerLayout title="Mon Profil">
        <div class="max-w-5xl mx-auto space-y-12">
            <!-- Header Profil -->
            <div class="profile-section bg-[#7C3AED] rounded-[3rem] p-12 text-white shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-bl-[10rem] -mr-20 -mt-20"></div>
                
                <div class="flex flex-col md:flex-row items-center space-y-8 md:space-y-0 md:space-x-12 relative z-10">
                    <div class="w-40 h-40 rounded-[3rem] bg-yellow-400 p-1 shadow-2xl rotate-3">
                        <img :src="`https://ui-avatars.com/api/?name=${user.name}&background=7C3AED&color=fff&size=200`" 
                             class="w-full h-full rounded-[2.8rem] object-cover" alt="Avatar">
                    </div>
                    
                    <div class="text-center md:text-left">
                        <h2 class="text-5xl font-black italic uppercase tracking-tighter mb-2">{{ user.name }}</h2>
                        <p class="text-white/70 text-sm font-bold uppercase tracking-widest mb-6 italic">Explorateur aguerri depuis {{ new Date(user.created_at).getFullYear() }}</p>
                        
                        <div class="flex flex-wrap justify-center md:justify-start gap-4">
                            <span class="px-6 py-2 bg-white/20 backdrop-blur-md rounded-2xl text-xs font-black uppercase tracking-widest border border-white/10">
                                Niveau 12
                            </span>
                            <span class="px-6 py-2 bg-white/20 backdrop-blur-md rounded-2xl text-xs font-black uppercase tracking-widest border border-white/10">
                                {{ user.score || 0 }} Points
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Paramètres -->
                <div class="profile-section lg:col-span-1 space-y-8">
                    <div class="bg-white p-10 rounded-[3rem] shadow-xl border border-blue-50">
                        <h3 class="text-2xl font-black italic uppercase text-slate-800 mb-8">Paramètres</h3>
                        
                        <form @submit.prevent="updateProfile" class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-2">Pseudo</label>
                                <input v-model="form.name" type="text" 
                                       class="w-full bg-blue-50 border-none rounded-2xl py-4 px-6 text-xs font-bold tracking-widest text-slate-600 focus:ring-2 focus:ring-[#7C3AED] transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-2">Email</label>
                                <input v-model="form.email" type="email" 
                                       class="w-full bg-blue-50 border-none rounded-2xl py-4 px-6 text-xs font-bold tracking-widest text-slate-600 focus:ring-2 focus:ring-[#7C3AED] transition-all">
                            </div>
                            <button type="submit" :disabled="form.processing"
                                    class="w-full py-4 bg-[#7C3AED] text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl hover:scale-105 transition-all">
                                Sauvegarder
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Historique -->
                <div class="profile-section lg:col-span-2 space-y-8">
                    <div class="bg-white p-10 rounded-[3rem] shadow-xl border border-blue-50">
                        <h3 class="text-2xl font-black italic uppercase text-slate-800 mb-8">Historique des Aventures</h3>
                        
                        <div v-if="historique && historique.length > 0" class="space-y-6">
                            <div v-for="item in historique" :key="item.id" 
                                 class="p-6 bg-blue-50/50 rounded-3xl border border-blue-50 flex items-center justify-between">
                                <div class="flex items-center space-x-6">
                                    <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-[#7C3AED] shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-black italic uppercase text-slate-800">{{ item.ville_nom }}</h4>
                                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">{{ new Date(item.date).toLocaleDateString() }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xl font-black italic text-[#7C3AED]">+{{ item.score }} PTS</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-12 text-center">
                            <p class="text-slate-400 text-sm font-bold uppercase tracking-widest italic">Aucune aventure terminée pour le moment.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PlayerLayout>
</template>

<style scoped>
h2, h3, h4, button, span {
    font-family: 'Bangers', cursive;
}
</style>
