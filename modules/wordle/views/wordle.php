<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1,user-scalable=0"/>
	<meta name="apple-mobile-web-app-capable" content="yes">
    <title>Trongate Wordle</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/dark.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>wordle_module/css/style.css">
</head>

<body>
    <section>

        <h1>Trongate Wordle</h1>

        <div class="wordle">
            <?php for ($i = 0; $i < 30; $i++) : ?>
                <div id="ltr-<?= $i ?>" class="letter"></div>
            <?php endfor; ?>
        </div>

        <div id="keyboard" class="keyboard">
            <?php foreach ($keyboard as $keys) : ?>
                <?php foreach ($keys as $key) : ?>
                    <button id="<?= $key ?>"><?= $key ?></button>
                <?php endforeach; ?>
                <br>
            <?php endforeach; ?>
        </div>

        <p>
            <b>Played:</b> <?= $games ?> |
            <b>Won:</b> <?= $won ?> |
            <b>Streak:</b> <?= $streak ?> |
            <b>Best Streak:</b> <?= $best ?>
        </p>
        <p>Join The Revolution <a href="http://www.trongate.io">Today!</a></p>
    </section>
    <script src="<?= BASE_URL ?>wordle_module/js/script.js"></script>
</body>

</html>