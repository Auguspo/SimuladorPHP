<?php
require_once __DIR__ . '/bootstrap.php';
require_once PROJECT_ROOT . '/private/db.php';

session_start();
$_SESSION['user_id'] = 1;
session_write_close();

$opts = [
    "http" => [
        "method" => "GET",
        "header" => "Cookie: PHPSESSID=" . session_id() . "\r\n"
    ]
];
$context = stream_context_create($opts);
$result = file_get_contents('http://localhost/api/latest_sessions', false, $context);
if ($result === false) {
    echo "Error fetching URL. Headers:\n";
    print_r($http_response_header);
} else {
    echo $result;
}
