<div class="modal fade" id="modalParticipacaoPreset" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title">Presets de Participação</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                @php
                    $sufixo = 'ModalParticipacaoPreset';
                    $dados = new Illuminate\Support\Fluent([
                        'camposFiltrados' => [
                            'nome' => ['nome' => 'Nome'],
                            'descricao' => ['nome' => 'Descrição'],
                            'nome_grupo' => ['nome' => 'Nome Grupo Participante'],
                            'nome_participante' => ['nome' => 'Nome Participante'],
                            'nome_integrante' => ['nome' => 'Nome Integrante'],
                        ],
                        'direcaoConsultaChecked' => 'desc',
                        'arrayCamposChecked' => ['nome', 'descricao'],
                        'dadosSelectTratamento' => ['selecionado' => 'texto_dividido'],
                        'dadosSelectFormaBusca' => ['selecionado' => 'iniciado_por'],
                        'arrayCamposOrdenacao' => [
                            'nome' => ['nome' => 'Nome'],
                        ],
                        'preset_tamanho' => 'md',
                    ]);
                @endphp
                <x-consulta.formulario-padrao-filtro.componente :sufixo="$sufixo" :dados="$dados" />

                <div class="table-responsive mt-2">
                    <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover tableData">
                        <thead>
                            <tr>
                                <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Participantes</th>
                                <th>Integrantes (Grupos)</th>
                                <th>Cadastrado em</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <x-consulta.section-paginacao.componente :sufixo="$sufixo" />

                <div class="row">
                    <div class="col mt-2">
                        <a href="{{ route('servico.participacao.form') }}" class="btn btn-outline-primary"
                            target="_blank">Cadastrar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@component('components.api.api-routes', [
    'routes' => [
        'baseParticipacaoPreset' => route('api.comum.participacao-preset'),
    ],
])
@endcomponent
