<?php

/*
 * Plugin name: PHPCompatibility - Supports 7.2 and above.
 */

// Should flag for PHP < 7.0
$anonymousClass = new class {};

// Should flag for PHP < 7.1
function voidReturnType(): void {}

// Should flag for PHP < 7.2
$object = new class { public const A = 'a'; };
$objectType = $object::A ?? 'default'; // Null coalescing operator with class constant

// Should flag for PHP < 7.3
$array = ['a' => 'Apple', 'b' => 'Banana'];
extract($array, EXTR_SKIP | EXTR_REFS); // Multiple flags with extract()

// Should flag for PHP < 7.4
$arrowFunction = fn($x) => $x + 1;

// Should flag for PHP < 8.0
str_contains('hello world', 'world');

// Should flag for PHP < 8.1
class Bar {
	readonly string $foo;
}

// Should flag for PHP < 8.2
readonly class Foo {

}