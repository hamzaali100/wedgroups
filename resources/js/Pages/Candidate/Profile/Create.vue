<script setup>
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

defineProps({
    countries: { type: Array, required: true },
    religions: { type: Array, required: true },
    genders: { type: Object, required: true },
    maritalStatuses: { type: Object, required: true },
    educationLevels: { type: Object, required: true },
})

const form = useForm({
    real_name: '',
    display_name: '',
    year_of_birth: '',
    month_of_birth: '',
    gender: '',
    height: '',
    marital_status: '',
    religion_id: '',
    country_id: '',
    education_level: '',
})

const months = [
    ['1', 'January'], ['2', 'February'], ['3', 'March'], ['4', 'April'],
    ['5', 'May'], ['6', 'June'], ['7', 'July'], ['8', 'August'],
    ['9', 'September'], ['10', 'October'], ['11', 'November'], ['12', 'December'],
]

const thisYear = new Date().getFullYear()
const years = []
for (let year = thisYear - 18; year >= 1940; year -= 1) {
    years.push(year)
}

function submit() {
    form.post('/profile')
}
</script>

<template>
    <Head title="Create profile" />

    <CandidateLayout>
        <form class="panel" @submit.prevent="submit">
            <p class="eyebrow">Profile</p>
            <h1>Create your profile</h1>
            <p class="lede">This is the profile members of your groups can see. You can add the rest of your details next.</p>

            <div class="form-grid">
                <label class="field">
                    <span>Real name</span>
                    <input v-model="form.real_name" type="text" name="real_name" required maxlength="150" autocomplete="name">
                    <small v-if="form.errors.real_name">{{ form.errors.real_name }}</small>
                </label>

                <label class="field">
                    <span>Display name</span>
                    <input v-model="form.display_name" type="text" name="display_name" required maxlength="100">
                    <small v-if="form.errors.display_name">{{ form.errors.display_name }}</small>
                </label>

                <label class="field">
                    <span>Year of birth</span>
                    <select v-model="form.year_of_birth" name="year_of_birth" required>
                        <option value="" disabled>Select year</option>
                        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                    </select>
                    <small v-if="form.errors.year_of_birth">{{ form.errors.year_of_birth }}</small>
                </label>

                <label class="field">
                    <span>Month of birth</span>
                    <select v-model="form.month_of_birth" name="month_of_birth" required>
                        <option value="" disabled>Select month</option>
                        <option v-for="[value, label] in months" :key="value" :value="value">{{ label }}</option>
                    </select>
                    <small v-if="form.errors.month_of_birth">{{ form.errors.month_of_birth }}</small>
                </label>

                <label class="field">
                    <span>Gender</span>
                    <select v-model="form.gender" name="gender" required>
                        <option value="" disabled>Select gender</option>
                        <option v-for="(label, value) in genders" :key="value" :value="value">{{ label }}</option>
                    </select>
                    <small v-if="form.errors.gender">{{ form.errors.gender }}</small>
                </label>

                <label class="field">
                    <span>Height (cm)</span>
                    <input v-model="form.height" type="number" name="height" required min="120" max="230">
                    <small v-if="form.errors.height">{{ form.errors.height }}</small>
                </label>

                <label class="field">
                    <span>Marital status</span>
                    <select v-model="form.marital_status" name="marital_status" required>
                        <option value="" disabled>Select status</option>
                        <option v-for="(label, value) in maritalStatuses" :key="value" :value="value">{{ label }}</option>
                    </select>
                    <small v-if="form.errors.marital_status">{{ form.errors.marital_status }}</small>
                </label>

                <label class="field">
                    <span>Religion</span>
                    <select v-model="form.religion_id" name="religion_id" required>
                        <option value="" disabled>Select religion</option>
                        <option v-for="religion in religions" :key="religion.id" :value="religion.id">{{ religion.name }}</option>
                    </select>
                    <small v-if="form.errors.religion_id">{{ form.errors.religion_id }}</small>
                </label>

                <label class="field">
                    <span>Country</span>
                    <select v-model="form.country_id" name="country_id" required>
                        <option value="" disabled>Select country</option>
                        <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
                    </select>
                    <small v-if="form.errors.country_id">{{ form.errors.country_id }}</small>
                </label>

                <label class="field">
                    <span>Education</span>
                    <select v-model="form.education_level" name="education_level" required>
                        <option value="" disabled>Select education</option>
                        <option v-for="(label, value) in educationLevels" :key="value" :value="value">{{ label }}</option>
                    </select>
                    <small v-if="form.errors.education_level">{{ form.errors.education_level }}</small>
                </label>
            </div>

            <button class="btn btn-primary" type="submit" :disabled="form.processing">Save profile</button>
        </form>
    </CandidateLayout>
</template>
