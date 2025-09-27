<?php
namespace Itb\Core\Logger;

use Psr\Log\LoggerInterface;

interface LoggerFactoryInterface
{
    public function channel(string $name): LoggerInterface;
}
