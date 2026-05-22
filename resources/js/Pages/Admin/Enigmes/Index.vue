<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import gsap from 'gsap';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    enigmes: Array,
    lieux: Array,
    villes: Array,
    isSuperAdmin: Boolean
});

const selectedLieuId = ref(null);
const selectedVilleFilter = ref(null);
const selectedFormVille = ref(null);
const isMobileMenuOpen = ref(false);

// Filtre les énigmes globales par ville (pour le SuperAdmin)
const allFilteredEnigmes = computed(() => {
    if (!props.isSuperAdmin || !selectedVilleFilter.value) {
        return props.enigmes;
    }
    return props.enigmes.filter(e => e.lieu && e.lieu.ville_id === selectedVilleFilter.value);
});

// Filtre les lieux affichés dans les onglets par ville (pour le SuperAdmin)
const filteredLieuxTabs = computed(() => {
    if (!props.isSuperAdmin || !selectedVilleFilter.value) {
        return props.lieux;
    }
    return props.lieux.filter(l => l.ville_id === selectedVilleFilter.value);
});

// Énigmes du lieu sélectionné (parmi celles filtrées par ville)
const enigmesOfSelectedLieu = computed(() => {
    if (!selectedLieuId.value) return [];
    return allFilteredEnigmes.value.filter(e => e.lieu_id === selectedLieuId.value);
});

const mainEnigmes = computed(() => enigmesOfSelectedLieu.value.filter(e => !e.is_bonus).sort((a, b) => a.ordre - b.ordre));
const bonusEnigmesList = computed(() => enigmesOfSelectedLieu.value.filter(e => e.is_bonus).sort((a, b) => a.ordre - b.ordre));

const usedLevels = computed(() => {
    if (!selectedLieuId.value) return [];
    return props.enigmes
        .filter(e => e.lieu_id === selectedLieuId.value && !e.is_bonus && e.id !== form.id)
        .map(e => e.niveau);
});

const visible = ref(false);

const filteredLieuxForForm = computed(() => {
    if (!props.isSuperAdmin || !selectedFormVille.value) {
        return props.lieux;
    }
    return props.lieux.filter(l => l.ville_id === selectedFormVille.value);
});

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
    is_bonus: false,
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
    selectedFormVille.value = null;
    form.indices = [{ contenu: '', penalite: 5 }];
    form.lieu_id = selectedLieuId.value; // Pré-remplir le lieu si déjà sélectionné
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
    form.is_bonus = !!enigme.is_bonus;
    form.indices = enigme.indices.map(i => ({ contenu: i.contenu, penalite: i.penalite }));
    if (props.isSuperAdmin && enigme.lieu) {
        selectedFormVille.value = enigme.lieu.ville_id;
    }
    visible.value = true;
    setTimeout(initMap, 100);
};

const submit = () => {
    // Nettoyer les indices vides pour éviter les erreurs de validation
    form.indices = form.indices.filter(i => i.contenu && i.contenu.trim() !== '');

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

const deleteEnigme = () => {
    triggerConfirm(
        "Détruire l'énigme ?",
        "Voulez-vous vraiment détruire cette énigme des annales de CityPlay ?",
        () => {
            form.delete(route('admin.enigmes.destroy', form.id), {
                onSuccess: () => {
                    visible.value = false;
                    triggerNotify('success', 'Énigme supprimée', 'L\'énigme a été supprimée avec succès des annales.');
                }
            });
        }
    );
};

// Autocenter and auto-fill coordinates when a lieu is selected
watch(() => form.lieu_id, (newVal) => {
    if (newVal && !form.id) { // Seulement lors de la création pour ne pas écraser une position personnalisée d'énigme existante
        const selectedLieu = props.lieux.find(l => l.id === newVal);
        if (selectedLieu && selectedLieu.latitude && selectedLieu.longitude) {
            form.latitude = selectedLieu.latitude;
            form.longitude = selectedLieu.longitude;

            if (map) {
                map.setView([form.latitude, form.longitude], 16);
                if (marker) {
                    marker.setLatLng([form.latitude, form.longitude]);
                }
                if (radiusCircle) {
                    radiusCircle.setLatLng([form.latitude, form.longitude]);
                }
            }
        }
    }
});

watch(() => [form.lieu_id, form.is_bonus], ([newLieu, newBonus]) => {
    if (!newBonus && newLieu) {
        const selectedLieu = props.lieux.find(l => l.id === newLieu);
        if (selectedLieu) {
            form.titre = selectedLieu.nom;
        }
    }
});

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

    <div class="min-h-screen bg-slate-900 font-sans flex relative overflow-x-hidden">
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
                <button @click="isMobileMenuOpen = true" class="p-3 bg-white/5 rounded-2xl text-white">
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
                        <h1 class="text-3xl md:text-5xl font-black italic uppercase text-white tracking-tighter leading-none mb-2 md:mb-4">
                            Le Grimoire des <span class="text-[#1DA1F2]">Énigmes</span>
                        </h1>
                        <p class="text-slate-400 text-[10px] md:text-sm font-bold uppercase tracking-[0.2em] md:tracking-[0.3em] flex items-center">
                            <span class="w-6 md:w-8 h-1 bg-[#1DA1F2] mr-3 md:mr-4"></span>
                            Gestion des secrets de CityPlay
                        </p>
                    </div>

                    <Button @click="openNew" class="!px-6 md:!px-8 !py-3 md:!py-4 !bg-yellow-400 !border-none !rounded-2xl !shadow-2xl !shadow-yellow-400/20 hover:!scale-105 transition-transform !flex !items-center w-full md:w-auto justify-center group">
                        <template #default>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 md:h-6 w-5 md:w-6 mr-3 text-white group-hover:rotate-90 transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 4v16m8-8H4" /></svg>
                            <span class="text-white font-black italic uppercase tracking-widest text-base md:text-lg">Nouvelle Énigme</span>
                        </template>
                    </Button>
                </div>

            <!-- SuperAdmin Global City Filter Bar -->
            <div v-if="isSuperAdmin" class="mb-10 bg-white/5 backdrop-blur-md rounded-[2rem] p-6 border border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-yellow-400/20 text-yellow-400 rounded-2xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black italic uppercase text-white tracking-tight">Filtre territorial</h3>
                        <p class="text-white/40 text-xs font-bold uppercase tracking-wider">Sélectionner une cité pour concentrer le grimoire</p>
                    </div>
                </div>
                <div class="w-full sm:w-72">
                    <select v-model="selectedVilleFilter" class="w-full rounded-xl bg-white/10 border-white/10 p-3 font-bold text-white focus:ring-2 focus:ring-[#1DA1F2] appearance-none cursor-pointer">
                        <option :value="null" class="bg-[#0a0c1b]">Toutes les Cités du Royaume</option>
                        <option v-for="ville in villes" :key="ville.id" :value="ville.id" class="bg-[#0a0c1b]">{{ ville.nom }}</option>
                    </select>
                </div>
            </div>

            <!-- Lieu Selector -->
            <div class="flex flex-wrap gap-4 mb-12">
                <button v-for="lieu in filteredLieuxTabs" :key="lieu.id"
                        @click="selectedLieuId = lieu.id"
                        :class="[
                            'px-6 py-3 rounded-2xl font-black uppercase tracking-widest transition-all border-2',
                            selectedLieuId === lieu.id
                                ? 'bg-[#1DA1F2] border-[#1DA1F2] text-white shadow-lg shadow-blue-500/50'
                                : 'bg-white/5 border-white/10 text-white/60 hover:border-white/30'
                        ]">
                    {{ lieu.nom }} {{ isSuperAdmin && lieu.ville ? `(${lieu.ville.nom})` : '' }}
                </button>
            </div>

            <div v-if="selectedLieuId">
                <!-- Main Enigmes Section -->
                <div class="mb-16">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="h-[2px] flex-1 bg-gradient-to-r from-transparent to-white/10"></div>
                        <h3 class="text-3xl font-black italic uppercase text-white tracking-tighter">Énigmes Principales <span class="text-[#1DA1F2] text-xl ml-2">({{ mainEnigmes.length }}/3)</span></h3>
                        <div class="h-[2px] flex-1 bg-gradient-to-l from-transparent to-white/10"></div>
                    </div>

                    <div v-if="mainEnigmes.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                        <div v-for="enigme in mainEnigmes" :key="enigme.id" @click="editEnigme(enigme)" class="enigme-card group bg-white/5 backdrop-blur-xl rounded-[3rem] p-8 border-2 border-white/10 hover:border-[#1DA1F2]/50 transition-all cursor-pointer shadow-2xl relative">
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
                                    <span class="px-3 py-1 bg-white/10 text-white/40 text-[10px] font-black uppercase rounded-lg tracking-widest">{{ enigme.lieu.nom }} {{ enigme.lieu.ville ? `• ${enigme.lieu.ville.nom}` : '' }}</span>
                                    <div class="flex gap-2">
                                        <span v-if="enigme.is_bonus" class="px-3 py-1 bg-purple-500/20 text-purple-400 text-[10px] font-black uppercase rounded-lg">Bonus</span>
                                        <span class="px-3 py-1 bg-yellow-400/20 text-yellow-400 text-[10px] font-black uppercase rounded-lg">Niveau {{ enigme.niveau }}</span>
                                    </div>
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
                    <div v-else class="text-center py-12 bg-white/5 rounded-[3rem] border-2 border-dashed border-white/10">
                        <p class="text-white/30 font-black uppercase tracking-widest">Aucune énigme principale définie.</p>
                    </div>
                </div>

                <!-- Bonus Enigmes Section -->
                <div>
                    <div class="flex items-center gap-4 mb-8">
                        <div class="h-[2px] flex-1 bg-gradient-to-r from-transparent to-purple-500/20"></div>
                        <h3 class="text-3xl font-black italic uppercase text-white tracking-tighter">Énigmes Bonus <span class="text-purple-400 text-xl ml-2">({{ bonusEnigmesList.length }})</span></h3>
                        <div class="h-[2px] flex-1 bg-gradient-to-l from-transparent to-purple-500/20"></div>
                    </div>

                    <div v-if="bonusEnigmesList.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                        <div v-for="enigme in bonusEnigmesList" :key="enigme.id" @click="editEnigme(enigme)" class="enigme-card group bg-purple-900/10 backdrop-blur-xl rounded-[3rem] p-8 border-2 border-purple-500/10 hover:border-purple-500/50 transition-all cursor-pointer shadow-2xl relative">
                            <div class="aspect-video rounded-2xl overflow-hidden mb-6 border-2 border-white/5 bg-slate-900">
                                <img v-if="enigme.image" :src="enigme.image" class="w-full h-full object-cover opacity-80 group-hover:scale-110 transition-transform duration-700">
                                <div v-else class="w-full h-full flex items-center justify-center text-white/10">
                                     <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="px-3 py-1 bg-white/10 text-white/40 text-[10px] font-black uppercase rounded-lg tracking-widest">{{ enigme.lieu.nom }} {{ enigme.lieu.ville ? `• ${enigme.lieu.ville.nom}` : '' }}</span>
                                    <div class="flex gap-2">
                                        <span class="px-3 py-1 bg-purple-500/20 text-purple-400 text-[10px] font-black uppercase rounded-lg">Bonus</span>
                                        <span class="px-3 py-1 bg-yellow-400/20 text-yellow-400 text-[10px] font-black uppercase rounded-lg">Niveau {{ enigme.niveau }}</span>
                                    </div>
                                </div>
                                <h3 class="text-2xl font-black italic uppercase text-white tracking-tighter group-hover:text-purple-400 transition-colors">{{ enigme.titre }}</h3>
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
                    <div v-else class="text-center py-12 bg-purple-900/5 rounded-[3rem] border-2 border-dashed border-purple-500/10">
                        <p class="text-purple-500/30 font-black uppercase tracking-widest">Aucune énigme bonus.</p>
                    </div>
                </div>
            </div>
            <div v-else class="text-center py-32">
                <div class="w-24 h-24 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-8 border border-white/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white/20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </div>
                <h3 class="text-2xl font-black italic uppercase text-white/40 tracking-widest">Sélectionnez un Lieu pour voir ses secrets</h3>
            </div>
        </div>
    </main>

    <!-- Form Dialog -->
        <Dialog v-model:visible="visible" modal :style="{ width: '92vw', maxWidth: '60rem' }" class="prime-dark-dialog">
            <template #header>
                <div class="flex items-center">
                    <span class="text-2xl md:text-4xl font-black italic uppercase text-[#1DA1F2] tracking-tighter">Écrire le Destin</span>
                </div>
            </template>

            <form @submit.prevent="submit" class="space-y-6 md:space-y-10 py-4 md:py-8 px-2 md:px-4 font-sans">
                <!-- Message d'erreur global -->
                <div v-if="Object.keys(form.errors).length > 0" class="bg-red-500/20 border-2 border-red-500 text-red-200 p-4 md:p-6 rounded-2xl mb-6 font-bold shadow-lg">
                    <p class="text-lg md:text-xl font-black italic uppercase text-red-400 mb-2">Erreur de validation</p>
                    <ul class="list-disc pl-5 text-xs md:text-sm">
                        <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                    </ul>
                </div>

                <!-- Type d'Énigme Selector -->
                <div class="flex justify-center p-1 bg-slate-900/50 rounded-2xl border border-white/10 max-w-md mx-auto">
                    <button type="button" @click="form.is_bonus = false" class="flex-1 py-3 px-6 rounded-xl font-black uppercase text-[10px] tracking-widest transition-all duration-300" :class="form.is_bonus ? 'text-white/40 hover:text-white' : 'bg-[#1DA1F2] text-white shadow-lg shadow-blue-500/20'">
                        Énigme Principale
                    </button>
                    <button type="button" @click="form.is_bonus = true" class="flex-1 py-3 px-6 rounded-xl font-black uppercase text-[10px] tracking-widest transition-all duration-300" :class="form.is_bonus ? 'bg-purple-600 text-white shadow-lg shadow-purple-500/20' : 'text-white/40 hover:text-white'">
                        Énigme Bonus
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-8">
                    <div v-if="isSuperAdmin" class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-widest text-[#1DA1F2] ml-2">Filtrer par Ville</label>
                        <select v-model="selectedFormVille" class="w-full rounded-xl bg-blue-50 border border-blue-100 p-3 md:p-4 text-sm md:text-base font-bold text-slate-800 focus:ring-2 focus:ring-[#1DA1F2] focus:bg-white outline-none transition-all appearance-none !block">
                            <option :value="null">Toutes les cités...</option>
                            <option v-for="ville in villes" :key="ville.id" :value="ville.id">{{ ville.nom }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-widest text-slate-500 ml-2">Lieu associé</label>
                        <select v-model="form.lieu_id" class="w-full rounded-xl bg-blue-50 border border-blue-100 p-3 md:p-4 text-sm md:text-base font-bold text-slate-800 focus:ring-2 focus:ring-[#1DA1F2] focus:bg-white outline-none transition-all appearance-none !block">
                            <option value="" disabled>Choisir un lieu...</option>
                            <option v-for="lieu in filteredLieuxForForm" :key="lieu.id" :value="lieu.id">{{ lieu.nom }} {{ isSuperAdmin && lieu.ville ? `(${lieu.ville.nom})` : '' }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-widest text-slate-500 ml-2">Titre de l'énigme</label>
                        <InputText v-model="form.titre" :disabled="!form.is_bonus" class="w-full !rounded-xl !bg-blue-50 !border !border-blue-100 !p-3 md:!p-4 !text-sm md:!text-base !font-bold !text-slate-800 focus:!bg-white" :class="{'!bg-slate-200/50 !text-slate-500 cursor-not-allowed': !form.is_bonus}" />
                        <p v-if="!form.is_bonus" class="text-[8px] md:text-[10px] text-blue-500 font-bold uppercase tracking-wider ml-2">Identique au nom du lieu associé</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[9px] font-black uppercase tracking-widest text-slate-500 ml-2">Le Mystère (Contenu)</label>
                    <textarea v-model="form.contenu" rows="4" class="w-full rounded-2xl bg-blue-50 border border-blue-100 p-4 md:p-6 text-sm md:text-base font-bold text-slate-800 focus:ring-2 focus:ring-[#1DA1F2] focus:bg-white outline-none transition-all placeholder:text-slate-400" placeholder="Décrivez l'énigme de manière mystérieuse..."></textarea>
                </div>

                <!-- GPS Map Selection -->
                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1">
                        <label class="text-[9px] font-black uppercase tracking-widest text-[#1DA1F2] ml-2">Localisation de l'Énigme (GPS)</label>
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" v-model="form.verification_gps" id="gps_check" class="rounded text-[#1DA1F2]">
                            <label for="gps_check" class="text-[9px] font-black uppercase text-slate-400">Activer validation GPS</label>
                        </div>
                    </div>
                    <div class="h-48 md:h-64 rounded-3xl overflow-hidden border-2 border-blue-50">
                        <div ref="mapContainer" class="w-full h-full z-0"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 md:gap-6">
                        <div class="space-y-1">
                            <label class="text-[8px] font-black uppercase text-slate-400 ml-2">Latitude</label>
                            <InputText v-model="form.latitude" readonly class="w-full !rounded-lg !bg-blue-50/50 !border-none !p-2 md:!p-3 !text-[10px] md:!text-xs !font-bold" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[8px] font-black uppercase text-slate-400 ml-2">Longitude</label>
                            <InputText v-model="form.longitude" readonly class="w-full !rounded-lg !bg-blue-50/50 !border-none !p-2 md:!p-3 !text-[10px] md:!text-xs !font-bold" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[8px] font-black uppercase text-slate-400 ml-2">Rayon (m)</label>
                            <InputText v-model="form.rayon" type="number" class="w-full !rounded-lg !bg-blue-50/50 !border-none !p-2 md:!p-3 !text-[10px] md:!text-xs !font-bold" />
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-8">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-widest text-slate-500 ml-2">Mots-clés de réponse</label>
                        <InputText v-model="form.reponse" placeholder="ex: secret,porte" class="w-full !rounded-xl !bg-blue-50 !border !border-blue-100 !p-3 md:!p-4 !text-sm md:!text-base !font-bold !text-slate-800 focus:!bg-white" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-widest text-slate-500 ml-2">Niveau (1-3)</label>
                        <select v-model="form.niveau" 
                                class="w-full rounded-xl bg-blue-50 border border-blue-100 p-3 md:p-4 text-sm md:text-base font-bold text-slate-800 focus:ring-2 focus:ring-[#1DA1F2] focus:bg-white outline-none transition-all appearance-none !block"
                                :class="{'opacity-50 cursor-not-allowed': form.is_bonus}">
                            <option v-for="lvl in 3" :key="lvl" :value="lvl" :disabled="!form.is_bonus && usedLevels.includes(lvl)">
                                Niveau {{ lvl }} {{ !form.is_bonus && usedLevels.includes(lvl) ? '(Déjà utilisé)' : '' }}
                            </option>
                        </select>
                        <p v-if="!form.is_bonus && usedLevels.length >= 3" class="text-[8px] md:text-[10px] text-red-500 font-bold uppercase">Tous les niveaux principaux sont occupés</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-widest text-slate-500 ml-2">Ordre</label>
                        <InputText v-model="form.ordre" type="number" class="w-full !rounded-xl !bg-blue-50 !border !border-blue-100 !p-3 md:!p-4 !text-sm md:!text-base !font-bold !text-slate-800 focus:!bg-white" />
                    </div>
                </div>

                <!-- Indices Section (Parchments) -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h4 class="text-lg md:text-2xl font-black italic uppercase text-yellow-400 tracking-tighter">Les Indices du Parchemin ({{ form.indices.length }}/5)</h4>
                        <Button v-if="form.indices.length < 5" type="button" @click="addIndice" class="!bg-yellow-400/10 !text-yellow-400 !border-none !rounded-xl !px-3 !py-2 hover:!bg-yellow-400/20 transition-all !flex !items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            <span class="text-[10px] font-black uppercase tracking-widest">Ajouter</span>
                        </Button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-8">
                        <div v-for="(indice, index) in form.indices" :key="index" class="relative p-5 md:p-8 bg-[#F5DEB3] rounded-sm shadow-2xl border-x-4 border-amber-900/10 rotate-[-1deg] hover:rotate-0 transition-transform">
                            <div class="absolute -top-2 -left-2 w-6 h-6 md:w-8 md:h-8 bg-amber-900 text-white rounded-full flex items-center justify-center text-[10px] font-black">#{{ index + 1 }}</div>
                            <button type="button" @click.prevent="removeIndice(index)" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 md:w-8 md:h-8 p-0 flex items-center justify-center shadow-lg transition-colors cursor-pointer border-2 border-[#F5DEB3]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 md:h-4 md:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>

                            <textarea v-model="indice.contenu" rows="2" class="w-full bg-transparent border-none p-0 font-bold text-amber-900 placeholder:text-amber-900/40 focus:ring-0 italic text-sm md:text-base outline-none resize-none" placeholder="Écrivez l'indice ici..."></textarea>
                            <div class="mt-4 flex items-center justify-between border-t border-amber-900/20 pt-3">
                                <span class="text-[8px] font-black uppercase text-amber-900/60 tracking-widest">Pénalité Points</span>
                                <input type="number" v-model="indice.penalite" class="w-12 md:w-16 bg-white/40 border-none rounded-lg text-xs font-black text-amber-900 p-1.5 focus:ring-0">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-8">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/30 ml-2">Image de l'Énigme</label>
                        <div v-if="!form.is_bonus" class="bg-white/5 p-4 rounded-xl border border-white/10 flex items-center gap-4">
                            <img v-if="props.lieux.find(l => l.id === form.lieu_id)?.image_principale" :src="props.lieux.find(l => l.id === form.lieu_id)?.image_principale" class="w-12 h-12 rounded-lg object-cover border border-white/10">
                            <span class="text-xs font-bold text-white/40 uppercase tracking-wide">Image du lieu associée automatiquement</span>
                        </div>
                        <FileUpload v-else mode="basic" name="image" accept="image/*" @select="onFileSelect" class="w-full" chooseLabel="Choisir une image" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/30 ml-2">Audio de l'Énigme (Ambiance/Indice vocal)</label>
                        <FileUpload mode="basic" name="audio" accept="audio/*" @select="onAudioSelect" class="w-full" chooseLabel="Choisir un audio" />
                    </div>
                </div>

                <div class="pt-4 flex flex-col sm:flex-row gap-3">
                    <Button type="submit" :loading="form.processing" class="flex-1 !py-4 !bg-[#1DA1F2] !border-none !rounded-xl !shadow-xl hover:!scale-[1.02] transition-transform justify-center">
                         <span class="text-lg font-black italic uppercase text-white tracking-widest">Sceller l'Énigme</span>
                    </Button>
                    <Button v-if="form.id" @click.prevent="deleteEnigme" class="sm:w-auto !px-6 !py-4 !bg-red-500 hover:!bg-red-600 !border-none !rounded-xl !shadow-xl transition-colors justify-center">
                         <span class="text-lg font-black italic uppercase text-white tracking-widest">Détruire</span>
                    </Button>
                </div>
            </form>
        </Dialog>

        <!-- CONFIRMATION MODAL -->
        <div v-if="confirmModal.show" class="fixed inset-0 z-[1001] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="confirmModal.show = false"></div>
            <div class="relative w-full max-w-md bg-white rounded-[2.5rem] p-1 border-2 border-red-300 bg-gradient-to-br from-red-400 to-red-600 shadow-2xl overflow-hidden">
                <div class="bg-white rounded-[2.3rem] p-6 md:p-8 text-center relative overflow-hidden">
                    <div class="w-16 h-16 md:w-20 md:h-20 mx-auto bg-red-50 text-red-500 rounded-2xl flex items-center justify-center mb-6 shadow-lg relative z-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 md:h-10 w-8 md:w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
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

        <!-- NOTIFICATION MODAL -->
        <div v-if="notifyModal.show" class="fixed inset-0 z-[1002] flex items-center justify-center p-4 pointer-events-none">
            <div class="bg-white rounded-2xl px-6 md:px-8 py-3 md:py-4 shadow-2xl border-2 pointer-events-auto flex items-center gap-4 transition-all" :class="notifyModal.type === 'success' ? 'border-green-400 text-green-600' : 'border-red-400 text-red-600'">
                <span class="font-black uppercase italic tracking-widest text-xs md:text-sm">{{ notifyModal.message }}</span>
                <button @click="notifyModal.show = false" class="ml-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

    </div>
</template>

<style>
.prime-dark-dialog .p-dialog {
    background: #0f1123 !important;
    border: 2px solid rgba(255,255,255,0.05) !important;
    border-radius: 2rem !important;
}
@media (min-width: 768px) {
    .prime-dark-dialog .p-dialog {
        border-radius: 4rem !important;
    }
}
.prime-dark-dialog .p-dialog-header {
    background: transparent !important;
    padding: 1.5rem 1.5rem 0 1.5rem !important;
}
@media (min-width: 768px) {
    .prime-dark-dialog .p-dialog-header {
        padding: 3rem 3rem 0 3rem !important;
    }
}
.prime-dark-dialog .p-dialog-content {
    background: transparent !important;
    padding: 0 1.5rem 1.5rem 1.5rem !important;
}
@media (min-width: 768px) {
    .prime-dark-dialog .p-dialog-content {
        padding: 0 3rem 3rem 3rem !important;
    }
}
.prime-dark-dialog .p-dialog-title {
    color: white !important;
}

h2, h3, h4, span, button { font-family: 'Fredoka', sans-serif; }

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
