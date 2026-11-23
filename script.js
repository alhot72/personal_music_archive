document.addEventListener('DOMContentLoaded', function () {

    // === Космический фон: звёздное небо на canvas ===
    var canvas = document.getElementById('starfield');
    if (canvas) {
        var ctx = canvas.getContext('2d');
        var stars = [];
        var STAR_COUNT = 220;

        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }

        function initStars() {
            stars = [];
            for (var i = 0; i < STAR_COUNT; i++) {
                stars.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    radius: Math.random() * 1.4 + 0.3,
                    speed: Math.random() * 0.12 + 0.04,
                    twinkle: Math.random() * Math.PI * 2,
                    twinkleSpeed: Math.random() * 0.03 + 0.01,
                    hue: (Math.random() < 0.2) ? 1 : 0 // некоторые звёзды чуть цветные
                });
            }
        }

        function drawStars() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            for (var i = 0; i < stars.length; i++) {
                var s = stars[i];
                s.twinkle += s.twinkleSpeed;
                var alpha = 0.4 + Math.abs(Math.sin(s.twinkle)) * 0.6;
                var color = s.hue
                    ? (s.hue === 1 ? 'rgba(180, 200, 255, ' + alpha + ')' : 'rgba(200, 220, 255, ' + alpha + ')')
                    : 'rgba(200, 220, 255, ' + alpha + ')';

                ctx.beginPath();
                ctx.arc(s.x, s.y, s.radius, 0, Math.PI * 2);
                ctx.fillStyle = color;
                ctx.fill();

                s.y += s.speed;
                if (s.y > canvas.height) { s.y = 0; s.x = Math.random() * canvas.width; }
            }
            requestAnimationFrame(drawStars);
        }

        resizeCanvas();
        initStars();
        drawStars();

        window.addEventListener('resize', function () {
            resizeCanvas();
            initStars();
        });
    }

    // === Раскрытие каверов (страница Музыка) ===
    var toggles = document.querySelectorAll('.covers-toggle');
    toggles.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = btn.getAttribute('data-target');
            var list = document.getElementById(targetId);
            if (!list) return;
            list.classList.toggle('expanded');
            if (list.classList.contains('expanded')) {
                btn.innerHTML = btn.innerHTML.replace('\u25BC', '\u25B2');
            } else {
                btn.innerHTML = btn.innerHTML.replace('\u25B2', '\u25BC');
            }
        });
    });

    // Авто-раскрытие по якорю
    var hash = window.location.hash;
    if (hash.indexOf('#cover-') === 0 || hash.indexOf('#draft-') === 0 || hash.indexOf('#song-') === 0) {
        var targetEl = document.getElementById(hash.substring(1));
        if (targetEl) {
            var card = targetEl.closest('.draft-card');
            if (card) {
                var coversList = card.querySelector('.covers-list');
                if (coversList) {
                    coversList.classList.add('expanded');
                    var toggleBtn = card.querySelector('.covers-toggle');
                    if (toggleBtn) toggleBtn.innerHTML = toggleBtn.innerHTML.replace('\u25BC', '\u25B2');
                }
            }
            targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // === Звёздный рейтинг (1-10) ===
    var starGroups = document.querySelectorAll('.stars');
    starGroups.forEach(function (group) {
        var stars = group.querySelectorAll('span[data-value]');
        var hiddenInput = group.querySelector('input[type="hidden"]');
        var currentValue = 5;

        function paint(value) {
            stars.forEach(function (s, i) {
                if (i < value) s.classList.add('filled');
                else s.classList.remove('filled');
            });
        }

        paint(currentValue);

        stars.forEach(function (star, index) {
            star.addEventListener('mouseenter', function () { paint(index + 1); });
            star.addEventListener('click', function () {
                currentValue = index + 1;
                if (hiddenInput) hiddenInput.value = currentValue;
                paint(currentValue);
            });
        });

        group.addEventListener('mouseleave', function () { paint(currentValue); });
    });

    // === Утилита: формат времени ===
    function fmt(sec) {
        if (!isFinite(sec)) return '0:00';
        var m = Math.floor(sec / 60);
        var s = Math.floor(sec % 60);
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    // === Функция построения WinAmp-плеера ===
    function buildWinAmpPlayer(container, audio) {
        container.classList.add('winamp-player');
        container.innerHTML =
            '<button type="button" class="wa-play-btn" aria-label="Play">&#9654;</button>' +
            '<div class="wa-progress"><div class="wa-progress-fill"></div></div>' +
            '<span class="wa-time">0:00 / 0:00</span>' +
            '<div class="wa-volume">' +
                '<span class="wa-volume-icon">&#128266;</span>' +
                '<input type="range" min="0" max="1" step="0.01" value="1">' +
            '</div>';

        var btn = container.querySelector('.wa-play-btn');
        var progress = container.querySelector('.wa-progress');
        var fill = container.querySelector('.wa-progress-fill');
        var timeEl = container.querySelector('.wa-time');
        var volInput = container.querySelector('input[type="range"]');

        function pauseOthers(except) {
            document.querySelectorAll('.winamp-player').forEach(function (p) {
                if (p === except) return;
                var a = p._audio;
                if (a) { a.pause(); }
                var b = p.querySelector('.wa-play-btn');
                if (b) b.innerHTML = '&#9654;';
            });
        }

        btn.addEventListener('click', function () {
            pauseOthers(container);
            if (audio.paused) {
                audio.play().catch(function (e) { console.error(e); });
                btn.innerHTML = '&#9208;';
            } else {
                audio.pause();
                btn.innerHTML = '&#9654;';
            }
        });

        container._audio = audio;

        progress.addEventListener('click', function (e) {
            var rect = progress.getBoundingClientRect();
            var pct = (e.clientX - rect.left) / rect.width;
            if (isFinite(audio.duration)) audio.currentTime = pct * audio.duration;
        });

        volInput.addEventListener('input', function (e) {
            audio.volume = parseFloat(e.target.value);
        });

        audio.addEventListener('loadedmetadata', function () {
            timeEl.textContent = '0:00 / ' + fmt(audio.duration);
        });

        audio.addEventListener('timeupdate', function () {
            if (isFinite(audio.duration)) {
                var pct = (audio.currentTime / audio.duration) * 100;
                fill.style.width = pct + '%';
                timeEl.textContent = fmt(audio.currentTime) + ' / ' + fmt(audio.duration);
            }
        });

        audio.addEventListener('ended', function () {
            btn.innerHTML = '&#9654;';
            fill.style.width = '0%';
            audio.currentTime = 0;
            timeEl.textContent = '0:00 / ' + fmt(audio.duration);
        });
    }

    // === Переделка всех кастомных плееров в WinAmp-стиль ===
    document.querySelectorAll('.custom-player').forEach(function (container) {
        var src = container.getAttribute('data-src');
        var audio = new Audio();
        audio.preload = 'metadata';
        audio.src = src;
        container._audio = audio;
        buildWinAmpPlayer(container, audio);

        // Если трек уже играл через плейлист, показываем актуальную кнопку
        if (!audio.paused) {
            var b = container.querySelector('.wa-play-btn');
            if (b) b.innerHTML = '&#9208;';
        }
    });

    // === Кнопка "Показать ещё" для отзывов ===
    document.querySelectorAll('.reviews-more-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var container = btn.previousElementSibling;
            if (!container || !container.classList.contains('reviews-hidden')) return;
            var isShown = container.classList.contains('shown');
            var isEn = document.documentElement.lang === 'en';

            if (isShown) {
                container.classList.remove('shown');
                var count = container.querySelectorAll('li').length;
                btn.innerHTML = '\u25BC ' + (isEn ? 'Show more (' : '\u041F\u043E\u043A\u0430\u0437\u0430\u0442\u044C \u0435\u0449\u0435 (') + count + ')';
            } else {
                container.classList.add('shown');
                btn.innerHTML = '\u25B2 ' + (isEn ? 'Hide' : '\u0421\u043A\u0440\u044B\u0442\u044C');
            }
        });
    });

    // === Плейлист: один плеер + список треков (WinAmp) ===
    (function () {
        var playerEl = document.getElementById('mainPlayer');
        if (!playerEl) return;

        var tracks = window.PLAYLIST_TRACKS || [];
        if (tracks.length === 0) return;

        var audio = new Audio();
        audio.preload = 'metadata';

        var currentIndex = -1;
        var nowPlaying = document.getElementById('nowPlaying');

        buildWinAmpPlayer(playerEl, audio);

        var btn = playerEl.querySelector('.wa-play-btn');
        var fill = playerEl.querySelector('.wa-progress-fill');
        var timeEl = playerEl.querySelector('.wa-time');

        function loadTrack(index) {
            if (index < 0 || index >= tracks.length) return;
            currentIndex = index;
            audio.src = tracks[index].src;
            playerEl.setAttribute('data-src', tracks[index].src);
            if (nowPlaying) nowPlaying.textContent = tracks[index].title;

            document.querySelectorAll('.track-row').forEach(function (row) {
                row.classList.remove('playing');
            });
            var activeRow = document.querySelector('.track-row[data-index="' + index + '"]');
            if (activeRow) activeRow.classList.add('playing');
        }

        function playCurrent() {
            audio.play().catch(function (e) { console.error(e); });
            var b = playerEl.querySelector('.wa-play-btn');
            if (b) b.innerHTML = '&#9208;';
        }

        btn.addEventListener('click', function () {
            if (currentIndex < 0) { loadTrack(0); playCurrent(); return; }
            if (audio.paused) { playCurrent(); }
            else { audio.pause(); btn.innerHTML = '&#9654;'; }
        });

        audio.addEventListener('ended', function () {
            if (currentIndex + 1 < tracks.length) {
                loadTrack(currentIndex + 1);
                playCurrent();
            } else {
                btn.innerHTML = '&#9654;';
                var f = playerEl.querySelector('.wa-progress-fill');
                if (f) f.style.width = '0%';
                audio.currentTime = 0;
            }
        });

        document.querySelectorAll('.track-row').forEach(function (row) {
            row.addEventListener('click', function () {
                var idx = parseInt(row.getAttribute('data-index'), 10);
                loadTrack(idx);
                playCurrent();
            });
        });
    })();

    // === Админка: раскрытие плиток композиций ===
    document.querySelectorAll('.admin-tile-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var tile = btn.closest('.admin-tile');
            if (!tile) return;
            tile.classList.toggle('expanded');
            var isOpen = tile.classList.contains('expanded');
            var isEn = document.documentElement.lang === 'en';
            var openLabel = (isEn ? 'Collapse' : '\u0421\u0432\u0435\u0440\u043D\u0443\u0442\u044C');
            var closeLabel = (isEn ? 'Expand' : '\u0420\u0430\u0437\u0432\u0435\u0440\u043D\u0443\u0442\u044C');
            btn.textContent = isOpen ? openLabel : closeLabel;
        });
    });

});