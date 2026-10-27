<script setup>
import GroupAdminLayout from '@/Layouts/GroupAdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
    profile: { type: Object, required: true },
})

const password = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

function savePassword() {
    password.put('/manage/profile/password', {
        onSuccess: () => password.reset(),
    })
}
</script>

<template>
    <Head title="Your profile" />

    <GroupAdminLayout>
        <section class="panel">
            <p class="eyebrow">Profile</p>
            <h1>{{ profile.name }}</h1>

            <dl class="detail-list">
                <div>
                    <dt>Name</dt>
                    <dd>{{ profile.name }}</dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd>{{ profile.email }}</dd>
                </div>
                <div>
                    <dt>Phone</dt>
                    <dd>{{ profile.phone || '—' }}</dd>
                </div>
                <div>
                    <dt>WhatsApp</dt>
                    <dd>{{ profile.whatsapp || '—' }}</dd>
                </div>
                <div>
                    <dt>Description</dt>
                    <dd>{{ profile.description || '—' }}</dd>
                </div>
            </dl>

            <Link href="/manage/profile/edit" class="btn btn-primary">Edit profile</Link>
        </section>

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
    </GroupAdminLayout>
</template>
