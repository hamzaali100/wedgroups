<script setup>
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    details: { type: Object, required: true },
    educationLevels: { type: Object, required: true },
})

const form = useForm({ ...props.details })

function submit() {
    form.put('/profile/education')
}
</script>

<template>
    <Head title="Education" />

    <CandidateLayout>
        <form class="panel" @submit.prevent="submit">
            <p class="eyebrow">Profile</p>
            <h1>Education</h1>

            <label class="field">
                <span>Education level</span>
                <select v-model="form.education_level" required>
                    <option v-for="(label, value) in educationLevels" :key="value" :value="value">{{ label }}</option>
                </select>
                <small v-if="form.errors.education_level">{{ form.errors.education_level }}</small>
            </label>

            <label class="field">
                <span>Universities</span>
                <textarea v-model="form.universities_names" rows="3" maxlength="500" />
                <small v-if="form.errors.universities_names">{{ form.errors.universities_names }}</small>
            </label>

            <label class="field">
                <span>Schools</span>
                <textarea v-model="form.schools_names" rows="3" maxlength="500" />
                <small v-if="form.errors.schools_names">{{ form.errors.schools_names }}</small>
            </label>

            <div class="actions">
                <button class="btn btn-primary" type="submit" :disabled="form.processing">Save education</button>
                <Link href="/profile" class="link-quiet">Back to profile</Link>
            </div>
        </form>
    </CandidateLayout>
</template>
