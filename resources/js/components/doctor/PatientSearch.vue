<script setup lang="ts">
import { ref, watch, onMounted, nextTick, computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { DatePicker as VDatePicker } from 'v-calendar';
import type { PatientData } from '@/types/medicine.d';

const props = defineProps<{
  modelValue: PatientData;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: PatientData): void;
}>();

const searchQuery = ref('');
const searchResults = ref<PatientData[]>([]);
const isSearching = ref(false);
const showResults = ref(false);
const searchTimeout = ref<number | null>(null);
const isUpdatingFromProps = ref(false);

const localPatient = ref<PatientData>({
  name: '',
  date_of_birth: '',
});

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
  if (!isUpdatingFromProps.value) {
    isUpdatingFromProps.value = true; 
    localPatient.value = { ...newValue };
    nextTick(() => {
      isUpdatingFromProps.value = false;
    })
  }
}, { immediate: true });

// Emit changes to parent
watch(localPatient, (newValue) => {
  if (!isUpdatingFromProps.value) {
    isUpdatingFromProps.value = true;
    emit('update:modelValue', { ...newValue });
    nextTick(() => {
      isUpdatingFromProps.value = false;
    })
  }
}, { deep: true });

const searchPatients = async () => {
  if (searchQuery.value.length < 2) {
    searchResults.value = [];
    showResults.value = false;
    return;
  }

  isSearching.value = true;
  
  try {
    const response = await fetch(`/doctor/patients/search?search=${encodeURIComponent(searchQuery.value)}`);
    const data = await response.json();
    searchResults.value = data;
    showResults.value = data.length > 0;
  } catch (error) {
    console.error('Error searching patients:', error);
    searchResults.value = [];
  } finally {
    isSearching.value = false;
  }
};

const onSearchInput = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value);
  }
  
  searchTimeout.value = setTimeout(() => {
    searchPatients();
  }, 300);
};

// Close search results when clicking outside
const searchContainer = ref<HTMLDivElement>();

// Computed property for date picker that doesn't cause reactive loops
const datePickerValue = computed({
  get: () => {
    if (localPatient.value.date_of_birth) {
      return new Date(localPatient.value.date_of_birth);
    }
    return null;
  },
  set: (newDate: Date | null) => {
    if (newDate) {
      const dateString = newDate.toISOString().split('T')[0];
      localPatient.value.date_of_birth = dateString;
      emit('update:modelValue', { ...localPatient.value });
    }
  }
});


onMounted(() => {
  document.addEventListener('click', (e) => {
    if (searchContainer.value && !searchContainer.value.contains(e.target as Node)) {
      showResults.value = false;
    }
  });
});
</script>

<template>
  <div class="space-y-4">
    <!-- New Patient Form -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="space-y-2">
        <Label for="patient-name">Nama Pasien *</Label>
        <Input
          id="patient-name"
          v-model="localPatient.name"
          placeholder="Masukkan nama lengkap pasien"
          required
        />
      </div>
      <div class="space-y-2">
        <Label for="patient-dob">Tanggal Lahir *</Label>
        <VDatePicker
          v-model="datePickerValue"
          mode="date"
          @dayclick=" (day, event) => { event.target.blur()  } "
          :max-date="new Date()"
          :model-config="{
            type: 'string',
            mask: 'YYYY-MM-DD',
          }"
        >
          <template #default="{ inputValue, inputEvents }">
            <Input
              id="patient-dob"
              :value="inputValue"
              v-on="inputEvents"
              placeholder="Pilih tanggal lahir"
              readonly
              required
              class="cursor-pointer"
            />
          </template>
        </VDatePicker>
      </div>
    </div>
  </div>
</template>