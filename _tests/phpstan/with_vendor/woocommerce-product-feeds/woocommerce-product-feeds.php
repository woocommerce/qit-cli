<?php

/*
 * Plugin name: PHPStan - Vendor Analysed but Excluded from Analysis.
 */

require __DIR__ . '/vendor/Foo/Foo.php';
require __DIR__ . '/vendor-prefixed/Bar/Bar.php';

$foo = new SomeVendor\Foo();
$bar = new SomePrefixedVendor\Bar();
$baz = new SomeUnexistingClassThatPHPStanShouldFlag();