<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $avatarOptions = $getAvatarOptions();
    @endphp

    <div
        x-data="{
            avatarOptions: @js($avatarOptions),
            selectedAvatar: $wire.entangle('{{ $getStatePath() }}'),  

            incrementAvatar() {
                let currentIndex = this.avatarOptions.indexOf(this.selectedAvatar)
                const newIndex = (currentIndex + 1) % this.avatarOptions.length
                this.selectedAvatar = this.avatarOptions[newIndex]
            },
            decrementAvatar() {
                let currentIndex = this.avatarOptions.indexOf(this.selectedAvatar)
                const newIndex = (currentIndex - 1 + this.avatarOptions.length) % this.avatarOptions.length
                this.selectedAvatar = this.avatarOptions[newIndex]
            },
        }"
        class="form-control w-full max-w-sm"
    >

        <div class="flex gap-4 p-2 items-center">
            <button type="button"
                class="text-primary-500 border border-primary-500 p-2 rounded-full w-10 h-10 flex items-center justify-center"
                @click="decrementAvatar"
            >
                ❮
            </button>

            <div class="avatar">
                <div
                    class="ring-primary-500 ring-offset-base-100 w-20 rounded-full ring ring-offset-2 p-3"
                    @style([
                        'aspect-ratio: 1',
                        'background: #a4cbb4'
                    ])
                >
                    <template x-for="(option, index) in avatarOptions" :key="index">
                        <img
                            :src="option"
                            :class="{
                                'w-full': true,
                                'hidden': option !== selectedAvatar
                            }"
                            @style([
                                'aspect-ratio: 1',
                                'object-fit: contain'
                            ])
                        />
                    </template>
                </div>
            </div>

            <button type="button"
                class="text-primary-500 border border-primary-500 p-2 rounded-full w-10 h-10 flex items-center justify-center"
                @click="incrementAvatar"
            >
                ❯
            </button>
        </div>
    </div>
</x-dynamic-component>