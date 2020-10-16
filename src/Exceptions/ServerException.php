<?php
declare(strict_types=1);

namespace MACLookup\Exceptions;

/**
 * Exception when an HTTP server error is encountered. Code 5xx
 * @package MACLookup\Exceptions
 */
class ServerException extends \Exception
{
    public static function getInstance(): ServerException
    {
        return new self("API server error", 500, null);
    }
}
