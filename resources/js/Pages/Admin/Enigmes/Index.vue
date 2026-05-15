<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import Select from 'primevue/select';
import gsap from 'gsap';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    enigmes: Array,
    lieux: Array
});

const visible = ref(false);

const form = useForm({
    id: null,
    lieu_id: null,
    titre: '',
    contenu: '',
    reponse: '',
    niveau: 1,
    ordre: 1,
    image: null,
    audio: null,
    latitude: null,
    longitude: null,
    rayon: 50,
    verification_gps: true,
    indices: [{ contenu: '', penalite: 5 }]
});

const addIndice = () => {
    if (form.indices.length < 5) {
        form.indices.push({ contenu: '', penalite: 5 });
    }
};

const removeIndice = (index) => {
    form.indices.splice(index, 1);
};

const mapContainer = ref(null);
let map = null;
let marker = null;
let radiusCircle = null;

const initMap = () => {
    if (map) {
        map.remove();
        map = null;
    }

    const lat = form.latitude || 45.8992;
    const lng = form.longitude || 6.1264;

    map = L.map(mapContainer.value).setView([lat, lng], 15);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(map);

    const updateRadius = () => {
        if (radiusCircle) map.removeLayer(radiusCircle);
        if (form.latitude && form.longitude) {
            radiusCircle = L.circle([form.latitude, form.longitude], {
                radius: form.rayon || 50,
                color: '#1DA1F2',
                fillColor: '#1DA1F2',
                fillOpacity: 0.2,
                weight: 1
            }).addTo(map);
        }
    };

    if (form.latitude && form.longitude) {
        marker = L.marker([form.latitude, form.longitude], { draggable: true }).addTo(map);
        updateRadius();
        
        marker.on('drag', (e) => {
            const pos = e.target.getLatLng();
            form.latitude = pos.lat;
            form.longitude = pos.lng;
            updateRadius();
        });
    }

    map.on('click', (e) => {
        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng, { draggable: true }).addTo(map);
            marker.on('drag', (event) => {
                const pos = event.target.getLatLng();
                form.latitude = pos.lat;
                form.longitude = pos.lng;
                updateRadius();
            });
        }
        form.latitude = e.latlng.lat;
        form.longitude = e.latlng.lng;
        updateRadius();
    });

    setTimeout(() => {
        map.invalidateSize();
    }, 400);
};

const openNew = () => {
    form.reset();
    form.id = null;
    visible.value = true;
    setTimeout(initMap, 100);
};

const editEnigme = (enigme) => {
    form.id = enigme.id;
    form.lieu_id = enigme.lieu_id;
    form.titre = enigme.titre;
    form.contenu = enigme.contenu;
    form.reponse = enigme.reponse;
    form.niveau = enigme.niveau;
    form.ordre = enigme.ordre;
    form.latitude = enigme.latitude;
    form.longitude = enigme.longitude;
    form.rayon = enigme.rayon;
    form.verification_gps = !!enigme.verification_gps;
    form.indices = enigme.indices.map(i => ({ contenu: i.contenu, penalite: i.penalite }));
    visible.value = true;
    setTimeout(initMap, 100);
};

const submit = () => {
    if (form.id) {
        form.post(route('admin.enigmes.update', form.id), {
            onSuccess: () => visible.value = false
        });
    } else {
        form.post(route('admin.enigmes.store'), {
            onSuccess: () => visible.value = false
        });
    }
};

const onFileSelect = (event) => {
    form.image = event.files[0];
};

const onAudioSelect = (event) => {
    form.audio = event.files[0];
};

onMounted(() => {
    gsap.from('.enigme-card', {
        opacity: 0,
        y: 30,
        stagger: 0.1,
        duration: 0.8,
        ease: 'power3.out'
    });
});
</script>

<template>
    <Head title="Gestion des Énigmes - Admin" />

    <div class="min-h-screen bg-[#0a0c1b] font-sans p-6 md:p-12 relative overflow-hidden">
        <!-- Strange/Mysterious Background -->
        <div class="absolute inset-0 z-0 opacity-20">
            <div class="absolute inset-0 bg-gradient-to-br from-[#1DA1F2]/20 to-purple-900/40"></div>
            <img src="https://www.transparenttextures.com/patterns/dark-matter.png" class="absolute inset-0 w-full h-full opacity-50">
        </div>

        <div class="relative z-10 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-12 gap-6">
                <div>
                    <Link :href="route('admin.dashboard')" class="flex items-center text-white/60 font-black uppercase text-xs mb-2 hover:text-[#1DA1F2] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M15 19l-7-7 7-7" /></svg>
                        Quitter les ombres
                    </Link>
                    <h2 class="text-6xl font-black italic uppercase text-white tracking-tighter drop-shadow-[0_5px_15px_rgba(29,161,242,0.5)]">
                        Grimoire des <span class="text-yellow-400">Énigmes</span>
                    </h2>
                </div>
                <Button @click="openNew" class="!px-10 !py-5 !bg-[#1DA1F2] !border-none !rounded-[2rem] !shadow-2xl !shadow-blue-900 hover:!scale-105 transition-transform !flex !items-center">
                    <template #default>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 4v16m8-8H4" /></svg>
                        <span class="text-white font-black italic uppercase tracking-widest text-lg">Inscrire une Énigme</span>
                    </template>
                </Button>
            </div>

            <!-- Enigmes Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <div v-for="enigme in enigmes" :key="enigme.id" @click="editEnigme(enigme)" class="enigme-card group bg-white/5 backdrop-blur-xl rounded-[3rem] p-8 border-2 border-white/10 hover:border-[#1DA1F2]/50 transition-all cursor-pointer shadow-2xl relative">
                    <!-- Sketch/Drawing Card Style -->
                    <div class="absolute -top-4 -right-4 w-12 h-12 bg-yellow-400 rounded-2xl flex items-center justify-center shadow-xl rotate-12 group-hover:rotate-0 transition-transform">
                        <span class="text-white font-black text-xl italic">{{ enigme.ordre }}</span>
                    </div>

                    <div class="aspect-video rounded-2xl overflow-hidden mb-6 border-2 border-white/5 bg-slate-900">
                        <img v-if="enigme.image" :src="enigme.image" class="w-full h-full object-cover opacity-80 group-hover:scale-110 transition-transform duration-700">
                        <div v-else class="w-full h-full flex items-center justify-center text-white/10">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="px-3 py-1 bg-white/10 text-white/40 text-[10px] font-black uppercase rounded-lg tracking-widest">{{ enigme.lieu.nom }}</span>
                            <span class="px-3 py-1 bg-yellow-400/20 text-yellow-400 text-[10px] font-black uppercase rounded-lg">Niveau {{ enigme.niveau }}</span>
                        </div>
                        <h3 class="text-2xl font-black italic uppercase text-white tracking-tighter group-hover:text-[#1DA1F2] transition-colors">{{ enigme.titre }}</h3>
                        <p class="text-white/40 text-sm font-bold line-clamp-3 leading-relaxed italic">"{{ enigme.contenu }}"</p>
                        
                        <!-- Audio Player Small -->
                        <div v-if="enigme.audio" class="mt-4 flex items-center bg-white/5 rounded-2xl p-3 border border-white/10">
                            <audio :src="enigme.audio" controls class="h-8 w-full filter invert hue-rotate-180 opacity-50 hover:opacity-100 transition-opacity"></audio>
                        </div>
                    </div>

                    <!-- Indices Parchment Preview -->
                    <div class="mt-8 pt-6 border-t border-white/5 flex items-center justify-between">
                        <div class="flex -space-x-3">
                            <div v-for="i in enigme.indices.length" :key="i" class="w-10 h-12 bg-[#F5DEB3] rounded-sm shadow-lg border-x border-amber-900/20 flex flex-col items-center justify-center rotate-[-10deg] even:rotate-[10deg]">
                                <div class="w-6 h-[1px] bg-amber-900/20 my-0.5" v-for="j in 3" :key="j"></div>
                            </div>
                        </div>
                        <span class="text-[10px] font-black text-white/20 uppercase tracking-widest">{{ enigme.indices.length }} INDICES SCÉLLÉS</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Dialog -->
        <Dialog v-model:visible="visible" modal :style="{ width: '60rem' }" class="prime-dark-dialog">
            <template #header>
                <div class="flex items-center">
                    <span class="text-4xl font-black italic uppercase text-[#1DA1F2] tracking-tighter">Écrire le Destin</span>
                </div>
            </template>
            
            <form @submit.prevent="submit" class="space-y-10 py-8 custom-scrollbar max-h-[70vh] overflow-y-auto px-4 font-sans">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-4">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-2">Lieu associé</label>
                        <select v-model="form.lieu_id" class="w-full rounded-2xl bg-blue-50 border-blue-100 p-4 font-bold text-slate-800 focus:ring-2 focus:ring-[#1DA1F2] appearance-none !block">
                            <option value="" disabled>Choisir un lieu...</option>
                            <option v-for="lieu in lieux" :key="lieu.id" :value="lieu.id">{{ lieu.nom }}</option>
                        </select>
                    </div>
                    <div class="space-y-4">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-2">Titre de l'énigme</label>
                        <InputText v-model="form.titre" class="w-full !rounded-2xl !bg-blue-50 !border-blue-100 !p-4 !font-bold !text-slate-800" />
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-2">Le Mystère (Contenu)</label>
                    <textarea v-model="form.contenu" rows="4" class="w-full rounded-[2rem] bg-blue-50 border-blue-100 p-6 font-bold text-slate-800 focus:ring-2 focus:ring-[#1DA1F2] placeholder:text-slate-400" placeholder="Décrivez l'énigme de manière mystérieuse..."></textarea>
                </div>

                <!-- GPS Map Selection -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-[#1DA1F2] ml-2">Localisation de l'Énigme (GPS)</label>
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" v-model="form.verification_gps" id="gps_check" class="rounded text-[#1DA1F2]">
                            <label for="gps_check" class="text-[10px] font-black uppercase text-slate-400">Activer validation GPS</label>
                        </div>
                    </div>
                    <div class="h-64 rounded-3xl overflow-hidden border-2 border-blue-50">
                        <div ref="mapContainer" class="w-full h-full z-0"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-[8px] font-black uppercase text-slate-400 ml-2">Latitude</label>
                            <InputText v-model="form.latitude" readonly class="w-full !rounded-xl !bg-blue-50/50 !border-none !p-3 !text-xs !font-bold" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[8px] font-black uppercase text-slate-400 ml-2">Longitude</label>
                            <InputText v-model="form.longitude" readonly class="w-full !rounded-xl !bg-blue-50/50 !border-none !p-3 !text-xs !font-bold" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[8px] font-black uppercase text-slate-400 ml-2">Rayon de validation (m)</label>
                            <InputText v-model="form.rayon" type="number" class="w-full !rounded-xl !bg-blue-50/50 !border-none !p-3 !text-xs !font-bold" />
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    <div class="space-y-4">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-2">Réponse attendue</label>
                        <InputText v-model="form.reponse" class="w-full !rounded-2xl !bg-blue-50 !border-blue-100 !p-4 !font-bold !text-slate-800" />
                    </div>
                    <div class="space-y-4">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-2">Niveau (1-3)</label>
                        <InputText v-model="form.niveau" type="number" class="w-full !rounded-2xl !bg-blue-50 !border-blue-100 !p-4 !font-bold !text-slate-800" />
                    </div>
                    <div class="space-y-4">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-2">Ordre</label>
                        <InputText v-model="form.ordre" type="number" class="w-full !rounded-2xl !bg-blue-50 !border-blue-100 !p-4 !font-bold !text-slate-800" />
                    </div>
                </div>

                <!-- Indices Section (Parchments) -->
                <div class="space-y-8">
                    <div class="flex items-center justify-between">
                        <h4 class="text-2xl font-black italic uppercase text-yellow-400 tracking-tighter">Les Indices du Parchemin ({{ form.indices.length }}/5)</h4>
                        <Button v-if="form.indices.length < 5" type="button" icon="pi pi-plus" @click="addIndice" class="!bg-yellow-400/10 !text-yellow-400 !border-none !rounded-xl !p-3 hover:!bg-yellow-400/20 transition-all" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div v-for="(indice, index) in form.indices" :key="index" class="relative p-8 bg-[#F5DEB3] rounded-sm shadow-2xl border-x-4 border-amber-900/10 rotate-[-1deg] hover:rotate-0 transition-transform">
                            <div class="absolute -top-3 -left-3 w-8 h-8 bg-amber-900 text-white rounded-full flex items-center justify-center text-[10px] font-black">#{{ index + 1 }}</div>
                            <Button type="button" icon="pi pi-times" @click="removeIndice(index)" class="absolute -top-3 -right-3 !bg-red-500 !text-white !border-none !rounded-full !w-8 !h-8 !p-0" />
                            
                            <textarea v-model="indice.contenu" rows="2" class="w-full bg-transparent border-none p-0 font-bold text-amber-900 placeholder:text-amber-900/40 focus:ring-0 italic" placeholder="Écrivez l'indice ici..."></textarea>
                            <div class="mt-4 flex items-center justify-between border-t border-amber-900/20 pt-4">
                                <span class="text-[8px] font-black uppercase text-amber-900/60 tracking-widest">Pénalité Points</span>
                                <input type="number" v-model="indice.penalite" class="w-16 bg-white/40 border-none rounded-lg text-xs font-black text-amber-900 p-2 focus:ring-0">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-4">
                        <label class="text-[10px] font-black uppercase tracking-widest text-white/30 ml-2">Image de l'Énigme</label>
                        <FileUpload mode="basic" name="image" accept="image/*" @select="onFileSelect" class="w-full" chooseLabel="Révéler par une image" />
                    </div>
                    <div class="space-y-4">
                        <label class="text-[10px] font-black uppercase tracking-widest text-white/30 ml-2">Audio de l'Énigme (Ambiance/Indice vocal)</label>
                        <FileUpload mode="basic" name="audio" accept="audio/*" @select="onAudioSelect" class="w-full" chooseLabel="Murmurer un secret" />
                    </div>
                </div>

                <div class="pt-10">
                    <Button type="submit" :loading="form.processing" class="w-full !py-6 !bg-yellow-400 !border-none !rounded-[2rem] !shadow-2xl !shadow-yellow-900/20 hover:!scale-[1.02] transition-transform">
                         <span class="text-2xl font-black italic uppercase text-white tracking-widest">Sceller l'Énigme</span>
                    </Button>
                </div>
            </form>
        </Dialog>
    </div>
</template>

<style>
.prime-dark-dialog .p-dialog {
    background: #0f1123 !important;
    border: 2px solid rgba(255,255,255,0.05) !important;
    border-radius: 4rem !important;
}
.prime-dark-dialog .p-dialog-header {
    background: transparent !important;
    padding: 3rem 3rem 0 3rem !important;
}
.prime-dark-dialog .p-dialog-content {
    background: transparent !important;
    padding: 0 3rem 3rem 3rem !important;
}
.prime-dark-dialog .p-dialog-title {
    color: white !important;
}

@import url('https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;700;900&display=swap');
h2, h3, h4, span, button { font-family: 'Bangers', cursive; }

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(29, 161, 242, 0.2);
    border-radius: 10px;
}
</style>
