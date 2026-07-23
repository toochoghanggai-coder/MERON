<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
$GAME_VER = '20260607_06';
$startGame = $_GET['game'] ?? '';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>こども知育ゲーム</title>
<link rel="stylesheet" href="common.css?v=<?= $GAME_VER ?>">
</head>
<body>
<div class="wrap">
  <section id="menuScreen" class="card menu-card">
    <h1 class="title">こどもゲーム</h1>
    <div class="hint">えを タップして あそぼう</div>
    <div class="version">v<?= $GAME_VER ?></div>
    <div class="menu-grid">
      <button class="menu-btn" data-game="onaji"><span class="menu-emoji">🚗</span><span class="menu-label">おなじ</span></button>
      <button class="menu-btn" data-game="shape"><span class="menu-emoji">🔺</span><span class="menu-label">かたち</span></button>
      <button class="menu-btn" data-game="ookii"><span class="menu-emoji">🔴</span><span class="menu-label">おおきさ</span></button>
      <button class="menu-btn" data-game="animal"><span class="menu-emoji">🐶</span><span class="menu-label">どうぶつ</span></button>
      <button class="menu-btn" data-game="nakama"><span class="menu-emoji">🍎</span><span class="menu-label">なかま</span></button>
      <button class="menu-btn" data-game="order"><span class="menu-emoji">🍎🚗</span><span class="menu-label">じゅんばん</span></button>
      <button class="menu-btn" data-game="iro"><span class="menu-emoji">🌈</span><span class="menu-label">いろ</span></button>
    </div>
  </section>

  <section id="gameScreen" class="hidden">
    <div class="topnav">
      <button id="backMenu" class="navlink">🏠 メニュー</button>
      <button id="restartGame" class="navlink">🔄 もう一回</button>
    </div>
    <div class="card">
      <h1 id="gameTitle" class="title"></h1>
      <div id="targetArea"></div>
      <div id="choiceArea" class="choices"></div>
      <div id="msg" class="msg"></div>
    </div>
  </section>

  <div id="tapStart" class="tap-start hidden">
    <div class="tap-box">
      <div class="tap-emoji">🔊</div>
      <div>画面をタップして<br>音声をはじめる</div>
    </div>
  </div>
</div>
<script>
window.GAME_VER = <?= json_encode($GAME_VER) ?>;
window.START_GAME = <?= json_encode($startGame, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="common.js?v=<?= $GAME_VER ?>"></script>
</body>
</html>
