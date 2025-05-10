function roll() { return 1 + Math.floor(Math.random() * 6); }
const maxProgress = { 2: 3, 3: 5, 4: 7, 5: 9, 6: 11, 7: 13, 8: 11, 9: 9, 10: 7, 11: 5, 12: 3 };

let sprites;
const PSprites = "ACDEB";
const DSprites = ["", "die1", "die2", "die3", "die4", "die5", "die6"];

class Game {
    constructor(options, me, history) {
        this.players = options.players;
        this.online = options.online;
        this.me = me;

        this.playerProgress = {};
        this.playerColumnsComplete = {};
        for (let i = 0; i < this.players; i++) {
            this.playerProgress[i] = {};
            this.playerColumnsComplete[i] = 0;
        }
        this.columnComplete = {};

        this.markersAvailable = 3;
        this.markerProgress = {};
        this.markersOn = {};
        this.markerComplete = {};

        this.playerTurn = 0;
        this.rolled = false;

        document.querySelector(".action.roll").addEventListener("click", () => this.rollPressed());
        document.querySelector(".action.stop").addEventListener("click", () => this.stop());

        if (history) {
            for (const h of history) {
                this.replayHistory(h);
            }
        }
        this.updateUI();
    }

    placeToken(row, col, name) {
        const tile = document.querySelector(`#board > div:nth-child(${row + 1}) > div:nth-child(${col + 1})`);
        tile.appendChild(sprites[name].make());
    }

    commitMarkers() {
        const p = this.playerTurn;
        for (let c = 2; c <= 12; c++) {
            if (!this.markerProgress[c]) continue;
            if (this.markerProgress[c] === maxProgress[c]) {
                this.columnComplete[c] = true;
                this.playerColumnsComplete[p]++;
                for (let i = 0; i < this.players; i++) {
                    this.playerProgress[i][c] = 0;
                }
            }
            this.playerProgress[p][c] = this.markerProgress[c];
        }
    }

    win() {
        const sprite = sprites[PSprites[this.playerTurn]];
        const winElement = document.getElementById("win");
        winElement.style.display = "block";
        winElement.textContent = `Player ${this.playerTurn + 1} is the winner!`;
        winElement.prepend(sprite.make());
        winElement.append(sprite.make());
    }

    stop() {
        this._endTurn(false);
    }

    _endTurn(busted) {
        if (!busted) this.commitMarkers();
        this.markersAvailable = 3;
        this.markerProgress = {};
        this.markersOn = {};
        this.markerComplete = {};

        if (this.playerColumnsComplete[this.playerTurn] >= 3) {
            this.updateUI();
            this.win();
            document.querySelectorAll(".action").forEach(el => el.style.display = "none");
            return;
        } else {
            this.playerTurn = (this.playerTurn + 1) % this.players;
            this.rolled = false;
            this.updateUI();
        }
    }

    select(s) {
        this.rolled = false;
        for (const c of s) this.advanceMarker(c);
        this.updateUI();
    }

    advanceMarker(c) {
        if (this.markerComplete[c]) return;
        if (!this.markersOn[c]) {
            this.markerProgress[c] = this.playerProgress[this.playerTurn][c] || 0;
            this.markersAvailable--;
            this.markersOn[c] = true;
        }
        this.markerProgress[c]++;
        this.markerComplete[c] = (this.markerProgress[c] >= maxProgress[c]);
    }

    rollPressed() {
        const dice = [roll(), roll(), roll(), roll()];
        this.roll(dice);
    }

    roll(dice) {
        if (this.replay) {
            this._rolled(dice);
            return;
        }
        const rollTime = 900;
        document.querySelectorAll("#action-area .action").forEach(el => el.style.display = "none");
        for (let i = 0; i < rollTime; i += 50) {
            setTimeout(() => {
                document.querySelectorAll(".die").forEach(d => {
                    d.innerHTML = "";
                    d.appendChild(sprites[DSprites[roll()]].make());
                });
            }, i);
        }
        setTimeout(() => { this._rolled(dice); }, rollTime);
        this.updateUI();
    }

    canAdvance(player, columns) {
        let markersNeeded = 0;
        for (let i = 0; i < columns.length; i++) {
            const c = columns[i];
            if (this.markerComplete[c] || this.columnComplete[c]) return false;
            if (!this.markersOn[c]) markersNeeded++;
        }
        if (columns.length === 2 && columns[0] === columns[1]) markersNeeded--;
        return markersNeeded <= this.markersAvailable;
    }

    _rolled(dice) {
        let stuck = true;

        const setDiceOption = (element, dice) => {
            for (let i = 0; i < 4; i++) {
                const die = element.querySelector(`.die:nth-child(${i + 1})`);
                die.innerHTML = "";
                die.appendChild(sprites[DSprites[dice[i]]].make());
            }

            const sums = [dice[0] + dice[1], dice[2] + dice[3]];
            const actionSection = element.querySelector("div:nth-child(5)");
            actionSection.innerHTML = "";

            const addAdvance = (s) => {
                const action = document.createElement("div");
                action.className = "action";
                action.textContent = s.length === 1 ? `Advance on ${s[0]}` : `Advance on ${s[0]} & ${s[1]}`;
                action.style.display = "none";
                action.addEventListener("click", () => this.select(s));
                actionSection.appendChild(action);
                stuck = false;
            };

            if (this.canAdvance(this.playerTurn, sums)) {
                addAdvance(sums);
            } else {
                if (this.canAdvance(this.playerTurn, [sums[0]])) addAdvance([sums[0]]);
                if (this.canAdvance(this.playerTurn, [sums[1]])) addAdvance([sums[1]]);
            }
        };

        document.querySelectorAll(".dice-option").forEach((el, i) => {
            const dicePermutations = [
                [dice[0], dice[1], dice[2], dice[3]],
                [dice[0], dice[2], dice[1], dice[3]],
                [dice[0], dice[3], dice[1], dice[2]]
            ];
            setDiceOption(el, dicePermutations[i]);
        });

        this.rolled = true;
        if (stuck) this._endTurn(true);
        this.updateUI();
    }

    updateUI() {
        if (this.replay) return;
        document.querySelectorAll(".sprite .sprite").forEach(el => el.remove());

        for (let i = 0; i < this.markersAvailable; i++) this.placeToken(13, i, "x");

        const columnStart = { 2: 8, 3: 9, 4: 10, 5: 11, 6: 12, 7: 13, 8: 12, 9: 11, 10: 10, 11: 9, 12: 8 };
        for (let c = 2; c <= 12; c++) {
            const r = columnStart[c];
            for (let p = 0; p < this.players; p++) {
                if (this.columnComplete[c]) {
                    for (let i = 0; i < (this.playerProgress[p][c] || 0); i++) {
                        this.placeToken(r - this.playerProgress[p][c] + i + 1, c - 2, PSprites[p]);
                    }
                } else if (this.playerProgress[p][c]) {
                    this.placeToken(r - this.playerProgress[p][c] + 1, c - 2, PSprites[p]);
                }
            }
            if (this.markerProgress[c]) this.placeToken(r - this.markerProgress[c] + 1, c - 2, "x");
        }

        const turnInfo = document.getElementById("turn-info");
        turnInfo.textContent = `It's Player ${this.playerTurn + 1}'s turn`;
        if (this.online && this.playerTurn === this.me) {
            turnInfo.append(" (You)");
        }
        const sprite = sprites[PSprites[this.playerTurn]];
        turnInfo.prepend(sprite.make());
        turnInfo.append(sprite.make());

        if (!this.online || this.playerTurn === this.me) {
            document.querySelectorAll(".action").forEach(el => el.style.display = "block");

            if (this.rolled) {
                document.querySelectorAll("#action-area .action").forEach(el => el.style.display = "none");
            } else {
                document.querySelectorAll("#dice-area .action").forEach(el => el.remove());
                document.querySelectorAll("#action-area .action").forEach(el => el.style.display = "block");
                if (this.markersAvailable === 3) {
                    document.querySelector(".action.stop").style.display = "none";
                }
            }
        } else {
            document.querySelectorAll(".action").forEach(el => el.style.display = "none");
        }
    }

    replayHistory(h) {
        this.replay = true;
        if (this[h.name]) this[h.name](...h.args);
        delete this.replay;
    }
}

async function init() {
    const spriteNames = [
        "die1", "die2", "die3", "die4", "die5", "die6", " ", "^", "|", "v", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "o", "x", "A", "B", "C", "D", "E"
    ];
    sprites = await Sprites.loadAll("./assets/GuiElements.png", 64, spriteNames);

    const board = [
        "     7      ",
        "    6^8     ",
        "   5^|^9    ",
        "  4^|||^a   ",
        " 3^|||||^b  ",
        "2^|||||||^c ",
        "^|||||||||^ ",
        "||||||||||| ",
        "v|||||||||v ",
        " v|||||||v  ",
        "  v|||||v   ",
        "   v|||v    ",
        "    v|v     ",
        "ooo  v      ",
    ];
    board.forEach((row, rowNum) => {
        const r = document.createElement("div");
        document.getElementById("board").appendChild(r);
        row = [...row];
        row.forEach((symbol, colNum) => {
            const t = sprites[symbol].make();
            r.appendChild(t);
        });
    });

    window.multiplayer = new Multiplayer("cantstop");
    multiplayer.registerBroadcastMethods(["stop", "select", "roll"]);

    multiplayer.on("init", (options, player, history) => {
        multiplayer.proxy(new Game(options, player, history));
        window.game = multiplayer;
    });

    if (multiplayer.isExistingGame()) {
        document.getElementById("game-options").style.display = "none";
        multiplayer.connectExisting();
    } else {
        document.querySelectorAll("#action-area .action").forEach(el => el.style.display = "none");
        document.querySelectorAll("#game-options .action").forEach(action => {
            action.addEventListener("click", (e) => {
                const target = e.target;
                const online = target.dataset.online === "true";
                const players = parseInt(target.dataset.players, 10);
                const options = { online, players };

                if (online) multiplayer.create(options);
                else window.game = new Game(options);

                document.getElementById("game-options").style.display = "none";
            });
        });
    }
}


init();
