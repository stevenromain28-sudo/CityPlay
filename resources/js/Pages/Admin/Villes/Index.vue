<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';
import Dialog from 'primevue/dialog';
import gsap from 'gsap';

const props = defineProps({
    ville: Object
});

const isEditing = ref(false);

const form = useForm({
    nom: props.ville?.nom || '',
    description: props.ville?.description || '',
    history: props.ville?.history || '',
    pays: props.ville?.pays || '',
    population: props.ville?.population || null,
    latitude: props.ville?.latitude || null,
    longitude: props.ville?.longitude || null,
    banniere: null,
});

const submit = () => {
    if (props.ville) {
        form.post(route('admin.villes.update', props.ville.id), {
            onSuccess: () => {
                isEditing.value = false;
            }
        });
    } else {
        form.post(route('admin.villes.store'), {
            onSuccess: () => {
                isEditing.value = false;
            }
        });
    }
};

const onFileSelect = (event) => {
    form.banniere = event.files[0];
};

onMounted(() => {
    gsap.from('.ville-container', {
        y: 30,
        opacity: 0,
        duration: 0.8,
        ease: 'power3.out'
    });
});
</script>

<template>
    <Head title="Ma Ville - Admin" />

    <div class="min-h-screen bg-[#F0F7FF] font-sans p-6 md:p-12">
        <div class="max-w-6xl mx-auto ville-container">
            <!-- Breadcrumb -->
            <Link :href="route('admin.dashboard')" class="flex items-center text-[#1DA1F2] font-black uppercase text-xs mb-8 hover:translate-x-[-5px] transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M15 19l-7-7 7-7" /></svg>
                Retour Dashboard
            </Link>

            <!-- Case 1: Ville non créée -->
            <div v-if="!ville && !isEditing" class="bg-white rounded-3xl md:rounded-[3rem] p-8 md:p-20 text-center shadow-2xl shadow-blue-100 border-4 border-dashed border-blue-100">
                <div class="w-16 h-16 md:w-24 md:h-24 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 md:mb-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 md:h-12 md:w-12 text-[#1DA1F2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
                <h2 class="text-3xl md:text-4xl font-black italic uppercase text-slate-800 tracking-tighter mb-4">Votre aventure commence ici</h2>
                <p class="text-slate-400 text-xs md:text-base font-bold uppercase tracking-widest mb-8 md:mb-10">Vous n'avez pas encore configuré votre ville de jeu.</p>
                <Button @click="isEditing = true" class="!px-8 !py-4 md:!px-10 md:!py-5 !bg-[#1DA1F2] !border-none !rounded-2xl !shadow-xl">
                    <span class="text-white font-black italic uppercase tracking-widest text-base md:text-lg">Créer ma Ville</span>
                </Button>
            </div>

            <!-- Case 2: Affichage de la Ville (Vue Prestige) -->
            <div v-if="ville && !isEditing" class="bg-white rounded-3xl md:rounded-[4rem] shadow-2xl shadow-blue-100 overflow-hidden border-2 border-white">
                <div class="relative h-[400px] md:h-[500px]">
                    <img :src="ville.banniere" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>
                    
                    <div class="absolute bottom-6 left-6 right-6 md:bottom-16 md:left-16 md:right-16 z-20">
                        <div class="flex flex-col sm:flex-row sm:items-center items-start space-y-2 sm:space-y-0 sm:space-x-4 mb-4 md:mb-6">
                            <span class="px-4 py-1.5 md:px-6 md:py-2 bg-yellow-400 text-white text-[10px] md:text-xs font-black uppercase rounded-2xl tracking-[0.2em] shadow-lg shadow-yellow-400/20">
                                {{ ville.lieux_count }} POINTS D'EXPLORATION
                            </span>
                            <span class="px-4 py-1.5 md:px-6 md:py-2 bg-white/10 backdrop-blur-md text-white text-[10px] md:text-xs font-black uppercase rounded-2xl tracking-[0.2em] border border-white/20">
                                {{ ville.pays }} • {{ ville.population?.toLocaleString() }} HAB.
                            </span>
                        </div>
                        <h1 class="text-5xl md:text-8xl font-black italic uppercase text-white tracking-tighter leading-none mb-3 md:mb-6">{{ ville.nom }}</h1>
                        <p class="text-white/80 text-sm md:text-xl font-bold max-w-3xl leading-relaxed italic line-clamp-3 md:line-clamp-none">"{{ ville.description }}"</p>
                    </div>

                    <div class="absolute top-4 right-4 md:top-10 md:right-10 flex space-x-2 md:space-x-4 z-30">
                        <Button @click="isEditing = true" class="!p-3 md:!p-4 !bg-white !text-[#1DA1F2] !border-none !rounded-xl md:!rounded-2xl !shadow-2xl hover:!scale-110 transition-transform">
                            <template #default>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </template>
                        </Button>
                        <Link :href="route('admin.lieux.index')" class="p-3 md:p-4 bg-yellow-400 text-white rounded-xl md:rounded-2xl shadow-2xl shadow-yellow-400/40 hover:scale-110 transition-transform flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6 md:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <span class="font-black italic uppercase tracking-widest hidden md:inline">Gérer la carte</span>
                        </Link>
                    </div>
                </div>

                <div class="p-6 md:p-16 grid grid-cols-1 lg:grid-cols-3 gap-8 md:gap-16">
                    <div class="lg:col-span-2 space-y-6 md:space-y-8">
                        <h3 class="text-2xl md:text-3xl font-black italic uppercase text-[#1DA1F2] tracking-tighter">L'Histoire Secrète</h3>
                        <div class="text-slate-600 font-bold leading-relaxed md:leading-loose text-sm md:text-lg whitespace-pre-line">
                            {{ ville.history }}
                        </div>
                    </div>
                    <div class="space-y-10">
                        <div class="bg-blue-50/50 rounded-3xl md:rounded-[2.5rem] p-6 md:p-10 border-2 border-blue-50">
                            <h4 class="text-lg md:text-xl font-black italic uppercase text-slate-400 tracking-widest mb-4 md:mb-6">Coordonnées GPS</h4>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center border-b border-blue-100 pb-4">
                                    <span class="text-[10px] font-black uppercase text-slate-400">Latitude</span>
                                    <span class="font-black text-[#1DA1F2] text-sm md:text-base">{{ ville.latitude }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-2">
                                    <span class="text-[10px] font-black uppercase text-slate-400">Longitude</span>
                                    <span class="font-black text-[#1DA1F2] text-sm md:text-base">{{ ville.longitude }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Case 3: Formulaire de Création/Edition (Modal-like view) -->
            <div v-if="isEditing" class="bg-white rounded-3xl md:rounded-[3rem] shadow-2xl overflow-hidden border-2 border-white mt-8">
                <div class="h-24 md:h-32 bg-[#1DA1F2] flex items-center px-6 md:px-12 justify-between">
                    <h2 class="text-2xl md:text-4xl font-black italic uppercase text-white tracking-tighter">Configuration de la <span class="text-yellow-400">Cité</span></h2>
                    <Button icon="pi pi-times" @click="isEditing = false" class="!bg-white/10 !text-white !border-none !rounded-full" />
                </div>

                <form @submit.prevent="submit" class="p-6 md:p-12 lg:p-16 space-y-8 md:space-y-10 font-sans">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10">
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Nom de la ville</label>
                            <InputText v-model="form.nom" placeholder="Ex: Paris, Annecy..." class="w-full !rounded-2xl !border-blue-100 !bg-blue-50/30 !p-4 !font-bold !text-slate-800" />
                        </div>
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Pays</label>
                            <InputText v-model="form.pays" placeholder="France" class="w-full !rounded-2xl !border-blue-100 !bg-blue-50/30 !p-4 !font-bold !text-slate-800" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-10">
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Population</label>
                            <InputText v-model="form.population" type="number" class="w-full !rounded-2xl !border-blue-100 !bg-blue-50/30 !p-4 !font-bold !text-slate-800" />
                        </div>
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Latitude (Base)</label>
                            <InputText v-model="form.latitude" placeholder="45.8992" class="w-full !rounded-2xl !border-blue-100 !bg-blue-50/30 !p-4 !font-bold !text-slate-800" />
                        </div>
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Longitude (Base)</label>
                            <InputText v-model="form.longitude" placeholder="6.1264" class="w-full !rounded-2xl !border-blue-100 !bg-blue-50/30 !p-4 !font-bold !text-slate-800" />
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Description courte</label>
                        <Textarea v-model="form.description" rows="2" class="w-full !rounded-2xl !border-blue-100 !bg-blue-50/30 !p-4 !font-bold !text-slate-800" />
                    </div>

                    <div class="space-y-4">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Histoire & Légendes</label>
                        <Textarea v-model="form.history" rows="6" class="w-full !rounded-[2rem] !border-blue-100 !bg-blue-50/30 !p-6 !font-bold !text-slate-800" />
                    </div>

                    <div class="space-y-4">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Bannière Prestige</label>
                        <FileUpload mode="basic" name="banniere" accept="image/*" @select="onFileSelect" class="w-full" chooseLabel="Choisir un visuel épique" />
                    </div>

                    <div class="pt-6">
                        <Button type="submit" :loading="form.processing" class="w-full !py-4 md:!py-6 !bg-[#1DA1F2] !border-none !rounded-2xl md:!rounded-3xl !shadow-xl !shadow-blue-200">
                            <span class="text-xl md:text-2xl font-black italic uppercase tracking-tighter">Enregistrer le Destin</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;700;900&display=swap');
h1, h2, h3, h4, span, button { font-family: 'Bangers', cursive; }
.font-sans { font-family: 'Outfit', sans-serif !important; }
</style>
