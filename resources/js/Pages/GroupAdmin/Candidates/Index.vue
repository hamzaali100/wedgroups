<script setup>
import GroupAdminLayout from '@/Layouts/GroupAdminLayout.vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    group: { type: Object, required: true },
    candidates: { type: Object, required: true },
    filters: { type: Object, required: true },
    genders: { type: Object, required: true },
    maritalStatuses: { type: Object, required: true },
    educationLevels: { type: Object, required: true },
    membershipStatuses: { type: Object, required: true },
})

const page = usePage()
const membershipError = computed(() => page.props.errors?.membership)

const search = useForm({
    display_name: props.filters.display_name ?? '',
    gender: props.filters.gender ?? '',
    marital_status: props.filters.marital_status ?? '',
    education_level: props.filters.education_level ?? '',
    membership_status: props.filters.membership_status ?? '',
    age_min: props.filters.age_min ?? '',
    age_max: props.filters.age_max ?? '',
})

const add = useForm({
    email: '',
})

function applyFilters() {
    search.get(`/manage/groups/${props.group.id}/candidates`, {
        preserveState: true,
        replace: true,
    })
}

function addCandidate() {
    add.post(`/manage/groups/${props.group.id}/candidates`, {
        preserveScroll: true,
        onSuccess: () => add.reset(),
    })
}

function remove(candidate) {
    if (window.confirm(`Remove ${candidate.display_name} from ${props.group.name}?`)) {
        router.delete(`/manage/groups/${props.group.id}/candidates/${candidate.id}`)
    }
}
</script>

<template>
    <Head :title="`${group.name} candidates`" />

    <GroupAdminLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">{{ group.name }}</p>
            <h1>Group candidates</h1>
            <p class="lede">Search and filter people in this group. Added candidates stay pending until you approve them.</p>

            <form class="form-grid" @submit.prevent="applyFilters">
                <label class="field">
                    <span>Name</span>
                    <input v-model="search.display_name" type="search" maxlength="100">
                </label>
                <label class="field">
                    <span>Gender</span>
                    <select v-model="search.gender">
                        <option value="">Any</option>
                        <option v-for="(label, value) in genders" :key="value" :value="value">{{ label }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>Marital status</span>
                    <select v-model="search.marital_status">
                        <option value="">Any</option>
                        <option v-for="(label, value) in maritalStatuses" :key="value" :value="value">{{ label }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>Education</span>
                    <select v-model="search.education_level">
                        <option value="">Any</option>
                        <option v-for="(label, value) in educationLevels" :key="value" :value="value">{{ label }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>Membership</span>
                    <select v-model="search.membership_status">
                        <option value="">Any</option>
                        <option v-for="(label, value) in membershipStatuses" :key="value" :value="value">{{ label }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>Minimum age</span>
                    <input v-model="search.age_min" type="number" min="18" max="80">
                    <small v-if="search.errors.age_min">{{ search.errors.age_min }}</small>
                </label>
                <label class="field">
                    <span>Maximum age</span>
                    <input v-model="search.age_max" type="number" min="18" max="80">
                    <small v-if="search.errors.age_max">{{ search.errors.age_max }}</small>
                </label>
                <div class="actions field-span">
                    <button class="btn btn-primary" type="submit" :disabled="search.processing">Search and filter</button>
                    <Link :href="`/manage/groups/${group.id}/candidates`" class="link-quiet">Clear</Link>
                </div>
            </form>
        </section>

        <form class="panel panel-wide section-gap" @submit.prevent="addCandidate">
            <h2>Add candidate</h2>
            <label class="field">
                <span>Candidate email</span>
                <input v-model="add.email" type="email" required maxlength="255" autocomplete="off">
                <small v-if="add.errors.email">{{ add.errors.email }}</small>
            </label>
            <button class="btn btn-primary" type="submit" :disabled="add.processing">Add to group</button>
        </form>

        <small v-if="membershipError" class="section-gap">{{ membershipError }}</small>
        <p v-if="candidates.data.length === 0" class="meta section-gap">No candidates match.</p>

        <div v-else class="person-grid section-gap">
            <article v-for="candidate in candidates.data" :key="candidate.id" class="person-card">
                <h2>{{ candidate.display_name }}</h2>
                <p>{{ candidate.age }} years · {{ candidate.gender }} · {{ candidate.height }} cm</p>
                <p class="meta">{{ candidate.membership_label }}<span v-if="candidate.location"> · {{ candidate.location }}</span></p>
                <div class="actions">
                    <Link :href="`/manage/groups/${group.id}/candidates/${candidate.id}`" class="link-quiet">View profile</Link>
                    <Link
                        v-if="candidate.membership_status === 'pending'"
                        :href="`/manage/groups/${group.id}/candidates/${candidate.id}/approve`"
                        method="put"
                        as="button"
                        class="btn btn-primary"
                    >
                        Approve
                    </Link>
                    <Link
                        v-if="candidate.membership_status === 'active'"
                        :href="`/manage/groups/${group.id}/candidates/${candidate.id}/suspend`"
                        method="put"
                        as="button"
                        class="btn btn-ghost"
                    >
                        Suspend
                    </Link>
                    <Link
                        v-if="candidate.membership_status === 'inactive'"
                        :href="`/manage/groups/${group.id}/candidates/${candidate.id}/reactivate`"
                        method="put"
                        as="button"
                        class="btn btn-primary"
                    >
                        Reactivate
                    </Link>
                    <button class="btn btn-danger" type="button" @click="remove(candidate)">Remove</button>
                </div>
            </article>
        </div>

        <div v-if="candidates.prev_page_url || candidates.next_page_url" class="actions section-gap">
            <Link v-if="candidates.prev_page_url" :href="candidates.prev_page_url" class="btn btn-ghost">Previous</Link>
            <Link v-if="candidates.next_page_url" :href="candidates.next_page_url" class="btn btn-ghost">Next</Link>
        </div>

        <p class="section-gap">
            <Link :href="`/manage/groups/${group.id}`" class="link-quiet">Back to group</Link>
        </p>
    </GroupAdminLayout>
</template>
