<?php
//global variable configuration
/*
* PayPal configuration
*/
// PayPal configuration
define('PAYPAL_ID', 'sb-bljqm53012069@business.example.com'); //seller email
define('PAYPAL_SANDBOX', TRUE); //TRUE or FALSE
//redirect page
define('PAYPAL_RETURN_URL', 'http://localhost/tutorial5-paypal/success.php'); 
define('PAYPAL_CANCEL_URL', 'http://localhost/tutorial5-paypal/cancel.php'); 
define('PAYPAL_NOTIFY_URL', 'http://127.0.0.1/tutorial5-paypal/ipn.php');
//define currency
define('PAYPAL_CURRENCY', 'AUD');

// Change not required
define('PAYPAL_URL', (PAYPAL_SANDBOX == true)? "https://www.sandbox.paypal.com/cgi-bin/webscr": "https://www.paypal.com/cgi-bin/webscr");
?>