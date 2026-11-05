<?php
/**
 * Page layout.
 *
 * @var \OJStats\View $this
 * @var string $title
 * @var string $description
 * @var string $content rendered page body
 * @var list<string> $styles extra stylesheets
 * @var list<string> $scripts extra scripts
 * @var string $bodyClass
 */
$styles ??= [];
$scripts ??= [];
$bodyClass ??= '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title) ?></title>
    <meta name="description" content="<?= $this->e($description) ?>">
    <meta property="og:title" content="<?= $this->e($title) ?>">
    <meta property="og:description" content="<?= $this->e($description) ?>">
    <meta property="og:image" content="<?= $this->e($this->url('images/share.png')) ?>">
    <link rel="icon" href="<?= $this->e($this->url('images/favicon.ico')) ?>">
    <link rel="stylesheet" href="<?= $this->e($this->url('css/bootstrap.css')) ?>">
    <link rel="stylesheet" href="<?= $this->e($this->url('css/jquery.dataTables.css')) ?>">
<?php foreach ($styles as $style): ?>
    <link rel="stylesheet" href="<?= $this->e($this->url($style)) ?>">
<?php endforeach ?>
    <link rel="stylesheet" href="<?= $this->e($this->url('css/app.css')) ?>">
</head>
<body class="<?= $this->e($bodyClass) ?>">
<main>
<?= $content ?>
</main>
<script src="<?= $this->e($this->url('js/jquery.js')) ?>"></script>
<script src="<?= $this->e($this->url('js/bootstrap.min.js')) ?>"></script>
<script src="<?= $this->e($this->url('js/jquery.dataTables.min.js')) ?>"></script>
<?php foreach ($scripts as $script): ?>
<script src="<?= $this->e($this->url($script)) ?>"></script>
<?php endforeach ?>
<script src="<?= $this->e($this->url('js/app.js')) ?>"></script>
</body>
</html>
