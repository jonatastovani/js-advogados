import { commonFunctions } from "../../commons/commonFunctions";
import { templateSearch } from "../../commons/templates/templateSearch";
import { modalLancamentoMovimentar } from "../../components/financeiro/modalLancamentoMovimentar";
import { BootstrapFunctionsHelper } from "../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { ServicoParticipacaoHelpers } from "../../helpers/ServicoParticipacaoHelpers";

class PageLancamentoServicoIndex extends templateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.baseLancamento,
                urlSearch: `${window.apiRoutes.baseLancamento}/consulta-filtros`,
                baseFront: window.frontRoutes.baseFront,
            }
        },
        url: {
            baseServico: window.apiRoutes.baseServico,
        }
    };

    constructor() {
        super({ sufixo: 'PageLancamentoServicoIndex' });
        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this.initEvents();
    }

    initEvents() {
        const self = this;
        self.#addEventosBotoes();
        self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
        self._generateQueryFilters()
    }

    #addEventosBotoes() {
        const self = this;

        $(`#formDataSearch${self.getSufixo}`).find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            BootstrapFunctionsHelper.removeEventPopover();
            self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
            self._generateQueryFilters()
        });

        const openModal = async () => {
            try {
                const objModal = new modalLancamentoMovimentar();
                objModal.setDataEnvModal = {
                    idRegister: "9d73c9d3-e23c-4a0f-9aa7-953cc9e4552a",
                }
                const response = await objModal.modalOpen();
                console.log(response);

            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            }
        }

        openModal();
    }

    async insertTableData(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        let strBtns = self.#HtmlBtns(item);

        const numero_servico = item.pagamento.servico.numero_servico;
        const status = item.status.nome;
        const valorEsperado = commonFunctions.formatWithCurrencyCommasOrFraction(item.valor_esperado);
        const dataVencimento = DateTimeHelper.retornaDadosDataHora(item.data_vencimento, 2);
        const valorRecebido = item.data_recebimento ? commonFunctions.formatWithCurrencyCommasOrFraction(item.valor_recebido) : '***';
        const dataRecebimento = item.data_recebimento ? DateTimeHelper.retornaDadosDataHora(item.data_recebimento, 2) : '***';
        const descricaoAutomatica = item.descricao_automatica;
        const observacaoLancamento = item.observacao ?? '***';
        const valorPagamento = item.pagamento.valor_total ? commonFunctions.formatWithCurrencyCommasOrFraction(item.pagamento.valor_total) : '***';
        const tituloServico = item.pagamento.servico.titulo;
        const areaJuridica = item.pagamento.servico.area_juridica.nome;
        const valorLiquidado = item.pagamento.total_liquidado ? commonFunctions.formatWithCurrencyCommasOrFraction(item.pagamento.total_liquidado) : '***';
        const valorAguardando = item.pagamento.total_aguardando ? commonFunctions.formatWithCurrencyCommasOrFraction(item.pagamento.total_aguardando) : '***';
        const valorInadimplente = item.pagamento.total_inadimplente ? commonFunctions.formatWithCurrencyCommasOrFraction(item.pagamento.total_inadimplente) : '***';
        const pagamentoTipo = item.pagamento.pagamento_tipo_tenant.nome ?? item.pagamento.pagamento_tipo_tenant.pagamento_tipo.nome
        const observacaoPagamento = item.pagamento.observacao ?? '***';

        const arrays = ServicoParticipacaoHelpers.htmlRenderParticipantesEIntegrantes(
            item.participantes.length ? item.participantes :
                (item.pagamento.participantes.length ? item.pagamento.participantes :
                    (item.pagamento.servico.participantes.length ? item.pagamento.servico.participantes : [])
                )
        );

        const created_at = DateTimeHelper.retornaDadosDataHora(item.created_at, 12);
        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
                    </div>
                </td>
                <td class="text-nowrap" title="${numero_servico}">${numero_servico}</td>
                <td title="${status}">${status}</td>
                <td class="text-nowrap text-center" title="${valorEsperado}">${valorEsperado}</td>
                <td class="text-nowrap text-center" title="${dataVencimento}">${dataVencimento}</td>
                <td class="text-nowrap text-center" title="${valorRecebido}">${valorRecebido}</td>
                <td class="text-nowrap text-center" title="${dataRecebimento}">${dataRecebimento}</td>
                <td class="text-nowrap text-truncate" title="${descricaoAutomatica}">${descricaoAutomatica}</td>
                <td class="text-truncate" title="${observacaoLancamento}">${observacaoLancamento}</td>
                <td class="text-nowrap text-center" title="${valorPagamento}">${valorPagamento}</td>
                <td class="text-truncate" title="${tituloServico}">${tituloServico}</td>
                <td class="text-nowrap text-truncate" title="${areaJuridica}">${areaJuridica}</td>
                <td class="text-nowrap text-center" title="${valorLiquidado}">${valorLiquidado}</td>
                <td class="text-nowrap text-center" title="${valorAguardando}">${valorAguardando}</td>
                <td class="text-nowrap text-center" title="${valorInadimplente}">${valorInadimplente}</td>
                <td class="text-truncate" title="${pagamentoTipo}">${pagamentoTipo}</td>
                <td class="text-truncate" title="${observacaoPagamento}">${observacaoPagamento}</td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Participantes do Lançamento ${descricaoAutomatica}" data-bs-html="true" data-bs-content="${arrays.arrayParticipantes.join("<hr class='my-1'>")}">Ver mais</button></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Integrantes de Grupos" data-bs-html="true" data-bs-content="${arrays.arrayIntegrantes.join("<hr class='my-1'>")}">Ver mais</button></td>
                <td class="text-nowrap" title="${created_at ?? ''}">${created_at ?? ''}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        BootstrapFunctionsHelper.addEventPopover();
        return true;
    }

    #HtmlBtns(item) {
        const self = this;

        let strBtns = `
            <div class="btn-group">
                <button class="btn dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                    
                    <li>
                        <button type="button" class="dropdown-item fs-6 text-success btn-receber-liquidado-analise" title="Receber lancamento ${item.descricao_automatica} com status Liquidado em Análise.">
                            <i class="bi bi-check2"></i> Liquidado em Análise
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item fs-6 text-success btn-receber-liquidado" title="Receber lancamento ${item.descricao_automatica} com status Liquidado.">
                            <i class="bi bi-check2-all"></i> Liquidado
                        </button>
                    </li>
                
                    <li>
                        <button type="button" class="dropdown-item fs-6 text-warning btn-receber-parcial-analise" title="Receber lancamento ${item.descricao_automatica} com status Liquidado Parcial em Análise.">
                            <i class="bi bi-exclamation-lg"></i> Parcial em Análise
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item fs-6 text-warning btn-receber-parcial" title="Receber lançamento ${item.descricao_automatica} com status Liquidado Parcial.">
                            <i class="bi bi-check2-all"></i> Parcial
                        </button>
                    </li>

                    <li>
                        <button type="button" class="dropdown-item fs-6 btn-cancelado-analise text-danger" title="Registrar lançamento ${item.descricao_automatica} com status Cancelado em Análise.">
                           <i class="bi bi-dash-circle"></i> Cancelado em Análise
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item fs-6 btn-cancelado text-danger" title="Registrar lançamento ${item.descricao_automatica} com status Cancelado.">
                            <i class="bi bi-check2-all"></i> Cancelado
                        </button>
                    </li>

                </ul>
            </div>`;

        return strBtns;

    }

    #addEventosRegistrosConsulta(item) {
        const self = this;

        $(`#${item.idTr}`).find(`.btn-delete`).click(async function () {
            self._delButtonAction(item.id, item.name, {
                title: `Exclusão de Serviço`,
                message: `Confirma a exclusão do Serviço <b>${item.numero_servico}</b>?`,
                success: `Serviço excluído com sucesso!`,
                button: this
            });
        });
    }
}

$(function () {
    new PageLancamentoServicoIndex();
});