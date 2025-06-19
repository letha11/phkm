<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { formatCurrency } from '@/lib/utils';
import type { Patient } from '@/types/patient';
import PrescriptionActions from '@/components/pharmacist/PrescriptionActions.vue';
interface InvoiceItem {
  medicine_name: string;
  dosage: string;
  quantity: number;
  price_per_unit: number;
  total_price: number;
}

interface InvoiceData {
  invoice_number: string;
  date: string;
  patient: {
    name: string;
    date_of_birth: string;
  };
  doctor: string;
  items: InvoiceItem[];
  consultation_fee: number;
  medicines_subtotal: number;
  ppn_rate: number;
  ppn_amount: number;
  total_amount: number;
  paid_amount: number;
  payment_method: string;
}

// Get data from backend
const page = usePage();
const patient = ref<Patient>(page.props.patient as Patient);
const invoiceData = ref<InvoiceData | null>(page.props.invoice as InvoiceData || null);
const flashMessage = ref<string | null>(
  (page.props.flash as { message?: string })?.message || null
);

// Modal states
const showStatusModal = ref(false);
const showPaymentModal = ref(false);
const showInvoiceModal = ref(page.props.showInvoiceModal as boolean || false);
const selectedStatus = ref(patient.value.status);
const paymentMethod = ref('cash');
const pharmacistNotes = ref(patient.value.notes_pharmacist || '');
const isLoading = ref(false);

// Set invoice data if provided from backend
onMounted(() => {
  if (page.props.invoice) {
    invoiceData.value = page.props.invoice as InvoiceData;
  }
  
  // Set initial invoice modal state
  if (page.props.showInvoiceModal) {
    showInvoiceModal.value = true;
  }
  
  // Show flash message if any
  if ((page.props.flash as any)?.message) {
    showFlashMessage((page.props.flash as any).message);
  }
});

// Watch for page prop changes to update invoice modal
watch(() => page.props, (newProps) => {
  if (newProps.invoice) {
    invoiceData.value = newProps.invoice as InvoiceData;
    // Auto-show modal when invoice data is received
    showInvoiceModal.value = true;
  }
  
  if (newProps.showInvoiceModal) {
    showInvoiceModal.value = true;
  }
  
  // Update patient data if provided
  if (newProps.patient) {
    patient.value = newProps.patient as Patient;
  }
  
  // Handle flash message
  if ((newProps.flash as any)?.message) {
    showFlashMessage((newProps.flash as any).message);
  }
}, { deep: true });

// Watch for showInvoiceModal changes
watch(showInvoiceModal, (newValue) => {
  console.log('showInvoiceModal changed to:', newValue);
  console.log('invoiceData:', invoiceData.value);
});

// Helper function to show flash message
const showFlashMessage = (message: string) => {
  flashMessage.value = message;
  setTimeout(() => {
    flashMessage.value = null;
  }, 3000);
};

// Helper functions for status display
const getStatusColor = (status: Patient['status']) => {
  switch (status) {
    case 'waiting':
      return 'bg-amber-400';
    case 'success':
      return 'bg-emerald-500';
    case 'failed':
      return 'bg-rose-500';
    default:
      return 'bg-gray-400';
  }
};

const getStatusGradient = (status: Patient['status']) => {
  switch (status) {
    case 'waiting':
      return 'from-amber-50 to-yellow-100 border-amber-200'; 
    case 'success':
      return 'from-emerald-50 to-green-100 border-emerald-200'; 
    case 'failed':
      return 'from-rose-50 to-red-100 border-rose-200'; 
    default:
      return 'from-gray-50 to-gray-100 border-gray-200';
  }
};

const getStatusText = (status: Patient['status']) => {
  switch (status) {
    case 'waiting':
      return 'Menunggu';
    case 'success':
      return 'Selesai';
    case 'failed':
      return 'Gagal';
    default:
      return 'Unknown';
  }
};

const getPrescriptionStatusText = (status: string) => {
  switch (status) {
    case 'accepted':
      return 'Diterima';
    case 'preparing':
      return 'Sedang Dibuat';
    case 'completed':
      return 'Selesai';
    default:
      return status;
  }
};

// Format medications into array for better display
const formattedMedications = computed(() => patient.value.medications.split('\n'));

// Update prescription status using Inertia router
const updatePrescriptionStatus = () => {
  if (isLoading.value) return;
  
  isLoading.value = true;
  showStatusModal.value = false;
  
  router.put(`/dashboard/pharmacist/patient/${patient.value.id}/status`, {
    status: selectedStatus.value
  }, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: (page) => {
      // Update local patient data with response
      if (page.props.patient) {
        patient.value = page.props.patient as Patient;
      }
      showFlashMessage('Status berhasil diupdate');
    },
    onError: (errors) => {
      console.error('Error updating status:', errors);
      showFlashMessage('Terjadi kesalahan saat mengupdate status');
    },
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

// Process payment using Inertia router
const processPayment = () => {
  if (isLoading.value) return;
  
  isLoading.value = true;
  showPaymentModal.value = false;
  
  router.post(`/dashboard/pharmacist/patient/${patient.value.id}/payment`, {
    payment_method: paymentMethod.value,
    notes_pharmacist: pharmacistNotes.value
  }, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: (page) => {
      // Update local patient data with response
      if (page.props.patient) {
        patient.value = page.props.patient as Patient;
      }
      showFlashMessage('Pembayaran berhasil diproses');
    },
    onError: (errors) => {
      console.error('Error processing payment:', errors);
      showFlashMessage('Terjadi kesalahan saat memproses pembayaran');
    },
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

// Generate invoice using Inertia router (POST to same page)
const generateInvoice = () => {
  if (isLoading.value) return;
  
  isLoading.value = true;
  
  router.post(`/dashboard/pharmacist/patient/${patient.value.id}/invoice`, {}, {
    preserveState: false, // Allow state refresh to get new props
    preserveScroll: true,
    onSuccess: (page: any) => {
      
      // Data is now available directly in page.props via redirect()->back()->with()
      if (page.props.invoice) {
        invoiceData.value = page.props.invoice as InvoiceData;
        showInvoiceModal.value = true;
      }
      
      // Handle flash message from middleware
      if ((page.props.flash as any)?.message) {
        showFlashMessage((page.props.flash as any).message);
      }
      
      if (!page.props.invoice) {
      }
    },
    onError: (errors) => {
      showFlashMessage('Terjadi kesalahan saat membuat invoice');
    },
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

// Print invoice
const printInvoice = () => {
  // Focus on invoice content before printing
  const invoiceContent = document.querySelector('.invoice-content');
  if (invoiceContent) {
    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(`
        <html>
          <head>
            <title>Invoice - ${invoiceData.value?.invoice_number}</title>
            <style>
              body { font-family: Arial, sans-serif; margin: 20px; }
              table { width: 100%; border-collapse: collapse; margin: 20px 0; }
              th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
              th { background-color: #f2f2f2; }
              .text-center { text-align: center; }
              .text-right { text-align: right; }
              .font-bold { font-weight: bold; }
              .bg-gray-50 { background-color: #f9fafb; }
              .grid { display: grid; }
              .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
              .gap-6 { gap: 1.5rem; }
              .mb-6 { margin-bottom: 1.5rem; }
              .mb-2 { margin-bottom: 0.5rem; }
              .text-2xl { font-size: 1.5rem; }
              .text-green-600 { color: #059669; }
              @page { margin: 1in; }
            </style>
          </head>
          <body>
            ${invoiceContent.innerHTML}
          </body>
        </html>
      `);
      printWindow.document.close();
      
      printWindow.focus();
      setTimeout(function () { printWindow.print(); }, 200);
      printWindow.onfocus = function () { setTimeout(function () { printWindow.close(); }, 200); }
    }
  }
};
</script>

<template>
  <!-- Flash Message -->
  <div v-if="flashMessage" class="fixed top-4 right-4 z-50 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    <span class="font-medium text-sm">{{ flashMessage }}</span>
    <button @click="flashMessage = null" class="ml-2 text-white hover:text-gray-200">
      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>

  <!-- Main Patient Detail Container -->
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <!-- Main Content Area -->
    <div class="flex justify-center items-start py-8 px-4">
      <!-- Patient Detail Card -->
      <div class="w-full max-w-5xl bg-white rounded-xl flex flex-col gap-5 px-8 py-8 shadow-lg border border-gray-100">
        
        <!-- Header Section -->
        <div class="w-full flex flex-col gap-3">
          <!-- Back Button and Title -->
          <div class="w-full flex justify-between items-center">
            <!-- Back Button Container -->
            <div class="flex justify-start">
              <a href="/dashboard/pharmacist" class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg transition-all duration-300 group">
                <svg class="w-4 h-4 text-gray-600 group-hover:text-gray-800 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="text-xs font-medium text-gray-600 group-hover:text-gray-800 font-['Inter']">Kembali</span>
              </a>
            </div>
            
            <div class="text-center">
              <h1 class="text-2xl font-bold text-gray-800 tracking-tight font-['Epilogue']">
                Detail Resep
            </h1>
              <p class="text-sm text-gray-600 font-['Inter'] mt-1">
                Informasi lengkap pasien dan resep
              </p>
            </div>
            
            <!-- Status Badge Container -->
            <div class="flex justify-end">
              <div :class="`bg-gradient-to-r ${getStatusGradient(patient.status)} px-3 py-1.5 rounded-full border flex items-center gap-2 shadow-sm`">
                <div :class="getStatusColor(patient.status)" class="w-2 h-2 rounded-full shadow-sm"></div>
                <span class="text-xs font-semibold text-gray-700 font-['Inter']">
                  {{ getStatusText(patient.status) }}
                </span>
              </div>
            </div>
          </div>
          
          <!-- Header Divider -->
          <div class="w-full h-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full"></div>
        </div>

        <!-- Patient Name and ID - Center Top -->
        <div class="w-full text-center py-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
          <div class="flex items-center justify-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center shadow-lg">
              <img src="/assets/icons/avatar.svg" alt="Patient" class="w-6 h-6">
            </div>
          </div>
          <h2 class="text-2xl font-bold text-gray-800 font-['Epilogue'] mb-2">
            {{ patient.name }}
          </h2>
          <div class="flex items-center justify-center gap-2">
            <p class="text-sm font-semibold text-gray-600 font-['Inter']">
            ID: #{{ patient.id.toString().padStart(4, '0') }}
          </p>
          </div>
        </div>

        <!-- Information Sections -->
        <div class="w-full flex flex-col gap-5">
          
          <!-- Personal Information Section -->
          <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-md hover:shadow-lg transition-all duration-300">
            <div class="flex items-center gap-3 mb-5">
              <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center shadow-lg">
                <img src="/assets/icons/avatar.svg" alt="Patient" class="w-4 h-4">
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-800 font-['Epilogue']">
                Informasi Pribadi
              </h3>
                <p class="text-xs text-gray-600 font-['Inter']">
                  Data personal pasien
                </p>
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <!-- Birth Date -->
              <div class="bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                  </svg>
                  <span class="text-xs font-bold text-purple-700 font-['Inter'] uppercase tracking-wide">
                  Tanggal Lahir
                </span>
                </div>
                <span class="text-base font-bold text-gray-800 font-['Inter']">
                  {{ patient.birthDate }}
                </span>
              </div>
              
              <!-- Visit Time -->
              <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                  </svg>
                  <span class="text-xs font-bold text-blue-700 font-['Inter'] uppercase tracking-wide">
                  Waktu Kunjungan
                </span>
                </div>
                <span class="text-base font-bold text-gray-800 font-['Inter']">
                  {{ patient.timeAgo }}
                </span>
              </div>
            </div>
          </div>

          <!-- Medical Information Grid -->
          <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            
            <!-- Complaint Section -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-md hover:shadow-lg transition-all duration-300">
              <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-rose-600 rounded-lg flex items-center justify-center shadow-lg">
                  <img src="/assets/icons/warning.svg" alt="Patient" class="fill-white w-4 h-4">
                </div>
                <div>
                  <h3 class="text-lg font-bold text-gray-800 font-['Epilogue']">
                  Keluhan Pasien
                </h3>
                  <p class="text-xs text-gray-600 font-['Inter']">
                    Gejala yang dialami
                  </p>
                </div>
              </div>
              
              <div class="bg-gradient-to-br from-red-50 to-rose-100 rounded-lg p-4 border border-red-200">
                <p class="text-sm font-medium text-gray-800 font-['Inter'] leading-relaxed">
                  {{ patient.complaint }}
                </p>
              </div>
            </div>

            <!-- Medications Section -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-md hover:shadow-lg transition-all duration-300">
              <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-lg">
                  <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                  </svg>
                </div>
                <div>
                  <h3 class="text-lg font-bold text-gray-800 font-['Epilogue']">
                  Resep Obat
                </h3>
                  <p class="text-xs text-gray-600 font-['Inter']">
                    Daftar obat yang diresepkan
                  </p>
                </div>
              </div>
              
              <div class="flex flex-col gap-3">
                <div 
                  v-for="(medication, index) in formattedMedications" 
                  :key="index"
                  class="flex items-center gap-3 p-3 bg-gradient-to-r from-green-50 to-emerald-100 rounded-lg border border-green-200 hover:shadow-md transition-all duration-300"
                >
                  <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-emerald-500 rounded-lg flex-shrink-0 flex items-center justify-center shadow-lg">
<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M20 7L4 7" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"></path> <path d="M20 12L4 12" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"></path> <path d="M20 17L4 17" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"></path> </g></svg>
                  </div>
                  
                  <div class="flex-1">
                    <p class="text-sm font-bold text-gray-800 font-['Inter']">
                      {{ medication.trim() }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Treatment Status Section -->
          <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-md hover:shadow-lg transition-all duration-300">
            <div class="flex items-center gap-3 mb-5">
              <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-lg">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 01-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 010 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 010-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15 13.586V12a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-800 font-['Epilogue']">
                Status Pengobatan
              </h3>
                <p class="text-xs text-gray-600 font-['Inter']">
                  Status terkini dari resep
                </p>
              </div>
            </div>
            
            <div :class="`flex items-center justify-between p-4 bg-gradient-to-r ${getStatusGradient(patient.status)} rounded-lg border shadow-sm`">
              <div class="flex items-center gap-4">
                <div :class="getStatusColor(patient.status)" class="w-4 h-4 rounded-full shadow-lg"></div>
                <div>
                  <p class="text-base font-bold text-gray-800 font-['Inter']">
                    {{ getStatusText(patient.status) }}
                  </p>
                  <p class="text-xs font-medium text-gray-600 font-['Inter'] mt-1">
                    <span v-if="patient.status === 'waiting'">Menunggu penanganan apoteker</span>
                    <span v-else-if="patient.status === 'success'">Resep telah diselesaikan</span>
                    <span v-else-if="patient.status === 'failed'">Terjadi masalah dalam penanganan</span>
                  </p>
                </div>
              </div>
              
              <!-- Status timestamp -->
              <div class="text-right bg-white/50 backdrop-blur-sm rounded-lg p-3">
                <p class="text-xs font-bold text-gray-600 font-['Inter'] uppercase tracking-wide">
                  Terakhir Update
                </p>
                <p class="text-sm font-bold text-gray-800 font-['Inter'] mt-1">
                  {{ patient.timeAgo }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="w-full flex justify-center gap-4 mt-8 pt-5 border-t">
         <PrescriptionActions 
          :prescriptionId="patient.id"
          :prescriptionStatus="patient.status"
          :paymentStatus="patient.payment_status"
          :size="'sm'"
        />
          <!-- Update Status Button
          <button 
            v-if="patient.status !== 'success'"
            @click="showStatusModal = true"
            class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg font-bold font-['Inter'] hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 shadow-md hover:shadow-lg cursor-pointer text-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Update Status</span>
          </button> -->

          <!-- Process Payment Button -->
          <!-- <button 
            v-if="patient.status === 'waiting' && patient.payment_status === 'waiting'"
            @click="showPaymentModal = true"
            class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-lg font-bold font-['Inter'] hover:from-emerald-600 hover:to-green-700 transition-all duration-300 shadow-md hover:shadow-lg cursor-pointer text-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
            </svg>
            <span>Proses Pembayaran</span>
          </button>
          
          Generate Invoice Button
          <button 
            v-if="patient.payment_status === 'success'"
            @click="generateInvoice"
            class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg font-bold font-['Inter'] hover:from-purple-600 hover:to-purple-700 transition-all duration-300 shadow-md hover:shadow-lg cursor-pointer text-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span>Cetak Invoice</span>
          </button> -->
        </div>
      </div>
    </div>

    <!-- Status Update Modal -->
    <div v-if="showStatusModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-2xl p-6 mx-4 max-w-md w-full border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 font-['Epilogue'] mb-4">Update Status Resep</h3>
        
        <div class="space-y-3 mb-4">
          <label class="flex items-center gap-2">
            <input type="radio" v-model="selectedStatus" value="success" class="text-blue-600">
            <span class="font-medium text-sm">Selesai</span>
          </label>
          <label class="flex items-center gap-2">
            <input type="radio" v-model="selectedStatus" value="failed" class="text-blue-600">
            <span class="font-medium text-sm">Gagal</span>
          </label>
          <label class="flex items-center gap-2">
            <input type="radio" v-model="selectedStatus" value="waiting" class="text-blue-600">
            <span class="font-medium text-sm">Menunggu</span>
          </label>
        </div>

        <div class="flex gap-2">
          <button @click="showStatusModal = false" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg cursor-pointer text-sm">
            Batal
          </button>
          <button @click="updatePrescriptionStatus" :disabled="isLoading" 
                  class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 cursor-pointer text-sm">
            {{ isLoading ? 'Memproses...' : 'Update' }}
          </button>
        </div>
      </div>
    </div>
          
    <!-- Payment Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-2xl p-6 mx-4 max-w-md w-full border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 font-['Epilogue'] mb-4">Proses Pembayaran</h3>
        
        <div class="space-y-3 mb-4">
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Metode Pembayaran</label>
            <select v-model="paymentMethod" class="w-full p-2 border rounded-lg text-sm">
              <option value="cash">Tunai</option>
              <option value="card">Kartu</option>
              <option value="transfer">Transfer</option>
            </select>
          </div>
          
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Catatan Apoteker</label>
            <textarea v-model="pharmacistNotes" 
                     class="w-full p-2 border rounded-lg text-sm" 
                     rows="3" 
                     placeholder="Tambahkan catatan (opsional)"></textarea>
          </div>
        </div>

        <div class="flex gap-2">
          <button @click="showPaymentModal = false" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
            Batal
          </button>
          <button @click="processPayment" :disabled="isLoading"
                  class="flex-1 px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 text-sm">
            {{ isLoading ? 'Memproses...' : 'Proses Pembayaran' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Invoice Modal -->
    <div v-if="showInvoiceModal && invoiceData" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-2xl p-6 mx-4 max-w-2xl w-full border border-gray-100 max-h-[90vh] overflow-y-auto">
        <div class="print:hidden flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold text-gray-800 font-['Epilogue']">Invoice</h3>
          <div class="flex gap-2">
            <button @click="printInvoice" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
              Print
            </button>
            <button @click="showInvoiceModal = false" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
              Tutup
            </button>
          </div>
        </div>

        <!-- Invoice Content -->
        <div class="invoice-content">
          <div class="text-center mb-4">
            <h2 class="text-xl font-bold">PHKM Clinic</h2>
            <p class="text-gray-600 text-sm">Invoice Pembayaran Resep</p>
          </div>

          <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
              <h4 class="font-bold mb-2 text-sm">Informasi Pasien:</h4>
              <p class="text-sm">{{ invoiceData.patient.name }}</p>
              <p class="text-sm">{{ invoiceData.patient.date_of_birth }}</p>
            </div>
            <div>
              <h4 class="font-bold mb-2 text-sm">Informasi Invoice:</h4>
              <p class="text-sm">No: {{ invoiceData.invoice_number }}</p>
              <p class="text-sm">Tanggal: {{ invoiceData.date }}</p>
              <p class="text-sm">Dokter: {{ invoiceData.doctor }}</p>
            </div>
          </div>

          <table class="w-full mb-4 border text-sm">
            <thead>
              <tr class="bg-gray-50">
                <th class="border p-2 text-left">Item</th>
                <th class="border p-2 text-right">Qty</th>
                <th class="border p-2 text-right">Harga</th>
                <th class="border p-2 text-right">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in invoiceData.items" :key="item.medicine_name">
                <td class="border p-2">{{ item.medicine_name }} ({{ item.dosage }})</td>
                <td class="border p-2 text-right">{{ item.quantity }}</td>
                <td class="border p-2 text-right">{{ formatCurrency(item.price_per_unit) }}</td>
                <td class="border p-2 text-right">{{ formatCurrency(item.total_price) }}</td>
              </tr>
              <tr>
                <td class="border p-2" colspan="3"><strong>Biaya Konsultasi</strong></td>
                <td class="border p-2 text-right">{{ formatCurrency(invoiceData.consultation_fee) }}</td>
              </tr>
              <tr>
                <td class="border p-2" colspan="3"><strong>PPN ({{ invoiceData.ppn_rate }}%)</strong></td>
                <td class="border p-2 text-right">{{ formatCurrency(invoiceData.ppn_amount) }}</td>
              </tr>
              <tr class="bg-gray-50">
                <td class="border p-2" colspan="3"><strong>Total</strong></td>
                <td class="border p-2 text-right"><strong>{{ formatCurrency(invoiceData.total_amount) }}</strong></td>
              </tr>
            </tbody>
          </table>

          <div class="text-center">
            <p class="text-green-600 font-bold text-sm">LUNAS - {{ invoiceData.payment_method.toUpperCase() }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  .invoice-content, .invoice-content * {
    visibility: visible;
  }
  .invoice-content {
    position: absolute;
    left: 0;
    top: 0;
  }
}
</style>
