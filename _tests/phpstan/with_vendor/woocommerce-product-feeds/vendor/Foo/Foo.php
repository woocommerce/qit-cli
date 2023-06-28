<?php

namespace SomeVendor;

class Foo {
	public function get_foo() {
		return 'foo';
	}
}

$baz = new PHPStanShouldNotFlagThis();