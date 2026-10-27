<script setup>
import GroupAdminLayout from '@/Layouts/GroupAdminLayout.vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    group: { type: Object, required: true },
    admins: { type: Array, required: true },
})

const page = usePage()
const adminError = computed(() => page.props.errors?.admin)

const form = useForm({
    email: '',
})

function addAdmin() {
    form.post(`/manage/groups/${props.group.id}/admins`, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    })
}

function remove(admin) {
    if (window.confirm(`Remove ${admin.name} from ${props.group.name}?`)) {
        router.delete(`/manage/groups/${props.group.id}/admins/${admin.id}`)
    }
}
</script>

<template>
    <Head :title="`${group.name} admins`" />

    <GroupAdminLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">{{ group.name }}</p>
            <h1>Group admins</h1>
            <p class="lede">Add another group admin by the email on their account. You cannot remove yourself.</p>

            <form class="actions" @submit.prevent="addAdmin">
                <label class="field">
                    <span>Admin email</span>
                    <input v-model="form.email" type="email" required maxlength="255" autocomplete="off">
                    <small v-if="form.errors.email">{{ form.errors.email }}</small>
                </label>
                <button class="btn btn-primary" type="submit" :disabled="form.processing">Add admin</button>
            </form>
        </section>

        <small v-if="adminError" class="section-gap">{{ adminError }}</small>

        <div class="person-grid section-gap">
            <article v-for="admin in admins" :key="admin.id" class="person-card">
                <h2>{{ admin.name }}</h2>
                <p>{{ admin.email }}</p>
                <p v-if="admin.phone" class="meta">Phone {{ admin.phone }}</p>
                <p v-if="admin.whatsapp" class="meta">WhatsApp {{ admin.whatsapp }}</p>
                <p class="meta">
                    <span v-if="admin.is_primary">Primary admin</span>
                    <span v-else>Admin</span>
                    <span v-if="admin.is_you"> · You</span>
                </p>
                <button v-if="!admin.is_you" class="btn btn-danger" type="button" @click="remove(admin)">
                    Remove admin
                </button>
            </article>
        </div>

        <p class="section-gap">
            <Link :href="`/manage/groups/${group.id}`" class="link-quiet">Back to group</Link>
        </p>
    </GroupAdminLayout>
</template>
