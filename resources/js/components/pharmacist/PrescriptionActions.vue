<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

interface Props {
  prescriptionId: number;
  paymentStatus: string;
  size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md'
});

const emit = defineEmits<{
  statusUpdated: [status: string];
  paymentProcessed: [data: any];
  invoiceGenerated: [data: any];
}>();

// Modal states
// const showStatusModal = ref(false);
const showPaymentModal = ref(false);
// const selectedStatus = ref(props.prescriptionStatus);
const paymentMethod = ref('cash');
const pharmacistNotes = ref('');
const isLoading = ref(false);

// Status options
const statusOptions = [
  { value: 'accepted', label: 'Diterima' },
  { value: 'preparing', label: 'Sedang Dibuat' },
  { value: 'completed', label: 'Selesai' }
];

const paymentMethods = [
  { value: 'cash', label: 'Tunai' },
  { value: 'card', label: 'Kartu' },
  { value: 'transfer', label: 'Transfer' }
];

// Update prescription status using correct endpoint
// const updatePrescriptionStatus = () => {
//   if (isLoading.value) return;
  
//   isLoading.value = true;
  
//   router.put(`/dashboard/pharmacist/patient/${props.prescriptionId}/status`, {
//     status: selectedStatus.value
//   }, {
//     preserveState: false,
//     preserveScroll: true,
//     onSuccess: () => {
//       emit('statusUpdated', selectedStatus.value);
//       // showStatusModal.value = false;
//     },
//     onError: (errors) => {
//       console.error('Error updating status:', errors);
//     },
//     onFinish: () => {
//       isLoading.value = false;
//     }
//   });
// };

// Process payment using correct endpoint
const processPayment = () => {
  if (isLoading.value) return;
  
  isLoading.value = true;
  
  router.post(`/dashboard/pharmacist/patient/${props.prescriptionId}/payment`, {
    payment_method: paymentMethod.value,
    notes_pharmacist: pharmacistNotes.value
  }, {
    preserveState: false,
    preserveScroll: true,
    onSuccess: () => {
      emit('paymentProcessed', {
        payment_status: 'success',
        payment_method: paymentMethod.value,
        notes_pharmacist: pharmacistNotes.value
      });
      showPaymentModal.value = false;
    },
    onError: (errors) => {
      console.error('Error processing payment:', errors);
    },
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

// Generate invoice using correct endpoint and Inertia router (POST to same page)
const generateInvoice = () => {
  if (isLoading.value) return;
  
  isLoading.value = true;
  
  router.post(`/dashboard/pharmacist/patient/${props.prescriptionId}/invoice`, {}, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: (page) => {
      // The invoice modal will be handled by the PatientDetail page component via flash data
      let flash = page.props.flash as { invoice?: any};
      emit('invoiceGenerated', flash?.invoice);
    },
    onError: (errors) => {
      console.error('Error generating invoice:', errors);
    },
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

const getSizeClasses = () => {
  switch (props.size) {
    case 'sm':
      return {
        button: 'px-4 py-2 text-sm',
        icon: 'w-4 h-4'
      };
    case 'md':
      return {
        button: 'px-6 py-3 text-base',
        icon: 'w-5 h-5'
      };
    case 'lg':
      return {
        button: 'px-8 py-4 text-lg',
        icon: 'w-6 h-6'
      };
    default:
      return {
        button: 'px-6 py-3 text-base',
        icon: 'w-5 h-5'
      };
  }
};

const sizeClasses = getSizeClasses();
</script>

<template>
  <div class="flex items-center gap-3">
    <!-- Update Status Button -->
    <!-- <button 
      v-if="prescriptionStatus !== 'completed'"
      @click="showStatusModal = true"
      :class="`flex items-center gap-2 ${sizeClasses.button} bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl font-bold font-['Inter'] hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl cursor-pointer`"
    >
      <svg :class="sizeClasses.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
      <span>Update Status</span>
    </button> -->

    <!-- Process Payment Button -->
    <button 
      v-if="paymentStatus === 'waiting'"
      @click="showPaymentModal = true"
      :class="`flex items-center gap-2 ${sizeClasses.button} bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-xl font-bold font-['Inter'] hover:from-emerald-600 hover:to-green-700 transition-all duration-300 shadow-lg hover:shadow-xl cursor-pointer`"
    >
      <svg :class="sizeClasses.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
      </svg>
      <span>Proses Pembayaran</span>
    </button>

    <!-- Generate Invoice Button -->
    <button 
      v-if="paymentStatus === 'success'"
      @click="generateInvoice"
      :class="`flex items-center gap-2 ${sizeClasses.button} bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-bold font-['Inter'] hover:from-purple-600 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl cursor-pointer`"
    >
      <svg :class="sizeClasses.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
      </svg>
      <span>Cetak Invoice</span>
    </button>

    <!-- Status Update Modal -->
    <!-- <div v-if="showStatusModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl shadow-2xl p-8 mx-4 max-w-md w-full border border-gray-100">
        <h3 class="text-2xl font-bold text-gray-800 font-['Epilogue'] mb-6">Update Status Resep</h3>
        
        <div class="space-y-4 mb-6">
          <label v-for="option in statusOptions" :key="option.value" class="flex items-center gap-3 cursor-pointer">
            <input type="radio" v-model="selectedStatus" :value="option.value" class="text-blue-600 focus:ring-blue-500">
            <span class="font-medium text-gray-700">{{ option.label }}</span>
          </label>
        </div>

        <div class="flex gap-3">
          <button @click="showStatusModal = false" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
            Batal
          </button>
          <button @click="updatePrescriptionStatus" :disabled="isLoading" 
                  class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors cursor-pointer">
            {{ isLoading ? 'Memproses...' : 'Update' }}
          </button>
        </div>
      </div>
    </div> -->

    <!-- Payment Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl shadow-2xl p-8 mx-4 max-w-md w-full border border-gray-100">
        <h3 class="text-2xl font-bold text-gray-800 font-['Epilogue'] mb-6">Proses Pembayaran</h3>
        
        <div class="space-y-4 mb-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
            <select v-model="paymentMethod" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option v-for="method in paymentMethods" :key="method.value" :value="method.value">
                {{ method.label }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Apoteker</label>
            <textarea v-model="pharmacistNotes" 
                     class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                     rows="3" 
                     placeholder="Tambahkan catatan (opsional)"></textarea>
          </div>
        </div>

        <div class="flex gap-3">
          <button @click="showPaymentModal = false" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            Batal
          </button>
          <button @click="processPayment" :disabled="isLoading"
                  class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors">
            {{ isLoading ? 'Memproses...' : 'Proses Pembayaran' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template> 