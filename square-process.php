<?php

session_start();

include_once "config.php";
include_once "data.php";

header('Content-Type: application/json');

/*
 * Only accept POST requests.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

/**
 * Read JSON request.
 */

$input = json_decode(file_get_contents('php://input'), true);

$sourceId = $input['sourceId'] ?? '';
$cartJson = $input['cart'] ?? '[]';
$cart     = json_decode($cartJson, true);

if (empty($sourceId)) {
    echo json_encode([
        'success' => false,
        'message' => 'Payment token is missing.'
    ]);
    exit;
}

/**
 * Recalculate the total from data.php, the same way
 * paypal_checkout.php already does. We do NOT trust
 * a price sent from the browser.
 */

$total = 0;

if (!empty($cart)) {
    foreach ($cart as $line) {
        foreach ($item as $product) {
            if ($product['id'] === $line['id'] && (int) $line['qty'] > 0) {
                $total += $product['price'] * (int) $line['qty'];
                break;
            }
        }
    }
}

if ($total <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Your cart is empty.'
    ]);
    exit;
}

$amountCents = (int) round($total * 100);

$idempotencyKey = bin2hex(random_bytes(16));

$requestBody = [
    'source_id'       => $sourceId,
    'idempotency_key' => $idempotencyKey,
    'amount_money'    => [
        'amount'   => $amountCents,
        'currency' => PAYPAL_CURRENCY   // already 'AUD' in config.php
    ],
    'location_id' => SQUARE_LOCATION_ID,
    'note'        => 'Alice E-Bike Shop Order'
];

$ch = curl_init(SQUARE_API_URL);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Square-Version: ' . SQUARE_API_VERSION,
    'Authorization: Bearer ' . SQUARE_ACCESS_TOKEN,
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Could not connect to Square: ' . $curlError
    ]);
    exit;
}

$responseData = json_decode($response, true);

if ($httpCode >= 200 && $httpCode < 300 && isset($responseData['payment'])) {

    $payment = $responseData['payment'];

    // Save the result so success.php can display it, the same way
    // paypal_checkout.php saves $_SESSION['name'] for success.php.
    $_SESSION['square_payment'] = [
        'id'     => $payment['id'] ?? '',
        'status' => $payment['status'] ?? '',
        'amount' => $total
    ];

    echo json_encode(['success' => true]);
    exit;
}

$message = 'Square payment failed.';

if (isset($responseData['errors'][0]['detail'])) {
    $message = $responseData['errors'][0]['detail'];
}

echo json_encode([
    'success'         => false,
    'message'         => $message,
    'square_response' => $responseData
]);

?>