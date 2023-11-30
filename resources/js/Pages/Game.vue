<template>
    <div
        class="max-w-lg mx-auto grid aspect-square"
        :style="gridStyle"
    >
        <template
            v-for="(row, rowIndex) in grid"
        >
            <div
                v-for="(cell, colIndex) in row"
                :key="`${rowIndex}.${colIndex}`"
                :class="['cell', cell === 'snake' ? 'snake' : '', cell === 'food' ? 'food' : '']"
            ></div>
        </template>
    </div>
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
            timeout: 300,
            gameInterval: null
        }
    },
    beforeMount () {
        window.addEventListener('keydown', this.handleKeydown, null);
    },
    mounted() {
        this.generateFood();
        this.startGame();
    },
    beforeDestroy () {
        window.removeEventListener('keydown', this.handleKeydown);
    },
    methods: {
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
                alert('Game Over! You hit the wall.');
                this.resetGame();
                return;
            }

            // Check collision with itself
            if (this.snake.some(segment => segment.row === head.row && segment.col === head.col)) {
                alert('Game Over! You collided with yourself.');
                this.resetGame();
                return;
            }

            let unshifted = false;
            // Check collision with food
            if (head.row === this.food.row && head.col === this.food.col) {
                this.snake.unshift({ ...this.food });
                unshifted = true;
                this.generateFood();
            } else {
                this.snake.pop();
            }

            if(!unshifted) {
                this.snake.unshift(head);
            }
        },
        generateFood() {
            this.food.row = Math.floor(Math.random() * this.rows);
            this.food.col = Math.floor(Math.random() * this.cols);
        },
        resetGame() {
            this.snake = [{ row: 0, col: 0 }];
            this.direction = 'right';
            this.generateFood();
        }
    },
    computed: {
        grid() {
            const grid = Array.from({ length: this.rows }, () => Array(this.cols).fill(0));

            this.snake.forEach(segment => {
                grid[segment.row][segment.col] = 'snake';
            });

            grid[this.food.row][this.food.col] = 'food';

            return grid;
        },
        gridStyle() {
            return {
                "grid-template-columns": `repeat(${this.rows}, minmax(0, 1fr))`
            }
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
}
</style>
