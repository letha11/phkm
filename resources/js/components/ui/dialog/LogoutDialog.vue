<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3'
import WarningIcon from '@/components/icons/WarningIcon.vue';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    close: [];
    confirm: [];
}>();

const closeModalOnOverlay = (event: MouseEvent) => {
    if (event.target === event.currentTarget) {
        emit('close');
    }
};

const confirmLogout = () => {
    emit('confirm');
};
</script>

<template>
    <!-- Logout Confirmation Modal -->
    <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="props.show" @click="closeModalOnOverlay"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
            <Transition enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="transform scale-90 opacity-0" enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-90 opacity-0">
                <div v-if="props.show"
                    class="bg-white rounded-2xl shadow-2xl p-8 mx-4 max-w-md w-full border border-gray-100">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-center mb-6">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-red-100 to-rose-200 rounded-full flex items-center justify-center">
                            <WarningIcon class="w-8 h-8 text-red-600" />
                        </div>
                    </div>

                    <!-- Modal Content -->
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-gray-800 font-['Epilogue'] mb-3">
                            Konfirmasi Logout
                        </h3>
                        <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent mb-4">
                        </div>
                        <p class="text-base text-gray-600 font-['Inter'] leading-relaxed">
                            Apakah Anda yakin ingin keluar dari sistem? Anda akan diarahkan kembali ke halaman login.
                        </p>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex gap-4">
                        <button @click="closeModalOnOverlay"
                            class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 text-base font-semibold font-['Inter'] rounded-xl border border-gray-200 hover:bg-gray-200 transition-all duration-300 cursor-pointer">
                            Batal
                        </button>
                        <button @click="confirmLogout"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-rose-600 text-white text-base font-semibold font-['Inter'] rounded-xl hover:from-red-600 hover:to-rose-700 transition-all duration-300 cursor-pointer shadow-lg hover:shadow-xl">
                            Keluar
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>
