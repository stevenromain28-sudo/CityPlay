<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';
import { ref, onMounted, watch, nextTick } from 'vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';
import Dialog from 'primevue/dialog';
import gsap from 'gsap';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Fix Leaflet marker icons with Vite
import iconUrl from 'leaflet/dist/images/marker-icon.png';
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import shadowUrl from 'leaflet/dist/images/marker-shadow.png';

// Default Leaflet icons configuration removed as it is handled in initMap with CDN links

const props = defineProps({
    ville: Object,
    villes: Array,
    admins: Array,
    isSuperAdmin: Boolean
});

const isMobileMenuOpen = ref(false);

const triggerLogout = () => {
    triggerConfirm(
        'Déconnexion',
        'Êtes-vous sûr de vouloir quitter la console d\'administration ?',
        () => {
            import('@inertiajs/vue3').then(m => {
                m.router.post(route('logout'));
            });
        }
    );
};

const isEditing = ref(false);
const editingVilleId = ref(null);

const confirmModal = ref({
    show: false,
    title: '',
    message: '',
    onConfirm: null
});

const notifyModal = ref({
    show: false,
    type: 'success',
    title: '',
    message: ''
});

const triggerConfirm = (title, message, callback) => {
    confirmModal.value = {
        show: true,
        title,
        message,
        onConfirm: () => {
            confirmModal.value.show = false;
            callback();
        }
    };
};

const triggerNotify = (type, title, message) => {
    notifyModal.value = { show: true, type, title, message };
};

const form = useForm({
    nom: props.ville?.nom || '',
    description: props.ville?.description || '',
    history: props.ville?.history || '',
    pays: props.ville?.pays || '',
    population: props.ville?.population || null,
    latitude: props.ville?.latitude || null,
    longitude: props.ville?.longitude || null,
    rayon_action: props.ville?.rayon_action || 50,
    banniere: null,
    user_id: props.ville?.user_id || null,
});

const editVille = (v) => {
    editingVilleId.value = v.id;
    form.nom = v.nom;
    form.description = v.description;
    form.history = v.history;
    form.pays = v.pays;
    form.population = v.population;
    form.latitude = v.latitude;
    form.longitude = v.longitude;
    form.rayon_action = v.rayon_action || 50;
    form.banniere = null;
    form.user_id = v.user_id || null;
    
    isEditing.value = true;
};

const createVille = () => {
    editingVilleId.value = null;
    form.reset();
    form.nom = '';
    form.description = '';
    form.history = '';
    form.pays = '';
    form.population = null;
    form.latitude = null;
    form.longitude = null;
    form.rayon_action = 50;
    form.banniere = null;
    form.user_id = null;
    
    isEditing.value = true;
};

const deleteVille = (id) => {
    triggerConfirm(
        "Détruire la Cité ?",
        "Voulez-vous vraiment détruire cette cité mythique et toutes ses légendes ? Cette action effacera également tout son contenu.",
        () => {
            form.delete(route('admin.villes.destroy', id), {
                onSuccess: () => {
                    editingVilleId.value = null;
                    isEditing.value = false;
                    triggerNotify('success', 'Cité détruite', 'La cité mythique a été retirée des annales de CityPlay.');
                }
            });
        }
    );
};

const submit = () => {
    if (props.isSuperAdmin) {
        if (editingVilleId.value) {
            form.post(route('admin.villes.update', editingVilleId.value), {
                onSuccess: () => {
                    isEditing.value = false;
                    editingVilleId.value = null;
                }
            });
        } else {
            form.post(route('admin.villes.store'), {
                onSuccess: () => {
                    isEditing.value = false;
                }
            });
        }
    } else {
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
    }
};

const onFileSelect = (event) => {
    form.banniere = event.files[0];
};

const mapContainer = ref(null);
let map = null;
let marker = null;
let circle = null;
const searchQuery = ref('');

const initMap = () => {
    if (!mapContainer.value) return;
    // --- FORCE LEAFLET A UTILISER DES SITES DE CONFIANCE (CDN) PLUTÔT QUE LES DOSSIERS CASSÉS ---
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
        iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
    });
    //
    const lat = form.latitude || 48.8566;
    const lng = form.longitude || 2.3522;
    
    map = L.map(mapContainer.value).setView([lat, lng], 12);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    circle = L.circle([lat, lng], {
        color: '#1DA1F2',
        fillColor: '#1DA1F2',
        fillOpacity: 0.2,
        radius: (form.rayon_action || 50) * 1000
    }).addTo(map);

    const updateCoords = (e) => {
        const { lat, lng } = e.latlng || e.target.getLatLng();
        form.latitude = lat;
        form.longitude = lng;
        circle.setLatLng([lat, lng]);
    };

    marker.on('dragend', updateCoords);
    
    map.on('click', (e) => {
        marker.setLatLng(e.latlng);
        updateCoords(e);
    });
};

const updateMapRadius = () => {
    if (circle) {
        circle.setRadius((form.rayon_action || 50) * 1000);
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
            
            map.setView([lat, lng], 12);
            marker.setLatLng([lat, lng]);
            circle.setLatLng([lat, lng]);
            
            form.latitude = lat;
            form.longitude = lng;
            
            if (!form.nom) form.nom = data[0].name || '';
        } else {
            triggerNotify('error', 'Lieu non trouvé', 'Aucun résultat trouvé pour votre recherche géographique.');
        }
    } catch (e) {
        console.error("Erreur de recherche", e);
    }
};

watch(isEditing, (newVal) => {
    if (newVal) {
        nextTick(() => {
            if (!map) initMap();
        });
    } else {
        if (map) {
            map.remove();
            map = null;
        }
    }
});

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

    <div class="min-h-screen bg-[#F0F7FF] font-sans flex relative overflow-x-hidden">
        <!-- Admin Sidebar -->
        <AdminSidebar 
            v-model:isMobileMenuOpen="isMobileMenuOpen"
            @logout="triggerLogout"
        />

        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-12 overflow-y-auto max-h-screen">
            <!-- Mobile Header -->
            <div class="md:hidden flex items-center justify-between mb-8">
                <button @click="isMobileMenuOpen = true" class="p-2 text-[#1DA1F2] hover:bg-blue-50 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                <div class="w-12 h-12 bg-[#1DA1F2] rounded-xl flex items-center justify-center shadow-lg transform rotate-6">
                    <span class="text-white text-xl font-black italic">C</span>
                </div>
            </div>

            <div class="max-w-6xl mx-auto ville-container">
                <!-- Header -->
                <div v-if="!isEditing" class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 md:mb-12 gap-6">
                    <div v-if="isSuperAdmin">
                        <h2 class="text-2xl md:text-5xl font-black italic uppercase text-slate-800 tracking-tighter">
                            Toutes les <span class="text-[#1DA1F2]">Cités</span>
                        </h2>
                        <p class="text-slate-400 text-[10px] md:text-sm font-bold uppercase tracking-widest mt-2">Gestion globale des territoires</p>
                    </div>
                    <div v-else-if="ville">
                        <h2 class="text-2xl md:text-5xl font-black italic uppercase text-slate-800 tracking-tighter">
                            Ma <span class="text-[#1DA1F2]">Ville</span>
                        </h2>
                        <p class="text-slate-400 text-[10px] md:text-sm font-bold uppercase tracking-widest mt-2">Votre domaine d'administration</p>
                    </div>
                    
                    <Button v-if="isSuperAdmin" @click="createVille" class="!px-4 md:!px-8 !py-3 md:!py-4 !bg-yellow-400 !border-none !rounded-2xl !shadow-2xl !shadow-yellow-400/20 hover:!scale-105 transition-transform !flex !items-center w-full md:w-auto justify-center group">
                        <template #default>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 md:h-6 w-5 md:w-6 mr-3 text-white group-hover:rotate-90 transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 4v16m8-8H4" /></svg>
                            <span class="text-white font-black italic uppercase tracking-widest text-sm md:text-lg">Nouvelle Ville</span>
                        </template>
                    </Button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="v in villes" :key="v.id" class="bg-white rounded-3xl overflow-hidden shadow-2xl shadow-blue-100 border border-blue-50 group transition-all duration-300 hover:-translate-y-2 flex flex-col justify-between min-h-[350px]">
                        <div class="relative h-48">
                            <img :src="v.banniere || '/images/backgrounds/city.png'" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent opacity-85"></div>
                            <div class="absolute bottom-4 left-6 right-6">
                                <span class="px-3 py-1 bg-yellow-400 text-white text-[9px] font-black uppercase rounded-lg tracking-widest mb-1 inline-block">
                                    {{ v.lieux_count }} Lieux
                                </span>
                                <h3 class="text-3xl font-black italic uppercase text-white tracking-tighter leading-none">{{ v.nom }}</h3>
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <p class="text-slate-400 text-xs font-bold line-clamp-3 mb-4">"{{ v.description }}"</p>
                                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#1DA1F2]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    <span>Admin : <span class="text-slate-700 font-extrabold">{{ v.user ? v.user.name : 'Non assigné' }}</span></span>
                                </div>
                            </div>

                            <div class="flex space-x-4 pt-6 border-t border-slate-50 mt-6">
                                <Button @click="editVille(v)" class="flex-1 !py-3 !bg-blue-50 hover:!bg-blue-100 !text-[#1DA1F2] !border-none !rounded-xl !shadow-none transition-colors">
                                    <span class="font-black italic uppercase text-xs">Modifier</span>
                                </Button>
                                <Button @click="deleteVille(v.id)" class="!px-4 !py-3 !bg-red-50 hover:!bg-red-100 !text-red-500 !border-none !rounded-xl !shadow-none transition-colors">
                                    <template #default>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </template>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Case 1: Ville non créée (Pour Admin standard) -->
            <div v-if="!isSuperAdmin && !ville && !isEditing" class="bg-white rounded-3xl md:rounded-[3rem] p-8 md:p-20 text-center shadow-2xl shadow-blue-100 border-4 border-dashed border-blue-100">
                <div class="w-16 h-16 md:w-24 md:h-24 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 md:mb-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 md:h-12 md:w-12 text-[#1DA1F2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
                <h2 class="text-3xl md:text-4xl font-black italic uppercase text-slate-800 tracking-tighter mb-4">Votre aventure commence ici</h2>
                <p class="text-slate-400 text-xs md:text-base font-bold uppercase tracking-widest mb-8 md:mb-10">Vous n'avez pas encore configuré votre ville de jeu.</p>
                <Button @click="isEditing = true" class="!px-8 !py-4 md:!px-10 md:!py-5 !bg-[#1DA1F2] !border-none !rounded-2xl !shadow-xl">
                    <span class="text-white font-black italic uppercase tracking-widest text-base md:text-lg">Créer ma Ville</span>
                </Button>
            </div>

            <!-- Case 2: Affichage de la Ville (Vue Prestige - Pour Admin Standard) -->
            <div v-if="!isSuperAdmin && ville && !isEditing" class="bg-white rounded-3xl md:rounded-[4rem] shadow-2xl shadow-blue-100 overflow-hidden border-2 border-white">
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
                        <Link :href="route('admin.lieux.index')" class="p-2.5 md:p-4 bg-yellow-400 text-white rounded-xl md:rounded-2xl shadow-2xl shadow-yellow-400/40 hover:scale-110 transition-transform flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6 md:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <span class="font-black italic uppercase tracking-widest hidden md:inline">Gérer la carte</span>
                        </Link>
                        <Link :href="route('admin.enigmes.index')" class="p-2.5 md:p-4 bg-purple-500 text-white rounded-xl md:rounded-2xl shadow-2xl shadow-purple-500/40 hover:scale-110 transition-transform flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6 md:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                            <span class="font-black italic uppercase tracking-widest hidden md:inline">Gérer les énigmes</span>
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
                    <Button icon="pi pi-times" @click="isEditing = false; editingVilleId = null" class="!bg-white/10 !text-white !border-none !rounded-full" />
                </div>

                <form @submit.prevent="submit" class="p-4 md:p-12 lg:p-16 space-y-6 md:space-y-10 font-sans">
                    <!-- Admin Assignment (Only SuperAdmin) -->
                    <div v-if="isSuperAdmin" class="space-y-2">
                        <label class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Assigner à un Administrateur</label>
                        <select v-model="form.user_id" class="w-full rounded-xl border border-blue-100 bg-blue-50/30 p-3 md:p-4 text-sm md:text-base font-bold text-slate-800 focus:ring-2 focus:ring-[#1DA1F2] focus:bg-white outline-none transition-all">
                            <option :value="null">Laisser libre (Non assigné)</option>
                            <option v-for="admin in admins" :key="admin.id" :value="admin.id">{{ admin.name }} ({{ admin.email }})</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-8">
                        <div class="space-y-2">
                            <label class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Nom de la ville</label>
                            <InputText v-model="form.nom" placeholder="Ex: Paris, Annecy..." class="w-full !rounded-xl !border !border-blue-100 !bg-blue-50/30 !p-3 md:!p-4 !text-sm md:!text-base !font-bold !text-slate-800 focus:!bg-white" />
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Pays</label>
                            <InputText v-model="form.pays" placeholder="France" class="w-full !rounded-xl !border !border-blue-100 !bg-blue-50/30 !p-3 md:!p-4 !text-sm md:!text-base !font-bold !text-slate-800 focus:!bg-white" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-8">
                        <div class="space-y-2">
                            <label class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Population</label>
                            <InputText v-model="form.population" type="number" class="w-full !rounded-xl !border !border-blue-100 !bg-blue-50/30 !p-3 md:!p-4 !text-sm md:!text-base !font-bold !text-slate-800 focus:!bg-white" />
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Rayon d'action (km)</label>
                            <InputText v-model="form.rayon_action" @input="updateMapRadius" type="number" class="w-full !rounded-xl !border !border-blue-100 !bg-blue-50/30 !p-3 md:!p-4 !text-sm md:!text-base !font-bold !text-slate-800 focus:!bg-white" />
                        </div>
                    </div>

                    <!-- Map Section -->
                    <div class="space-y-4">
                        <label class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Localisation de la ville</label>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <InputText v-model="searchQuery" @keydown.enter.prevent="searchLocation" placeholder="Rechercher une ville ou une adresse..." class="flex-1 !rounded-xl !border !border-blue-100 !bg-blue-50/30 !p-3 md:!p-4 !text-sm md:!text-base !font-bold !text-slate-800 focus:!bg-white" />
                            <Button @click.prevent="searchLocation" class="!px-6 !py-3 !bg-[#1DA1F2] !border-none !rounded-xl !shadow-lg justify-center w-full sm:w-auto">
                                <span class="text-white font-black uppercase tracking-widest text-xs">Chercher</span>
                            </Button>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-xs font-bold text-slate-500 px-2 mb-2">
                            <div>Lat : <span class="text-[#1DA1F2]">{{ form.latitude || 'N/A' }}</span></div>
                            <div>Lng : <span class="text-[#1DA1F2]">{{ form.longitude || 'N/A' }}</span></div>
                        </div>
                        <div class="w-full h-[250px] md:h-[400px] rounded-3xl overflow-hidden border-4 border-blue-50 shadow-inner z-10" ref="mapContainer"></div>
                        <p class="text-[10px] md:text-xs font-bold text-slate-400 text-center mt-2">Vous pouvez déplacer le marqueur pour ajuster le centre de votre ville.</p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Description courte</label>
                        <Textarea v-model="form.description" rows="2" class="w-full !rounded-xl !border !border-blue-100 !bg-blue-50/30 !p-3 md:!p-4 !text-sm md:!text-base !font-bold !text-slate-800 focus:!bg-white focus:!ring-2 focus:!ring-[#1DA1F2] transition-all outline-none" />
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Histoire & Légendes</label>
                        <Textarea v-model="form.history" rows="6" class="w-full !rounded-2xl !border !border-blue-100 !bg-blue-50/30 !p-4 md:!p-6 !text-sm md:!text-base !font-bold !text-slate-800 focus:!bg-white focus:!ring-2 focus:!ring-[#1DA1F2] transition-all outline-none" />
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Bannière Prestige</label>
                        <FileUpload mode="basic" name="banniere" accept="image/*" @select="onFileSelect" class="w-full" chooseLabel="Choisir un visuel" />
                    </div>

                    <div class="pt-4 flex flex-col sm:flex-row gap-3">
                        <Button type="submit" :loading="form.processing" class="flex-1 !py-3 md:!py-4 !bg-[#1DA1F2] !border-none !rounded-xl !shadow-xl hover:!scale-[1.02] transition-transform justify-center">
                             <span class="text-sm md:text-lg font-black italic uppercase text-white tracking-widest">{{ editingVilleId ? 'Enregistrer les Changements' : 'Fonder la Cité' }}</span>
                        </Button>
                        <Button v-if="editingVilleId" @click.prevent="deleteVille(editingVilleId)" class="sm:w-auto !px-6 !py-3 md:!py-4 !bg-red-500 hover:!bg-red-600 !border-none !rounded-xl !shadow-xl transition-colors justify-center">
                             <span class="text-sm md:text-lg font-black italic uppercase text-white tracking-widest">Détruire</span>
                        </Button>
                    </div>
                </form>
            </div>
        </main>

        <!-- CUSTOM NOTIFICATION MODAL -->
        <div v-if="notifyModal.show" class="fixed inset-0 z-[2002] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="notifyModal.show = false"></div>
            <div class="relative w-full max-w-md bg-white rounded-[2.5rem] p-1 border-2 shadow-[0_30px_60px_rgba(0,0,0,0.2)] overflow-hidden"
                 :class="notifyModal.type === 'success' ? 'border-green-300 bg-gradient-to-br from-green-400 to-green-600' : 'border-red-300 bg-gradient-to-br from-red-400 to-red-600'">
                <div class="bg-white rounded-[2.3rem] p-6 md:p-8 text-center relative overflow-hidden">
                    <div class="w-16 h-16 md:w-20 md:h-20 mx-auto bg-slate-50 rounded-2xl flex items-center justify-center mb-6 shadow-lg relative z-10"
                         :class="notifyModal.type === 'success' ? 'text-green-500' : 'text-red-500'">
                        <svg v-if="notifyModal.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>

                    <h3 class="text-2xl md:text-3xl font-black italic uppercase tracking-tighter mb-3 relative z-10"
                        :class="notifyModal.type === 'success' ? 'text-green-600' : 'text-red-600'">
                        {{ notifyModal.title }}
                    </h3>
                    
                    <p class="text-slate-600 font-sans font-bold text-xs md:text-sm mb-6 relative z-10 leading-relaxed">{{ notifyModal.message }}</p>

                    <button @click="notifyModal.show = false" 
                            class="w-full py-3 md:py-4 text-white rounded-xl font-black uppercase tracking-widest shadow-lg hover:scale-105 active:scale-95 transition-all relative z-10 text-xs md:text-sm"
                            :class="notifyModal.type === 'success' ? 'bg-green-500 hover:bg-green-600 shadow-green-500/20' : 'bg-red-500 hover:bg-red-600 shadow-red-500/20'">
                        D'accord
                    </button>
                </div>
            </div>
        </div>

        <!-- CUSTOM CONFIRMATION MODAL -->
        <div v-if="confirmModal.show" class="fixed inset-0 z-[2001] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="confirmModal.show = false"></div>
            <div class="relative w-full max-w-md bg-white rounded-[2.5rem] p-1 border-2 border-yellow-300 bg-gradient-to-br from-yellow-400 to-yellow-600 shadow-[0_30px_60px_rgba(0,0,0,0.2)] overflow-hidden">
                <div class="bg-white rounded-[2.3rem] p-8 text-center relative overflow-hidden">
                    <div class="w-20 h-20 mx-auto bg-yellow-50 text-yellow-500 rounded-2xl flex items-center justify-center mb-6 shadow-lg relative z-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>

                    <h3 class="text-3xl font-black italic uppercase tracking-tighter text-yellow-600 mb-3 relative z-10">
                        {{ confirmModal.title }}
                    </h3>
                    
                    <p class="text-slate-600 font-sans font-bold text-sm mb-6 relative z-10 leading-relaxed">{{ confirmModal.message }}</p>

                    <div class="flex space-x-3 relative z-10">
                        <button @click="confirmModal.show = false" 
                                class="flex-1 py-4 bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-xl font-black uppercase tracking-widest transition-all">
                            Annuler
                        </button>
                        <button @click="confirmModal.onConfirm" 
                                class="flex-1 py-4 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl font-black uppercase tracking-widest shadow-lg shadow-yellow-500/20 hover:scale-105 active:scale-95 transition-all">
                            Confirmer
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
h1, h2, h3, h4, span, button { font-family: 'Fredoka', sans-serif; }
.font-sans { font-family: 'Outfit', sans-serif !important; }
</style>
