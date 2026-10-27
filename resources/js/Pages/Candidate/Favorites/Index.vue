<script setup>
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps({
    favorites: { type: Array, required: true },
})
</script>

<template>
    <Head title="Favorite candidates" />

    <CandidateLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Favorites</p>
            <h1>Favorite candidates</h1>
            <p class="lede">Favorites are a private list of people in your groups. Removing someone does not change what they can see.</p>
        </section>

        <p v-if="favorites.length === 0" class="meta section-gap">You have not favorited anyone in your groups.</p>

        <div v-else class="person-grid section-gap">
            <article v-for="candidate in favorites" :key="candidate.id" class="person-card">
                <h2>{{ candidate.display_name }}</h2>
                <p class="meta">{{ candidate.detail }}</p>
                <div class="actions">
                    <Link v-if="candidate.available" :href="`/candidates/${candidate.id}`" class="link-quiet">View profile</Link>
                    <Link :href="`/candidates/${candidate.id}/favorite`" method="delete" as="button" class="btn btn-ghost">Remove from favorites</Link>
                </div>
            </article>
        </div>
    </CandidateLayout>
</template>
