<template>
    <div class="flex justify-center w-svw max-w-6xl h-svh m-auto">
        <div
            class="w-full max-h-full flex-col md:flex-row items-center gap-3 md:gap-8 p-2 pt-4"
            :class="{
                'hidden': !gameStarted,
                'flex': gameStarted
            }"
        >
            <!-- Snake grid -->
            <div class="flex flex-col flex-1 gap-2 w-full max-w-xl min-h-64 md:min-h-full">
                <span class="text-center">
                    Score: {{score}}
                </span>
                <div
                    class="flex items-center justify-center flex-1 min-h-4 max-w-full"
                >
                    <div
                        class="grid aspect-square border border-white md:w-full max-w-full max-h-full ground-grid"
                        :style="gridStyle"
                    >
                        <template
                            v-for="(row, rowIndex) in grid"
                        >
                            <div
                                v-for="(cell, colIndex) in row"
                                :key="`${rowIndex}.${colIndex}`"
                                :class="['cell md:text-sm leading-none font-semibold', cell === 'snake' ? 'snake' : '', cell?.food ? 'food' : '']"
                            >
                                <span v-if="cell?.food">
                                    {{ cell.text ?? '' }}
                                </span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-auto md:flex-1 flex flex-col gap-4 pb-4 justify-center">
                <!-- Controls -->
                <div class="flex flex-col gap-3 md:hidden">
                    <div class="flex w-full justify-center">
                      <button><kbd class="kbd kbd-xl" @click="handleNav('up')">▲</kbd></button>
                    </div>
                    <div class="flex w-full justify-center gap-3">
                      <button><kbd class="kbd kbd-xl" @click="handleNav('left')">◀︎</kbd></button>
                      <button><kbd class="kbd kbd-xl" @click="handleNav('down')">▼</kbd></button>
                      <button><kbd class="kbd kbd-xl" @click="handleNav('right')">▶︎</kbd></button>
                    </div>
                </div>

                <!-- MCQ -->
                <div class="w-full text-xxs md:text-base" v-if="gameStarted">
                    <div class="mt-2 w-full p-1.5 bg-primary text-primary-content rounded"><span class="font-bold">Q:</span> {{questions[position].question}}?</div>
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <span v-for="(option, index) in questions[position].options" class="p-1.5 bg-primary text-primary-content rounded"><span class="font-bold">{{String.fromCharCode(65 + index)}}:</span> {{option.value}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <dialog id="game_start_modal" class="modal" ref="gameStartModal" open>
        <div class="modal-box" v-if="startModalPanel === 1">
            <h3 class="font-bold text-lg">Laracon 2024 Snake Game!</h3>
            <ol class="list-disc py-3 pl-2">
                <li>
                    <div class="form-control">
                      <label class="label cursor-pointer">
                        <span class="label-text">Full Screen</span>
                        <input type="checkbox" class="toggle" :checked="isFullScreen ? 'checked' : false" @input="toggleFullScreen"/>
                      </label>
                    </div>
                </li>
            </ol>
            <div class="modal-action justify-center">
                <button class="btn" @click="nextStartModal">Next</button>
            </div>
        </div>
        <div class="modal-box" v-else-if="startModalPanel === 2">
            <h3 class="font-bold text-lg">Laracon 2024 Snake Game!</h3>
            <ol class="list-disc py-3 pl-2">
                <li>A question will appear one after another with four options.</li>
                <li>Eat the food with the correct option to score a point else you lose a point.</li>
            </ol>
            <p class="pb-4">Click below button to start the game.</p>
            <div class="modal-action justify-center">
                <button class="btn" @click="prevStartModal">Prev</button>
                <button class="btn" @click="handleGameStartClose">Start</button>
            </div>
        </div>
    </dialog>

    <dialog id="game_end_modal" class="modal" ref="gameEndModal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">{{gameOverMsg}}</h3>
            <p class="py-4">Your Final Score: {{score}}</p>
            <p class="py-4">Click below button to start the game.</p>
            <div class="modal-action justify-center">
                <button class="btn" @click="resetGame">Restart</button>
            </div>
        </div>
    </dialog>
</template>

<script>
export default {
    name: "Game",
    data() {
        return {
            rows: 20,
            cols: 20,
            snake: [{ row: 1, col: 1 }],
            food: { row: 0, col: 0 },
            direction: 'right',
            timeout: 240,
            gameInterval: null,
            gameOverMsg: '',
            questions: [
                this.firstQuestion
            ],
            position: 0,
            gameStarted: false,
            score: 0,
            startModalPanel: 1,
            isFullScreen: false
        }
    },
    props: {
        firstQuestion: {
            type: Object,
            default: null
        }
    },
    beforeMount () {
        window.addEventListener('keydown', this.handleKeydown, null);
        document.documentElement.addEventListener('fullscreenchange', this.onFullScreenChange, null);
    },
    mounted() {
    },
    beforeDestroy () {
        window.removeEventListener('keydown', this.handleKeydown);
        window.documentElement.removeEventListener('fullscreenchange', this.onFullScreenChange);
    },
    methods: {
        nextStartModal() {
            this.startModalPanel++;
        },
        prevStartModal() {
            this.startModalPanel--;
        },
        onFullScreenChange() {
            this.isFullScreen = document.fullscreenElement || document.mozCancelFullScreen || document.webkitFullscreenElement;
        },
        toggleFullScreen() {
            if (!document.fullscreenElement && !document.mozFullScreenElement && !document.webkitFullscreenElement) {
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen();
                } else if (document.documentElement.mozRequestFullScreen) {
                    document.documentElement.mozRequestFullScreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                }
            } else {
                if (document.cancelFullScreen) {
                    document.cancelFullScreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitCancelFullScreen) {
                    document.webkitCancelFullScreen();
                }
            }
        },
        handleGameStartClose() {
            this.$refs.gameStartModal.close();
            this.startGame();
            this.gameStarted = true;
            this.getNextQuestion(null);
        },
        startGame() {
            this.gameInterval = setInterval(() => {
                this.move();
            }, this.timeout);
        },
        handleNav(dir) {
            switch(dir) {
                case 'left':
                    this.direction = this.direction !== 'right' ? 'left' : 'right';
                    break;
                case 'up':
                    this.direction = this.direction !== 'down' ? 'up' : 'down';
                    break;
                case 'right':
                    this.direction = this.direction !== 'left' ? 'right' : 'left';
                    break;
                case 'down':
                    this.direction = this.direction !== 'up' ? 'down' : 'up';
                    break;
                default:
                    break;
            }
        },
        handleKeydown (e) {
            switch (e.keyCode) {
                case 37:
                    this.direction = this.direction !== 'right' ? 'left' : 'right';
                    break;
                case 38:
                    this.direction = this.direction !== 'down' ? 'up' : 'down';
                    break;
                case 39:
                    this.direction = this.direction !== 'left' ? 'right' : 'left';
                    break;
                case 40:
                    this.direction = this.direction !== 'up' ? 'down' : 'up';
                    break;
                default:
                    // console.log(e.keyCode);
                    break;
            }
        },
        handleGameEnd(gameOverMsg) {
            clearInterval(this.gameInterval)
            this.gameOverMsg = gameOverMsg;
            this.$refs.gameEndModal.showModal();
        },
        move() {
            const head = Object.assign({}, this.snake[0]);

            switch (this.direction) {
                case 'up':
                    head.row--;
                    break;
                case 'down':
                    head.row++;
                    break;
                case 'left':
                    head.col--;
                    break;
                case 'right':
                    head.col++;
                    break;
            }

            // Check collision with walls
            if (head.row < 0 || head.row >= this.rows || head.col < 0 || head.col >= this.cols) {
                this.handleGameEnd('Game Over! You hit the wall.');
                return;
            }

            // Check collision with itself
            if (this.snake.some(segment => segment.row === head.row && segment.col === head.col)) {
                this.handleGameEnd('Game Over! You collided with yourself.');
                return;
            }

            let unshifted = false;
            // Check collision with food
            if (this.inPositions(head.row, head.col)) {
                this.position++;
                this.getNextQuestion([head.row, head.col]);
                // this.snake.unshift({ ...this.food });
                // unshifted = true;
            } else {
                this.snake.pop();
            }

            if(!unshifted) {
                this.snake.unshift(head);
            }
        },
        resetGame() {
            window.location.reload()
            this.$refs.gameEndModal.close();
            this.snake = [{ row: 0, col: 0 }];
            this.direction = 'right';
            this.startGame();
        },
        inPositions(r,c) {
            return this.optionsPosition.map(i => {
                return i[0] === r && i[1] === c
            }).includes(true)
        },
        async getNextQuestion(pos) {
            let data = await axios.get('/api/next-question', {
                params: {
                    pos: pos ?? ''
                }
            }).then(resp => resp.data);
            this.score = data.score;
            this.questions.push(data.question);
        }
    },
    computed: {
        grid() {
            const grid = Array.from({ length: this.rows }, () => Array(this.cols).fill(0));

            this.snake.forEach(segment => {
                grid[segment.row][segment.col] = 'snake';
            });

            if(this.gameStarted) {
                this.questions[this.position].options.forEach((d, i) => {grid[d.position[0]][d.position[1]] = {food: true, text: String.fromCharCode(65 + i)}})
            }

            return grid;
        },
        gridStyle() {
            return {
                "grid-template-columns": `repeat(${this.rows}, minmax(0, 1fr))`
            }
        },
        optionsPosition() {
            return this.questions[this.position].options.map(o => o.position)
        }
    }
}
</script>

<style scoped>

div.ground-grid {
    height: min(100%, calc(100vw - 20px));
}

.cell {
    height: 100%;
    aspect-ratio: 1;
    font-size: min(2vmax, 1.5rem);
    /*border: 1px solid #ddd;*/
}

.snake {
    background-color: #4CAF50;
}

.food {
    background-color: #FF5733;
    color: white;
    line-height: 100%;
}

.snake, .food {
    padding: 8%;
    border-radius: 8%;
}

.snake>span, .food>span {
    height: 100%;
    aspect-ratio: 1;
    border-radius: 8%;
}

.snake::after {
    content: '';
    border: 1.5px solid white;
    width: 100%;
    height: 100%;
    display: block;
}

.food>span {
    display: flex;
    justify-content: center;
    align-items: center;
}

.kbd-xl {
    min-width: 3.2rem;
    min-height: 3.2rem;
    font-size: 1.6rem;
}
</style>
