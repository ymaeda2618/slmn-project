<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FilterHandler;

class CustomLevelLogger
{
    public function __invoke(array $config)
    {
        $year  = date('Y');
        $month = date('m');
        $day   = date('d');

        $logPath = storage_path("logs/{$year}/{$month}");
        if (!file_exists($logPath)) {
            mkdir($logPath, 0755, true);
        }

        // ファイル名
        $infoFile  = "{$logPath}/info_log_{$year}{$month}{$day}.log";
        $errorFile = "{$logPath}/error_log_{$year}{$month}{$day}.log";

        // ロガー生成
        $logger = new Logger('custom_level_logger');

        // info, debug, notice, warning → info_log
        $infoHandler = new StreamHandler($infoFile, Logger::DEBUG);
        $filteredInfoHandler = new FilterHandler($infoHandler, Logger::DEBUG, Logger::WARNING, false);
        $logger->pushHandler($filteredInfoHandler);

        // error以上 → error_log
        $errorHandler = new StreamHandler($errorFile, Logger::ERROR);
        $logger->pushHandler($errorHandler);

        return $logger;
    }
}