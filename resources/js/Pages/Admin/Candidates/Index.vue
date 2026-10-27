<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    candidates: { type: Object, required: true },
    filters: { type: Object, required: true },
    reviewStatuses: { type: Object, required: true },
})

const form = useForm({
    search: props.filters.search,
    review_status: props.filters.review_status,
})

function search() {
    form.get('/admin/candidates', { preserveState: true, replace: true })
}
</script>

<template>
    <Head title="Candidates" />

    <AdminLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Admin</p>
            <h1>Candidate profiles</h1>

            <form class="form-grid section-gap" @submit.prevent="search">
                <label class="field">
                    <span>Search name or email</span>
                    <input v-model="form.search" type="search" name="search">
                </label>
                <label class="field">
                    <span>Review</span>
                    <select v-model="form.review_status" name="review_status">
                        <option value="">All</option>
                        <option v-for="(label, value) in reviewStatuses" :key="value" :value="value">{{ label }}</option>
                    </select>
                </label>
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>

            <p v-if="candidates.data.length === 0" class="meta section-gap">No candidate profiles match this search.</p>

            <div v-else class="section-gap" style="overflow-x: auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Display name</th>
                            <th>Real name</th>
                            <th>Email</th>
                            <th>Review</th>
                            <th>Profile</th>
                            <th>Account</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="candidate in candidates.data" :key="candidate.id">
                            <td>{{ candidate.display_name }}</td>
                            <td>{{ candidate.real_name }}</td>
                            <td>{{ candidate.email }}</td>
                            <td>{{ candidate.review_label }}</td>
                            <td>{{ candidate.profile_status_label }}</td>
                            <td>{{ candidate.account_status_label }}</td>
                            <td><Link :href="`/admin/candidates/${candidate.id}`" class="link-quiet">View</Link></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AdminLayout>
</template>
