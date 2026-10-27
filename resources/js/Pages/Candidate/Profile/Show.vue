<script setup>
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    profile: { type: Object, required: true },
    profileStatuses: { type: Object, required: true },
    profileVisibilities: { type: Object, required: true },
})

const statusForm = useForm({
    profile_status: props.profile.profile_status,
})

const visibilityForm = useForm({
    profile_visibility: props.profile.profile_visibility,
})

function saveStatus() {
    statusForm.put('/profile/status')
}

function saveVisibility() {
    visibilityForm.put('/profile/visibility')
}
</script>

<template>
    <Head title="Your profile" />

    <CandidateLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Profile</p>
            <h1>{{ profile.display_name }}</h1>
            <p class="lede">{{ profile.real_name }}</p>
            <p class="meta">People in your groups see your display name. Your real name, phone, address, and contact details stay on this page.</p>
            <p v-if="profile.review_status === 'pending'" class="notice" role="status">This profile is waiting for approval. Other members cannot see it yet.</p>
            <p v-else-if="profile.review_status === 'rejected'" class="notice" role="status">This profile was rejected<span v-if="profile.rejection_reason">: {{ profile.rejection_reason }}</span>.</p>
            <p v-else-if="profile.review_status === 'suspended'" class="notice" role="status">This profile is suspended. Other members cannot see it.</p>

            <div class="form-grid">
                <form class="stack" @submit.prevent="saveStatus">
                    <label class="field">
                        <span>Profile status</span>
                        <select v-model="statusForm.profile_status" name="profile_status">
                            <option v-for="(label, value) in profileStatuses" :key="value" :value="value">{{ label }}</option>
                        </select>
                        <small v-if="statusForm.errors.profile_status">{{ statusForm.errors.profile_status }}</small>
                    </label>
                    <p class="meta">Paused profiles stay in your groups but are left out of browse and profile views.</p>
                    <button class="btn btn-primary" type="submit" :disabled="statusForm.processing">Save status</button>
                </form>

                <form class="stack" @submit.prevent="saveVisibility">
                    <label class="field">
                        <span>Profile visibility</span>
                        <select v-model="visibilityForm.profile_visibility" name="profile_visibility">
                            <option v-for="(label, value) in profileVisibilities" :key="value" :value="value">{{ label }}</option>
                        </select>
                        <small v-if="visibilityForm.errors.profile_visibility">{{ visibilityForm.errors.profile_visibility }}</small>
                    </label>
                    <p class="meta">Hidden profiles cannot be opened by other candidates, even in a shared group.</p>
                    <button class="btn btn-primary" type="submit" :disabled="visibilityForm.processing">Save visibility</button>
                </form>
            </div>
        </section>

        <section v-for="section in profile.sections" :key="section.title" class="panel panel-wide section-gap">
            <div class="section-head">
                <h2>{{ section.title }}</h2>
                <Link :href="section.edit_url" class="link-quiet">Edit</Link>
            </div>
            <dl class="detail-list">
                <div v-for="item in section.items" :key="item.label">
                    <dt>{{ item.label }}</dt>
                    <dd>{{ item.value || '—' }}</dd>
                </div>
            </dl>
        </section>
    </CandidateLayout>
</template>
