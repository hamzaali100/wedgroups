<script setup>
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps({
    profile: { type: Object, required: true },
    passed: { type: Boolean, required: true },
    liked: { type: Boolean, required: true },
    favorited: { type: Boolean, required: true },
})
</script>

<template>
    <Head :title="profile.display_name" />

    <CandidateLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Candidate</p>
            <h1>{{ profile.display_name }}</h1>
            <div class="actions">
                <Link
                    v-if="liked"
                    :href="`/candidates/${profile.id}/like`"
                    method="delete"
                    as="button"
                    class="btn btn-ghost"
                >
                    Remove like
                </Link>
                <Link
                    v-else
                    :href="`/candidates/${profile.id}/like`"
                    method="post"
                    as="button"
                    class="btn btn-primary"
                >
                    Like
                </Link>
                <Link
                    v-if="favorited"
                    :href="`/candidates/${profile.id}/favorite`"
                    method="delete"
                    as="button"
                    class="btn btn-ghost"
                >
                    Remove from favorites
                </Link>
                <Link
                    v-else
                    :href="`/candidates/${profile.id}/favorite`"
                    method="post"
                    as="button"
                    class="btn btn-primary"
                >
                    Add to favorites
                </Link>
                <Link
                    v-if="passed"
                    :href="`/candidates/${profile.id}/pass`"
                    method="delete"
                    as="button"
                    class="btn btn-ghost"
                >
                    Undo pass
                </Link>
                <Link
                    v-else
                    :href="`/candidates/${profile.id}/pass`"
                    method="post"
                    as="button"
                    class="btn btn-ghost"
                >
                    Pass
                </Link>
                <Link href="/candidates" class="link-quiet">Back to browse</Link>
            </div>
        </section>

        <section v-for="section in profile.sections" :key="section.title" class="panel panel-wide section-gap">
            <h2>{{ section.title }}</h2>
            <dl class="detail-list">
                <div v-for="item in section.items" :key="item.label">
                    <dt>{{ item.label }}</dt>
                    <dd>{{ item.value || '—' }}</dd>
                </div>
            </dl>
        </section>
    </CandidateLayout>
</template>
