<script setup>
import PhotoCropper from '@/Components/PhotoCropper.vue'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, reactive, ref, watch } from 'vue'

const props = defineProps({
    photos: { type: Array, required: true },
    photosBlurred: { type: Boolean, required: true },
    maxPhotos: { type: Number, required: true },
    minYear: { type: Number, required: true },
    maxYear: { type: Number, required: true },
})

const form = useForm({
    photo: null,
    photo_year: props.maxYear,
    blur: false,
    crop_x: null,
    crop_y: null,
    crop_width: null,
    crop_height: null,
})

const blurForm = useForm({
    photos_blurred: props.photosBlurred ? 1 : 0,
})

const fileInput = ref(null)
const replaceFile = ref(null)
const replacingId = ref(null)
const years = reactive({})
const blurs = reactive({})

const replaceForm = useForm({
    photo: null,
    photo_year: props.maxYear,
    blur: false,
    crop_x: null,
    crop_y: null,
    crop_width: null,
    crop_height: null,
})

const canUpload = computed(() => props.photos.length < props.maxPhotos)
const cropReady = computed(() => (form.crop_width ?? 0) >= 100 && (form.crop_height ?? 0) >= 100)
const replaceReady = computed(() => (replaceForm.crop_width ?? 0) >= 100 && (replaceForm.crop_height ?? 0) >= 100)

function remember(photos) {
    for (const photo of photos) {
        years[photo.id] = photo.year
        blurs[photo.id] = Boolean(photo.blurred_url)
    }
}

remember(props.photos)
watch(() => props.photos, remember)

function applyCrop(target, crop) {
    target.crop_x = crop.x
    target.crop_y = crop.y
    target.crop_width = crop.width
    target.crop_height = crop.height
}

function onPhoto(event) {
    form.photo = event.target.files?.[0] ?? null
    form.crop_x = null
    form.crop_y = null
    form.crop_width = null
    form.crop_height = null
}

function upload() {
    form.post('/profile/photos', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset('photo', 'blur', 'crop_x', 'crop_y', 'crop_width', 'crop_height')
            form.photo_year = props.maxYear
            if (fileInput.value) {
                fileInput.value.value = ''
            }
        },
    })
}

function saveBlur() {
    blurForm.put('/profile/photos/blur', { preserveScroll: true })
}

function move(index, direction) {
    const next = props.photos.slice()
    const target = index + direction
    if (target < 0 || target >= next.length) {
        return
    }
    const [item] = next.splice(index, 1)
    next.splice(target, 0, item)
    router.post('/profile/photos/order', {
        order: next.map((photo) => photo.id),
    }, { preserveScroll: true })
}

function makeMain(photo) {
    router.post(`/profile/photos/${photo.id}/main`, {}, { preserveScroll: true })
}

function remove(photo) {
    if (!window.confirm('Delete this photo?')) {
        return
    }
    router.delete(`/profile/photos/${photo.id}`, { preserveScroll: true })
}

function onReplaceFile(photo, event) {
    replacingId.value = photo.id
    replaceFile.value = event.target.files?.[0] ?? null
    replaceForm.photo = replaceFile.value
    replaceForm.crop_width = null
    replaceForm.crop_height = null
}

function submitReplace(photo) {
    replacingId.value = photo.id
    replaceForm.photo_year = years[photo.id]
    replaceForm.blur = blurs[photo.id]
    replaceForm.post(`/profile/photos/${photo.id}/replace`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            replaceFile.value = null
            replaceForm.reset('photo', 'crop_x', 'crop_y', 'crop_width', 'crop_height')
        },
    })
}
</script>

<template>
    <Head title="Photos" />

    <CandidateLayout>
        <section class="panel panel-wide">
            <p class="eyebrow">Photos</p>
            <h1>Your photos</h1>
            <p class="lede">Crop each photo before it is saved. Photos are resized and stored as smaller JPEG files. A new photo stays hidden until an admin approves it.</p>

            <form class="stack" @submit.prevent="saveBlur">
                <label class="field">
                    <span>Photos other people see</span>
                    <select v-model="blurForm.photos_blurred">
                        <option :value="0">Clear</option>
                        <option :value="1">Blurred</option>
                    </select>
                    <small v-if="blurForm.errors.photos_blurred">{{ blurForm.errors.photos_blurred }}</small>
                </label>
                <p class="meta">Blurred keeps a softened copy for other people. You still see the clear photo here.</p>
                <button class="btn btn-primary" type="submit" :disabled="blurForm.processing">Save photo visibility</button>
            </form>
        </section>

        <section v-if="canUpload" class="panel panel-wide section-gap">
            <h2>Upload a photo</h2>
            <form class="stack" @submit.prevent="upload">
                <label class="field">
                    <span>Photo</span>
                    <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" required @change="onPhoto">
                    <small v-if="form.errors.photo">{{ form.errors.photo }}</small>
                </label>

                <PhotoCropper
                    v-if="form.photo"
                    :file="form.photo"
                    :blurred="form.blur"
                    @change="applyCrop(form, $event)"
                />
                <small v-if="form.errors.crop_width || form.errors.crop_x">{{ form.errors.crop_width || form.errors.crop_x }}</small>

                <label class="field">
                    <span>Year of the photo</span>
                    <input v-model="form.photo_year" type="number" :min="minYear" :max="maxYear" required>
                    <small v-if="form.errors.photo_year">{{ form.errors.photo_year }}</small>
                </label>

                <label class="check">
                    <input v-model="form.blur" type="checkbox">
                    <span>Blur this photo before saving</span>
                </label>
                <p class="meta">The clear crop is kept, and a blurred copy is saved as well.</p>

                <button class="btn btn-primary" type="submit" :disabled="form.processing || !cropReady">Save photo</button>
            </form>
        </section>

        <p v-else class="meta section-gap">You can keep up to {{ maxPhotos }} photos. Delete one before uploading another.</p>

        <section class="section-gap">
            <h2 v-if="photos.length === 0" class="meta">No photos yet.</h2>
            <div v-else class="photo-manager">
                <article v-for="(photo, index) in photos" :key="photo.id" class="photo-card">
                    <img :src="photo.url" :alt="`Photo from ${photo.year}`">
                    <img v-if="photo.blurred_url" :src="photo.blurred_url" alt="Blurred copy">
                    <p class="meta">
                        {{ photo.year }} · {{ photo.review_label }}
                        <span v-if="photo.is_main"> · Main</span>
                        <span v-if="photo.blurred_url"> · Blurred copy saved</span>
                        <span v-if="photo.approved_by"> · Approved by {{ photo.approved_by }}<span v-if="photo.approved_at"> on {{ photo.approved_at }}</span></span>
                    </p>
                    <div class="photo-actions">
                        <button class="btn btn-ghost" type="button" :disabled="index === 0" @click="move(index, -1)">Earlier</button>
                        <button class="btn btn-ghost" type="button" :disabled="index === photos.length - 1" @click="move(index, 1)">Later</button>
                        <button v-if="!photo.is_main" class="btn btn-ghost" type="button" @click="makeMain(photo)">Set as main</button>
                        <button class="btn btn-danger" type="button" @click="remove(photo)">Delete</button>
                    </div>

                    <form class="stack" @submit.prevent="submitReplace(photo)">
                        <label class="field">
                            <span>Replace photo</span>
                            <input type="file" accept="image/jpeg,image/png,image/webp" @change="onReplaceFile(photo, $event)">
                            <small v-if="replacingId === photo.id && replaceForm.errors.photo">{{ replaceForm.errors.photo }}</small>
                        </label>
                        <PhotoCropper
                            v-if="replacingId === photo.id && replaceFile"
                            :file="replaceFile"
                            :blurred="blurs[photo.id]"
                            @change="applyCrop(replaceForm, $event)"
                        />
                        <label class="field">
                            <span>Year</span>
                            <input v-model.number="years[photo.id]" type="number" :min="minYear" :max="maxYear" required>
                        </label>
                        <label class="check">
                            <input v-model="blurs[photo.id]" type="checkbox">
                            <span>Blur this photo</span>
                        </label>
                        <button class="btn btn-primary" type="submit" :disabled="replaceForm.processing || replacingId !== photo.id || !replaceReady">Replace photo</button>
                    </form>
                </article>
            </div>
        </section>
    </CandidateLayout>
</template>
