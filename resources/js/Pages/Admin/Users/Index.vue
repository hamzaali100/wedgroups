<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    users: { type: Object, required: true },
    filters: { type: Object, required: true },
    roles: { type: Object, required: true },
})

const form = useForm({
    search: props.filters.search,
    role: props.filters.role,
})

function search() {
    form.get('/admin/users', { preserveState: true, replace: true })
}
</script>

<template>
    <Head title="Users" />

    <AdminLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Admin</p>
            <div class="section-head">
                <h1>User accounts</h1>
                <Link href="/admin/users/create" class="btn btn-primary">Create user</Link>
            </div>

            <form class="form-grid section-gap" @submit.prevent="search">
                <label class="field">
                    <span>Search name or email</span>
                    <input v-model="form.search" type="search" name="search">
                </label>
                <label class="field">
                    <span>Role</span>
                    <select v-model="form.role" name="role">
                        <option value="">All roles</option>
                        <option v-for="(label, value) in roles" :key="value" :value="value">{{ label }}</option>
                    </select>
                </label>
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>

            <p v-if="users.data.length === 0" class="meta section-gap">No accounts match this search.</p>

            <div v-else class="section-gap" style="overflow-x: auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="account in users.data" :key="account.id">
                            <td>{{ account.name }}</td>
                            <td>{{ account.email }}</td>
                            <td>{{ account.role_label }}</td>
                            <td>{{ account.status_label }}</td>
                            <td><Link :href="`/admin/users/${account.id}`" class="link-quiet">View</Link></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav v-if="users.links.length > 3" class="pager" aria-label="Pages">
                <template v-for="(link, index) in users.links" :key="index">
                    <Link v-if="link.url" :href="link.url" :aria-current="link.active ? 'page' : undefined">{{ link.label.replace(/<[^>]+>/g, '') }}</Link>
                    <span v-else>{{ link.label.replace(/<[^>]+>/g, '') }}</span>
                </template>
            </nav>
        </section>
    </AdminLayout>
</template>
