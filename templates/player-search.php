<?php
/**
 * Search form shown when no (valid / public) player was requested.
 *
 * @var \OJStats\View $this
 * @var string $input previous input
 * @var list<string> $errors
 */
?>
<div class="search-overlay">
    <form class="search-box" method="get" action="<?= $this->e($this->url('player.php')) ?>">
        <h2>百分百鲜橙汁，玩家统计</h2>
        <div class="search-input">
            <input type="text" id="steamid" name="steamid" value="<?= $this->e($input) ?>" required
                   autocomplete="off" spellcheck="false">
            <label for="steamid">Steam64 ID 或个人资料链接</label>
        </div>
        <button type="submit">查询</button>
        <?php if ($errors !== []): ?>
            <div class="search-error">
                <?php foreach ($errors as $line): ?>
                    <p><?= $this->e($line) ?></p>
                <?php endforeach ?>
            </div>
        <?php endif ?>
        <p class="search-hint">
            <a href="<?= $this->e($this->url('global.php')) ?>">查看全球统计</a>
        </p>
    </form>
</div>
