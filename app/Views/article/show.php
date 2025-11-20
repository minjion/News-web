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
?>

<div class="article-shell row">
    <div class="col-lg-9 mx-auto">
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

                    <?= nl2br(htmlspecialchars($articleContent ?? '')) ?>
                </div>
            </div>
        </div>

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
</script>
