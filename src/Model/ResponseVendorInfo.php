<?php
declare(strict_types=1);

namespace MACLookup\Model;

class ResponseVendorInfo
{
    private $responseTime;

    private $rateLimit;

    private $vendorInfo;

    public function __construct(VendorInfoModel $vendorInfo, RateLimitModel $rateLimit, float $responseTime)
    {

        $this->vendorInfo = $vendorInfo;

        $this->rateLimit = $rateLimit;

        $this->responseTime = $responseTime;
    }

    /**
     * @return float
     */
    public function getResponseTime(): float
    {
        return $this->responseTime;
    }

    /**
     * @param float $responseTime
     */
    public function setResponseTime(float $responseTime)
    {
        $this->responseTime = $responseTime;
    }

    /**
     * @return RateLimitModel
     */
    public function getRateLimit(): RateLimitModel
    {
        return $this->rateLimit;
    }

    /**
     * @param RateLimitModel $rateLimit
     */
    public function setRateLimit(RateLimitModel $rateLimit)
    {
        $this->rateLimit = $rateLimit;
    }

    /**
     * @return VendorInfoModel
     */
    public function getVendorInfo(): VendorInfoModel
    {
        return $this->vendorInfo;
    }

    /**
     * @param VendorInfoModel $vendorInfo
     */
    public function setVendorInfo(VendorInfoModel $vendorInfo)
    {
        $this->vendorInfo = $vendorInfo;
    }
}
