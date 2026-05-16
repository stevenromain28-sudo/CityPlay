<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import gsap from 'gsap';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

const props = defineProps({
    lieux: Array,
    ville: Object
});

const visible = ref(false);
const mapContainer = ref(null);
let map = null;
let tempMarker = null;

const form = useForm({
    id: null,
    nom: '',
    description: '',
    localisation: '',
    latitude: props.ville?.latitude || 45.8992,
    longitude: props.ville?.longitude || 6.1264,
    rayon: 50,
    difficulte: 1,
    duree_estimee: 30,
    image_principale: null,
});

const searchQuery = ref('');

const openNew = () => {
    form.reset();
    form.id = null;
    if (tempMarker) {
        form.latitude = tempMarker.getLatLng().lat;
        form.longitude = tempMarker.getLatLng().lng;
    } else {
        form.latitude = props.ville?.latitude || 45.8992;
        form.longitude = props.ville?.longitude || 6.1264;
    }
    visible.value = true;
};

const editLieu = (lieu) => {
    form.id = lieu.id;
    form.nom = lieu.nom;
    form.description = lieu.description;
    form.localisation = lieu.localisation || '';
    form.latitude = lieu.latitude;
    form.longitude = lieu.longitude;
    form.rayon = lieu.rayon;
    form.difficulte = lieu.difficulte;
    form.duree_estimee = lieu.duree_estimee;
    visible.value = true;
    if (map && lieu.latitude && lieu.longitude) {
        map.setView([lieu.latitude, lieu.longitude], 15);
    }
};

const searchLocation = async () => {
    if (!searchQuery.value) return;
    
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery.value)}`);
        const data = await response.json();
        
        if (data && data.length > 0) {
            const lat = parseFloat(data[0].lat);
            const lng = parseFloat(data[0].lon);
            form.latitude = lat;
            form.longitude = lng;
            
            if (!form.nom) form.nom = data[0].name || '';
            
            if (map) {
                map.setView([lat, lng], 15);
                if (tempMarker) {
                    tempMarker.setLatLng([lat, lng]);
                } else {
                    tempMarker = L.marker([lat, lng], { 
                        icon: L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        })
                    }).addTo(map);
                }
            }
        } else {
            alert("Lieu non trouvé");
        }
    } catch (e) {
        console.error("Erreur de recherche", e);
    }
};

const deleteLieu = () => {
    if (confirm("Voulez-vous vraiment supprimer ce lieu et toutes les énigmes associées ?")) {
        form.delete(route('admin.lieux.destroy', form.id), {
            onSuccess: () => visible.value = false
        });
    }
};

const submit = () => {
    if (form.id) {
        form.post(route('admin.lieux.update', form.id), {
            onSuccess: () => visible.value = false
        });
    } else {
        form.post(route('admin.lieux.store'), {
            onSuccess: () => visible.value = false
        });
    }
};

const onFileSelect = (event) => {
    form.image_principale = event.files[0];
};

onMounted(() => {
    if (!mapContainer.value) return;

    // Initialize Leaflet Map
    map = L.map(mapContainer.value, {
        zoomControl: false
    }).setView([form.latitude, form.longitude], 13);
    
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
    }).addTo(map);

    // Add existing markers
    props.lieux.forEach(lieu => {
        if (lieu.latitude && lieu.longitude) {
            L.marker([lieu.latitude, lieu.longitude], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            })
            .addTo(map)
            .bindPopup(`<b class="font-black uppercase italic">${lieu.nom}</b><br><span class="text-[10px] font-bold">${lieu.description}</span>`)
            .on('click', () => editLieu(lieu));
        }
    });

    // Click on map to set coordinates and open modal directly
    map.on('click', (e) => {
        form.reset();
        form.id = null;
        form.latitude = e.latlng.lat;
        form.longitude = e.latlng.lng;

        if (tempMarker) {
            tempMarker.setLatLng(e.latlng);
        } else {
            tempMarker = L.marker(e.latlng, { 
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }).addTo(map);
        }
        visible.value = true;
    });

    // FIX: Map invalidate size after initial load
    setTimeout(() => {
        map.invalidateSize();
    }, 500);

    gsap.from('.map-wrapper', { opacity: 0, scale: 0.9, duration: 1, ease: 'expo.out' });
});
</script>

<template>
    <Head title="Gestion des Lieux - Admin" />

    <div class="min-h-screen bg-[#F0F7FF] font-sans p-6 md:p-12 overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between mb-10">
            <div>
                <Link :href="route('admin.dashboard')" class="flex items-center text-[#1DA1F2] font-black uppercase text-xs mb-2 hover:translate-x-[-5px] transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M15 19l-7-7 7-7" /></svg>
                    Retour Dashboard
                </Link>
                <h2 class="text-5xl font-black italic uppercase text-slate-800 tracking-tighter">
                    Lieux de <span class="text-[#1DA1F2]">L'Aventure</span>
                </h2>
            </div>
            <Button @click="openNew" class="!px-8 !py-4 !bg-yellow-400 !border-none !rounded-2xl !shadow-xl !shadow-yellow-100 hover:!scale-105 transition-transform !flex !items-center">
                <template #default>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 4v16m8-8H4" /></svg>
                    <span class="text-white font-black italic uppercase tracking-widest">Nouveau Lieu</span>
                </template>
            </Button>
        </div>

        <!-- Map Container -->
        <div class="flex-1 bg-white rounded-[3rem] shadow-2xl shadow-blue-100 overflow-hidden border-8 border-white relative map-wrapper">
            <div ref="mapContainer" class="w-full h-full min-h-[500px] z-0"></div>
            
            <!-- Floating List (Mini Overlay) -->
            <div class="absolute top-10 right-10 z-[1000] w-80 bg-white/80 backdrop-blur-xl rounded-[2rem] p-6 shadow-2xl border border-white max-h-[70%] overflow-y-auto custom-scrollbar">
                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-6">Liste des points</h3>
                <div class="space-y-4">
                    <div v-for="lieu in lieux" :key="lieu.id" @click="editLieu(lieu)" class="p-4 bg-white rounded-2xl border border-blue-50 hover:border-[#1DA1F2] cursor-pointer transition-all group font-sans relative overflow-hidden">
                        <div v-if="lieu.image_principale" class="absolute inset-0 opacity-5 group-hover:opacity-10 transition-opacity">
                            <img :src="lieu.image_principale" class="w-full h-full object-cover">
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-black italic uppercase text-slate-800 group-hover:text-[#1DA1F2]">{{ lieu.nom }}</span>
                                <div class="flex items-center space-x-2">
                                    <span v-if="lieu.contenu_culturel" class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></span>
                                    <span class="px-2 py-1 bg-blue-50 text-[#1DA1F2] text-[8px] font-black rounded-lg uppercase">Niveau {{ lieu.difficulte }}</span>
                                </div>
                            </div>
                            <p class="text-[10px] text-slate-400 line-clamp-1 font-bold">{{ lieu.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Dialog -->
        <Dialog v-model:visible="visible" modal :style="{ width: '50rem' }" class="prime-custom-dialog">
            <template #header>
                <div class="flex items-center">
                    <span class="text-3xl font-black italic uppercase text-[#1DA1F2] tracking-tighter">Config Lieu</span>
                </div>
            </template>
            
            <form @submit.prevent="submit" class="space-y-6 py-4 font-sans px-2">
                
                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Recherche géographique</label>
                    <div class="flex flex-col md:flex-row gap-4 mb-4">
                        <InputText v-model="searchQuery" @keydown.enter.prevent="searchLocation" placeholder="Rechercher une adresse, un monument..." class="flex-1 !rounded-xl !bg-blue-50/50 !border-none !p-4 !font-bold" />
                        <Button @click.prevent="searchLocation" class="!px-6 !bg-[#1DA1F2] !border-none !rounded-xl !shadow-lg hover:scale-105 transition-transform">
                            <span class="text-white font-black uppercase tracking-widest text-xs">Chercher</span>
                        </Button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-3">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Nom du lieu</label>
                        <InputText v-model="form.nom" class="w-full !rounded-xl !bg-blue-50/50 !border-none !p-4 !font-bold" />
                    </div>
                    <div class="space-y-3">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Localisation (Adresse/Quartier)</label>
                        <InputText v-model="form.localisation" placeholder="Ex: Vieille Ville, Rue de la Paix..." class="w-full !rounded-xl !bg-blue-50/50 !border-none !p-4 !font-bold" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-3">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Difficulté (1-3)</label>
                        <InputText v-model="form.difficulte" type="number" min="1" max="3" class="w-full !rounded-xl !bg-blue-50/50 !border-none !p-4 !font-bold" />
                    </div>
                    <div class="space-y-3">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Temps estimé (min)</label>
                        <InputText v-model="form.duree_estimee" type="number" class="w-full !rounded-xl !bg-blue-50/50 !border-none !p-4 !font-bold" />
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Description</label>
                    <textarea v-model="form.description" rows="3" class="w-full rounded-xl bg-blue-50/50 border-none p-4 font-bold focus:ring-2 focus:ring-[#1DA1F2] transition-all"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="space-y-3">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Latitude</label>
                        <InputText v-model="form.latitude" class="w-full !rounded-xl !bg-blue-50/50 !border-none !p-4 !font-bold" readonly />
                    </div>
                    <div class="space-y-3">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Longitude</label>
                        <InputText v-model="form.longitude" class="w-full !rounded-xl !bg-blue-50/50 !border-none !p-4 !font-bold" readonly />
                    </div>
                    <div class="space-y-3">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Rayon (m)</label>
                        <InputText v-model="form.rayon" type="number" class="w-full !rounded-xl !bg-blue-50/50 !border-none !p-4 !font-bold" />
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Image du Lieu</label>
                    <FileUpload mode="basic" name="image_principale" accept="image/*" @select="onFileSelect" class="w-full" chooseLabel="Sélectionner une photo" />
                </div>

                <div class="pt-6 flex flex-col sm:flex-row gap-4">
                    <Button type="submit" :loading="form.processing" class="flex-1 !py-5 !bg-[#1DA1F2] !border-none !rounded-2xl !shadow-lg hover:scale-105 transition-transform">
                         <span class="text-lg font-black italic uppercase text-white tracking-widest">Enregistrer l'emplacement</span>
                    </Button>
                    <Button v-if="form.id" @click.prevent="deleteLieu" class="sm:w-auto !px-8 !py-5 !bg-red-500 hover:!bg-red-600 !border-none !rounded-2xl !shadow-lg transition-colors">
                         <span class="text-lg font-black italic uppercase text-white tracking-widest">Supprimer</span>
                    </Button>
                </div>
            </form>
        </Dialog>
    </div>
</template>

<style>
.custom-animated-marker {
    position: relative;
}
.marker-pin {
    width: 30px;
    height: 30px;
    border-radius: 50% 50% 50% 0;
    background: #1DA1F2;
    position: absolute;
    transform: rotate(-45deg);
    left: 50%;
    top: 50%;
    margin: -15px 0 0 -15px;
    border: 3px solid white;
    box-shadow: 0 5px 15px rgba(29, 161, 242, 0.4);
}
.marker-pin::after {
    content: '';
    width: 12px;
    height: 12px;
    margin: 6px 0 0 6px;
    background: white;
    position: absolute;
    border-radius: 50%;
}
.marker-pulse {
    background: rgba(29, 161, 242, 0.4);
    border-radius: 50%;
    height: 40px;
    width: 40px;
    position: absolute;
    left: 50%;
    top: 50%;
    margin: -20px 0 0 -20px;
    transform: rotateX(55deg);
    z-index: -2;
}
.marker-pulse::after {
    content: "";
    border-radius: 50%;
    height: 40px;
    width: 40px;
    position: absolute;
    margin: -13px 0 0 -13px;
    animation: pulsate 1.5s ease-out;
    animation-iteration-count: infinite;
    opacity: 0;
    box-shadow: 0 0 1px 2px #1DA1F2;
    animation-delay: 1.1s;
}

@keyframes pulsate {
    0% { transform: scale(0.1, 0.1); opacity: 0; }
    50% { opacity: 1; }
    100% { transform: scale(1.2, 1.2); opacity: 0; }
}

.prime-custom-dialog .p-dialog-header {
    background: #f8fafc;
    padding: 2rem;
    border-bottom: 1px solid #e2e8f0;
}
.prime-custom-dialog .p-dialog-content {
    padding: 0 2rem;
}

@import url('https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;700;900&display=swap');
h2, span, button, h3 { font-family: 'Bangers', cursive; }

.font-sans {
    font-family: 'Outfit', sans-serif !important;
}
</style>
