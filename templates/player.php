<?php
/**
 * @var \OJStats\View $this
 * @var \OJStats\Stats\PlayerProfile $profile
 * @var \OJStats\Stats\PlayerStats $stats
 * @var \OJStats\Game\GameData $game
 * @var list<int> $renderIds
 * @var int $fetchedAt unix time of the Steam query
 * @var int $cacheTtl seconds the data is cached, 0 = not cached
 */

use OJStats\Game\CoopRole;

$summary = [
    ['等级', $stats->level(), '经验', $stats->get('STAT_PLAYER_EXP')],
    ['线上游戏场数', $stats->onlineGamesPlayed(), '线上游戏胜场', $stats->onlineGamesWon()],
    ['赏金模式场数', $stats->bountyGamesPlayed(), '赏金模式胜场', $stats->bountyGamesWon()],
    ['合作模式场数', $stats->coopGamesPlayed(), 'NPC 击倒数', $stats->npcDefeats()],
];
$renderUrl = $this->absoluteUrl('render.php', ['steamid' => (string) $profile->steamId]);
?>
<div class="box">
    <div class="player-header">
        <div class="player-avatar">
            <img src="<?= $this->e($profile->avatarUrl) ?>" alt="">
            <a href="<?= $this->e($this->url('player.php')) ?>" class="player-logo" title="查询其他玩家">
                <img src="<?= $this->e($this->url('images/logo.png')) ?>" alt="100% Orange Juice">
            </a>
        </div>
        <div class="player-info">
            <h2><?= $this->e($profile->name) ?> 的统计数据</h2>
            <p class="text-muted small player-updated">
                数据更新于 <?= gmdate('Y-m-d H:i', $fetchedAt) ?> UTC
                <?php if ($cacheTtl > 0): ?>（同一玩家的数据最多每 <?= $this->e($this->duration($cacheTtl)) ?>从 Steam 刷新一次）<?php endif ?>
            </p>
            <table class="player-summary">
                <?php foreach ($summary as [$label1, $value1, $label2, $value2]): ?>
                    <tr>
                        <th><?= $this->e($label1) ?></th>
                        <td><?= $value1 ?></td>
                        <th><?= $this->e($label2) ?></th>
                        <td><?= $value2 ?></td>
                    </tr>
                <?php endforeach ?>
                <?php foreach (CoopRole::cases() as $role): ?>
                    <tr>
                        <th>
                            <img class="role-icon" src="<?= $this->e($this->url("images/role{$role->index()}.png")) ?>"
                                 alt="<?= $role->name ?>" title="<?= $role->name ?>">
                            <?= $this->e($role->label()) ?> 等级
                        </th>
                        <td><?= $stats->coopLevel($role) ?></td>
                        <th>经验</th>
                        <td><?= $stats->get($role->value) ?></td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>
    </div>

    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#tab-chara" role="tab" data-toggle="tab">角色统计</a></li>
        <li role="presentation"><a href="#tab-card" role="tab" data-toggle="tab">卡牌携带</a></li>
        <li role="presentation"><a href="#tab-bh" role="tab" data-toggle="tab">赏金击杀</a></li>
        <li role="presentation"><a href="#tab-other" role="tab" data-toggle="tab">垃圾桶</a></li>
        <li role="presentation"><a href="#tab-image" role="tab" data-toggle="tab">图片</a></li>
    </ul>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="tab-chara">
            <?= $this->render('partials/character-table', [
                'records' => $stats->characters,
                'total' => $stats->characterTotal(),
            ]) ?>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-card">
            <?= $this->render('partials/icon-table', [
                'values' => $stats->cards,
                'imageDir' => 'images/cards',
                'heading' => '携带次数',
            ]) ?>
            <p class="text-muted">并不是所有的卡包都有对应的统计，只有早年的几个卡包有统计项。</p>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-bh">
            <?= $this->render('partials/icon-table', [
                'values' => $stats->bounties,
                'imageDir' => 'images/bh',
                'heading' => '击杀次数',
            ]) ?>
            <p class="text-muted">击倒赏金 NPC 的次数。</p>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-other">
            <table class="table table-striped table-bordered stat-table js-stat-table" data-order-column="0" data-order-dir="asc">
                <thead>
                <tr>
                    <th>键</th>
                    <th class="text-center">值</th>
                    <th class="text-center">备注</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($stats->others as $stat => $value): ?>
                    <tr>
                        <td><?= $this->e($stat) ?></td>
                        <td class="text-center"><?= $value ?></td>
                        <td class="text-center"><?= $this->e($game->describe($stat)) ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
            <p class="text-muted">不在前 3 个表中的数据，都被丢到了这个表中。</p>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-image">
            <p>把统计数据生成为图片，可以直接贴到论坛、群聊里。图片地址：</p>
            <input type="text" class="form-control js-select-on-focus" readonly value="<?= $this->e($renderUrl) ?>">
            <p class="text-muted small">
                可选参数：<code>render=<?= min($renderIds) ?>~<?= max($renderIds) ?></code> 样式，
                <code>limit=</code> 显示的角色数量，<code>nick=</code> 自定义昵称，<code>sp1=</code> 徽章，
                <code>format=png</code> 返回 PNG（默认返回体积更小的 WebP）。
            </p>
            <div class="render-previews">
                <?php foreach ($renderIds as $id): ?>
                    <a href="<?= $this->e($this->url('render.php', ['steamid' => (string) $profile->steamId, 'render' => $id])) ?>"
                       target="_blank" rel="noopener">render=<?= $id ?></a>
                <?php endforeach ?>
            </div>
        </div>
    </div>
</div>
