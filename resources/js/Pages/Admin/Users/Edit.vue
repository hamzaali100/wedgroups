<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    account: { type: Object, required: true },
})

const form = useForm({
    name: props.account.name,
    email: props.account.email,
})

function submit() {
    form.put(`/admin/users/${props.account.id}`)
}
</script>

<template>
    <Head title="Edit user" />

    <AdminLayout>
        <form class="panel" @submit.prevent="submit">
            <p class="eyebrow">User account</p>
            <h1>Edit {{ account.name }}</h1>
            <p class="lede">Role and status stay on the account page, so they cannot be changed from this form.</p>

            <div class="form-grid">
                <label class="field">
                    <span>Name</span>
                    <input v-model="form.name" name="name" required maxlength="255">
                    <small v-if="form.errors.name">{{ form.errors.name }}</small>
                </label>
                <label class="field">
                    <span>Email</span>
                    <input v-model="form.email" type="email" name="email" required maxlength="255">
                    <small v-if="form.errors.email">{{ form.errors.email }}</small>
                </label>
            </div>

            <div class="actions section-gap">
                <button class="btn btn-primary" type="submit" :disabled="form.processing">Save details</button>
                <Link :href="`/admin/users/${account.id}`" class="link-quiet">Cancel</Link>
            </div>
        </form>
    </AdminLayout>
</template>
