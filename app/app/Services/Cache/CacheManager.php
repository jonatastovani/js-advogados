<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Redis;
use Illuminate\Redis\Connections\Connection;

class CacheManager
{
    protected Connection $redis;

    /**
     * CacheManager constructor.
     * Conecta-se ao Redis na conexão padrão ou a uma conexão personalizada.
     *
     * @param string|null $connection Nome da conexão Redis
     */
    public function __construct(string $connection = null)
    {
        // Obter a conexão Redis
        $this->redis = Redis::connection($connection);
    }

    /**
     * Obtém o valor de uma chave no Redis.
     *
     * @param string $key A chave a ser buscada
     * @return mixed O valor associado à chave, ou null se não existir
     */
    public function get(string $key)
    {
        return $this->redis->get($key);
    }

    /**
     * Define um valor no Redis com uma chave e expiração opcional.
     *
     * @param string $key A chave onde o valor será armazenado
     * @param mixed $value O valor a ser armazenado
     * @param int $expiration Expiração em segundos (padrão 3600 segundos)
     * @return bool Se o valor foi armazenado com sucesso
     */
    public function set(string $key, $value, int $expiration = 3600): bool
    {
        return $this->redis->setex($key, $expiration, $value);
    }

    /**
     * Deleta uma chave do Redis.
     *
     * @param string $key A chave a ser deletada
     * @return int O número de chaves deletadas
     */
    public function delete(string $key): int
    {
        return $this->redis->del($key);
    }

    /**
     * Incrementa o valor de uma chave no Redis.
     *
     * @param string $key A chave cujo valor será incrementado
     * @param int $amount O valor pelo qual incrementar (padrão: 1)
     * @return int O valor após o incremento
     */
    public function increment(string $key, int $amount = 1): int
    {
        return $this->redis->incrby($key, $amount);
    }

    /**
     * Decrementa o valor de uma chave no Redis.
     *
     * @param string $key A chave cujo valor será decrementado
     * @param int $amount O valor pelo qual decrementar (padrão: 1)
     * @return int O valor após o decremento
     */
    public function decrement(string $key, int $amount = 1): int
    {
        return $this->redis->decrby($key, $amount);
    }

    /**
     * Verifica se uma chave existe no Redis.
     *
     * @param string $key A chave a ser verificada
     * @return bool Se a chave existe
     */
    public function exists(string $key): bool
    {
        return $this->redis->exists($key);
    }

    /**
     * Define múltiplas chaves/valores no Redis de uma vez.
     *
     * @param array $data Um array associativo de chave => valor
     * @return bool Se as chaves/valores foram armazenadas com sucesso
     */
    public function setMultiple(array $data): bool
    {
        return $this->redis->mset($data);
    }

    /**
     * Obtém múltiplos valores de uma vez, com base em um array de chaves.
     *
     * @param array $keys Um array de chaves a serem buscadas
     * @return array Um array associativo de chave => valor
     */
    public function getMultiple(array $keys): array
    {
        return $this->redis->mget($keys);
    }

    /**
     * Remove todas as chaves e valores de todas as bases de dados do Redis.
     *
     * @return bool Se o flush foi bem-sucedido
     */
    public function flushAll(): bool
    {
        return $this->redis->flushall();
    }

    /**
     * Remove todas as chaves e valores da base de dados atual do Redis.
     *
     * @return bool Se o flush foi bem-sucedido
     */
    public function flushDb(): bool
    {
        return $this->redis->flushdb();
    }

    /**
     * Define um tempo de expiração para uma chave específica no Redis.
     *
     * @param string $key A chave a expirar
     * @param int $seconds O tempo de expiração em segundos
     * @return bool Se a expiração foi definida com sucesso
     */
    public function expire(string $key, int $seconds): bool
    {
        return $this->redis->expire($key, $seconds);
    }
}
