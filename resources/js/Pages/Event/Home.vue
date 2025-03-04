<template>
	<game-layout title="Home">
		<div class="container flex flex-col justify-center items-center mx-auto px-4">
		    <div class="sm:max-w-2xl w-full rounded-xl flex flex-col items-center">
		    	<div class="w-full flex justify-center items-end my-4 gap-1 bg-[#121325] rounded">
		    	    <img src="/images/game-logo.png" class="w-56 h-auto mx-auto">
		    	</div>
		    	<div class="w-full p-4 bg-base-200 text-base-content rounded">
		    		  	<h2 class="text-xl font-bold text-center">Welcome to Laracon India 2025, from all of us at Ranium.</h2>
		    		  	<div class="flex items-center justify-center pt-2">
		    		    	<h2 class="text-shadows">Kem Chho?</h2>
		    		  	</div>
		    		  	<p class="text-lg font-medium">We’re super excited to have you jump into <strong>SnaQ</strong> for a uniquely thrilling adventure that fuses the fast-paced action of a classic arcade game with the mental jolt of trivia.</p>
		    		  	<div class="py-3 gradient-text-container text-center">
		    		  	  <p>Rack up 250+ points for a shot at a brand new&nbsp;&nbsp;<span class="gradient-text">iPhone 16</span></p>
		    		  	</div>
		    		  	<div class="flex flex-col items-center" v-if="registrationSetting.value">
		    		  		<p class="text-lg font-semibold text-center py-2 pb-3">Want in? Click the below button or come meet us at the Ranium booth to get registered!</p>
		    		  		<Link class="btn btn-primary btn-active text-lg w-min" :href="route('player.register', {event: $page.props.currentEvent.slug})">Register</Link>
		    		  	</div>
		    		  	<div class="flex flex-col items-center" v-else>
		    		  		<p class="text-lg font-semibold text-center py-2">Want in? Come meet us at the Ranium booth to get registered!</p>
		    		  	</div>
		    	</div>
		    </div>
		</div>
	</game-layout>
</template>

<script>
import GameLayout from "@/Layouts/GameLayout.vue";
import { router, Link } from '@inertiajs/vue3'

export default {
    components: {
        GameLayout,
        Link
    },

    props: {
        registrationSetting: {
            type: Object,
            default: {}
        }
    },

    data() {
        return {
            interval: null
        }
    },

    created() {
        this.reload();
        this.interval = setInterval(this.reload, 5000)
    },

    beforeDestroy() {
        clearInterval(this.interval);
    },

    methods: {
        goToLink(link) {
            this.$inertia.visit(link);
        },
        reload() {
            console.log('reloading');
            router.reload({ only: ['games'] })
        }
    }
}

</script>
<style scoped>
.text-shadows {
  --color-primary: #5192ED;
  --color-secondary: #69A1F0;
  --color-tertiary: #7EAEF2;
  --color-quaternary: #90BAF5;
  --color-quinary: #A2C4F5;
  text-shadow: 2px 2px 0 var(--color-secondary), 4px 4px 0 var(--color-tertiary),
    6px 6px var(--color-quaternary), 8px 8px 0 var(--color-quinary);
  font-family: bungee, sans-serif;
  font-weight: 400;
  text-transform: uppercase;
  font-size: calc(1.2rem + 5vw);
  text-align: center;
  margin: 0;
  color: var(--color-primary);
  animation: shadows 1.2s ease-in infinite;
  letter-spacing: 0.2rem;
}

@keyframes shadows {
  0% {
    text-shadow: none;
  }
  10% {
    transform: translate(-2px, -2px);
    text-shadow: 2px 2px 0 var(--color-secondary);
  }
  20% {
    transform: translate(-4px, -4px);
    text-shadow: 2px 2px 0 var(--color-secondary),
      4px 4px 0 var(--color-tertiary);
  }
  30% {
    transform: translate(-6px, -6px);
    text-shadow: 2px 2px 0 var(--color-secondary),
      4px 4px 0 var(--color-tertiary), 6px 6px var(--color-quaternary);
  }
  40% {
    transform: translate(-8px, -8px);
    text-shadow: 2px 2px 0 var(--color-secondary),
      4px 4px 0 var(--color-tertiary), 6px 6px var(--color-quaternary),
      8px 8px 0 var(--color-quinary);
  }
  50% {
    transform: translate(-8px, -8px);
    text-shadow: 2px 2px 0 var(--color-secondary),
      4px 4px 0 var(--color-tertiary), 6px 6px var(--color-quaternary),
      8px 8px 0 var(--color-quinary);
  }
  60% {
    text-shadow: 2px 2px 0 var(--color-secondary),
      4px 4px 0 var(--color-tertiary), 6px 6px var(--color-quaternary),
      8px 8px 0 var(--color-quinary);
  }
  70% {
    text-shadow: 2px 2px 0 var(--color-secondary),
      4px 4px 0 var(--color-tertiary), 6px 6px var(--color-quaternary);
  }
  80% {
    text-shadow: 2px 2px 0 var(--color-secondary),
      4px 4px 0 var(--color-tertiary);
  }
  90% {
    text-shadow: 2px 2px 0 var(--color-secondary);
  }
  100% {
    text-shadow: none;
  }
}

.gradient-text-container {
  font-family: bungee, sans-serif;
  color: #3c3c3c;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  flex-direction: column;
  font-size: clamp(1.3rem, 0.8vw, 1rem);
  font-weight: 600;
}

@keyframes textShine {
  0% {
    background-position: 0% 50%;
  }
  100% {
    background-position: 100% 50%;
  }
}


.gradient-text-container .gradient-text {
  font-weight: bold;
  background: linear-gradient(90deg, #61bb47, #fcb828, #f3831e, #e1393e, #963d98, #039edc);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  text-fill-color: transparent;
  background-size: 500% auto;
  animation: textShine 3s ease-in-out infinite alternate;
}
</style>