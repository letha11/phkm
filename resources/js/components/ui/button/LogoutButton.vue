<script setup lang="ts">
// Define emits for the logout click event
const emit = defineEmits<{
  logout: []
}>();

const handleLogoutClick = () => {
  emit('logout');
}
</script>

<template>
  <h2 @click="handleLogoutClick" class="text-[12px] font-bold text-[#FF0000] tracking-(--tracking-body) font-['Epilogue'] cursor-pointer select-none hover:text-red-600 transition-colors">
    Logout
  </h2>
</template>