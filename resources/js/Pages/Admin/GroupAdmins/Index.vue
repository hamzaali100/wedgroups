<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    admins: { type: Object, required: true },
    filters: { type: Object, required: true },
})

const form = useForm({
    search: props.filters.search,
})

function search() {
    form.get('/admin/group-admins', { preserveState: true, replace: true })
}
</script>

<template>
    <Head title="Group admins" />

    <AdminLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Admin</p>
            <div class="section-head">
                <h1>Group admin accounts</h1>
                <Link href="/admin/group-admins/create" class="btn btn-primary">Create group admin</Link>
            </div>

            <form class="actions section-gap" @submit.prevent="search">
                <label class="field">
                    <span>Search name or email</span>
                    <input v-model="form.search" type="search" name="search">
                </label>
                <button class="btn btn-primary" type="submit">Search</button>
            </form>

            <p v-if="admins.data.length === 0" class="meta section-gap">No group admin accounts match this search.</p>

            <div v-else class="section-gap" style="overflow-x: auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Approval</th>
                            <th>Groups</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="admin in admins.data" :key="admin.id">
                            <td>{{ admin.name }}</td>
                            <td>{{ admin.email }}</td>
                            <td>{{ admin.status_label }}</td>
                            <td>{{ admin.approved ? 'Approved' : 'Waiting' }}</td>
                            <td>{{ admin.groups_count }}</td>
                            <td><Link :href="`/admin/group-admins/${admin.id}`" class="link-quiet">View</Link></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav v-if="admins.links.length > 3" class="pager" aria-label="Pages">
                <template v-for="(link, index) in admins.links" :key="index">
                    <Link v-if="link.url" :href="link.url">{{ link.label.replace(/<[^>]+>/g, '') }}</Link>
                    <span v-else>{{ link.label.replace(/<[^>]+>/g, '') }}</span>
                </template>
            </nav>
        </section>
    </AdminLayout>
</template>
