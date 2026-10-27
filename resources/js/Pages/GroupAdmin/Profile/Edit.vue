<script setup>
import GroupAdminLayout from '@/Layouts/GroupAdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    profile: { type: Object, required: true },
})

const form = useForm({
    name: props.profile.name ?? '',
    email: props.profile.email ?? '',
    phone: props.profile.phone ?? '',
    whatsapp: props.profile.whatsapp ?? '',
    description: props.profile.description ?? '',
})

function submit() {
    form.put('/manage/profile')
}
</script>

<template>
    <Head title="Edit profile" />

    <GroupAdminLayout>
        <form class="panel" @submit.prevent="submit">
            <p class="eyebrow">Profile</p>
            <h1>Edit profile</h1>

            <label class="field">
                <span>Name</span>
                <input v-model="form.name" type="text" required maxlength="150" autocomplete="name">
                <small v-if="form.errors.name">{{ form.errors.name }}</small>
            </label>

            <label class="field">
                <span>Email</span>
                <input v-model="form.email" type="email" required maxlength="255" autocomplete="username">
                <small v-if="form.errors.email">{{ form.errors.email }}</small>
            </label>

            <label class="field">
                <span>Phone</span>
                <input v-model="form.phone" type="tel" maxlength="30" autocomplete="tel">
                <small v-if="form.errors.phone">{{ form.errors.phone }}</small>
            </label>

            <label class="field">
                <span>WhatsApp</span>
                <input v-model="form.whatsapp" type="tel" maxlength="30">
                <small v-if="form.errors.whatsapp">{{ form.errors.whatsapp }}</small>
            </label>

            <label class="field">
                <span>Description</span>
                <textarea v-model="form.description" maxlength="1000" rows="4" />
                <small v-if="form.errors.description">{{ form.errors.description }}</small>
            </label>

            <div class="actions">
                <button class="btn btn-primary" type="submit" :disabled="form.processing">Save profile</button>
                <Link href="/manage/profile" class="link-quiet">Cancel</Link>
            </div>
        </form>
    </GroupAdminLayout>
</template>
