<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    stats: Object,
    ma_ville: Object,
    recent_lieux: Array
});

const dashboardContainer = ref(null);
const bgSlider = ref(null);
const isMobileMenuOpen = ref(false);

onMounted(() => {
    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

    tl.from(dashboardContainer.value, {
        opacity: 0,
        duration: 0.8
    })
    .from('.sidebar-item', {
        x: -50,
        opacity: 0,
        stagger: 0.1,
        duration: 0.5
    }, '-=0.4')
    .from('.stat-card', {
        y: 30,
        opacity: 0,
        stagger: 0.1,
        duration: 0.6
    }, '-=0.3');

    // Simple fade for content sections instead of moving them
    gsap.from('.content-section', {
        opacity: 0,
        duration: 1,
        delay: 0.5
    });

    // Background Sliding Animation
    gsap.to('.bg-slide', {
        xPercent: -20,
        duration: 20,
        repeat: -1,
        yoyo: true,
        ease: "linear"
    });
});

const hoverCard = (el) => {
    gsap.to(el.currentTarget, {
        y: -10,
        scale: 1.02,
        duration: 0.4,
        ease: 'back.out(1.7)'
    });
};

const leaveCard = (el) => {
    gsap.to(el.currentTarget, {
        y: 0,
        scale: 1,
        duration: 0.4,
        ease: 'power2.out'
    });
};

const notifyModal = ref({
    show: false,
    type: 'success',
    title: '',
    message: ''
});

const triggerNotify = (type, title, message) => {
    notifyModal.value = { show: true, type, title, message };
};

const invitationLink = ref('');
const generateLink = () => {
    if (invitationLink.value) {
        navigator.clipboard.writeText(invitationLink.value);
        triggerNotify('success', 'Lien Copié', 'Le lien d\'invitation a été copié dans votre presse-papier !');
        return;
    }
    const token = Math.random().toString(36).substring(2, 10).toUpperCase();
    invitationLink.value = window.location.origin + '/join/' + token;
    
    // Copy automatically on first generation
    setTimeout(() => {
        navigator.clipboard.writeText(invitationLink.value);
        triggerNotify('success', 'Lien Généré', 'Le lien d\'invitation épique a été généré et copié dans le presse-papier !');
    }, 100);
};
</script>

<template>
    <Head title="Admin Dashboard" />

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
               class="fixed md:relative inset-y-0 left-0 flex w-24 lg:w-32 bg-[#1DA1F2] flex-col items-center pt-10 pb-6 md:py-10 shadow-[5px_0_30px_rgba(29,161,242,0.1)] z-[100] transition-transform duration-300 ease-in-out md:translate-x-0 overflow-y-auto no-scrollbar">
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
                    <!-- Mobile Menu Button -->
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
                    <div class="relative hidden xl:block">
                        <input type="text" placeholder="RECHERCHER UNE AVENTURE..." class="bg-blue-50 border-none rounded-2xl py-4 px-14 w-96 text-xs font-bold tracking-widest text-slate-600 focus:ring-2 focus:ring-[#1DA1F2] transition-all placeholder:text-slate-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-6 top-1/2 -translate-y-1/2 text-[#1DA1F2]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    
                    <div class="flex items-center space-x-3 md:space-x-5 md:pl-10 md:border-l-2 md:border-blue-50">
                        <div class="text-right hidden sm:block">
                            <p class="text-slate-800 font-black text-sm md:text-lg uppercase italic leading-tight">{{ $page.props.auth.user.name }}</p>
                            <p class="text-[#1DA1F2] text-[10px] font-black uppercase tracking-widest">
                                {{ $page.props.auth.user.roles.includes('super_admin') ? 'Super Admin' : 'Master Admin' }}
                            </p>
                        </div>
                        <div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl md:rounded-[2rem] bg-yellow-400 p-0.5 md:p-1 shadow-lg shadow-yellow-200 rotate-3">
                            <img src="https://ui-avatars.com/api/?name=Admin&background=1DA1F2&color=fff" class="w-full h-full rounded-xl md:rounded-[1.8rem] object-cover" alt="Avatar">
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto px-4 md:px-12 py-6 md:py-12 space-y-8 md:space-y-16 custom-scrollbar">
                <section class="content-section">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 md:mb-10 gap-4 md:gap-6">
                        <div>
                            <h2 class="text-3xl md:text-5xl font-black italic uppercase tracking-tighter text-slate-800">Ma <span class="text-[#1DA1F2]">Ville</span></h2>
                            <p class="text-slate-400 text-xs md:text-sm font-bold uppercase tracking-widest mt-2">Votre terrain d'exploration</p>
                        </div>
                        <Link :href="route('admin.villes.index')" class="w-full sm:w-auto px-6 md:px-8 py-3 md:py-4 bg-yellow-400 text-white text-xs md:text-sm font-black uppercase tracking-widest rounded-xl md:rounded-2xl shadow-xl shadow-yellow-100 hover:scale-105 transition-transform active:scale-95 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                            {{ ma_ville ? 'Gérer ma ville' : 'Créer ma ville' }}
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-10">
                        <!-- Ma Ville Section -->
                        <div class="lg:col-span-2">
                            <Link v-if="ma_ville" :href="route('admin.villes.index')" 
                                 class="stat-card relative group overflow-hidden rounded-3xl md:rounded-[3rem] h-full min-h-[300px] md:min-h-[400px] cursor-pointer shadow-2xl transition-all duration-500 bg-white border-2 border-white"
                                 @mouseenter="hoverCard" @mouseleave="leaveCard">
                                
                                <!-- Background Image with Overlay -->
                                <div class="absolute inset-0">
                                    <img v-if="ma_ville.banniere" :src="ma_ville.banniere" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                    <div v-else class="w-full h-full bg-gradient-to-br from-[#1DA1F2] to-blue-700"></div>
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>
                                </div>

                                <!-- Content -->
                                <div class="absolute inset-0 p-6 md:p-12 flex flex-col justify-between">
                                    <div class="flex justify-between items-start">
                                        <span class="px-4 py-1.5 md:px-6 md:py-2 bg-white/20 backdrop-blur-md text-white text-[8px] md:text-[10px] font-black uppercase rounded-xl tracking-[0.2em] border border-white/20">Votre Capitale</span>
                                        <div class="w-10 h-10 md:w-14 md:h-14 bg-white text-[#1DA1F2] rounded-2xl md:rounded-3xl flex items-center justify-center shadow-2xl opacity-0 group-hover:opacity-100 transition-all duration-300 -translate-y-4 group-hover:translate-y-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-8 md:w-8" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>
                                        </div>
                                    </div>

                                    <div class="mt-auto">
                                        <h3 class="text-4xl md:text-6xl font-black italic uppercase text-white tracking-tighter leading-none mb-3 md:mb-4 group-hover:text-yellow-400 transition-colors">
                                            {{ ma_ville.nom }}
                                        </h3>
                                        <div class="flex flex-wrap gap-4 items-center">
                                            <div class="flex items-center text-white/80 space-x-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                <span class="text-xs md:text-sm font-bold uppercase tracking-widest">{{ ma_ville.lieux_count || 0 }} Lieux</span>
                                            </div>
                                            <div class="flex items-center text-white/80 space-x-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5 text-[#1DA1F2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                <span class="text-xs md:text-sm font-bold uppercase tracking-widest">Aventure Active</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Link>

                            <!-- Case: No City -->
                            <div v-else class="h-full min-h-[300px] md:min-h-[400px] rounded-3xl md:rounded-[3rem] bg-white border-4 border-dashed border-blue-100 flex flex-col items-center justify-center p-6 md:p-12 text-center group hover:border-[#1DA1F2] transition-colors">
                                <div class="w-16 h-16 md:w-24 md:h-24 bg-blue-50 text-[#1DA1F2] rounded-2xl md:rounded-[2rem] flex items-center justify-center mb-6 md:mb-8 group-hover:scale-110 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 md:h-12 md:w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                </div>
                                <h3 class="text-2xl md:text-3xl font-black italic uppercase text-slate-800 tracking-tighter mb-2">Aucune ville n'est sous votre règne</h3>
                                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs mb-6 md:mb-8">Commencez par bâtir votre terrain de jeu</p>
                                <Link :href="route('admin.villes.index')" class="px-8 py-4 md:px-10 md:py-5 bg-[#1DA1F2] text-white rounded-2xl md:rounded-[2rem] font-black italic uppercase tracking-widest shadow-2xl shadow-blue-200 hover:scale-105 transition-transform text-sm md:text-base">
                                    Fonder ma ville
                                </Link>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-10">
                    <div v-for="(stat, index) in [
                        {label: 'Explorateurs', value: stats.users_count, color: 'bg-[#1DA1F2]', icon: 'users'},
                        {label: 'Aventures', value: stats.sessions_count, color: 'bg-yellow-400', icon: 'map'},
                        {label: 'Lieux', value: stats.lieux_count, color: 'bg-green-400', icon: 'location'},
                        {label: 'Énigmes', value: stats.enigmes_count, color: 'bg-purple-400', icon: 'puzzle'}
                    ]" :key="index" class="bg-white p-5 md:p-8 rounded-3xl md:rounded-[2.5rem] shadow-xl shadow-blue-50 border border-blue-50 group hover:scale-105 transition-transform duration-300">
                        <div class="flex items-center justify-between mb-4 md:mb-6">
                            <div :class="`w-10 h-10 md:w-14 md:h-14 ${stat.color} rounded-xl md:rounded-2xl flex items-center justify-center text-white shadow-lg`">
                                <svg v-if="stat.icon==='users'" class="h-5 w-5 md:h-7 md:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                <svg v-if="stat.icon==='map'" class="h-5 w-5 md:h-7 md:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9" /></svg>
                                <svg v-if="stat.icon==='location'" class="h-5 w-5 md:h-7 md:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>
                                <svg v-if="stat.icon==='puzzle'" class="h-5 w-5 md:h-7 md:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" /></svg>
                            </div>
                        </div>
                        <h4 class="text-3xl md:text-4xl font-black italic tracking-tighter text-slate-800 leading-none">{{ stat.value }}</h4>
                        <p class="text-slate-400 text-[8px] md:text-[10px] font-black uppercase tracking-[0.2em] mt-1 md:mt-2 truncate">{{ stat.label }}</p>
                    </div>
                </div>

                <!-- Invitation Section -->
                <section class="content-section bg-white rounded-3xl md:rounded-[3rem] p-6 md:p-10 shadow-xl shadow-blue-50 border border-blue-50">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-6 md:gap-8">
                        <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-6 text-center md:text-left">
                            <div class="w-16 h-16 md:w-20 md:h-20 bg-yellow-400 rounded-2xl md:rounded-[2rem] flex items-center justify-center text-white shadow-xl rotate-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 md:h-10 md:w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                            </div>
                            <div>
                                <h3 class="text-2xl md:text-3xl font-black italic uppercase text-slate-800 tracking-tighter">Recruter des <span class="text-[#1DA1F2]">Explorateurs</span></h3>
                                <p class="text-slate-400 text-xs md:text-sm font-bold uppercase tracking-widest mt-1">Générez un lien d'accès épique</p>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
                            <div class="w-full md:w-96 bg-blue-50 rounded-xl md:rounded-2xl px-4 py-3 md:px-6 md:py-4 font-bold text-slate-400 text-[10px] md:text-xs truncate border-2 border-dashed border-blue-100 text-center">
                                {{ invitationLink || 'CLIQUEZ POUR GÉNÉRER' }}
                            </div>
                            <button @click="generateLink" class="w-full sm:w-auto px-6 py-3 md:px-8 md:py-4 bg-[#1DA1F2] text-white text-xs md:text-sm font-black uppercase tracking-widest rounded-xl md:rounded-2xl shadow-lg hover:scale-105 transition-transform active:scale-95">
                                {{ invitationLink ? 'Copier' : 'Générer' }}
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </main>

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
    </div>
</template>

<style scoped>
.font-sans {
    font-family: 'Outfit', sans-serif;
}

h1, h2, h3, h4, button, span {
    font-family: 'Bangers', cursive;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #F0F7FF;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #1DA1F2;
    border-radius: 20px;
    border: 3px solid #F0F7FF;
}

.bg-slide {
    will-change: transform;
}
</style>

