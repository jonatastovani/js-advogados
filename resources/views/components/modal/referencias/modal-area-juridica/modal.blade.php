<div class="modal fade" id="modalAreaJuridica" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title">Áreas Juridicas</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                @php
                    $sufixo = 'ModalAreaJuridica';
                    $dados = new Illuminate\Support\Fluent([
                        'camposFiltrados' => [
                            'id' => ['nome' => 'ID'],
                            'nome' => ['nome' => 'Nome'],
                        ],
                        'arrayCamposChecked' => ['nome', 'descricao'],
                        'dadosSelectTratamento' => ['selecionado' => 'texto_dividido'],
                        'dadosSelectFormaBusca' => ['selecionado' => 'iniciado_por'],
                    ]);
                @endphp
                <x-consulta.formulario-padrao-filtro.componente :sufixo="$sufixo" :dados="$dados" />

                <div class="table-responsive mt-2">
                    <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover tableData">
                        <thead>
                            <tr>
                                <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                                <th>Nome</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <x-consulta.section-paginacao.componente :sufixo="$sufixo" />

                <div class="row">
                    <div class="col-12 mt-2 divBtnAdd">
                        <button type="button" class="btn-new-register btn btn-outline-primary"
                            style="min-width: 7rem;">Adicionar</button>
                    </div>
                </div>
                <div class="divRegistrationFields mt-2" style="display: none;">
                    @include('components.modal.referencias.modal-area-juridica.campos-cadastro', [
                        'sufixo' => $sufixo,
                    ])
                </div>
            </div>
        </div>
    </div>
</div>

@push('modals')
    <x-modal.admin.modal-code.modal />
@endpush

@component('components.api.api-routes', [
    'routes' => [
        'baseAreaJuridica' => route('api.referencias.area-juridica'),
    ],
])
@endcomponent
