<script setup>
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { ref } from 'vue'

const props = defineProps({
    details: { type: Object, required: true },
    countries: { type: Array, required: true },
    religions: { type: Array, required: true },
    states: { type: Array, required: true },
    cities: { type: Array, required: true },
    sects: { type: Array, required: true },
    ethnicities: { type: Array, required: true },
    genders: { type: Object, required: true },
    maritalStatuses: { type: Object, required: true },
    wantsChildren: { type: Object, required: true },
})

const form = useForm({ ...props.details })
const stateOptions = ref(props.states)
const cityOptions = ref(props.cities)
const sectOptions = ref(props.sects)
const ethnicityOptions = ref(props.ethnicities)

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

async function onCountryChange() {
    form.state_id = ''
    form.city_id = ''
    form.ethnicity_id = ''
    cityOptions.value = []

    if (!form.country_id) {
        stateOptions.value = []
        ethnicityOptions.value = []
        return
    }

    const [states, ethnicities] = await Promise.all([
        axios.get('/lookups/states', { params: { country_id: form.country_id } }),
        axios.get('/lookups/ethnicities', { params: { country_id: form.country_id } }),
    ])

    stateOptions.value = states.data
    ethnicityOptions.value = ethnicities.data
}

async function onStateChange() {
    form.city_id = ''
    cityOptions.value = []

    if (!form.state_id) {
        return
    }

    const response = await axios.get('/lookups/cities', { params: { state_id: form.state_id } })
    cityOptions.value = response.data
}

async function onReligionChange() {
    form.sect_id = ''
    sectOptions.value = []

    if (!form.religion_id) {
        return
    }

    const response = await axios.get('/lookups/sects', { params: { religion_id: form.religion_id } })
    sectOptions.value = response.data
}

function submit() {
    form.put('/profile/personal')
}
</script>

<template>
    <Head title="Personal details" />

    <CandidateLayout>
        <form class="panel panel-wide" @submit.prevent="submit">
            <p class="eyebrow">Profile</p>
            <h1>Personal details</h1>
            <p class="lede">Fields marked private are stored for you and are not shown on the profile other candidates open.</p>

            <div class="form-grid">
                <label class="field">
                    <span>Real name (private)</span>
                    <input v-model="form.real_name" type="text" required maxlength="150" autocomplete="name">
                    <small v-if="form.errors.real_name">{{ form.errors.real_name }}</small>
                </label>
                <label class="field">
                    <span>Display name</span>
                    <input v-model="form.display_name" type="text" required maxlength="100">
                    <small v-if="form.errors.display_name">{{ form.errors.display_name }}</small>
                </label>
                <label class="field">
                    <span>Year of birth</span>
                    <select v-model="form.year_of_birth" required>
                        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                    </select>
                    <small v-if="form.errors.year_of_birth">{{ form.errors.year_of_birth }}</small>
                </label>
                <label class="field">
                    <span>Month of birth</span>
                    <select v-model="form.month_of_birth" required>
                        <option v-for="[value, label] in months" :key="value" :value="Number(value)">{{ label }}</option>
                    </select>
                    <small v-if="form.errors.month_of_birth">{{ form.errors.month_of_birth }}</small>
                </label>
                <label class="field">
                    <span>Gender</span>
                    <select v-model="form.gender" required>
                        <option v-for="(label, value) in genders" :key="value" :value="value">{{ label }}</option>
                    </select>
                    <small v-if="form.errors.gender">{{ form.errors.gender }}</small>
                </label>
                <label class="field">
                    <span>Height (cm)</span>
                    <input v-model="form.height" type="number" required min="120" max="230">
                    <small v-if="form.errors.height">{{ form.errors.height }}</small>
                </label>
                <label class="field">
                    <span>Marital status</span>
                    <select v-model="form.marital_status" required>
                        <option v-for="(label, value) in maritalStatuses" :key="value" :value="value">{{ label }}</option>
                    </select>
                    <small v-if="form.errors.marital_status">{{ form.errors.marital_status }}</small>
                </label>
                <label class="field">
                    <span>Religion</span>
                    <select v-model="form.religion_id" required @change="onReligionChange">
                        <option v-for="religion in religions" :key="religion.id" :value="religion.id">{{ religion.name }}</option>
                    </select>
                    <small v-if="form.errors.religion_id">{{ form.errors.religion_id }}</small>
                </label>
                <label class="field">
                    <span>Sect</span>
                    <select v-model="form.sect_id">
                        <option value="">None</option>
                        <option v-for="sect in sectOptions" :key="sect.id" :value="sect.id">{{ sect.name }}</option>
                    </select>
                    <small v-if="form.errors.sect_id">{{ form.errors.sect_id }}</small>
                </label>
                <label class="field">
                    <span>Country</span>
                    <select v-model="form.country_id" required @change="onCountryChange">
                        <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
                    </select>
                    <small v-if="form.errors.country_id">{{ form.errors.country_id }}</small>
                </label>
                <label class="field">
                    <span>State</span>
                    <select v-model="form.state_id" @change="onStateChange">
                        <option value="">None</option>
                        <option v-for="state in stateOptions" :key="state.id" :value="state.id">{{ state.name }}</option>
                    </select>
                    <small v-if="form.errors.state_id">{{ form.errors.state_id }}</small>
                </label>
                <label class="field">
                    <span>City</span>
                    <select v-model="form.city_id">
                        <option value="">None</option>
                        <option v-for="city in cityOptions" :key="city.id" :value="city.id">{{ city.name }}</option>
                    </select>
                    <small v-if="form.errors.city_id">{{ form.errors.city_id }}</small>
                </label>
                <label class="field">
                    <span>Grew up in</span>
                    <select v-model="form.grew_up_in">
                        <option value="">None</option>
                        <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
                    </select>
                    <small v-if="form.errors.grew_up_in">{{ form.errors.grew_up_in }}</small>
                </label>
                <label class="field">
                    <span>Ethnicity</span>
                    <select v-model="form.ethnicity_id">
                        <option value="">None</option>
                        <option v-for="ethnicity in ethnicityOptions" :key="ethnicity.id" :value="ethnicity.id">{{ ethnicity.name }}</option>
                    </select>
                    <small v-if="form.errors.ethnicity_id">{{ form.errors.ethnicity_id }}</small>
                </label>
                <label class="field">
                    <span>Ethnicity details</span>
                    <input v-model="form.ethnicity" type="text" maxlength="100">
                    <small v-if="form.errors.ethnicity">{{ form.errors.ethnicity }}</small>
                </label>
                <label class="field">
                    <span>Caste</span>
                    <input v-model="form.caste" type="text" maxlength="100">
                    <small v-if="form.errors.caste">{{ form.errors.caste }}</small>
                </label>
                <label class="field">
                    <span>Has children</span>
                    <select v-model="form.has_kids">
                        <option value="">Not specified</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                    <small v-if="form.errors.has_kids">{{ form.errors.has_kids }}</small>
                </label>
                <label class="field">
                    <span>Wants children</span>
                    <select v-model="form.wants_children">
                        <option value="">Not specified</option>
                        <option v-for="(label, value) in wantsChildren" :key="value" :value="value">{{ label }}</option>
                    </select>
                    <small v-if="form.errors.wants_children">{{ form.errors.wants_children }}</small>
                </label>
                <label class="field">
                    <span>Willing to move abroad</span>
                    <select v-model="form.willing_to_move_abroad">
                        <option value="">Not specified</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                    <small v-if="form.errors.willing_to_move_abroad">{{ form.errors.willing_to_move_abroad }}</small>
                </label>
                <label class="field field-span">
                    <span>Nationalities</span>
                    <select v-model="form.nationality_ids" multiple size="4">
                        <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.nationality || country.name }}</option>
                    </select>
                    <small v-if="form.errors.nationality_ids">{{ form.errors.nationality_ids }}</small>
                </label>
                <label class="field field-span">
                    <span>Residence (private)</span>
                    <input v-model="form.residence_details" type="text" maxlength="150">
                    <small v-if="form.errors.residence_details">{{ form.errors.residence_details }}</small>
                </label>
                <label class="field field-span">
                    <span>Other details</span>
                    <textarea v-model="form.other_details" rows="4" maxlength="700" />
                    <small v-if="form.errors.other_details">{{ form.errors.other_details }}</small>
                </label>
                <label class="field field-span">
                    <span>Disabilities (private)</span>
                    <textarea v-model="form.disabilities" rows="3" maxlength="500" />
                    <small v-if="form.errors.disabilities">{{ form.errors.disabilities }}</small>
                </label>
                <label class="field">
                    <span>Phone (private)</span>
                    <input v-model="form.phone_number" type="tel" maxlength="30" autocomplete="tel">
                    <small v-if="form.errors.phone_number">{{ form.errors.phone_number }}</small>
                </label>
                <label class="field">
                    <span>Contact person (private)</span>
                    <input v-model="form.contact_person" type="text" maxlength="150">
                    <small v-if="form.errors.contact_person">{{ form.errors.contact_person }}</small>
                </label>
                <label class="field">
                    <span>Contact relationship (private)</span>
                    <input v-model="form.contact_relationship" type="text" maxlength="50">
                    <small v-if="form.errors.contact_relationship">{{ form.errors.contact_relationship }}</small>
                </label>
                <label class="field">
                    <span>Contact phone (private)</span>
                    <input v-model="form.contact_phone_number" type="tel" maxlength="30">
                    <small v-if="form.errors.contact_phone_number">{{ form.errors.contact_phone_number }}</small>
                </label>
                <label class="field">
                    <span>Contact email (private)</span>
                    <input v-model="form.contact_email" type="email" maxlength="254">
                    <small v-if="form.errors.contact_email">{{ form.errors.contact_email }}</small>
                </label>
            </div>

            <div class="actions">
                <button class="btn btn-primary" type="submit" :disabled="form.processing">Save personal details</button>
                <Link href="/profile" class="link-quiet">Back to profile</Link>
            </div>
        </form>
    </CandidateLayout>
</template>
