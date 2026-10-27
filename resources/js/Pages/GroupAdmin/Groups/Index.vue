<script setup>
import GroupAdminLayout from '@/Layouts/GroupAdminLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps({
    groups: { type: Array, required: true },
})
</script>

<template>
    <Head title="Your groups" />

    <GroupAdminLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Groups</p>
            <div class="section-head">
                <h1>Groups you manage</h1>
                <Link href="/manage/groups/create" class="btn btn-primary">Create group</Link>
            </div>
            <p class="lede">You can manage details, admins, and candidates for each group you belong to.</p>
        </section>

        <p v-if="groups.length === 0" class="meta section-gap">You do not manage a group yet.</p>

        <div v-else class="person-grid section-gap">
            <article v-for="group in groups" :key="group.id" class="person-card">
                <h2>{{ group.name }}</h2>
                <p>{{ group.description }}</p>
                <p class="meta">{{ group.status_label }}<span v-if="group.is_primary"> · Primary admin</span></p>
                <Link :href="`/manage/groups/${group.id}`" class="link-quiet">View group</Link>
            </article>
        </div>
    </GroupAdminLayout>
</template>
