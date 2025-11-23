<?php
/** @var array $totals */
/** @var array|null $topArticle */
/** @var array $topList */
/** @var array $recent */
/** @var array $chartData */

$views7d = (int)($totals['views7d'] ?? 0);
$articles7d = array_sum($chartData['week']['articles'] ?? []);
$chartJson = json_encode($chartData, JSON_UNESCAPED_UNICODE);
?>
<div class="container py-3 dashboard-page">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <p class="text-muted mb-1 small">T&#7893;ng quan</p>
            <h1 class="h3 mb-0">Dashboard</h1>
        </div>
        <a class="btn btn-outline-light" href="<?= htmlspecialchars($baseUrl) ?>/admin/articles">Qu&#7843;n l&#253; b&#224;i vi&#7871;t</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card bg-gradient-blue h-100">
                <div class="stat-top">
                    <div>
                        <div class="stat-label">Ng&#432;&#7901;i d&#249;ng</div>
                        <div class="stat-value"><?= number_format((int)($totals['users'] ?? 0)) ?></div>
                    </div>
                    <span class="stat-badge">T&#7893;ng</span>
                </div>
                <div class="stat-foot text-muted">&#272;&#227; &#273;&#259;ng k&#253;</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card bg-gradient-cyan h-100">
                <div class="stat-top">
                    <div>
                        <div class="stat-label">B&#224;i vi&#7871;t</div>
                        <div class="stat-value"><?= number_format((int)($totals['articles'] ?? 0)) ?></div>
                    </div>
                    <span class="stat-badge">&#272;&#227; xu&#7845;t b&#7843;n</span>
                </div>
                <div class="stat-foot text-muted">Trong h&#7879; th&#7889;ng</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card bg-gradient-yellow h-100">
                <div class="stat-top">
                    <div>
                        <div class="stat-label">T&#7893;ng l&#432;&#7907;t xem</div>
                        <div class="stat-value"><?= number_format((int)($totals['views'] ?? 0)) ?></div>
                    </div>
                    <span class="stat-badge">T&#7845;t c&#7843; th&#7901;i gian</span>
                </div>
                <div class="stat-foot text-muted">T&#259;ng tr&#432;&#7903;ng &#7897;n &#273;&#7883;nh</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card bg-gradient-red h-100">
                <div class="stat-top">
                    <div>
                        <div class="stat-label">L&#432;&#7907;t xem 7 ng&#224;y</div>
                        <div class="stat-value"><?= number_format($views7d) ?></div>
                    </div>
                    <span class="stat-badge">Tu&#7847;n n&#224;y</span>
                </div>
                <div class="stat-foot text-muted">C&#7853;p nh&#7853;t theo gi&#7901;</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card chart-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="mb-0">L&#432;u l&#432;&#7907;ng</h5>
                            <small class="text-muted" data-range-label>7 ng&#224;y g&#7847;n nh&#7845;t</small>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-light range-btn active" type="button" data-range="week">Tu&#7847;n</button>
                            <button class="btn btn-outline-light range-btn" type="button" data-range="month">Th&#225;ng</button>
                            <button class="btn btn-outline-light range-btn" type="button" data-range="year">N&#259;m</button>
                        </div>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="trafficChart" height="140"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0">B&#224;i xem nhi&#7873;u nh&#7845;t</h5>
                        <?php if (!empty($topArticle['article_id'])): ?>
                            <a class="btn btn-sm btn-outline-light" href="<?= htmlspecialchars($baseUrl) ?>/article/<?= (int)$topArticle['article_id'] ?>">Xem b&#224;i</a>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($topList)): ?>
                        <ol class="list-group list-group-numbered list-group-flush">
                            <?php foreach ($topList as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($item['title'] ?? 'N/A') ?></div>
                                    <div class="text-muted small">L&#432;&#7907;t xem: <?= number_format((int)($item['view_count'] ?? 0)) ?></div>
                                </div>
                                <a class="btn btn-sm btn-outline-light" href="<?= htmlspecialchars($baseUrl) ?>/article/<?= (int)$item['article_id'] ?>">Chi ti&#7871;t</a>
                            </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php else: ?>
                        <div class="text-muted">Ch&#432;a c&#243; d&#7919; li&#7879;u.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="mb-3">B&#224;i m&#7899;i nh&#7845;t</h5>
                    <?php if (!empty($recent)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recent as $r): ?>
                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($r['title'] ?? 'N/A') ?></div>
                                    <div class="text-muted small">
                                        Ng&#224;y: <?= htmlspecialchars($r['created_at'] ?? '') ?> &#183; L&#432;&#7907;t xem: <?= number_format((int)($r['views'] ?? 0)) ?>
                                    </div>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <a class="btn btn-outline-light" href="<?= htmlspecialchars($baseUrl) ?>/article/<?= (int)$r['article_id'] ?>">Xem</a>
                                    <a class="btn btn-outline-light" href="<?= htmlspecialchars($baseUrl) ?>/admin/articles/edit/<?= (int)$r['article_id'] ?>">S&#7917;a</a>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-muted">Ch&#432;a c&#243; b&#224;i vi&#7871;t.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="mb-3">&#272;i&#7875;m nhanh</h5>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="mini-stat">
                                <div class="mini-label">L&#432;&#7907;t xem tu&#7847;n</div>
                                <div class="mini-value"><?= number_format($views7d) ?></div>
                                <div class="mini-foot text-muted">7 ng&#224;y g&#7847;n &#273;&#226;y</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mini-stat">
                                <div class="mini-label">B&#224;i m&#7899;i trong tu&#7847;n</div>
                                <div class="mini-value"><?= number_format($articles7d) ?></div>
                                <div class="mini-foot text-muted">&#272;&#227; &#273;&#259;ng</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mini-stat">
                                <div class="mini-label">B&#224;i top 1</div>
                                <div class="mini-value"><?= number_format((int)($topArticle['view_count'] ?? 0)) ?></div>
                                <div class="mini-foot text-muted"><?= htmlspecialchars($topArticle['title'] ?? 'Ch&#432;a c&#243; d&#7919; li&#7879;u') ?></div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mini-stat">
                                <div class="mini-label">T&#7893;ng b&#224;i vi&#7871;t</div>
                                <div class="mini-value"><?= number_format((int)($totals['articles'] ?? 0)) ?></div>
                                <div class="mini-foot text-muted">&#272;ang hi&#7875;n th&#7883;</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
    const ctx = document.getElementById('trafficChart');
    if (!ctx) return;
    const series = <?= $chartJson ?>;
    const labelsEl = document.querySelector('[data-range-label]');
    const buttons = document.querySelectorAll('.range-btn');
    const textColor = (getComputedStyle(document.documentElement).getPropertyValue('--text') || '#e5e7eb').trim() || '#e5e7eb';

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: series.week.labels ?? [],
            datasets: [
                {
                    label: 'L\u01b0\u1ee3t xem',
                    data: series.week.views ?? [],
                    tension: 0.35,
                    fill: true,
                    backgroundColor: 'rgba(56, 189, 248, 0.18)',
                    borderColor: '#38bdf8',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#38bdf8'
                },
                {
                    label: 'B\u00e0i m\u1edbi',
                    data: series.week.articles ?? [],
                    tension: 0.35,
                    fill: false,
                    borderColor: '#22c55e',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#22c55e'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { labels: { color: textColor } },
                tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', titleColor: '#fff', bodyColor: '#e5e7eb' }
            },
            scales: {
                x: {
                    ticks: { color: textColor },
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: textColor },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                }
            }
        }
    });

    const rangeLabels = {
        week: '7 ng\u00e0y g\u1ea7n nh\u1ea5t',
        month: '30 ng\u00e0y g\u1ea7n nh\u1ea5t',
        year: '12 th\u00e1ng g\u1ea7n nh\u1ea5t'
    };

    const setRange = (range) => {
        const data = series[range] || series.week;
        chart.data.labels = data.labels || [];
        chart.data.datasets[0].data = data.views || [];
        chart.data.datasets[1].data = data.articles || [];
        chart.update();
        buttons.forEach(btn => btn.classList.toggle('active', btn.dataset.range === range));
        if (labelsEl && rangeLabels[range]) {
            labelsEl.textContent = rangeLabels[range];
        }
    };

    buttons.forEach(btn => {
        btn.addEventListener('click', () => setRange(btn.dataset.range || 'week'));
    });
    setRange('week');
})();
</script>
