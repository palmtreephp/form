<?php declare(strict_types=1);

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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
HTML;
}

function get_scripts()
{
    return <<<HTML
HTML;
}
