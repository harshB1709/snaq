<template>
    <Head :title="title" />
    <div class="flex flex-col min-h-screen ubuntu-mono bg-wall relative">
        <div class="absolute top-0 w-full h-full -z-10" id="particles-js">
            
        </div>
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
                        <li v-if="route().current() !== 'home'">
                            <a :href="route('home', {event: $page.props?.currentEvent?.slug})">Home</a>
                        </li>
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
    mounted() {
        this.initParticles();
    },
    methods: {
        openAboutModal() {
            this.showAboutModal = true;
        },
        initParticles() {
            particlesJS('particles-js', {
                "particles": {
                "number": {
                  "value": 69,
                  "density": {
                    "enable": true,
                    "value_area": 380
                  }
                },
                "color": {
                  "value": "#f53103"
                },
                "shape": {
                  "type": "circle",
                  "stroke": {
                    "width": 0,
                    "color": "#000000"
                  },
                  "polygon": {
                    "nb_sides": 5
                  },
                  "image": {
                    "src": "img/github.svg",
                    "width": 100,
                    "height": 100
                  }
                },
                "opacity": {
                  "value": 0.7,
                  "random": true,
                  "anim": {
                    "enable": true,
                    "speed": 1,
                    "opacity_min": 0.25,
                    "sync": false
                  }
                },
                "size": {
                  "value": 3,
                  "random": true,
                  "anim": {
                    "enable": true,
                    "speed": 4,
                    "size_min": 0.4,
                    "sync": false
                  }
                },
                "line_linked": {
                  "enable": true,
                  "distance": 100,
                  "color": "#f53103",
                  "opacity": 0.6,
                  "width": 1.1
                },
                "move": {
                  "enable": true,
                  "speed": 0.5,
                  "direction": "none",
                  "random": true,
                  "straight": false,
                  "out_mode": "out",
                  "bounce": false,
                  "attract": {
                    "enable": false,
                    "rotateX": 1000,
                    "rotateY": 1200
                  }
                }
                },
                "interactivity": {
                "detect_on": "window",
                "events": {
                  "onhover": {
                    "enable": false,
                    "mode": "repulse"
                  },
                  "onclick": {
                    "enable": false,
                    "mode": "push"
                  },
                  "resize": true
                },
                "modes": {
                  "grab": {
                    "distance": 700,
                    "line_linked": {
                      "opacity": 0.3
                    }
                  },
                  "bubble": {
                    "distance": 400,
                    "size": 40,
                    "duration": 0.9,
                    "opacity": 0.5,
                    "speed": 3
                  },
                  "repulse": {
                    "distance": 80,
                    "duration": 0.4
                  },
                  "push": {
                    "particles_nb": 4
                  },
                  "remove": {
                    "particles_nb": 2
                  }
                }
                },
                "retina_detect": true
            })
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