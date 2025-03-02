<template>
    <Head :title="title" />
    <div class="min-h-screen ubuntu-mono bg-wall" :style="`--bg_wall: url('${$page.props?.currentEvent?.background_img_url ?? '/images/wall.png'}')`">
        <div class="navbar bg-base-200 bg-opacity-90 justify-between" v-if="showNavbar">
            <div class="navbar-start">
<!--                <a class="btn btn-ghost normal-case text-xl text-primary" href="/">Ranium's SnaQ</a>-->
            </div>
            <div class="dropdown dropdown-end lg:hidden">
                <label tabindex="0" class="btn btn-ghost">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>
                </label>
                <ul tabindex="0" class="menu menu-compact dropdown-content mt-3 p-2 shadow bg-base-100 rounded-box w-52 z-10">
                    <li><a href="javascript:void(0);" @click="openAboutModal">About</a></li>
                    <template v-if="showEventRoutes">
                        <li v-if="($page.props?.appSettings?.show_leaderboard?.value ?? false) || ($page.props?.auth?.user ?? false)">
                            <a :href="route('leaderboard', {event: $page.props?.currentEvent?.slug})">Leaderboard</a>
                        </li>
                        <li v-if="$page.props?.registrationSetting?.value ?? true"><a :href="route('player.register', {event: $page.props?.currentEvent?.slug})">Register</a></li>
                    </template>
                </ul>
            </div>
            <div class="hidden lg:flex lg:gap-2">
                <ul class="menu menu-horizontal px-1">
                    <li><a href="javascript:void(0);" @click="openAboutModal">About</a></li>
                    <template v-if="showEventRoutes">
                        <li v-if="($page.props?.appSettings?.show_leaderboard?.value ?? false) || ($page.props?.auth?.user ?? false)">
                            <a :href="route('leaderboard', {event: $page.props?.currentEvent?.slug})">Leaderboard</a>
                        </li>
                        <li v-if="$page.props?.registrationSetting?.value ?? true"><a :href="route('player.register', {event: $page.props?.currentEvent?.slug})">Register</a></li>
                    </template>
                </ul>
            </div>
        </div>
        <slot />
    </div>

    <about-modal
        v-model="showAboutModal"
    />
</template>
<script>
import AboutModal from "@/Components/AboutModal.vue"
import { Head } from '@inertiajs/vue3';

export default {
    components: {
        AboutModal,
        Head
    },

    props: {
        title: {
            type: String,
            default: ''
        },
        showNavbar: {
            type: Boolean,
            default: true
        },
        showEventRoutes: {
            type: Boolean,
            default: true
        }
    },

    data() {
        return {
            showAboutModal: false
        }
    },

    methods: {
        openAboutModal() {
            this.showAboutModal = true;
        }
    }
}
</script>

<style type="text/css">
    .bg-wall {
        background-image: var(--bg_wall);
        background-size: cover;
    }
</style>