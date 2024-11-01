<div class="modal fade" id="modalInformacaoSubjetivaCategoria" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title">Categoria de Informação Subjetiva</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                @php
                    $sufixo = 'modalInformacaoSubjetivaCategoria';
                    $dados = new Illuminate\Support\Fluent([
                        'camposFiltrados' => [
                            'id' => ['nome' => 'ID'],
                            'nome' => ['nome' => 'Nome'],
                            'descricao' => ['nome' => 'Descrição'],
                        ],
                        'arrayCamposChecked' => ['nome', 'descricao'],
                        'dadosSelectTratamento' => ['selecionado' => 'texto_dividido'],
                        'dadosSelectFormaBusca' => ['selecionado' => 'qualquer_incidencia'],
                    ]);
                @endphp
                <x-consulta.formulario-padrao-filtro.componente :sufixo="$sufixo" :dados="$dados" />

                <div class="table-responsive mt-2">
                    <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover tableData">
                        <thead>
                            <tr>
                                <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                                <th class="text-center">ID</th>
                                <th>Nome</th>
                                <th>Descrição</th>
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
                    @include('components.modal.inteligencia.modal-informacao-subjetiva-categoria.campos-cadastro')
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
        'baseInfoSubjCategorias' => route('api.inteligencia.info-subj.categoria'),
    ],
])
@endcomponent
