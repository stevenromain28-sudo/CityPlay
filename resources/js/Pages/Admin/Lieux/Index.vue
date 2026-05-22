<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import gsap from 'gsap';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

const props = defineProps({
    lieux: Array,
    ville: Object,
    villes: Array,
    isSuperAdmin: Boolean
});

const visible = ref(false);
const mapContainer = ref(null);
let map = null;
let tempMarker = null;

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
    ville_id: props.ville?.id || null,
});

const searchQuery = ref('');
const ignoreNextError = ref(false); // Pour ignorer le watcher quand on réinitialise le form
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

const openNew = () => {
    ignoreNextError.value = true;
    form.reset();
    form.clearErrors(); // Réinitialiser les erreurs
    searchQuery.value = ''; // Vider la recherche
    form.id = null;
    form.nom = '';
    form.description = '';
    form.localisation = '';
    form.ville_id = props.ville?.id || null;
    form.rayon = 50;
    form.difficulte = 1;
    form.duree_estimee = 30;
    form.image_principale = null;
    
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
    ignoreNextError.value = true;
    form.clearErrors(); // Réinitialiser les erreurs
    form.id = lieu.id;
    form.nom = lieu.nom;
    form.description = lieu.description;
    form.localisation = lieu.localisation || '';
    form.latitude = lieu.latitude;
    form.longitude = lieu.longitude;
    form.rayon = lieu.rayon;
    form.difficulte = lieu.difficulte;
    form.duree_estimee = lieu.duree_estimee;
    form.ville_id = lieu.ville_id;
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
            triggerNotify('error', 'Lieu non trouvé', 'Aucun résultat trouvé pour votre recherche géographique.');
        }
    } catch (e) {
        console.error("Erreur de recherche", e);
    }
};

const deleteLieu = () => {
    triggerConfirm(
        "Supprimer ce lieu ?",
        "Voulez-vous vraiment supprimer ce lieu et toutes les énigmes associées ? Cette action est irréversible.",
        () => {
            form.delete(route('admin.lieux.destroy', form.id), {
                onSuccess: () => {
                    visible.value = false;
                    triggerNotify('success', 'Lieu supprimé', 'Le lieu et ses énigmes ont été effacés avec succès.');
                }
            });
        }
    );
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

watch(visible, () => {
    setTimeout(() => {
        if (map) {
            map.invalidateSize();
        }
    }, 150);
});

// Watcher pour afficher les erreurs dans le modal
watch(() => form.errors, (newErrors) => {
    if (ignoreNextError.value) {
        ignoreNextError.value = false;
        return;
    }
    
    if (newErrors && Object.keys(newErrors).length > 0) {
        // Récupérer la première erreur
        const firstErrorKey = Object.keys(newErrors)[0];
        const firstErrorMessage = Array.isArray(newErrors[firstErrorKey]) 
            ? newErrors[firstErrorKey][0] 
            : newErrors[firstErrorKey];
        
        triggerNotify('error', 'Erreur de validation', firstErrorMessage);
    }
}, { deep: true });
</script>

<template>
    <Head title="Gestion des Lieux - Admin" />

    <div class="min-h-screen bg-[#F0F7FF] font-sans flex relative overflow-x-hidden">
        <!-- Mobile Sidebar Overlay -->
        <div v-if="isMobileMenuOpen" 
             @click="isMobileMenuOpen = false"
             class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-40 lg:hidden transition-opacity duration-300"></div>

        <!-- Sidebar -->
        <aside :class="[
            'bg-[#1DA1F2] flex flex-col items-center py-8 md:py-12 px-2 md:px-4 shrink-0 sticky top-0 h-screen transition-all duration-300 z-50',
            isMobileMenuOpen ? 'fixed left-0 w-24 md:w-32 translate-x-0' : 'fixed -translate-x-full lg:relative lg:translate-x-0 w-24 md:w-32'
        ]">
            <div class="mb-12 md:mb-20">
                <div class="w-12 h-12 md:w-16 md:h-16 bg-white rounded-2xl md:rounded-[2rem] shadow-2xl flex items-center justify-center transform -rotate-12">
                    <span class="text-[#1DA1F2] text-2xl md:text-4xl font-black italic">C</span>
                </div>
            </div>

            <nav class="flex-1 flex flex-col space-y-6 md:space-y-12">
                <Link v-for="(item, i) in [
                    {icon: 'home', label: 'Dashboard', active: route().current('admin.dashboard'), url: route('admin.dashboard')},
                    {icon: 'villes', label: 'Villes', active: route().current('admin.villes.index'), url: route('admin.villes.index')},
                    {icon: 'lieux', label: 'Lieux', active: route().current('admin.lieux.index'), url: route('admin.lieux.index')},
                    {icon: 'puzzle', label: 'Énigmes', active: route().current('admin.enigmes.index'), url: route('admin.enigmes.index')},
                    {icon: 'culture', label: 'Culture', active: route().current('admin.contenus-culturels.index'), url: route('admin.contenus-culturels.index')},
                    {icon: 'users', label: $page.props.auth.user.roles.includes('super_admin') ? 'Utilisateurs' : 'Joueurs', active: route().current('admin.users.index'), url: $page.props.auth.user.roles.includes('super_admin') ? route('admin.users.index') : '#'}
                ]" :key="i" :href="item.url" class="sidebar-item group relative">
                    <div class="p-4 rounded-3xl transition-all duration-300 group-hover:scale-110" :class="item.active ? 'bg-white text-[#1DA1F2] shadow-xl' : 'text-white/80 hover:text-white'">
                        <svg v-if="item.icon==='home'" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        <svg v-if="item.icon==='villes'" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        <svg v-if="item.icon==='lieux'" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <svg v-if="item.icon==='puzzle'" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" /></svg>
                        <svg v-if="item.icon==='culture'" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        <svg v-if="item.icon==='users'" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                </Link>
            </nav>

            <div class="mt-auto">
                <button @click="triggerLogout" class="p-4 text-white/60 hover:text-white hover:scale-110 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
    <main class="flex-1 p-4 md:p-12 overflow-y-auto max-h-screen">
            <!-- Mobile Header -->
            <div class="lg:hidden flex items-center justify-between mb-8">
                <button @click="isMobileMenuOpen = true" class="p-3 bg-white rounded-2xl text-[#1DA1F2] shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M4 6h16M4 12h16m-7 6h7" /></svg>
                </button>
                <div class="w-12 h-12 bg-[#1DA1F2] rounded-xl flex items-center justify-center shadow-lg transform rotate-6">
                    <span class="text-white text-xl font-black italic">C</span>
                </div>
            </div>

            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 md:mb-12 gap-6">
                    <div>
                        <h1 class="text-2xl md:text-5xl font-black italic uppercase text-slate-800 tracking-tighter leading-none mb-2 md:mb-4">
                            Les <span class="text-[#1DA1F2]">Lieux</span>
                        </h1>
                        <p class="text-slate-400 text-[10px] md:text-sm font-bold uppercase tracking-[0.2em] md:tracking-[0.3em] flex items-center">
                            <span class="w-6 md:w-8 h-1 bg-[#1DA1F2] mr-3 md:mr-4"></span>
                            Gestion territoriale de CityPlay
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <Link :href="route('admin.enigmes.index')" class="!px-4 md:!px-8 !py-3 md:!py-4 !bg-purple-600 !border-none !rounded-2xl !shadow-2xl !shadow-purple-400/20 hover:!scale-105 transition-transform !flex !items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 md:h-6 w-5 md:w-6 mr-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" /></svg>
                            <span class="text-white font-black italic uppercase tracking-widest text-sm md:text-lg">Énigmes</span>
                        </Link>
                        <Button @click="openNew" class="!px-4 md:!px-8 !py-3 md:!py-4 !bg-yellow-400 !border-none !rounded-2xl !shadow-2xl !shadow-yellow-400/20 hover:!scale-105 transition-transform !flex !items-center justify-center group">
                            <template #default>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 md:h-6 w-5 md:w-6 mr-3 text-white group-hover:rotate-90 transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 4v16m8-8H4" /></svg>
                                <span class="text-white font-black italic uppercase tracking-widest text-sm md:text-lg">Nouveau Lieu</span>
                            </template>
                        </Button>
                    </div>
                </div>
            </div>
        <!-- Main Split Panel (Map + Form) -->

        <div class="flex-1 flex flex-col lg:flex-row gap-6 min-h-[500px] items-stretch relative">
            
            <!-- Map Container Column -->
            <div class="flex-1 bg-white rounded-3xl md:rounded-[3rem] shadow-2xl shadow-blue-100 overflow-hidden border-4 md:border-8 border-white relative map-wrapper transition-all duration-500">
                <div ref="mapContainer" class="w-full h-full min-h-[400px] lg:min-h-full z-0"></div>
                
                <!-- Floating List (Mini Overlay) - Centered at the bottom on mobile, absolute top-right on desktop -->
                <div class="absolute bottom-4 left-4 right-4 md:bottom-auto md:left-auto md:top-10 md:right-10 z-[1000] w-auto max-w-[calc(100%-2rem)] md:w-80 bg-white/90 backdrop-blur-xl rounded-[2rem] p-4 md:p-6 shadow-2xl border border-white max-h-[35%] md:max-h-[70%] overflow-y-auto custom-scrollbar mx-auto md:mx-0">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-3 md:mb-6">Liste des points</h3>
                    <div class="space-y-3">
                        <div v-for="lieu in lieux" :key="lieu.id" @click="editLieu(lieu)" class="p-3 md:p-4 bg-white rounded-xl md:rounded-2xl border border-blue-50 hover:border-[#1DA1F2] cursor-pointer transition-all group font-sans relative overflow-hidden">
                            <div v-if="lieu.image_principale" class="absolute inset-0 opacity-5 group-hover:opacity-10 transition-opacity">
                                <img :src="lieu.image_principale" class="w-full h-full object-cover">
                            </div>
                            <div class="relative z-10">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs md:text-sm font-black italic uppercase text-slate-800 group-hover:text-[#1DA1F2]">{{ lieu.nom }}</span>
                                    <div class="flex items-center space-x-1.5 shrink-0">
                                        <span v-if="lieu.contenu_culturel" class="w-1.5 h-1.5 bg-yellow-400 rounded-full animate-pulse"></span>
                                        <span class="px-1.5 py-0.5 bg-blue-50 text-[#1DA1F2] text-[8px] font-black rounded uppercase">N.{{ lieu.difficulte }}</span>
                                    </div>
                                </div>
                                <p class="text-[9px] md:text-[10px] text-slate-400 line-clamp-1 font-bold">{{ lieu.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Side Panel (Only visible when creating/editing) -->
            <div v-if="visible" class="w-full lg:w-[600px] bg-white rounded-3xl md:rounded-[3rem] shadow-2xl shadow-blue-100 border-4 md:border-8 border-white p-6 md:p-8 flex flex-col justify-between transition-all duration-300 shrink-0 lg:max-h-full overflow-y-auto custom-scrollbar">
                
                <!-- Panel Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                    <span class="text-2xl md:text-3xl font-black italic uppercase text-[#1DA1F2] tracking-tighter">
                        {{ form.id ? 'Modifier le Lieu' : 'Créer un Lieu' }}
                    </span>
                    <button @click="visible = false" class="text-slate-400 hover:text-slate-600 transition-colors p-2 hover:bg-slate-50 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Form Body -->
                <form @submit.prevent="submit" class="space-y-4 md:space-y-6 font-sans">
                    <!-- Ville Selection (Only SuperAdmin) -->
                    <div v-if="isSuperAdmin" class="space-y-1.5">
                        <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-2">Ville Associée</label>
                        <select v-model="form.ville_id" class="w-full rounded-xl bg-blue-50/50 border border-blue-100 p-3 md:p-4 text-sm md:text-base font-bold focus:ring-2 focus:ring-[#1DA1F2] focus:bg-white outline-none transition-all">
                            <option :value="null">Sélectionner une ville...</option>
                            <option v-for="v in villes" :key="v.id" :value="v.id">{{ v.nom }}</option>
                        </select>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-2">Recherche géographique</label>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <InputText v-model="searchQuery" @keydown.enter.prevent="searchLocation" placeholder="Rechercher une adresse, un monument..." class="flex-1 !rounded-xl !bg-blue-50/50 !border !border-blue-100 !p-3 md:!p-4 !text-sm md:!text-base !font-bold focus:!bg-white" />
                            <Button @click.prevent="searchLocation" class="!px-5 !py-3 !bg-[#1DA1F2] !border-none !rounded-xl !shadow-lg hover:scale-105 transition-transform w-full sm:w-auto justify-center">
                                <span class="text-white font-black uppercase tracking-widest text-xs">Chercher</span>
                            </Button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-2">Nom du lieu</label>
                            <InputText v-model="form.nom" class="w-full !rounded-xl !bg-blue-50/50 !border !border-blue-100 !p-3 md:!p-4 !text-sm md:!text-base !font-bold focus:!bg-white" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-2">Localisation (Adresse/Quartier)</label>
                            <InputText v-model="form.localisation" placeholder="Ex: Vieille Ville, Rue de la Paix..." class="w-full !rounded-xl !bg-blue-50/50 !border !border-blue-100 !p-3 md:!p-4 !text-sm md:!text-base !font-bold focus:!bg-white" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-2">Difficulté (1-3)</label>
                            <InputText v-model="form.difficulte" type="number" min="1" max="3" class="w-full !rounded-xl !bg-blue-50/50 !border !border-blue-100 !p-3 md:!p-4 !text-sm md:!text-base !font-bold focus:!bg-white" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-2">Temps estimé (min)</label>
                            <InputText v-model="form.duree_estimee" type="number" class="w-full !rounded-xl !bg-blue-50/50 !border !border-blue-100 !p-3 md:!p-4 !text-sm md:!text-base !font-bold focus:!bg-white" />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-2">Description</label>
                        <textarea v-model="form.description" rows="3" class="w-full rounded-xl bg-blue-50/50 border border-blue-100 p-3 md:p-4 text-sm md:text-base font-bold focus:ring-2 focus:ring-[#1DA1F2] focus:bg-white transition-all outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-2">Latitude</label>
                            <InputText v-model="form.latitude" class="w-full !rounded-xl !bg-blue-100/20 !border !border-blue-100 !p-3 md:!p-4 !text-sm md:!text-base !font-bold" readonly />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-2">Longitude</label>
                            <InputText v-model="form.longitude" class="w-full !rounded-xl !bg-blue-100/20 !border !border-blue-100 !p-3 md:!p-4 !text-sm md:!text-base !font-bold" readonly />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-2">Rayon (m)</label>
                            <InputText v-model="form.rayon" type="number" class="w-full !rounded-xl !bg-blue-50/50 !border !border-blue-100 !p-3 md:!p-4 !text-sm md:!text-base !font-bold focus:!bg-white" />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-2">Image du Lieu</label>
                        <FileUpload mode="basic" name="image_principale" accept="image/*" @select="onFileSelect" class="w-full" chooseLabel="Sélectionner une photo" />
                    </div>

                    <div class="pt-4 flex flex-col sm:flex-row gap-3">
                        <Button type="submit" :loading="form.processing" class="flex-1 !py-3.5 md:!py-5 !bg-[#1DA1F2] !border-none !rounded-2xl !shadow-lg hover:scale-105 transition-transform justify-center">
                             <span class="text-base md:text-lg font-black italic uppercase text-white tracking-widest">Enregistrer</span>
                        </Button>
                        <Button v-if="form.id" @click.prevent="deleteLieu" class="sm:w-auto !px-6 md:!px-8 !py-3.5 md:!py-5 !bg-red-500 hover:!bg-red-600 !border-none !rounded-2xl !shadow-lg transition-colors justify-center">
                             <span class="text-base md:text-lg font-black italic uppercase text-white tracking-widest">Supprimer</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </main>

        <!-- CUSTOM NOTIFICATION MODAL -->
        <div v-if="notifyModal.show" class="fixed inset-0 z-[2002] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="notifyModal.show = false"></div>
            <div 
                class="relative w-full max-w-md bg-white rounded-[2.5rem] p-1 border-2 shadow-[0_30px_60px_rgba(0,0,0,0.2)] overflow-hidden"
                :class="[
                    notifyModal.type === 'success' ? 'border-green-300 bg-gradient-to-br from-green-400 to-green-600' : '',
                    notifyModal.type === 'error' ? 'border-red-300 bg-gradient-to-br from-red-400 to-red-600' : ''
                ]"
            >
                <div class="bg-white rounded-[2.3rem] p-8 text-center relative overflow-hidden">
                    <div 
                        class="w-16 h-16 md:w-20 md:h-20 mx-auto rounded-2xl flex items-center justify-center mb-6 shadow-lg relative z-10"
                        :class="[
                            notifyModal.type === 'success' ? 'bg-green-50 text-green-500' : '',
                            notifyModal.type === 'error' ? 'bg-red-50 text-red-500' : ''
                        ]"
                    >
                        <svg v-if="notifyModal.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-8 md:h-10 w-8 md:w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-if="notifyModal.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-8 md:h-10 w-8 md:w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>

                    <h3 
                        class="text-2xl md:text-3xl font-black italic uppercase tracking-tighter mb-3 relative z-10"
                        :class="[
                            notifyModal.type === 'success' ? 'text-green-600' : '',
                            notifyModal.type === 'error' ? 'text-red-600' : ''
                        ]"
                    >
                        {{ notifyModal.title }}
                    </h3>
                    
                    <p class="text-slate-600 font-sans font-bold text-xs md:text-sm mb-6 relative z-10 leading-relaxed">{{ notifyModal.message }}</p>

                    <button 
                        @click="notifyModal.show = false" 
                        class="w-full py-3 md:py-4 text-white rounded-xl font-black uppercase tracking-widest shadow-lg hover:scale-105 active:scale-95 transition-all relative z-10 text-xs md:text-sm"
                        :class="[
                            notifyModal.type === 'success' ? 'bg-green-500 hover:bg-green-600 shadow-green-500/20' : '',
                            notifyModal.type === 'error' ? 'bg-red-500 hover:bg-red-600 shadow-red-500/20' : ''
                        ]"
                    >
                        D'accord
                    </button>
                </div>
            </div>
        </div>

        <!-- CUSTOM CONFIRMATION MODAL -->
        <div v-if="confirmModal.show" class="fixed inset-0 z-[2001] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="confirmModal.show = false"></div>
            <div class="relative w-full max-w-md bg-white rounded-[2.5rem] p-1 border-2 border-red-300 bg-gradient-to-br from-red-400 to-red-600 shadow-[0_30px_60px_rgba(0,0,0,0.2)] overflow-hidden">
                <div class="bg-white rounded-[2.3rem] p-6 md:p-8 text-center relative overflow-hidden">
                    <div class="w-16 h-16 md:w-20 md:h-20 mx-auto bg-red-50 text-red-500 rounded-2xl flex items-center justify-center mb-6 shadow-lg relative z-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 md:h-10 w-8 md:w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    </div>

                    <h3 class="text-2xl md:text-3xl font-black italic uppercase tracking-tighter text-red-600 mb-3 relative z-10">
                        {{ confirmModal.title }}
                    </h3>
                    
                    <p class="text-slate-600 font-sans font-bold text-xs md:text-sm mb-6 relative z-10 leading-relaxed">{{ confirmModal.message }}</p>

                    <div class="flex space-x-3 relative z-10">
                        <button @click="confirmModal.show = false" 
                                class="flex-1 py-3 md:py-4 bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-xl font-black uppercase tracking-widest transition-all text-xs md:text-sm">
                            Annuler
                        </button>
                        <button @click="confirmModal.onConfirm" 
                                class="flex-1 py-3 md:py-4 bg-red-500 hover:bg-red-600 text-white rounded-xl font-black uppercase tracking-widest shadow-lg shadow-red-500/20 hover:scale-105 active:scale-95 transition-all text-xs md:text-sm">
                            Confirmer
                        </button>
                    </div>
                </div>
            </div>
        </div>

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

h2, span, button, h3 { font-family: 'Fredoka', sans-serif; }

.font-sans {
    font-family: 'Outfit', sans-serif !important;
}
</style>
