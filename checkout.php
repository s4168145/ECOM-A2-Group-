<?php
include_once "config.php";
include_once "data.php";

// Get product id from URL
$id = isset($_GET['id']) ? $_GET['id'] : '1';

// Find that product
$product = null;
foreach ($item as $i) {
    if ($i['id'] == $id) {
        $product = $i;
        break;
    }
}

if ($product == null) {
    echo "Product not found.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment = isset($_POST['payment']) ? $_POST['payment'] : '';

    if ($payment == 'paypal') {
        // Auto-submit a form to PayPal sandbox
        ?>
        <!DOCTYPE html>
        <html>
        <head><title>Redirecting to PayPal...</title></head>
        <body>
            <p>Redirecting to PayPal, please wait...</p>
            <form id="paypalForm" action="<?php echo PAYPAL_URL; ?>" method="post">
                <input type="hidden" name="cmd" value="_xclick">
                <input type="hidden" name="business" value="<?php echo PAYPAL_ID; ?>">
                <input type="hidden" name="item_name" value="<?php echo $product['name']; ?>">
                <input type="hidden" name="item_number" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="amount" value="<?php echo $product['price']; ?>">
                <input type="hidden" name="currency_code" value="<?php echo PAYPAL_CURRENCY; ?>">
                <input type="hidden" name="return" value="<?php echo PAYPAL_RETURN_URL; ?>">
                <input type="hidden" name="cancel_return" value="<?php echo PAYPAL_CANCEL_URL; ?>">
                <input type="hidden" name="notify_url" value="<?php echo PAYPAL_NOTIFY_URL; ?>">
            </form>
            <script>document.getElementById('paypalForm').submit();</script>
        </body>
        </html>
        <?php
        exit;
    } else {
        // For this tutorial only PayPal is fully integrated
        echo "<h2>This payment option is not available yet.</h2>";
        echo "<p>Please go back and choose PayPal.</p>";
        echo '<a href="checkout.php?id=' . $id . '">Go back</a>';
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout - Billing Information</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { display: flex; gap: 40px; }
        .left  { flex: 1; }
        .right { flex: 1; background: #f5f5f5; padding: 20px; }
        label { display: block; margin: 8px 0; cursor: pointer; }
        input[type="text"], input[type="email"] {
            width: 90%; padding: 6px; margin-bottom: 10px;
        }
        .row { display: flex; gap: 10px; }
        .row > div { flex: 1; }
        .pay-option {
            display: flex; align-items: center; padding: 8px;
            background: white; margin-bottom: 8px;
            border: 1px solid #ddd;
        }
        .pay-option img { height: 40px; margin-left: 10px; }
        button {
            background: #0070ba; color: white; padding: 12px 30px;
            border: none; font-size: 16px; cursor: pointer; width: 100%;
        }
    </style>
</head>
<body>
    <h1>Provide Billing Information</h1>

    <form method="post" action="checkout.php?id=<?php echo $product['id']; ?>">
        <div class="container">

            <!-- LEFT: Billing Information -->
            <div class="left">
                <h2>Billing address</h2>

                <div class="row">
                    <div>
                        <label>First name</label>
                        <input type="text" name="firstname">
                    </div>
                    <div>
                        <label>Last name</label>
                        <input type="text" name="lastname">
                    </div>
                </div>

                <label>Username</label>
                <input type="text" name="username">

                <label>Email (Optional)</label>
                <input type="email" name="email" placeholder="you@example.com">

                <label>Address</label>
                <input type="text" name="address" placeholder="1234 Main St">

                <label>Address 2 (Optional)</label>
                <input type="text" name="address2" placeholder="Apartment or suite">

                <div class="row">
                    <div>
                        <label>Country</label>
                        <input type="text" name="country">
                    </div>
                    <div>
                        <label>State</label>
                        <input type="text" name="state">
                    </div>
                    <div>
                        <label>Zip</label>
                        <input type="text" name="zip">
                    </div>
                </div>

                <label>
                    <input type="checkbox" name="same_address">
                    Shipping address is the same as my billing address
                </label>
                <label>
                    <input type="checkbox" name="save_info">
                    Save this information for next time
                </label>
            </div>

            <!-- RIGHT: Payment Options -->
            <div class="right">
                <h2>Select A Payment Option</h2>

                <label class="pay-option">
                    <input type="radio" name="payment" value="visa">
                    <img src="img/visa.png" alt="Visa">
                </label>

                <label class="pay-option">
                    <input type="radio" name="payment" value="mastercard">
                    <img src="img/mastercard.png" alt="MasterCard">
                </label>

                <label class="pay-option">
                    <input type="radio" name="payment" value="paypal" checked>
                    <img src="img/paypal.png" alt="PayPal">
                </label>

                <label class="pay-option">
                    <input type="radio" name="payment" value="amex">
                    <img src="img/amex.png" alt="American Express">
                </label>

                <label class="pay-option">
                    <input type="radio" name="payment" value="alipay">
                    <img src="img/alipay.png" alt="Alipay">
                </label>

                <label class="pay-option">
                    <input type="radio" name="payment" value="googlepay">
                    <img src="img/googlepay.png" alt="Google Pay">
                </label>

                <label class="pay-option">
                    <input type="radio" name="payment" value="applepay">
                    <img src="img/applepay.png" alt="Apple Pay">
                </label>

                <br>
                <button type="submit">Continue to checkout</button>
            </div>

        </div>
    </form>
</body>
</html>