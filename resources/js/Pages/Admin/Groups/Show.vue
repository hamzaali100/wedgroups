<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    group: { type: Object, required: true },
    admins: { type: Array, required: true },
    available_admins: { type: Array, required: true },
})

const form = useForm({
    group_admin_id: props.available_admins[0]?.id ?? '',
    is_primary: false,
})

function addAdmin() {
    form.post(`/admin/groups/${props.group.id}/admins`)
}

function removeGroup() {
    if (window.confirm('Delete this group and its memberships? Candidate profiles will remain.')) {
        router.delete(`/admin/groups/${props.group.id}`)
    }
}
</script>

<template>
    <Head :title="group.name" />

    <AdminLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Group</p>
            <div class="section-head">
                <h1>{{ group.name }}</h1>
                <Link :href="`/admin/groups/${group.id}/edit`" class="btn btn-ghost">Edit details</Link>
            </div>
            <p>{{ group.description }}</p>
            <dl class="detail-list">
                <div>
                    <dt>Status</dt>
                    <dd>{{ group.status_label }}</dd>
                </div>
                <div>
                    <dt>Candidates</dt>
                    <dd>{{ group.candidate_count }} ({{ group.active_count }} active, {{ group.pending_count }} pending)</dd>
                </div>
                <div>
                    <dt>Photos</dt>
                    <dd>{{ group.male_pics_allowed ? 'Male photos allowed' : 'Male photos hidden' }} · {{ group.female_pics_allowed ? 'Female photos allowed' : 'Female photos hidden' }}</dd>
                </div>
            </dl>
            <div class="actions section-gap">
                <Link v-if="group.status !== 'active'" :href="`/admin/groups/${group.id}/activate`" method="post" as="button" class="btn btn-primary">Activate group</Link>
                <Link v-if="group.status === 'active'" :href="`/admin/groups/${group.id}/deactivate`" method="post" as="button" class="btn btn-danger">Deactivate group</Link>
                <button type="button" class="btn btn-danger" @click="removeGroup">Delete group</button>
            </div>
        </section>

        <section class="panel panel-wide section-gap">
            <h2>Group admins</h2>
            <p v-if="admins.length === 0" class="meta">No admin is assigned to this group.</p>
            <ul v-else class="stack">
                <li v-for="admin in admins" :key="admin.id" class="actions">
                    <span>{{ admin.name }} · {{ admin.email }}<span v-if="admin.is_primary"> · Primary</span></span>
                    <Link :href="`/admin/groups/${group.id}/admins/${admin.id}`" method="delete" as="button" class="btn btn-danger">Remove</Link>
                </li>
            </ul>
            <form v-if="available_admins.length" class="form-grid section-gap" @submit.prevent="addAdmin">
                <label class="field">
                    <span>Add an approved group admin</span>
                    <select v-model="form.group_admin_id" name="group_admin_id" required>
                        <option v-for="admin in available_admins" :key="admin.id" :value="admin.id">{{ admin.name }}</option>
                    </select>
                    <small v-if="form.errors.group_admin_id">{{ form.errors.group_admin_id }}</small>
                </label>
                <label class="check">
                    <input v-model="form.is_primary" type="checkbox">
                    <span>Primary admin for this group</span>
                </label>
                <div class="actions">
                    <button class="btn btn-primary" type="submit" :disabled="form.processing">Add admin</button>
                </div>
            </form>
            <p v-else class="meta section-gap">No other approved group admin is available to add.</p>
        </section>
    </AdminLayout>
</template>
