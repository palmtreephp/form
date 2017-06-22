<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Palmtree Form - Simple Example</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css"
          integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
</head>
<body>
<main>
    <div class="container mt-3">
        <h1>Palmtree Form - Simple Example</h1>
        <?php if ($success) { ?>
            <div class="alert alert-success">Form submitted successfully</div>
        <?php } ?>
        <?php echo $form->render(); ?>
    </div>
</main>
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js"
        integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js"
        integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn"
        crossorigin="anonymous"></script>
<script src="../../src/js/jquery.palmtree-form.js"></script>
<script src="../../public/js/jquery.bsAlert.js"></script>
</body>
</html>
