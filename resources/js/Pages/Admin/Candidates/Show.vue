<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    candidateId: { type: Number, required: true },
    account: { type: Object, required: true },
    profile: { type: Object, required: true },
    photos: { type: Array, required: true },
    groups: { type: Array, required: true },
    available_groups: { type: Array, required: true },
})

const rejectForm = useForm({
    rejection_reason: '',
})

const groupForm = useForm({
    group_id: props.available_groups[0]?.id ?? '',
    status: 'pending',
})

function reject() {
    rejectForm.post(`/admin/candidates/${props.candidateId}/reject`)
}

function addGroup() {
    groupForm.post(`/admin/candidates/${props.candidateId}/groups`)
}

function removeProfile() {
    if (window.confirm('Delete this candidate profile? The login account will remain.')) {
        router.delete(`/admin/candidates/${props.candidateId}`)
    }
}
</script>

<template>
    <Head :title="profile.display_name" />

    <AdminLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Candidate</p>
            <div class="section-head">
                <h1>{{ profile.display_name }}</h1>
                <Link :href="`/admin/candidates/${candidateId}/edit`" class="btn btn-ghost">Edit profile</Link>
            </div>
            <p class="lede">{{ profile.real_name }} · {{ account.email }}</p>
            <dl class="detail-list">
                <div>
                    <dt>Account</dt>
                    <dd>{{ account.account_status }}</dd>
                </div>
                <div>
                    <dt>Review</dt>
                    <dd>{{ account.review_label }}</dd>
                </div>
                <div v-if="account.rejection_reason">
                    <dt>Rejection reason</dt>
                    <dd>{{ account.rejection_reason }}</dd>
                </div>
            </dl>
            <div class="actions section-gap">
                <Link v-if="account.review_status !== 'approved'" :href="`/admin/candidates/${candidateId}/approve`" method="post" as="button" class="btn btn-primary">Approve profile</Link>
                <Link v-if="account.review_status === 'pending' || account.review_status === 'approved'" :href="`/admin/candidates/${candidateId}/suspend`" method="post" as="button" class="btn btn-danger">Suspend profile</Link>
                <Link v-if="account.review_status === 'suspended'" :href="`/admin/candidates/${candidateId}/reactivate`" method="post" as="button" class="btn btn-primary">Reactivate profile</Link>
                <button type="button" class="btn btn-danger" @click="removeProfile">Delete profile</button>
            </div>
        </section>

        <section v-if="account.review_status !== 'rejected'" class="panel panel-wide section-gap">
            <h2>Reject profile</h2>
            <form class="stack" @submit.prevent="reject">
                <label class="field">
                    <span>Rejection reason</span>
                    <textarea v-model="rejectForm.rejection_reason" name="rejection_reason" rows="3" maxlength="500" required></textarea>
                    <small v-if="rejectForm.errors.rejection_reason">{{ rejectForm.errors.rejection_reason }}</small>
                </label>
                <button class="btn btn-danger" type="submit" :disabled="rejectForm.processing">Reject profile</button>
            </form>
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
            <h2>Photos</h2>
            <p v-if="photos.length === 0" class="meta">This candidate has not added photos.</p>
            <div v-else class="photo-grid">
                <figure v-for="photo in photos" :key="photo.id" class="stack">
                    <img :src="photo.url" :alt="`${profile.display_name} photo`">
                    <figcaption class="meta">{{ photo.year }} · {{ photo.review_label }}<span v-if="photo.is_main"> · Main</span></figcaption>
                    <Link v-if="photo.review_status !== 'approved'" :href="`/admin/candidates/${candidateId}/photos/${photo.id}/approve`" method="post" as="button" class="btn btn-primary">Approve photo</Link>
                </figure>
            </div>
        </section>

        <section class="panel panel-wide section-gap">
            <h2>Groups</h2>
            <p v-if="groups.length === 0" class="meta">This candidate is not in a group.</p>
            <ul v-else class="stack">
                <li v-for="group in groups" :key="group.id" class="actions">
                    <span>{{ group.name }} · {{ group.status_label }} · {{ group.membership_label }}</span>
                    <Link :href="`/admin/candidates/${candidateId}/groups/${group.id}`" method="delete" as="button" class="btn btn-danger">Remove</Link>
                </li>
            </ul>
            <form v-if="available_groups.length" class="form-grid section-gap" @submit.prevent="addGroup">
                <label class="field">
                    <span>Add to a group</span>
                    <select v-model="groupForm.group_id" name="group_id" required>
                        <option v-for="group in available_groups" :key="group.id" :value="group.id">{{ group.name }}</option>
                    </select>
                    <small v-if="groupForm.errors.group_id">{{ groupForm.errors.group_id }}</small>
                </label>
                <label class="field">
                    <span>Membership</span>
                    <select v-model="groupForm.status" name="status">
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                    </select>
                </label>
                <div class="actions">
                    <button class="btn btn-primary" type="submit" :disabled="groupForm.processing">Add to group</button>
                </div>
            </form>
        </section>
    </AdminLayout>
</template>
