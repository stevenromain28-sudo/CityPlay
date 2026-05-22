<template>
  <div v-if="show" class="fixed inset-0 z-[300] flex items-start justify-end p-4">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" @click="close"></div>
    <div class="relative w-80 max-w-full h-full bg-white shadow-xl border-l-4 border-purple-600 overflow-y-auto">
      <div class="flex items-center justify-between p-4 border-b">
        <h3 class="text-lg font-black uppercase text-purple-700">Notifications</h3>
        <button @click="close" class="text-gray-500 hover:text-gray-800">✕</button>
      </div>
      <ul class="divide-y">
        <li v-for="toast in history" :key="toast.id" class="p-4 group">
          <div class="flex items-start justify-between">
            <div class="flex flex-col space-y-1">
              <span class="text-xs text-gray-500 font-bold">{{ new Date(toast.timestamp).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
              <p :class="['font-bold text-sm', toast.type === 'success' ? 'text-green-800' : toast.type === 'error' ? 'text-red-800' : 'text-blue-800']">
                {{ toast.message }}
              </p>
            </div>
            <button @click="deleteItem(toast.id)" class="text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity p-1 shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </button>
          </div>
        </li>
      </ul>
      <div class="p-4 border-t">
        <button @click="clearAll" class="w-full py-2 bg-purple-600 text-white rounded hover:bg-purple-700 transition">Tout effacer</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, watch, onMounted } from 'vue';
import { useGameStore } from '@/Stores/game';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
});
const emit = defineEmits(['update:show']);

const gameStore = useGameStore();

const history = computed(() => gameStore.toastHistory);

watch(() => props.show, (newVal) => {
  if (newVal) {
    gameStore.fetchNotifications();
  }
});

onMounted(() => {
  gameStore.fetchNotifications();
});

function close() {
  emit('update:show', false);
}

function deleteItem(id) {
  gameStore.deleteNotification(id);
}

function clearAll() {
  gameStore.clearAllNotifications();
  close();
}
</script>

<style scoped>
/* Add slide‑in animation if desired */
</style>
