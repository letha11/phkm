<script setup lang="ts">
import type { Patient } from '@/types/patient.d';
import type { PaginationData, FilterData, StatsData } from '@/types';
import LogoutButton from '@/components/ui/button/LogoutButton.vue';
import LogoutDialog from '@/components/ui/dialog/LogoutDialog.vue';
import { ref, computed, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { showToast } from '@/lib/utils';

// Modal state
const showLogoutModal = ref(false);

// Get initial data from backend
const page = usePage();
const patientsData = ref<Patient[]>((page.props.patients as Patient[]) || []);
const uniqueDoctors = ref<string[]>((page.props.unique_doctors as string[]) || []);

// Pagination state from backend
const pagination = ref<PaginationData>((page.props.pagination as PaginationData) || {
  current_page: 1,
  last_page: 1,
  per_page: 12,
  total: 0,
  from: 0,
  to: 0,
});

// Filter states
const filters = ref<FilterData>((page.props.filters as FilterData) || {
  search: '',
  status: 'all',
  date_range: 'all',
  doctor: 'all',
  per_page: 12,
});

// Stats from backend
const stats = ref({
  total: (page.props.stats as { total: StatsData; filtered: StatsData })?.total || { total: 0, completed: 0, pending: 0 },
  filtered: (page.props.stats as { total: StatsData; filtered: StatsData })?.filtered || { total: 0, completed: 0, pending: 0 },
});

// UI states
const showFilters = ref(false);
const isLoading = ref(false);
const itemsPerPageOptions = [8, 12, 16, 24];

// Debounced search
const searchDebounce = ref<number | null>(null);

// Update data when page props change
watch(() => page.props, (newProps) => {
  patientsData.value = (newProps.patients as Patient[]) || [];
  uniqueDoctors.value = (newProps.unique_doctors as string[]) || [];
  pagination.value = (newProps.pagination as PaginationData) || pagination.value;
  filters.value = (newProps.filters as FilterData) || filters.value;
  stats.value = {
    total: (newProps.stats as { total: StatsData; filtered: StatsData })?.total || stats.value.total,
    filtered: (newProps.stats as { total: StatsData; filtered: StatsData })?.filtered || stats.value.filtered,
  };
  isLoading.value = false;
}, { deep: true });

// Navigate with filters using Inertia router
const navigateWithFilters = (resetPage = false) => {
  if (isLoading.value) return;
  
  isLoading.value = true;
  
  const params: Record<string, any> = {
    search: filters.value.search,
    status: filters.value.status,
    date_range: filters.value.date_range,
    doctor: filters.value.doctor,
    per_page: filters.value.per_page,
  };

  if (!resetPage) {
    params.page = pagination.value.current_page;
  }

  // Remove empty filters to keep URL clean
  Object.keys(params).forEach(key => {
    if (params[key] === '' || params[key] === 'all' || (key === 'page' && params[key] === 1)) {
      delete params[key];
    }
  });

  router.visit(route('dashboard.pharmacist'), {
    data: params,
    preserveState: true,
    preserveScroll: true,
    only: ['patients', 'pagination', 'filters', 'stats'],
    onStart: () => {
      isLoading.value = true;
    },
    onFinish: () => {
      isLoading.value = false;
    },
  });
};

// Watch for filter changes with debouncing for search
watch(() => filters.value.search, (newSearch) => {
  if (searchDebounce.value) {
    clearTimeout(searchDebounce.value);
  }
  
  searchDebounce.value = setTimeout(() => {
    navigateWithFilters(true);
  }, 500); // 500ms debounce
});

// Watch for other filter changes
watch([
  () => filters.value.status,
  () => filters.value.date_range,
  () => filters.value.doctor,
], () => {
  navigateWithFilters(true);
});

// Watch for per page changes
watch(() => filters.value.per_page, () => {
  navigateWithFilters(true);
});

// Helper function to get status indicator color
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

// Helper function to get status background gradient
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

// Logout handlers
const handleLogoutClick = () => {
  showLogoutModal.value = true;
};

const confirmLogout = () => {
  showLogoutModal.value = false;
  router.post('/logout');
};

const closeModalOnOverlay = (event: MouseEvent) => {
  if (event.target === event.currentTarget) {
    showLogoutModal.value = false;
  }
};

// Pagination helpers
const goToPage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page && !isLoading.value) {
    pagination.value.current_page = page;
    navigateWithFilters();
    // Scroll to top of patient cards
    document.querySelector('.patient-cards-section')?.scrollIntoView({ behavior: 'smooth' });
  }
};

const nextPage = () => {
  if (pagination.value.current_page < pagination.value.last_page) {
    goToPage(pagination.value.current_page + 1);
  }
};

const prevPage = () => {
  if (pagination.value.current_page > 1) {
    goToPage(pagination.value.current_page - 1);
  }
};

// Generate page numbers for pagination
const visiblePages = computed(() => {
  const pages = [];
  const total = pagination.value.last_page;
  const current = pagination.value.current_page;
  
  if (total <= 7) {
    // Show all pages if 7 or fewer
    for (let i = 1; i <= total; i++) {
      pages.push(i);
    }
  } else {
    // Always show first page
    pages.push(1);
    
    if (current > 4) {
      pages.push('...');
    }
    
    // Show pages around current page
    const start = Math.max(2, current - 1);
    const end = Math.min(total - 1, current + 1);
    
    for (let i = start; i <= end; i++) {
      if (i !== 1 && i !== total) {
        pages.push(i);
      }
    }
    
    if (current < total - 3) {
      pages.push('...');
    }
    
    // Always show last page
    if (total > 1) {
      pages.push(total);
    }
  }
  
  return pages;
});

// Clear all filters and reset pagination
const clearFilters = () => {
  filters.value = {
    search: '',
    status: 'all',
    date_range: 'all',
    doctor: 'all',
    per_page: 12,
  };
  navigateWithFilters(true);
};

// Check if any filters are active
const hasActiveFilters = computed(() => {
  return filters.value.search !== '' || 
         filters.value.status !== 'all' || 
         filters.value.date_range !== 'all' || 
         filters.value.doctor !== 'all';
});

// Change items per page
const changeItemsPerPage = (newItemsPerPage: number) => {
  filters.value.per_page = newItemsPerPage;
};

// Pagination info
const paginationInfo = computed(() => {
  return {
    start: pagination.value.from || 0,
    end: pagination.value.to || 0,
    total: pagination.value.total || 0,
  };
});
</script>

<template>
  <!-- Main Dashboard Container -->
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">

    <!-- Main Content Area -->
    <div class="flex justify-center items-start py-8 px-4 gap-6">

      <!-- Left Panel - Patient List -->
      <div
        class="w-full max-w-6xl bg-white rounded-xl flex flex-col gap-5 px-8 py-8 shadow-lg border border-gray-100">
        <!-- Header Section -->
        <div class="w-full flex flex-col gap-3">
          <div class="w-full flex justify-between items-center">
            <!-- Welcome Section -->
            <div class="flex flex-col">
              <h1 class="text-3xl font-bold text-gray-800 tracking-tight font-['Epilogue']">
                Dashboard Apoteker
              </h1>
              <p class="text-base text-gray-600 font-['Inter'] mt-1">
                Kelola resep dan pantau status pasien
              </p>
            </div>
            <LogoutButton @logout="handleLogoutClick" />
          </div>
          <!-- Header Divider -->
          <div class="w-full h-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full"></div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-3">
          <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-blue-100 text-xs font-medium">Total Pasien</p>
                <p class="text-2xl font-bold">{{ stats.total.total }}</p>
              </div>
              <div class="w-10 h-10 bg-blue-400 rounded-lg flex items-center justify-center">
                <img src="/assets/icons/avatar.svg" alt="Patient" class="w-5 h-5">
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-emerald-100 text-xs font-medium">Selesai</p>
                <p class="text-2xl font-bold">{{ stats.total.completed }}</p>
              </div>
              <div class="w-10 h-10 bg-emerald-400 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-amber-100 text-xs font-medium">Menunggu</p>
                <p class="text-2xl font-bold">{{ stats.total.pending }}</p>
              </div>
              <div class="w-10 h-10 bg-amber-400 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                    clip-rule="evenodd" />
                </svg>
              </div>
            </div>
          </div>
          
          <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-red-100 text-xs font-medium">Gagal</p>
                <p class="text-2xl font-bold">{{ stats.total.failed }}</p>
              </div>
              <div class="w-10 h-10 bg-red-400 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters Section -->
        <div class="w-full bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4 border border-gray-200">
          <!-- Filter Header -->
          <div :class="['flex items-center justify-between', showFilters ? 'mb-4' : '']">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 bg-gradient-to-br from-gray-500 to-gray-600 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/>
                </svg>
              </div>
              <h3 class="text-lg font-bold text-gray-800 font-['Epilogue']">
                Filter Data
              </h3>
            </div>
            
            <div class="flex items-center gap-2">
              <button 
                v-if="hasActiveFilters"
                @click="clearFilters"
                :disabled="isLoading"
                :class="[
                  'px-3 py-1.5 rounded-lg transition-all duration-300 text-xs font-medium',
                  isLoading 
                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                    : 'bg-red-100 text-red-700 hover:bg-red-200'
                ]"
              >
                {{ isLoading ? 'Memuat...' : 'Reset Filter' }}
              </button>
              <button 
                @click="showFilters = !showFilters"
                class="cursor-pointer px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-all duration-300 text-xs font-medium flex items-center gap-1"
              >
                <svg class="w-3 h-3" :class="{'rotate-180': showFilters}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
                {{ showFilters ? 'Sembunyikan' : 'Tampilkan' }}
              </button>
            </div>
          </div>

          <!-- Filter Controls -->
            <div v-if="showFilters" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <!-- Search Filter -->
              <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-gray-700 font-['Inter']">
                  Cari Nama Pasien
                </label>
                <div class="relative">
                  <input 
                    v-model="filters.search"
                    type="text" 
                    placeholder="Ketik nama pasien..."
                    :disabled="isLoading"
                    class="w-full pl-8 pr-3 py-[0.5em] bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm font-['Inter'] disabled:bg-gray-100 disabled:cursor-not-allowed"
                  >
                  <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Status Filter -->
              <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-gray-700 font-['Inter']">
                  Status
                </label>
                <select 
                  v-model="filters.status"
                  :disabled="isLoading"
                  class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm font-['Inter'] disabled:bg-gray-100 disabled:cursor-not-allowed"
                >
                  <option value="all">Semua Status</option>
                  <option value="waiting">Menunggu</option>
                  <option value="success">Selesai</option>
                  <option value="failed">Gagal</option>
                </select>
              </div>

              <!-- Date Range Filter -->
              <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-gray-700 font-['Inter']">
                  Rentang Waktu
                </label>
                <select 
                  v-model="filters.date_range"
                  :disabled="isLoading"
                  class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm font-['Inter'] disabled:bg-gray-100 disabled:cursor-not-allowed"
                >
                  <option value="all">Semua Waktu</option>
                  <option value="today">Hari Ini</option>
                  <option value="week">7 Hari Terakhir</option>
                  <option value="month">30 Hari Terakhir</option>
                </select>
              </div>

              <!-- Doctor Filter -->
              <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-gray-700 font-['Inter']">
                  Dokter
                </label>
                <select 
                  v-model="filters.doctor"
                  :disabled="isLoading"
                  class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm font-['Inter'] disabled:bg-gray-100 disabled:cursor-not-allowed"
                >
                  <option value="all">Semua Dokter</option>
                  <option v-for="doctor in uniqueDoctors" :key="doctor" :value="doctor">
                    {{ doctor }}
                  </option>
                </select>
              </div>
            </div>
        </div>

        <!-- Patient Cards Grid -->
        <div class="w-full patient-cards-section">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800 font-['Epilogue']">Daftar Pasien</h2>
            <div class="flex items-center gap-3">
              <!-- Items per page selector -->
              <div class="flex items-center gap-2">
                <span class="text-xs text-gray-600 font-['Inter']">Tampilkan:</span>
                <select 
                  v-model="filters.per_page" 
                  @change="changeItemsPerPage(filters.per_page)"
                  class="px-2 py-1 bg-white border border-gray-300 rounded-md text-xs font-['Inter'] focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option v-for="option in itemsPerPageOptions" :key="option" :value="option">
                    {{ option }}
                  </option>
                </select>
              </div>
              
              <!-- Current page info -->
              <div v-if="stats.filtered.total > 0" class="text-xs text-gray-600 font-['Inter']">
                {{ paginationInfo.start }}-{{ paginationInfo.end }} dari {{ paginationInfo.total }}
              </div>
            </div>
          </div>
          
          <!-- Loading State -->
          <div v-if="isLoading" class="text-center py-8">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3 animate-pulse">
              <svg class="w-8 h-8 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-600 font-['Epilogue'] mb-1">
              Memuat Data...
            </h3>
            <p class="text-gray-500 font-['Inter'] text-sm">
              Sedang mengambil data dari server
            </p>
          </div>
          
          <!-- No Results Message -->
          <div v-else-if="patientsData.length === 0" class="text-center py-8">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
              <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1H7a1 1 0 00-1 1v1m7 0h2a1 1 0 011 1v4H8V6a1 1 0 011-1h2z"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-600 font-['Epilogue'] mb-1">
              Tidak Ada Data
            </h3>
            <p class="text-gray-500 font-['Inter'] text-sm">
              Tidak ditemukan pasien yang sesuai dengan filter yang dipilih
            </p>
            <button 
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-300 text-sm"
            >
              Reset Filter
            </button>
          </div>

          <!-- Patient Cards -->
          <div v-else class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
              <div v-for="patient in patientsData" :key="patient.id"
                :class="['bg-white border border-gray-200 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 flex flex-col gap-4 p-4 group cursor-pointer justify-between hover:-translate-y-[0.3em]',
                         { 'opacity-50 pointer-events-none': isLoading }]">
                
                  <a :href="route('dashboard.pharmacist.patient', patient.id)">
                <div class="flex flex-col gap-4">
                  <!-- Patient Header with Status and Time -->
                  <div class="flex justify-between items-center gap-2">
                    <!-- Status Badge -->
                    <div
                      :class="`bg-gradient-to-r ${getStatusGradient(patient.status)} px-2 py-1 rounded-full border flex items-center gap-1.5`">
                      <div :class="getStatusColor(patient.status)" class="w-2 h-2 rounded-full"></div>
                      <span class="text-xs font-semibold text-gray-700">{{ getStatusText(patient.status) }}</span>
                    </div>
                    <!-- Time Ago -->
                    <p class="text-xs font-medium text-gray-500 font-['Inter']">
                      {{ patient.timeAgo }}
                    </p>
                  </div>

                  <!-- Patient Info -->
                  <div class="flex flex-col gap-1.5">
                    <h3
                      class="text-lg font-bold text-gray-800 font-['Epilogue'] group-hover:text-blue-600 transition-colors">
                      {{ patient.name }}
                    </h3>
                    <div class="flex items-center gap-1.5 text-gray-600">
                      <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                          d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                          clip-rule="evenodd" />
                      </svg>
                      <p class="text-xs font-medium font-['Inter']">
                        {{ patient.birthDate }}
                      </p>
                    </div>
                    <!-- Doctor Name -->
                    <div class="flex items-center gap-1.5 text-gray-600">
                      <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                      </svg>
                      <p class="text-xs font-medium font-['Inter']">
                        Dr. {{ patient.doctor_name }}
                      </p>
                    </div>
                  </div>

                  <!-- Divider -->
                  <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>

                  <!-- Complaint Section -->
                  <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-1.5">
                      <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                          d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                          clip-rule="evenodd" />
                      </svg>
                      <p class="text-xs font-bold text-gray-800 font-['Inter']">
                        Keluhan
                      </p>
                    </div>
                    <div class="bg-red-50 border border-red-100 rounded-lg p-2">
                      <p class="text-xs text-gray-700 font-['Inter'] line-clamp-2 leading-relaxed">
                        {{ patient.complaint }}
                      </p>
                    </div>
                  </div>

                  <!-- Medication Section -->
                  <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-1.5">
                      <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path
                          d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                      <p class="text-xs font-bold text-gray-800 font-['Inter']">
                        Resep Obat
                      </p>
                    </div>
                    <div class="bg-green-50 border border-green-100 rounded-lg p-2">
                      <p class="text-xs text-gray-700 font-['Inter'] whitespace-pre-line leading-relaxed">
                        {{ patient.medications }}
                      </p>
                    </div>
                  </div>
                </div>

                  <!-- Detail Button -->
                  <!-- <a :href="route('dashboard.pharmacist.patient', patient.id)"
                    class="w-full bg-primary text-white text-xs font-bold font-['Inter'] rounded-lg py-3 text-center hover:from-blue-600 hover:to-indigo-700 cursor-pointer transition-all duration-300 transform shadow-md hover:shadow-lg">
                    <div class="flex items-center justify-center gap-1.5">
                      <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        <path fill-rule="evenodd"
                          d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                          clip-rule="evenodd" />
                      </svg>
                      Lihat Detail
                    </div>
                  </a> -->
            </a>
              </div>

            </div>

            <!-- Pagination Component -->
            <div v-if="pagination.last_page > 1" class="p-4">
              <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <!-- Pagination Info -->
                <div class="text-xs text-gray-600 font-['Inter']">
                  Halaman {{ pagination.current_page }} dari {{ pagination.last_page }} 
                  <span class="hidden sm:inline">({{ paginationInfo.start }}-{{ paginationInfo.end }} dari {{ paginationInfo.total }} pasien)</span>
                </div>

                <!-- Pagination Controls -->
                <div class="flex items-center gap-1">
                  <!-- Previous Button -->
                  <button 
                    @click="prevPage"
                    :disabled="pagination.current_page === 1 || isLoading"
                    :class="[
                      'px-2 py-1.5 rounded-md text-xs font-medium transition-all duration-300',
                      pagination.current_page === 1 || isLoading
                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                        : 'bg-primary text-white hover:bg-blue-600 shadow-md hover:shadow-lg cursor-pointer'
                    ]"
                  >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                  </button>

                  <!-- Page Numbers -->
                  <div class="flex items-center gap-0.5">
                    <template v-for="(pageNumber, index) in visiblePages" :key="index">
                      <button
                        v-if="pageNumber !== '...'"
                        @click="goToPage(pageNumber as number)"
                        :disabled="isLoading"
                        :class="[
                          'px-2 py-1.5 rounded-md text-xs font-medium transition-all duration-300',
                          pagination.current_page === pageNumber
                            ? 'bg-primary text-white shadow-lg'
                            : isLoading
                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            : 'bg-gray-50 text-gray-700 hover:bg-gray-100 hover:text-blue-600 cursor-pointer'
                        ]"
                      >
                        {{ pageNumber }}
                      </button>
                      <span v-else class="px-1 py-1.5 text-gray-400 text-xs">...</span>
                    </template>
                  </div>

                  <!-- Next Button -->
                  <button 
                    @click="nextPage"
                    :disabled="pagination.current_page === pagination.last_page || isLoading"
                    :class="[
                      'px-2 py-1.5 rounded-md text-xs font-medium transition-all duration-300',
                      pagination.current_page === pagination.last_page || isLoading
                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                        : 'bg-primary text-white hover:bg-blue-600 shadow-md hover:shadow-lg cursor-pointer'
                    ]"
                  >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Quick Jump to First/Last Pages (for large datasets) -->
              <div v-if="pagination.last_page > 10" class="flex items-center justify-center gap-3 mt-3 pt-3 border-t border-gray-100">
                <button 
                  @click="goToPage(1)"
                  :disabled="pagination.current_page === 1 || isLoading"
                  class="text-xs text-blue-600 hover:text-blue-800 disabled:text-gray-400 font-medium transition-colors cursor-pointer"
                >
                  Halaman Pertama
                </button>
                <span class="text-gray-300">|</span>
                <button 
                  @click="goToPage(pagination.last_page)"
                  :disabled="pagination.current_page === pagination.last_page || isLoading"
                  class="text-xs text-blue-600 hover:text-blue-800 disabled:text-gray-400 font-medium transition-colors cursor-pointer"
                >
                  Halaman Terakhir
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Panel - Information -->
      <div
        class="w-72 bg-white rounded-xl flex flex-col gap-4 px-6 py-6 shadow-lg border border-gray-100 sticky top-8">
        <!-- Information Header -->
        <div class="flex flex-col gap-3">
          <div class="flex items-center gap-2">
            <div
              class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 10-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                  clip-rule="evenodd" />
              </svg>
            </div>
            <h2 class="text-lg font-bold text-gray-800 font-['Epilogue']">
              Informasi
            </h2>
          </div>
          <!-- Header Divider -->
          <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>
        </div>

        <!-- Status Legend -->
        <div class="flex flex-col gap-3">
          <h3 class="text-base font-semibold text-gray-700 font-['Inter']">Status Legenda</h3>

          <!-- Payment Failed -->
          <div
            class="flex items-center gap-3 p-3 bg-gradient-to-r from-rose-50 to-red-100 border border-rose-200 rounded-lg">
            <div class="w-3 h-3 bg-rose-500 rounded-full shadow-sm"></div>
            <div class="flex-1">
              <p class="text-xs font-semibold text-gray-800 font-['Inter']">
                Gagal
              </p>
              <p class="text-xs text-gray-600 font-['Inter']">
                Perlu tindakan lebih lanjut
              </p>
            </div>
          </div>

          <!-- Waiting Payment -->
          <div
            class="flex items-center gap-3 p-3 bg-gradient-to-r from-amber-50 to-yellow-100 border border-amber-200 rounded-lg">
            <div class="w-3 h-3 bg-amber-400 rounded-full shadow-sm"></div>
            <div class="flex-1">
              <p class="text-xs font-semibold text-gray-800 font-['Inter']">
                Menunggu
              </p>
              <p class="text-xs text-gray-600 font-['Inter']">
                Sedang dalam proses
              </p>
            </div>
          </div>

          <!-- Payment Success -->
          <div
            class="flex items-center gap-3 p-3 bg-gradient-to-r from-emerald-50 to-green-100 border border-emerald-200 rounded-lg">
            <div class="w-3 h-3 bg-emerald-500 rounded-full shadow-sm"></div>
            <div class="flex-1">
              <p class="text-xs font-semibold text-gray-800 font-['Inter']">
                Selesai
              </p>
              <p class="text-xs text-gray-600 font-['Inter']">
                Transaksi selesai
              </p>
            </div>
          </div>
        </div>

        <!-- Filter Summary -->
        <div v-if="hasActiveFilters" class="mt-3">
          <h3 class="text-base font-semibold text-gray-700 font-['Inter'] mb-3">Filter Aktif</h3>
          <div class="flex flex-col gap-2">
            <div v-if="filters.search" class="flex items-center justify-between p-2 bg-blue-50 border border-blue-200 rounded-md">
              <span class="text-xs font-medium text-blue-800">Nama: {{ filters.search }}</span>
              <button 
                @click="filters.search = ''"
                :disabled="isLoading"
                class="text-blue-600 hover:text-blue-800 disabled:text-gray-400"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
            <div v-if="filters.status !== 'all'" class="flex items-center justify-between p-2 bg-green-50 border border-green-200 rounded-md">
              <span class="text-xs font-medium text-green-800">Status: {{ getStatusText(filters.status as Patient['status']) }}</span>
              <button 
                @click="filters.status = 'all'"
                :disabled="isLoading"
                class="text-green-600 hover:text-green-800 disabled:text-gray-400"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
            <div v-if="filters.date_range !== 'all'" class="flex items-center justify-between p-2 bg-purple-50 border border-purple-200 rounded-md">
              <span class="text-xs font-medium text-purple-800">
                Waktu: {{ 
                  filters.date_range === 'today' ? 'Hari Ini' :
                  filters.date_range === 'week' ? '7 Hari Terakhir' :
                  filters.date_range === 'month' ? '30 Hari Terakhir' : filters.date_range
                }}
              </span>
              <button 
                @click="filters.date_range = 'all'"
                :disabled="isLoading"
                class="text-purple-600 hover:text-purple-800 disabled:text-gray-400"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
            <div v-if="filters.doctor !== 'all'" class="flex items-center justify-between p-2 bg-orange-50 border border-orange-200 rounded-md">
              <span class="text-xs font-medium text-orange-800">Dokter: {{ filters.doctor }}</span>
              <button 
                @click="filters.doctor = 'all'"
                :disabled="isLoading"
                class="text-orange-600 hover:text-orange-800 disabled:text-gray-400"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-3">
          <h3 class="text-base font-semibold text-gray-700 font-['Inter'] mb-3">Aksi Cepat</h3>
          <div class="flex flex-col gap-2">
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              :disabled="isLoading"
              :class="[
                'w-full text-white text-xs font-semibold py-2.5 px-3 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg',
                isLoading 
                  ? 'bg-gray-400 cursor-not-allowed' 
                  : 'bg-primary text-white hover:bg-primary/90 cursor-pointer'
              ]"
            >
              {{ isLoading ? 'Memuat...' : 'Reset Semua Filter' }}
            </button>
            <button
              @click="showToast('Laporan Harian berhasil diunduh')"
              :disabled="isLoading"
              :class="[
                'w-full text-white text-xs font-semibold py-2.5 px-3 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg',
                isLoading 
                  ? 'bg-gray-400 cursor-not-allowed' 
                  : 'bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 cursor-pointer'
              ]"
            >
              Laporan Harian
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

<style scoped>
/* Custom line clamp utilities */
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
