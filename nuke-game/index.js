const GRID_SIZE = 10;
const GOALS = 3;
const TITLE = document.getElementById('title');
const GRID = document.querySelector('.grid');
const RESET_BUTTON = document.getElementById('reset');
const CLASS_SQUARE = 'square';
const CLASS_PLAYER = 'player';

let tries = 0;
let goals = getRandomGoals();
let playerPosition = { x: 0, y: 9 };
let foundGoals = 0;
let possibleMoves = [];

function getRandomGoals() {
    let goals = [];
    while (goals.length < GOALS) {
        let newGoal = {
            x: getRandomCoord(),
            y: getRandomCoord()
        };
        if (!goals.some(
                goal => goal.x === newGoal.x
                    && goal.y === newGoal.y)
        ) {
            goals.push(newGoal);
        }
    }
    return goals;
}

function getRandomCoord() {
    return Math.floor(Math.random() * GRID_SIZE);
}

function createGrid() {
    for (let y = 0; y < GRID_SIZE; y++) {
        for (let x = 0; x < GRID_SIZE; x++) {
            const square = document.createElement('div');
            square.dataset.x = String(x);
            square.dataset.y = String(y);
            square.dataset.dead = String(false);
            square.classList.add(CLASS_SQUARE);
            square.addEventListener('click', function() {
                clickSquare(makePos(x, y));
            });
            if (
                x === playerPosition.x
                && y === playerPosition.y
            ) {
                square.classList.add(CLASS_PLAYER);
            }
            GRID.appendChild(square);
        }
    }
    highlightMoves();
}

function makePos(x, y) {
    return {x: x, y: y};
}

function highlightMoves() {
    document.querySelectorAll('.possible-move').forEach(square => {
        square.classList.remove('possible-move');
    });
    const moves = [
        { x: playerPosition.x - 1, y: playerPosition.y },
        { x: playerPosition.x + 1, y: playerPosition.y },
        { x: playerPosition.x, y: playerPosition.y - 1 },
        { x: playerPosition.x, y: playerPosition.y + 1 }
    ];
    possibleMoves = [];
    moves.forEach(pos => {
        const square = getSquare(pos);
        if (square && (square.dataset.dead === String(false))) {
            const moveSquare = getSquare(pos);
            if (moveSquare && !moveSquare.innerHTML) {
                moveSquare.classList.add('possible-move');
            }
            possibleMoves.push(pos);
        }
    });
}

function getSquare(pos) {
    return document.querySelector(`.square[data-x="${pos.x}"][data-y="${pos.y}"]`);
}

function movePlayer(pos) {
    playerPosition = pos;
    updateGrid();
}

function clickSquare(pos) {
    if (hasPos(pos, possibleMoves)) {
        movePlayer(pos);
    } else {
        checkSquare(pos);
    }
}

function hasPos(pos, source) {
    return source.some(
        position => position.x === pos.x
        && position.y === pos.y
    );
}
function checkSquare(pos) {
    const square = getSquare(pos);
    if (square.dataset.dead === String(true)) {
        return;
    }
    if (goals.some(goal => goal.x === pos.x && goal.y === pos.y)) {
        square.innerHTML = '✅';
        square.removeEventListener('click', checkSquare);
        foundGoals++;
        if (foundGoals === goals.length) {
            alert('Congratulations! You found all the goals.');
        }
    } else {
        square.innerHTML = '❌';
    }
    console.log(555);
    square.dataset.dead = String(true);
    tries++;
    TITLE.textContent = `Tries: ${tries}`;
}

function resetGame() {
    GRID.innerHTML = '';
    tries = 0;
    foundGoals = 0;
    TITLE.textContent = 'Tries: 0';
    goals = getRandomGoals();
    playerPosition = { x: 0, y: 9 };
    createGrid();
}

function updateGrid() {
    document.querySelectorAll('.square').forEach(square => {
        square.classList.remove('player');
        square.removeEventListener('contextmenu', movePlayer);
    });
    const playerSquare = document.querySelector(`.square[data-x="${playerPosition.x}"][data-y="${playerPosition.y}"]`);
    playerSquare.classList.add('player');
    highlightMoves();
}

function main() {
    document.addEventListener('contextmenu', function(event) {
        event.preventDefault();
    });
    RESET_BUTTON.addEventListener('click', resetGame);
    createGrid();
}

document.addEventListener('DOMContentLoaded', () => {
    main();
});

