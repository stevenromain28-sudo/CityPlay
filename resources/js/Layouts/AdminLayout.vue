<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    title: String,
    bgClass: {
        type: String,
        default: 'bg-[#F0F7FF]'
    }
});

const page = usePage();
const isSidebarOpen = ref(false);

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const confirmModal = ref({
    show: false,
    title: '',
    message: '',
    onConfirm: () => {}
});

const triggerLogout = () => {
    confirmModal.value = {
        show: true,
        title: 'Déconnexion',
        message: 'Êtes-vous sûr de vouloir quitter la console d\'administration ?',
        onConfirm: () => {
            router.post(route('logout'));
        }
    };
};

const navItems = [
    { name: 'Dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', route: 'admin.dashboard' },
    { name: 'Villes', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', route: 'admin.villes.index' },
    { name: 'Lieux', icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z', route: 'admin.lieux.index' },
    { name: 'Énigmes', icon: 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z', route: 'admin.enigmes.index' },
    { name: 'Culture', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', route: 'admin.contenus-culturels.index' },
];

if (page.props.auth.user.roles.includes('super_admin')) {
    navItems.push({ name: 'Users', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', route: 'admin.users.index' });
}
</script>

<template>
    <div class="flex min-h-screen font-sans overflow-x-hidden">
        <Head :title="title" />

        <!-- Sidebar Desktop -->
        <aside :class="['fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-blue-100 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0', isSidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full']">
            <div class="flex flex-col h-full">
                <!-- Sidebar Header -->
                <div class="h-24 flex items-center px-8 border-b border-blue-50">
                    <div class="w-10 h-10 bg-[#1DA1F2] rounded-xl flex items-center justify-center shadow-lg transform rotate-6 mr-3">
                        <span class="text-white text-xl font-black italic">C</span>
                    </div>
                    <span class="text-slate-800 font-black uppercase italic tracking-tighter text-lg">City<span class="text-[#1DA1F2]">Play</span></span>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-8 space-y-2 overflow-y-auto custom-scrollbar">
                    <Link v-for="item in navItems" :key="item.name" 
                          :href="route(item.route)" 
                          :class="['flex items-center px-4 py-3.5 rounded-2xl transition-all font-black uppercase italic tracking-widest text-xs group', route().current(item.route) ? 'bg-blue-50 text-[#1DA1F2]' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                        </svg>
                        {{ item.name }}
                    </Link>
                </nav>

                <!-- Sidebar Footer -->
                <div class="p-4 border-t border-blue-50">
                    <button @click="triggerLogout" class="w-full flex items-center px-4 py-3.5 rounded-2xl text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all font-black uppercase italic tracking-widest text-xs group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Déconnexion
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 bg-[#F0F7FF]">
            <!-- Header -->
            <header class="bg-white/80 backdrop-blur-md sticky top-0 z-[40] border-b border-blue-50 shadow-sm h-20 md:h-24 flex items-center px-6 md:px-10 justify-between">
                <div class="flex items-center">
                    <!-- Mobile Menu Button -->
                    <button @click="toggleSidebar" class="p-2 mr-4 text-slate-400 hover:text-[#1DA1F2] lg:hidden transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path v-if="!isSidebarOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Page Title / Back button -->
                    <div class="flex items-center">
                        <Link v-if="!route().current('admin.dashboard')" 
                              :href="route('admin.dashboard')" 
                              class="flex items-center bg-blue-50 text-[#1DA1F2] hover:bg-[#1DA1F2] hover:text-white px-4 py-2.5 rounded-2xl transition-all font-black uppercase italic tracking-widest text-[10px] shadow-sm mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span class="hidden xs:inline">Dashboard</span>
                        </Link>
                        <h2 class="text-lg md:text-xl font-black italic uppercase text-slate-800 tracking-tighter">{{ title }}</h2>
                    </div>
                </div>

                <!-- Profile Info -->
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-slate-800 font-black text-sm uppercase italic leading-tight">{{ $page.props.auth.user.name }}</p>
                        <p class="text-[#1DA1F2] text-[10px] font-black uppercase tracking-widest mt-0.5">
                            {{ $page.props.auth.user.roles.includes('super_admin') ? 'Super Admin' : 'Admin' }}
                        </p>
                    </div>
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-yellow-400 p-0.5 shadow-lg rotate-3 shrink-0">
                        <img :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user.name}&background=1DA1F2&color=fff`" class="w-full h-full rounded-[14px] object-cover" alt="Avatar">
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main :class="['flex-1 p-6 md:p-10 transition-all duration-300', bgClass]">
                <slot />
            </main>
        </div>

        <!-- Mobile Overlay -->
        <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 z-[45] bg-slate-900/60 backdrop-blur-sm lg:hidden transition-opacity duration-300"></div>

        <!-- Modale de Confirmation Globale -->
        <div v-if="confirmModal.show" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="confirmModal.show = false"></div>
            <div class="bg-white rounded-[2.5rem] p-8 md:p-12 max-w-lg w-full relative z-10 shadow-2xl border border-slate-100 animate-in fade-in zoom-in duration-200">
                <h3 class="text-3xl font-black italic uppercase text-slate-800 mb-4 tracking-tighter">{{ confirmModal.title }}</h3>
                <p class="text-slate-500 font-bold mb-10 leading-relaxed">{{ confirmModal.message }}</p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <button @click="confirmModal.show = false" class="flex-1 px-8 py-4 bg-slate-100 text-slate-400 rounded-2xl font-black italic uppercase tracking-widest hover:bg-slate-200 transition-colors">Annuler</button>
                    <button @click="confirmModal.onConfirm" class="flex-1 px-8 py-4 bg-red-500 text-white rounded-2xl font-black italic uppercase tracking-widest shadow-xl shadow-red-200 hover:scale-105 transition-all">Confirmer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@300;400;500;600;700&family=Outfit:wght@100;200;300;400;500;600;700;800;900&display=swap');

.font-sans {
    font-family: 'Outfit', sans-serif;
}

h1, h2, h3, h4, button, span {
    font-family: 'Fredoka', sans-serif;
}

/* Scrollbar personnalisée discrète */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #1DA1F233;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #1DA1F266;
}
</style>
