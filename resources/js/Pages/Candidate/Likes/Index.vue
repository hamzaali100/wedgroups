<script setup>
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps({
    liked: { type: Array, required: true },
})
</script>

<template>
    <Head title="Liked candidates" />

    <CandidateLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Likes</p>
            <h1>Liked candidates</h1>
            <p class="lede">A like stays on your list while you share an active group. It does not hide anyone from browse.</p>
        </section>

        <p v-if="liked.length === 0" class="meta section-gap">You have not liked anyone in your groups.</p>

        <div v-else class="person-grid section-gap">
            <article v-for="candidate in liked" :key="candidate.id" class="person-card">
                <h2>{{ candidate.display_name }}</h2>
                <p class="meta">{{ candidate.detail }}</p>
                <div class="actions">
                    <Link v-if="candidate.available" :href="`/candidates/${candidate.id}`" class="link-quiet">View profile</Link>
                    <Link :href="`/candidates/${candidate.id}/like`" method="delete" as="button" class="btn btn-ghost">Remove like</Link>
                </div>
            </article>
        </div>
    </CandidateLayout>
</template>
