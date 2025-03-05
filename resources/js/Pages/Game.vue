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
                <div class="flex justify-between">
                    <span class="text-center font-bold text-lg md:text-2xl text-primary-content">
                        Score: {{score}}
                    </span>

                    <span class="text-center font-bold text-lg md:text-2xl text-primary-content">
                        Lives: <span v-html="livesString"></span>
                    </span>
                </div>
                <div
                    class="flex justify-center relative flex-1 min-h-4 max-w-full"
                >
                    <div
                        class="grid aspect-square border relative border-white md:w-full max-w-full max-h-full ground-grid bg-base-300"
                        :style="gridStyle"
                    >
                        <div
                            class="absolute w-64 h-52 bg-secondary bg-opacity-75 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 flex items-center justify-center z-10"
                            v-if="respawnCountdown !== null"
                        >
                            <span class="countdown font-mono font-bold text-6xl">
                                <span :style="`--value:${respawnCountdown};`"></span>
                            </span>
                        </div>
                        <span
                            class="w-full h-3/4 flex items-end justify-center absolute bottom-1/4 text-6xl font-bold text-success"

                            :class="{
                                'animate__animated animate__slow animate__fadeOutUp': pointsAdded,
                                'hidden': !pointsAdded,
                            }"
                        >
                            +{{pointsAdded}}
                        </span>
                        <span
                            class="w-full h-3/4 flex items-end justify-center absolute bottom-1/4 text-4xl font-semibold text-info"

                            :class="{
                                'animate__animated animate__slow animate__fadeOutUp': bonusAdded,
                                'hidden': !bonusAdded
                            }"
                        >   
                            &nbsp;&nbsp;+{{bonusAdded}}
                        </span>
                        <span
                            class="w-full h-3/4 flex items-end justify-center absolute bottom-1/4 text-6xl font-bold text-error"

                            :class="{
                                'animate__animated animate__slow animate__fadeOutUp': pointsSubtracted,
                                'hidden': !pointsSubtracted,
                            }"
                        >
                            {{pointsSubtracted}}
                        </span>
                        <span
                            class="w-full h-3/4 flex items-end justify-center absolute bottom-1/4 text-5xl font-bold"

                            :class="{
                                'animate__animated animate__slow animate__fadeOutUp': lifeLost,
                                'hidden': !lifeLost,
                            }"
                        >
                            💔
                        </span>
                        <template
                            v-for="(row, rowIndex) in grid"
                        >
                            <div
                                v-for="(cell, colIndex) in row"
                                :key="`${rowIndex}.${colIndex}`"
                                :class="[
                                    'cell md:leading-none font-semibold',
                                    cell?.snake ? 'snake' : '',
                                    (cell?.snake && cell.head) ? `head head_${direction} text-xs md:text-base` : '',
                                    (cell?.snake && cell.tail) ? `tail tail_${tailDirection}` : '',
                                    (cell?.snake && blinkSnake) ? 'opacity-50' : '',
                                    cell?.food ? 'food' : '',
                                    (cell?.food && cooldown) ? 'opacity-50' : ''
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
                <div class="flex flex-col gap-4 md:hidden">
                    <div class="flex w-full justify-center">
                      <button><kbd class="kbd bg-base-300 text-base-content shadow-md kbd-xl" @click="handleNav('up')">▲</kbd></button>
                    </div>
                    <div class="flex w-full justify-center gap-5">
                      <button><kbd class="kbd bg-base-300 text-base-content shadow-md kbd-xl" @click="handleNav('left')">◀︎</kbd></button>
                      <button><kbd class="kbd bg-base-300 text-base-content shadow-md kbd-xl" @click="handleNav('down')">▼</kbd></button>
                      <button><kbd class="kbd bg-base-300 text-base-content shadow-md kbd-xl" @click="handleNav('right')">▶︎</kbd></button>
                    </div>
                </div>

                <!-- MCQ -->
                <div class="w-full" v-if="gameStarted">
                    <div class="mt-2 w-full p-2 px-6 bg-warning text-warning-content rounded-xl font-vt323 md:text-lg font-medium leading-tight md:leading-normal">
                        <span class="font-extrabold">Q{{questionNum}}:</span> {{question}}
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-1 md:gap-2 mt-3 md:mt-4 font-vt323 text-sm leading-none md:text-base md:leading-normal font-medium">
                        <div v-for="(option, index) in options" class="p-2 px-5 bg-accent text-accent-content rounded-xl flex gap-2 items-center">
                            <div
                                class="font-bold border border-white h-4 w-4 flex-none"
                                :style="`background: ${option.color};`"
                            >
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
            <h3 class="font-bold text-xl text-primary-content">Ranium's SnaQ!</h3>
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
            <h3 class="font-bold text-lg">Ranium's SnaQ!</h3>
            <ol class="list-disc py-3 pl-2">
                <li><strong>Get ready to play!</strong> Answer MCQs while steering your snake with the arrow keys.</li>
                <li><strong>{{totalQuestions}} questions in total!</strong> Each comes with four options—choose wisely!</li>
                <li><strong>Quick Selection:</strong> Use your arrow keys to navigate and "eat" the answer food.</li>
                <li><strong>Earn & Deduct Points:</strong> Correct answers score points and speed up your snake, while wrong answers deduct points.</li>
                <li><strong>3 Lives & Cooldown:</strong> Hitting a wall or your tail costs a life, and there's a {{cooldownTime/1000}}-second break between questions.</li>
                <li><strong>Double Challenge:</strong> Manage both your snake and the MCQs. Good luck!</li>
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
            <!-- <h3 class="text-2xl font-bold text-center text-primary">Game Over!</h3> -->
            <h3 class="font-bold text-xl text-primary-content text-center">{{gameOverMsg}}</h3>
            <p class="text-2xl text-center pt-4">Final Score:</p>
            <h3 class="font-bold text-primary text-6xl text-center">{{ score }}</h3>
            <div v-if="score >= thresholdScore">
                <p class="mt-3 font-bold text-lg text-center">Please head to the Ranium booth for a chance to win an iPhone 16!</p>
                <p class="py-2 font-medium text-center" v-if="$page?.props?.appSettings?.allow_replay?.value ?? false">You can try again to beat the highscore. Click below button to restart the game. </p>
            </div>
            <p class="mt-3 font-bold text-lg text-center" v-else-if="$page?.props?.appSettings?.allow_replay?.value ?? false">Oh no! You fell just short.. You can restart to score more than {{thresholdScore}} before the link expires and get a change to win an iPhone 16.</p>
            <div class="modal-action justify-center">
                <Link class="btn btn-primary text-lg" :href="route('leaderboard', {event: $page.props.currentEvent.slug})" v-if="$page.props?.appSettings?.show_leaderboard?.value ?? false">Leaderboard</Link>
                <button class="btn btn-active text-lg" v-if="$page?.props?.appSettings?.allow_replay?.value ?? false" @click="resetGame">Restart</button>
            </div>
            <div class="flex justify-center gap-2 pt-4">
                <button class="btn btn-accent" @click="openAboutModal">About</button>
                <a href="https://x.com/ranium" target="_blank" class="btn btn-outline btn-accent">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-5" viewBox="0 0 512 512"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>
                </a>
            </div>
        </div>
    </dialog>

    <about-modal
        v-model="showAboutModal"
        @update:modelValue="handleAboutModalShow"
    />

    <template v-for="(src, key) in audios" :key="key">
        <audio :ref="key" preload="auto" :src="src"></audio>
    </template>
</template>

<script>
import { Head, usePage, Link } from '@inertiajs/vue3';
import AboutModal from "@/Components/AboutModal.vue";
import 'animate.css';

export default {
    name: "Game",
    components: {
        Head,
        Link,
        AboutModal
    },
    data() {
        return {
            rows: 20,
            cols: 20,
            snake: [{ row: 1, col: 1 }],
            food: { row: 0, col: 0 },
            direction: 'right',
            tailDirection: 'right',
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
            livesRemaining: this.lives,
            blinkSnake: false,
            blinkSnakeInterval: null,
            respawnCountdown: null,
            showAboutModal: false,
            directionsArray: {
                up: { dr: -1, dc:  0},
                down: { dr:  1, dc:  0},
                left: { dr:  0, dc: -1},
                right: { dr:  0, dc:  1},
            },
            audios: {
                countdown: '/sounds/countdown.mp3',
                move: '/sounds/move.wav',
                eat: '/sounds/eat.wav',
                hit: '/sounds/hit.wav',
                plusPoints: '/sounds/plusPoints.wav',
                minusPoints: '/sounds/minusPoints.wav',
                bonus: '/sounds/bonus.wav',
                gameOver: '/sounds/gameOver.wav'
            },
            pointsAdded: null,
            bonusAdded: null,
            pointsSubtracted: null,
            lifeLost: false
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
        thresholdScore: {
            type: Number,
            default: 250
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
        openAboutModal() {
            this.showAboutModal = true;
            this.$refs.gameEndModal.close();
        },
        handleAboutModalShow(val) {
            this.showAboutModal = false;
            if(val === false) {
                this.$refs.gameEndModal.showModal();
            }
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
        playSound(key) {
            try {
                const sound = new Audio(this.audios[key] ?? '');
                if(sound) {
                    sound.play();   
                }
            }
            catch(err) {
                console.info(err)
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
                    setTimeout(() => {
                        this.playSound('countdown');
                        this.vibrate([70, 1000, 70, 1000, 70, 1000, 100]);
                    }, 100)
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
        getNextHeadPosition(dir) {
            const nd = this.directionsArray[dir] ?? {dr: 0, dc: 0};
            return {
                row: (this.snake[0].row + nd.dr),
                col: (this.snake[0].col + nd.dc)
            }
        },
        handleNav(dir) {
            this.playSound('move');
            this.vibrate(50);
            let newDir = null
            switch(dir) {
                case 'left':
                    newDir = this.direction !== 'right' ? 'left' : 'right';
                    break;
                case 'up':
                    newDir = this.direction !== 'down' ? 'up' : 'down';
                    break;
                case 'right':
                    newDir = this.direction !== 'left' ? 'right' : 'left';
                    break;
                case 'down':
                    newDir = this.direction !== 'up' ? 'down' : 'up';
                    break;
                default:
                    break;
            }
            const currSec = this.snake[1]
            const nextHead = this.getNextHeadPosition(newDir);

            if(this.snake.length !== 1 && currSec.row === nextHead.row && currSec.col === nextHead.col)
                return;
            this.direction = newDir;
        },
        handleKeydown (e) {
            if([37, 38, 39, 40].includes(e.keyCode)) {
                this.playSound('move');
                this.vibrate(50);
                let newDir = null
                switch (e.keyCode) {
                    case 37:
                        newDir = this.direction !== 'right' ? 'left' : 'right';
                        break;
                    case 38:
                        newDir = this.direction !== 'down' ? 'up' : 'down';
                        break;
                    case 39:
                        newDir = this.direction !== 'left' ? 'right' : 'left';
                        break;
                    case 40:
                        newDir = this.direction !== 'up' ? 'down' : 'up';
                        break;
                    default:
                        // console.log(e.keyCode);
                        break;
                }
                const currSec = this.snake[1]
                const nextHead = this.getNextHeadPosition(newDir);

                if(this.snake.length !== 1 && currSec.row === nextHead.row && currSec.col === nextHead.col)
                    return;
                this.direction = newDir;
            }
        },
        handleGameEnd(gameOverMsg) {
            clearInterval(this.gameInterval)
            this.playSound('gameOver');
            this.vibrate([0, 150, 300, 150, 300]);
            this.gameOverMsg = gameOverMsg;
            this.$refs.gameEndModal.showModal();
        },
        vibrate(args) {
            if('vibrate' in navigator)
                navigator.vibrate(args)
        },
        move() {
            const prevSnake = [...this.snake];
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
                this.handleSnakeHit(prevSnake,'hitWall');
                return;
            }

            // Check collision with itself
            if (this.snake.some(segment => segment.row === head.row && segment.col === head.col)) {
                this.handleSnakeHit(prevSnake, 'hitSelf');
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

            this.tailDirection = this.getTailDirection();
        },
        getTailDirection(snake = this.snake, direction = this.direction) {
            let tailDir = null;
            if(snake.length > 1) {
                const lastBlock = snake[snake.length - 1];
                const secondLastBlock = snake[snake.length - 2];
                const dirString = `${lastBlock.row - secondLastBlock.row}${lastBlock.col - secondLastBlock.col}`;

                switch(dirString) {
                    case '01':
                        tailDir = 'left';
                        break;
                    case '0-1':
                        tailDir = 'right';
                        break;
                    case '10':
                        tailDir = 'up';
                        break;
                    case '-10':
                        tailDir = 'down';
                        break;
                }
            }
            else {
                tailDir = direction;
            }
            return tailDir;
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
        handleSnakeHit(prevSnakeState, action) {
            this.playSound('hit');
            this.lifeLost = true;
            this.vibrate([200, 100, 200]);
            setTimeout(() => {
                this.lifeLost = false
            }, 2300);
            clearInterval(this.gameInterval);
            this.snake = prevSnakeState;
            this.startBlinkSnake();
            const newSnake = this.spawnNewSnake();
            axios.post(`/api/${usePage().props.currentEvent.slug}/game-action`, {
                action
            }).then(res => {
                const data = res.data;
                if(data.gameOver)
                    this.handleGameEnd(data.gameOverMessage);
                else {
                    this.score = data.points;
                    this.livesRemaining = data.lives;
                    this.unblinkSnake();
                    if(newSnake.snake.length) {
                        this.snake = newSnake.snake;
                        this.direction = newSnake.direction;
                        this.tailDirection = newSnake.tailDirection;
                    }
                    this.respawnCountdown = 3;
                    setTimeout(() => {
                        this.playSound('countdown');
                        this.vibrate([70, 1000, 70, 1000, 70, 1000, 100]);
                    }, 400);
                    const respawnInterval = setInterval(() => {
                        this.respawnCountdown--;
                        if(this.respawnCountdown === 0) {
                            clearInterval(respawnInterval);
                            this.respawnCountdown = null;
                            this.changeSnakeSpeed();
                        }
                    }, 1000)
                }
            });
        },
        startBlinkSnake() {
            this.blinkSnakeInterval = setInterval(() => {
                this.blinkSnake = !this.blinkSnake;
            }, 100)
        },
        unblinkSnake() {
            if(this.blinkSnakeInterval && this.blinkSnakeInterval !== null) {
                clearInterval(this.blinkSnakeInterval);
                this.blinkSnakeInterval = null;
            }
            this.blinkSnake = false;
        },
        spawnNewSnake() {
            
            const foodSet = new Set(this.options.map(opt => `${opt.position[0]},${opt.position[1]}`));
            const snakeLength = this.snake.length;

            const inBounds = (r, c) => {
                return (r >= 0 && r < this.rows && c >= 0 && c < this.cols);
            }

            function isFood(r, c) {
                return foodSet.has(`${r},${c}`);
            }

            function inSnake(r, c, snakeArray) {
                return snakeArray.some(segment => segment.row === r && segment.col === c);
            }

            const directions = Object.entries(this.directionsArray).map((entry) => ({...entry[1], direction: entry[0]}));
            
            function randomChoice(array) {
                return array[Math.floor(Math.random() * array.length)];
            }

            function randomInRange(min, max) {
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }

            function oppositeDirection(direction) {
                return {
                    dr: direction.dr * -1,
                    dc: direction.dc * -1
                }
            }

            function turnDirections(direction) {
                return [
                    {
                        dr: direction.dc,
                        dc: direction.dr
                    },
                    {
                        dr: direction.dc * -1,
                        dc: direction.dr * -1
                    }
                ]
            }
            
            const maxAttempts = 1000;

            for (let attempt = 0; attempt < maxAttempts; attempt++) {
                const headRow = randomInRange(3, this.rows - 4);
                const headCol = randomInRange(3, this.cols - 4);
                if (isFood(headRow, headCol)) {
                    continue;
                }

                const headDirection = randomChoice(directions);

                const newSnake = [{ row: headRow, col: headCol }];
                let segmentLength = 1;
                let spawnDirection = oppositeDirection(headDirection);
                let turnedDirections = 0;
                let justTurned = false;

                for (let segmentIndex = 1; segmentIndex < snakeLength; segmentIndex++) {
                    if(turnedDirections > 3)
                        break;

                    const prev = newSnake[newSnake.length - 1];

                    const nextR = prev.row + spawnDirection.dr;
                    const nextC = prev.col + spawnDirection.dc;

                    if (
                        inBounds(nextR, nextC) &&
                        !isFood(nextR, nextC) &&
                        !inSnake(nextR, nextC, newSnake)
                    ) {
                        newSnake.push({ row: nextR, col: nextC });
                        segmentLength++;
                        justTurned = false;
                        if(segmentLength >= 3 && turnedDirections < 3) {
                            const turnDir = randomChoice([true, false]);
                            if(turnDir) {
                                spawnDirection = randomChoice(turnDirections(spawnDirection));
                                segmentLength = 0
                                justTurned = true;
                                turnedDirections++;
                            }
                        }
                    }
                    else if(justTurned === false) {
                        spawnDirection = randomChoice(turnDirections(spawnDirection));
                        justTurned = true
                        turnedDirections++;
                        segmentIndex--;
                        continue;
                    }
                    else
                        break;
                }

                if (newSnake.length === snakeLength) {
                    // console.log(attempt)
                    return {
                        snake: newSnake,
                        direction: headDirection.direction,
                        tailDirection: this.getTailDirection(newSnake, headDirection.direction)
                    }
                    break;
                }
            }
        },
        handleFoodEat(pos) {
            this.playSound('eat');
            this.vibrate([40, 20, 40]);
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
                    const pointsDiff = data.points - this.score;
                    if(pointsDiff > 0) {
                        this.pointsAdded = pointsDiff - data.bonus_points;
                        this.playSound('plusPoints');
                        setTimeout(() => {
                            this.pointsAdded = null
                        }, 2300);
                        if(data.bonus_points) {
                            setTimeout(() => {
                                this.bonusAdded = data.bonus_points;
                                this.playSound('bonus');
                                setTimeout(() => {
                                    this.bonusAdded = null
                                }, 2300)
                            }, 200);
                        }
                    }
                    else if(pointsDiff < 0) {
                        this.pointsSubtracted = pointsDiff;
                        this.playSound('minusPoints');
                        setTimeout(() => {
                            this.pointsSubtracted = null
                        }, 2300);
                    }
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

            this.snake.forEach((segment, ind) => {
                grid[segment.row][segment.col] = {
                    snake: true,
                    head: (ind === 0) ? true : false,
                    tail: ((ind === (this.snake.length - 1)) && (this.snake.length !== 1)) ? true : false
                };
            });

            if(this.gameStarted) {
                this.options?.forEach((d, i) => {
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
    background-size: cover;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
    animation: pulse 1.5s infinite;
    transition: transform 0.2s, box-shadow 0.2s;
}

@keyframes pulse {
  0% {
    transform: scale(1);
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
  }
  50% {
    transform: scale(1.05);
    box-shadow: 3px 3px 8px rgba(0, 0, 0, 0.4);
  }
  100% {
    transform: scale(1);
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
  }
}

.snake {
    background-color: #e940a8;
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
    color: white;
    border: 1.5px solid white;
    width: 100%;
    height: 100%;
    display: block;
}

.snake.head {
    border-top-right-radius: 50%;
    border-bottom-right-radius: 50%;
}

.snake.head::after {
    content: ':';
    display: flex;
    justify-content: space-around;
    align-items: flex-end;
    border-top-right-radius: 50%;
    border-bottom-right-radius: 50%;
}

.snake.head.head_up {
    transform: rotate(270deg);
}

.snake.head.head_down {
    transform: rotate(90deg);
}

.snake.head.head_left {
    transform: rotate(180deg);
}

.snake.tail {
    border-top-left-radius: 900%;
    border-bottom-left-radius: 900%;
}

.snake.tail::after {
    border-top-left-radius: 900%;
    border-bottom-left-radius: 900%;
}

.snake.tail.tail_up {
    transform: rotate(270deg);
}

.snake.tail.tail_down {
    transform: rotate(90deg);
}

.snake.tail.tail_left {
    transform: rotate(180deg);
}

.food>span {
    display: flex;
    justify-content: center;
    align-items: center;
}

.kbd-xl {
    min-width: 3.5rem;
    min-height: 3.5rem;
    font-size: 1.7rem;
}

.font-vt323 {
  font-family: "VT323", monospace;
}
</style>
<style>
@keyframes fadeOutUp {
  from {
    opacity: 1;
  }

  to {
    opacity: 0;
    transform: translate3d(0, -100%, 0);
  }
}
</style>