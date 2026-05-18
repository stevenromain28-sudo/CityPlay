<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import gsap from 'gsap';

const props = defineProps({
    users: Array,
    roles: Array
});

const isMobileMenuOpen = ref(false);
const visible = ref(false);
const isEditing = ref(false);
const editingUserId = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'admin',
});

const openNew = () => {
    isEditing.value = false;
    editingUserId.value = null;
    form.reset();
    form.name = '';
    form.email = '';
    form.password = '';
    form.role = 'admin';
    visible.value = true;
};

const editUser = (user) => {
    isEditing.value = true;
    editingUserId.value = user.id;
    form.reset();
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.role = user.role;
    visible.value = true;
};

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

const deleteUser = (user) => {
    triggerConfirm(
        "Supprimer l'utilisateur ?",
        `Voulez-vous vraiment supprimer le compte de ${user.name} ? Cette action est irréversible.`,
        () => {
            form.delete(route('admin.users.destroy', user.id), {
                onSuccess: () => {
                    triggerNotify('success', 'Utilisateur supprimé', 'Le compte a été supprimé avec succès.');
                }
            });
        }
    );
};

const submit = () => {
    if (isEditing.value) {
        form.post(route('admin.users.update', editingUserId.value), {
            onSuccess: () => {
                visible.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('admin.users.store'), {
            onSuccess: () => {
                visible.value = false;
                form.reset();
            }
        });
    }
};

onMounted(() => {
    gsap.from('.user-card', {
        y: 20,
        opacity: 0,
        stagger: 0.1,
        duration: 0.6,
        ease: 'power3.out'
    });
});
</script>

<template>
    <Head title="Gestion des Utilisateurs - CityPlay" />

    <div class="min-h-screen bg-[#F0F7FF] text-slate-800 flex overflow-hidden font-sans relative">
        
        <!-- Mobile Menu Overlay -->
        <div v-if="isMobileMenuOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[90] md:hidden transition-opacity" @click="isMobileMenuOpen = false"></div>

        <!-- Sidebar -->
        <aside :class="{'translate-x-0': isMobileMenuOpen, '-translate-x-full': !isMobileMenuOpen}"
               class="fixed md:relative inset-y-0 left-0 flex w-24 lg:w-32 bg-[#1DA1F2] flex-col items-center pt-10 pb-6 md:py-10 shadow-[5px_0_30px_rgba(29,161,242,0.1)] z-[100] transition-transform duration-300 ease-in-out md:translate-x-0 overflow-y-auto no-scrollbar">
            <div class="mb-10 md:mb-16 shrink-0">
                <div class="w-16 h-16 bg-yellow-400 rounded-3xl flex items-center justify-center shadow-xl rotate-3 hover:rotate-0 transition-transform duration-300">
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
                <Link :href="route('logout')" method="post" as="button" class="p-4 text-white/60 hover:text-white hover:scale-110 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-screen overflow-hidden z-10">
            <!-- Header -->
            <header class="h-20 md:h-28 flex items-center justify-between px-6 md:px-12 shrink-0 bg-white/80 backdrop-blur-md border-b border-blue-100 relative z-[40]">
                <div class="flex items-center">
                    <button @click="isMobileMenuOpen = true" class="md:hidden mr-4 p-2 text-[#1DA1F2] hover:bg-blue-50 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <div>
                        <h1 class="text-2xl md:text-4xl font-black italic tracking-tighter text-[#1DA1F2] uppercase">
                            City<span class="text-yellow-400">Play</span> <span class="text-slate-800 hidden sm:inline">Admin</span>
                        </h1>
                        <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.3em] mt-1 hidden sm:block">Exploration Management Console</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4 md:space-x-10">
                    <div class="flex items-center space-x-3 md:space-x-5 md:pl-10 md:border-l-2 md:border-blue-50">
                        <div class="text-right hidden sm:block">
                            <p class="text-slate-800 font-black text-sm md:text-lg uppercase italic leading-tight">{{ $page.props.auth.user.name }}</p>
                            <p class="text-[#1DA1F2] text-[10px] font-black uppercase tracking-widest">Super Admin</p>
                        </div>
                        <div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl md:rounded-[2rem] bg-yellow-400 p-0.5 md:p-1 shadow-lg shadow-yellow-200 rotate-3">
                            <img src="https://ui-avatars.com/api/?name=Admin&background=1DA1F2&color=fff" class="w-full h-full rounded-xl md:rounded-[1.8rem] object-cover" alt="Avatar">
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto px-4 md:px-12 py-6 md:py-12 space-y-8 md:space-y-12 custom-scrollbar">
                
                <!-- Page Title -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <h2 class="text-3xl md:text-5xl font-black italic uppercase tracking-tighter text-slate-800">
                            Gestion des <span class="text-[#1DA1F2]">Comptes</span>
                        </h2>
                        <p class="text-slate-400 text-xs md:text-sm font-bold uppercase tracking-widest mt-2">
                            Administrer les privilèges et utilisateurs de la plateforme
                        </p>
                    </div>
                    <Button @click="openNew" class="!px-6 !py-4 !bg-yellow-400 !border-none !rounded-2xl !shadow-xl hover:scale-105 transition-transform flex items-center justify-center">
                        <template #default>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 4v16m8-8H4" /></svg>
                            <span class="text-white font-black italic uppercase tracking-widest text-xs md:text-sm">Créer un Admin</span>
                        </template>
                    </Button>
                </div>

                <!-- Global stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-3xl p-6 shadow-xl border border-blue-50/50 flex items-center space-x-6">
                        <div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center text-[#1DA1F2] shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                        <div>
                            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Total Utilisateurs</p>
                            <h3 class="text-3xl font-black italic uppercase text-slate-800">{{ users.length }}</h3>
                        </div>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-xl border border-blue-50/50 flex items-center space-x-6">
                        <div class="w-16 h-16 rounded-2xl bg-yellow-50 flex items-center justify-center text-yellow-500 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        </div>
                        <div>
                            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Administrateurs</p>
                            <h3 class="text-3xl font-black italic uppercase text-slate-800">
                                {{ users.filter(u => u.role === 'admin' || u.role === 'super_admin').length }}
                            </h3>
                        </div>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-xl border border-blue-50/50 flex items-center space-x-6">
                        <div class="w-16 h-16 rounded-2xl bg-green-50 flex items-center justify-center text-green-500 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                        <div>
                            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Joueurs Explorateurs</p>
                            <h3 class="text-3xl font-black italic uppercase text-slate-800">
                                {{ users.filter(u => u.role === 'player').length }}
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Grid User Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="user in users" :key="user.id" class="user-card bg-white rounded-3xl p-6 shadow-xl border border-blue-50/50 flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300 relative overflow-hidden group">
                        
                        <!-- Role Badge -->
                        <div class="absolute top-4 right-4">
                            <span v-if="user.role === 'super_admin'" class="px-3 py-1 bg-red-100 text-red-500 text-[9px] font-black uppercase tracking-widest rounded-lg">
                                SuperAdmin
                            </span>
                            <span v-else-if="user.role === 'admin'" class="px-3 py-1 bg-yellow-100 text-yellow-600 text-[9px] font-black uppercase tracking-widest rounded-lg">
                                Admin
                            </span>
                            <span v-else class="px-3 py-1 bg-blue-100 text-[#1DA1F2] text-[9px] font-black uppercase tracking-widest rounded-lg">
                                Player
                            </span>
                        </div>

                        <!-- User Info -->
                        <div class="flex items-center space-x-4 mb-6">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-[#1DA1F2] to-blue-700 p-0.5 shadow-lg shrink-0">
                                <img :src="`https://ui-avatars.com/api/?name=${user.name}&background=1DA1F2&color=fff`" class="w-full h-full rounded-[14px] object-cover">
                            </div>
                            <div class="overflow-hidden">
                                <h4 class="text-lg font-black italic uppercase text-slate-800 truncate leading-none mb-1">{{ user.name }}</h4>
                                <p class="text-slate-400 text-xs font-bold truncate">{{ user.email }}</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="pt-4 border-t border-slate-50 flex items-center justify-between mt-auto">
                            <span class="text-[9px] font-black text-slate-300 uppercase tracking-wider">Créé le {{ user.created_at }}</span>
                            <div class="flex space-x-2">
                                <Button @click="editUser(user)" class="!p-2 !bg-blue-50 hover:!bg-blue-100 !text-[#1DA1F2] !border-none !rounded-xl transition-colors">
                                    <template #default>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </template>
                                </Button>
                                <Button v-if="user.id !== $page.props.auth.user.id" @click="deleteUser(user)" class="!p-2 !bg-red-50 hover:!bg-red-100 !text-red-500 !border-none !rounded-xl transition-colors">
                                    <template #default>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </template>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Form Dialog -->
        <Dialog v-model:visible="visible" modal :style="{ width: '40rem' }" class="prime-custom-dialog">
            <template #header>
                <div class="flex items-center">
                    <span class="text-3xl font-black italic uppercase text-[#1DA1F2] tracking-tighter">
                        {{ isEditing ? 'Modifier le compte' : 'Créer un Compte' }}
                    </span>
                </div>
            </template>
            
            <form @submit.prevent="submit" class="space-y-6 py-4 font-sans px-2">
                
                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Nom Complet</label>
                    <InputText v-model="form.name" placeholder="Ex: Jean Dupont" class="w-full !rounded-xl !border-blue-100 !bg-blue-50/30 !p-4 !font-bold !text-slate-800 focus:!ring-2 focus:!ring-[#1DA1F2]" />
                    <span v-if="form.errors.name" class="text-red-500 text-xs font-bold px-2 block mt-1">{{ form.errors.name }}</span>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Adresse Email</label>
                    <InputText v-model="form.email" type="email" placeholder="email@cityplay.fr" class="w-full !rounded-xl !border-blue-100 !bg-blue-50/30 !p-4 !font-bold !text-slate-800 focus:!ring-2 focus:!ring-[#1DA1F2]" />
                    <span v-if="form.errors.email" class="text-red-500 text-xs font-bold px-2 block mt-1">{{ form.errors.email }}</span>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">
                        Mot de passe {{ isEditing ? '(Optionnel - Laisser vide pour conserver)' : '' }}
                    </label>
                    <InputText v-model="form.password" type="password" placeholder="••••••••" class="w-full !rounded-xl !border-blue-100 !bg-blue-50/30 !p-4 !font-bold !text-slate-800 focus:!ring-2 focus:!ring-[#1DA1F2]" />
                    <span v-if="form.errors.password" class="text-red-500 text-xs font-bold px-2 block mt-1">{{ form.errors.password }}</span>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Rôle Assigné</label>
                    <select v-model="form.role" class="w-full rounded-xl border-2 border-blue-100 bg-blue-50/30 p-4 font-bold text-slate-800 focus:ring-2 focus:ring-[#1DA1F2] outline-none">
                        <option value="admin">Administrateur</option>
                        <option value="super_admin">Super Administrateur</option>
                        <option value="player">Joueur</option>
                    </select>
                    <span v-if="form.errors.role" class="text-red-500 text-xs font-bold px-2 block mt-1">{{ form.errors.role }}</span>
                </div>

                <div class="pt-4">
                    <Button type="submit" :loading="form.processing" class="w-full !py-4 !bg-[#1DA1F2] !border-none !rounded-xl !shadow-xl !shadow-blue-200">
                        <span class="text-lg font-black italic uppercase tracking-widest text-white">Enregistrer le compte</span>
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

<style scoped>
h1, h2, h3, h4, span, button { font-family: 'Bangers', cursive; }
.font-sans { font-family: 'Outfit', sans-serif !important; }

.prime-custom-dialog :deep(.p-dialog-header) {
    background: transparent !important;
    border-bottom: 2px border-blue-50 !important;
    padding: 2rem 2rem 1rem 2rem !important;
}

.prime-custom-dialog :deep(.p-dialog-content) {
    background: transparent !important;
    padding: 1rem 2rem 2rem 2rem !important;
}

.prime-custom-dialog :deep(.p-dialog) {
    border-radius: 2rem !important;
    background: rgba(255, 255, 255, 0.9) !important;
    backdrop-filter: blur(12px) !important;
    box-shadow: 0 25px 50px -12px rgba(29, 161, 242, 0.15) !important;
    border: 4px solid white !important;
}
</style>
