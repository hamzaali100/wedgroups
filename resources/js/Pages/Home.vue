<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)

const menuOpen = ref(false)
const year = new Date().getFullYear()
const accountHref = computed(() => {
    if (user.value?.role === 'admin') {
        return '/admin/users'
    }

    if (user.value?.role === 'group_admin') {
        return '/manage/groups'
    }

    return '/profile'
})
const accountLabel = computed(() => {
    if (user.value?.role === 'admin') {
        return 'Admin'
    }

    if (user.value?.role === 'group_admin') {
        return 'Your groups'
    }

    return 'Your profile'
})

function closeMenu() {
    menuOpen.value = false
}
</script>

<template>
    <Head>
        <title>WedGroups — Matchmaking that stays inside your group</title>
        <meta
            name="description"
            content="Create a profile on WedGroups and stay visible only in the private groups you join. Group admins decide who belongs. Matchmaking without a public catalog."
        >
    </Head>

    <header class="site-header">
        <div class="header-inner">
            <a href="/" class="logo">
                <span class="logo-mark">WG</span>
                WedGroups
            </a>

            <nav class="nav-desktop" aria-label="Primary">
                <a href="#privacy">Privacy</a>
                <a href="#how-it-works">How it works</a>
                <a href="#stories">Stories</a>
            </nav>

            <div class="header-actions">
                <Link v-if="user" :href="accountHref" class="btn btn-primary">{{ accountLabel }}</Link>
                <template v-else>
                    <Link href="/login" class="btn btn-ghost">Log in</Link>
                    <Link href="/register" class="btn btn-primary">Create a profile</Link>
                </template>
            </div>

            <button
                type="button"
                class="menu-toggle"
                :aria-expanded="menuOpen ? 'true' : 'false'"
                aria-controls="mobile-nav"
                @click="menuOpen = !menuOpen"
            >
                <span class="sr-only">Open menu</span>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M4 6h12M4 10h12M4 14h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                </svg>
            </button>
        </div>

        <nav id="mobile-nav" class="nav-mobile" :class="{ 'is-open': menuOpen }" aria-label="Mobile">
            <div class="nav-mobile-links">
                <a href="#privacy" @click="closeMenu">Privacy</a>
                <a href="#how-it-works" @click="closeMenu">How it works</a>
                <a href="#stories" @click="closeMenu">Stories</a>
                <Link v-if="user" :href="accountHref" class="btn btn-primary" @click="closeMenu">{{ accountLabel }}</Link>
                <Link v-else href="/register" class="btn btn-primary" @click="closeMenu">Create a profile</Link>
            </div>
        </nav>
    </header>

    <main>
        <section class="wrap hero">
            <div>
                <p class="eyebrow">Private matchmaking</p>
                <h1>Your profile is yours. Visibility is by invitation only.</h1>
                <p class="lede">
                    WedGroups is matchmaking without a public feed. You create a profile, then join closed groups — doctors only, bankers only, armed forces only — and appear only to members of those groups.
                </p>
                <div class="hero-actions">
                    <Link v-if="user" :href="accountHref" class="btn btn-primary">{{ accountLabel }}</Link>
                    <Link v-else href="/register" class="btn btn-primary">Create your profile</Link>
                    <a href="#how-it-works" class="link-quiet">See how it works</a>
                </div>
                <dl class="stats">
                    <div>
                        <dt>Profiles</dt>
                        <dd>Private</dd>
                    </div>
                    <div>
                        <dt>Discovery</dt>
                        <dd>In-group</dd>
                    </div>
                    <div>
                        <dt>Access</dt>
                        <dd>Admin-led</dd>
                    </div>
                </dl>
            </div>

            <aside class="preview" aria-label="Example- Doctors only group">
                <div class="preview-top">
                    <div>
                        <p class="eyebrow">Private group</p>
                        <h2>Example- Doctors only group</h2>
                    </div>
                    <span class="chip">Admin managed</span>
                </div>
                <p class="privacy-note">This is one example. Bankers only, Armed Forces only, and other closed groups work the same way: members here see each other, and nobody outside this group does.</p>
                <ul class="preview-list">
                    <li>
                        <div class="member">
                            <span class="avatar" aria-hidden="true">SA</span>
                            <div>
                                <p>Dr. Sara Ahmed</p>
                                <p class="muted">Cardiology · member</p>
                            </div>
                        </div>
                        <span class="accent">Member</span>
                    </li>
                    <li>
                        <div class="member">
                            <span class="avatar" aria-hidden="true">HK</span>
                            <div>
                                <p>Dr. Hassan Khan</p>
                                <p class="muted">Visible in this group only</p>
                            </div>
                        </div>
                        <span class="gold-text">Member</span>
                    </li>
                    <li>
                        <div class="member">
                            <span class="avatar avatar-admin" aria-hidden="true">NM</span>
                            <div>
                                <p>Dr. Nida Malik</p>
                                <p class="muted">Verifies medical credentials</p>
                            </div>
                        </div>
                        <span class="muted">Admin</span>
                    </li>
                </ul>
                <p class="privacy-note">Other examples: Bankers only · Armed Forces only</p>
            </aside>
        </section>

        <section id="privacy" class="band">
            <div class="wrap">
                <h2 class="display">Matchmaking without a public catalog</h2>
                <p class="section-intro">Most apps make profiles searchable by anyone. WedGroups keeps you inside the rooms you actually belong to.</p>
                <div class="cards">
                    <article class="card">
                        <h3>A profile, not a listing</h3>
                        <p>You write your story once. It is never a public catalog. Strangers outside your groups cannot browse you.</p>
                    </article>
                    <article class="card">
                        <h3>Visible only in your groups</h3>
                        <p>Join a doctors-only, bankers-only, or armed forces group and you appear there. Leave, and you disappear from that circle.</p>
                    </article>
                    <article class="card">
                        <h3>Admins hold the door</h3>
                        <p>Every group has an admin who invites, approves, and removes members. Discovery stays among people they let in.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="how-it-works" class="wrap section">
            <h2 class="display">How it works</h2>
            <ol class="steps">
                <li>
                    <p class="step-num">01</p>
                    <h3>Create your profile</h3>
                    <p>Share what you want a trusted circle to know. Until you join a group, nobody else can see it.</p>
                </li>
                <li>
                    <p class="step-num">02</p>
                    <h3>Join a private group</h3>
                    <p>Accept an invite, or ask an admin. Membership is the only way your profile becomes visible to others.</p>
                </li>
                <li>
                    <p class="step-num">03</p>
                    <h3>Meet people in that circle</h3>
                    <p>Browse members of groups you share. Match within a room that already has a person in charge of who belongs.</p>
                </li>
            </ol>
        </section>

        <section id="stories" class="stories">
            <div class="wrap">
                <h2 class="display">Why people choose a closed circle</h2>
                <div class="quotes">
                    <blockquote>
                        <p>“I did not want my profile on a public site. I joined Doctors only, so only other physicians in that group can see me.”</p>
                        <footer>Hira · Member, Doctors only</footer>
                    </blockquote>
                    <blockquote>
                        <p>“I admin Armed Forces only. Members write their own profiles; I only confirm they belong in this service circle.”</p>
                        <footer>Imran · Admin, Armed Forces only</footer>
                    </blockquote>
                </div>
            </div>
        </section>

        <section id="start" class="cta">
            <h2 class="display">Start private. Stay private.</h2>
            <p>Create a profile, then join a circle that fits you — doctors, bankers, armed forces, and others like them. You are visible only where you belong.</p>
            <Link v-if="user" :href="accountHref" class="btn btn-primary">{{ accountLabel }}</Link>
            <Link v-else href="/register" class="btn btn-primary">Create your profile</Link>
        </section>
    </main>

    <footer class="site-footer">
        <div class="wrap footer-inner">
            <p>&copy; {{ year }} WedGroups</p>
            <p>Matchmaking, only inside your groups.</p>
        </div>
    </footer>
</template>
