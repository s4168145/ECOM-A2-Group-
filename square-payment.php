<?php

// Loads Square API keys, URLs and version from config.php
include_once "config.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Square Payment</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">

    <!-- Loads the Square Sandbox Web Payments SDK -->
    <script type="text/javascript" src="https://sandbox.web.squarecdn.com/v1/square.js"></script>

    <style>
        /* Centres the payment box on the page */
        .payment-container {
            max-width: 700px;
            margin: 50px auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        /* Square injects its card input into this box */
        #card-container {
            margin-top: 20px;
            margin-bottom: 20px;
        }

        /* Area where success/error messages appear */
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

            <!-- Shows the order total, filled in by JavaScript below -->
            <div class="alert alert-info">
                <strong>Order Total:</strong>
                $<span id="order-total-display">0.00</span> AUD
            </div>

            <form id="square-payment-form">

                <h4>Enter Card Details</h4>

                <!-- Square's secure card form is inserted here -->
                <div id="card-container"></div>

                <!-- Pay button, disabled until the card form is ready -->
                <button id="card-button" type="button" class="btn btn-dark btn-lg">
                    Pay $<span id="order-total-display-2">0.00</span> AUD
                </button>

            </form>

            <!-- Feedback area for the user -->
            <div id="payment-status"></div>

            <br>

            <!-- Link back to the cart page -->
            <a href="CartCode.html" class="btn btn-secondary">Return to Cart</a>

        </div>

    </div>

    <script>

        // Pulls the Square credentials from config.php and sends them to the browser as JS values
        const applicationId = <?= json_encode(SQUARE_APPLICATION_ID) ?>;
        const locationId = <?= json_encode(SQUARE_LOCATION_ID) ?>;

        // Reads the cart total and cart items saved earlier by CartCode.html
        const orderTotal = parseFloat(localStorage.getItem('cartTotal')) || 0;
        const cart = JSON.parse(localStorage.getItem('cart')) || [];

        // Shows the total in both places on the page
        document.getElementById('order-total-display').innerText = orderTotal.toFixed(2);
        document.getElementById('order-total-display-2').innerText = orderTotal.toFixed(2);

        // Sets up the Square card form when the page opens
        async function initializeSquare() {

            // If the cart is empty, blocks payment and disables the button
            if (orderTotal <= 0 || cart.length === 0) {
                showStatus("Your cart is empty. Please add an item before paying.", "danger");
                document.getElementById('card-button').disabled = true;
                return;
            }

            // If the Square SDK failed to load, shows an error
            if (!window.Square) {
                showStatus("Square Payments SDK could not be loaded.", "danger");
                return;
            }

            try {

                // Creates the Square payments object using the credentials
                const payments = window.Square.payments(applicationId, locationId);

                // Creates the card input and attaches it into #card-container
                const card = await payments.card();
                await card.attach('#card-container');

                const cardButton = document.getElementById('card-button');

                // Runs when the Pay button is clicked
                cardButton.addEventListener('click', async function () {

                    cardButton.disabled = true;
                    showStatus("Processing payment...", "info");

                    try {

                        // Sends the card details to Square and gets back a one-time token
                        const tokenResult = await card.tokenize();

                        // If the card is invalid, shows an error and re-enables the button
                        if (tokenResult.status !== 'OK') {
                            console.error(tokenResult.errors);
                            showStatus("Card error. Please check your details and try again.", "danger");
                            cardButton.disabled = false;
                            return;
                        }

                        // Sends the token and cart to square-process.php to actually charge the card
                        const response = await fetch('square-process.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                sourceId: tokenResult.token,
                                amount: orderTotal,
                                cart: cart
                            })
                        });

                        // Reads the PHP response (success or failure)
                        const result = await response.json();

                        // If payment succeeded, clears the cart and goes to success.php
                        if (result.success) {
                            localStorage.removeItem('cart');
                            localStorage.removeItem('cartTotal');
                            window.location.href = 'success.php?method=square';
                        } else {
                            // Otherwise, shows the error message from the server
                            console.error(result);
                            showStatus("Payment failed: " + (result.message || "unknown error"), "danger");
                            cardButton.disabled = false;
                        }

                    } catch (error) {
                        // Catches any unexpected error during tokenize or fetch
                        console.error(error);
                        showStatus("Something went wrong. Please try again.", "danger");
                        cardButton.disabled = false;
                    }

                });

            } catch (error) {
                // Catches any error while setting up the Square form
                console.error(error);
                showStatus("Unable to initialise Square payment.", "danger");
            }

        }

        // Helper function that displays a message in the #payment-status area
        function showStatus(message, type) {
            document.getElementById('payment-status').innerHTML =
                '<div class="alert alert-' + type + '">' + message + '</div>';
        }

        // Runs initializeSquare() once the page has fully loaded
        document.addEventListener('DOMContentLoaded', initializeSquare);

    </script>

</body>

</html>