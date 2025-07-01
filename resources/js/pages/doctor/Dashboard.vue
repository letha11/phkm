<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';

import LogoutButton from '@/components/ui/button/LogoutButton.vue';
import LogoutDialog from '@/components/ui/dialog/LogoutDialog.vue';
import PatientSearch from '@/components/doctor/PatientSearch.vue';
import MedicineSearch from '@/components/doctor/MedicineSearch.vue';
import { router } from '@inertiajs/vue3';
import { showToast } from '@/lib/utils';
import type { PatientData, PrescriptionMedicine } from '@/types/medicine.d';

// Form data using Inertia's useForm
const form = useForm({
  patient: {
    name: '',
    date_of_birth: '',
    existing_id: null as number | null,
  } as PatientData,
  symptom: '',
  medicines: [] as PrescriptionMedicine[],
});

const showLogoutModal = ref(false);

// Computed values
const canSubmit = computed(() => {
  return form.patient.name && 
         form.patient.date_of_birth && 
         form.symptom && 
         form.medicines.length > 0 &&
         form.medicines.every((m: PrescriptionMedicine) => m.dosage && m.amount > 0);
});

const totalCost = computed(() => {
  return form.medicines.reduce((total: number, medicine: PrescriptionMedicine) => {
    return total + (medicine.price * medicine.amount);
  }, 0);
});

// Methods
const submitPrescription = () => {
  if (!canSubmit.value || form.processing) return;
  
  form.post('/doctor/prescription/submit', {
    onSuccess: (page) => {
      const flash = page.props.flash as { message?: string, type?: string };
      showToast(flash.message || 'Resep berhasil dikirim', flash.type as 'success' | 'error' | 'warning' | 'info' | undefined || 'success');
      resetForm();
    },
    onError: (errors: any) => {
      console.error('Submission errors:', errors);
    },
  });
};

const resetForm = () => {
  form.reset();
};

const handleLogoutClick = () => {
  showLogoutModal.value = true;
};

const confirmLogout = () => {
  showLogoutModal.value = false;
  router.post('/logout');
};
</script>

<template>
  <Head title="Dashboard Dokter" />
  
  <!-- Main Dashboard Container -->
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">

    <!-- Main Content Area -->
    <div class="flex justify-center items-start py-8 px-4 gap-6">

      <!-- Main Form Panel -->
      <div class="w-full max-w-5xl bg-white rounded-xl flex flex-col gap-5 px-8 py-8 shadow-lg border border-gray-100">
        
        <!-- Header Section -->
        <div class="w-full flex flex-col gap-3">
          <div class="w-full flex justify-between items-center">
            <!-- Welcome Section -->
            <div class="flex flex-col">
              <h1 class="text-3xl font-bold text-gray-800 tracking-tight font-['Epilogue']">
                Dashboard Dokter
              </h1>
              <p class="text-base text-gray-600 font-['Inter'] mt-1">
                Kelola konsultasi pasien dan buat resep digital
              </p>
            </div>
            <LogoutButton @logout="handleLogoutClick" />
          </div>
          <!-- Header Divider -->
          <div class="w-full h-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full"></div>
        </div>

        <!-- Main Form Section -->
        <div class="w-full bg-white rounded-xl border border-gray-200 p-6 shadow-md hover:shadow-lg transition-all duration-300">
          <form @submit.prevent="submitPrescription" class="space-y-8">
            <!-- Patient Data Section -->
            <div class="space-y-4">
              <div class="border-l-4 border-blue-500 pl-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Data Pasien</h3>
                <p class="text-sm text-gray-600">Masukkan data pasien yang akan diresepkan</p>
              </div>
              
              <PatientSearch v-model="form.patient"/>
              
              <div v-if="form.errors.patient" class="text-red-600 text-sm">
                {{ form.errors.patient }}
              </div>
            </div>

            <!-- Symptoms Section -->
            <div class="space-y-4">
              <div class="border-l-4 border-green-500 pl-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Keluhan Pasien</h3>
                <p class="text-sm text-gray-600">Catat gejala dan keluhan yang dialami pasien</p>
              </div>
              
              <div class="space-y-2">
                <Label for="symptom">Keluhan / Gejala *</Label>
                <textarea
                  id="symptom"
                  v-model="form.symptom"
                  placeholder="Jelaskan keluhan dan gejala yang dialami pasien..."
                  rows="4"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <div v-if="form.errors.symptom" class="text-red-600 text-sm">
                  {{ form.errors.symptom }}
                </div>
              </div>
            </div>

            <!-- Medicine Prescription Section -->
            <div class="space-y-4">
              <div class="border-l-4 border-purple-500 pl-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Resep Obat</h3>
                <p class="text-sm text-gray-600">Cari dan tambahkan obat yang akan diresepkan</p>
              </div>
              
              <MedicineSearch 
                v-model="form.medicines"
                @update:modelValue="form.medicines = $event"
              />
              
              <div v-if="form.errors.medicines" class="text-red-600 text-sm">
                {{ form.errors.medicines }}
              </div>
            </div>

            <!-- Submit Section -->
            <div class="pt-6 border-t border-gray-200">
              <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <!-- Total Cost Display -->
                <div v-if="form.medicines.length > 0" class="bg-gray-50 p-4 rounded-lg">
                  <div class="text-sm text-gray-600">Estimasi Total Biaya Obat:</div>
                  <div class="text-xl font-bold text-gray-900">
                    Rp {{ totalCost.toLocaleString('id-ID') }}
                  </div>
                  <div class="text-xs text-gray-500 mt-1">
                    *Belum termasuk biaya konsultasi dan PPN
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-3">
                  <Button
                    type="button"
                    variant="outline"
                    @click="resetForm"
                    :disabled="form.processing"
                  >
                    Reset Form
                  </Button>
                  
                  <Button
                    type="submit"
                    :disabled="!canSubmit || form.processing"
                    class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700"
                  >
                    <div v-if="form.processing" class="flex items-center">
                      <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                      Mengirim...
                    </div>
                    <span v-else>Kirim Resep ke Apoteker</span>
                  </Button>
                </div>
              </div>
              
              <!-- Validation Summary -->
              <div v-if="!canSubmit" class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="text-sm text-yellow-800">
                  <strong>Form belum lengkap:</strong>
                  <ul class="list-disc list-inside mt-1 space-y-1">
                    <li v-if="!form.patient.name">Nama pasien harus diisi</li>
                    <li v-if="!form.patient.date_of_birth">Tanggal lahir pasien harus diisi</li>
                    <li v-if="!form.symptom">Keluhan pasien harus diisi</li>
                    <li v-if="form.medicines.length === 0">Minimal satu obat harus diresepkan</li>
                    <li v-if="form.medicines.some((m: PrescriptionMedicine) => !m.dosage)">Semua obat harus memiliki dosis</li>
                    <li v-if="form.medicines.some((m: PrescriptionMedicine) => m.amount <= 0)">Jumlah obat harus lebih dari 0</li>
                  </ul>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Right Panel - Information -->
      <div class="w-72 bg-white rounded-xl flex flex-col gap-4 px-6 py-6 shadow-lg border border-gray-100 sticky top-8">
        <!-- Information Header -->
        <div class="flex flex-col gap-3">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 10-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
            </div>
            <h2 class="text-lg font-bold text-gray-800 font-['Epilogue']">
              Informasi
            </h2>
          </div>
          <!-- Header Divider -->
          <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>
        </div>

        <!-- Form Status -->
        <div class="flex flex-col gap-3">
          <h3 class="text-base font-semibold text-gray-700 font-['Inter']">Status Form</h3>
          
          <!-- Form Completion Status -->
          <div class="flex flex-col gap-2">
            <div class="flex items-center gap-3 p-3 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg">
              <div :class="[form.patient.name && form.patient.date_of_birth ? 'bg-emerald-500' : 'bg-gray-400']" class="w-3 h-3 rounded-full shadow-sm"></div>
              <div class="flex-1">
                <p class="text-xs font-semibold text-gray-800 font-['Inter']">
                  Data Pasien
                </p>
                <p class="text-xs text-gray-600 font-['Inter']">
                  {{ form.patient.name ? form.patient.date_of_birth ? 'Lengkap' : 'Belum Lengkap' : 'Belum diisi' }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-3 p-3 bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-lg">
              <div :class="[form.symptom ? 'bg-emerald-500' : 'bg-gray-400']" class="w-3 h-3 rounded-full shadow-sm"></div>
              <div class="flex-1">
                <p class="text-xs font-semibold text-gray-800 font-['Inter']">
                  Keluhan Pasien
                </p>
                <p class="text-xs text-gray-600 font-['Inter']">
                  {{ form.symptom ? 'Terisi' : 'Belum diisi' }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-3 p-3 bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 rounded-lg">
              <div :class="[form.medicines.length > 0 ? 'bg-emerald-500' : 'bg-gray-400']" class="w-3 h-3 rounded-full shadow-sm"></div>
              <div class="flex-1">
                <p class="text-xs font-semibold text-gray-800 font-['Inter']">
                  Resep Obat
                </p>
                <p class="text-xs text-gray-600 font-['Inter']">
                  {{ form.medicines.length }} obat dipilih
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-3">
          <h3 class="text-base font-semibold text-gray-700 font-['Inter'] mb-3">Aksi Cepat</h3>
          <div class="flex flex-col gap-2">
            <button
              @click="resetForm"
              :disabled="form.processing"
              :class="[
                'w-full text-white text-xs font-semibold py-2.5 px-3 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg',
                form.processing 
                  ? 'bg-gray-400 cursor-not-allowed' 
                  : 'bg-gray-500 hover:bg-gray-600 cursor-pointer'
              ]"
            >
              {{ form.processing ? 'Memuat...' : 'Reset Form' }}
            </button>
            <button
              v-if="canSubmit"
              @click="submitPrescription"
              :disabled="form.processing"
              :class="[
                'w-full text-white text-xs font-semibold py-2.5 px-3 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg',
                form.processing 
                  ? 'bg-gray-400 cursor-not-allowed' 
                  : 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 cursor-pointer'
              ]"
            >
              {{ form.processing ? 'Mengirim...' : 'Kirim Resep' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <LogoutDialog 
      :show="showLogoutModal" 
      @close="showLogoutModal = false"
      @confirm="confirmLogout"
    />
  </div>
</template>
