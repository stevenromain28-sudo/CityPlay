<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { onMounted } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    leaderboard: Array,
    mon_rang: Object
});

onMounted(() => {
    gsap.from('.rank-item', {
        x: 50,
        opacity: 0,
        stagger: 0.1,
        duration: 0.6,
        ease: 'power3.out'
    });
});
</script>

<template>
    <PlayerLayout title="Classement Mondial">
        <div class="space-y-12">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6">
                <div>
                    <h2 class="text-4xl md:text-5xl font-black italic uppercase tracking-tighter text-slate-800">Le <span class="text-[#7C3AED]">Classement</span></h2>
                    <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mt-2">Qui sera le plus grand explorateur ?</p>
                </div>
                
                <div v-if="mon_rang" class="bg-[#7C3AED] p-6 rounded-3xl text-white shadow-xl shadow-purple-100 rotate-2">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Votre Rang</p>
                    <div class="flex items-end space-x-2">
                        <span class="text-3xl font-black italic leading-none">#{{ mon_rang.rank }}</span>
                        <span class="text-xs font-bold uppercase mb-1">{{ mon_rang.points }} PTS</span>
                    </div>
                </div>
            </div>

            <!-- Table des scores -->
            <div class="bg-white rounded-[3rem] shadow-xl shadow-blue-50 border border-blue-50 overflow-hidden">
                <div class="divide-y divide-blue-50">
                    <div v-for="(player, index) in leaderboard" :key="player.id" 
                         class="rank-item p-8 flex items-center justify-between hover:bg-blue-50/50 transition-colors"
                         :class="{'bg-yellow-50/50': index === 0}">
                        
                        <div class="flex items-center space-x-8">
                            <!-- Rang -->
                            <div class="w-12 h-12 flex items-center justify-center rounded-2xl font-black italic text-2xl"
                                 :class="[
                                    index === 0 ? 'bg-yellow-400 text-white shadow-lg' : 
                                    index === 1 ? 'bg-slate-200 text-slate-600' :
                                    index === 2 ? 'bg-orange-100 text-orange-600' : 'text-slate-300'
                                 ]">
                                {{ index + 1 }}
                            </div>

                            <!-- Avatar & Infos -->
                            <div class="flex items-center space-x-6">
                                <div class="w-16 h-16 rounded-2xl overflow-hidden shadow-md">
                                    <img :src="`https://ui-avatars.com/api/?name=${player.name}&background=random`" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h5 class="text-xl font-black italic uppercase text-slate-800">{{ player.name }}</h5>
                                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">
                                        Niveau {{ player.level || 1 }} • {{ player.sessions_count || 0 }} Aventures
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-3xl font-black italic text-[#7C3AED]">{{ player.score || player.points }}</p>
                            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Points d'XP</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PlayerLayout>
</template>

<style scoped>
h2, h5, span, p {
    font-family: 'Bangers', cursive;
}
</style>
