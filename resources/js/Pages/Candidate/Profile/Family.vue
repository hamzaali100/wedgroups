<script setup>
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    details: { type: Object, required: true },
})

const form = useForm({ ...props.details })

function submit() {
    form.put('/profile/family')
}
</script>

<template>
    <Head title="Family" />

    <CandidateLayout>
        <form class="panel" @submit.prevent="submit">
            <p class="eyebrow">Profile</p>
            <h1>Family</h1>

            <label class="field">
                <span>Number of siblings</span>
                <input v-model="form.total_siblings" type="number" required min="0" max="30">
                <small v-if="form.errors.total_siblings">{{ form.errors.total_siblings }}</small>
            </label>

            <label class="field">
                <span>Siblings</span>
                <textarea v-model="form.siblings_description" rows="3" maxlength="500" />
                <small v-if="form.errors.siblings_description">{{ form.errors.siblings_description }}</small>
            </label>

            <label class="field">
                <span>Father's occupation</span>
                <input v-model="form.father_occupation" type="text" maxlength="150">
                <small v-if="form.errors.father_occupation">{{ form.errors.father_occupation }}</small>
            </label>

            <label class="field">
                <span>Mother's occupation</span>
                <input v-model="form.mother_occupation" type="text" maxlength="150">
                <small v-if="form.errors.mother_occupation">{{ form.errors.mother_occupation }}</small>
            </label>

            <label class="field">
                <span>Family details</span>
                <textarea v-model="form.family_details" rows="4" maxlength="500" />
                <small v-if="form.errors.family_details">{{ form.errors.family_details }}</small>
            </label>

            <label class="field">
                <span>Parent contact number (private)</span>
                <input v-model="form.parent_contact_number" type="tel" maxlength="30">
                <small v-if="form.errors.parent_contact_number">{{ form.errors.parent_contact_number }}</small>
            </label>

            <div class="actions">
                <button class="btn btn-primary" type="submit" :disabled="form.processing">Save family</button>
                <Link href="/profile" class="link-quiet">Back to profile</Link>
            </div>
        </form>
    </CandidateLayout>
</template>
