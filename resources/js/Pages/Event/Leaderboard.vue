<template>
    <game-layout title="Leaderboard">
        <div class="container min-h-screen flex flex-col items-center mx-auto py-8 px-4 ubuntu-mono">
            <div class="sm:max-w-3xl w-full rounded-xl flex flex-col items-center">
                <h1 class="font-bold text-center text-primary text-4xl md:text-5xl underline underline-offset-4">Leaderboard</h1>
                <div class="overflow-x-auto mt-8 w-full">
                    <table class="table table-zebra w-full border-2 border-primary rounded w-full">
                        <!-- head -->
                        <thead>
                            <tr>
                                <th class="w-1/5 text-center text-base">Position</th>
                                <th class="w-3/5 text-center text-base">Player</th>
                                <th class="w-1/5 text-center text-base">Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(game, index) in games.data" :key="index">
                                <tr>
                                    <th class="text-center text-xl">#{{ games.from + index }}</th>
                                    <td class="text-center text-xl">{{ game.player.display_name }}</td>
                                    <td class="text-center text-xl">{{ game.score }}</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div class="btn-group mt-2 border-2 border-primary p-px max-w-full mx-auto overflow-x-auto">
                    <template v-for="(link, index) in games.links" :key="index">
                        <button
                            class="btn btn-md text-base md:btn-lg md:text-2xl"
                            :class="{
                                'btn-active': link.active
                            }"
                            :disabled="link.url === null"
                            v-html="link.label"
                            @click="goToLink(link.url)"
                        />
                    </template>
                </div>
            </div>
        </div>
    </game-layout>
</template>

<script>
import GameLayout from "@/Layouts/GameLayout.vue";
import { router } from '@inertiajs/vue3'

export default {
    components: {
        GameLayout
    },

    props: {
        games: {
            type: Object,
            required: true
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