<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Palmtree Form - File Upload Example</title>
    <?php include '../shared/styles.php'; ?>
</head>
<body>
<main>
    <div class="container mt-3">
        <h1>Palmtree Form - File Upload Example</h1>
        <?php if ($success) { ?>
            <div class="alert alert-success">Form submitted successfully</div>
        <?php } ?>
        <?php echo $form->render(); ?>
    </div>
</main>
<?php include '../shared/scripts.php'; ?>
<script src="../../public/dist/js/palmtree-form.pkgd.js"></script>
</body>
</html>
