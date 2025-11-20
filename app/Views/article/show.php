<?php
$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="675" viewBox="0 0 1200 675"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#0ea5e9" stop-opacity=".18"/><stop offset="1" stop-color="#64748b" stop-opacity=".18"/></linearGradient></defs><rect fill="url(#g)" width="1200" height="675"/><g fill="none" stroke="#94a3b8" stroke-width="16" opacity=".85"><rect x="420" y="245" width="360" height="240" rx="24"/><path d="M520 325h40l20-20h40l20 20h40"/><circle cx="600" cy="365" r="36"/></g></svg>';
$placeholder = 'data:image/svg+xml;base64,' . base64_encode($svg);

$coverUrl = '';
$coverCaption = '';
$usedCoverId = null;

if (!empty($images)) {
    foreach ($images as $img) {
        $rel = (string)($img['media_url'] ?? '');
        $fsPath = __DIR__ . '/../../../public/' . $rel;
        if ($rel && is_file($fsPath)) {
            $coverUrl = $baseUrl . '/' . $rel;
            $coverCaption = (string)($img['caption'] ?? '');
            $usedCoverId = (int)$img['media_id'];
            break;
        }
    }
}
$coverUrl = $coverUrl ?: $placeholder;

$bodyImages = [];
if (!empty($images)) {
    foreach ($images as $img) {
        if ((int)($img['media_id'] ?? 0) === $usedCoverId) {
            continue;
        }
        $bodyImages[] = $img;
    }
}

// Xay dung muc luc tu noi dung (#, ##, ### dau dong)
$contentHtml = '';
$tocEntries = [];
$idCounts = [];
$lines = preg_split("/\\r\\n|\\n|\\r/", (string)($articleContent ?? ''));

// Ham resolve duong dan anh (tuong doi -> baseUrl, neu khong ton tai thi placeholder)
$resolveImg = function(string $path) use ($placeholder, $baseUrl): string {
    $trim = trim($path);
    if ($trim === '') {
        return $placeholder;
    }
    // Neu la URL tuyet doi thi giu nguyen
    if (preg_match('/^https?:\\/\\//i', $trim)) {
        return $trim;
    }
    // Loai bo dau slash
    $rel = ltrim($trim, '/');
    $fs = __DIR__ . '/../../../public/' . $rel;
    if (is_file($fs)) {
        return $baseUrl . '/' . $rel;
    }
    return $placeholder;
};

// Resolve media (image/video) tu duong dan tuong doi hoac URL
$resolveMedia = function(string $path) use ($baseUrl): ?string {
    $trim = trim($path);
    if ($trim === '') {
        return null;
    }
    if (preg_match('/^https?:\\/\\//i', $trim)) {
        return $trim;
    }
    $rel = ltrim($trim, '/');
    $fs = __DIR__ . '/../../../public/' . $rel;
    if (is_file($fs)) {
        return $baseUrl . '/' . $rel;
    }
    return null;
};

// Extract YouTube ID (11 chars) tu URL
$extractYoutubeId = function(string $url): ?string {
    $u = trim($url);
    if ($u === '') { return null; }
    if (preg_match('~youtu\\.be/([A-Za-z0-9_-]{11})~', $u, $m)) {
        return $m[1];
    }
    if (preg_match('~youtube\\.com/(?:embed/|watch\\?[^\\s]*v=)([A-Za-z0-9_-]{11})~', $u, $m)) {
        return $m[1];
    }
    parse_str((string)(parse_url($u, PHP_URL_QUERY) ?? ''), $q);
    if (!empty($q['v']) && preg_match('/^[A-Za-z0-9_-]{11}$/', (string)$q['v'])) {
        return (string)$q['v'];
    }
    return null;
};

// Extract Vimeo ID (chuoi so)
$extractVimeoId = function(string $url): ?string {
    $u = trim($url);
    if ($u === '') { return null; }
    if (preg_match('~vimeo\\.com/(?:video/)?([0-9]{6,})~', $u, $m)) {
        return $m[1];
    }
    return null;
};

// Render video embed (YouTube/Vimeo/video file)
$renderVideo = function(string $type, string $url) use ($resolveMedia, $extractYoutubeId, $extractVimeoId): string {
    $type = strtolower(trim($type));
    $src = null;

    if ($type === 'youtube') {
        $vid = $extractYoutubeId($url);
        if ($vid) {
            $src = 'https://www.youtube.com/embed/' . rawurlencode($vid);
        }
    } elseif ($type === 'vimeo') {
        $vid = $extractVimeoId($url);
        if ($vid) {
            $src = 'https://player.vimeo.com/video/' . rawurlencode($vid);
        }
    } else { // video file
        $src = $resolveMedia($url);
    }

    if (!$src) {
        return '<div class="alert alert-warning small mb-3">Video URL khong hop le.</div>';
    }

    if ($type === 'video') {
        return '<div class="video-embed ratio-16x9"><video controls preload="metadata" playsinline src="' . htmlspecialchars($src) . '"></video></div>';
    }
    return '<div class="video-embed ratio-16x9"><iframe src="' . htmlspecialchars($src) . '" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe></div>';
};

foreach ($lines as $ln) {
    $trim = trim($ln);
    if ($trim === '') {
        continue;
    }
    // Video embed: @[youtube](url), @[vimeo](url), @[video](mp4 hoac duong dan file)
    if (preg_match('/^@\\[(youtube|vimeo|video)\\]\\(([^)]+)\\)$/i', $trim, $mVid)) {
        $contentHtml .= $renderVideo($mVid[1], $mVid[2]);
        continue;
    }
    // Markdown anh: ![caption](duong_dan)
    if (preg_match('/^!\\[([^\\]]*)\\]\\(([^)]+)\\)$/', $trim, $mImg)) {
        $caption = trim($mImg[1]);
        $src = $resolveImg($mImg[2]);
        $contentHtml .= '<figure class="article-image inline-image img-large img-center">';
        $contentHtml .= '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($caption) . '">';
        if ($caption !== '') {
            $contentHtml .= '<figcaption>' . htmlspecialchars($caption) . '</figcaption>';
        }
        $contentHtml .= '</figure>';
        continue;
    }
    if (preg_match('/^(#{1,3})\\s*(.+)$/', $trim, $m)) {
        $level = strlen($m[1]); // 1-3
        $text = trim($m[2]);
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $text));
        $slug = trim($slug, '-');
        if ($slug === '') {
            $slug = 'section';
        }
        $idx = $idCounts[$slug] ?? 0;
        $idCounts[$slug] = $idx + 1;
        $id = $idx > 0 ? $slug . '-' . $idx : $slug;
        // map #->h2, ##->h3, ###->h4
        $tag = min(4, 1 + $level);
        $tocEntries[] = ['id' => $id, 'text' => $text, 'level' => $tag];
        $contentHtml .= '<h' . $tag . ' id="' . htmlspecialchars($id) . '">' . htmlspecialchars($text) . '</h' . $tag . '>';
    } else {
        $contentHtml .= '<p>' . nl2br(htmlspecialchars($trim)) . '</p>';
    }
}
if ($contentHtml === '') {
    $contentHtml = nl2br(htmlspecialchars($articleContent ?? ''));
}
?>

<div class="article-shell row">
    <div id="read-progress"></div>
    <div class="col-lg-9 mx-auto">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($baseUrl) ?>/">Trang chủ</a></li>
                <?php if (!empty($article['category_id'])): ?>
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($baseUrl) ?>/category/<?= (int)$article['category_id'] ?>"><?= htmlspecialchars($article['category_name'] ?? 'Danh mục') ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($article['title']) ?></li>
            </ol>
        </nav>
        <div class="card article-hero mb-4 overflow-hidden">
            <div class="article-hero-media" style="background-image: linear-gradient(120deg, rgba(15,23,42,.65), rgba(14,165,233,.2)), url('<?= htmlspecialchars($coverUrl) ?>');"></div>
            <div class="article-hero-body">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <a class="badge badge-cat" href="<?= htmlspecialchars($baseUrl) ?>/category/<?= (int)($article['category_id'] ?? 0) ?>"><?= htmlspecialchars($article['category_name'] ?? 'N/A') ?></a>
                    <span class="meta-chip"><?= htmlspecialchars($article['created_at']) ?></span>
                    <span class="meta-chip"><?= number_format((int)($article['views_count'] ?? 0)) ?> lượt xem</span>
                    <span class="meta-chip"><?= (int)($readingMinutes ?? 1) ?> phút đọc</span>
                </div>
                <h1 class="article-title display-6 mb-2"><?= htmlspecialchars($article['title']) ?></h1>
                <div class="article-meta mb-3">Tác giả: <?= htmlspecialchars($article['username'] ?? 'N/A') ?></div>
                <?php if (!empty($article['summary'])): ?>
                    <p class="lead article-lead"><?= nl2br(htmlspecialchars($article['summary'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($coverCaption)): ?>
                    <div class="text-muted small"><?= htmlspecialchars($coverCaption) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card article-body mb-4">
            <div class="card-body">
                <?php if (!empty($tocEntries)): ?>
                <div class="toc-box mb-4">
                    <div class="toc-title">Mục lục</div>
                    <ol class="toc-list">
                        <?php foreach ($tocEntries as $t): ?>
                        <li class="toc-item level-<?= (int)$t['level'] ?>">
                            <a href="#<?= htmlspecialchars($t['id']) ?>"><?= htmlspecialchars($t['text']) ?></a>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <?php endif; ?>
                <div class="article-content">
                    <?php if (!empty($bodyImages)): ?>
                        <?php foreach ($bodyImages as $img): ?>
                            <?php 
                                $rel = (string)($img['media_url'] ?? '');
                                $fsPath = __DIR__ . '/../../../public/' . $rel;
                                if (!is_file($fsPath)) { continue; }
                            ?>
                            <figure class="article-image <?= htmlspecialchars($img['size_class'] ?? 'img-medium') ?> <?= htmlspecialchars($img['align_class'] ?? 'img-center') ?>">
                                <img src="<?= htmlspecialchars($baseUrl . '/' . $rel) ?>" alt="<?= htmlspecialchars($img['caption'] ?? '') ?>">
                                <?php if (!empty($img['caption'])): ?>
                                <figcaption><?= htmlspecialchars($img['caption']) ?></figcaption>
                                <?php endif; ?>
                            </figure>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?= $contentHtml ?>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div class="btn-group" role="group">
                <a class="btn btn-outline-light" href="<?= htmlspecialchars($baseUrl) ?>/">Về trang chủ</a>
                <?php if (!empty($article['category_id'])): ?>
                <a class="btn btn-outline-light" href="<?= htmlspecialchars($baseUrl) ?>/category/<?= (int)$article['category_id'] ?>">Xem danh mục</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($prevNext['prev']) || !empty($prevNext['next'])): ?>
        <div class="card mb-4">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div class="flex-fill text-start">
                    <?php if (!empty($prevNext['prev'])): ?>
                    <div class="text-muted small mb-1">Bài trước</div>
                    <a class="h6 d-block" href="<?= htmlspecialchars($baseUrl) ?>/article/<?= (int)$prevNext['prev']['article_id'] ?>"><?= htmlspecialchars($prevNext['prev']['title'] ?? '') ?></a>
                    <?php endif; ?>
                </div>
                <div class="flex-fill text-end">
                    <?php if (!empty($prevNext['next'])): ?>
                    <div class="text-muted small mb-1">Bài tiếp</div>
                    <a class="h6 d-block" href="<?= htmlspecialchars($baseUrl) ?>/article/<?= (int)$prevNext['next']['article_id'] ?>"><?= htmlspecialchars($prevNext['next']['title'] ?? '') ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($related)): ?>
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0">Bài liên quan</h5>
                    <a class="text-muted small" href="<?= htmlspecialchars($baseUrl) ?>/category/<?= (int)($article['category_id'] ?? 0) ?>">Xem danh mục</a>
                </div>
                <div class="row g-3">
                    <?php foreach ($related as $r): ?>
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm article-related">
                            <?php
                                $relSrc = '';
                                $relThumb = (string)($r['thumb'] ?? '');
                                if ($relThumb !== '') {
                                    $p = __DIR__ . '/../../../public/' . $relThumb;
                                    if (is_file($p)) {
                                        $relSrc = $baseUrl . '/' . $relThumb;
                                    }
                                }
                                $relSrc = $relSrc ?: $placeholder;
                            ?>
                            <img class="article-thumb" src="<?= htmlspecialchars($relSrc) ?>" alt="<?= htmlspecialchars($r['title']) ?>">
                            <div class="card-body d-flex flex-column">
                                <span class="badge badge-cat mb-2"><?= htmlspecialchars($r['category_name'] ?? 'N/A') ?></span>
                                <h6 class="mb-2"><a href="<?= htmlspecialchars($baseUrl) ?>/article/<?= (int)$r['article_id'] ?>"><?= htmlspecialchars($r['title']) ?></a></h6>
                                <div class="text-muted small mb-2"><?= htmlspecialchars($r['created_at'] ?? '') ?></div>
                                <p class="text-muted flex-grow-1 mb-0"><?= nl2br(htmlspecialchars($r['summary'] ?? '')) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="card mb-5">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0">Bình luận</h5>
                    <span class="text-muted small"><?= count($comments ?? []) ?> mục</span>
                </div>
                <?php if (!empty($comments)): ?>
                <ul class="list-group mb-3" id="comment-list">
                    <?php foreach ($comments as $c): ?>
                        <li class="list-group-item">
                            <div class="fw-semibold"><?= htmlspecialchars($c['username']) ?></div>
                            <div><?= nl2br(htmlspecialchars($c['content'])) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($c['created_at']) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                    <div class="alert alert-info">Chưa có bình luận nào.</div>
                <?php endif; ?>

                <?php if (!empty($_SESSION['user_id'])): ?>
                <div class="comment-form">
                    <div class="mb-2">Thêm bình luận</div>
                    <textarea id="comment-content" class="form-control mb-2" rows="3" placeholder="Nội dung..."></textarea>
                    <button id="btn-send" class="btn btn-primary">Gửi</button>
                </div>
                <?php else: ?>
                <div class="alert alert-info mb-0">Vui lòng <a href="<?= htmlspecialchars($baseUrl) ?>/auth/login">đăng nhập</a> để bình luận.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
const baseUrl = '<?= htmlspecialchars($baseUrl) ?>';
const articleId = <?= (int)$article['article_id'] ?>;

<?php if (!empty($_SESSION['user_id'])): ?>
document.getElementById('btn-send').addEventListener('click', async () => {
  const textarea = document.getElementById('comment-content');
  const content = textarea.value.trim();
  if (!content) return;
  const res = await postJSON(baseUrl + '/api/comments', {article_id: articleId, content});
  if (!res.error){
    location.reload();
  } else {
    alert(res.error || 'Loi');
  }
});
<?php endif; ?>

// Scroll progress bar
(function(){
  const bar = document.getElementById('read-progress');
  const articleBody = document.querySelector('.article-body');

  function update(){
    if(!bar || !articleBody) return;
    const start = articleBody.offsetTop;
    const end = start + articleBody.offsetHeight - window.innerHeight;
    const scroll = window.scrollY || document.documentElement.scrollTop || 0;
    const pct = end > start ? Math.min(1, Math.max(0, (scroll - start) / (end - start))) : 0;
    bar.style.transform = `scaleX(${pct})`;
  }

  window.addEventListener('scroll', update, {passive:true});
  window.addEventListener('resize', update);
  update();
})();

// Smooth scroll for TOC
(function(){
  const links = document.querySelectorAll('.toc-box a[href^="#"]');
  links.forEach(a => {
    a.addEventListener('click', function(e){
      e.preventDefault();
      const id = this.getAttribute('href').replace('#','');
      const target = document.getElementById(id);
      if(!target) return;
      const top = target.getBoundingClientRect().top + window.scrollY - 72;
      window.scrollTo({top, behavior:'smooth'});
    });
  });
})();
</script>
