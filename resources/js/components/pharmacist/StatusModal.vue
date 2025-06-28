
<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { showToast } from '@/lib/utils';
import { Patient } from '@/types/patient';

interface Props {
  show: boolean;
  patientId?: number | null;
  status?: string;
}

interface Emits {
  (e: 'close'): void;
  (e: 'update:status', status: Patient['status']): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

console.log(`PROPS: ${props.status}`);

const selectedStatus = ref<string>('');
const isLoading = ref(false);

// Watch for props changes to update selectedStatus
watch(() => props.status, (newStatus) => {
  console.log(`NEW STATUS: ${newStatus}`);
  if (newStatus) {
    selectedStatus.value = newStatus;
  }
}, { immediate: true, deep: true });

onMounted(() => {
  console.log(`COMPUTED STATUS: ${selectedStatus.value}`);
});

// Update prescription status
const updatePrescriptionStatus = () => {
  if (isLoading.value || !props.patientId) return;
  
  isLoading.value = true;
  
  router.put(`/dashboard/pharmacist/patient/${props.patientId}/status`, {
    prescription_status: selectedStatus.value,
    notes_pharmacist: selectedStatus.value === 'completed' ? 'Obat telah disiapkan dan siap diambil' : null
  }, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      showToast('Status resep berhasil diupdate', 'success');
      emit('update:status', selectedStatus.value as Patient['status']);
      emit('close');
    },
    onError: (errors) => {
      console.error('Error updating status:', errors);
      showToast('Terjadi kesalahan saat mengupdate status resep', 'error');
    },
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

const closeModal = () => {
  emit('close');
};

</script>

<template>
    <div v-if="props.show" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-2xl p-6 mx-4 max-w-md w-full border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 font-['Epilogue'] mb-4">Update Status Resep</h3>
        
        <!-- Status Options -->
        <div class="space-y-3 mb-4">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" v-model="selectedStatus" value="accepted" class="text-blue-600">
            <span class="font-medium text-sm">Diterima</span>
            <span class="text-xs text-gray-500">(Resep telah diterima)</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" v-model="selectedStatus" value="preparing" class="text-blue-600">
            <span class="font-medium text-sm">Dibuat</span>
            <span class="text-xs text-gray-500">(Sedang menyiapkan obat)</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" v-model="selectedStatus" value="completed" class="text-blue-600">
            <span class="font-medium text-sm">Selesai</span>
            <span class="text-xs text-gray-500">(Obat siap diambil)</span>
          </label>
        </div>

        <div class="flex gap-2">
          <button 
            @click="closeModal" 
            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg cursor-pointer text-sm hover:bg-gray-50 transition-colors"
          >
            Batal
          </button>
          <button 
            @click="updatePrescriptionStatus" 
            :disabled="isLoading" 
            class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 cursor-pointer text-sm transition-colors"
          >
            {{ isLoading ? 'Memproses...' : 'Update' }}
          </button>
        </div>
      </div>
    </div>
</template>