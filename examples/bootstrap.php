<?php

function template($file, $data = [])
{
    ob_start();

    extract($data);

    include $file;

    return ob_get_clean();
}

function redirect($location)
{
    header("Location: $location", true, 302);
    exit;
}

function send_json($data = [], $success = true)
{
    $response = json_encode([
        'success' => $success,
        'data'    => $data,
    ]);

    echo $response;
    exit;
}

function send_json_error($data = [])
{
    send_json($data, false);
}

function get_styles()
{
    return <<<HTML
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
HTML;
}

function get_scripts()
{
    return <<<HTML
<script src="https://code.jquery.com/jquery-3.5.0.min.js" integrity="sha256-xNzN2a4ltkB44Mc/Jz3pT4iU1cmeR0FkXs4pru/JxaQ=" crossorigin="anonymous"></script>
HTML;
}
