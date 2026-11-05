<?php
/**
 * Character table (icon, games, wins, win rate) with a total row.
 *
 * @var \OJStats\View $this
 * @var list<\OJStats\Stats\CharacterRecord> $records
 * @var \OJStats\Stats\CharacterRecord $total
 */

// Win rates of characters with only a few games are meaningless.
$minGames = 10;

$winRate = function (\OJStats\Stats\CharacterRecord $record) use ($minGames): string {
    if ($record->games <= $minGames) {
        return '<abbr title="至少 ' . ($minGames + 1) . ' 场才会计算胜率">N/A</abbr>';
    }

    return $this->percent($record->winRate(), 3);
};
?>
<table class="table table-striped table-bordered stat-table js-stat-table" data-order-column="1">
    <thead>
    <tr>
        <th class="col-icon">角色</th>
        <th class="text-center">场数</th>
        <th class="text-center">胜场</th>
        <th class="text-center">胜率 %</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($records as $record): ?>
        <tr>
            <td data-order="<?= $this->e($record->name) ?>">
                <img class="stat-icon" src="<?= $this->e($this->url("images/chara/{$record->name}.webp")) ?>"
                     alt="<?= $this->e($record->name) ?>" title="<?= $this->e($record->name) ?>">
            </td>
            <td class="text-center" data-order="<?= $record->games ?>"><?= $record->games ?></td>
            <td class="text-center" data-order="<?= $record->wins ?>"><?= $record->wins ?></td>
            <td class="text-center" data-order="<?= $record->games > $minGames ? $record->winRate() : -1 ?>"><?= $winRate($record) ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
    <tfoot>
    <tr>
        <th>
            <img class="stat-icon" src="<?= $this->e($this->url('images/chara/A_Total.webp')) ?>" alt="合计" title="合计">
        </th>
        <th class="text-center"><?= $total->games ?></th>
        <th class="text-center"><?= $total->wins ?></th>
        <th class="text-center"><?= $total->games > 0 ? $this->percent($total->winRate(), 3) : 'N/A' ?></th>
    </tr>
    </tfoot>
</table>
