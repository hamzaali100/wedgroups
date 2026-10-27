<script setup>
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Head, router } from '@inertiajs/vue3'

defineProps({
    groups: { type: Array, required: true },
})

function leave(group) {
    if (window.confirm(`Leave ${group.name}? You will no longer see its members.`)) {
        router.delete(`/groups/${group.id}`)
    }
}
</script>

<template>
    <Head title="Your groups" />

    <CandidateLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Groups</p>
            <h1>Joined groups</h1>
            <p class="lede">You can see candidates only from groups you are actively in.</p>
        </section>

        <p v-if="groups.length === 0" class="meta section-gap">You have not joined a group. A group admin adds candidates to a group.</p>

        <div v-else class="person-grid section-gap">
            <article v-for="group in groups" :key="group.id" class="person-card">
                <h2>{{ group.name }}</h2>
                <p>{{ group.description }}</p>
                <p class="meta">{{ group.status }}</p>
                <button class="btn btn-danger" type="button" @click="leave(group)">Leave group</button>
            </article>
        </div>
    </CandidateLayout>
</template>
