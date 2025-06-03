<script setup lang="ts">
import { useVModel } from '@vueuse/core';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps<{
    modelValue?: string | number;
    id: string;
    type?: string;
    placeholder?: string;
    leadingIcon?: string;
    trailingIcon?: string;
    inputClass?: HTMLAttributes['class'];
    containerClass?: HTMLAttributes['class'];
    trailingIconCursorPointer?: boolean;
    error?: boolean; // To style the input if there's an error
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', payload: string | number): void;
    (e: 'trailingIconClick'): void;
}>();

// Create a writable computed ref that syncs with the modelValue prop and emits updates
// This effectively makes AuthInputWithIcon compatible with v-model from its parent,
// and allows it to use v-model on the child Input component.
const internalModelValue = useVModel(props, 'modelValue', emit, {
    passive: true, // Use passive mode for better performance if immediate state change is not critical
});

const handleTrailingIconClick = () => {
    emit('trailingIconClick');
};
</script>

<template>
    <div :class="cn('relative flex items-center bg-[rgba(64,91,230,0.06)] rounded-[8px] px-2.5 py-1', props.containerClass)">
        <img
            v-if="props.leadingIcon"
            :src="props.leadingIcon"
            alt=""
            class="h-4 w-4 mr-2 text-gray-500 shrink-0"
        />
        <Input
            :id="props.id"
            :type="props.type"
            v-model="internalModelValue"
            :placeholder="props.placeholder"
            :class="cn(
                'flex-grow bg-transparent border-none focus:ring-0 p-0 text-sm  placeholder-[#6F7985] shadow-none focus-visible:ring-0 tracking-(--tracking-body) rounded-[8px]',
                props.inputClass,
            )"
            v-bind="$attrs"
        />
        <img
            v-if="props.trailingIcon"
            :src="props.trailingIcon"
            alt=""
            @click="handleTrailingIconClick"
            :class="cn('h-4 w-4 ml-2 text-gray-500 shrink-0', { 'cursor-pointer': props.trailingIconCursorPointer })"
        />
    </div>
</template>