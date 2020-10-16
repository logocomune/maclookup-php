<?php
declare(strict_types=1);

namespace MACLookup\Model;

use DateTimeImmutable;

class RateLimitModel
{

    private $limit;

    private $remainig;

    private $reset;

    /**
     * RateLimitModel constructor.
     * @param $limit
     * @param $remainig
     * @param $reset
     */


    public function __construct(int $limit, int $remainig, DateTimeImmutable $reset)
    {
        $this->limit = $limit;

        $this->remainig = $remainig;

        $this->reset = $reset;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getRemaining(): int
    {
        return $this->remainig;
    }

    public function getReset(): DateTimeImmutable
    {
        return $this->reset;
    }
}
