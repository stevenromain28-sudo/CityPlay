import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

class WebSocketService {
    constructor() {
        this.echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT,
            wssPort: import.meta.env.VITE_REVERB_PORT,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
    }

    /**
     * Rejoint le canal d'une session de jeu.
     */
    joinSession(sessionId, callbacks) {
        return this.echo.join(`session.${sessionId}`)
            .here(callbacks.onJoined || (() => {}))
            .joining(callbacks.onUserJoining || (() => {}))
            .leaving(callbacks.onUserLeaving || (() => {}))
            .listen('EnigmeResolue', callbacks.onEnigmeResolue || (() => {}))
            .listen('SessionCommencee', callbacks.onSessionCommencee || (() => {}))
            .error((error) => {
                console.error('Erreur WebSocket:', error);
            });
    }

    /**
     * Quitte un canal.
     */
    leaveSession(sessionId) {
        this.echo.leave(`session.${sessionId}`);
    }

    /**
     * Écoute les notifications globales de l'utilisateur.
     */
    listenToPrivateChannel(userId, callback) {
        this.echo.private(`App.Models.User.${userId}`)
            .notification(callback);
    }
}

export const webSocketService = new WebSocketService();
