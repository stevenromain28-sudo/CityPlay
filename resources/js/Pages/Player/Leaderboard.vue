<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <PlayerLayout title="Classement Mondial">
        <div class="space-y-10">
            <div>
                <h2 class="text-4xl md:text-5xl font-black italic uppercase tracking-tighter text-slate-800">Le <span class="text-[#7C3AED]">Classement</span></h2>
                <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mt-2">Qui sera le plus grand explorateur ?</p>
            </div>

            <!-- Top 3 Podium -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 items-end py-10">
                <!-- Rank 2 -->
                <div class="bg-white p-8 rounded-[3rem] shadow-xl border border-blue-50 flex flex-col items-center relative order-2 md:order-1 h-80 justify-center">
                    <div class="absolute -top-10 w-24 h-24 rounded-3xl bg-slate-100 p-1 rotate-6 shadow-lg">
                        <img src="https://ui-avatars.com/api/?name=User2&background=slate&color=fff" class="w-full h-full rounded-2xl object-cover">
                    </div>
                    <div class="mt-8 text-center">
                        <h3 class="text-2xl font-black italic uppercase text-slate-800">Explorateur 2</h3>
                        <p class="text-[#7C3AED] font-black text-xl">12,450 PTS</p>
                    </div>
                    <div class="mt-6 w-12 h-12 bg-slate-200 rounded-2xl flex items-center justify-center text-slate-600 font-black text-xl">2</div>
                </div>

                <!-- Rank 1 -->
                <div class="bg-white p-10 rounded-[3rem] shadow-2xl border-2 border-yellow-400 flex flex-col items-center relative order-1 md:order-2 h-96 justify-center scale-110 z-10">
                    <div class="absolute -top-12 w-32 h-32 rounded-[2.5rem] bg-yellow-400 p-1 -rotate-3 shadow-2xl shadow-yellow-200">
                        <img src="https://ui-avatars.com/api/?name=King&background=yellow&color=fff" class="w-full h-full rounded-[2.2rem] object-cover">
                    </div>
                    <div class="absolute -top-16 text-yellow-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 drop-shadow-lg" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                    </div>
                    <div class="mt-12 text-center">
                        <h3 class="text-3xl font-black italic uppercase text-slate-800">Le Maître</h3>
                        <p class="text-[#7C3AED] font-black text-2xl">15,800 PTS</p>
                    </div>
                    <div class="mt-8 w-16 h-16 bg-yellow-400 rounded-3xl flex items-center justify-center text-white font-black text-3xl shadow-xl shadow-yellow-100">1</div>
                </div>

                <!-- Rank 3 -->
                <div class="bg-white p-8 rounded-[3rem] shadow-xl border border-blue-50 flex flex-col items-center relative order-3 h-72 justify-center">
                    <div class="absolute -top-10 w-20 h-20 rounded-2xl bg-orange-100 p-1 -rotate-6 shadow-lg">
                        <img src="https://ui-avatars.com/api/?name=User3&background=orange&color=fff" class="w-full h-full rounded-xl object-cover">
                    </div>
                    <div class="mt-6 text-center">
                        <h3 class="text-xl font-black italic uppercase text-slate-800">Explorateur 3</h3>
                        <p class="text-[#7C3AED] font-black text-lg">10,120 PTS</p>
                    </div>
                    <div class="mt-4 w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center text-orange-600 font-black text-lg">3</div>
                </div>
            </div>

            <!-- Rest of Leaderboard Table -->
            <div class="bg-white rounded-[3rem] shadow-xl shadow-blue-50 border border-blue-50 overflow-hidden mb-12">
                <div class="divide-y divide-blue-50">
                    <div v-for="i in 5" :key="i" class="p-8 flex items-center justify-between hover:bg-blue-50/50 transition-colors">
                        <div class="flex items-center space-x-8">
                            <span class="text-2xl font-black italic text-slate-300 w-8">{{ i + 3 }}</span>
                            <div class="w-14 h-14 rounded-2xl overflow-hidden bg-blue-100">
                                <img :src="`https://ui-avatars.com/api/?name=Player${i+3}&background=random`" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <h5 class="text-xl font-black italic uppercase text-slate-800">Joueur {{ i + 3 }}</h5>
                                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Niveau 12 • 45 Énigmes résolues</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-black italic text-[#7C3AED]">{{ 9000 - (i * 500) }} PTS</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PlayerLayout>
</template>

<style scoped>
h2, h3, h5, span, p {
    font-family: 'Bangers', cursive;
}
</style>
