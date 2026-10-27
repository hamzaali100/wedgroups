<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    admin: { type: Object, required: true },
})

const form = useForm({
    name: props.admin.name,
    email: props.admin.email,
    phone: props.admin.phone,
    whatsapp: props.admin.whatsapp,
    description: props.admin.description,
})

function submit() {
    form.put(`/admin/group-admins/${props.admin.id}`)
}
</script>

<template>
    <Head title="Edit group admin" />

    <AdminLayout>
        <form class="panel" @submit.prevent="submit">
            <p class="eyebrow">Group admin</p>
            <h1>Edit {{ admin.name }}</h1>

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
                <button class="btn btn-primary" type="submit" :disabled="form.processing">Save details</button>
                <Link :href="`/admin/group-admins/${admin.id}`" class="link-quiet">Cancel</Link>
            </div>
        </form>
    </AdminLayout>
</template>
