<?php
$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="675" viewBox="0 0 1200 675"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#0ea5e9" stop-opacity=".18"/><stop offset="1" stop-color="#64748b" stop-opacity=".18"/></linearGradient></defs><rect fill="url(#g)" width="1200" height="675"/><g fill="none" stroke="#94a3b8" stroke-width="16" opacity=".85"><rect x="420" y="245" width="360" height="240" rx="24"/><path d="M520 325h40l20-20h40l20 20h40"/><circle cx="600" cy="365" r="36"/></g></svg>';
$placeholder = 'data:image/svg+xml;base64,' . base64_encode($svg);

$thumbSrc = function (array $a) use ($baseUrl, $placeholder): string {
    $rel = (string)($a['thumb'] ?? '');
    if ($rel !== '') {
        $fsPath = __DIR__ . '/../../../public/' . $rel;
        if (is_file($fsPath)) {
            return $baseUrl . '/' . $rel;
        }
    }
    return $placeholder;
};
?>

<div class="py-3 home-page">
    <div class="card mb-3 home-toolbar">
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <form method="get" action="<?= htmlspecialchars($baseUrl) ?>/search" class="flex-grow-1" style="min-width:260px">
                <div class="input-group">
                    <input type="text" class="form-control" name="q" placeholder="Tìm kiếm bài viết..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button class="btn btn-primary" type="submit">Tìm</button>
                </div>
            </form>
            <form method="get" class="d-flex align-items-center ms-auto" action="">
                <label class="me-2 text-muted small">Danh mục</label>
                <select name="cat" class="form-select" onchange="this.form.submit()">
                    <option value="0">Tất cả</option>
                    <?php foreach (($categories ?? []) as $c): ?>
                        <option value="<?= (int)$c['category_id'] ?>" <?= (($selectedCat ?? 0) == $c['category_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <?php if (!empty($featured)): ?>
    <?php $heroSrc = $thumbSrc($featured); ?>
    <section class="home-hero card border-0 text-white mb-4" style="background-image: linear-gradient(120deg, rgba(15,23,42,.75), rgba(34,211,238,.18)), url('<?= htmlspecialchars($heroSrc) ?>');">
        <div class="hero-inner">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="badge badge-cat"><?= htmlspecialchars($featured['category_name'] ?? 'N/A') ?></span>
                <span class="meta-chip"><?= htmlspecialchars($featured['created_at'] ?? '') ?></span>
                <span class="meta-chip">Nổi bật</span>
            </div>
            <h1 class="display-6 hero-title mb-3"><?= htmlspecialchars($featured['title'] ?? '') ?></h1>
            <?php if (!empty($featured['summary'])): ?>
                <p class="lead hero-lead mb-4"><?= nl2br(htmlspecialchars($featured['summary'])) ?></p>
            <?php endif; ?>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <a class="btn btn-light" href="<?= htmlspecialchars($baseUrl) ?>/article/<?= (int)$featured['article_id'] ?>">Đọc ngay</a>
                <a class="btn btn-outline-light" href="<?= htmlspecialchars($baseUrl) ?>/category/<?= (int)($featured['category_id'] ?? 0) ?>">Xem danh mục</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <div class="row g-4 align-items-start">
        <div class="col-lg-8">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                <h2 class="h4 hero-title mb-0">Bài mới</h2>
                <div class="text-muted small">Trang <?= (int)($page ?? 1) ?> / <?= (int)($pages ?? 1) ?></div>
            </div>

            <?php if (empty($articles)): ?>
                <div class="alert alert-info">Chưa có bài viết.</div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($articles as $a): ?>
                    <div class="col-sm-6 col-lg-6">
                        <div class="card h-100 article-card">
                            <?php $src = $thumbSrc($a); ?>
                            <img class="article-thumb" src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($a['title']) ?>">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge badge-cat"><?= htmlspecialchars($a['category_name'] ?? 'N/A') ?></span>
                                    <span class="meta-chip meta-muted"><?= htmlspecialchars($a['created_at']) ?></span>
                                </div>
                                <h5 class="card-title mb-2"><a href="<?= htmlspecialchars($baseUrl) ?>/article/<?= (int)$a['article_id'] ?>"><?= htmlspecialchars($a['title']) ?></a></h5>
                                <p class="card-text text-muted flex-grow-1"><?= nl2br(htmlspecialchars($a['summary'] ?? '')) ?></p>
                                <a class="btn btn-primary mt-3" href="<?= htmlspecialchars($baseUrl) ?>/article/<?= (int)$a['article_id'] ?>">Doc tiep</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (($pages ?? 1) > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination">
                        <?php for ($p = 1; $p <= $pages; $p++): ?>
                            <li class="page-item <?= ($p == ($page ?? 1)) ? 'active' : '' ?>">
                                <a class="page-link" href="?cat=<?= (int)($selectedCat ?? 0) ?>&page=<?= $p ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card sticky-card mb-4" id="weather-card">
                <div class="weather-glow"></div>
                <div class="card-body position-relative">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0">Thời tiết</h6>
                        <span class="text-muted small" id="weather-updated"></span>
                    </div>
                    <form class="row g-2" id="weather-form">
                        <div class="col-8">
                            <select id="weather-city-select" class="form-select form-select-sm">
                                <option value="Ho Chi Minh" selected>TP HCM (mặc định)</option>
                                <option value="Ha Noi">Hà Nội</option>
                                <option value="Da Nang">Đà Nẵng</option>
                                <option value="Can Tho">Cần Thơ</option>
                                <option value="Hue">Huế</option>
                                <option value="Hai Phong">Hải Phòng</option>
                                <option value="Nha Trang">Nha Trang</option>
                                <option value="custom">Nhập khác...</option>
                            </select>
                            <input id="weather-city-custom" class="form-control form-control-sm mt-2 d-none" placeholder="Nhập tên thành phố khác">
                        </div>
                        <div class="col-4 d-grid">
                            <button class="btn btn-outline-light btn-sm" type="submit">Xem</button>
                        </div>
                    </form>
                    <div class="weather-hero d-flex align-items-center gap-3 mt-3">
                        <div class="weather-icon" id="weather-icon">&#9729;</div>
                        <div>
                            <div class="weather-temp" id="weather-temp">--&deg;C</div>
                            <div class="weather-desc text-muted" id="weather-desc">Sẵn sàng tra cứu</div>
                        </div>
                    </div>
                    <div class="weather-meta-grid mt-3">
                        <div class="weather-meta">
                            <span>Địa điểm</span>
                            <strong id="weather-location">-</strong>
                        </div>
                        <div class="weather-meta">
                            <span>Gió</span>
                            <strong id="weather-wind">-</strong>
                        </div>
                        <div class="weather-meta">
                            <span>Mã</span>
                            <strong id="weather-code">-</strong>
                        </div>
                    </div>
                    <div class="weather-state small text-muted mt-2" id="weather-state">Sẵn sàng tra cứu</div>
                </div>
            </div>

            <?php if (!empty($trending)): ?>
            <div class="card sticky-card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0">Đọc nhiều</h5>
                        <span class="text-muted small">7 ngày</span>
                    </div>
                    <ol class="trending-list">
                        <?php foreach ($trending as $t): ?>
                        <li>
                            <a href="<?= htmlspecialchars($baseUrl) ?>/article/<?= (int)$t['article_id'] ?>"><?= htmlspecialchars($t['title']) ?></a>
                            <div class="text-muted small">
                                <?= htmlspecialchars($t['category_name'] ?? 'N/A') ?> &middot; <?= number_format((int)($t['views_7d'] ?? 0)) ?> lượt xem
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Danh mục nổi bật</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach (array_slice(($categories ?? []), 0, 10) as $c): ?>
                            <a class="chip-link" href="<?= htmlspecialchars($baseUrl) ?>/category/<?= (int)$c['category_id'] ?>">
                                <?= htmlspecialchars($c['category_name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
(function(){
  const form = document.getElementById('weather-form');
  const selectEl = document.getElementById('weather-city-select');
  const customEl = document.getElementById('weather-city-custom');
  const state = document.getElementById('weather-state');
  const updated = document.getElementById('weather-updated');
  const iconEl = document.getElementById('weather-icon');
  const tempEl = document.getElementById('weather-temp');
  const descEl = document.getElementById('weather-desc');
  const locEl = document.getElementById('weather-location');
  const windEl = document.getElementById('weather-wind');
  const codeEl = document.getElementById('weather-code');
  const card = document.getElementById('weather-card');
  if (!form || !selectEl || !state || !iconEl || !tempEl || !descEl || !locEl || !windEl || !codeEl) return;

  const fmtTime = new Intl.DateTimeFormat('vi-VN', { hour:'2-digit', minute:'2-digit' });
  const setState = (text) => { state.textContent = text; };
  const escapeHtml = (str) => String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

  const moodMap = [
    { codes:[0], icon:'\u2600', desc:'Trời quang', mood:'sunny' },
    { codes:[1,2,3], icon:'\u26c5', desc:'Có mây', mood:'cloudy' },
    { codes:[45,48], icon:'\uD83C\uDF2B', desc:'Sương mù', mood:'cloudy' },
    { codes:[51,53,55,56,57], icon:'\uD83C\uDF26', desc:'Mưa nhẹ', mood:'rain' },
    { codes:[61,63,65,66,67,80,81,82], icon:'\uD83C\uDF27', desc:'Mưa', mood:'rain' },
    { codes:[71,73,75,77,85,86], icon:'\uD83C\uDF28', desc:'Tuyết', mood:'snow' },
    { codes:[95,96,99], icon:'\u26c8', desc:'Giông', mood:'storm' },
  ];
  const resolveMood = (code) => {
    const num = Number(code);
    for (const m of moodMap) {
      if (m.codes.includes(num)) return m;
    }
    return { icon:'\u2601', desc:'Thời tiết', mood:'default' };
  };

  const getCity = () => {
    const val = selectEl.value;
    if (val === 'custom') {
      return (customEl?.value || '').trim();
    }
    return val;
  };

  async function fetchWeather(city){
    const q = (city || '').trim();
    if (!q) {
      setState('Nhập tên thành phố');
      return;
    }
    setState('Đang tra cứu...');
    if (card) { card.dataset.mood = 'default'; }
    try{
      const geoRes = await fetch('https://geocoding-api.open-meteo.com/v1/search?count=1&language=vi&format=json&name=' + encodeURIComponent(q));
      if (!geoRes.ok) { throw new Error('geo'); }
      const geo = await geoRes.json();
      const place = geo && Array.isArray(geo.results) ? geo.results[0] : null;
      if (!place) {
        setState('Không tìm thấy địa điểm');
        return;
      }
      const lat = place.latitude;
      const lon = place.longitude;
      const weatherRes = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true&timezone=auto`);
      if (!weatherRes.ok) { throw new Error('weather'); }
      const data = await weatherRes.json();
      const cw = data.current_weather;
      if (!cw) {
        setState('Không có dữ liệu thời tiết');
        return;
      }
      const displayName = escapeHtml([place.name, place.country].filter(Boolean).join(', '));
      const temp = (typeof cw.temperature === 'number') ? cw.temperature.toFixed(1) : cw.temperature;
      const wind = (typeof cw.windspeed === 'number') ? cw.windspeed.toFixed(1) : cw.windspeed;
      const windDir = (cw.winddirection ?? '');
      const mood = resolveMood(cw.weathercode ?? '');
      iconEl.textContent = mood.icon;
      descEl.textContent = mood.desc;
      tempEl.textContent = `${temp ?? '--'}°C`;
      locEl.textContent = displayName || '-';
      windEl.textContent = `${escapeHtml(wind)} km/h | ${escapeHtml(windDir)}°`;
      codeEl.textContent = escapeHtml(cw.weathercode ?? '');
      setState('Cập nhật thành công');
      if (updated) { updated.textContent = fmtTime.format(new Date()); }
      if (card) { card.dataset.mood = mood.mood; }
    } catch (err){
      setState('Không thể lấy dữ liệu');
      if (card) { card.dataset.mood = 'default'; }
    }
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    fetchWeather(getCity());
  });
  selectEl.addEventListener('change', () => {
    const isCustom = selectEl.value === 'custom';
    if (customEl) {
      customEl.classList.toggle('d-none', !isCustom);
      if (isCustom) customEl.focus();
    }
    if (!isCustom) {
      fetchWeather(getCity());
    }
  });

  fetchWeather(getCity());
})();
</script>
