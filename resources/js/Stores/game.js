import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useGameStore = defineStore('game', () => {
    // State
    const session = ref(null);
    const enigmeActive = ref(null);
    const joueursConnectes = ref([]);
    const tempsRestant = ref(0); // On stocke les secondes ici
    const timerInterval = ref(null);

    // Getters
    const estEnJeu = computed(() => !!session.value && session.value.statut === 'actif');
    const formatTemps = computed(() => {
        const secondes = tempsRestant.value;
        const h = Math.floor(secondes / 3600);
        const m = Math.floor((secondes % 3600) / 60);
        const s = secondes % 60;
        return `${h > 0 ? h + ':' : ''}${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    });

    // Actions
    function setSession(newSession) {
        session.value = newSession;
        
        // Synchronisation du temps restant (uniquement si dérive importante > 2s ou premier chargement)
        if (!timerInterval.value || Math.abs(tempsRestant.value - newSession.temps_restant) > 2) {
            tempsRestant.value = newSession.temps_restant;
        }

        if (newSession.statut === 'actif') {
            startTimer();
        } else {
            stopTimer();
        }
    }

    function startTimer() {
        if (timerInterval.value) return; // Évite les doublons
        
        timerInterval.value = setInterval(() => {
            if (tempsRestant.value > 0) {
                tempsRestant.value--;
            } else {
                session.value.statut = 'temps_epuise';
                stopTimer();
            }
        }, 1000);
    }

    function stopTimer() {
        if (timerInterval.value) {
            clearInterval(timerInterval.value);
            timerInterval.value = null;
        }
    }

    function syncTempsForce(serverSeconds) {
        tempsRestant.value = serverSeconds;
    }

    return {
        session,
        enigmeActive,
        joueursConnectes,
        tempsRestant,
        estEnJeu,
        formatTemps,
        setSession,
        startTimer,
        stopTimer,
        syncTempsForce
    };
});