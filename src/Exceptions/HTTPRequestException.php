<?php
declare(strict_types=1);

namespace MACLookup\Exceptions;

class HTTPRequestException extends \Exception
{
    public static function getInstance(string $msg): self
    {
        return new self($msg, 100, null);
    }
}
