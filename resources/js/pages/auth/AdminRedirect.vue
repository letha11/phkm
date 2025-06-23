<script setup lang="ts">
import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';

// Immediately redirect to admin panel using full page redirect
onMounted(() => {
    window.location.href = '/admin';
});
</script>

<template>
    <Head title="Redirecting..." />
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-600">Redirecting to admin panel...</p>
        </div>
    </div>
</template> 