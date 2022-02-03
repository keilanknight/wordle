const game = {
  currentWord: "",
  currentPos: 0,
  minPos: 0,
  maxPos: 5,
  lastPos: 0,
  finished: false,
  win: false,
  url: "api/",
};

_("keyboard").addEventListener("click", clickButton);

function clickButton(e) {
  let action = e.target.id;

  switch (action) {
    case "keyboard":
      return;
    case "Enter":
      enter();
      break;
    case "Del":
      del();
      break;
    default:
      letter(action);
      break;
  }
}

function _(id) {
  return document.getElementById(id);
}

function add(el) {
  return document.createElement(el);
}

function endGame() {
  /* Remove event listener from buttons */
  _("keyboard").removeEventListener("click", clickButton);

  /* Disable the keyboard */
  _("keyboard")
    .querySelectorAll("button")
    .forEach((e) => {
      e.disabled = true;
    });

  if (!game.win) showBanner("GAME OVER!");
}

function showBanner(txt) {
  let color = txt == "CORRECT!" ? "green" : "red";
  let banner = add("div");

  banner.id = "banner";
  banner.classList.add("banner");
  banner.classList.add(color);
  banner.innerText = txt;

  /* display banner then timeout remove */
  document.querySelector("section").appendChild(banner);

  setTimeout(() => {
    _("banner").remove();
  }, 4000);
}

function enter() {
  if (game.currentPos != game.maxPos) return;

  if (game.currentPos == 30) return checkWord(game.currentWord);

  game.minPos += 5;
  game.maxPos += 5;

  checkWord(game.currentWord);
  game.currentWord = "";
}

function checkWord(word) {
  fetch(game.url, { method: "POST", body: word })
    .then((res) => res.json())
    .then((res) => {
      updateGame(res);
    })
    .catch((err) => {
      console.log(err);
    });
}

function updateGame(res) {
  /* Clear out previous answer */
  for (i = 0; i < 5; i++) {
    let ltr = _("ltr-" + game.lastPos);
    ltr.innerText = "";
    game.lastPos++;
  }

  /* Reset last pos and recreate our letters */
  game.lastPos -= 5;

  for (i = 0; i < 5; i++) {
    let ltr = _("ltr-" + game.lastPos);
    let r = res.result[i];
    game.lastPos++;

    setTimeout(() => {
      ltr.innerText = r.letter;
      if (r.style) ltr.style = ltr.classList.add(r.style);
    }, i * 300);
  }

  if (res.answer == "correct") {
    game.win = true;
    showBanner("CORRECT!");
    endGame();
  }

  if (game.currentPos == 30) endGame();
}

function del() {
  if (game.currentPos == game.minPos) return;

  game.currentPos--;

  let letter = _("ltr-" + game.currentPos);
  letter.innerText = "";
  game.currentWord = game.currentWord.slice(0, -1);
}

function letter(char) {
  if (game.currentPos == game.maxPos) return;

  let letter = _("ltr-" + game.currentPos);

  letter.innerText = char;
  game.currentWord += char;
  game.currentPos++;
}
