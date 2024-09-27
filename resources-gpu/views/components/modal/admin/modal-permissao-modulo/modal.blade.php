<div class="modal fade" id="modalPermissaoModulo" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title">Módulos do Sistema</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                @php
                    $sufixo = 'ModalPermissaoModulo';
                    $dados = [
                        'camposFiltrados' => [
                            'id' => ['nome' => 'ID'],
                            'nome' => ['nome' => 'Nome'],
                            'descricao' => ['nome' => 'Descrição'],
                            'modulo' => ['nome' => 'Módulo'],
                        ],
                        'arrayCamposChecked' => ['nome', 'descricao', 'modulo'],
                        'dadosSelectTratamento' => ['selecionado' => 'texto_dividido'],
                        'dadosSelectFormaBusca' => ['selecionado' => 'qualquer_incidencia'],
      ];
                @endphp
                <x-consulta.formulario-padrao-filtro.componente :sufixo="$sufixo" :dados="$dados" />

                <div class="table-responsive mt-2">
                    <table class="table table-sm table-striped table-hover tableData">
                        <thead>
                            <tr>
                                <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                                <th class="text-center">ID</th>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Modulo</th>
                                <th>Individuais</th>
                                <th>Ativo</th>
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
                    @include('components.modal.admin.modal-permissao-grupo.campos-cadastro')
                </div>
            </div>
        </div>
    </div>
</div>

@component('components.api.api-routes', [
    'routes' => [
        'basePermissoesGrupos' => route('api.admin.permissoes.grupos'),
    ],
])
@endcomponent