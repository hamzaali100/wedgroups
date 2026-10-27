<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    candidate: { type: Object, required: true },
    religions: { type: Array, required: true },
    countries: { type: Array, required: true },
    genders: { type: Object, required: true },
    maritalStatuses: { type: Object, required: true },
    educationLevels: { type: Object, required: true },
    profileStatuses: { type: Object, required: true },
    profileVisibilities: { type: Object, required: true },
})

const form = useForm({ ...props.candidate })

function submit() {
    form.put(`/admin/candidates/${props.candidate.id}`)
}
</script>

<template>
    <Head title="Edit candidate" />

    <AdminLayout>
        <form class="panel panel-wide" @submit.prevent="submit">
            <p class="eyebrow">Candidate</p>
            <h1>Edit {{ candidate.display_name }}</h1>
            <p class="lede">Approval, rejection, and suspension stay on the profile page.</p>

            <div class="form-grid">
                <label class="field">
                    <span>Real name</span>
                    <input v-model="form.real_name" required maxlength="150">
                    <small v-if="form.errors.real_name">{{ form.errors.real_name }}</small>
                </label>
                <label class="field">
                    <span>Display name</span>
                    <input v-model="form.display_name" required maxlength="100">
                    <small v-if="form.errors.display_name">{{ form.errors.display_name }}</small>
                </label>
                <label class="field">
                    <span>Year of birth</span>
                    <input v-model="form.year_of_birth" type="number" required>
                    <small v-if="form.errors.year_of_birth">{{ form.errors.year_of_birth }}</small>
                </label>
                <label class="field">
                    <span>Month of birth</span>
                    <input v-model="form.month_of_birth" type="number" min="1" max="12" required>
                    <small v-if="form.errors.month_of_birth">{{ form.errors.month_of_birth }}</small>
                </label>
                <label class="field">
                    <span>Gender</span>
                    <select v-model="form.gender" required>
                        <option v-for="(label, value) in genders" :key="value" :value="value">{{ label }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>Height (cm)</span>
                    <input v-model="form.height" type="number" required>
                    <small v-if="form.errors.height">{{ form.errors.height }}</small>
                </label>
                <label class="field">
                    <span>Marital status</span>
                    <select v-model="form.marital_status" required>
                        <option v-for="(label, value) in maritalStatuses" :key="value" :value="value">{{ label }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>Religion</span>
                    <select v-model="form.religion_id" required>
                        <option v-for="religion in religions" :key="religion.id" :value="religion.id">{{ religion.name }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>Country</span>
                    <select v-model="form.country_id" required>
                        <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>Education</span>
                    <select v-model="form.education_level" required>
                        <option v-for="(label, value) in educationLevels" :key="value" :value="value">{{ label }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>Profile status</span>
                    <select v-model="form.profile_status" required>
                        <option v-for="(label, value) in profileStatuses" :key="value" :value="value">{{ label }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>Visibility</span>
                    <select v-model="form.profile_visibility" required>
                        <option v-for="(label, value) in profileVisibilities" :key="value" :value="value">{{ label }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>Phone</span>
                    <input v-model="form.phone_number" maxlength="30">
                </label>
                <label class="field">
                    <span>Universities</span>
                    <input v-model="form.universities_names" maxlength="500">
                </label>
                <label class="field">
                    <span>Schools</span>
                    <input v-model="form.schools_names" maxlength="500">
                </label>
                <label class="field">
                    <span>Monthly income</span>
                    <input v-model="form.monthly_income" maxlength="300">
                </label>
                <label class="field field-span">
                    <span>Career details</span>
                    <textarea v-model="form.career_details" rows="3" maxlength="500"></textarea>
                </label>
                <label class="field">
                    <span>Siblings</span>
                    <input v-model="form.total_siblings" type="number" min="0" max="30">
                </label>
                <label class="field">
                    <span>Father's occupation</span>
                    <input v-model="form.father_occupation" maxlength="150">
                </label>
                <label class="field">
                    <span>Mother's occupation</span>
                    <input v-model="form.mother_occupation" maxlength="150">
                </label>
                <label class="field">
                    <span>Parent contact</span>
                    <input v-model="form.parent_contact_number" maxlength="30">
                </label>
                <label class="field field-span">
                    <span>Family details</span>
                    <textarea v-model="form.family_details" rows="3" maxlength="500"></textarea>
                </label>
                <label class="field field-span">
                    <span>Partner requirements</span>
                    <textarea v-model="form.partner_requirement_details" rows="3" maxlength="500"></textarea>
                </label>
                <label class="field field-span">
                    <span>Other details</span>
                    <textarea v-model="form.other_details" rows="3" maxlength="700"></textarea>
                </label>
                <label class="field field-span">
                    <span>Disabilities</span>
                    <textarea v-model="form.disabilities" rows="3" maxlength="500"></textarea>
                </label>
            </div>

            <div class="actions section-gap">
                <button class="btn btn-primary" type="submit" :disabled="form.processing">Save profile</button>
                <Link :href="`/admin/candidates/${candidate.id}`" class="link-quiet">Cancel</Link>
            </div>
        </form>
    </AdminLayout>
</template>
