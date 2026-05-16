import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useGameStore = defineStore('game', () => {
    // State
    const session = ref(null);
    const enigmeActive = ref(null);
    const joueursConnectes = ref([]);
    const tempsEcoule = ref(0);
    const timerInterval = ref(null);

    // Getters
    const estEnJeu = computed(() => !!session.value && session.value.statut === 'actif');
    const progressionPourcentage = computed(() => {
        if (!session.value || !session.value.total_enigmes) return 0;
        return (session.value.progression / session.value.total_enigmes) * 100;
    });

    // Actions
    function setSession(newSession) {
        session.value = newSession;
        if (newSession.statut === 'actif') {
            startTimer();
        }
    }

    function setEnigmeActive(enigme) {
        enigmeActive.value = enigme;
    }

    function updateJoueurs(joueurs) {
        joueursConnectes.value = joueurs;
    }

    function startTimer() {
        if (timerInterval.value) return;
        timerInterval.value = setInterval(() => {
            tempsEcoule.value++;
        }, 1000);
    }

    function stopTimer() {
        if (timerInterval.value) {
            clearInterval(timerInterval.value);
            timerInterval.value = null;
        }
    }

    function resetGame() {
        session.value = null;
        enigmeActive.value = null;
        joueursConnectes.value = [];
        tempsEcoule.value = 0;
        stopTimer();
    }

    return {
        session,
        enigmeActive,
        joueursConnectes,
        tempsEcoule,
        estEnJeu,
        progressionPourcentage,
        setSession,
        setEnigmeActive,
        updateJoueurs,
        startTimer,
        stopTimer,
        resetGame
    };
});
