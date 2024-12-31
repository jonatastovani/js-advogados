@push('modals')
    <x-modal.tenant.modal-estado-civil-tenant.modal />
    <x-modal.tenant.modal-escolaridade-tenant.modal />
    <x-modal.tenant.modal-sexo-tenant.modal />
    <x-modal.pessoa.modal-selecionar-documento-tipo.modal />
    <x-modal.pessoa.modal-pessoa-documento.modal />
    <x-modal.pessoa.modal-selecionar-pessoa-perfil-tipo.modal />
@endpush

@push('scripts')
    @component('components.api.api-routes', [
        'routes' => [
            'basePessoaPerfil' => route('api.pessoa.perfil'),
            'basePessoaJuridica' => route('api.pessoa.pessoa-juridica'),
            'baseEscolaridadeTenant' => route('api.tenant.escolaridade'),
            'baseEstadoCivilTenant' => route('api.tenant.estado-civil'),
            'baseSexoTenant' => route('api.tenant.sexo'),
        ],
    ])
    @endcomponent
@endpush
