<?php

namespace SomePrefixedVendor;

class Bar {
	public function get_foo() {
		return 'foo';
	}
}

$baz = new PHPStanShouldNotFlagThis2();