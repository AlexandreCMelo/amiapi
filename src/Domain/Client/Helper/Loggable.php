<?php
declare(strict_types=1);

namespace Ams\Domain\Client\Helper;

use Psr\Log\LoggerInterface;

trait Loggable{

    /**
     * @var LoggerInterface
     */
    protected $logger;


    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}