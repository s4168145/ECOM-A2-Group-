<?php

// Start the session so we can save payment info for success.php later
session_start();

// Load the Square API keys/URLs and the product data
include_once "config.php";
include_once "data.php";

// Tell the browser we are sending back JSON, not HTML
header('Content-Type: application/json');

/*
 * Only accept POST requests.
 */

// Block anyone trying to load this file directly 
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

// Read the JSON body that JavaScript sent us
$input = json_decode(file_get_contents('php://input'), true);

// Pull out the Square token and the cart JSON
$sourceId = $input['sourceId'] ?? '';
$cartJson = $input['cart'] ?? '[]';
$cart     = json_decode($cartJson, true);

// If there's no Square token, we can't charge anything 
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

// Start the running total at zero
$total = 0;

// Loop through each cart line and look up its real price in data.php
if (!empty($cart)) {
    foreach ($cart as $line) {
        foreach ($item as $product) {
            // If the ID matches, add price × quantity to the total
            if ($product['id'] === $line['id'] && (int) $line['qty'] > 0) {
                $total += $product['price'] * (int) $line['qty'];
                break;
            }
        }
    }
}

// If nothing was added, the cart is empty - stop here
if ($total <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Your cart is empty.'
    ]);
    exit;
}

// Square needs the amount in cents (so $10.50 becomes 1050)
$amountCents = (int) round($total * 100);

// A random unique key so the same payment can't be charged twice
$idempotencyKey = bin2hex(random_bytes(16));

// Build the request that will be sent to Square
$requestBody = [
    'source_id'       => $sourceId,          // the card token from the browser
    'idempotency_key' => $idempotencyKey,    // prevents double charges
    'amount_money'    => [
        'amount'   => $amountCents,          // total in cents
        'currency' => PAYPAL_CURRENCY        // 'AUD' from config.php
    ],
    'location_id' => SQUARE_LOCATION_ID,     // which Square location to use
    'note'        => 'Alice E-Bike Shop Order'
];

// Set up a cURL request to call Square's API
$ch = curl_init(SQUARE_API_URL);

curl_setopt($ch, CURLOPT_POST, true);              
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    // return the response as a string
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Square-Version: ' . SQUARE_API_VERSION,       // Square API version
    'Authorization: Bearer ' . SQUARE_ACCESS_TOKEN, // secret access token
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));

// Actually send the request to Square
$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE); // HTTP status (200 = OK)
$curlError = curl_error($ch);
curl_close($ch);

// If cURL itself failed (no internet, DNS issue, etc.)
if ($response === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Could not connect to Square: ' . $curlError
    ]);
    exit;
}

// Turn Square's JSON reply into a PHP array
$responseData = json_decode($response, true);

// If Square returned 200-299 AND a payment object, the charge succeeded
if ($httpCode >= 200 && $httpCode < 300 && isset($responseData['payment'])) {

    $payment = $responseData['payment'];

    // Save the payment details in the session so success.php can show them
    $_SESSION['square_payment'] = [
        'id'     => $payment['id'] ?? '',
        'status' => $payment['status'] ?? '',
        'amount' => $total
    ];

    // Tell the JavaScript it worked
    echo json_encode(['success' => true]);
    exit;
}

// If we got here, the payment failed - try to get the error message from Square
$message = 'Square payment failed.';

if (isset($responseData['errors'][0]['detail'])) {
    $message = $responseData['errors'][0]['detail'];
}

// Send the failure message back to the browser
echo json_encode([
    'success'         => false,
    'message'         => $message,
    'square_response' => $responseData
]);

?>