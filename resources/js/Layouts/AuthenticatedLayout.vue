<script setup>
import { ref, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, router } from '@inertiajs/vue3';
import gsap from 'gsap';

const showingNavigationDropdown = ref(false);
const isNavigating = ref(false);
const loaderRef = ref(null);

onMounted(() => {
    router.on('start', () => {
        isNavigating.value = true;
        gsap.fromTo(loaderRef.value, 
            { opacity: 0 }, 
            { opacity: 1, duration: 0.3 }
        );
    });

    router.on('finish', () => {
        gsap.to(loaderRef.value, {
            opacity: 0,
            duration: 0.5,
            onComplete: () => {
                isNavigating.value = false;
            }
        });
    });
});
</script>

<template>
    <div>
        <!-- Custom Loader Luxury -->
        <div v-if="isNavigating" ref="loaderRef" class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/90 backdrop-blur-sm">
            <div class="flex flex-col items-center">
                <div class="relative w-24 h-24 mb-6">
                    <!-- Cercle de progression -->
                    <svg class="w-full h-full rotate-[-90deg]">
                        <circle cx="48" cy="48" r="45" stroke="#F1F5F9" stroke-width="6" fill="transparent" />
                        <circle cx="48" cy="48" r="45" stroke="#1DA1F2" stroke-width="6" fill="transparent" 
                            stroke-dasharray="283" class="animate-loader-circle" />
                    </svg>
                    <!-- Logo central -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <ApplicationLogo class="w-12 h-12 text-[#1DA1F2] animate-pulse" />
                    </div>
                </div>
                <div class="text-2xl font-black italic uppercase tracking-tighter text-[#1DA1F2]">
                    City<span class="text-yellow-400">Play</span>
                </div>
                <div class="mt-2 text-[10px] font-bold uppercase tracking-[0.5em] text-slate-400 animate-pulse">
                    Chargement de l'aventure...
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-gray-100">
            <nav
                class="border-b border-gray-100 bg-white"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="$page.props.auth.user.roles.includes('admin') ? route('admin.dashboard') : route('player.dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    v-if="$page.props.auth.user.roles.includes('admin')"
                                    :href="route('admin.dashboard')"
                                    :active="route().current('admin.dashboard')"
                                >
                                    Admin Dashboard
                                </NavLink>
                                <NavLink
                                    v-if="$page.props.auth.user.roles.includes('admin')"
                                    :href="route('admin.contenus-culturels.index')"
                                    :active="route().current('admin.contenus-culturels.index')"
                                >
                                    Culture
                                </NavLink>
                                <NavLink
                                    v-if="$page.props.auth.user.roles.includes('player')"
                                    :href="route('player.dashboard')"
                                    :active="route().current('player.dashboard')"
                                >
                                    Jouer
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <!-- Contenu du menu -->
                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
            <div
                :class="{
                    block: showingNavigationDropdown,
                    hidden: !showingNavigationDropdown,
                }"
                class="sm:hidden"
            >
                <div class="space-y-1 pb-3 pt-2">
                    <ResponsiveNavLink
                        v-if="$page.props.auth.user.roles.includes('admin')"
                        :href="route('admin.dashboard')"
                        :active="route().current('admin.dashboard')"
                    >
                        Admin Dashboard
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        v-if="$page.props.auth.user.roles.includes('admin')"
                        :href="route('admin.contenus-culturels.index')"
                        :active="route().current('admin.contenus-culturels.index')"
                    >
                        Culture
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        v-if="$page.props.auth.user.roles.includes('player')"
                        :href="route('player.dashboard')"
                        :active="route().current('player.dashboard')"
                    >
                        Jouer
                    </ResponsiveNavLink>
                </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
@keyframes loader-circle {
    0% { stroke-dashoffset: 283; }
    50% { stroke-dashoffset: 70; }
    100% { stroke-dashoffset: 283; }
}
.animate-loader-circle {
    animation: loader-circle 2s ease-in-out infinite;
    stroke-linecap: round;
}
</style>
