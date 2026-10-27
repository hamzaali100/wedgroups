<script setup>
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
    account: { type: Object, required: true },
})

const details = useForm({
    name: props.account.name,
    email: props.account.email,
})

const password = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const deactivate = useForm({
    current_password: '',
    confirm: false,
})

function saveDetails() {
    details.put('/account')
}

function savePassword() {
    password.put('/account/password', {
        onSuccess: () => password.reset(),
    })
}

function closeAccount() {
    deactivate.post('/account/deactivate')
}
</script>

<template>
    <Head title="Account" />

    <CandidateLayout>
        <form class="panel" @submit.prevent="saveDetails">
            <p class="eyebrow">Account</p>
            <h1>Account details</h1>

            <label class="field">
                <span>Name</span>
                <input v-model="details.name" type="text" required maxlength="255" autocomplete="name">
                <small v-if="details.errors.name">{{ details.errors.name }}</small>
            </label>

            <label class="field">
                <span>Email</span>
                <input v-model="details.email" type="email" required maxlength="255" autocomplete="username">
                <small v-if="details.errors.email">{{ details.errors.email }}</small>
            </label>

            <button class="btn btn-primary" type="submit" :disabled="details.processing">Save account</button>
        </form>

        <form class="panel section-gap" @submit.prevent="savePassword">
            <h2>Change password</h2>

            <label class="field">
                <span>Current password</span>
                <input v-model="password.current_password" type="password" required autocomplete="current-password">
                <small v-if="password.errors.current_password">{{ password.errors.current_password }}</small>
            </label>

            <label class="field">
                <span>New password</span>
                <input v-model="password.password" type="password" required autocomplete="new-password">
                <small v-if="password.errors.password">{{ password.errors.password }}</small>
            </label>

            <label class="field">
                <span>Confirm new password</span>
                <input v-model="password.password_confirmation" type="password" required autocomplete="new-password">
            </label>

            <button class="btn btn-primary" type="submit" :disabled="password.processing">Update password</button>
        </form>

        <form class="panel section-gap" @submit.prevent="closeAccount">
            <h2>Deactivate account</h2>
            <p class="meta">Deactivating signs you out. You will not be able to sign in again until an administrator reactivates the account.</p>

            <label class="field">
                <span>Current password</span>
                <input v-model="deactivate.current_password" type="password" required autocomplete="current-password">
                <small v-if="deactivate.errors.current_password">{{ deactivate.errors.current_password }}</small>
            </label>

            <label class="check">
                <input v-model="deactivate.confirm" type="checkbox">
                <span>I want to deactivate my account</span>
            </label>
            <small v-if="deactivate.errors.confirm">{{ deactivate.errors.confirm }}</small>

            <button class="btn btn-danger" type="submit" :disabled="deactivate.processing">Deactivate account</button>
        </form>
    </CandidateLayout>
</template>
