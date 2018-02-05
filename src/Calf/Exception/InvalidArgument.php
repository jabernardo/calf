<?php

namespace Calf\Exception;

/**
 * Error 101: Invalid Argument Exception
 * 
 * @version 1.0
 * @author John Aldrich Bernardo <4ldrich@protonmail.com>
 * 
 */
class InvalidArgument extends \Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 101, Exception $previous = null) {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
