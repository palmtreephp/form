<?php

declare(strict_types=1);

// Router for PHP's built-in server that stubs a captcha verification endpoint.

$json = static function (array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
};

match (parse_url($_SERVER['REQUEST_URI'], \PHP_URL_PATH)) {
    '/success' => $json(['success' => true]),
    '/failure' => $json(['success' => false, 'error-codes' => ['invalid-input-response']]),
    '/invalid-json' => print 'not json',
    '/server-error' => $json(['success' => true], 500),
    '/unexpected-shape' => $json(['foo' => 'bar']),
    '/slow' => (static function () use ($json): void {
        sleep(3);
        $json(['success' => true]);
    })(),
    default => http_response_code(404),
};
