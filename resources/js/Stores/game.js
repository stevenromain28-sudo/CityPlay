import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useGameStore = defineStore('game', () => {
    // State - Session Global (Frontend est le seul maître !)
    const session = ref(null);
    const joueursConnectes = ref([]);
    const tempsSessionRestant = ref(0); // Temps global de la session
    const sessionTimerInterval = ref(null);

    const toasts = ref([]);
    const toastHistory = ref([]);
    let toastIdCounter = 0;

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
        
        // On initialise le temps FRONTEND avec la valeur de la session, SANS RECALCUL !
        if (tempsSessionRestant.value === 0 || tempsSessionRestant.value !== newSession.temps_restant) {
            tempsSessionRestant.value = newSession.temps_restant;
        }

        if (newSession.statut === 'actif') {
            startSessionTimer();
            // Si on a un lieu actif, redémarrer aussi le timer du lieu
            if (lieuActuel.value) {
                startLieuTimer();
            }
        } else {
            stopSessionTimer();
            stopLieuTimer();
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
        // On garde cette fonction pour les cas où on doit vraiment recaler (ex: ajouter du temps)
        tempsSessionRestant.value = serverSeconds;
    }

    function updateJoueurs(users) {
        joueursConnectes.value = users;
    }

    // Actions - Toast Notifications
    function addToast(message, type = 'info', persisted = false) {
        const id = ++toastIdCounter;
        const toast = { id, message, type };
        toasts.value.push(toast);
        // We no longer automatically push to history here because the backend handles DB storage.
        // But for immediate visual feedback before the next fetch, we can optionally add it.
        // We will just rely on fetchNotifications for the persistent history.
        
        setTimeout(() => {
            removeToast(id);
        }, 5000);
    }

    function removeToast(id) {
        const index = toasts.value.findIndex(t => t.id === id);
        if (index !== -1) {
            toasts.value.splice(index, 1);
        }
    }

    async function fetchNotifications() {
        try {
            const response = await axios.get('/play/notifications');
            if (response.data.success) {
                toastHistory.value = response.data.notifications.map(n => ({
                    id: n.id,
                    message: n.data.message,
                    type: n.data.type,
                    timestamp: n.created_at
                }));
            }
        } catch (error) {
            console.error('Failed to fetch notifications', error);
        }
    }

    async function deleteNotification(id) {
        try {
            await axios.delete(`/play/notifications/${id}`);
            toastHistory.value = toastHistory.value.filter(n => n.id !== id);
        } catch (error) {
            console.error('Failed to delete notification', error);
        }
    }

    async function clearAllNotifications() {
        try {
            await axios.delete('/play/notifications/clear');
            toastHistory.value = [];
        } catch (error) {
            console.error('Failed to clear notifications', error);
        }
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
        updateJoueurs,

        // Toasts
        toasts,
        toastHistory,
        addToast,
        removeToast,
        fetchNotifications,
        deleteNotification,
        clearAllNotifications,
        
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
