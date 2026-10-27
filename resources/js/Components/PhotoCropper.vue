<script setup>
import { onBeforeUnmount, ref, watch } from 'vue'

const props = defineProps({
    file: { type: File, required: true },
    blurred: { type: Boolean, default: false },
})

const emit = defineEmits(['change'])

const image = ref(null)
const url = ref('')
const box = ref({ x: 0, y: 0, width: 0, height: 0 })
const ready = ref(false)

const ratio = 3 / 4
let drag = null

watch(() => props.file, (file) => {
    ready.value = false
    if (url.value) {
        URL.revokeObjectURL(url.value)
    }
    url.value = URL.createObjectURL(file)
}, { immediate: true })

onBeforeUnmount(() => {
    if (url.value) {
        URL.revokeObjectURL(url.value)
    }
    stopDrag()
})

function onLoad() {
    const img = image.value
    if (!img?.naturalWidth || !img.clientWidth) {
        return
    }

    let width = img.clientWidth
    let height = width / ratio
    if (height > img.clientHeight) {
        height = img.clientHeight
        width = height * ratio
    }

    box.value = {
        x: (img.clientWidth - width) / 2,
        y: (img.clientHeight - height) / 2,
        width,
        height,
    }
    ready.value = true
    publish()
}

function publish() {
    const img = image.value
    if (!img?.naturalWidth || !img.clientWidth || !img.clientHeight) {
        return
    }

    const scaleX = img.naturalWidth / img.clientWidth
    const scaleY = img.naturalHeight / img.clientHeight

    emit('change', {
        x: Math.max(0, Math.round(box.value.x * scaleX)),
        y: Math.max(0, Math.round(box.value.y * scaleY)),
        width: Math.max(1, Math.round(box.value.width * scaleX)),
        height: Math.max(1, Math.round(box.value.height * scaleY)),
    })
}

function limits() {
    const img = image.value
    const scaleX = img.naturalWidth / img.clientWidth
    const scaleY = img.naturalHeight / img.clientHeight

    return {
        width: Math.min(img.clientWidth, 100 / scaleX),
        height: Math.min(img.clientHeight, 100 / scaleY),
    }
}

function startDrag(mode, event) {
    event.preventDefault()
    drag = {
        mode,
        startX: event.clientX,
        startY: event.clientY,
        origin: { ...box.value },
    }
    window.addEventListener('pointermove', onDrag)
    window.addEventListener('pointerup', stopDrag)
}

function onDrag(event) {
    if (!drag || !image.value) {
        return
    }

    const img = image.value
    const dx = event.clientX - drag.startX
    const dy = event.clientY - drag.startY

    if (drag.mode === 'move') {
        box.value = {
            ...box.value,
            x: Math.min(Math.max(0, drag.origin.x + dx), img.clientWidth - box.value.width),
            y: Math.min(Math.max(0, drag.origin.y + dy), img.clientHeight - box.value.height),
        }
        publish()
        return
    }

    const min = limits()
    let width = Math.max(min.width, drag.origin.width + dx)
    let height = width / ratio

    if (drag.origin.x + width > img.clientWidth) {
        width = img.clientWidth - drag.origin.x
        height = width / ratio
    }

    if (drag.origin.y + height > img.clientHeight) {
        height = img.clientHeight - drag.origin.y
        width = height * ratio
    }

    if (width < min.width || height < min.height) {
        return
    }

    box.value = {
        x: drag.origin.x,
        y: drag.origin.y,
        width,
        height,
    }
    publish()
}

function stopDrag() {
    drag = null
    window.removeEventListener('pointermove', onDrag)
    window.removeEventListener('pointerup', stopDrag)
}

function nudge(event) {
    const img = image.value
    if (!img) {
        return
    }

    const step = event.shiftKey ? 12 : 4
    const next = { ...box.value }

    if (event.key === 'ArrowLeft') {
        next.x = Math.max(0, next.x - step)
    } else if (event.key === 'ArrowRight') {
        next.x = Math.min(img.clientWidth - next.width, next.x + step)
    } else if (event.key === 'ArrowUp') {
        next.y = Math.max(0, next.y - step)
    } else if (event.key === 'ArrowDown') {
        next.y = Math.min(img.clientHeight - next.height, next.y + step)
    } else {
        return
    }

    event.preventDefault()
    box.value = next
    publish()
}
</script>

<template>
    <div class="crop-step">
        <p class="meta">Drag the frame to crop. Pull the corner to resize. Arrow keys move the frame.</p>
        <div class="crop-stage">
            <img ref="image" :src="url" alt="Photo to crop" @load="onLoad">
            <div
                v-if="ready"
                class="crop-box"
                :class="{ 'is-blurred': blurred }"
                role="application"
                aria-label="Crop frame"
                tabindex="0"
                :style="{
                    left: `${box.x}px`,
                    top: `${box.y}px`,
                    width: `${box.width}px`,
                    height: `${box.height}px`,
                }"
                @pointerdown="startDrag('move', $event)"
                @keydown="nudge"
            >
                <span
                    class="crop-handle"
                    role="slider"
                    aria-label="Resize crop"
                    aria-valuemin="100"
                    @pointerdown="startDrag('resize', $event)"
                />
            </div>
        </div>
    </div>
</template>
