<h1>Palmtree Form Examples</h1>
<ul>
<?php foreach (glob('*/index.php') ?: [] as $file): ?>
    <li><a href="<?= basename(dirname($file)); ?>/index.php"><?= basename(dirname($file)); ?></a></li>
<?php endforeach; ?>
</ul>
