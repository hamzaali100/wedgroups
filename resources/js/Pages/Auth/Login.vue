<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const success = computed(() => page.props.flash?.success)

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

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
        <p v-if="success" class="notice" role="status">{{ success }}</p>

        <form class="panel" @submit.prevent="submit">
            <p class="eyebrow">Account</p>
            <h1>Log in</h1>

            <label class="field">
                <span>Email</span>
                <input v-model="form.email" type="email" name="email" autocomplete="username" required>
                <small v-if="form.errors.email">{{ form.errors.email }}</small>
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
        </form>
    </main>
</template>
