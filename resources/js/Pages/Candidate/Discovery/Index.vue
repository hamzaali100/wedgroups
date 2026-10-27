<script setup>
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    candidates: { type: Object, required: true },
    filters: { type: Object, required: true },
    hasGroup: { type: Boolean, required: true },
    genders: { type: Object, required: true },
    maritalStatuses: { type: Object, required: true },
    educationLevels: { type: Object, required: true },
    religions: { type: Array, required: true },
    countries: { type: Array, required: true },
})

const form = useForm({
    display_name: props.filters.display_name ?? '',
    gender: props.filters.gender ?? '',
    marital_status: props.filters.marital_status ?? '',
    religion_id: props.filters.religion_id ?? '',
    country_id: props.filters.country_id ?? '',
    education_level: props.filters.education_level ?? '',
    age_min: props.filters.age_min ?? '',
    age_max: props.filters.age_max ?? '',
    height_min: props.filters.height_min ?? '',
    height_max: props.filters.height_max ?? '',
})

function submit() {
    form.get('/candidates', {
        preserveState: true,
        replace: true,
    })
}
</script>

<template>
    <Head title="Browse candidates" />

    <CandidateLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Discovery</p>
            <h1>Browse candidates</h1>
            <p class="lede">You only see candidates who share an active group with you. People you have passed are listed separately.</p>

            <form class="form-grid" @submit.prevent="submit">
                <label class="field">
                    <span>Name</span>
                    <input v-model="form.display_name" type="search" maxlength="100">
                </label>
                <label class="field">
                    <span>Gender</span>
                    <select v-model="form.gender">
                        <option value="">Any</option>
                        <option v-for="(label, value) in genders" :key="value" :value="value">{{ label }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>Marital status</span>
                    <select v-model="form.marital_status">
                        <option value="">Any</option>
                        <option v-for="(label, value) in maritalStatuses" :key="value" :value="value">{{ label }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>Religion</span>
                    <select v-model="form.religion_id">
                        <option value="">Any</option>
                        <option v-for="religion in religions" :key="religion.id" :value="String(religion.id)">{{ religion.name }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>Country</span>
                    <select v-model="form.country_id">
                        <option value="">Any</option>
                        <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>Education</span>
                    <select v-model="form.education_level">
                        <option value="">Any</option>
                        <option v-for="(label, value) in educationLevels" :key="value" :value="value">{{ label }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>Minimum age</span>
                    <input v-model="form.age_min" type="number" min="18" max="80">
                    <small v-if="form.errors.age_min">{{ form.errors.age_min }}</small>
                </label>
                <label class="field">
                    <span>Maximum age</span>
                    <input v-model="form.age_max" type="number" min="18" max="80">
                    <small v-if="form.errors.age_max">{{ form.errors.age_max }}</small>
                </label>
                <label class="field">
                    <span>Minimum height (cm)</span>
                    <input v-model="form.height_min" type="number" min="120" max="230">
                    <small v-if="form.errors.height_min">{{ form.errors.height_min }}</small>
                </label>
                <label class="field">
                    <span>Maximum height (cm)</span>
                    <input v-model="form.height_max" type="number" min="120" max="230">
                    <small v-if="form.errors.height_max">{{ form.errors.height_max }}</small>
                </label>
                <div class="actions field-span">
                    <button class="btn btn-primary" type="submit" :disabled="form.processing">Apply filters</button>
                    <Link href="/candidates" class="link-quiet">Clear</Link>
                </div>
            </form>
        </section>

        <p v-if="!hasGroup" class="meta section-gap">You are not in an active group yet, so there is nobody to show.</p>

        <div v-else-if="candidates.data.length === 0" class="meta section-gap">No candidates match.</div>

        <div v-else class="person-grid section-gap">
            <article v-for="candidate in candidates.data" :key="candidate.id" class="person-card">
                <h2>{{ candidate.display_name }}</h2>
                <p>{{ candidate.age }} years · {{ candidate.gender }} · {{ candidate.height }} cm</p>
                <p class="meta">{{ candidate.marital_status }} · {{ candidate.education }}</p>
                <p v-if="candidate.location" class="meta">{{ candidate.location }}</p>
                <Link :href="`/candidates/${candidate.id}`" class="link-quiet">View profile</Link>
            </article>
        </div>

        <div v-if="candidates.prev_page_url || candidates.next_page_url" class="actions section-gap">
            <Link v-if="candidates.prev_page_url" :href="candidates.prev_page_url" class="btn btn-ghost">Previous</Link>
            <Link v-if="candidates.next_page_url" :href="candidates.next_page_url" class="btn btn-ghost">Next</Link>
        </div>
    </CandidateLayout>
</template>
