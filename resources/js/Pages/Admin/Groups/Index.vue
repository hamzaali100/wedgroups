<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    groups: { type: Object, required: true },
    filters: { type: Object, required: true },
})

const form = useForm({
    search: props.filters.search,
})

function search() {
    form.get('/admin/groups', { preserveState: true, replace: true })
}
</script>

<template>
    <Head title="Groups" />

    <AdminLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Admin</p>
            <div class="section-head">
                <h1>Groups</h1>
                <Link href="/admin/groups/create" class="btn btn-primary">Create group</Link>
            </div>

            <form class="actions section-gap" @submit.prevent="search">
                <label class="field">
                    <span>Search name</span>
                    <input v-model="form.search" type="search" name="search">
                </label>
                <button class="btn btn-primary" type="submit">Search</button>
            </form>

            <p v-if="groups.data.length === 0" class="meta section-gap">No groups match this search.</p>

            <div v-else class="section-gap" style="overflow-x: auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Admins</th>
                            <th>Candidates</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="group in groups.data" :key="group.id">
                            <td>{{ group.name }}</td>
                            <td>{{ group.status_label }}</td>
                            <td>{{ group.admin_count }}</td>
                            <td>{{ group.candidate_count }}</td>
                            <td><Link :href="`/admin/groups/${group.id}`" class="link-quiet">View</Link></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AdminLayout>
</template>
