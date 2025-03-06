<template>
    <game-layout title="Register">
        <div class="container flex flex-col justify-center items-center mx-auto px-4">
            <div class="sm:max-w-md w-full rounded-xl flex flex-col items-center">
                <div class="w-full flex justify-center items-end my-4 gap-1 bg-[#121325] p-1 rounded">
                    <img src="/images/game-logo.png" class="w-56 h-auto mx-auto">
                    <!-- <span class="whitespace-nowrap font-bold text-center text-2xl text-primary leading-4">
                        's SnaQ
                    </span> -->
                </div>
                <div class="w-full flex flex-col bg-base-200 rounded-lg p-4 items-center gap-4">
                    <div class="form-control w-full max-w-sm">
                        <label class="label font-bold justify-center" for="avatar">
                            <span class="label-text">Select Your avatar</span>
                        </label>
                        <div class="flex gap-4 p-2 items-center justify-center">
                            <button class="btn btn-circle btn-accent btn-outline" @click="decrementAvatar">❮</button>
                            <div class="avatar">
                                <div class="ring-primary ring-offset-base-100 w-24 rounded-full ring ring-offset-2 p-3 bg-secondary">
                                    <template v-for="(option, index) in avatarOptions" :key="index">
                                        <img
                                            :src="option"
                                            :class="{
                                                '!object-contain w-full h-full': true,
                                                'hidden': index !== selectedAvatar
                                            }"
                                            rel="preload"
                                        />
                                    </template>
                                </div>
                            </div>
                            <button class="btn btn-circle btn-accent btn-outline" @click="incrementAvatar">❯</button>
                        </div>
                    </div>
                    <div class="form-control w-full max-w-sm">
                      <label class="label font-bold" for="name">
                        <span class="label-text">Name</span>
                      </label>
                      <input type="text" placeholder="Name" v-model="name" autocomplete="name" id="name" class="input input-bordered border-base-content w-full" />
                      <InputError class="mt-1 text-error" :message="errors?.name?.join(' ')" />
                    </div>
                    <div class="form-control w-full max-w-sm">
                      <label class="label font-bold" for="display_name">
                        <span class="label-text">Display Name(Optional)</span>
                      </label>
                      <input type="text" placeholder="Display Name" v-model="display_name" id="display_name" class="input input-bordered border-base-content w-full" />
                      <InputError class="mt-1 text-error" :message="errors?.display_name?.join(' ')" />
                    </div>
                    <div class="form-control w-full max-w-sm">
                      <label class="label font-bold" for="email">
                        <span class="label-text">Email</span>
                      </label>
                      <input type="text" placeholder="Email" v-model="email" id="email" class="input input-bordered border-base-content w-full" />
                      <InputError class="mt-1 text-error" :message="errors?.email?.join(' ')" />
                    </div>
                    <div class="alert alert-success shadow-lg" v-if="registered">
                        <div class="text-left">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6 inline-block" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>
                                Thank you! Please check your email inbox for the the link to start the game.
                            </span>
                            <br>
                            <p class="pt-3"><span class="text-lg font-semibold">Note: </span>This link is valid only for the next {{inviteValidityMins}} mins, please start the game before the link expires.</p>
                        </div>
                    </div>
                    <button
                        type="submit"
                        @click="submit"
                        :disabled="processing"
                        class="btn btn-active btn-primary mt-4 mb-2"
                        :class="{
                            'loading': processing
                        }"
                    >
                        Register
                    </button>
                </div>
            </div>
        </div>
    </game-layout>
</template>

<script>
import GameLayout from "@/Layouts/GameLayout.vue";
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';

export default {
    components: {
        GameLayout,
        InputLabel,
        InputError,
        PrimaryButton
    },

    data() {
        let avatarOptions = [];
        for(let i = 1; i < 25; i++) {
            avatarOptions.push(`/images/avatars/snake_${i}.png`)
        }
        return {
            name: "",
            display_name: "",
            email: "",
            processing: false,
            errors: null,
            registered: false,
            avatarOptions,
            selectedAvatar: Math.floor(Math.random() * avatarOptions.length)
        }
    },

    props: {
        inviteValidityMins: {
            type: Number,
            default: 15
        }
    },

    methods: {
        submit() {
            this.processing = true;
            this.errors = null;
            this.registered = false;
            axios
                .post(route('register-api', {'event': this.$page.props?.currentEvent?.slug}), {
                    name: this.name,
                    email: this.email,
                    display_name: this.display_name,
                    avatar: this.avatarOptions[this.selectedAvatar]
                })
                .then((res) => {
                    if(res.data.status === 'success') {
                        this.registered = true;
                        this.name = '';
                        this.email = '';
                        this.display_name = '';
                        this.selectedAvatar = Math.floor(Math.random() * this.avatarOptions.length)
                    }
                })
                .catch((err) => {
                    this.errors = err.response.data.errors;
                })
                .finally(() => {
                    this.processing = false;
                });
        },
        incrementAvatar() {
            this.selectedAvatar = (this.selectedAvatar + 1) % this.avatarOptions.length;
            console.log(this.selectedAvatar);
        },
        decrementAvatar() {
            this.selectedAvatar = (this.selectedAvatar - 1) % this.avatarOptions.length;
            if(this.selectedAvatar < 0)
                this.selectedAvatar += this.avatarOptions.length;
            console.log(this.selectedAvatar);
        }
    }
}
</script>