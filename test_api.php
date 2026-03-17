<?php
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
        'content' => json_encode(['email' => 'admin@ggcs.com', 'password' => 'password'])
    ]
]);

$response = file_get_contents('http://127.0.0.1:8000/api/login', false, $context);
$data = json_decode($response);
$token = $data->token;

$context2 = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Accept: application/json\r\nAuthorization: Bearer $token\r\n"
    ]
]);

$companies = file_get_contents('http://127.0.0.1:8000/api/companies', false, $context2);
echo "Companies API status: " . substr($http_response_header[0], 9, 3) . "\n";
echo "Response snippet: " . substr($companies, 0, 100) . "\n";
