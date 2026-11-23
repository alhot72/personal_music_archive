<?php
declare(strict_types=1);

if (!defined('APP_RUNNING')) {
    http_response_code(403);
    exit('Forbidden');
}

$lang = [
    'ru' => [
        'site_title'          => 'Космическая музыка',
        'hero_title'          => 'Космическая музыка',
        'hero_subtitle'       => 'моя музыка вдохновлённая такими группами как Space, Zodiac и Jean-Michel Jarre',

        'nav_home'            => 'Главная',
        'nav_music'           => 'Музыка',
        'nav_songs'           => 'Песни',
        'nav_playlist'        => 'Плеер',
        'nav_admin'           => 'Админка',

        'story_title'         => 'История проекта',
        'story_text'          => "В начале 90-х у меня был детский синтезатор, и я записывал первые мелодии на катушечный магнитофон. Это были черновые наброски без вокала — просто идеи, которые крутились в голове.\n\nСо временем катушки с музыкой перекочевали в кладовку на долгие годы. За это время лента почти размагнитилась, магнитофон полуразобранный покрылся пылью. Но некоторое время назад нашёл время, чтобы восстановить свои произведения.\n\nЯ оцифровал эти записи и начал восстанавливать их с помощью современных инструментов и делать аранжировку с помощью ИИ.\n\nЭтот архив — не просто музыка, а хронология поиска звучания. Каждый трек и каждый кавер — отдельный шаг на этом пути.",
        'story_photo_alt'     => 'Катушечный магнитофон и катушки',

        'drafts_title'        => 'Черновики (orig)',
        'covers_title'        => 'Каверы и аранжировки',
        'song_list_title'     => 'Песни с вокалом',
        'playlist_title'      => 'Музыкальный плеер',
        'playlist_covers'     => 'Каверы и аранжировки',
        'playlist_songs'      => 'Песни с вокалом',
        'no_items'            => 'Нет файлов',
        'no_covers'           => 'Каверов пока нет',
        'review_name'         => 'Ваше имя',
        'review_comment'      => 'Комментарий',
        'review_submit'       => 'Отправить отзыв',
        'review_thanks'       => 'Спасибо за отзыв!',
        'review_error_name'   => 'Пожалуйста, укажите имя',
        'admin_login'         => 'Вход в админ-панель',
        'admin_wrong_pass'    => 'Неверный пароль',
        'admin_logout'        => 'Выйти',
        'reviews_list'        => 'Отзывы',
        'rating_stars'        => 'Оценка',
        'no_reviews'          => 'Пока нет отзывов',
        'file_name_label'     => 'Файл:',
        'toggle_covers'       => '▼ КАВЕРЫ (%d)',
        'toggle_covers_up'    => '▲ КАВЕРЫ',
        'show_more_reviews'   => '▼ Показать ещё (%d)',
        'hide_reviews'        => '▲ Скрыть',
        'stats_title'         => 'Статистика посещений',
        'stats_total'         => 'Всего визитов',
        'stats_by_page'       => 'По страницам',
        'stats_recent'        => 'Последние визиты',
        'stats_time'          => 'Время',
        'stats_page'          => 'Страница',
        'stats_ip'            => 'IP',
        'stats_no_visits'     => 'Визитов пока нет',

        'admin_compositions'  => 'Композиции',
        'admin_expand'        => 'Развернуть',
        'admin_collapse'      => 'Свернуть',
        'admin_listens'       => 'Прослушиваний',
        'admin_country'       => 'Страна',
        'admin_listeners'     => 'Слушатели',
        'admin_no_listeners'  => 'Пока нет оценок и комментариев',
        'listener_country'    => 'Страна',
        'listener_date'       => 'Дата',
        'listener_rating'     => 'Оценка',
        'admin_unknown'       => 'неизвестно',
    ],
    'en' => [
        'site_title'          => 'Cosmic Music',
        'hero_title'          => 'Cosmic Music',
        'hero_subtitle'       => 'my music inspired by bands like Space, Zodiac and Jean-Michel Jarre',

        'nav_home'            => 'Home',
        'nav_music'           => 'Music',
        'nav_songs'           => 'Songs',
        'nav_playlist'        => 'Player',
        'nav_admin'           => 'Admin',

        'story_title'         => 'Project Story',
        'story_text'          => "Back in the early 90s, I had a toy keyboard and recorded my first melodies on a reel-to-reel tape recorder. These were rough sketches without vocals — just ideas spinning in my head.\n\nOver the years, the reels with the music ended up in a storage room and stayed there for a long time. During that time, the tape almost lost its magnetism, and the half-disassembled recorder got covered in dust. But recently, I found the time to restore my pieces.\n\nI digitized those recordings and started restoring them using modern tools, and then arranging them with the help of AI.\n\nThis archive isn't just music — it's a chronology of my search for the right sound. Each track and each cover represents a separate step on that journey.",
        'story_photo_alt'     => 'Reel-to-reel tape recorder and reels',

        'drafts_title'        => 'Drafts (orig)',
        'covers_title'        => 'Covers & Arrangements',
        'song_list_title'     => 'Songs with Vocals',
        'playlist_title'      => 'Music Player',
        'playlist_covers'     => 'Covers & Arrangements',
        'playlist_songs'      => 'Songs with Vocals',
        'no_items'            => 'No files',
        'no_covers'           => 'No covers yet',
        'review_name'         => 'Your name',
        'review_comment'      => 'Comment',
        'review_submit'       => 'Submit review',
        'review_thanks'       => 'Thanks for your review!',
        'review_error_name'   => 'Please enter your name',
        'admin_login'         => 'Admin Login',
        'admin_wrong_pass'    => 'Wrong password',
        'admin_logout'        => 'Logout',
        'reviews_list'        => 'Reviews',
        'rating_stars'        => 'Rating',
        'no_reviews'          => 'No reviews yet',
        'file_name_label'     => 'File:',
        'toggle_covers'       => '▼ COVERS (%d)',
        'toggle_covers_up'    => '▲ COVERS',
        'show_more_reviews'   => '▼ Show more (%d)',
        'hide_reviews'        => '▲ Hide',
        'stats_title'         => 'Visit Statistics',
        'stats_total'         => 'Total visits',
        'stats_by_page'       => 'By page',
        'stats_recent'        => 'Recent visits',
        'stats_time'          => 'Time',
        'stats_page'          => 'Page',
        'stats_ip'            => 'IP',
        'stats_no_visits'     => 'No visits yet',

        'admin_compositions'  => 'Compositions',
        'admin_expand'        => 'Expand',
        'admin_collapse'      => 'Collapse',
        'admin_listens'       => 'Listens',
        'admin_country'       => 'Country',
        'admin_listeners'     => 'Listeners',
        'admin_no_listeners'  => 'No ratings or comments yet',
        'listener_country'    => 'Country',
        'listener_date'       => 'Date',
        'listener_rating'     => 'Rating',
        'admin_unknown'       => 'unknown',
    ],
];

if (isset($_GET['lang']) && in_array($_GET['lang'], ['ru', 'en'], true)) {
    $_SESSION['lang'] = $_GET['lang'];
}
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'ru';
}

function t(string $key): string
{
    global $lang;
    $current = $_SESSION['lang'] ?? 'ru';
    return $lang[$current][$key] ?? $key;
}

function currentLang(): string { return $_SESSION['lang'] ?? 'ru'; }
function otherLang(): string { return currentLang() === 'ru' ? 'en' : 'ru'; }