/** 
 * Define the version of the Google Pay API referenced when creating your
 * configuration
 *
 * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#PaymentDataRequest|apiVersion in PaymentDataRequest}
 */
//1. Define your Google Pay API version
const baseRequest = {
  apiVersion: 2,
  apiVersionMinor: 0
};

/** 
 * Identify your gateway and your site's gateway merchant identifier
 *
 * The Google Pay API response will return an encrypted payment method capable
 * of being charged by a supported gateway after payer authorization
 *
 * @todo check with your gateway on the parameters to pass
 * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#gateway|PaymentMethodTokenizationSpecification}
 */
//2. Request a payment token for your payment provider
const tokenizationSpecification = {
  type: 'PAYMENT_GATEWAY',
  parameters: {
    'gateway': 'example',
    'gatewayMerchantId': 'exampleGatewayMerchantId'
  }
};

/**
* Card networks supported by your site and your gateway
*
* @see {@link https://developers.google.com/pay/api/web/reference/request-objects#CardParameters|CardParameters}
* @todo confirm card networks supported by your site and gateway
*/
//3.1 Define supported payment card networks
const allowedCardNetworks = ["AMEX", "DISCOVER", "INTERAC", "JCB", "MASTERCARD", "VISA"];

/**
* Card authentication methods supported by your site and your gateway
*
* @see {@link https://developers.google.com/pay/api/web/reference/request-objects#CardParameters|CardParameters}
* @todo confirm your processor supports Android device tokens for your
* supported card networks
*/
//3.2
const allowedCardAuthMethods = ["PAN_ONLY", "CRYPTOGRAM_3DS"];
// const allowedCardAuthMethods = ["PAN_ONLY"];

/**
 * Describe your site's support for the CARD payment method and its required
 * fields
 *
 * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#CardParameters|CardParameters}
 */
//4.1 Describe your allowed payment methods
const baseCardPaymentMethod = {
  type: 'CARD',
  parameters: {
    allowedAuthMethods: allowedCardAuthMethods,
    allowedCardNetworks: allowedCardNetworks
  }
};

/**
 * Describe your site's support for the CARD payment method including optional
 * fields
 *
 * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#CardParameters|CardParameters}
 */
//4.2 Describe your allowed payment methods
const cardPaymentMethod = Object.assign(
  {tokenizationSpecification: tokenizationSpecification},
  baseCardPaymentMethod
);

/**
* An initialized google.payments.api.PaymentsClient object or null if not yet set
*
* @see {@link getGooglePaymentsClient}
*/
//5.2 Load the Google Pay API JavaScript library
let paymentsClient = null;

/**
* Return an active PaymentsClient or initialize
*
* @see {@link https://developers.google.com/pay/api/web/reference/client#PaymentsClient|PaymentsClient constructor}
* @returns {google.payments.api.PaymentsClient} Google Pay API client
*/
//5.2 Load the Google Pay API JavaScript library
function getGooglePaymentsClient() {
  if ( paymentsClient === null ) {
    paymentsClient = new google.payments.api.PaymentsClient({
      environment: 'TEST',
      //10.1
      paymentDataCallbacks: {
        onPaymentAuthorized: onPaymentAuthorized
      }
    });
  }
  return paymentsClient;
}

/**
* Configure your site's support for payment methods supported by the Google Pay
* API.
*
* Each member of allowedPaymentMethods should contain only the required fields,
* allowing reuse of this base request when determining a viewer's ability
* to pay and later requesting a supported payment method
*
* @returns {object} Google Pay API version, payment methods supported by the site
*/
//6.1 Determine readiness to pay with the Google Pay API
function getGoogleIsReadyToPayRequest() {
  return Object.assign(
    {},
    baseRequest,
    {
      allowedPaymentMethods: [baseCardPaymentMethod]
    }
  );
}

/**
 * Initialize Google PaymentsClient after Google-hosted JavaScript has loaded
 *
 * Display a Google Pay payment button after confirmation of the viewer's
 * ability to pay.
 */
//6.2 Determine readiness to pay with the Google Pay API
function onGooglePayLoaded() {
  const paymentsClient = getGooglePaymentsClient();
  paymentsClient.isReadyToPay(getGoogleIsReadyToPayRequest())
      .then(function(response) {
        if (response.result) {
          addGooglePayButton();
          // @todo prefetch payment data to improve performance after confirming site functionality
          // prefetchGooglePaymentData();
        }
      })
      .catch(function(err) {
        // show error in developer console for debugging
        console.error(err);
      });
}

/**
 * Add a Google Pay purchase button alongside an existing checkout button
 *
 * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#ButtonOptions|Button options}
 * @see {@link https://developers.google.com/pay/api/web/guides/brand-guidelines|Google Pay brand guidelines}
 */
//7. Add a Google Pay payment button
function addGooglePayButton() {
  const paymentsClient = getGooglePaymentsClient();
  const button =
      paymentsClient.createButton({
        onClick: onGooglePaymentButtonClicked,
        allowedPaymentMethods: [baseCardPaymentMethod]
      });
  document.getElementById('google-pay-container').appendChild(button);
}

/**
* Configure support for the Google Pay API
*
* @see {@link https://developers.google.com/pay/api/web/reference/request-objects#PaymentDataRequest|PaymentDataRequest}
* @returns {object} PaymentDataRequest fields
*/
//8. Create a PaymentDataRequest object
function getGooglePaymentDataRequest() {
  //8.1 
  const paymentDataRequest = Object.assign({}, baseRequest);
  //8.2 
  paymentDataRequest.allowedPaymentMethods = [cardPaymentMethod];
  //8.3 Part 1
  paymentDataRequest.transactionInfo = getGoogleTransactionInfo();
  //8.4 
  paymentDataRequest.merchantInfo = {
    // @todo a merchant ID is available for a production environment after approval by Google
    // See {@link https://developers.google.com/pay/api/web/guides/test-and-deploy/integration-checklist|Integration checklist}
    // merchantId: '12345678901234567890',
    merchantName: '12345678901234567890'
  };
  //10.2
  paymentDataRequest.callbackIntents = ["PAYMENT_AUTHORIZATION"];
  return paymentDataRequest;
}

/**
 * Provide Google Pay API with a payment amount, currency, and amount status
 *
 * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#TransactionInfo|TransactionInfo}
 * @returns {object} transaction info, suitable for use as transactionInfo property of PaymentDataRequest
 */
//8.3 Part 2
function getGoogleTransactionInfo() {
    const storedTotal = localStorage.getItem('cartTotal') || "0.00";
    return {
    countryCode: 'AU',
    currencyCode: "AUD",
    totalPriceStatus: "FINAL",
    totalPrice: storedTotal,
    totalPriceLabel: "Total"
  };
}

/**
 * Show Google Pay payment sheet when Google Pay payment button is clicked
 */
//9. Register an event handler for user gestures
function onGooglePaymentButtonClicked() {
  if (!billingValid()) return; 
  const paymentDataRequest = getGooglePaymentDataRequest();
  paymentDataRequest.transactionInfo = getGoogleTransactionInfo();

  const paymentsClient = getGooglePaymentsClient();
  paymentsClient.loadPaymentData(paymentDataRequest)
      .then(function(paymentData) {
        // handle the response
        processPayment(paymentData);
      })
      .catch(function(err) {
        // show error in developer console for debugging
        console.error(err);
      });
}

/**
* Handles authorize payments callback intents.
*
* @param {object} paymentData response from Google Pay API after a payer approves payment through user gesture.
* @see {@link https://developers.google.com/pay/api/web/reference/response-objects#PaymentData object reference}
*
* @see {@link https://developers.google.com/pay/api/web/reference/response-objects#PaymentAuthorizationResult}
* @returns Promise<{object}> Promise of PaymentAuthorizationResult object to acknowledge the payment authorization status.
*/
//10.3  Set up Authorize Payments
function onPaymentAuthorized(paymentData) {
  return new Promise(function(resolve, reject){
    // handle the response
    processPayment(paymentData)
    .then(function() {
      resolve({transactionState: 'SUCCESS'});
    })
    .catch(function() {
      resolve({
        transactionState: 'ERROR',
        error: {
          intent: 'PAYMENT_AUTHORIZATION',
          message: 'Insufficient funds',
          reason: 'PAYMENT_DATA_INVALID'
        }
      });
    });
  });
}


/**
* Process payment data returned by the Google Pay API
*
* @param {object} paymentData response from Google Pay API after user approves payment
* @see {@link https://developers.google.com/pay/api/web/reference/response-objects#PaymentData|PaymentData object reference}
*/
//11 Process payment data returned by the API
let attempts = 0;
function processPayment(paymentData) {
  return new Promise(function(resolve, reject) {
        setTimeout(function() {
                // @todo pass payment token to your gateway to process payment
                paymentToken = paymentData.paymentMethodData.tokenizationData.token;

        resolve({});
    }, 3000);
  });
  // return new Promise(function(resolve, reject) {
  //   setTimeout(function() {
  //     // @todo pass payment token to your gateway to process payment
  //     paymentToken = paymentData.paymentMethodData.tokenizationData.token;
      
  //     if (attempts++ % 2 == 0) {
  //       reject(new Error('Every other attempt fails, next one should succeed'));
  //     } else {
  //       resolve({});
  //     }
  //   }, 500);
  // });
}