<?php
// process_square.php
// This file receives the card token from the frontend and asks Square to
// process the payment. Square requires this to happen server-side, so this
// PHP file is what actually talks to Square's API.

header('Content-Type: application/json');

// Get the JSON body sent by the frontend
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['token'])) {
    echo json_encode(['success' => false, 'error' => 'No token provided']);
    exit;
}

// --- SQUARE CONFIG ---
// Paste your Sandbox Access Token here.
// This is the one that shows as dots on the dashboard - click the copy icon
// to get the full value. It starts with "EAAA..."
$accessToken = 'EAAAl6qHXeCJv04P1FIMoMW2_Xt8X6cHNr2VSq5-5InfH6OjcRiazBRiTFYrKEei';

// Paste your Sandbox Location ID here.
// Must be the SAME value you put in Billing Page 1.html as squareLocationId.
$locationId = 'LXGGHH6JWJ91H';
// ---------------------

// Build the payment request
$payload = [
    'source_id' => $data['token'],
    'idempotency_key' => uniqid('', true), // unique key so Square doesn't double-charge
    'amount_money' => [
        'amount' => 100,        // $1.00 in cents (adjust to your cart total if you like)
        'currency' => 'AUD'     // change to USD/CAD/etc. if needed
    ],
    'location_id' => $locationId
];

// Send the request to Square's sandbox API
$ch = curl_init('https://connect.squareupsandbox.com/v2/payments');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json',
    'Square-Version: 2024-06-04'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$responseData = json_decode($response, true);

// Check the result
if ($httpCode === 200 && isset($responseData['payment']['status'])) {
    if ($responseData['payment']['status'] === 'COMPLETED') {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Payment status: ' . $responseData['payment']['status']]);
    }
} else {
    $error = 'Payment failed.';
    if (isset($responseData['errors'][0]['detail'])) {
        $error = $responseData['errors'][0]['detail'];
    }
    echo json_encode(['success' => false, 'error' => $error]);
}
?>