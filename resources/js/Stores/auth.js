import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
    // State
    const user = ref(null)
    const isAuthenticated = ref(false)

    // Getters
    const nom = computed(() => user.value?.name ?? 'Joueur')
    const xp = computed(() => user.value?.xp ?? 0)

    // Actions
    function setUser(newUser) {
        user.value = newUser
        isAuthenticated.value = !!newUser
    }

    function logout() {
        user.value = null
        isAuthenticated.value = false
    }

    return {
        user,
        isAuthenticated,
        nom,
        xp,
        setUser,
        logout
    }
})