<?php

include_once "config.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Square Payment</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">

    <!-- Square Sandbox Web Payments SDK -->
    <script type="text/javascript" src="https://sandbox.web.squarecdn.com/v1/square.js"></script>

    <style>
        .payment-container {
            max-width: 700px;
            margin: 50px auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        #card-container {
            margin-top: 20px;
            margin-bottom: 20px;
        }

        #payment-status {
            margin-top: 20px;
        }
    </style>

</head>

<body>

    <div class="container">

        <div class="payment-container">

            <h1>Square Payment</h1>

            <hr>

            <div class="alert alert-info">
                <strong>Order Total:</strong>
                $<span id="order-total-display">0.00</span> AUD
            </div>

            <form id="square-payment-form">

                <h4>Enter Card Details</h4>

                <!-- Square creates the secure card form here -->
                <div id="card-container"></div>

                <button id="card-button" type="button" class="btn btn-dark btn-lg">
                    Pay $<span id="order-total-display-2">0.00</span> AUD
                </button>

            </form>

            <div id="payment-status"></div>

            <br>

            <a href="CartCode.html" class="btn btn-secondary">Return to Cart</a>

        </div>

    </div>

    <script>

        const applicationId = <?= json_encode(SQUARE_APPLICATION_ID) ?>;
        const locationId = <?= json_encode(SQUARE_LOCATION_ID) ?>;

        /**
         * Your cart total was saved into localStorage
         * as "cartTotal" when the checkout button was
         * clicked on CartCode.html.
         */
        const orderTotal = parseFloat(localStorage.getItem('cartTotal')) || 0;
        const cart = JSON.parse(localStorage.getItem('cart')) || [];

        document.getElementById('order-total-display').innerText = orderTotal.toFixed(2);
        document.getElementById('order-total-display-2').innerText = orderTotal.toFixed(2);

        async function initializeSquare() {

            if (orderTotal <= 0 || cart.length === 0) {
                showStatus("Your cart is empty. Please add an item before paying.", "danger");
                document.getElementById('card-button').disabled = true;
                return;
            }

            if (!window.Square) {
                showStatus("Square Payments SDK could not be loaded.", "danger");
                return;
            }

            try {

                const payments = window.Square.payments(applicationId, locationId);
                const card = await payments.card();
                await card.attach('#card-container');

                const cardButton = document.getElementById('card-button');

                cardButton.addEventListener('click', async function () {

                    cardButton.disabled = true;
                    showStatus("Processing payment...", "info");

                    try {

                        const tokenResult = await card.tokenize();

                        if (tokenResult.status !== 'OK') {
                            console.error(tokenResult.errors);
                            showStatus("Card error. Please check your details and try again.", "danger");
                            cardButton.disabled = false;
                            return;
                        }

                        const response = await fetch('square-process.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                sourceId: tokenResult.token,
                                amount: orderTotal,
                                cart: cart
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            localStorage.removeItem('cart');
                            localStorage.removeItem('cartTotal');
                            window.location.href = 'success.php?method=square';
                        } else {
                            console.error(result);
                            showStatus("Payment failed: " + (result.message || "unknown error"), "danger");
                            cardButton.disabled = false;
                        }

                    } catch (error) {
                        console.error(error);
                        showStatus("Something went wrong. Please try again.", "danger");
                        cardButton.disabled = false;
                    }

                });

            } catch (error) {
                console.error(error);
                showStatus("Unable to initialise Square payment.", "danger");
            }

        }

        function showStatus(message, type) {
            document.getElementById('payment-status').innerHTML =
                '<div class="alert alert-' + type + '">' + message + '</div>';
        }

        document.addEventListener('DOMContentLoaded', initializeSquare);

    </script>

</body>

</html>