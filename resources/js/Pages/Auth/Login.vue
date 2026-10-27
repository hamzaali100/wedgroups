<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

defineProps({
    googleUrl: {
        type: String,
        required: true,
    },
})

const page = usePage()
const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const success = computed(() => page.props.flash?.success)
const emailError = computed(() => form.errors.email || page.props.errors?.email)
const googleError = computed(() => page.props.errors?.google)

function submit() {
    form.post('/login')
}
</script>

<template>
    <Head title="Log in" />

    <header class="site-header">
        <div class="header-inner">
            <Link href="/" class="logo">
                <span class="logo-mark">WG</span>
                WedGroups
            </Link>
            <Link href="/register" class="btn btn-primary">Create a profile</Link>
        </div>
    </header>

    <main class="wrap app-section">
        <form class="panel" @submit.prevent="submit">
            <p v-if="success" class="notice" role="status">{{ success }}</p>
            <p v-if="googleError" class="notice-error" role="alert">{{ googleError }}</p>

            <p class="eyebrow">Account</p>
            <h1>Log in</h1>

            <label class="field">
                <span>Email</span>
                <input v-model="form.email" type="email" name="email" autocomplete="username" required>
                <small v-if="emailError">{{ emailError }}</small>
            </label>

            <label class="field">
                <span>Password</span>
                <input v-model="form.password" type="password" name="password" autocomplete="current-password" required>
                <small v-if="form.errors.password">{{ form.errors.password }}</small>
            </label>

            <label class="check">
                <input v-model="form.remember" type="checkbox" name="remember">
                <span>Remember me</span>
            </label>

            <button class="btn btn-primary" type="submit" :disabled="form.processing">Log in</button>

            <p class="auth-divider">or</p>

            <a class="btn btn-google" :href="googleUrl">
                <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true">
                    <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" />
                    <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" />
                    <path fill="#FBBC05" d="M3.964 10.706A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.706V4.962H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.038l3.007-2.332z" />
                    <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.962L3.964 7.294C4.672 5.163 6.656 3.58 9 3.58z" />
                </svg>
                Continue with Google
            </a>
        </form>
    </main>
</template>
