<?php
declare(strict_types=1);

namespace MACLookup\Model;

class ResponseMACInfo
{

    private $responseTime;

    private $rateLimit;

    private $macInfo;

    public function __construct(MACInfoModel $macInfo, RateLimitModel $rateLimit, float $responseTime)
    {

        $this->macInfo = $macInfo;

        $this->rateLimit = $rateLimit;

        $this->responseTime = $responseTime;
    }


    /**
     * Get the value of responseTime
     */
    public function getResponseTime(): float
    {
        return $this->responseTime;
    }

    /**
     * Get the value of rateLimit
     */
    public function getRateLimit(): RateLimitModel
    {
        return $this->rateLimit;
    }

    /**
     * Get the value of macInfo
     */
    public function getMacInfo(): MACInfoModel
    {
        return $this->macInfo;
    }
}
