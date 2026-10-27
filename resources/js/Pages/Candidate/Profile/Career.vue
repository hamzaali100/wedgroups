<script setup>
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    details: { type: Object, required: true },
    occupations: { type: Array, required: true },
})

const form = useForm({ ...props.details })

function submit() {
    form.put('/profile/career')
}
</script>

<template>
    <Head title="Career" />

    <CandidateLayout>
        <form class="panel" @submit.prevent="submit">
            <p class="eyebrow">Profile</p>
            <h1>Career</h1>

            <label class="field">
                <span>Occupation</span>
                <select v-model="form.occupation_id">
                    <option value="">None</option>
                    <option v-for="occupation in occupations" :key="occupation.id" :value="occupation.id">{{ occupation.name }}</option>
                </select>
                <small v-if="form.errors.occupation_id">{{ form.errors.occupation_id }}</small>
            </label>

            <label class="field">
                <span>Monthly income</span>
                <input v-model="form.monthly_income" type="text" maxlength="300">
                <small v-if="form.errors.monthly_income">{{ form.errors.monthly_income }}</small>
            </label>

            <label class="field">
                <span>Career details</span>
                <textarea v-model="form.career_details" rows="4" maxlength="500" />
                <small v-if="form.errors.career_details">{{ form.errors.career_details }}</small>
            </label>

            <label class="field">
                <span>LinkedIn (private)</span>
                <input v-model="form.linkedin" type="url" maxlength="255" placeholder="https://">
                <small v-if="form.errors.linkedin">{{ form.errors.linkedin }}</small>
            </label>

            <div class="actions">
                <button class="btn btn-primary" type="submit" :disabled="form.processing">Save career</button>
                <Link href="/profile" class="link-quiet">Back to profile</Link>
            </div>
        </form>
    </CandidateLayout>
</template>
