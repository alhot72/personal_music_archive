<?php
declare(strict_types=1);

if (!defined('APP_RUNNING')) {
    http_response_code(403);
    exit('Forbidden');
}

error_reporting(E_ALL);
ini_set('display_errors', '0');

define('BASE_DIR', __DIR__);
define('MUSIC_ORIG_DIR', BASE_DIR . '/music/orig');
define('MUSIC_COVERS_DIR', BASE_DIR . '/music/covers');
define('MUSIC_SONGS_DIR', BASE_DIR . '/music/songs');
define('REVIEWS_FILE', BASE_DIR . '/reviews.json');

// Пароль администратора берётся из переменной окружения ADMIN_PASSWORD.
// Задайте его на сервере (например, в .htaccess: SetEnv ADMIN_PASSWORD "your-pass")
// или в Настройках хостинга. Если не задан — вход в админку будет закрыт.
define('ADMIN_PASSWORD', (string)getenv('ADMIN_PASSWORD'));

// Версия статических ресурсов (стили/скрипты) для сброса кеша браузеров.
// Меняйте значение, когда меняете style.css / script.js.
define('ASSET_VERSION', '2026-11-22-2');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}