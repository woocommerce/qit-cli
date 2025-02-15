<?php
/**
 * Plugin Name: PHPStan - Google Listings and Ads Container Test
 *
 * Description: A self-test scenario to confirm that PHPStan properly recognizes
 *              classes and methods from the Google Listings and Ads plugin.
 */

use Automattic\WooCommerce\GoogleListingsAndAds\Container;
use Automattic\WooCommerce\GoogleListingsAndAds\Vendor\Psr\Container\ContainerInterface;

// Instantiate the Container class directly (assuming it's already loaded by WordPress).
$gla_container = new Container();

// Use the PSR-11 `get()` method to fetch a service:
$resolved_service = $gla_container->get( ContainerInterface::class );

$has = $gla_container->has( 'foo' ); // OK.
$has = $gla_container->has( static function() {} ); // Flagged.

// Call a container method that does NOT exist to ensure PHPStan flags it:
$gla_container->someNonExistentMethod(); // This should trigger an "undefined method" error in PHPStan.


call_to_undefined_funtion();