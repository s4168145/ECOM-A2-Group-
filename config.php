<?php 
//global variable configuration
/*
* PayPal configuration
*/
// PayPal configuration
define('PAYPAL_ID', 'sb-z67eg53022815@business.example.com'); //seller email
define('PAYPAL_SANDBOX', TRUE); //TRUE or FALSE
//redirect page
define('PAYPAL_RETURN_URL', 'http://localhost/ECOM-A2-Group-/success.php');
define('PAYPAL_CANCEL_URL', 'http://localhost/ECOM-A2-Group-/Billing%20Page.html');
define('PAYPAL_NOTIFY_URL', 'http://127.0.0.1/ECOM-A2-Group-/ipn.php');
 
//define currency
define('PAYPAL_CURRENCY', 'AUD');

// Change not required
define('PAYPAL_URL', (PAYPAL_SANDBOX == true)? "https://www.sandbox.paypal.com/cgi-bin/webscr": "https://www.paypal.com/cgi-bin/webscr");
?>