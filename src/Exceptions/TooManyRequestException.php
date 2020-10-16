<?php
declare(strict_types=1);

namespace MACLookup\Exceptions;

use Exception;
use MACLookup\Model\RateLimitModel;

/**
 *  Exception when rate limit is reached. HTTP code 429
 * @package MACLookup\Exceptions
 */
class TooManyRequestException extends \Exception
{
    private $rateLimit;

    public static function getInstance(RateLimitModel $rateLimitModel): self
    {

        $accessDeniedException = new self("Too many request", 429, null);

        $accessDeniedException->rateLimit = $rateLimitModel;

        return $accessDeniedException;
    }

    /**
     * @return RateLimitModel
     */
    public function getRateLimit(): RateLimitModel
    {
        return $this->rateLimit;
    }
}
