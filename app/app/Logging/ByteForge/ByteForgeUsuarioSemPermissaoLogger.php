<?php

namespace App\Logging\ByteForge;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;

class ByteForgeUsuarioSemPermissaoLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $name = 'byteforge_app_usuario_sem_permissao_log';
        $logger = new Logger($name);

        $logFilePath = storage_path("logs/$name.log");
        // Adicione um manipulador de arquivo rotativo que gera um novo arquivo por mês
        $logger->pushHandler(new RotatingFileHandler($logFilePath, 30, Level::Debug, true, null, true, 'Y-m'));

        return $logger;
    }
}
