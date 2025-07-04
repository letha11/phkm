<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import AuthInputWithIcon from '@/components/auth/AuthInputWithIcon.vue';
import { ref } from 'vue';
import InfoIcon from '@/components/icons/InfoIcon.vue';

defineProps<{
    status?: string;
}>();

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

// Define input classes as a variable
const figmaInputClasses = "font-['Inter'] tracking-[-0.04em] text-[10px]";

const submit = () => {
    form.post('/login', {
        onFinish: () => {
            form.reset('password');
            showPassword.value = false;
        },
    });
};

const submitWithThisRole = (role: string) => {
    form.username = role;
    form.password = 'password';

    submit();
};

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <Head title="Log in" />
    <div class="min-h-screen flex items-center flex-col md:flex-row justify-center gap-4  p-4">
        <div class="w-full max-w-[320px] rounded-[10px] p-[28px_16px]">
        </div>
        <div class="w-full max-w-[400px] bg-card rounded-[10px] p-[28px_16px] shadow-xl flex flex-col items-center">
            <h1 class="text-[25px] font-bold font-[Epilogue] text-card-foreground text-center mb-[18px] tracking-(--tracking-title)">
                Login
            </h1>

            <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="w-full flex flex-col gap-[9px]">
                <div class="grid gap-1">
                    <AuthInputWithIcon
                        id="username"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="username"
                        v-model="form.username"
                        placeholder="Username"
                        leadingIcon="/assets/figma/login/avatar_icon.svg"
                        :error="!!form.errors.username"
                        :inputClass="figmaInputClasses"
                    />
                    <InputError :message="form.errors.username" class="mt-1" />
                </div>

                <div class="grid gap-1">
                    <AuthInputWithIcon
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="Password"
                        leadingIcon="/assets/icons/lock.svg"
                        :trailingIcon="showPassword ? '/assets/icons/eye-open.svg' : '/assets/icons/eye-closed.svg'"
                        @trailingIconClick="togglePasswordVisibility"
                        :error="!!form.errors.password"
                        :inputClass="figmaInputClasses"
                        :trailingIconCursorPointer="true"
                    />
                    <InputError :message="form.errors.password" class="mt-1" />
                </div>

                <Button
                    type="submit"
                    class="mt-[18px] w-full bg-primary hover:bg-primary/90 text-primary-foreground font-bold font-['Inter'] text-[13px] py-5 rounded-[8px] flex items-center justify-center tracking-(--tracking-body)"
                    :tabindex="4"
                    :disabled="form.processing"
                >
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                    Masuk
                </Button>
            </form>
        </div>
        <div class="w-full max-w-[320px] bg-card rounded-[10px] p-[28px_16px] shadow-xl flex flex-col items-center">
            <div class="w-full flex items-center mb-2">
                <InfoIcon class="w-6 h-6 mb-1 mr-2 text-amber-400" />
                <h1 class="text-[25px] font-bold font-[Epilogue] text-card-foreground tracking-(--tracking-title)">
                    Demo Accounts
                </h1>
            </div>
            <div class="w-full mb-2">
                <div class="w-full flex items-center mb-2">
                    <h2 class="text-[13px] font-bold font-[Inter] text-card-foreground tracking-(--tracking-body)">
                        Admin Role
                    </h2>
                </div>
                <div class="w-full bg-amber-50 border border-amber-100 rounded-lg p-2">
                    <p class="text-xs text-gray-700 font-['Inter'] line-clamp-2 leading-relaxed">
                        Username: <span class="font-bold">admin</span>
                    </p>
                    <p class="text-xs text-gray-700 font-['Inter'] line-clamp-2 leading-relaxed">
                        Password: <span class="font-bold">password</span>
                    </p>
                    <button 
                        class="w-full bg-primary mt-2 text-white text-xs font-bold font-['Inter'] rounded-lg py-2 px-3 text-center hover:from-blue-600 hover:to-indigo-700 cursor-pointer transition-all duration-300 transform shadow-md"
                        @click="submitWithThisRole('admin')"
                    >
                        Use Account
                    </button>

                </div>
            </div>
            <div class="w-full mb-2">
                <div class="w-full flex items-center mb-2">
                    <h2 class="text-[13px] font-bold font-[Inter] text-card-foreground tracking-(--tracking-body)">
                        Apoteker Role
                    </h2>
                </div>
                <div class="w-full bg-amber-50 border border-amber-100 rounded-lg p-2">
                    <p class="text-xs text-gray-700 font-['Inter'] line-clamp-2 leading-relaxed">
                        Username: <span class="font-bold">apoteker</span>
                    </p>
                    <p class="text-xs text-gray-700 font-['Inter'] line-clamp-2 leading-relaxed">
                        Password: <span class="font-bold">password</span>
                    </p>
                    <button 
                        class="w-full bg-primary mt-2 text-white text-xs font-bold font-['Inter'] rounded-lg py-2 px-3 text-center hover:from-blue-600 hover:to-indigo-700 cursor-pointer transition-all duration-300 transform shadow-md"
                        @click="submitWithThisRole('apoteker')"
                    >
                        Use Account
                    </button>
                </div>
            </div>
            <div class="w-full mb-2">
                <div class="w-full flex items-center mb-2">
                    <h2 class="text-[13px] font-bold font-[Inter] text-card-foreground tracking-(--tracking-body)">
                        Dokter Role
                    </h2>
                </div>
                <div class="w-full bg-amber-50 border border-amber-100 rounded-lg p-2">
                    <p class="text-xs text-gray-700 font-['Inter'] line-clamp-2 leading-relaxed">
                        Username: <span class="font-bold">doctor</span>
                    </p>
                    <p class="text-xs text-gray-700 font-['Inter'] line-clamp-2 leading-relaxed">
                        Password: <span class="font-bold">password</span>
                    </p>
                    <button 
                        class="w-full bg-primary mt-2 text-white text-xs font-bold font-['Inter'] rounded-lg py-2 px-3 text-center hover:from-blue-600 hover:to-indigo-700 cursor-pointer transition-all duration-300 transform shadow-md"
                        @click="submitWithThisRole('doctor')"
                    >
                        Use Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

