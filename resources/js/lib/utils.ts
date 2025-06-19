import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { useToast } from 'vue-toastification';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function formatCurrency(amount: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
}

export function showToast(message: string, type?: 'success' | 'error' | 'warning' | 'info') {
    const toast = useToast();
    switch (type) {
        case 'success':
            toast.success(message);
            break;
        case 'error':
            toast.error(message);
            break;
        case 'warning':
            toast.warning(message);
            break;
        case 'info':
            toast.info(message);
            break;
        default:
            toast(message);
            break;
    }
}