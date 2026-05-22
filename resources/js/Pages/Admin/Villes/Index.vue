<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
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

    <div ref="dashboardContainer" class="min-h-screen bg-[#F0F7FF] text-slate-800 flex overflow-hidden font-sans relative">
        <!-- Animated Background Slides -->
        <div class="absolute inset-0 z-0 opacity-5 pointer-events-none overflow-hidden">
            <div class="bg-slide flex w-[200%] h-full">
                <img src="/images/backgrounds/city.png" class="w-1/2 h-full object-cover">
                <img src="/images/backgrounds/img2.jpg" class="w-1/2 h-full object-cover">
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div v-if="isMobileMenuOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[90] md:hidden transition-opacity" @click="isMobileMenuOpen = false"></div>

        <!-- Sidebar -->
        <aside :class="{'translate-x-0': isMobileMenuOpen, '-translate-x-full': !isMobileMenuOpen}"
               class="fixed md:relative inset-y-0 left-0 flex w-24 lg:w-32 bg-[#1DA1F2] flex-col items-center pt-10 pb-6 md:py-10 shadow-[5px_0_30px_rgba(29,161,242,0.1)] z-[100] transition-transform duration-300 ease-in-out md:translate-x-0 overflow-x-hidden md:overflow-x-visible overflow-y-auto md:overflow-y-visible no-scrollbar">
            <div class="mb-10 md:mb-16 shrink-0">
                <div class="w-16 h-16 bg-yellow-400 rounded-3xl flex items-center justify-center shadow-xl rotate-3 hover:rotate-0 transition-transform duration-300">
                    <!-- Eiffel Tower / City Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.5,2H11.5L11,5H13L12.5,2M13.5,6H10.5L10,12H14L13.5,6M15,13H9L8,22H10L10.5,18H13.5L14,22H16L15,13Z" />
                    </svg>
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
                    <span class="absolute left-full ml-6 px-3 py-2 bg-slate-800 text-white text-xs font-bold rounded-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50 shadow-xl">
                        {{ item.label }}
                    </span>
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
                        <Link :href="route('admin.lieux.index')" class="p-3 md:p-4 bg-yellow-400 text-white rounded-xl md:rounded-2xl shadow-2xl shadow-yellow-400/40 hover:scale-110 transition-transform flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6 md:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <span class="font-black italic uppercase tracking-widest hidden md:inline">Gérer la carte</span>
                        </Link>
                        <Link :href="route('admin.enigmes.index')" class="p-3 md:p-4 bg-purple-500 text-white rounded-xl md:rounded-2xl shadow-2xl shadow-purple-500/40 hover:scale-110 transition-transform flex items-center">
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

                    <div class="pt-4">
                        <Button type="submit" :loading="form.processing" class="w-full !py-4 md:!py-5 !bg-[#1DA1F2] !border-none !rounded-2xl !shadow-xl justify-center">
                            <span class="text-lg md:text-xl font-black italic uppercase tracking-tighter text-white">Enregistrer</span>
                        </Button>
                    </div>
                </form>
            </div>
        </main>

        <!-- CUSTOM NOTIFICATION MODAL -->
        <div v-if="notifyModal.show" class="fixed inset-0 z-[2002] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="notifyModal.show = false"></div>
            <div class="relative w-full max-w-md bg-white rounded-[2.5rem] p-1 border-2 border-green-300 bg-gradient-to-br from-green-400 to-green-600 shadow-[0_30px_60px_rgba(0,0,0,0.2)] overflow-hidden">
                <div class="bg-white rounded-[2.3rem] p-8 text-center relative overflow-hidden">
                    <div class="w-20 h-20 mx-auto bg-green-50 text-green-500 rounded-2xl flex items-center justify-center mb-6 shadow-lg relative z-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    </div>

                    <h3 class="text-3xl font-black italic uppercase tracking-tighter text-green-600 mb-3 relative z-10">
                        {{ notifyModal.title }}
                    </h3>
                    
                    <p class="text-slate-600 font-sans font-bold text-sm mb-6 relative z-10 leading-relaxed">{{ notifyModal.message }}</p>

                    <button @click="notifyModal.show = false" 
                            class="w-full py-4 bg-green-500 hover:bg-green-600 text-white rounded-xl font-black uppercase tracking-widest shadow-lg shadow-green-500/20 hover:scale-105 active:scale-95 transition-all relative z-10">
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
