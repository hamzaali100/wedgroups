<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    whatsapp: '',
    description: '',
})

function submit() {
    form.post('/admin/group-admins')
}
</script>

<template>
    <Head title="Create group admin" />

    <AdminLayout>
        <form class="panel" @submit.prevent="submit">
            <p class="eyebrow">Group admin</p>
            <h1>Create group admin</h1>
            <p class="lede">The account stays unapproved until you approve it. It cannot be added to a group before that.</p>

            <div class="form-grid">
                <label class="field">
                    <span>Name</span>
                    <input v-model="form.name" name="name" required maxlength="150">
                    <small v-if="form.errors.name">{{ form.errors.name }}</small>
                </label>
                <label class="field">
                    <span>Email</span>
                    <input v-model="form.email" type="email" name="email" required maxlength="255">
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
                <label class="field">
                    <span>Phone</span>
                    <input v-model="form.phone" name="phone" maxlength="30">
                    <small v-if="form.errors.phone">{{ form.errors.phone }}</small>
                </label>
                <label class="field">
                    <span>WhatsApp</span>
                    <input v-model="form.whatsapp" name="whatsapp" maxlength="30">
                    <small v-if="form.errors.whatsapp">{{ form.errors.whatsapp }}</small>
                </label>
                <label class="field field-span">
                    <span>Description</span>
                    <textarea v-model="form.description" name="description" rows="4" maxlength="1000"></textarea>
                    <small v-if="form.errors.description">{{ form.errors.description }}</small>
                </label>
            </div>

            <div class="actions section-gap">
                <button class="btn btn-primary" type="submit" :disabled="form.processing">Create group admin</button>
                <Link href="/admin/group-admins" class="link-quiet">Cancel</Link>
            </div>
        </form>
    </AdminLayout>
</template>
