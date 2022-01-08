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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
HTML;
}

function get_scripts()
{
    return <<<HTML
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
HTML;
}
