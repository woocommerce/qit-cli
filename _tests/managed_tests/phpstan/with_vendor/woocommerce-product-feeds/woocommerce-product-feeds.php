<?php

/*
 * Plugin name: PHPStan - Vendor Analysed but Excluded from Analysis.
 */

require __DIR__ . '/vendor/Foo/Foo.php';
require __DIR__ . '/vendor-prefixed/Bar/Bar.php';
require __DIR__ . '/not-vendor/src/Baz.php';

$foo = new SomeVendor\Foo();
$foo->get_foo();

$bar = new SomePrefixedVendor\Bar();
$bar->get_bar();

$baz = new NotAVendor\Baz();
$baz->get_baz();

$qux = new SomeUnexistingClassThatPHPStanShouldFlag();