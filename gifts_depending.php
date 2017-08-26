<?php
/*
Plugin Name: WooCommerce - Gifts Depending Purchase
Plugin URI: http://cesar.pe
Description: Select gifts for your orders in WooCommerce
Version: 1.0
Author: César Aquino Maximiliano
Author URI: http://cesar.pe/
Text Domain: gifts_depending_purchase
*/

require_once dirname(__FILE__).'/gifts_depending.class.php';
$gifts_depending_purchase = new GiftsDependingPurchase();

require_once dirname(__FILE__).'/gifts_depending_shortcode.php';

?>
