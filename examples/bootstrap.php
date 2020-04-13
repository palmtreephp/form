<?php

function template($file, $data = [])
{
    ob_start();

    extract($data);

    include $file;

    return ob_get_clean();
}

function redirect($location, $statusCode = 302)
{
    header(sprintf('Location: %s', $location), true, $statusCode);
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
