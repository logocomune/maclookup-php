<?php
declare(strict_types=1);

namespace MACLookup\Exceptions;

use Exception;

/**
 * Exception when API Key is not valid. (HTTP error 401)
 * @package MACLookup\Exceptions
 */
class APIKeyException extends Exception
{

    public static function getInstance(): self
    {
        return new self("Bad API Key value", 403, null);
    }
}
