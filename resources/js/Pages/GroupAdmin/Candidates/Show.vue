<script setup>
import GroupAdminLayout from '@/Layouts/GroupAdminLayout.vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    group: { type: Object, required: true },
    profile: { type: Object, required: true },
    membership: { type: Object, required: true },
    photos_allowed: { type: Boolean, required: true },
    photos_message: { type: String, default: null },
    photos: { type: Array, required: true },
})

const page = usePage()
const membershipError = computed(() => page.props.errors?.membership)

const notes = useForm({
    notes: props.membership.notes ?? '',
})

function saveNotes() {
    notes.put(`/manage/groups/${props.group.id}/candidates/${props.profile.id}/notes`, {
        preserveScroll: true,
    })
}

function remove() {
    if (window.confirm(`Remove ${props.profile.display_name} from ${props.group.name}?`)) {
        router.delete(`/manage/groups/${props.group.id}/candidates/${props.profile.id}`)
    }
}
</script>

<template>
    <Head :title="profile.display_name" />

    <GroupAdminLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">{{ group.name }}</p>
            <h1>{{ profile.display_name }}</h1>
            <p class="meta">Membership: {{ membership.label }}</p>
            <small v-if="membershipError">{{ membershipError }}</small>

            <div class="actions">
                <Link
                    v-if="membership.status === 'pending'"
                    :href="`/manage/groups/${group.id}/candidates/${profile.id}/approve`"
                    method="put"
                    as="button"
                    class="btn btn-primary"
                >
                    Approve
                </Link>
                <Link
                    v-if="membership.status === 'active'"
                    :href="`/manage/groups/${group.id}/candidates/${profile.id}/suspend`"
                    method="put"
                    as="button"
                    class="btn btn-ghost"
                >
                    Suspend
                </Link>
                <Link
                    v-if="membership.status === 'inactive'"
                    :href="`/manage/groups/${group.id}/candidates/${profile.id}/reactivate`"
                    method="put"
                    as="button"
                    class="btn btn-primary"
                >
                    Reactivate
                </Link>
                <button class="btn btn-danger" type="button" @click="remove">Remove from group</button>
                <Link :href="`/manage/groups/${group.id}/candidates`" class="link-quiet">Back to candidates</Link>
            </div>
        </section>

        <section class="panel panel-wide section-gap">
            <h2>Photos</h2>
            <p v-if="photos_message" class="meta">{{ photos_message }}</p>
            <div v-if="photos_allowed && photos.length" class="photo-grid">
                <figure v-for="photo in photos" :key="photo.id">
                    <img :src="photo.url" :alt="`${profile.display_name} photo, ${photo.year}`">
                    <figcaption class="meta">{{ photo.year }}<span v-if="photo.is_main"> · Main</span></figcaption>
                </figure>
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

        <section class="panel panel-wide section-gap">
            <h2>Group notes</h2>
            <p v-if="membership.notes" class="lede">{{ membership.notes }}</p>
            <p v-else class="meta">No notes for this candidate in {{ group.name }} yet.</p>

            <form @submit.prevent="saveNotes">
                <label class="field">
                    <span>{{ membership.notes ? 'Edit notes' : 'Add notes' }}</span>
                    <textarea v-model="notes.notes" required maxlength="1000" rows="4" />
                    <small v-if="notes.errors.notes">{{ notes.errors.notes }}</small>
                </label>
                <button class="btn btn-primary" type="submit" :disabled="notes.processing">
                    {{ membership.notes ? 'Save notes' : 'Add notes' }}
                </button>
            </form>
        </section>
    </GroupAdminLayout>
</template>
