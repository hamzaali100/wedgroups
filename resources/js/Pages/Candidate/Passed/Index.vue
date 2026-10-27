<script setup>
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps({
    passed: { type: Array, required: true },
})
</script>

<template>
    <Head title="Passed candidates" />

    <CandidateLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Passed</p>
            <h1>Passed candidates</h1>
            <p class="lede">Passing hides someone from browse. You can still open a profile while you share an active group and their profile is available.</p>
        </section>

        <p v-if="passed.length === 0" class="meta section-gap">You have not passed anyone in your groups.</p>

        <div v-else class="person-grid section-gap">
            <article v-for="candidate in passed" :key="candidate.id" class="person-card">
                <h2>{{ candidate.display_name }}</h2>
                <p class="meta">{{ candidate.detail }}</p>
                <div class="actions">
                    <Link v-if="candidate.available" :href="`/candidates/${candidate.id}`" class="link-quiet">View profile</Link>
                    <Link :href="`/candidates/${candidate.id}/pass`" method="delete" as="button" class="btn btn-ghost">Undo pass</Link>
                </div>
            </article>
        </div>
    </CandidateLayout>
</template>
