<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

defineProps({
    roles: {
        type: Object,
        required: true,
    },
})

const page = usePage()
const role = ref('')

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
})

const googleForm = useForm({
    role: '',
})

const roleError = computed(() => form.errors.role || googleForm.errors.role || page.props.errors?.role)
const googleError = computed(() => googleForm.errors.google || page.props.errors?.google)

function submit() {
    form.role = role.value
    form.post('/register')
}

function continueWithGoogle() {
    googleForm.clearErrors()
    googleForm.role = role.value
    googleForm.post('/auth/google')
}
</script>

<template>
    <Head title="Create an account" />

    <header class="site-header">
        <div class="header-inner">
            <Link href="/" class="logo">
                <span class="logo-mark">WG</span>
                WedGroups
            </Link>
            <Link href="/login" class="btn btn-ghost">Log in</Link>
        </div>
    </header>

    <main class="wrap app-section">
        <form class="panel" @submit.prevent="submit">
            <p class="eyebrow">Account</p>
            <h1>Create your account</h1>
            <p class="lede">Choose a role first. Google sign-in does not create an account without one.</p>

            <p v-if="googleError" class="notice-error" role="alert">{{ googleError }}</p>

            <label class="field">
                <span>I am joining as</span>
                <select v-model="role" name="role" required>
                    <option value="" disabled>Select a role</option>
                    <option v-for="(label, value) in roles" :key="value" :value="value">{{ label }}</option>
                </select>
                <small v-if="roleError">{{ roleError }}</small>
            </label>

            <label class="field">
                <span>Name</span>
                <input v-model="form.name" type="text" name="name" autocomplete="name" required maxlength="255">
                <small v-if="form.errors.name">{{ form.errors.name }}</small>
            </label>

            <label class="field">
                <span>Email</span>
                <input v-model="form.email" type="email" name="email" autocomplete="username" required>
                <small v-if="form.errors.email">{{ form.errors.email }}</small>
            </label>

            <label class="field">
                <span>Password</span>
                <input v-model="form.password" type="password" name="password" autocomplete="new-password" required>
                <small v-if="form.errors.password">{{ form.errors.password }}</small>
            </label>

            <label class="field">
                <span>Confirm password</span>
                <input v-model="form.password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
            </label>

            <button class="btn btn-primary" type="submit" :disabled="form.processing">Continue</button>

            <p class="auth-divider">or</p>

            <button class="btn btn-google" type="button" :disabled="googleForm.processing" @click="continueWithGoogle">
                <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true">
                    <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" />
                    <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" />
                    <path fill="#FBBC05" d="M3.964 10.706A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.706V4.962H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.038l3.007-2.332z" />
                    <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.962L3.964 7.294C4.672 5.163 6.656 3.58 9 3.58z" />
                </svg>
                Continue with Google
            </button>
        </form>
    </main>
</template>
