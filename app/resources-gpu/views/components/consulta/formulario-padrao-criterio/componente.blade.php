@php
    use App\Helpers\Views\ConsultaHelper;
@endphp
<form action="#" id="formDataSearch{{ $sufixo }}" class="col-12 formDataSearch">
    <div class="row">
        <div class="col mt-2 text-end">
            <button type="button" class="btn btn-outline-primary btnDesmarcarCriterios" title="Desmarcar todos os critérios"><i class="bi bi-square"></i> Desmarcar Critérios</button>
            <button type="button" class="btn btn-outline-primary btnLimparCriterios" title="Desmarcar e limpar todos os critérios"><i class="bi bi-stop-fill"></i> Limpar Critérios</button>
            <button type="submit" class="btn btn-outline-primary btnBuscar" title="Realizar busca"><i class="bi bi-search"></i> Buscar</button>
        </div>
    </div>
    <div class="accordion mt-2" id="accordionCriterios{{ $sufixo }}">
        <div class="accordion-item">
            <div class="accordion-header">
                <button class="accordion-button py-1" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseOneCriterios{{ $sufixo }}" aria-expanded="true"
                    aria-controls="collapseOneCriterios{{ $sufixo }}">
                    Criterios de busca
                </button>
            </div>
            <div id="collapseOneCriterios{{ $sufixo }}" class="accordion-collapse"
                data-bs-parent="#accordionCriterios{{ $sufixo }}">
                <div class="accordion-body py-1">
                    <span class="mt-2">Selecione o critério e preencha com a informação a ser buscada.</span>
                    <div class="row row-cols-2 row-cols-xxl-3 containerCriterios overflow-auto" style="max-height: 15rem;">
                        @php
                            ConsultaHelper::renderizarCamposCriterios($dados->camposFiltrados, [
                                'sufixoId' => $sufixo,  
                            ]);
                        @endphp
                    </div>
                    <div class="form-text my-0">Obs: Quanto mais campos marcados, a consulta poderá ser mais lenta.
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
