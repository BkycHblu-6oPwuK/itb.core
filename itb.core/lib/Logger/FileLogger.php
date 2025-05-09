<?php

namespace Itb\Core\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class FileLogger implements LoggerInterface
{
    protected string $logFile;

    /**
     * @param string $logFile path to log file
     */
    public function __construct(string $logFile)
    {
        $this->logFile = $logFile;
    }

    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * @param string $level in Psr\Log\LogLevel
     */
    public function log($level, $message, array $context = []): void
    {
        $date = date('Y-m-d H:i:s');
        $message = $this->interpolate($message, $context);
        $contextString = !empty($context) ? print_r($context, true) : '';

        file_put_contents(
            $this->logFile,
            "[$date] $level: $message " . ($contextString ? "\nContext: " . $contextString : '') . PHP_EOL,
            FILE_APPEND
        );
    }

    protected function interpolate(string $message, array $context = []): string
    {
        foreach ($context as $key => $value) {
            if (!is_scalar($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
            $message = str_replace("{" . $key . "}", (string) $value, $message);
        }
        return $message;
    }
}
