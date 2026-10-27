<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

defineProps({
    reports: { type: Object, required: true },
})
</script>

<template>
    <Head title="Reports" />

    <AdminLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Reports</p>
            <h1>User statistics</h1>
            <p class="lede">{{ reports.users.total }} accounts</p>
            <div class="person-grid">
                <article class="person-card">
                    <h2>By role</h2>
                    <p v-for="row in reports.users.by_role" :key="row.label" class="meta">{{ row.label }} · {{ row.total }}</p>
                </article>
                <article class="person-card">
                    <h2>By status</h2>
                    <p v-for="row in reports.users.by_status" :key="row.label" class="meta">{{ row.label }} · {{ row.total }}</p>
                </article>
            </div>
        </section>

        <section class="panel panel-wide section-gap">
            <h1>Candidate statistics</h1>
            <p class="lede">{{ reports.candidates.total }} profiles</p>
            <div class="person-grid">
                <article class="person-card">
                    <h2>Review</h2>
                    <p v-for="row in reports.candidates.by_review" :key="row.label" class="meta">{{ row.label }} · {{ row.total }}</p>
                </article>
                <article class="person-card">
                    <h2>Gender</h2>
                    <p v-for="row in reports.candidates.by_gender" :key="row.label" class="meta">{{ row.label }} · {{ row.total }}</p>
                </article>
                <article class="person-card">
                    <h2>Visibility</h2>
                    <p v-for="row in reports.candidates.by_visibility" :key="row.label" class="meta">{{ row.label }} · {{ row.total }}</p>
                </article>
                <article class="person-card">
                    <h2>Profile status</h2>
                    <p v-for="row in reports.candidates.by_profile_status" :key="row.label" class="meta">{{ row.label }} · {{ row.total }}</p>
                </article>
            </div>
        </section>

        <section class="panel panel-wide section-gap">
            <h1>Group statistics</h1>
            <p class="lede">{{ reports.groups.total }} groups · {{ reports.groups.active_memberships }} active memberships</p>
            <div class="person-grid">
                <article class="person-card">
                    <h2>Group status</h2>
                    <p v-for="row in reports.groups.by_status" :key="row.label" class="meta">{{ row.label }} · {{ row.total }}</p>
                </article>
                <article class="person-card">
                    <h2>Memberships</h2>
                    <p v-for="row in reports.groups.memberships" :key="row.label" class="meta">{{ row.label }} · {{ row.total }}</p>
                </article>
            </div>
        </section>

        <section class="panel panel-wide section-gap">
            <h1>Matching statistics</h1>
            <div class="person-grid">
                <article class="person-card"><h2>{{ reports.matching.likes }}</h2><p class="meta">Likes</p></article>
                <article class="person-card"><h2>{{ reports.matching.mutual_likes }}</h2><p class="meta">Mutual likes</p></article>
                <article class="person-card"><h2>{{ reports.matching.favorites }}</h2><p class="meta">Favorites</p></article>
                <article class="person-card"><h2>{{ reports.matching.passes }}</h2><p class="meta">Passes</p></article>
            </div>
        </section>

        <section class="panel panel-wide section-gap">
            <h1>Activity statistics</h1>
            <p class="lede">The last 30 days, plus profiles currently approved.</p>
            <div class="person-grid">
                <article class="person-card"><h2>{{ reports.activity.users_30_days }}</h2><p class="meta">New accounts</p></article>
                <article class="person-card"><h2>{{ reports.activity.profiles_30_days }}</h2><p class="meta">New profiles</p></article>
                <article class="person-card"><h2>{{ reports.activity.likes_30_days }}</h2><p class="meta">Likes</p></article>
                <article class="person-card"><h2>{{ reports.activity.favorites_30_days }}</h2><p class="meta">Favorites</p></article>
                <article class="person-card"><h2>{{ reports.activity.passes_30_days }}</h2><p class="meta">Passes</p></article>
                <article class="person-card"><h2>{{ reports.activity.group_assignments_30_days }}</h2><p class="meta">Group admin assignments</p></article>
                <article class="person-card"><h2>{{ reports.activity.profile_approvals }}</h2><p class="meta">Approved profiles</p></article>
            </div>
        </section>
    </AdminLayout>
</template>
