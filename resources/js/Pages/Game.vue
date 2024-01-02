<template>
    <div class="flex justify-center w-full">
        <div class="max-w-lg w-full flex flex-col items-center gap-2">
            <span>
                Score: {{score}}
            </span>
            <div
                class="w-full grid aspect-square"
                :style="gridStyle"
            >
                <template
                    v-for="(row, rowIndex) in grid"
                >
                    <div
                        v-for="(cell, colIndex) in row"
                        :key="`${rowIndex}.${colIndex}`"
                        :class="['cell', cell === 'snake' ? 'snake' : '', cell?.food ? 'food' : '']"
                    >{{cell.text ?? ''}}</div>
                </template>
            </div>
            <div class="w-full" v-if="gameStarted">
                <div class="mt-2 w-full p-2 bg-primary text-primary-content rounded">Q: {{questions[position].question}}?</div>
                <div class="grid grid-cols-2 gap-3 mt-3">
                    <span v-for="(option, index) in questions[position].options" class="p-2 bg-primary text-primary-content rounded">{{String.fromCharCode(65 + index)}}: {{option.value}}</span>
                </div>
            </div>
        </div>
    </div>

    <dialog id="game_start_modal" class="modal" ref="gameStartModal" open>
        <div class="modal-box">
            <h3 class="font-bold text-lg">Laracon 2024 Snake Game!</h3>
            <ol class="list-disc py-3 pl-2">
                <li>A mathematical question will appear one after another with four options.</li>
                <li>Eat the food with the correct option to score a point else you lose a point.</li>
            </ol>
            <p class="pb-4">Click below button to start the game.</p>
            <div class="modal-action justify-center">
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
            timeout: 200,
            gameInterval: null,
            gameOverMsg: '',
            questions: [
                this.firstQuestion
            ],
            position: 0,
            gameStarted: false,
            score: 0
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
    },
    mounted() {
    },
    beforeDestroy () {
        window.removeEventListener('keydown', this.handleKeydown);
    },
    methods: {
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
.cell {
    aspect-ratio: 1;
    border: 1px solid #ddd;
}

.snake {
    background-color: #4CAF50;
}

.food {
    background-color: #FF5733;
    color: white;
    text-align: center;
}
</style>
