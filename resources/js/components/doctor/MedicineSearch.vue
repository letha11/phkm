<script setup lang="ts">
import { ref, watch, onMounted, nextTick } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Card, CardContent, } from '@/components/ui/card';
import type { Medicine, PrescriptionMedicine } from '@/types/medicine.d';
import { showToast } from '@/lib/utils';

const props = defineProps<{
  modelValue: PrescriptionMedicine[];
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: PrescriptionMedicine[]): void;
}>();

const searchQuery = ref('');
const searchResults = ref<Medicine[]>([]);
const isSearching = ref(false);
const showResults = ref(false);
const searchTimeout = ref<number | null>(null);
const prescribedMedicines = ref<PrescriptionMedicine[]>([]);
const medicineDetails = ref<Map<number, Medicine>>(new Map());
const editingField = ref<string | null>(null);

let isInternalUpdate = false

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
  if (!isInternalUpdate) {
    prescribedMedicines.value = [...newValue];
    
    // Fetch medicine details for any medicines we don't have cached
    newValue.forEach(medicine => {
      if (!medicineDetails.value.has(medicine.medicine_id)) {
        fetchMedicineDetails(medicine.medicine_id);
      }
    });
    
    // Reset search query when form is reset (when modelValue becomes empty)
    if (newValue.length === 0) {
      searchQuery.value = '';
      showResults.value = false;
    }
  }
}, { immediate: true });

// Emit changes to parent
watch(prescribedMedicines, (newValue) => {
  isInternalUpdate = true;
  emit('update:modelValue', [...newValue]);

  nextTick(() => {
    isInternalUpdate = false;
  });
}, { deep: true });

const searchMedicines = async () => {
  if (!searchQuery.value.trim()) {
    searchResults.value = [];
    showResults.value = false;
    return;
  }
  
  isSearching.value = true;
  
  try {
    const response = await fetch(`/doctor/medicines/search?search=${encodeURIComponent(searchQuery.value)}`);
    const data = await response.json();
    searchResults.value = data;
    showResults.value = data.length > 0;
    
    // Store medicine details for later use
    data.forEach((medicine: Medicine) => {
      medicineDetails.value.set(medicine.id, medicine);
    });
  } catch (error) {
    console.error('Error searching medicines:', error);
    searchResults.value = [];
    showResults.value = false;
  } finally {
    isSearching.value = false;
  }
};

const onSearchInput = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value);
  }
  
  searchTimeout.value = setTimeout(() => {
    searchMedicines();
  }, 300);
};

const addMedicine = (medicine: Medicine) => {
  // Check if medicine is already added
  const existingIndex = prescribedMedicines.value.findIndex(
    m => m.medicine_id === medicine.id
  );
  
  if (existingIndex >= 0) {
    showToast('Obat ini sudah ditambahkan ke resep!', 'warning');
    return;
  }

  // Store medicine details for future reference
  medicineDetails.value.set(medicine.id, medicine);

  const newMedicine: PrescriptionMedicine = {
    medicine_id: medicine.id,
    medicine_name: medicine.name,
    dosage: medicine.dosages[0] || '',
    amount: 1,
    price: medicine.price,
  };

  prescribedMedicines.value.push(newMedicine);
  searchQuery.value = '';
  showResults.value = false;
};

const removeMedicine = (index: number) => {
  prescribedMedicines.value.splice(index, 1);
};

const updateMedicine = (index: number, field: keyof PrescriptionMedicine, value: any) => {
  if (!prescribedMedicines.value[index]) return;
  
  // For amount field, validate against stock
  if (field === 'amount') {
    const medicine = prescribedMedicines.value[index];
    const medicineDetail = medicineDetails.value.get(medicine.medicine_id);
    const stock = medicineDetail?.stock || 0;
    
    if (value > stock) {
      showToast(`Jumlah melebihi stok yang tersedia (${stock})`, 'warning');
      value = stock; // Cap the value to available stock
    }
    
    if (value < 1) {
      value = 1; // Minimum amount is 1
    }
  }
  
  // Update immediately
  prescribedMedicines.value[index] = {
    ...prescribedMedicines.value[index],
    [field]: value,
  };
};

// Debounced validation for stock checking
const validationTimeouts = ref<Map<number, number>>(new Map());

// Watch for amount changes and validate against stock
const validateAmount = (index: number) => {
  const medicine = prescribedMedicines.value[index];
  if (!medicine) return;
  
  const medicineDetail = medicineDetails.value.get(medicine.medicine_id);
  const stock = medicineDetail?.stock || 0;
  const amount = medicine.amount;
  
  if (amount > stock) {
    showToast(`Jumlah melebihi stok yang tersedia (${stock})`, 'warning');
    // Cap the value to available stock
    prescribedMedicines.value[index].amount = stock;
  }
  
  if (amount < 1) {
    prescribedMedicines.value[index].amount = 1;
  }
};

// Handle immediate updates on blur/change/enter for validation
const handleAmountCommit = (index: number) => {
  // Clear any pending timeout
  if (validationTimeouts.value.has(index)) {
    clearTimeout(validationTimeouts.value.get(index));
    validationTimeouts.value.delete(index);
  }
  
  validateAmount(index);
  editingField.value = null;
};

// Handle focus to track editing state
const handleAmountFocus = (index: number) => {
  editingField.value = `amount-${index}`;
};

// Watch for amount changes in prescribedMedicines for real-time validation
watch(prescribedMedicines, (newMedicines) => {
  if (isInternalUpdate) return;
  
  newMedicines.forEach((medicine, index) => {
    // Clear any existing timeout for this index
    if (validationTimeouts.value.has(index)) {
      clearTimeout(validationTimeouts.value.get(index));
    }
    
    // Set debounced validation
    const timeoutId = setTimeout(() => {
      validateAmount(index);
      validationTimeouts.value.delete(index);
    }, 500); // 500ms debounce for typing validation
    
    validationTimeouts.value.set(index, timeoutId);
  });
}, { deep: true });

const fetchMedicineDetails = async (id: number): Promise<Medicine | undefined> => {
  try {
    const response = await fetch(`/doctor/medicines/${id}`);
    if (!response.ok) {
      throw new Error('Medicine not found');
    }
    const medicine = await response.json();
    medicineDetails.value.set(id, medicine);
    return medicine;
  } catch (error) {
    console.error('Error fetching medicine details:', error);
    return undefined;
  }
};

// Removed getMedicineById function - now using medicineDetails.get() directly for better performance

// Close search results when clicking outside
const searchContainer = ref<HTMLDivElement>();

onMounted(() => {
  document.addEventListener('click', (e) => {
    if (searchContainer.value && !searchContainer.value.contains(e.target as Node)) {
      showResults.value = false;
    }
  });
});
</script>

<template>
  <div class="space-y-6">
    <!-- Medicine Search -->
    <div class="space-y-4">
      <Label class="text-sm font-semibold text-gray-800">Resep Obat</Label>
      
      <div class="space-y-2" ref="searchContainer">
        <div class="relative">
          <Input
            v-model="searchQuery"
            @input="onSearchInput"
            placeholder="Cari obat untuk ditambahkan ke resep..."
            :class="{ 'border-blue-500': showResults }"
          />
          
          <!-- Loading indicator -->
          <div v-if="isSearching" class="absolute right-3 top-1/2 transform -translate-y-1/2">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
          </div>
          
          <!-- Search Results -->
          <div v-if="showResults" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
            <div
              v-for="medicine in searchResults"
              :key="medicine.id"
              @click="medicine.stock <= 0 ? showToast(`Stok ${medicine.name} habis`, 'error') : addMedicine(medicine)"
              :class="[
                'p-3 border-b border-gray-100 last:border-b-0',
                medicine.stock <= 0 
                  ? 'bg-red-50 opacity-75 cursor-not-allowed' 
                  : 'cursor-pointer hover:bg-gray-50'
              ]"
            >
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ medicine.name }}</div>
                  <div class="text-sm text-gray-600">{{ medicine.type }}</div>
                  <div class="text-sm text-gray-500">{{ medicine.description }}</div>
                  <div class="text-xs text-blue-600 mt-1">
                    Dosis tersedia: {{ medicine.dosages.join(', ') }}
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-medium text-gray-900">
                    Rp {{ medicine.price.toLocaleString('id-ID') }}
                  </div>
                  <div :class="[
                    'text-xs font-medium',
                    medicine.stock > 10 ? 'text-green-600' : 
                    medicine.stock > 0 ? 'text-yellow-600' : 'text-red-600'
                  ]">
                    <span class="inline-flex items-center">
                      <span :class="[
                        'w-2 h-2 rounded-full mr-1',
                        medicine.stock > 10 ? 'bg-green-500' : 
                        medicine.stock > 0 ? 'bg-yellow-500' : 'bg-red-500'
                      ]"></span>
                      Stok: {{ medicine.stock }}
                      <span v-if="medicine.stock === 0" class="ml-1">(Habis)</span>
                      <span v-else-if="medicine.stock <= 10" class="ml-1">(Terbatas)</span>
                    </span>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-if="searchResults.length === 0" class="p-3 text-gray-500 text-center">
              Tidak ada obat ditemukan
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Prescribed Medicines List -->
    <div v-if="prescribedMedicines.length > 0" class="space-y-4">
      <Label class="text-sm font-semibold text-gray-800">Obat yang Diresepkan:</Label>
      
      <div class="space-y-3">
        <Card v-for="(medicine, index) in prescribedMedicines" :key="`${medicine.medicine_id}-${index}`">
          <CardContent class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
              <!-- Medicine Name -->
              <div class="md:col-span-1">
                <Label class="text-xs text-gray-600">Nama Obat</Label>
                <div class="font-medium text-gray-900 text-sm">{{ medicine.medicine_name }}</div>
                <div class="text-xs text-gray-500">
                  Rp {{ medicine.price.toLocaleString('id-ID') }} / unit
                </div>
              </div>
              
              <!-- Dosage -->
              <div class="md:col-span-1">
                <Label :for="`dosage-${index}`" class="text-xs text-gray-600">Dosis</Label>
                <select
                  :id="`dosage-${index}`"
                  :value="medicine.dosage"
                  @change="updateMedicine(index, 'dosage', ($event.target as HTMLSelectElement).value)"
                  class="w-full mt-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required
                >
                  <option value="">Pilih dosis</option>
                  <option 
                    v-for="dosage in (medicineDetails.get(medicine.medicine_id)?.dosages || [])" 
                    :key="dosage" 
                    :value="dosage"
                  >
                    {{ dosage }}
                  </option>
                </select>
              </div>
              
              <!-- Amount -->
              <div class="md:col-span-1">
                <Label :for="`amount-${index}`" class="text-xs text-gray-600">Jumlah</Label>
                <Input
                  :id="`amount-${index}`"
                  type="number"
                  v-model="prescribedMedicines[index].amount"
                  @focus="handleAmountFocus(index)"
                  @blur="handleAmountCommit(index)"
                  @change="handleAmountCommit(index)"
                  @keydown.enter="handleAmountCommit(index)"
                  min="1"
                  :class="[
                    'mt-1',
                    editingField === `amount-${index}` ? 'ring-2 ring-blue-500 border-blue-500' : ''
                  ]"
                  required
                />
                <div class="text-xs mt-1">
                  <span :class="[
                    'font-medium',
                    (medicineDetails.get(medicine.medicine_id)?.stock || 0) <= medicine.amount
                      ? 'text-red-600' 
                      : (medicineDetails.get(medicine.medicine_id)?.stock || 0) <= 10 
                        ? 'text-yellow-600' 
                        : 'text-gray-500'
                  ]">
                    Stok: {{ medicineDetails.get(medicine.medicine_id)?.stock || 0 }}
                    <span v-if="(medicineDetails.get(medicine.medicine_id)?.stock || 0) <= medicine.amount" class="ml-1">⚠️</span>
                  </span>
                </div>
              </div>
              
              <!-- Remove Button -->
              <div class="md:col-span-1 flex justify-end items-center h-full">
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  @click="removeMedicine(index)"
                  class="text-red-600 border-red-300 hover:bg-red-50"
                >
                  Hapus
                </Button>
              </div>
            </div>
            
            <!-- Total Price -->
            <div class="mt-3 pt-3 border-t border-gray-200">
              <div class="text-right">
                <span class="text-sm text-gray-600">Total: </span>
                <span class="font-medium text-gray-900">
                  Rp {{ (medicine.price * medicine.amount).toLocaleString('id-ID') }}
                </span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
      
      <!-- Total for all medicines -->
      <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex justify-between items-center">
          <span class="font-medium text-blue-800">Total Biaya Obat:</span>
          <span class="text-lg font-bold text-blue-900">
            Rp {{ prescribedMedicines.reduce((total, medicine) => total + (medicine.price * medicine.amount), 0).toLocaleString('id-ID') }}
          </span>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="text-center py-8 text-gray-500">
      <div class="text-sm">Belum ada obat yang diresepkan</div>
      <div class="text-xs mt-1">Gunakan kolom pencarian di atas untuk menambahkan obat</div>
    </div>
  </div>
</template> 