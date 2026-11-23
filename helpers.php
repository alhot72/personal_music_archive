<?php
declare(strict_types=1);

if (!defined('APP_RUNNING')) {
    http_response_code(403);
    exit('Forbidden');
}

/**
 * Сканирует orig и связывает каверы из covers по ID.
 */
function scanDraftsWithCovers(): array
{
    $drafts = [];
    if (!is_dir(MUSIC_ORIG_DIR)) { return $drafts; }

    $origFiles = scandir(MUSIC_ORIG_DIR);
    if ($origFiles === false) { return $drafts; }

    foreach ($origFiles as $filename) {
        if (!preg_match('/^My_music-(\d+)\.mp3$/i', $filename, $m)) { continue; }
        $rawId = $m[1];

        $covers = [];
        if (is_dir(MUSIC_COVERS_DIR)) {
            $coverFiles = scandir(MUSIC_COVERS_DIR);
            if ($coverFiles !== false) {
                foreach ($coverFiles as $coverFile) {
                    if (!preg_match('/^My_music-' . preg_quote($rawId, '/') . '-.+\.mp3$/i', $coverFile)) {
                        continue;
                    }
                    $baseName = pathinfo($coverFile, PATHINFO_FILENAME);
                    $niceTitle = preg_replace('/^My_music-' . preg_quote($rawId, '/') . '-?/', '', $baseName);
                    $niceTitle = str_replace('_', ' ', $niceTitle);
                    $niceTitle = trim($niceTitle) ?: 'Cover';

                    $covers[] = [
                        'filename' => $coverFile,
                        'path'     => 'music/covers/' . rawurlencode($coverFile),
                        'title'    => $niceTitle,
                    ];
                }
            }
        }
        usort($covers, fn($a, $b) => strcmp($a['filename'], $b['filename']));

        $drafts[] = [
            'id'       => $rawId,
            'filename' => $filename,
            'path'     => 'music/orig/' . rawurlencode($filename),
            'title'    => t('drafts_title') . ' #' . $rawId,
            'covers'   => $covers,
        ];
    }
    usort($drafts, fn($a, $b) => (int)$a['id'] - (int)$b['id']);
    return $drafts;
}

/**
 * Сканирует папку songs.
 */
function scanSongs(): array
{
    $songs = [];
    if (!is_dir(MUSIC_SONGS_DIR)) { return $songs; }

    $files = scandir(MUSIC_SONGS_DIR);
    if ($files === false) { return $songs; }

    foreach ($files as $filename) {
        if (!preg_match('/\.mp3$/i', $filename)) { continue; }
        if ($filename === '.' || $filename === '..') { continue; }

        $niceTitle = preg_replace('/^My_music-(\d+)-?/', 'Трек #$1: ', $filename);
        $niceTitle = str_replace('_', ' ', $niceTitle);
        $niceTitle = pathinfo($niceTitle, PATHINFO_FILENAME);

        $songs[] = [
            'filename' => $filename,
            'path'     => 'music/songs/' . rawurlencode($filename),
            'title'    => $niceTitle ?: $filename,
        ];
    }
    usort($songs, fn($a, $b) => strcmp($a['filename'], $b['filename']));
    return $songs;
}

/**
 * Сканирует ВСЕ каверы (без привязки к orig).
 */
function scanAllCovers(): array
{
    $covers = [];
    if (!is_dir(MUSIC_COVERS_DIR)) { return $covers; }

    $files = scandir(MUSIC_COVERS_DIR);
    if ($files === false) { return $covers; }

    foreach ($files as $filename) {
        if (!preg_match('/\.mp3$/i', $filename)) { continue; }
        if ($filename === '.' || $filename === '..') { continue; }

        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        $niceTitle = preg_replace('/^My_music-\d+-?/', '', $baseName);
        $niceTitle = str_replace('_', ' ', $niceTitle);
        $niceTitle = trim($niceTitle) ?: $baseName;

        $covers[] = [
            'filename' => $filename,
            'path'     => 'music/covers/' . rawurlencode($filename),
            'title'    => $niceTitle,
        ];
    }
    usort($covers, fn($a, $b) => strcmp($a['filename'], $b['filename']));
    return $covers;
}

/**
 * Отзывы.
 */
function getAllReviews(): array
{
    if (!file_exists(REVIEWS_FILE)) { return []; }
    $json = file_get_contents(REVIEWS_FILE);
    if ($json === false) { return []; }
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function saveReview(string $type, string $filename, string $name, string $comment, int $rating): bool
{
    $key = $type . '::' . $filename;
    $all = getAllReviews();
    if (!isset($all[$key])) { $all[$key] = []; }

    $all[$key][] = [
        'name'    => $name,
        'comment' => $comment,
        'rating'  => $rating,
        'date'    => date('Y-m-d H:i:s'),
    ];

    return file_put_contents(
        REVIEWS_FILE,
        json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    ) !== false;
}

function getReviewsForItem(string $type, string $filename): array
{
    $key = $type . '::' . $filename;
    $all = getAllReviews();
    return isset($all[$key]) ? $all[$key] : [];
}

/**
 * Статистика посещений.
 */
function trackVisit(string $page): void
{
    $statsFile = BASE_DIR . '/stats.json';
    $stats = [];
    if (file_exists($statsFile)) {
        $json = file_get_contents($statsFile);
        $stats = json_decode($json, true) ?: [];
    }

    if (!isset($stats['total'])) $stats['total'] = 0;
    if (!isset($stats['by_page'])) $stats['by_page'] = [];
    if (!isset($stats['recent'])) $stats['recent'] = [];

    $stats['total']++;
    if (!isset($stats['by_page'][$page])) $stats['by_page'][$page] = 0;
    $stats['by_page'][$page]++;

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    // Анонимизируем IP — последние октеты заменяем
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $parts = explode('.', $ip);
        $parts[2] = 'x';
        $parts[3] = 'x';
        $ip = implode('.', $parts);
    }

    $stats['recent'][] = [
        'time' => date('Y-m-d H:i:s'),
        'ip'   => $ip,
        'page' => $page,
        'ua'   => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 150),
    ];

    // Храним только последние 200 визитов
    $stats['recent'] = array_slice($stats['recent'], -200);

    file_put_contents(
        $statsFile,
        json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

function getStats(): array
{
    $statsFile = BASE_DIR . '/stats.json';
    if (!file_exists($statsFile)) {
        return ['total' => 0, 'by_page' => [], 'recent' => []];
    }
    $json = file_get_contents($statsFile);
    $stats = json_decode($json, true);
    return is_array($stats) ? $stats : ['total' => 0, 'by_page' => [], 'recent' => []];
}

function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Шапка с баннером галактики и 5 кнопками навигации.
 */
function renderHeader(string $page): void
{
    $nav = [
        ['index.php',    'nav_home',     'index'],
        ['music.php',    'nav_music',    'music'],
        ['songs.php',    'nav_songs',    'songs'],
        ['playlist.php', 'nav_playlist', 'playlist'],
        ['admin.php',    'nav_admin',    'admin'],
    ];

    $h = '';

    $h .= '<!DOCTYPE html>' . "\n";
    $h .= '<html lang="' . e(currentLang()) . '">' . "\n";
    $h .= '<head>' . "\n";
    $h .= '  <meta charset="utf-8">' . "\n";
    $h .= '  <meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
    $h .= '  <title>' . e(t('site_title')) . '</title>' . "\n";
    $h .= '  <link rel="stylesheet" href="style.css?v=' . ASSET_VERSION . '">' . "\n";
    $h .= '</head>' . "\n";
    $h .= '<body>' . "\n";
    $h .= '  <canvas id="starfield"></canvas>' . "\n";
    $h .= '  <div class="page-wrapper">' . "\n";
    $h .= '  <header class="site-header">' . "\n";
    $h .= '    <div class="hero" style="background-image:url(\'cosmic-banner.jpg\');">' . "\n";
    $h .= '      <div class="hero-veil"></div>' . "\n";
    $h .= '      <div class="hero-content">' . "\n";
    $h .= '        <h1 class="hero-title">' . e(t('hero_title')) . '</h1>' . "\n";
    $h .= '        <p class="hero-subtitle">' . e(t('hero_subtitle')) . '</p>' . "\n";
    $h .= '      </div>' . "\n";
    $h .= '    </div>' . "\n";
    $h .= '    <nav class="main-nav">' . "\n";
    foreach ($nav as $item) {
        $active = ($item[2] === $page) ? ' active' : '';
        $h .= '      <a class="nav-btn' . $active . '" href="' . e($item[0]) . '">' . e(t($item[1])) . '</a>' . "\n";
    }
    $h .= '    </nav>' . "\n";
    $h .= '    <div class="lang-switch">' . "\n";
    $h .= '      <a href="?lang=' . e(otherLang()) . '">' . strtoupper(e(otherLang())) . '</a>' . "\n";
    $h .= '    </div>' . "\n";
    $h .= '  </header>' . "\n";

    echo $h;
}

function renderFooter(): void
{
    echo '</div>' . "\n";
    echo '<script src="script.js?v=' . ASSET_VERSION . '"></script>' . "\n";
    echo '</body>' . "\n";
    echo '</html>' . "\n";
}