<?php
$VERSION = '20260607_04';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>いろぬりゲーム</title>
  <link rel="stylesheet" href="common.css?v=<?php echo $VERSION; ?>">
</head>
<body>
  <main class="app">
    <section id="menuScreen" class="screen menu-screen">
      <div class="top-title">
        <div class="badge">🎨</div>
        <div>
          <h1>いろぬり</h1>
          <p>すきなキャラクターをえらんでね</p>
        </div>
      </div>

      <div class="character-grid" aria-label="キャラクターを選択">
        <button class="character-card" data-character="niko">
          <span class="mini-face niko-mini">♡</span>
          <strong>にこちゃん</strong>
        </button>
        <button class="character-card" data-character="pota">
          <span class="mini-face pota-mini">💧</span>
          <strong>ぽたちゃん</strong>
        </button>
        <button class="character-card" data-character="pika">
          <span class="mini-face pika-mini">★</span>
          <strong>ぴかちゃん</strong>
        </button>
        <button class="character-card" data-character="pan">
          <span class="mini-face pan-mini">P</span>
          <strong>ぱんくん</strong>
        </button>
        <button class="character-card" data-character="ribo">
          <span class="mini-face ribo-mini">🎀</span>
          <strong>りぼちゃん</strong>
        </button>
      </div>
    </section>

    <section id="gameScreen" class="screen game-screen hidden">
      <div class="game-header">
        <button id="backBtn" class="small-btn" type="button">← メニュー</button>
        <div class="talk" id="talkText">すきないろでぬってね</div>
      </div>

      <div class="play-area">
        <div class="paint-box" id="paintBox">
          <svg id="characterSvg" class="character-svg" viewBox="0 0 340 360" role="img" aria-label="色塗りキャラクター"></svg>
        </div>

        <div class="palette" aria-label="色を選択">
          <button class="color-chip active" style="--c:#ff4b8b" data-color="#ff4b8b" aria-label="ピンク"></button>
          <button class="color-chip" style="--c:#ff3b30" data-color="#ff3b30" aria-label="あか"></button>
          <button class="color-chip" style="--c:#ffcc00" data-color="#ffcc00" aria-label="きいろ"></button>
          <button class="color-chip" style="--c:#34c759" data-color="#34c759" aria-label="みどり"></button>
          <button class="color-chip" style="--c:#007aff" data-color="#007aff" aria-label="あお"></button>
          <button class="color-chip" style="--c:#af52de" data-color="#af52de" aria-label="むらさき"></button>
          <button class="color-chip" style="--c:#ff9500" data-color="#ff9500" aria-label="オレンジ"></button>
          <button class="color-chip" style="--c:#8e5a2b" data-color="#8e5a2b" aria-label="ちゃいろ"></button>
          <button class="color-chip" style="--c:#111111" data-color="#111111" aria-label="くろ"></button>
          <button class="color-chip white" style="--c:#ffffff" data-color="#ffffff" aria-label="しろ"></button>
        </div>
      </div>
    </section>
  </main>

  <script src="common.js?v=<?php echo $VERSION; ?>"></script>
</body>
</html>
