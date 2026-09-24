<?php 
// Product data - CHANGED from laptops to Alice's bikes.
// The server uses these prices to calculate the total, so a user
// can't lower the price by editing localStorage in the browser.
// ids MUST match the ids used in addToCart() in index.html
$item = array(
        array('id' => 'EB1', 'name' => 'Bronton Electric Bike', 'price' => 3000),
        array('id' => 'EB2', 'name' => 'E-BMX Electric Bike',   'price' => 2000),
        array('id' => 'EB3', 'name' => 'F-65 Electric Bike',    'price' => 700),
        );
?> 