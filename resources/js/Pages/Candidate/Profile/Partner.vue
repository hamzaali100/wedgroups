<script setup>
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    details: { type: Object, required: true },
})

const form = useForm({ ...props.details })

function submit() {
    form.put('/profile/partner')
}
</script>

<template>
    <Head title="Partner requirements" />

    <CandidateLayout>
        <form class="panel" @submit.prevent="submit">
            <p class="eyebrow">Profile</p>
            <h1>Partner requirements</h1>

            <div class="form-grid">
                <label class="field">
                    <span>Minimum age</span>
                    <input v-model="form.partner_age_min" type="number" min="18" max="80">
                    <small v-if="form.errors.partner_age_min">{{ form.errors.partner_age_min }}</small>
                </label>
                <label class="field">
                    <span>Maximum age</span>
                    <input v-model="form.partner_age_max" type="number" min="18" max="80">
                    <small v-if="form.errors.partner_age_max">{{ form.errors.partner_age_max }}</small>
                </label>
                <label class="field">
                    <span>Minimum height (cm)</span>
                    <input v-model="form.partner_height_min" type="number" min="120" max="230">
                    <small v-if="form.errors.partner_height_min">{{ form.errors.partner_height_min }}</small>
                </label>
                <label class="field">
                    <span>Maximum height (cm)</span>
                    <input v-model="form.partner_height_max" type="number" min="120" max="230">
                    <small v-if="form.errors.partner_height_max">{{ form.errors.partner_height_max }}</small>
                </label>
            </div>

            <label class="field">
                <span>Details</span>
                <textarea v-model="form.partner_requirement_details" rows="4" maxlength="500" />
                <small v-if="form.errors.partner_requirement_details">{{ form.errors.partner_requirement_details }}</small>
            </label>

            <div class="actions">
                <button class="btn btn-primary" type="submit" :disabled="form.processing">Save requirements</button>
                <Link href="/profile" class="link-quiet">Back to profile</Link>
            </div>
        </form>
    </CandidateLayout>
</template>
