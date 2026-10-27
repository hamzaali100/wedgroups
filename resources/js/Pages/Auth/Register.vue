<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

function submit() {
    form.post('/register')
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
            <p class="eyebrow">Candidate</p>
            <h1>Create your account</h1>
            <p class="lede">Your profile stays private until you are in a group.</p>

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
        </form>
    </main>
</template>
