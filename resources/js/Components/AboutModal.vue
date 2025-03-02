<template>
    <input type="checkbox" id="about-modal" :checked="modelValue" class="modal-toggle" />
    <div @click="closeModal" class="modal cursor-pointer ubuntu-mono">
      <label class="modal-box relative max-w-2xl w-11/12" for="" @click.stop="">
        <button type="button" @click="closeModal" class="btn btn-sm btn-circle absolute right-2 top-2">✕</button>
        <h3 class="text-2xl text-primary font-bold">About!</h3>
        <p class="pt-3 pb-1 text-lg"><strong>Welcome to SnaQ!</strong><br>SnaQ is a game designed to push your limits by combining the thrill of arcade action with the wit of trivia. As you guide your snake through a maze of questions, you’ll need to balance quick reflexes with sharp thinking—proving that you can handle multiple tasks at once.<br>Whether you’re a seasoned coder, a trivia enthusiast, or just in for some fun, this game is your playground. It’s a fun way to explore your ability to think on your feet and react with precision. Enjoy the ride and good luck!</p>
        <div>
            <svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200.000000 200.000000"
             preserveAspectRatio="xMidYMid meet" class="w-20 inline-block mr-3 fill-effect" :class="{ 'svg-outline': !showColour }" ref="raniumLogo">

                <g transform="translate(0.000000,200.000000) scale(0.100000,-0.100000)"
                fill="#000000" stroke="#000000">
                    <path d="M292 1570 c-63 -12 -98 -33 -118 -71 -29 -57 -11 -247 38 -396 123
                    -372 469 -606 980 -662 164 -18 175 -15 76 23 -180 68 -326 196 -423 371 -55
                    99 -55 101 10 108 142 15 237 137 207 267 -41 176 -167 297 -363 345 -76 19
                    -330 28 -407 15z"/>
                    <path d="M1167 1468 c60 -65 113 -193 113 -267 -2 -104 -87 -218 -208 -276
                    l-33 -16 38 -70 c51 -94 122 -172 210 -230 117 -76 252 -119 374 -119 125 0
                    156 40 146 189 -21 286 -154 526 -381 683 -66 46 -234 128 -261 128 -17 0 -17
                    -2 2 -22z" fill="#006df5" stroke="#006df5"/>
                </g>
            </svg>
        </div>
        <h3 class="text-xl font-semibold text-primary mb-2 underline">Contributors:</h3>
        <p class="list-item ml-6 text-lg">
            Harsh Bachawat
            <a href="https://www.linkedin.com/in/harsh-bachawat" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="inline w-4 pb-0.5">
                    <path d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1 243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z" fill="currentColor"/>
                </svg>
            </a>
            <a href="https://x.com/HarshBachawat" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="inline w-4 ml-2 pb-0.5">
                    <path fill="currentColor" d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zm297.1 84L257.3 234.6 379.4 396H283.8L209 298.1 123.3 396H75.8l111-126.9L69.7 116h98l67.7 89.5L313.6 116h47.5zM323.3 367.6L153.4 142.9H125.1L296.9 367.6h26.3z"/>
                </svg>
            </a>
        </p>
        <p class="list-item ml-6 text-lg">
            Abbas Ali
            <a href="https://www.linkedin.com/in/abbasali1" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="inline w-4 pb-0.5">
                    <path d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1 243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z" fill="currentColor"/>
                </svg>
            </a>
            <a href="https://x.com/_abbas" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="inline w-4 ml-2 pb-0.5">
                    <path fill="currentColor" d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zm297.1 84L257.3 234.6 379.4 396H283.8L209 298.1 123.3 396H75.8l111-126.9L69.7 116h98l67.7 89.5L313.6 116h47.5zM323.3 367.6L153.4 142.9H125.1L296.9 367.6h26.3z"/>
                </svg>
            </a>
        </p>
      </label>
    </div>
</template>

<script>

export default {
    data() {
        return {
            showColour: true,
            animateSetTimeout: null,
            vivus: null,
            fps: this.parentFps
        }
    },

    props: {
        modelValue: {
            type: Boolean,
            default: false
        }
    },

    mounted() {
    },

    emits: ['update:modelValue'],

    methods: {
        closeModal() {
            this.$emit('update:modelValue', false);
        }
    },
}
</script>
<style>
    svg.fill-effect * {
        stroke-width: 20%;
        transition: fill-opacity 0.5s, stroke 0.4s;
    }

    .svg-outline * {
        stroke: currentColor !important;
        fill-opacity: 0;
    }
</style>
