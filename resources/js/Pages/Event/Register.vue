<template>
    <game-layout title="Register">
        <div class="container flex flex-col justify-center items-center mx-auto px-4">
            <div class="sm:max-w-md w-full rounded-xl flex flex-col items-center">
                <div class="w-full flex justify-center items-end my-4 gap-1 bg-[#121325] rounded">
                    <img src="/images/game-logo.png" class="w-56 h-auto mx-auto">
                    <!-- <span class="whitespace-nowrap font-bold text-center text-2xl text-primary leading-4">
                        's SnaQ
                    </span> -->
                </div>
                <div class="w-full flex flex-col bg-base-200 rounded-lg p-4 items-center gap-4">
                    <div class="form-control w-full max-w-sm">
                      <label class="label" for="name">
                        <span class="label-text">Name</span>
                      </label>
                      <input type="text" placeholder="Name" v-model="name" autocomplete="name" id="name" class="input input-bordered border-base-content w-full" />
                      <InputError class="mt-1 text-error" :message="errors?.name?.join(' ')" />
                    </div>
                    <div class="form-control w-full max-w-sm">
                      <label class="label" for="display_name">
                        <span class="label-text">Display Name(Optional)</span>
                      </label>
                      <input type="text" placeholder="Display Name" v-model="display_name" id="display_name" class="input input-bordered border-base-content w-full" />
                      <InputError class="mt-1 text-error" :message="errors?.display_name?.join(' ')" />
                    </div>
                    <div class="form-control w-full max-w-sm">
                      <label class="label" for="email">
                        <span class="label-text">Email</span>
                      </label>
                      <input type="text" placeholder="Email" v-model="email" id="email" class="input input-bordered border-base-content w-full" />
                      <InputError class="mt-1 text-error" :message="errors?.email?.join(' ')" />
                    </div>
                    <div class="form-control w-full max-w-sm">
                      <label class="label" for="phone">
                        <span class="label-text">Phone No.</span>
                      </label>
                      <input type="text" placeholder="Phone No." v-model="phone" id="phone" class="input input-bordered border-base-content w-full" />
                      <InputError class="mt-1 text-error" :message="errors?.phone?.join(' ')" />
                    </div>
                    <div class="alert alert-success shadow-lg" v-if="registered">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <span>
                                    Thank you! Please check your email inbox for the the link to start the game.
                                </span>
                                <br>
                                <p><span class="text-lg font-semibold">Note: </span>This link is valid only for the next {{inviteValidityMins}} mins, please start the game before the link expires.</p>
                            </div>
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
        return {
            name: "",
            display_name: "",
            email: "",
            phone: "",
            processing: false,
            errors: null,
            registered: false,
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
                    phone: this.phone
                })
                .then((res) => {
                    if(res.data.status === 'success') {
                        this.registered = true;
                        this.name = '';
                        this.email = '';
                        this.display_name = '';
                        this.phone = '';
                    }
                })
                .catch((err) => {
                    this.errors = err.response.data.errors;
                })
                .finally(() => {
                    this.processing = false;
                });
        }
    }
}
</script>