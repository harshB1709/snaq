<template>
    <Head title="Game Page" />
    <div class="flex justify-center w-svw max-w-6xl h-svh m-auto">
        <div
            class="w-full max-h-full flex-col md:flex-row items-center gap-3 md:gap-8 p-3"
            :class="{
                'hidden': !gameStarted,
                'flex': gameStarted
            }"
        >
            <!-- Snake grid -->
            <div class="flex flex-col flex-1 gap-2 w-full max-w-xl min-h-64 md:min-h-full">
                <div class="flex justify-evenly">
                    <span class="text-center font-bold text-xl md:text-2xl text-primary-content">
                        Score: {{score}}
                    </span>

                    <span class="text-center font-bold text-xl md:text-2xl text-primary-content">
                        Lives: <span v-html="livesString"></span>
                    </span>
                </div>
                <div
                    class="flex justify-center flex-1 min-h-4 max-w-full"
                >
                    <div
                        class="grid aspect-square border border-white md:w-full max-w-full max-h-full ground-grid bg-base-300"
                        :style="gridStyle"
                    >
                        <template
                            v-for="(row, rowIndex) in grid"
                        >
                            <div
                                v-for="(cell, colIndex) in row"
                                :key="`${rowIndex}.${colIndex}`"
                                :class="[
                                    'cell md:text-sm leading-none font-semibold',
                                    cell === 'snake' ? 'snake bg-primary' : '',
                                    cell?.food ? 'food' : '',
                                    cell?.food && cooldown ? 'opacity-50' : ''
                                ]"
                                :style="`background: ${cell?.color ?? ''}`"
                            >
                                <!-- <span v-if="cell?.food">
                                    {{ cell.text ?? '' }}
                                </span> -->
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-auto md:flex-1 flex flex-col gap-2 pb-1 justify-center">
                <!-- Controls -->
                <div class="flex flex-col gap-3 md:hidden">
                    <div class="flex w-full justify-center">
                      <button><kbd class="kbd bg-base-300 text-base-content shadow-md kbd-xl" @click="handleNav('up')">▲</kbd></button>
                    </div>
                    <div class="flex w-full justify-center gap-3">
                      <button><kbd class="kbd bg-base-300 text-base-content shadow-md kbd-xl" @click="handleNav('left')">◀︎</kbd></button>
                      <button><kbd class="kbd bg-base-300 text-base-content shadow-md kbd-xl" @click="handleNav('down')">▼</kbd></button>
                      <button><kbd class="kbd bg-base-300 text-base-content shadow-md kbd-xl" @click="handleNav('right')">▶︎</kbd></button>
                    </div>
                </div>

                <!-- MCQ -->
                <div class="w-full" v-if="gameStarted">
                    <div class="mt-2 w-full p-3 px-6 bg-warning text-warning-content rounded-xl md:text-lg font-bold">
                        <span class="font-extrabold">Q{{questionNum}}:</span> {{question}}?</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-1 md:gap-2 mt-3 md:mt-4 text-sm md:text-base font-semibold">
                        <div v-for="(option, index) in options" class="p-3 px-5 bg-accent text-accent-content rounded-xl flex gap-2 items-center">
                            <div
                                class="font-bold border border-white h-5 w-5 md:h-5 md:w-5 flex-none"
                                :style="`background: ${option.color};`"
                            >
                                <!-- {{String.fromCharCode(65 + index)}}: -->
                            </div>
                            <span>
                                &nbsp;{{option.value}}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <dialog id="game_start_modal" class="modal" ref="gameStartModal" open>
        <div class="modal-box" v-if="startModalPanel === 1">
            <h3 class="font-bold text-xl text-primary-content">Laracon 2024 Snake Game!</h3>
            <div class="w-full flex justify-center">
                <ol class="list-disc py-3 px-2 w-fit">
                    <li>
                        <div class="form-control items-center">
                          <label class="label cursor-pointer justify-start gap-16">
                            <span class="label-text text-lg font-bold text-primary-content">Full Screen</span>
                            <input type="checkbox" class="toggle toggle-primary" :checked="isFullScreen ? 'checked' : false" @input="toggleFullScreen"/>
                          </label>
                        </div>
                    </li>
                </ol>
            </div>
            <div class="modal-action justify-center">
                <button class="btn btn-accent" @click="nextStartModal">Next</button>
            </div>
        </div>
        <div class="modal-box" v-else-if="startModalPanel === 2">
            <h3 class="font-bold text-lg">Laracon 2024 Snake Game!</h3>
            <ol class="list-disc py-3 pl-2">
                <li>{{totalQuestions}} questions will appear one after another with four options.</li>
                <li>Eat the food with the correct option to the adjacent question to score points else you lose a point.</li>
                <li>There is a cooldown of 2s after every question.</li>
                <li>The speed of the snake increases after answering every question.</li>
            </ol>
            <p class="pb-4">Click below button to start the game.</p>
            <div class="modal-action justify-center">
                <button class="btn btn-neutral" @click="prevStartModal">Prev</button>
                <button class="btn btn-primary" @click="startGame" ref="startGame">Start</button>
            </div>
        </div>
        <div class="modal-box" v-else-if="startModalPanel === 3">
            <div class="w-full flex flex-col items-center gap-6">
                <h3 class="text-2xl font-bold text-primary">STARTING IN</h3>
                <div class="radial-progress bg-primary text-primary-content border-4 border-primary" :style="`--value:${(gameStartedTimer*100)/300}; --size:12rem; --thickness: 2rem;`">
                    <span class="countdown font-mono font-bold text-6xl">
                        <span :style="`--value:${Math.round(gameStartedTimer/86)};`"></span>
                    </span>
                </div>
            </div>
        </div>
    </dialog>

    <dialog id="game_end_modal" class="modal" ref="gameEndModal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">{{gameOverMsg}}</h3>
            <p class="py-4">Your Final Score: {{score}}</p>
            <p class="py-4">Click below button to start the game.</p>
            <div class="modal-action justify-center">
                <button class="btn btn-active" @click="resetGame">Restart</button>
            </div>
        </div>
    </dialog>
</template>

<script>
import { Head, usePage, Link } from '@inertiajs/vue3'

export default {
    name: "Game",
    components: {
        Head
    },
    data() {
        return {
            rows: 20,
            cols: 20,
            snake: [{ row: 1, col: 1 }],
            food: { row: 0, col: 0 },
            direction: 'right',
            timeout: this.speedTimeout,
            gameInterval: null,
            gameOverMsg: '',
            question: null,
            options: null,
            gameStarted: false,
            score: 0,
            startModalPanel: 1,
            isFullScreen: false,
            gameStartedTimer: null,
            gameStartedTimerSetInterval: null,
            cooldown: false,
            questionNum: 0,
            livesRemaining: this.lives
        }
    },
    props: {
        totalQuestions: {
            type: Number,
            required: true
        },
        speedTimeout: {
            type: Number,
            default: 450
        },
        cooldownTime: {
            type: Number,
            default: 2000
        },
        lives: {
            type: Number,
            default: 3
        },
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
        startGame() {

            this.$refs.startGame?.classList?.add('loading');
            axios
                .post(`/api/${usePage().props.currentEvent.slug}/start-game`, {})
                .then((res) => {
                    const data = res.data;
                    this.nextStartModal();
                    this.gameStartedTimer = 300;
                    this.gameStartedTimerSetInterval = setInterval(function() {
                        if(this.gameStartedTimer > 0) {
                            this.gameStartedTimer--;
                        }
                        if(this.gameStartedTimer === 0) {
                            this.questionNum++;
                            this.setNewQuestion(data.question, data.options);
                            this.$refs.gameStartModal.close();
                            clearInterval(this.gameStartedTimerSetInterval);
                            this.gameStartedTimerSetInterval = null;
                            this.gameStarted = true;
                            this.changeSnakeSpeed();
                        }
                    }.bind(this), 10)
                })
                .catch((err) => {

                })
                .finally(() => {
                    this.$refs.startGame?.classList?.remove('loading');
                })
        },
        changeSnakeSpeed() {
            if(this.gameInterval) {
                clearInterval(this.gameInterval);
                this.gameInterval = null;
            }
            this.gameInterval = setInterval(() => {
                this.move();
            }, this.timeout);
        },
        setNewQuestion(question, options) {
            this.question = question;
            this.options = options;

            if(question?.length){
                this.cooldown = true;
                setTimeout(() => {
                    this.cooldown = false
                }, this.cooldownTime)
            }
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
                this.gameEnd('hitWall');
                return;
            }

            // Check collision with itself
            if (this.snake.some(segment => segment.row === head.row && segment.col === head.col)) {
                this.gameEnd('hitSelf');
                return;
            }

            let unshifted = false;
            // Check collision with food
            if (this.inPositions(head.row, head.col) && !this.cooldown) {
                this.handleFoodEat([head.row, head.col]);
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
            return this.options.map(i => {
                return i?.position?.[0] === r && i?.position?.[1] === c
            }).includes(true)
        },
        gameEnd(action) {
            axios.post(`/api/${usePage().props.currentEvent.slug}/game-action`, {
                action
            }).then(res => {
                const data = res.data;
                this.score = data.points;
                if(data.gameOver)
                    this.handleGameEnd(data.gameOverMessage);
            });
        },
        handleFoodEat(pos) {
            const option = this.options.filter(o => o?.position?.[0] === pos[0] && o?.position?.[1] === pos[1]);
            const color = option?.[0]?.color;
            if(color?.length) {
                this.setNewQuestion('', [
                    {value: ''},
                    {value: ''},
                    {value: ''},
                    {value: ''}
                ]);
                axios.post(`/api/${usePage().props.currentEvent.slug}/game-action`, {
                    color,
                    action: 'eatFood'
                }).then(res => {
                    const data = res.data;
                    this.score = data.points;
                    this.questionNum++;
                    this.setNewQuestion(data.question, data.options);
                    this.timeout = data.speedTimeout;
                    this.changeSnakeSpeed();
                    if(data.gameOver)
                        this.handleGameEnd(data.gameOverMessage);
                });
            }
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
                this.options.forEach((d, i) => {
                    if(d.position)
                        grid[d.position[0]][d.position[1]] = {food: true, color: d.color}
                })
            }

            return grid;
        },
        gridStyle() {
            return {
                "grid-template-columns": `repeat(${this.rows}, minmax(0, 1fr))`
            }
        },
        livesString() {
            let lives = '';
            for(let i = 0; i < this.livesRemaining; i++)
                lives += '❤️&nbsp;';
            return lives;
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
    border: 0.5px solid #ddd;
}

.food {
/*    background-color: #FF5733;*/
    color: white;
    line-height: 100%;
    border: 1px solid white;
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
