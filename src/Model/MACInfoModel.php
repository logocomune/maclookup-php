<?php
declare(strict_types=1);

namespace MACLookup\Model;

class MACInfoModel
{

    private $success = false;

    private $found = false;

    private $macPrefix;

    private $company;

    private $address;

    private $country;

    private $blockStart;

    private $blockEnd;

    private $blockSize;

    private $blockType;

    private $updated;

    private $isRand = false;

    private $isPrivate = false;

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     */
    public function setSuccess(bool $success)
    {
        $this->success = $success;
    }

    /**
     * @return bool
     */
    public function isFound(): bool
    {
        return $this->found;
    }

    /**
     * @param bool $found
     */
    public function setFound(bool $found)
    {
        $this->found = $found;
    }

    /**
     * @return mixed
     */
    public function getMacPrefix()
    {
        return $this->macPrefix;
    }

    /**
     * @param mixed $macPrefix
     */
    public function setMacPrefix($macPrefix)
    {
        $this->macPrefix = $macPrefix;
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param mixed $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getBlockStart()
    {
        return $this->blockStart;
    }

    /**
     * @param mixed $blockStart
     */
    public function setBlockStart($blockStart)
    {
        $this->blockStart = $blockStart;
    }

    /**
     * @return mixed
     */
    public function getBlockEnd()
    {
        return $this->blockEnd;
    }

    /**
     * @param mixed $blockEnd
     */
    public function setBlockEnd($blockEnd)
    {
        $this->blockEnd = $blockEnd;
    }

    /**
     * @return mixed
     */
    public function getBlockSize()
    {
        return $this->blockSize;
    }

    /**
     * @param mixed $blockSize
     */
    public function setBlockSize($blockSize)
    {
        $this->blockSize = $blockSize;
    }

    /**
     * @return mixed
     */
    public function getBlockType()
    {
        return $this->blockType;
    }

    /**
     * @param mixed $blockType
     */
    public function setBlockType($blockType)
    {
        $this->blockType = $blockType;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param mixed $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * @return bool
     */
    public function isRand(): bool
    {
        return $this->isRand;
    }

    /**
     * @param bool $isRand
     */
    public function setIsRand(bool $isRand)
    {
        $this->isRand = $isRand;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->isPrivate;
    }

    /**
     * @param bool $isPrivate
     */
    public function setIsPrivate(bool $isPrivate)
    {
        $this->isPrivate = $isPrivate;
    }
}
