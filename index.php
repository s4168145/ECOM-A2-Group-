<?php
//main page
// Include configuration file
include_once "config.php";
// Include database file
include_once "data.php";
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Payment Gateway</title>
</head>
<body>
	<h1>Item Catalogue</h1>

	<table>
		<?php foreach ($item as $i) { ?>
			<tr>
	            <th><?php echo $i["name"]; ?></th>
	            <th>
	                <img width="150" height="150" src="<?php echo $i["image"]; ?>" alt="<?php echo $i["name"]; ?>">
	            </th>
	            <th>
                	This is a good laptop
                </th>
                <th> 
                        Price: $<?php echo $i["price"] . PAYPAL_CURRENCY; ?>
                </th>
                <th> 
                    <a href="checkout.php?id=<?php echo $i["id"]; ?>">
                        <img src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif" border="0" alt="Buy Now">
                    </a>
                </th>
	          </tr>
		<?php } ?>
	</table>
	
</body>
</html>