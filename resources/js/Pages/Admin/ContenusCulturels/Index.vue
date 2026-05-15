<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import gsap from 'gsap';

const props = defineProps({
    contenus: Array,
    lieux: Array
});

const visible = ref(false);

const form = useForm({
    id: null,
    lieu_id: null,
    titre: '',
    description: '',
    audio: null
});

const openNew = () => {
    form.reset();
    form.id = null;
    visible.value = true;
};

const editContenu = (contenu) => {
    form.id = contenu.id;
    form.lieu_id = contenu.lieu_id;
    form.titre = contenu.titre;
    form.description = contenu.description;
    form.audio = null;
    visible.value = true;
};

const submit = () => {
    if (form.id) {
        form.post(route('admin.contenus-culturels.update', form.id), {
            onSuccess: () => visible.value = false
        });
    } else {
        form.post(route('admin.contenus-culturels.store'), {
            onSuccess: () => visible.value = false
        });
    }
};

const deleteContenu = () => {
    if (confirm('Voulez-vous vraiment effacer ce savoir ?')) {
        form.delete(route('admin.contenus-culturels.destroy', form.id), {
            onSuccess: () => visible.value = false
        });
    }
};

const onAudioSelect = (event) => {
    form.audio = event.files[0];
};

onMounted(() => {
    gsap.from('.culture-card', {
        opacity: 0,
        scale: 0.9,
        stagger: 0.1,
        duration: 0.8,
        ease: 'back.out(1.7)'
    });
});
</script>

<template>
    <Head title="Contenus Culturels - Admin" />

    <div class="min-h-screen bg-[#F0F7FF] font-sans p-6 md:p-12 relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-blue-100 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl opacity-50"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-yellow-100 rounded-full translate-y-1/2 -translate-x-1/2 blur-3xl opacity-50"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-16 gap-6">
                <div>
                    <Link :href="route('admin.dashboard')" class="flex items-center text-slate-400 font-black uppercase text-xs mb-2 hover:text-[#1DA1F2] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M15 19l-7-7 7-7" /></svg>
                        Retour au Dashboard
                    </Link>
                    <h2 class="text-6xl font-black italic uppercase text-slate-800 tracking-tighter">
                        Archives <span class="text-[#1DA1F2]">Culturelles</span>
                    </h2>
                    <p class="text-slate-400 font-bold uppercase tracking-widest mt-2">Enrichissez l'expérience de vos explorateurs</p>
                </div>
                <Button @click="openNew" class="!px-10 !py-5 !bg-[#1DA1F2] !border-none !rounded-[2rem] !shadow-2xl !shadow-blue-200 hover:!scale-105 transition-transform !flex !items-center">
                    <template #default>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 4v16m8-8H4" /></svg>
                        <span class="text-white font-black italic uppercase tracking-widest text-lg">Ajouter un Savoir</span>
                    </template>
                </Button>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <div v-for="contenu in contenus" :key="contenu.id" 
                     @click="editContenu(contenu)"
                     class="culture-card group bg-white rounded-[3rem] p-10 shadow-xl shadow-blue-100 border-2 border-transparent hover:border-[#1DA1F2] transition-all cursor-pointer relative overflow-hidden">
                    
                    <div class="absolute top-0 right-0 w-32 h-32 bg-[#1DA1F2]/5 rounded-bl-[5rem] -mr-10 -mt-10 group-hover:bg-[#1DA1F2]/10 transition-colors"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-8">
                            <span class="px-4 py-2 bg-blue-50 text-[#1DA1F2] text-[10px] font-black uppercase rounded-xl tracking-widest">{{ contenu.lieu.nom }}</span>
                            <div v-if="contenu.audio" class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center text-white shadow-lg shadow-yellow-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.983 5.983 0 01-1.414 4.243 1 1 0 11-1.414-1.415A3.984 3.984 0 0013 10a3.984 3.984 0 00-1.172-2.828a1 1 0 010-1.415z" clip-rule="evenodd" /></svg>
                            </div>
                        </div>

                        <h3 class="text-3xl font-black italic uppercase text-slate-800 tracking-tighter leading-none mb-4 group-hover:text-[#1DA1F2] transition-colors">
                            {{ contenu.titre }}
                        </h3>
                        
                        <p class="text-slate-500 font-bold text-sm line-clamp-4 leading-relaxed">
                            {{ contenu.description }}
                        </p>

                        <div v-if="contenu.audio" class="mt-8 pt-8 border-t border-slate-50">
                            <audio :src="contenu.audio" controls class="w-full h-8 opacity-50 hover:opacity-100 transition-opacity"></audio>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="contenus.length === 0" class="col-span-full py-32 flex flex-col items-center justify-center text-center">
                    <div class="w-32 h-32 bg-white rounded-[3rem] shadow-2xl flex items-center justify-center mb-8 rotate-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    </div>
                    <h3 class="text-3xl font-black italic uppercase text-slate-800 tracking-tighter mb-4">Le Grimoire est vide</h3>
                    <p class="text-slate-400 font-bold uppercase tracking-widest max-w-md">Ajoutez des anecdotes culturelles pour rendre vos lieux inoubliables.</p>
                </div>
            </div>
        </div>

        <!-- Form Dialog -->
        <Dialog v-model:visible="visible" modal :style="{ width: '50rem' }" class="prime-light-dialog">
            <template #header>
                <div class="flex items-center">
                    <span class="text-4xl font-black italic uppercase text-[#1DA1F2] tracking-tighter">
                        {{ form.id ? 'Modifier le Savoir' : 'Graver un Nouveau Savoir' }}
                    </span>
                </div>
            </template>
            
            <form @submit.prevent="submit" class="space-y-10 py-8 px-4 font-sans">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-4">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Lieu associé</label>
                        <select v-model="form.lieu_id" :disabled="form.id" class="w-full rounded-2xl bg-slate-50 border-slate-100 p-4 font-bold text-slate-800 focus:ring-2 focus:ring-[#1DA1F2] appearance-none disabled:opacity-50">
                            <option value="" disabled>Choisir un lieu...</option>
                            <option v-for="lieu in lieux" :key="lieu.id" :value="lieu.id">{{ lieu.nom }}</option>
                        </select>
                    </div>
                    <div class="space-y-4">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Titre du contenu</label>
                        <InputText v-model="form.titre" class="w-full !rounded-2xl !bg-slate-50 !border-slate-100 !p-4 !font-bold !text-slate-800" placeholder="Ex: L'histoire du Vieux Pont" />
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Récit Culturel</label>
                    <textarea v-model="form.description" rows="6" class="w-full rounded-[2rem] bg-slate-50 border-slate-100 p-6 font-bold text-slate-800 focus:ring-2 focus:ring-[#1DA1F2] placeholder:text-slate-300" placeholder="Racontez une anecdote passionnante sur ce lieu..."></textarea>
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Fichier Audio (Optionnel)</label>
                    <FileUpload mode="basic" name="audio" accept="audio/*" @select="onAudioSelect" class="w-full" chooseLabel="Ajouter une voix au récit" />
                </div>

                <div class="pt-6 flex space-x-4">
                    <Button v-if="form.id" type="button" @click="deleteContenu" class="!py-6 !bg-red-50 !text-red-500 !border-none !rounded-[2rem] hover:!bg-red-100 transition-all flex-1">
                        <span class="text-xl font-black italic uppercase tracking-widest">Effacer</span>
                    </Button>
                    <Button type="submit" :loading="form.processing" class="!py-6 !bg-[#1DA1F2] !border-none !rounded-[2rem] !shadow-2xl !shadow-blue-200 hover:!scale-[1.02] transition-transform flex-[2]">
                         <span class="text-2xl font-black italic uppercase text-white tracking-widest">Enregistrer l'Archive</span>
                    </Button>
                </div>
            </form>
        </Dialog>
    </div>
</template>

<style>
.prime-light-dialog .p-dialog {
    background: white !important;
    border-radius: 4rem !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1) !important;
}
.prime-light-dialog .p-dialog-header {
    background: transparent !important;
    padding: 3rem 3rem 0 3rem !important;
}
.prime-light-dialog .p-dialog-content {
    background: transparent !important;
    padding: 0 3rem 3rem 3rem !important;
}

@import url('https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;700;900&display=swap');
h2, h3, h4, span, button { font-family: 'Bangers', cursive; }
</style>
