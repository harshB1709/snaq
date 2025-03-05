<template>
	<game-layout :title="`Error ${status}`">
		<div class="flex flex-col justify-center items-center mx-auto px-4 flex-1">
			<div class="sm:max-w-lg w-full rounded-xl flex flex-col items-center">
				<div class="w-full flex flex-col gap-1 justify-center items-end gap-1 rounded p-2 mb-4 bg-base-100" style="font-family: bungee, sans-serif;">
					<p class="text-center text-xl font-medium text-base-content"><strong>{{status}}</strong>: {{message}}</p>
					<img src="/images/error-snake.png" class="w-full h-auto mx-auto">
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
	  status: {
	  	type: Number,
	  	default: 400
	  },
	  message: {
	  	type: String,
	  	default: ''
	  }
	},

	data() {
		return {
			interval: null
		}
	},
}

</script>