<?php
session_start();   // so success.php can show the customer's name
// Include configuration file
include_once "config.php";
// Include database file
include_once "data.php";

// make sure the billing details were filled in
$required = array('first_name', 'last_name', 'username', 'address', 'country', 'zip');
foreach ($required as $field) {
	if (empty($_POST[$field])) {
		header('Location: Billing%20Page%201.html?error=missing');
		exit;
	}
}

// work out the total using the prices in data.php
$cart = json_decode($_POST['cart'], true);
$total = 0;
if (!empty($cart)) {
	foreach ($cart as $line) {
		foreach ($item as $product) {
			if ($product['id'] === $line['id'] && (int)$line['qty'] > 0) {
				$total += $product['price'] * (int)$line['qty'];
				break;
			}
		}
	}
}
if ($total <= 0) {
	header('Location: Billing%20Page%201.html?error=cart');
	exit;
}

// save the customer's name for success.php
$_SESSION['name'] = $_POST['first_name'] . ' ' . $_POST['last_name'];
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Payment Gateway</title>
</head>
<body>
	<h1>Confirm Your Order</h1>
	<table>
		<tr>
			<th>Alice's Electronic Bike Shop Order</th>
			<th>
				Total: $<?php echo $total . PAYPAL_CURRENCY; ?>
			</th>
			<th>
<!-- define paypal button and send data -->
				<form action="<?php echo PAYPAL_URL; ?>" method="post" style="padding: 0; margin: 0;">

				<!-- Specify a Buy Now button. -->
					<input type="hidden" name="cmd" value="_xclick" />

				<!-- Identify your business so that you can collect the payments. -->
					<input type="hidden" name="business" value="<?php echo PAYPAL_ID; ?>" />

				<!-- Specify details about the item that buyers will purchase. CHANGED: whole cart -->
					<input type="hidden" name="item_name" value="Alice's Electronic Bike Shop Order" />
					<input type="hidden" name="item_number" value="CART" />
					<input type="hidden" name="amount" value="<?php echo $total; ?>" />
					<input type="hidden" name="currency_code" value="<?php echo PAYPAL_CURRENCY; ?>" />

				<!-- Specify URLs -->
					<input type="hidden" name="return" value="<?php echo PAYPAL_RETURN_URL; ?>">
					<input type="hidden" name="cancel_return" value="<?php echo PAYPAL_CANCEL_URL; ?>">
					<input type="hidden" name="notify_url" value="<?php echo PAYPAL_NOTIFY_URL; ?>">
					<input type="image" border="0" name="submit" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif"/>
				</form>
			</th>
		</tr>
	</table>

</body>
</html>