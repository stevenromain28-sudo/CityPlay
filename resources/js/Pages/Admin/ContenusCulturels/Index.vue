<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import gsap from 'gsap';

const props = defineProps({
    contenus: Array,
    lieux: Array,
    villes: Array,
    isSuperAdmin: Boolean
});

const visible = ref(false);
const selectedVilleFilter = ref(null);
const selectedFormVille = ref(null);
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

const filteredContenus = computed(() => {
    if (!props.isSuperAdmin || !selectedVilleFilter.value) {
        return props.contenus;
    }
    return props.contenus.filter(c => c.lieu && c.lieu.ville_id === selectedVilleFilter.value);
});

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
    description: '',
    audio: null,
    images: [],
    existing_images: []
});

const openNew = () => {
    form.reset();
    form.id = null;
    form.images = [];
    form.existing_images = [];
    selectedFormVille.value = null;
    visible.value = true;
};

const editContenu = (contenu) => {
    form.id = contenu.id;
    form.lieu_id = contenu.lieu_id;
    form.titre = contenu.titre;
    form.description = contenu.description;
    form.audio = null;
    form.images = [];
    form.existing_images = contenu.images ? [...contenu.images] : [];
    if (props.isSuperAdmin && contenu.lieu) {
        selectedFormVille.value = contenu.lieu.ville_id;
    }
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
    triggerConfirm(
        "Effacer ce savoir ?",
        "Voulez-vous vraiment effacer ce savoir des archives culturelles de la cité ? Cette action est irréversible.",
        () => {
            form.delete(route('admin.contenus-culturels.destroy', form.id), {
                onSuccess: () => {
                    visible.value = false;
                    triggerNotify('success', 'Savoir effacé', 'Le savoir culturel a été effacé avec succès.');
                }
            });
        }
    );
};

const onAudioSelect = (event) => {
    form.audio = event.files[0];
};

const onImagesSelect = (event) => {
    const selectedFiles = Array.from(event.target.files);
    form.images = [...form.images, ...selectedFiles];
    event.target.value = '';
};

const removeExistingImage = (index) => {
    form.existing_images.splice(index, 1);
};

const removeNewImage = (index) => {
    form.images.splice(index, 1);
};

const getObjectUrl = (file) => {
    return URL.createObjectURL(file);
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
                            Archives <span class="text-[#1DA1F2]">Culturelles</span>
                        </h1>
                        <p class="text-slate-400 text-[10px] md:text-sm font-bold uppercase tracking-[0.2em] md:tracking-[0.3em] flex items-center">
                            <span class="w-6 md:w-8 h-1 bg-[#1DA1F2] mr-3 md:mr-4"></span>
                            Chroniques et Savoirs de CityPlay
                        </p>
                    </div>

                    <Button @click="openNew" class="!px-6 md:!px-8 !py-3 md:!py-4 !bg-yellow-400 !border-none !rounded-2xl !shadow-2xl !shadow-yellow-400/20 hover:!scale-105 transition-transform !flex !items-center w-full md:w-auto justify-center group">
                        <template #default>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 md:h-6 w-5 md:w-6 mr-3 text-white group-hover:rotate-90 transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 4v16m8-8H4" /></svg>
                            <span class="text-white font-black italic uppercase tracking-widest text-sm md:text-lg">Nouveau Contenu</span>
                        </template>
                    </Button>
                </div>

            <!-- SuperAdmin Global City Filter Bar -->
            <div v-if="isSuperAdmin" class="mb-12 bg-white rounded-[2.5rem] p-8 shadow-xl shadow-blue-100/50 border-2 border-white flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-blue-50 text-[#1DA1F2] rounded-[1.5rem] flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-black italic uppercase text-slate-800 tracking-tight">Filtre territorial</h3>
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Sélectionner une cité pour concentrer les archives</p>
                    </div>
                </div>
                <div class="w-full sm:w-80">
                    <select v-model="selectedVilleFilter" class="w-full rounded-2xl bg-slate-50 border-slate-100 p-4 font-bold text-slate-800 focus:ring-2 focus:ring-[#1DA1F2] appearance-none cursor-pointer">
                        <option :value="null">Toutes les Cités du Royaume</option>
                        <option v-for="ville in villes" :key="ville.id" :value="ville.id">{{ ville.nom }}</option>
                    </select>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <div v-for="contenu in filteredContenus" :key="contenu.id" 
                     @click="editContenu(contenu)"
                     class="culture-card group bg-white rounded-[3rem] p-10 shadow-xl shadow-blue-100 border-2 border-transparent hover:border-[#1DA1F2] transition-all cursor-pointer relative overflow-hidden">
                    
                    <div class="absolute top-0 right-0 w-32 h-32 bg-[#1DA1F2]/5 rounded-bl-[5rem] -mr-10 -mt-10 group-hover:bg-[#1DA1F2]/10 transition-colors"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-8">
                            <span class="px-4 py-2 bg-blue-50 text-[#1DA1F2] text-[10px] font-black uppercase rounded-xl tracking-widest">{{ contenu.lieu.nom }} {{ contenu.lieu.ville ? `• ${contenu.lieu.ville.nom}` : '' }}</span>
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

                        <!-- Images Preview Grid in Card -->
                        <div v-if="contenu.images && contenu.images.length > 0" class="grid grid-cols-3 gap-2 mt-6">
                            <div v-for="(img, idx) in contenu.images.slice(0, 3)" :key="idx" class="h-20 rounded-2xl overflow-hidden relative border border-slate-100 shadow-sm">
                                <img :src="img" class="w-full h-full object-cover" />
                                <div v-if="idx === 2 && contenu.images.length > 3" class="absolute inset-0 bg-slate-900/60 flex items-center justify-center text-white text-xs font-black italic">
                                    +{{ contenu.images.length - 3 }}
                                </div>
                            </div>
                        </div>

                        <div v-if="contenu.audio" class="mt-8 pt-8 border-t border-slate-50">
                            <audio :src="contenu.audio" controls class="w-full h-8 opacity-50 hover:opacity-100 transition-opacity"></audio>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredContenus.length === 0" class="col-span-full py-32 flex flex-col items-center justify-center text-center">
                    <div class="w-32 h-32 bg-white rounded-[3rem] shadow-2xl flex items-center justify-center mb-8 rotate-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    </div>
                    <h3 class="text-3xl font-black italic uppercase text-slate-800 tracking-tighter mb-4">Le Grimoire est vide</h3>
                    <p class="text-slate-400 font-bold uppercase tracking-widest max-w-md">Ajoutez des anecdotes culturelles pour rendre vos lieux inoubliables.</p>
                </div>
            </div>
        </div>
    </main>

        <!-- Form Dialog -->
        <Dialog v-model:visible="visible" modal :style="{ width: '92vw', maxWidth: '50rem' }" class="prime-light-dialog">
            <template #header>
                <div class="flex items-center">
                    <span class="text-2xl md:text-4xl font-black italic uppercase text-[#1DA1F2] tracking-tighter">
                        {{ form.id ? 'Modifier le Savoir' : 'Graver un Nouveau Savoir' }}
                    </span>
                </div>
            </template>
            
            <form @submit.prevent="submit" class="space-y-6 md:space-y-10 py-4 md:py-8 px-2 md:px-4 font-sans">
                <div class="grid grid-cols-1 sm:grid-cols-2" :class="[isSuperAdmin ? 'md:grid-cols-3' : 'md:grid-cols-2', 'gap-4 md:gap-8']">
                    <div v-if="isSuperAdmin" class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-widest text-[#1DA1F2] ml-2">Filtrer par Ville</label>
                        <select v-model="selectedFormVille" class="w-full rounded-xl bg-slate-50 border border-slate-150 p-3 md:p-4 text-sm md:text-base font-bold text-slate-800 focus:ring-2 focus:ring-[#1DA1F2] focus:bg-white outline-none appearance-none !block transition-all">
                            <option :value="null">Toutes les cités...</option>
                            <option v-for="ville in villes" :key="ville.id" :value="ville.id">{{ ville.nom }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-2">Lieu associé</label>
                        <select v-model="form.lieu_id" :disabled="form.id" class="w-full rounded-xl bg-slate-50 border border-slate-150 p-3 md:p-4 text-sm md:text-base font-bold text-slate-800 focus:ring-2 focus:ring-[#1DA1F2] focus:bg-white outline-none appearance-none disabled:opacity-50 !block transition-all">
                            <option value="" disabled>Choisir un lieu...</option>
                            <option v-for="lieu in filteredLieuxForForm" :key="lieu.id" :value="lieu.id">{{ lieu.nom }} {{ isSuperAdmin && lieu.ville ? `(${lieu.ville.nom})` : '' }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-2">Titre du contenu</label>
                        <InputText v-model="form.titre" class="w-full !rounded-xl !bg-slate-50 !border !border-slate-150 !p-3 md:!p-4 !text-sm md:!text-base !font-bold !text-slate-800 focus:!bg-white" placeholder="Ex: L'histoire du Vieux Pont" />
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-2">Récit Culturel</label>
                    <textarea v-model="form.description" rows="6" class="w-full rounded-2xl bg-slate-50 border border-slate-150 p-4 md:p-6 text-sm md:text-base font-bold text-slate-800 focus:ring-2 focus:ring-[#1DA1F2] focus:bg-white placeholder:text-slate-300 outline-none transition-all" placeholder="Racontez une anecdote passionnante sur ce lieu..."></textarea>
                </div>

                <div class="space-y-2">
                    <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-2">Fichier Audio (Optionnel)</label>
                    <FileUpload mode="basic" name="audio" accept="audio/*" @select="onAudioSelect" class="w-full" chooseLabel="Ajouter une voix au récit" />
                </div>

                <!-- Galerie de photos multiples -->
                <div class="space-y-4">
                    <label class="text-[9px] font-black uppercase tracking-widest text-[#1DA1F2] ml-2">Photos illustratives (Plusieurs possibles)</label>
                    
                    <!-- Galerie existante et nouvelle -->
                    <div v-if="form.existing_images.length > 0 || form.images.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-3xl border border-slate-100">
                        
                        <!-- Images existantes -->
                        <div v-for="(img, idx) in form.existing_images" :key="'exist-' + idx" class="relative group aspect-video rounded-2xl overflow-hidden border-2 border-white shadow-md">
                            <img :src="img" class="w-full h-full object-cover" />
                            <button type="button" @click="removeExistingImage(idx)" class="absolute top-2 right-2 p-1.5 bg-red-500 hover:bg-red-600 text-white rounded-full transition-transform hover:scale-110 flex items-center justify-center shadow">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <span class="absolute bottom-2 left-2 px-2 py-0.5 bg-blue-500/80 text-white text-[8px] font-bold rounded uppercase tracking-wider">Actuelle</span>
                        </div>

                        <!-- Nouvelles images -->
                        <div v-for="(file, idx) in form.images" :key="'new-' + idx" class="relative group aspect-video rounded-2xl overflow-hidden border-2 border-dashed border-blue-300 shadow-md">
                            <img :src="getObjectUrl(file)" class="w-full h-full object-cover" />
                            <button type="button" @click="removeNewImage(idx)" class="absolute top-2 right-2 p-1.5 bg-red-500 hover:bg-red-600 text-white rounded-full transition-transform hover:scale-110 flex items-center justify-center shadow">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <span class="absolute bottom-2 left-2 px-2 py-0.5 bg-green-500/80 text-white text-[8px] font-bold rounded uppercase tracking-wider">Nouvelle</span>
                        </div>

                    </div>

                    <!-- Input de sélection multiple -->
                    <div class="relative">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-300 hover:border-[#1DA1F2] rounded-[2rem] cursor-pointer bg-slate-50 hover:bg-slate-100/50 transition-colors p-4 text-center">
                            <div class="flex flex-col items-center justify-center pt-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#1DA1F2] mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-xs font-black uppercase text-slate-600 tracking-wider">Sélectionner des photos</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">Glisser ou cliquer pour ajouter plusieurs images (PNG, JPG, JPEG)</p>
                            </div>
                            <input type="file" multiple accept="image/*" @change="onImagesSelect" class="hidden" />
                        </label>
                    </div>
                </div>

                <div class="pt-4 flex flex-col sm:flex-row gap-3">
                    <Button v-if="form.id" type="button" @click="deleteContenu" class="!py-4 !bg-red-50 !text-red-500 !border-none !rounded-xl hover:!bg-red-100 transition-all flex-1 justify-center">
                        <span class="text-lg font-black italic uppercase tracking-widest">Effacer</span>
                    </Button>
                    <Button type="submit" :loading="form.processing" class="!py-4 !bg-[#1DA1F2] !border-none !rounded-xl !shadow-xl hover:!scale-[1.02] transition-transform flex-[2] justify-center">
                         <span class="text-lg font-black italic uppercase text-white tracking-widest">Enregistrer</span>
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
.prime-light-dialog .p-dialog {
    background: white !important;
    border-radius: 2rem !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1) !important;
}
@media (min-width: 768px) {
    .prime-light-dialog .p-dialog {
        border-radius: 4rem !important;
    }
}
.prime-light-dialog .p-dialog-header {
    background: transparent !important;
    padding: 1.5rem 1.5rem 0 1.5rem !important;
}
@media (min-width: 768px) {
    .prime-light-dialog .p-dialog-header {
        padding: 3rem 3rem 0 3rem !important;
    }
}
.prime-light-dialog .p-dialog-content {
    background: transparent !important;
    padding: 0 1.5rem 1.5rem 1.5rem !important;
}
@media (min-width: 768px) {
    .prime-light-dialog .p-dialog-content {
        padding: 0 3rem 3rem 3rem !important;
    }
}

h2, h3, h4, span, button { font-family: 'Fredoka', sans-serif; }
</style>
