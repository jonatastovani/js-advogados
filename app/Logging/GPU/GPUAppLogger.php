<?php

namespace App\Logging\GPU;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;

class GPUAppLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $name = 'gpu_app_log';
        $logger = new Logger($name);

        $logFilePath = storage_path("logs/$name.log");

        // Adicione um manipulador de arquivo rotativo que gera um novo arquivo por ano
        // $logger->pushHandler(new RotatingFileHandler($logFilePath, 365, Level::Debug, true, null, false, 'Y'));
        
        // Adicione um manipulador de arquivo rotativo que gera um novo arquivo por mês
        $logger->pushHandler(new RotatingFileHandler($logFilePath, 30, Level::Debug, true, null, true, 'Y-m'));

        return $logger;
    }
}
