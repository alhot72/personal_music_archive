<?php
declare(strict_types=1);
define('APP_RUNNING', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/helpers.php';

trackVisit('admin');

// Выход
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Вход
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_password'])) {
    if (hash_equals(ADMIN_PASSWORD, (string)$_POST['admin_password'])) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    }
    $loginError = t('admin_wrong_pass');
}

$loggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$stats = $loggedIn ? getStats() : [];
$recentVisits = $loggedIn ? array_slice(array_reverse($stats['recent'] ?? []), 0, 30) : [];

// Собираем единый список композиций: черновики + их каверы + песни
function buildCompositionList(): array {
    $items = [];

    foreach (scanDraftsWithCovers() as $draft) {
        $items[] = [
            'kind'     => 'orig',
            'filename' => $draft['filename'],
            'title'    => $draft['title'],
        ];
        foreach ($draft['covers'] as $cover) {
            $items[] = [
                'kind'     => 'cover',
                'filename' => $cover['filename'],
                'title'    => $cover['title'],
            ];
        }
    }

    foreach (scanSongs() as $song) {
        $items[] = [
            'kind'     => 'song',
            'filename' => $song['filename'],
            'title'    => $song['title'],
        ];
    }

    // Показываем только те композиции, которые уже слушали/оценивали
    $items = array_filter($items, function ($item) {
        $type = ($item['kind'] === 'song') ? 'song' : 'cover';
        return count(getReviewsForItem($type, $item['filename'])) > 0;
    });

    return array_values($items);
}

$compositions = $loggedIn ? buildCompositionList() : [];

renderHeader('admin');
?>

<main class="container">
  <?php if (!$loggedIn): ?>
    <section class="admin-login">
      <h2><?php echo e(t('admin_login')); ?></h2>
      <?php if ($loginError !== ''): ?>
        <p class="error"><?php echo e($loginError); ?></p>
      <?php endif; ?>
      <form method="post">
        <label>
          <input type="password" name="admin_password" placeholder="Password" required>
        </label>
        <button type="submit">OK</button>
      </form>
    </section>

  <?php else: ?>
    <section class="admin-panel">
      <div class="admin-top">
        <h2><?php echo e(t('admin_compositions')); ?> (<?php echo count($compositions); ?>)</h2>
        <a href="admin.php?logout" class="logout-btn"><?php echo e(t('admin_logout')); ?></a>
      </div>

      <!-- Плитка композиций -->
      <?php if (empty($compositions)): ?>
        <p class="no-reviews"><?php echo e(t('no_items')); ?></p>
      <?php else: ?>
        <div class="admin-list">
          <?php foreach ($compositions as $comp): ?>
            <?php
            $reviewType = ($comp['kind'] === 'song') ? 'song' : 'cover';
            $reviews = getReviewsForItem($reviewType, $comp['filename']);
            $count = count($reviews);
            $sum = 0;
            foreach ($reviews as $r) { $sum += (int)$r['rating']; }
            $avg = $count > 0 ? round($sum / $count, 1) : 0;
            ?>
            <div class="admin-tile" id="tile-<?php echo e(preg_replace('/[^a-zA-Z0-9_-]/', '_', $comp['filename'])); ?>">
              <div class="admin-tile-header">
                <div>
                  <h3 class="admin-tile-title"><?php echo e($comp['title']); ?></h3>
                  <div class="admin-tile-sub"><?php echo e($comp['filename']); ?></div>
                </div>
                <button type="button" class="admin-tile-btn"><?php echo e(t('admin_expand')); ?></button>
              </div>

              <div class="admin-tile-meta">
                <div class="admin-meta-item">
                  <div class="meta-label"><?php echo e(t('reviews_list')); ?></div>
                  <div class="meta-value"><?php echo $count; ?></div>
                </div>
                <div class="admin-meta-item">
                  <div class="meta-label"><?php echo e(t('rating_stars')); ?></div>
                  <div class="meta-value" style="font-size:1rem;"><?php echo $avg; ?>/10</div>
                </div>
              </div>

              <div class="admin-tile-body">
                <h4 class="admin-listener-heading"><?php echo e(t('admin_listeners')); ?> (<?php echo $count; ?>)</h4>

                <?php if ($count === 0): ?>
                  <p class="admin-no-listeners"><?php echo e(t('admin_no_listeners')); ?></p>
                <?php else: ?>
                  <?php foreach ($reviews as $r): ?>
                    <div class="admin-listener">
                      <div class="admin-listener-head">
                        <span class="admin-listener-name"><?php echo e($r['name']); ?></span>
                        <span class="review-rating"><?php echo (int)$r['rating']; ?>/10</span>
                      </div>
                      <?php if (!empty($r['comment'])): ?>
                        <p class="admin-listener-comment"><?php echo nl2br(e($r['comment'])); ?></p>
                      <?php endif; ?>
                      <div class="admin-listener-meta">
                        <?php echo e(t('listener_date')); ?>: <?php echo e($r['date']); ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <hr class="admin-divider">

      <!-- Статистика посещений -->
      <div class="stats-box">
        <h3><?php echo e(t('stats_title')); ?></h3>
        <div class="stats-total"><?php echo e(t('stats_total')); ?>: <strong><?php echo (int)($stats['total'] ?? 0); ?></strong></div>

        <?php if (!empty($stats['by_page'])): ?>
          <div class="stats-by-page">
            <h4><?php echo e(t('stats_by_page')); ?>:</h4>
            <ul>
              <?php foreach ($stats['by_page'] as $pageName => $count): ?>
                <li><?php echo e($pageName); ?> — <?php echo (int)$count; ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if (!empty($recentVisits)): ?>
          <div class="stats-recent">
            <h4><?php echo e(t('stats_recent')); ?> (<?php echo count($recentVisits); ?>)</h4>
            <table class="stats-table">
              <thead>
                <tr>
                  <th><?php echo e(t('stats_time')); ?></th>
                  <th><?php echo e(t('stats_page')); ?></th>
                  <th><?php echo e(t('stats_ip')); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentVisits as $v): ?>
                  <tr>
                    <td><?php echo e($v['time'] ?? ''); ?></td>
                    <td><?php echo e($v['page'] ?? ''); ?></td>
                    <td><?php echo e($v['ip'] ?? ''); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="no-reviews"><?php echo e(t('stats_no_visits')); ?></p>
        <?php endif; ?>
      </div>
    </section>
  <?php endif; ?>
</main>

<?php renderFooter(); ?>