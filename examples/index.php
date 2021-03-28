<h1>Palmtree Form Examples</h1>
<ul>
<?php foreach (glob('*/index.php') ?: [] as $file) { ?>
    <li><a href="<?php echo basename(dirname($file)); ?>/index.php"><?php echo basename(dirname($file)); ?></a></li>
<?php } ?>
</ul>
