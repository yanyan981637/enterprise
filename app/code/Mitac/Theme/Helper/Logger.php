<?php
namespace Mitac\Theme\Helper;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger as MonologLogger;
use Psr\Log\LoggerInterface;
use Stringable;
use Magento\Framework\Filesystem\Driver\File;
class Logger extends Base implements LoggerInterface
{
    protected $filepath = '/var/log/';
    private MonologLogger $logger;

    public function __construct(
        string $fileName = 'custom.log'
    ) {
        $this->logger = new MonologLogger($fileName);
        parent::__construct(new File(), BP . '/var/log/', $fileName);
        
        // 設置日期格式
        $dateFormat = "Y-m-d H:i:s";
        $formatter = new \Monolog\Formatter\LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            $dateFormat
        );
        $this->setFormatter($formatter);
        
        $this->logger->pushHandler($this);
    }

    // 實現 LoggerInterface 的必要方法
    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->log(MonologLogger::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}

