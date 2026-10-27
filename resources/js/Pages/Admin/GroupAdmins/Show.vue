<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    admin: { type: Object, required: true },
    groups: { type: Array, required: true },
    available_groups: { type: Array, required: true },
    activity: { type: Array, required: true },
})

const assignForm = useForm({
    group_id: props.available_groups[0]?.id ?? '',
    is_primary: false,
})

const passwordForm = useForm({
    password: '',
    password_confirmation: '',
})

function assign() {
    assignForm.post(`/admin/group-admins/${props.admin.id}/groups`)
}

function resetPassword() {
    passwordForm.put(`/admin/users/${props.admin.user_id}/password`, {
        onSuccess: () => passwordForm.reset(),
    })
}
</script>

<template>
    <Head :title="admin.name" />

    <AdminLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Group admin</p>
            <div class="section-head">
                <h1>{{ admin.name }}</h1>
                <Link :href="`/admin/group-admins/${admin.id}/edit`" class="btn btn-ghost">Edit details</Link>
            </div>

            <dl class="detail-list section-gap">
                <div>
                    <dt>Email</dt>
                    <dd>{{ admin.email }}</dd>
                </div>
                <div>
                    <dt>Phone</dt>
                    <dd>{{ admin.phone || '—' }}</dd>
                </div>
                <div>
                    <dt>WhatsApp</dt>
                    <dd>{{ admin.whatsapp || '—' }}</dd>
                </div>
                <div>
                    <dt>Account status</dt>
                    <dd>{{ admin.status_label }}</dd>
                </div>
                <div>
                    <dt>Approval</dt>
                    <dd>{{ admin.approved ? `Approved ${admin.approved_at}` : 'Waiting for approval' }}</dd>
                </div>
                <div v-if="admin.description">
                    <dt>Description</dt>
                    <dd>{{ admin.description }}</dd>
                </div>
            </dl>

            <div class="actions section-gap">
                <Link
                    v-if="!admin.approved"
                    :href="`/admin/group-admins/${admin.id}/approve`"
                    method="post"
                    as="button"
                    class="btn btn-primary"
                >
                    Approve group admin
                </Link>
                <Link
                    v-if="admin.status !== 'suspended'"
                    :href="`/admin/users/${admin.user_id}/suspend`"
                    method="post"
                    as="button"
                    class="btn btn-danger"
                >
                    Suspend account
                </Link>
                <Link
                    v-if="admin.status !== 'active'"
                    :href="`/admin/users/${admin.user_id}/reactivate`"
                    method="post"
                    as="button"
                    class="btn btn-primary"
                >
                    Reactivate account
                </Link>
            </div>
        </section>

        <section class="panel panel-wide section-gap">
            <h2>Groups managed</h2>
            <p v-if="groups.length === 0" class="meta">This group admin is not assigned to a group.</p>
            <ul v-else class="stack">
                <li v-for="group in groups" :key="group.id" class="actions">
                    <span>{{ group.name }} · {{ group.status_label }}<span v-if="group.is_primary"> · Primary</span></span>
                    <Link
                        :href="`/admin/group-admins/${admin.id}/groups/${group.id}`"
                        method="delete"
                        as="button"
                        class="btn btn-danger"
                    >
                        Remove
                    </Link>
                </li>
            </ul>

            <form v-if="admin.approved && available_groups.length" class="form-grid section-gap" @submit.prevent="assign">
                <label class="field">
                    <span>Add to a group</span>
                    <select v-model="assignForm.group_id" name="group_id" required>
                        <option v-for="group in available_groups" :key="group.id" :value="group.id">{{ group.name }}</option>
                    </select>
                    <small v-if="assignForm.errors.group_id">{{ assignForm.errors.group_id }}</small>
                </label>
                <label class="check">
                    <input v-model="assignForm.is_primary" type="checkbox" name="is_primary">
                    <span>Primary admin for this group</span>
                </label>
                <div class="actions">
                    <button class="btn btn-primary" type="submit" :disabled="assignForm.processing">Add to group</button>
                </div>
            </form>
            <p v-else-if="!admin.approved" class="meta section-gap">Approve this account before adding it to a group.</p>
        </section>

        <section class="panel panel-wide section-gap">
            <h2>Reset password</h2>
            <form class="form-grid" @submit.prevent="resetPassword">
                <label class="field">
                    <span>New password</span>
                    <input v-model="passwordForm.password" type="password" name="password" autocomplete="new-password" required>
                    <small v-if="passwordForm.errors.password">{{ passwordForm.errors.password }}</small>
                </label>
                <label class="field">
                    <span>Confirm password</span>
                    <input v-model="passwordForm.password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                </label>
                <div class="actions">
                    <button class="btn btn-primary" type="submit" :disabled="passwordForm.processing">Reset password</button>
                </div>
            </form>
        </section>

        <section class="panel panel-wide section-gap">
            <h2>Activity</h2>
            <ul class="stack">
                <li v-for="(event, index) in activity" :key="index">
                    <span class="meta">{{ event.at }}</span>
                    {{ event.text }}
                </li>
            </ul>
        </section>
    </AdminLayout>
</template>
