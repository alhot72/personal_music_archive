<?php
declare(strict_types=1);
define('APP_RUNNING', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/helpers.php';

trackVisit('playlist');

$covers = scanAllCovers();
$songs  = scanSongs();

// Собираем общий список треков для JS
$allTracks = [];
foreach ($covers as $c) {
    $allTracks[] = ['src' => $c['path'], 'title' => $c['title'], 'type' => 'cover'];
}
foreach ($songs as $s) {
    $allTracks[] = ['src' => $s['path'], 'title' => $s['title'], 'type' => 'song'];
}

renderHeader('playlist');
?>

<main class="container">
  <h2><?php echo e(t('playlist_title')); ?></h2>

  <div class="playlist-player-wrap">
    <div id="mainPlayer" class="custom-player" data-src=""></div>
    <div class="now-playing" id="nowPlaying"></div>
  </div>

  <?php if (!empty($covers)): ?>
    <h3 class="playlist-section-title"><?php echo e(t('playlist_covers')); ?> (<?php echo count($covers); ?>)</h3>
    <div class="playlist-tracks" id="coversList">
      <?php foreach ($covers as $i => $c): ?>
        <div class="track-row"
             data-src="<?php echo e($c['path']); ?>"
             data-title="<?php echo e($c['title']); ?>"
             data-index="<?php echo $i; ?>">
          <span class="track-num"><?php echo $i + 1; ?></span>
          <span class="track-title"><?php echo e($c['title']); ?></span>
          <span class="track-dur"></span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($songs)): ?>
    <h3 class="playlist-section-title"><?php echo e(t('playlist_songs')); ?> (<?php echo count($songs); ?>)</h3>
    <div class="playlist-tracks" id="songsList">
      <?php foreach ($songs as $i => $s): ?>
        <div class="track-row"
             data-src="<?php echo e($s['path']); ?>"
             data-title="<?php echo e($s['title']); ?>"
             data-index="<?php echo count($covers) + $i; ?>">
          <span class="track-num"><?php echo count($covers) + $i + 1; ?></span>
          <span class="track-title"><?php echo e($s['title']); ?></span>
          <span class="track-dur"></span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<script>
  // Список всех треков для автопроигрывания
  window.PLAYLIST_TRACKS = <?php echo json_encode($allTracks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>

<?php renderFooter(); ?>