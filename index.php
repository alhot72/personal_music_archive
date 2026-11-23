<?php
declare(strict_types=1);
define('APP_RUNNING', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/helpers.php';
trackVisit('index');

renderHeader('index');
?>

<main class="container">
  <section class="story-box">
    <h2><?php echo e(t('story_title')); ?></h2>
    <p><?php echo nl2br(e(t('story_text'))); ?></p>

    <!-- Место под фото. Пока заглушка-картинка, замените файл story-photo.jpg -->
    <div class="story-photo">
      <img src="story-photo.jpg" alt="<?php echo e(t('story_photo_alt')); ?>" loading="lazy">
    </div>
  </section>
</main>

<?php renderFooter(); ?>