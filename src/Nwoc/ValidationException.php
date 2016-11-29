<?php
namespace Nwoc;

/**
* target: validation was performed against this object.
* errors: array of errors in corresponding fields.
*/
class ValidationException extends \Exception {
    public $target;
    public $errors;

    public function __construct($target, $errors) {
        $this->target = $target;
        $this->errors = $errors;
    }
}
