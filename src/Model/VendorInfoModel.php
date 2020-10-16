<?php
declare(strict_types=1);

namespace MACLookup\Model;

class VendorInfoModel
{
    private $found = false;

    private $private = false;

    private $company = "";

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
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @param bool $private
     */
    public function setPrivate(bool $private)
    {
        $this->private = $private;
    }

    /**
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * @param string $company
     */
    public function setCompany(string $company)
    {
        $this->company = $company;
    }
}
