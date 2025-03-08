<?php

return [
    // Configuracoes para o os Tipos de Tenant
    'tenant_type' => [
        // Nome do atributo da chave onde armazenará o id do domínio manualmente selecionado
        'name_attribute_key' => 'manual_selected_domain_id',

        // Nome do atributo no header onde armazenará o domínio manualmente selecionado
        'header_attribute_key' => 'X-Selected-Domain',
        'domain_custom_identification_class_name' => 'element-domain-custom-identification'
    ]
];
