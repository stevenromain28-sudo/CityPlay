<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    lieu: Object
});

const messages = ref([
    { id: 1, user: 'Système', text: 'Bienvenue sur le canal de synchronisation !', time: '10:00', system: true },
]);

const newMessage = ref('');

const sendMessage = () => {
    if (newMessage.value.trim()) {
        messages.value.push({
            id: Date.now(),
            user: 'Moi',
            text: newMessage.value,
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            system: false
        });
        newMessage.value = '';
    }
};
</script>

<template>
    <PlayerLayout :title="lieu ? 'Chat - ' + lieu.nom : 'Canal Temps Réel'">
        <div class="h-[calc(100vh-12rem)] flex flex-col gap-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 shrink-0">
                <div v-if="lieu">
                    <h2 class="text-4xl md:text-5xl font-black italic uppercase tracking-tighter text-slate-800">Canal : <span class="text-[#7C3AED]">{{ lieu.nom }}</span></h2>
                    <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mt-2">Synchronisation en temps réel pour ce lieu</p>
                </div>
                <div v-else>
                    <h2 class="text-4xl md:text-5xl font-black italic uppercase tracking-tighter text-slate-800">Canal <span class="text-[#7C3AED]">Websocket</span></h2>
                    <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mt-2">Sélectionnez un lieu pour rejoindre la discussion</p>
                </div>
                <div class="flex items-center space-x-3 bg-green-50 px-6 py-3 rounded-2xl border border-green-100">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-green-600 font-black text-xs uppercase tracking-widest">Connecté</span>
                </div>
            </div>

            <div v-if="lieu" class="flex-1 bg-white rounded-[3rem] shadow-xl shadow-blue-50 border border-blue-50 flex flex-col overflow-hidden">
                <!-- Chat Messages -->
                <div class="flex-1 overflow-y-auto p-10 space-y-8 custom-scrollbar">
                    <div v-for="msg in messages" :key="msg.id" 
                         :class="msg.user === 'Moi' ? 'flex flex-col items-end' : 'flex flex-col items-start'">
                        <div class="flex items-center space-x-3 mb-2">
                            <span v-if="msg.user !== 'Moi'" class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ msg.user }}</span>
                            <span class="text-[10px] font-bold text-slate-300">{{ msg.time }}</span>
                            <span v-if="msg.user === 'Moi'" class="text-[10px] font-black uppercase tracking-widest text-[#7C3AED]">Moi</span>
                        </div>
                        <div :class="[
                            'max-w-md p-6 rounded-[2rem] text-sm font-bold shadow-sm',
                            msg.system ? 'bg-blue-50 text-blue-600 italic text-center w-full max-w-none' : 
                            msg.user === 'Moi' ? 'bg-[#7C3AED] text-white rounded-tr-none' : 'bg-slate-50 text-slate-700 rounded-tl-none'
                        ]">
                            {{ msg.text }}
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="p-8 bg-blue-50/50 border-t border-blue-50">
                    <form @submit.prevent="sendMessage" class="relative">
                        <input v-model="newMessage" type="text" placeholder="ENVOYER UN MESSAGE À L'ÉQUIPE..." 
                               class="w-full bg-white border-none rounded-2xl py-6 px-8 text-xs font-bold tracking-widest text-slate-600 focus:ring-2 focus:ring-[#7C3AED] transition-all placeholder:text-slate-300 shadow-inner">
                        <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-[#7C3AED] text-white rounded-xl flex items-center justify-center hover:scale-110 transition-transform shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                        </button>
                    </form>
                </div>
            </div>
            <div v-else class="flex-1 flex items-center justify-center bg-white rounded-[3rem] shadow-xl border border-blue-50">
                 <p class="text-slate-400 text-lg font-bold uppercase tracking-widest">Discussion indisponible sans lieu sélectionné.</p>
            </div>
        </div>
    </PlayerLayout>
</template>

<style scoped>
h2, span, button {
    font-family: 'Bangers', cursive;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #7C3AED22;
    border-radius: 10px;
}
</style>
