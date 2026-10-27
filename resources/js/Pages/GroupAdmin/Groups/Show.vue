<script setup>
import GroupAdminLayout from '@/Layouts/GroupAdminLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    group: { type: Object, required: true },
})

const page = usePage()
const statusError = computed(() => page.props.errors?.status)

function deactivate() {
    if (window.confirm(`Deactivate ${props.group.name}? Members will no longer see each other.`)) {
        router.put(`/manage/groups/${props.group.id}/deactivate`)
    }
}
</script>

<template>
    <Head :title="group.name" />

    <GroupAdminLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Group</p>
            <h1>{{ group.name }}</h1>
            <p class="lede">{{ group.description }}</p>

            <dl class="detail-list">
                <div>
                    <dt>Status</dt>
                    <dd>{{ group.status_label }}</dd>
                </div>
                <div>
                    <dt>Your role</dt>
                    <dd>{{ group.is_primary ? 'Primary admin' : 'Admin' }}</dd>
                </div>
                <div>
                    <dt>Admins</dt>
                    <dd>{{ group.admin_count }}</dd>
                </div>
                <div>
                    <dt>Candidates</dt>
                    <dd>{{ group.candidate_count }}</dd>
                </div>
                <div>
                    <dt>Pending approval</dt>
                    <dd>{{ group.pending_count }}</dd>
                </div>
                <div>
                    <dt>Active</dt>
                    <dd>{{ group.active_count }}</dd>
                </div>
                <div>
                    <dt>Suspended</dt>
                    <dd>{{ group.suspended_count }}</dd>
                </div>
                <div>
                    <dt>Male photos</dt>
                    <dd>{{ group.male_pics_allowed ? 'Allowed' : 'Not allowed' }}</dd>
                </div>
                <div>
                    <dt>Female photos</dt>
                    <dd>{{ group.female_pics_allowed ? 'Allowed' : 'Not allowed' }}</dd>
                </div>
            </dl>

            <p v-if="group.status === 'suspended'" class="meta">A platform administrator suspended this group.</p>
            <small v-if="statusError">{{ statusError }}</small>

            <div class="actions">
                <Link :href="`/manage/groups/${group.id}/edit`" class="btn btn-primary">Edit details</Link>
                <Link
                    v-if="group.status === 'inactive'"
                    :href="`/manage/groups/${group.id}/activate`"
                    method="put"
                    as="button"
                    class="btn btn-primary"
                >
                    Activate group
                </Link>
                <button v-if="group.status === 'active'" class="btn btn-danger" type="button" @click="deactivate">
                    Deactivate group
                </button>
                <Link :href="`/manage/groups/${group.id}/admins`" class="btn btn-ghost">View admins</Link>
                <Link :href="`/manage/groups/${group.id}/candidates`" class="btn btn-ghost">View candidates</Link>
            </div>
        </section>
    </GroupAdminLayout>
</template>
