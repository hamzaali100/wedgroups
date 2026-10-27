<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const success = computed(() => page.props.flash?.success)

const links = [
    ['/profile', 'Profile'],
    ['/candidates', 'Browse'],
    ['/likes', 'Likes'],
    ['/favorites', 'Favorites'],
    ['/passed', 'Passed'],
    ['/groups', 'Groups'],
    ['/account', 'Account'],
]

function current(path) {
    const url = page.url.split('?')[0]

    if (path === '/profile') {
        return url === '/profile' || url.startsWith('/profile/')
    }

    return url === path || url.startsWith(`${path}/`)
}
</script>

<template>
    <div class="app-shell">
        <header class="site-header">
            <div class="header-inner">
                <Link href="/" class="logo">
                    <span class="logo-mark">WG</span>
                    WedGroups
                </Link>
                <Link href="/logout" method="post" as="button" class="btn btn-ghost">Log out</Link>
            </div>
            <nav class="header-inner subnav" aria-label="Candidate">
                <Link
                    v-for="[href, label] in links"
                    :key="href"
                    :href="href"
                    :aria-current="current(href) ? 'page' : undefined"
                >
                    {{ label }}
                </Link>
            </nav>
        </header>

        <main class="wrap app-section">
            <p v-if="success" class="notice" role="status">{{ success }}</p>
            <slot />
        </main>
    </div>
</template>
