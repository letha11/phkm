<script setup lang="ts">
interface Props {
  status: 'pending' | 'success' | 'failed';
  prescriptionStatus?: string;
  paymentStatus?: string;
  size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md'
});

// Helper functions for status display
const getStatusColor = (status: Props['status']) => {
  switch (status) {
    case 'pending':
      return 'bg-amber-400';
    case 'success':
      return 'bg-emerald-500';
    case 'failed':
      return 'bg-rose-500';
    default:
      return 'bg-gray-400';
  }
};

const getStatusGradient = (status: Props['status']) => {
  switch (status) {
    case 'pending':
      return 'from-amber-50 to-yellow-100 border-amber-200'; 
    case 'success':
      return 'from-emerald-50 to-green-100 border-emerald-200'; 
    case 'failed':
      return 'from-rose-50 to-red-100 border-rose-200'; 
    default:
      return 'from-gray-50 to-gray-100 border-gray-200';
  }
};

const getStatusText = (status: Props['status']) => {
  switch (status) {
    case 'pending':
      return 'Menunggu';
    case 'success':
      return 'Selesai';
    case 'failed':
      return 'Gagal';
    default:
      return 'Unknown';
  }
};

const getSizeClasses = (size: Props['size']) => {
  switch (size) {
    case 'sm':
      return {
        container: 'px-3 py-1',
        dot: 'w-2 h-2',
        text: 'text-xs'
      };
    case 'md':
      return {
        container: 'px-4 py-2',
        dot: 'w-3 h-3',
        text: 'text-sm'
      };
    case 'lg':
      return {
        container: 'px-6 py-3',
        dot: 'w-4 h-4',
        text: 'text-base'
      };
    default:
      return {
        container: 'px-4 py-2',
        dot: 'w-3 h-3',
        text: 'text-sm'
      };
  }
};

const sizeClasses = getSizeClasses(props.size);

const getDetailedStatusText = () => {
  if (props.prescriptionStatus && props.paymentStatus) {
    const prescriptionText = getPrescriptionStatusText(props.prescriptionStatus);
    const paymentText = getPaymentStatusText(props.paymentStatus);
    
    if (props.prescriptionStatus === 'completed' && props.paymentStatus === 'success') {
      return 'Selesai & Lunas';
    } else if (props.prescriptionStatus === 'completed' && props.paymentStatus === 'waiting') {
      return 'Siap Bayar';
    } else {
      return prescriptionText;
    }
  }
  return getStatusText(props.status);
};

const getPrescriptionStatusText = (status: string) => {
  switch (status) {
    case 'accepted':
      return 'Diterima';
    case 'preparing':
      return 'Dibuat';
    case 'completed':
      return 'Selesai';
    default:
      return status;
  }
};

const getPaymentStatusText = (status: string) => {
  switch (status) {
    case 'waiting':
      return 'Menunggu Bayar';
    case 'success':
      return 'Lunas';
    case 'failed':
      return 'Gagal Bayar';
    default:
      return status;
  }
};
</script>

<template>
  <div :class="`bg-gradient-to-r ${getStatusGradient(status)} ${sizeClasses.container} rounded-full border flex items-center gap-3 shadow-sm backdrop-blur-sm`">
    <div :class="`${getStatusColor(status)} ${sizeClasses.dot} rounded-full shadow-sm`"></div>
    <span :class="`${sizeClasses.text} font-semibold text-gray-700 font-['Inter']`">
      {{ getDetailedStatusText() }}
    </span>
  </div>
</template> 