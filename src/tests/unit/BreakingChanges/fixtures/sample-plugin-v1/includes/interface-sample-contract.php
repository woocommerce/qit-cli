<?php

namespace SamplePlugin;

interface SampleContract {
	public function execute(): void;
	public function get_name(): string;
}
