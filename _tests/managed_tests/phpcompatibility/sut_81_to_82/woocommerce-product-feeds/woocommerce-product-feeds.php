<?php

/*
 * Plugin name: PHPCompatibility
 * Description: Should work in PHP 8.1 and PHP 8.2.
 */

// Requires PHP 7.0.
$anonymousClass = new class {};

// Requires PHP 7.1.
function voidReturnType(): void {}

// Requires PHP 7.2.
$object = new class { public const A = 'a'; };
$objectType = $object::A ?? 'default'; // Null coalescing operator with class constant

// Requires PHP 7.3.
$result = my_function(1, 2, 3,);  // Trailing comma in function call

// Requires PHP 7.4.
$arrowFunction = fn($x) => $x + 1;

// Requries PHP 8.0.
str_contains('hello world', 'world');

// Requires PHP 8.1.
class Bar {
	readonly string $foo;
}

// Requires PHP 8.2 (should be flagged since this plugin supports PHP 8.1).
readonly class Foo {

}

// Deprecated in PHP 7.2
// The create_function() function is deprecated as of PHP 7.2.
$newfunc = create_function('$a', 'return $a * 2;');

// Deprecated in PHP 7.2
// The __autoload function is deprecated in favor of spl_autoload_register.
function __autoload($class) {
	include 'classes/' . $class . '.class.php';
}

// Deprecated in PHP 7.3
// Deprecated flags for filter_var with FILTER_VALIDATE_URL.
filter_var('http://example.com', FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED);

// Deprecated in PHP 7.3
// Using implode() with the parameters in a non-canonical order is deprecated.
implode($pieces, '');

// Deprecated in PHP 7.4
// Using curly braces for array and string offset access is deprecated.
$array = [1, 2, 3];
$element = $array{0};

// Deprecated in PHP 7.4
// This function is deprecated.
$magic_quotes = get_magic_quotes_gpc();

// PHP 8.0 Deprecations
// Deprecated: Required parameters after optional parameters
function exampleFunction($a = [], $b) {}

// Deprecated: ReflectionParameter methods
function deprecatedReflectionMethods(ReflectionParameter $param) {
	$param->getClass();
}

// PHP 8.1 Deprecations
// Deprecated: Passing null to non-nullable internal function parameters
strlen(null);

// Deprecated: Serializable interface
class DeprecatedSerializable implements Serializable {
	public function serialize() {}
	public function unserialize($serialized) {}
}

// PHP 8.2 Deprecations
// Deprecated: Dynamic Properties
class ExampleClass {}
$example = new ExampleClass;
$example->dynamicProperty = 'Deprecated';

// Deprecated: utf8_encode and utf8_decode functions
utf8_encode('Deprecated');

// Requires PHP 8.3
// Test: JSON validation
assert(json_validate('{ "key": "value" }'));

// Deprecated in PHP 8.3: Using get_class() without arguments
// Deprecated in PHP 8.3: Using get_parent_class() without arguments
class TestClass {
	public function someMethod() {
		$className = get_class();
	}

	public function anotherMethod() {
		$parentClassName = get_parent_class();
	}
}
