<script setup>
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';
import { ref, onMounted } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import gsap from 'gsap';

const props = defineProps({
    users: Array,
    roles: Array,
    pendingRequests: Array,
});

const isMobileMenuOpen = ref(false);
const visible = ref(false);
const isEditing = ref(false);
const editingUserId = ref(null);
const isRequestsModalVisible = ref(false);

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

const approveRequest = (request) => {
    triggerConfirm(
        "Approuver la demande ?",
        `Voulez-vous vraiment approuver la demande d'adhésion de ${request.name} en tant qu'administrateur ?`,
        () => {
            router.post(route('admin.users.approve-admin', request.id), {}, {
                onSuccess: () => {
                    isRequestsModalVisible.value = false;
                    triggerNotify('success', 'Demande approuvée', 'L\'utilisateur a été promu administrateur.');
                }
            });
        }
    );
};

const rejectRequest = (request) => {
    triggerConfirm(
        "Refuser la demande ?",
        `Voulez-vous vraiment refuser la demande d'adhésion de ${request.name} ?`,
        () => {
            router.post(route('admin.users.reject-admin', request.id), {}, {
                onSuccess: () => {
                    isRequestsModalVisible.value = false;
                    triggerNotify('success', 'Demande refusée', 'La demande a été rejetée avec succès.');
                }
            });
        }
    );
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

    <div class="min-h-screen bg-[#F0F7FF] text-slate-800 flex relative overflow-x-hidden font-sans">
        
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

            <div class="max-w-7xl mx-auto">
                <!-- Page Title -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 md:mb-12 gap-6">
                    <div>
                        <h2 class="text-2xl md:text-5xl font-black italic uppercase tracking-tighter text-slate-800">
                            Gestion des <span class="text-[#1DA1F2]">Comptes</span>
                        </h2>
                        <p class="text-slate-400 text-[10px] md:text-sm font-bold uppercase tracking-widest mt-2 flex items-center">
                            <span class="w-6 md:w-8 h-1 bg-[#1DA1F2] mr-3 md:mr-4"></span>
                            Administrer les privilèges de CityPlay
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <button @click="isRequestsModalVisible = true" class="relative !px-4 !py-3 !bg-blue-50 hover:!bg-blue-100 !text-[#1DA1F2] !rounded-2xl transition-all hover:scale-105 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="font-black uppercase tracking-widest text-xs">Demandes</span>
                            <span v-if="pendingRequests && pendingRequests.length > 0" class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-[10px] font-black border-2 border-white animate-bounce">
                                {{ pendingRequests.length }}
                            </span>
                        </button>
                        <Button @click="openNew" class="!px-4 md:!px-6 !py-2.5 md:!py-4 !bg-yellow-400 !border-none !rounded-2xl !shadow-xl hover:scale-105 transition-transform flex items-center justify-center">
                            <template #default>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 4v16m8-8H4" /></svg>
                                <span class="text-white font-black italic uppercase tracking-widest text-[10px] md:text-sm">Créer un Admin</span>
                            </template>
                        </Button>
                    </div>
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

        <!-- Pending Requests Dialog -->
        <Dialog v-model:visible="isRequestsModalVisible" modal :style="{ width: '45rem' }" class="prime-custom-dialog">
            <template #header>
                <div class="flex items-center">
                    <span class="text-3xl font-black italic uppercase text-[#1DA1F2] tracking-tighter">
                        Demandes d'Adhésion Admin
                    </span>
                </div>
            </template>
            
            <div class="py-4 font-sans px-2 max-h-[60vh] overflow-y-auto custom-scrollbar">
                <div v-if="!pendingRequests || pendingRequests.length === 0" class="text-center py-10 text-slate-400 font-bold">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto stroke-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                    </svg>
                    Aucune demande d'adhésion en attente.
                </div>
                <div v-else class="space-y-4">
                    <div v-for="request in pendingRequests" :key="request.id" class="bg-blue-50/40 border border-blue-100 rounded-3xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h4 class="text-lg font-black italic uppercase text-slate-800 tracking-tight">{{ request.name }}</h4>
                                <span class="px-2.5 py-0.5 bg-yellow-100 text-yellow-600 text-[8px] font-black uppercase tracking-wider rounded-md">Adhésion</span>
                            </div>
                            <p class="text-xs font-bold text-[#1DA1F2]">{{ request.email }}</p>
                            <p class="text-xs text-slate-500 font-bold">
                                Ville souhaitée : 
                                <span class="font-black text-slate-800 uppercase italic text-yellow-600">{{ request.requested_city }}</span>
                            </p>
                            <p class="text-[9px] text-slate-400 font-bold uppercase">Demandé le {{ request.created_at }}</p>
                        </div>
                        <div class="flex space-x-2 shrink-0">
                            <Button @click="rejectRequest(request)" class="!px-4 !py-2.5 !bg-red-50 hover:!bg-red-100 !text-red-500 !border-none !rounded-xl transition-colors">
                                <template #default>
                                    <span class="font-black uppercase tracking-wider text-xs">Refuser</span>
                                </template>
                            </Button>
                            <Button @click="approveRequest(request)" class="!px-4 !py-2.5 !bg-[#1DA1F2] hover:!bg-[#1DA1F2]/90 !text-white !border-none !rounded-xl transition-colors shadow-md shadow-blue-500/10">
                                <template #default>
                                    <span class="font-black uppercase tracking-wider text-xs">Approuver</span>
                                </template>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </Dialog>

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

<style scoped>
h1, h2, h3, h4, span, button { font-family: 'Fredoka', sans-serif; }
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
