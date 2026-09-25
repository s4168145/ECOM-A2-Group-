// Square sandbox credentials. Application ID and Location ID are safe to
// use in the browser (same idea as Stripe's publishable key in stripepay.js) -
// they can't charge anything on their own. The real secret, the Access
// Token, stays on the server inside config.php and is never sent here.
const SQUARE_APPLICATION_ID = 'sandbox-sq0idb-UsQmAiHAtHB5FQl-6GABgA';
const SQUARE_LOCATION_ID = 'LXGGHH6JWJ91H';

let squareCard = null;
let squareCardMounted = false;

// Mounts the Square card form into #square-card-container the first time
// the Mastercard radio is selected. Mirrors mountStripeCard() for Visa.
async function mountSquareCard() {
    if (squareCardMounted) {
        return;
    }

    if (!window.Square) {
        document.getElementById('square-card-errors').textContent =
            'Square Payments SDK could not be loaded.';
        return;
    }

    try {
        const payments = window.Square.payments(SQUARE_APPLICATION_ID, SQUARE_LOCATION_ID);
        squareCard = await payments.card();
        await squareCard.attach('#square-card-container');
        squareCardMounted = true;
    } catch (error) {
        console.error(error);
        document.getElementById('square-card-errors').textContent =
            'Unable to load the card form.';
    }
}

document.getElementById('square-pay-button').addEventListener('click', async function () {

    const errorBox = document.getElementById('square-card-errors');
    errorBox.textContent = '';

    // Same billing-address check the assignment asks for: the billing form
    // must be valid before the payment can go ahead. This is the same
    // billingValid() function already defined in Billing Page 1.html.
    if (!billingValid()) {
        return;
    }

    if (!squareCard) {
        errorBox.textContent = 'Card form is not ready yet.';
        return;
    }

    const payButton = this;
    payButton.disabled = true;

    try {

        const tokenResult = await squareCard.tokenize();

        if (tokenResult.status !== 'OK') {
            console.error(tokenResult.errors);
            errorBox.textContent = 'Please check your card details and try again.';
            payButton.disabled = false;
            return;
        }

        // Reuse the same {id, qty} cart your PayPal checkout already builds
        // into the hidden #cart-field input.
        const cartField = document.getElementById('cart-field').value;

        const response = await fetch('square-process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                sourceId: tokenResult.token,
                cart: cartField
            })
        });

        const result = await response.json();

        if (result.success) {
            localStorage.removeItem('cart');
            window.location.href = 'success.php?method=square';
        } else {
            errorBox.textContent = 'Payment failed: ' + (result.message || 'unknown error');
            payButton.disabled = false;
        }

    } catch (error) {
        console.error(error);
        errorBox.textContent = 'Something went wrong. Please try again.';
        payButton.disabled = false;
    }

});