<?php
declare(strict_types=1);

namespace MACLookup\Exceptions;

/**
 * Exception when an HTTP client error is encountered. Code 4xx except 401 and 429
 * @package MACLookup\Exceptions
 */
class ClientException extends \Exception
{
    public static function getInstance(string $msg): self
    {
        return new self($msg, 400, null);
    }
}
