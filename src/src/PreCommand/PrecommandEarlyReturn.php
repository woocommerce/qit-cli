<?php
namespace QIT_CLI\PreCommand;

/**
 * Exception thrown when QIT_SELF_TEST=precommand is set to enable early return
 * with configuration data for testing purposes.
 */
class PrecommandEarlyReturn extends \Exception {
	// Simple exception class - the JSON data is passed in the message
}
