const game = {
  currentWord: "",
  todaysWord: "",
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

  fadeout();
}

function reload() {
  location.reload();
}

function fadeout() {
  let div = add("div");
  div.id = "fadeout";
  div.classList.add("fadeout");

  setTimeout(() => {
    document.querySelector("body").appendChild(div);
    _("fadeout").addEventListener("click", reload);
  }, 1500);
}

function showBanner(txt) {
  let color = txt == "CORRECT!" ? "green" : "red";
  let banner = add("div");

  banner.id = "banner";
  banner.classList.add("banner");
  banner.classList.add(color);

  if (txt == "CORRECT!") {
    banner.innerText = txt;
  } else {
    banner.innerHTML =
      "<p style='font-size:16px'> \
       The answer was " +
      game.todaysWord +
      "</p>";
  }

  setTimeout(() => {
    document.querySelector("body").appendChild(banner);
  }, 1500);
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
  if (res.todays_word) game.todaysWord = res.todays_word;
  else game.todaysWord = "";

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

    /* Update the buttons, unless answer is not valid word */
    if (r.style == "wrong") continue;

    setTimeout(() => {
      if (!r.style) {
        _(r.letter).className = "";
        _(r.letter).classList.add("missing");
      } else {
        _(r.letter).className = "";
        _(r.letter).classList.add(r.style);
      }
    }, 1500);
  }

  if (res.answer == "correct") {
    game.win = true;
    showBanner("CORRECT!");
    return endGame();
  }

  if (res.answer == "invalid") {
    return endGame();
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
