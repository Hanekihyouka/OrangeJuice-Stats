<?php
/**
 * Two column table: icon + count (cards, bounty NPCs).
 *
 * @var \OJStats\View $this
 * @var array<string, int> $values id => count
 * @var string $imageDir directory of <id>.webp icons, relative to public/
 * @var string $heading
 */
?>
<table class="table table-striped table-bordered stat-table js-stat-table" data-order-column="1">
    <thead>
    <tr>
        <th class="col-icon"></th>
        <th class="text-center"><?= $this->e($heading) ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($values as $id => $count): ?>
        <tr>
            <td data-order="<?= $this->e($id) ?>">
                <img class="stat-icon" src="<?= $this->e($this->url("$imageDir/$id.webp")) ?>"
                     alt="<?= $this->e($id) ?>" title="<?= $this->e($id) ?>">
            </td>
            <td class="text-center" data-order="<?= $count ?>"><?= $count ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
