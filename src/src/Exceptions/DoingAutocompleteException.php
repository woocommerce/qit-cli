<?php

namespace QIT_CLI\Exceptions;

/**
 * This exception will be thrown when attempting to do
 * a remote request in the context of an autocompletion command.
 */
class DoingAutocompleteException extends \Exception {
}
