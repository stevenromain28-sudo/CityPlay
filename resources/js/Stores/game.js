import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useGameStore = defineStore('game', () => {
    // State - Session Global
    const session = ref(null);
    const joueursConnectes = ref([]);
    const tempsSessionRestant = ref(0); // Temps global de la session (choisi par le joueur)
    const sessionTimerInterval = ref(null);

    // State - Lieu Actuel
    const enigmeActive = ref(null);
    const lieuActuel = ref(null);
    const tempsLieuRestant = ref(0); // Temps restant sur le lieu actuel (duree_estimee)
    const tempsLieuPasse = ref(0); // Temps déjà passé sur le lieu
    const lieuTimerInterval = ref(null);
    const lieuDepasse = ref(false); // True si le temps du lieu est dépassé

    // Getters
    const estEnJeu = computed(() => !!session.value && session.value.statut === 'actif');
    
    const formatTemps = (secondes) => {
        const h = Math.floor(secondes / 3600);
        const m = Math.floor((secondes % 3600) / 60);
        const s = secondes % 60;
        return `${h > 0 ? h + ':' : ''}${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    };

    const formatTempsSession = computed(() => formatTemps(tempsSessionRestant.value));
    const formatTempsLieu = computed(() => formatTemps(tempsLieuRestant.value));

    // Actions - Session Global
    function setSession(newSession) {
        session.value = newSession;
        
        if (!sessionTimerInterval.value || Math.abs(tempsSessionRestant.value - newSession.temps_restant) > 2) {
            tempsSessionRestant.value = newSession.temps_restant;
        }

        if (newSession.statut === 'actif') {
            startSessionTimer();
        } else {
            stopSessionTimer();
        }
    }

    function startSessionTimer() {
        if (sessionTimerInterval.value) return;
        
        sessionTimerInterval.value = setInterval(() => {
            if (tempsSessionRestant.value > 0) {
                tempsSessionRestant.value--;
            } else {
                session.value.statut = 'temps_epuise';
                stopSessionTimer();
            }
        }, 1000);
    }

    function stopSessionTimer() {
        if (sessionTimerInterval.value) {
            clearInterval(sessionTimerInterval.value);
            sessionTimerInterval.value = null;
        }
    }

    // Actions - Lieu Actuel
    function entrerDansLieu(lieu) {
        lieuActuel.value = lieu;
        tempsLieuPasse.value = 0;
        tempsLieuRestant.value = (lieu.duree_estimee || 15) * 60; // Convertir minutes en secondes
        lieuDepasse.value = false;
        startLieuTimer();
    }

    function startLieuTimer() {
        if (lieuTimerInterval.value) return;
        
        lieuTimerInterval.value = setInterval(() => {
            if (tempsLieuRestant.value > 0) {
                tempsLieuRestant.value--;
                tempsLieuPasse.value++;
            } else {
                lieuDepasse.value = true;
                // On continue de compter le temps passé même après dépassement
                tempsLieuPasse.value++;
            }
        }, 1000);
    }

    function stopLieuTimer() {
        if (lieuTimerInterval.value) {
            clearInterval(lieuTimerInterval.value);
            lieuTimerInterval.value = null;
        }
    }

    function terminerLieu() {
        stopLieuTimer();
        // Retourner les informations utiles pour le backend
        return {
            temps_utilise: tempsLieuPasse.value,
            lieu_depasse: lieuDepasse.value
        };
    }

    function syncTempsForce(serverSeconds) {
        tempsSessionRestant.value = serverSeconds;
    }

    return {
        // Session
        session,
        joueursConnectes,
        tempsSessionRestant,
        estEnJeu,
        formatTempsSession,
        setSession,
        startSessionTimer,
        stopSessionTimer,
        syncTempsForce,
        
        // Lieu
        enigmeActive,
        lieuActuel,
        tempsLieuRestant,
        tempsLieuPasse,
        lieuDepasse,
        formatTempsLieu,
        entrerDansLieu,
        startLieuTimer,
        stopLieuTimer,
        terminerLieu
    };
});
