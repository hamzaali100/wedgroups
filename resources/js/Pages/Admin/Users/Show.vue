<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    account: { type: Object, required: true },
    roles: { type: Object, required: true },
})

const roleForm = useForm({
    role: props.account.role,
})

const passwordForm = useForm({
    password: '',
    password_confirmation: '',
})

function updateRole() {
    roleForm.put(`/admin/users/${props.account.id}/role`)
}

function resetPassword() {
    passwordForm.put(`/admin/users/${props.account.id}/password`, {
        onSuccess: () => passwordForm.reset(),
    })
}
</script>

<template>
    <Head :title="account.name" />

    <AdminLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">User account</p>
            <div class="section-head">
                <h1>{{ account.name }}</h1>
                <Link :href="`/admin/users/${account.id}/edit`" class="btn btn-ghost">Edit details</Link>
            </div>

            <dl class="detail-list section-gap">
                <div>
                    <dt>Email</dt>
                    <dd>{{ account.email }}</dd>
                </div>
                <div>
                    <dt>Role</dt>
                    <dd>{{ account.role_label }}</dd>
                </div>
                <div>
                    <dt>Status</dt>
                    <dd>{{ account.status_label }}</dd>
                </div>
                <div>
                    <dt>Created</dt>
                    <dd>{{ account.created_at }}</dd>
                </div>
                <template v-if="account.candidate">
                    <div>
                        <dt>Display name</dt>
                        <dd>{{ account.candidate.display_name }}</dd>
                    </div>
                    <div>
                        <dt>Real name</dt>
                        <dd>{{ account.candidate.real_name }}</dd>
                    </div>
                    <div>
                        <dt>Profile status</dt>
                        <dd>{{ account.candidate.profile_status }}</dd>
                    </div>
                    <div>
                        <dt>Visibility</dt>
                        <dd>{{ account.candidate.profile_visibility }}</dd>
                    </div>
                </template>
            </dl>

            <div class="actions section-gap">
                <Link v-if="account.group_admin_id" :href="`/admin/group-admins/${account.group_admin_id}`" class="link-quiet">Group admin profile</Link>
                <Link
                    v-if="!account.is_you && account.status !== 'suspended'"
                    :href="`/admin/users/${account.id}/suspend`"
                    method="post"
                    as="button"
                    class="btn btn-danger"
                >
                    Suspend account
                </Link>
                <Link
                    v-if="!account.is_you && account.status !== 'active'"
                    :href="`/admin/users/${account.id}/reactivate`"
                    method="post"
                    as="button"
                    class="btn btn-primary"
                >
                    Reactivate account
                </Link>
            </div>
        </section>

        <section v-if="!account.is_you" class="panel panel-wide section-gap">
            <h2>Change role</h2>
            <form class="form-grid" @submit.prevent="updateRole">
                <label class="field">
                    <span>Role</span>
                    <select v-model="roleForm.role" name="role" required>
                        <option v-for="(label, value) in roles" :key="value" :value="value">{{ label }}</option>
                    </select>
                    <small v-if="roleForm.errors.role">{{ roleForm.errors.role }}</small>
                </label>
                <div class="actions">
                    <button class="btn btn-primary" type="submit" :disabled="roleForm.processing">Save role</button>
                </div>
            </form>
        </section>

        <section v-if="!account.is_you" class="panel panel-wide section-gap">
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
    </AdminLayout>
</template>
