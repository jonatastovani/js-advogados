<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Prefix
    |--------------------------------------------------------------------------
    |
    | Prefixo usado em todas as chaves de cache geradas pelo pacote.
    | Útil para evitar colisões de cache quando múltiplos projetos ou 
    | múltiplas instâncias compartilham o mesmo armazenamento (ex: Redis).
    | Se vazio, não haverá prefixo.
    */
    // 'cache-prefix' => '',
    'cache-prefix' => env('MODEL_CACHE_PREFIX', config('sistema.sigla')),

    /*
    |--------------------------------------------------------------------------
    | Habilitar Cache
    |--------------------------------------------------------------------------
    |
    | Controla se o cache de modelos está ativado ou desativado.
    | Pode ser controlado via variável de ambiente MODEL_CACHE_ENABLED,
    | o que facilita ativar/desativar em diferentes ambientes (ex: dev, prod).
    | Valor padrão: true (cache ativado).
    */
    'enabled' => env('MODEL_CACHE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Uso de Database Keying
    |--------------------------------------------------------------------------
    |
    | Quando ativado, adiciona uma chave única ao cache baseada no banco de dados
    | que está em uso na conexão, útil quando você tem múltiplos bancos diferentes
    | usando o mesmo cache (ex: multi-tenant com bancos separados).
    | Essa chave evita que dados de um banco sejam misturados no cache de outro.
    | Valor padrão: true.
    */
    'use-database-keying' => env('MODEL_CACHE_USE_DATABASE_KEYING', false),

    /*
    |--------------------------------------------------------------------------
    | Store de Cache
    |--------------------------------------------------------------------------
    |
    | Permite definir explicitamente qual store de cache o pacote deve usar.
    | Por padrão, usa a store padrão do Laravel (ex: redis, file, etc).
    | Pode ser útil para forçar o uso do Redis, por exemplo, independente da config geral.
    | Se null, usa o cache default do Laravel.
    */
    'store' => env('MODEL_CACHE_STORE', env('CACHE_STORE')),

];
