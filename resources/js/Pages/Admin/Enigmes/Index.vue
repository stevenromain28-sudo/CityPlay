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
                        <InputText v-model="form.titre" class="w-full !rounded-xl !bg-blue-50 !border !border-blue-100 !p-3 md:!p-4 !text-sm md:!text-base !font-bold !text-slate-800 focus:!bg-white" />
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

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 md:gap-8">
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
                    <div class="space-y-2 flex flex-col justify-end pb-1">
                        <div class="flex items-center space-x-3 bg-purple-50/50 p-3 rounded-xl border border-purple-100">
                            <input type="checkbox" v-model="form.is_bonus" id="is_bonus" class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500">
                            <label for="is_bonus" class="text-xs font-black uppercase text-purple-700 cursor-pointer select-none">Énigme Bonus</label>
                        </div>
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
                        <FileUpload mode="basic" name="image" accept="image/*" @select="onFileSelect" class="w-full" chooseLabel="Choisir une image" />
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

        <!-- CUSTOM NOTIFICATION MODAL -->
        <div v-if="notifyModal.show" class="fixed inset-0 z-[999] flex items-center justify-center p-4">
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
        <div v-if="confirmModal.show" class="fixed inset-0 z-[999] flex items-center justify-center p-4">
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
