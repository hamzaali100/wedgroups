<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
    roles: { type: Object, required: true },
    statuses: { type: Object, required: true },
})

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'candidate',
    status: 'active',
    phone: '',
    whatsapp: '',
    description: '',
})

function submit() {
    form.post('/admin/users')
}
</script>

<template>
    <Head title="Create user" />

    <AdminLayout>
        <form class="panel" @submit.prevent="submit">
            <p class="eyebrow">User account</p>
            <h1>Create user</h1>

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
                    <span>Role</span>
                    <select v-model="form.role" name="role" required>
                        <option v-for="(label, value) in roles" :key="value" :value="value">{{ label }}</option>
                    </select>
                    <small v-if="form.errors.role">{{ form.errors.role }}</small>
                </label>
                <label class="field">
                    <span>Status</span>
                    <select v-model="form.status" name="status" required>
                        <option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option>
                    </select>
                    <small v-if="form.errors.status">{{ form.errors.status }}</small>
                </label>
                <template v-if="form.role === 'group_admin'">
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
                </template>
            </div>

            <div class="actions section-gap">
                <button class="btn btn-primary" type="submit" :disabled="form.processing">Create account</button>
                <Link href="/admin/users" class="link-quiet">Cancel</Link>
            </div>
        </form>
    </AdminLayout>
</template>
