<template>
  <div v-if="show" class="fixed inset-0 z-[300] flex items-start justify-end p-4">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" @click="close"></div>
    <div class="relative w-80 max-w-full h-full bg-white shadow-xl border-l-4 border-purple-600 overflow-y-auto">
      <div class="flex items-center justify-between p-4 border-b">
        <h3 class="text-lg font-black uppercase text-purple-700">Notifications</h3>
        <button @click="close" class="text-gray-500 hover:text-gray-800">✕</button>
      </div>
      <ul class="divide-y">
        <li v-for="toast in history" :key="toast.id" class="p-4">
          <div class="flex items-start space-x-2">
            <span class="text-xs text-gray-500">{{ new Date(toast.timestamp).toLocaleTimeString() }}</span>
            <p :class="['font-bold', toast.type === 'success' ? 'text-green-800' : toast.type === 'error' ? 'text-red-800' : 'text-blue-800']">
              {{ toast.message }}
            </p>
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
import { computed } from 'vue';
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

function close() {
  emit('update:show', false);
}

function clearAll() {
  // clear persisted history array in the store
  gameStore.toastHistory = [];
  close();
}
</script>

<style scoped>
/* Add slide‑in animation if desired */
</style>
