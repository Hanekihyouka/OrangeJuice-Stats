<?php
/**
 * @var \OJStats\View $this
 * @var \OJStats\Stats\GlobalStats|null $stats null when loading failed
 * @var string|null $error
 * @var string $earliestDate Y-m-d
 * @var string $today Y-m-d
 */
$range = $stats?->range;
?>
<div class="box">
    <div class="global-toolbar">
        <label for="daterange">日期（UTC）</label>
        <input type="text" id="daterange" class="form-control js-daterange" readonly
               value="<?= $range ? $this->e($range->firstString() . ' ~ ' . $range->lastString()) : '全部' ?>"
               data-start="<?= $this->e($range?->firstString() ?? $today) ?>"
               data-end="<?= $this->e($range?->lastString() ?? $today) ?>"
               data-min="<?= $this->e($earliestDate) ?>"
               data-max="<?= $this->e($today) ?>">
        <span class="global-days">天数：<?= $range ? $range->days() : '全部' ?></span>
        <?php if ($range): ?>
            <a href="<?= $this->e($this->url('global.php')) ?>">查看全部</a>
        <?php endif ?>
        <a target="_blank" href="<?= $this->e($this->url('player.php')) ?>"> | 🔗 个人统计页面</a>
        <a target="_blank" href="https://github.com/Hanekihyouka/OrangeJuice-Stats"> | 🔗 GitHub 页面</a>
    </div>

    <?php if ($error !== null): ?>
        <div class="alert alert-danger"><?= $this->e($error) ?></div>
    <?php else: ?>
        <?php if ($range && $stats->liveDays > 0): ?>
            <p class="text-muted text-center small">
                最近 <?= \OJStats\Stats\GlobalStatsService::LIVE_DAYS ?> 天（昨天和今天）的数据仍在变化，今天的数据 Steam 尚未提供。
            </p>
        <?php endif ?>
        <?= $this->render('partials/character-table', ['records' => $stats->characters, 'total' => $stats->total()]) ?>
    <?php endif ?>
</div>
