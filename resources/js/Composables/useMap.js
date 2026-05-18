import { ref, onUnmounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

export function useMap() {
    const map = ref(null);
    const userMarker = ref(null);
    const markers = ref([]);
    const watchId = ref(null);

    function initMap(containerId, center = [48.8566, 2.3522], zoom = 13) {
        map.value = L.map(containerId, {
            zoomControl: false,
            attributionControl: false
        }).setView(center, zoom);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
        }).addTo(map.value);

        return map.value;
    }

    function addMarker(lat, lng, options = {}) {
        const marker = L.marker([lat, lng], options).addTo(map.value);
        markers.value.push(marker);
        return marker;
    }

    function addPlaceMarker(lat, lng, name, status = 'pending') {
        const color = status === 'validated' ? '#22c55e' : '#7C3AED';
        const icon = L.divIcon({
            className: 'place-marker',
            html: `<div class="w-6 h-6 rounded-full border-4 border-white shadow-lg flex items-center justify-center" style="background-color: ${color}">
                    <div class="w-2 h-2 bg-white rounded-full"></div>
                   </div>`,
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        const marker = L.marker([lat, lng], { icon }).addTo(map.value);
        marker.bindPopup(`<b class="font-sans uppercase text-[10px] tracking-widest">${name}</b>`);
        markers.value.push(marker);
        return marker;
    }

    function startTracking(onUpdate) {
        if ("geolocation" in navigator) {
            watchId.value = navigator.geolocation.watchPosition(
                (position) => {
                    const { latitude, longitude } = position.coords;
                    updateUserLocation(latitude, longitude);
                    if (onUpdate) onUpdate(latitude, longitude);
                },
                (error) => console.error("Erreur GPS:", error),
                { enableHighAccuracy: true }
            );
        }
    }

    function updateUserLocation(lat, lng) {
        if (!map.value) return;

        if (!userMarker.value) {
            const userIcon = L.divIcon({
                className: 'user-location-marker',
                html: '<div class="w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-lg animate-pulse"></div>',
                iconSize: [16, 16]
            });
            userMarker.value = L.marker([lat, lng], { icon: userIcon }).addTo(map.value);
        } else {
            userMarker.value.setLatLng([lat, lng]);
        }
    }

    function stopTracking() {
        if (watchId.value) {
            navigator.geolocation.clearWatch(watchId.value);
            watchId.value = null;
        }
    }

    function centerOnUser() {
        if (userMarker.value) {
            map.value.flyTo(userMarker.value.getLatLng(), 16);
        }
    }

    onUnmounted(() => {
        stopTracking();
        if (map.value) {
            map.value.remove();
        }
    });

    return {
        map,
        initMap,
        addMarker,
        addPlaceMarker,
        startTracking,
        stopTracking,
        centerOnUser,
        updateUserLocation
    };
}
