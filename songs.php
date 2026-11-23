<?php
declare(strict_types=1);
define('APP_RUNNING', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/helpers.php';
trackVisit('songs');

// Обработка отзыва для песни
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_type']) && $_POST['review_type'] === 'song') {
    $filename = trim($_POST['review_filename'] ?? '');
    $name     = trim($_POST['review_name'] ?? '');
    $comment  = trim($_POST['review_comment'] ?? '');
    $rating   = max(1, min(10, (int)($_POST['review_rating'] ?? 5)));

    if ($name !== '' && $filename !== '') {
        saveReview('song', $filename, $name, $comment, $rating);
        $_SESSION['flash_message'] = t('review_thanks');
    } else {
        $_SESSION['flash_message'] = t('review_error_name');
    }
    header('Location: songs.php#song-' . rawurlencode($filename));
    exit;
}

$songs = scanSongs();
$flashMessage = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);

renderHeader('songs');
?>

<main class="container">
  <?php if ($flashMessage !== ''): ?>
    <div class="flash-message"><?php echo e($flashMessage); ?></div>
  <?php endif; ?>

  <h2><?php echo e(t('song_list_title')); ?> (<?php echo count($songs); ?>)</h2>

  <?php if (empty($songs)): ?>
    <p><?php echo e(t('no_items')); ?></p>
  <?php else: ?>
    <?php foreach ($songs as $song): ?>
      <article id="song-<?php echo e($song['filename']); ?>" class="draft-card">
        <h3><?php echo e($song['title']); ?></h3>
        <p class="file-name"><strong><?php echo e(t('file_name_label')); ?></strong> <?php echo e($song['filename']); ?></p>

        <!-- Кастомный плеер -->
        <div class="custom-player" data-src="<?php echo e($song['path']); ?>"></div>

        <!-- Форма отзыва -->
        <form class="review-form" method="post">
          <input type="hidden" name="review_type" value="song">
          <input type="hidden" name="review_filename" value="<?php echo e($song['filename']); ?>">
          <label>
            <?php echo e(t('review_name')); ?>
            <input type="text" name="review_name" required>
          </label>
          <label>
            <?php echo e(t('review_comment')); ?>
            <textarea name="review_comment" rows="3"></textarea>
          </label>
          <div class="star-rating">
            <span class="rating-label"><?php echo e(t('rating_stars')); ?></span>
            <div class="stars" data-name="review_rating">
              <?php for ($i = 1; $i <= 10; $i++): ?>
                <span data-value="<?php echo $i; ?>" title="<?php echo $i; ?>"></span>
              <?php endfor; ?>
              <input type="hidden" name="review_rating" value="5">
            </div>
          </div>
          <button type="submit"><?php echo e(t('review_submit')); ?></button>
        </form>

        <!-- Отзывы: первые 3 сразу, остальные по кнопке -->
        <?php
        $songReviews = getReviewsForItem('song', $song['filename']);
        $reviewLimit = 3;
        $shownReviews = array_slice($songReviews, 0, $reviewLimit);
        $hiddenReviews = array_slice($songReviews, $reviewLimit);
        ?>

        <?php if (!empty($songReviews)): ?>
          <div class="reviews-block">
            <ul class="existing-reviews">
              <?php foreach ($shownReviews as $r): ?>
                <li>
                  <strong><?php echo e($r['name']); ?></strong>
                  <span class="review-rating"><?php echo $r['rating']; ?>/10</span>
                  <?php if (!empty($r['comment'])): ?>
                    <p><?php echo nl2br(e($r['comment'])); ?></p>
                  <?php endif; ?>
                  <small><?php echo e($r['date']); ?></small>
                </li>
              <?php endforeach; ?>
            </ul>

            <?php if (!empty($hiddenReviews)): ?>
              <ul class="existing-reviews reviews-hidden">
                <?php foreach ($hiddenReviews as $r): ?>
                  <li>
                    <strong><?php echo e($r['name']); ?></strong>
                    <span class="review-rating"><?php echo $r['rating']; ?>/10</span>
                    <?php if (!empty($r['comment'])): ?>
                      <p><?php echo nl2br(e($r['comment'])); ?></p>
                    <?php endif; ?>
                    <small><?php echo e($r['date']); ?></small>
                  </li>
                <?php endforeach; ?>
              </ul>
              <button type="button" class="reviews-more-btn" data-target="<?php echo e($song['filename']); ?>">
                <?php echo sprintf(t('show_more_reviews'), count($hiddenReviews)); ?>
              </button>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <p class="no-reviews"><?php echo e(t('no_reviews')); ?></p>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

<?php renderFooter(); ?>