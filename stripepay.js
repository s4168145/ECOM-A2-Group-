// Setting up my Stripe account connection using my publishable key
// (this key is safe to put here - it can't actually charge anyone on its own,
// that would need my SECRET key on a server, which I don't have for this demo)
const stripe = Stripe('pk_test_51UHwQlAXpfKljQmHQQIOPok2pgBkxX3WbUSQfQ4uZ3YpIpQsKrs3eoePul3ZOHBIMjNBmTi2LBzjQRFrrwDyZYHF00S02c7ETR'); // publishable key

// This creates the "Elements" object - basically Stripe's toolkit for
// building secure payment forms that I can add to my page
const elements = stripe.elements();

// Making the card input box look nicer instead of using Stripe's plain default styling
// This is optional - just changing font, colour, and how errors are shown
const style = {
  base: {
    color: "#32325d",
    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
    fontSmoothing: "antialiased",
    fontSize: "16px",
    "::placeholder": {
      color: "#aab7c4"
    }
  },
  invalid: {
    color: "#fa755a", // turns red if the card number/date/cvc is invalid
    iconColor: "#fa755a"
  }
};

// This actually builds the card number/expiry/CVC input field
// I never see or touch the real card data myself - Stripe handles all of that
// inside a secure iframe, which is the whole point of using their Elements
const cardElement = elements.create("card", { style: style, disableLink: true, hidePostalCode: true });

// Using this flag so I don't accidentally try to mount the card field twice
// if someone clicks the Visa option more than once
let cardMounted = false;

// This function actually inserts the card field into my page,
// but only into the #card-element div, and only the first time
function mountStripeCard() {
  if (!cardMounted) {
    cardElement.mount("#card-element");
    cardMounted = true;
  }
}

// This listens for Stripe telling me if what the customer typed is invalid
// (like an incomplete card number) and shows the error message live,
// as they're typing, instead of only after they hit submit
cardElement.on("change", function (event) {
  const displayError = document.getElementById("card-errors");
  if (event.error) {
    displayError.textContent = event.error.message;
  } else {
    displayError.textContent = "";
  }
});

// Grabbing my Visa payment form so I can react when it's submitted
const form = document.getElementById("visa-payment-form");
if (form) {
  form.addEventListener("submit", function (event) {
    event.preventDefault();

    // Sending the card details straight to Stripe (not my own server)
    // to get back a safe "token" (paymentMethod) that represents the card
    stripe.createPaymentMethod({
      type: "card",
      card: cardElement
    }).then(stripePaymentMethodHandler);
  });
}

// This runs once Stripe responds back with either an error or a successful token
function stripePaymentMethodHandler(result) {
  if (result.error) { // Something was wrong with the card (e.g. Stripe rejected the test card)
    const errorElement = document.getElementById("card-errors");
    errorElement.textContent = result.error.message;
  } else { // Success! In a real production app, I'd now send result.paymentMethod.id
    // to my backend server to actually process the charge - but since this is
    // just a test-mode student demo with no backend, I'm just confirming it
    // worked by logging it and showing an alert
    const totalValue = localStorage.getItem('cartTotal') || "0.00";
    console.log("Received Stripe PaymentMethod:", result.paymentMethod);
    alert("Visa payment validated successfully (test mode)! Amount: $" + totalValue);
  }
}