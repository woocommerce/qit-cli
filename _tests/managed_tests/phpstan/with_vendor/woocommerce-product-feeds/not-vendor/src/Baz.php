<?php

namespace NotAVendor;

class Baz {
	public function get_baz() {
		return 'baz';
	}
}

$baz = new SomeOtherUnexistingClassThatPHPStanShouldFlag();