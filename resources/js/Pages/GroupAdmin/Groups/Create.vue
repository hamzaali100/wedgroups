<script setup>
import GroupAdminLayout from '@/Layouts/GroupAdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    description: '',
    male_pics_allowed: true,
    female_pics_allowed: false,
})

function submit() {
    form.post('/manage/groups')
}
</script>

<template>
    <Head title="Create group" />

    <GroupAdminLayout>
        <form class="panel" @submit.prevent="submit">
            <p class="eyebrow">Groups</p>
            <h1>Create group</h1>

            <label class="field">
                <span>Name</span>
                <input v-model="form.name" type="text" required maxlength="150">
                <small v-if="form.errors.name">{{ form.errors.name }}</small>
            </label>

            <label class="field">
                <span>Description</span>
                <textarea v-model="form.description" required maxlength="150" rows="3" />
                <small v-if="form.errors.description">{{ form.errors.description }}</small>
            </label>

            <label class="check">
                <input v-model="form.male_pics_allowed" type="checkbox">
                <span>Male members can share photos</span>
            </label>

            <label class="check">
                <input v-model="form.female_pics_allowed" type="checkbox">
                <span>Female members can share photos</span>
            </label>

            <div class="actions">
                <button class="btn btn-primary" type="submit" :disabled="form.processing">Create group</button>
                <Link href="/manage/groups" class="link-quiet">Cancel</Link>
            </div>
        </form>
    </GroupAdminLayout>
</template>
