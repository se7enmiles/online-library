<?php
// BookLoop — tiny HTTP helper built on cURL.
// Every call to another server on the internet goes through here.

/**
 * GET a URL and decode the JSON it returns.
 * Returns null if the request fails or the answer isn't valid JSON.
 */
function httpGetJson(string $url, int $timeout = 10): ?array
{
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,   // give us the body as a string
        CURLOPT_FOLLOWLOCATION => true,   // follow redirects
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_USERAGENT      => 'BookLoop workshop project',
    ]);

    $body   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $status !== 200) {
        return null;
    }

    $data = json_decode($body, true);   // true = give me arrays, not objects

    return is_array($data) ? $data : null;
}

/**
 * POST JSON to a URL with extra headers, and decode the JSON answer.
 * Returns ['status' => int, 'data' => array|null].
 */
function httpPostJson(string $url, array $payload, array $headers = [], int $timeout = 30): array
{
    $ch = curl_init($url);

    $allHeaders = array_merge(['Content-Type: application/json'], $headers);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => $allHeaders,
        CURLOPT_TIMEOUT        => $timeout,
    ]);

    $body   = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = $body === false ? null : json_decode($body, true);

    return ['status' => $status, 'data' => is_array($data) ? $data : null];
}
