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

/**
 *=================================================*
 * 3. Square Sandbox Configuration
 *=================================================*
 */

/**
 * Square Sandbox Application ID.
 * This value can be used by the browser.
 */
define(
    'SQUARE_APPLICATION_ID',
    'sandbox-sq0idb-UsQmAiHAtHB5FQl-6GABgA'
);

/**
 * Square Sandbox Location ID.
 */
define(
    'SQUARE_LOCATION_ID',
    'LXGGHH6JWJ91H'
);

/**
 * Square Sandbox Access Token.
 *
 * IMPORTANT:
 * Never put this value in JavaScript or HTML.
 * Replace this with your NEW rotated token.
 */
define(
    'SQUARE_ACCESS_TOKEN',
    'EAAAl8VmnFTL2lNDIoCW8s6sxDSA5x85sScwYOxn9NYNdAe2AtgkW6V8ybN6US4o'
);

/**
 * Square Sandbox API endpoint.
 */
define(
    'SQUARE_API_URL',
    'https://connect.squareupsandbox.com/v2/payments'
);

/**
 * Square API version.
 */
define(
    'SQUARE_API_VERSION',
    '2026-09-16'
);

?>