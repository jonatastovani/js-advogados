import { CommonFunctions } from "../../../commons/CommonFunctions";
import { TemplateSearch } from "../../../commons/templates/TemplateSearch";
import { ModalAreaJuridicaTenant } from "../../../components/tenant/ModalAreaJuridicaTenant";
import { ModalFormaPagamentoTenant } from "../../../components/tenant/ModalFormaPagamentoTenant";
import { BootstrapFunctionsHelper } from "../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import { ParticipacaoHelpers } from "../../../helpers/ParticipacaoHelpers";
import { UUIDHelper } from "../../../helpers/UUIDHelper";

class PagePagamentoServicoIndex extends TemplateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: `${window.apiRoutes.basePagamento}/servicos`,
                urlSearch: `${window.apiRoutes.basePagamento}/servicos/consulta-filtros`,
            }
        },
        url: {
            basePagamento: window.apiRoutes.basePagamento,
            baseMovimentacaoContaLancamentoServico: window.apiRoutes.baseMovimentacaoContaLancamentoServico,
            baseFormaPagamento: window.apiRoutes.baseFormaPagamento,
            baseAreaJuridicaTenant: window.apiRoutes.baseAreaJuridicaTenant,
            basePagamentoTipoTenants: window.apiRoutes.basePagamentoTipoTenants,
            baseFrontServicoForm: window.frontRoutes.baseFrontServicoForm,
        },
    };

    constructor() {
        super({ sufixo: 'PagePagamentoServicoIndex' });
        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this.initEvents();
    }

    initEvents() {
        const self = this;
        self.#addEventosBotoes();
        self._executarBusca();
        self.#buscarFormaPagamento();
        self.#buscarPagamentoStatusTipo();
        self.#buscarPagamentoTipoTenant();
        self.#buscarAreasJuridicas();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#formDataSearch${self.getSufixo}`).find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            BootstrapFunctionsHelper.removeEventPopover();
            self._executarBusca();
        });

        CommonFunctions.handleModal(self, $(`#openModalFormaPagamento${self.getSufixo}`), ModalFormaPagamentoTenant, self.#buscarFormaPagamento.bind(self));

        CommonFunctions.handleModal(self, $(`#openModalAreaJuridica${self.getSufixo}`), ModalAreaJuridicaTenant, self.#buscarAreasJuridicas.bind(self));

    }

    async _executarBusca() {
        const self = this;

        const getAppendDataQuery = () => {
            const formData = $(`#formDataSearch${self.getSufixo}`);
            let appendData = {};
            let data = CommonFunctions.getInputsValues(formData[0]);

            if (data.pagamento_tipo_tenant_id && UUIDHelper.isValidUUID(data.pagamento_tipo_tenant_id)) {
                appendData.pagamento_tipo_tenant_id = data.pagamento_tipo_tenant_id;
            }

            if (data.forma_pagamento_id && UUIDHelper.isValidUUID(data.forma_pagamento_id)) {
                appendData.forma_pagamento_id = data.forma_pagamento_id;
            }

            if (data.pagamento_status_tipo_id && Number(data.pagamento_status_tipo_id) > 0) {
                appendData.pagamento_status_tipo_id = data.pagamento_status_tipo_id;
            }

            if (data.area_juridica_id && UUIDHelper.isValidUUID(data.area_juridica_id)) {
                appendData.area_juridica_id = data.area_juridica_id;
            }

            return { appendData: appendData };
        }

        BootstrapFunctionsHelper.removeEventPopover();
        self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
        await self._generateQueryFilters(getAppendDataQuery());
    }

    async insertTableData(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        let strBtns = self.#htmlBtns(item);

        const numero_servico = item.servico.numero_servico;
        const numero_pagamento = item.numero_pagamento;
        const status = item.status.nome;
        const formaPagamento = item.forma_pagamento.nome;
        const valorPagamento = item.valor_total ? CommonFunctions.formatNumberToCurrency(item.valor_total) : '***';
        const tituloServico = item.servico.titulo;
        const areaJuridica = item.servico.area_juridica.nome;
        const valorLiquidado = item.total_liquidado ? CommonFunctions.formatNumberToCurrency(item.total_liquidado) : '***';
        const valorAguardando = item.total_aguardando ? CommonFunctions.formatNumberToCurrency(item.total_aguardando) : '***';
        const valorInadimplente = item.total_inadimplente ? CommonFunctions.formatNumberToCurrency(item.total_inadimplente) : '***';
        const pagamentoTipo = item.pagamento_tipo_tenant.nome ?? item.pagamento_tipo_tenant.pagamento_tipo.nome
        const observacaoPagamento = item.observacao ?? '***';

        const arrays = ParticipacaoHelpers.htmlRenderParticipantesEIntegrantes(
            item.participantes.length ? item.participantes :
                (item.servico.participantes.length ? item.servico.participantes : [])
        );

        const htmlCliente = self.#htmlRenderCliente(item);

        const created_at = DateTimeHelper.retornaDadosDataHora(item.created_at, 12);
        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
                    </div>
                </td>
                <td class="text-nowrap text-truncate" title="${status}">${status}</td>
                <td class="text-nowrap">${htmlCliente}</td>
                <td class="text-nowrap text-center" title="${valorPagamento}">${valorPagamento}</td>
                <td class="text-nowrap text-center" title="${valorAguardando}">${valorAguardando}</td>
                <td class="text-nowrap text-center" title="${valorLiquidado}">${valorLiquidado}</td>
                <td class="text-nowrap text-center" title="${valorInadimplente}">${valorInadimplente}</td>
                <td class="text-nowrap text-truncate" title="${pagamentoTipo}">${pagamentoTipo}</td>
                <td class="text-nowrap" title="${formaPagamento}">${formaPagamento}</td>
                <td class="text-nowrap text-truncate" title="${observacaoPagamento}">${observacaoPagamento}</td>
                <td class="text-truncate" title="${tituloServico}">${tituloServico}</td>
                <td class="text-nowrap text-truncate" title="${areaJuridica}">${areaJuridica}</td>
                <td class="text-nowrap" title="${numero_pagamento}">${numero_pagamento}</td>
                <td class="text-nowrap" title="${numero_servico}">${numero_servico}</td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Participantes do pagamento ${numero_pagamento}" data-bs-html="true" data-bs-content="${arrays.arrayParticipantes.join("<hr class='my-1'>")}">Ver mais</button></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Integrantes de Grupos" data-bs-html="true" data-bs-content="${arrays.arrayIntegrantes.join("<hr class='my-1'>")}">Ver mais</button></td>
                <td class="text-nowrap" title="${created_at ?? ''}">${created_at ?? ''}</td>
            </tr>
        `);

        BootstrapFunctionsHelper.addEventPopover();
        return true;
    }

    #htmlBtns(item) {
        const self = this;

        let strVerServico = `
            <li>
                <a href="${self._objConfigs.url.baseFrontServicoForm}/${item.servico.id}" target="_blank" class="dropdown-item fs-6" title="Ir para o Serviço ${item.servico.numero_servico} - ${item.servico.titulo}.">
                    <i class="bi bi-box-arrow-up-right"></i> Ver Serviço
                </a>
            </li>`;

        return `
            <button class="btn dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu">
                ${strVerServico}
            </ul>`;
    }

    #htmlRenderCliente(pagamento) {
        const self = this;

        const arrayCliente = pagamento?.servico?.cliente;
        if (!arrayCliente.length) {
            return '<span class="fst-italic" title="Nenhum cliente encontrado">Nenhum cliente encontrado</span>';
        }

        let nomes = [];

        arrayCliente.map(cliente => {
            const pessoa = cliente.perfil.pessoa;
            let nome = '';

            switch (pessoa.pessoa_dados_type) {
                case window.Enums.PessoaTipoEnum.PESSOA_FISICA:
                    nome = pessoa.pessoa_dados.nome;
                    break;
                case window.Enums.PessoaTipoEnum.PESSOA_JURIDICA:
                    nome = pessoa.pessoa_dados.nome_fantasia;
                    break;

                default:
                    nome = `Não implementado - ${pessoa.pessoa_dados_type}`;
                    console.error(`O tipo de pessoa <b>${pessoa.pessoa_dados_type}</b> ainda não foi implementado.`, cliente);

            }
            nomes.push(nome);
        });

        if (nomes.length > 1) {
            const total = nomes.length;
            return `<button type="button" class="btn btn-sm btn-outline-info border-0 text-nowrap text-reset" data-bs-toggle="popover" data-bs-title="Cliente(s)" data-bs-html="true" data-bs-content="${nomes.join("<hr class='my-1'>")}">${nomes[0]} + ${total - 1}</button>`
        }
        return `<span class="text-nowrap">${nomes[0]}</span>`;
    };

    async #buscarFormaPagamento(selected_id = null) {
        try {
            const self = this;
            let options = {
                outInstanceParentBln: true,
                firstOptionName: 'Todas as formas de pagamento',
            };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#forma_pagamento_id${self.getSufixo}`);
            await CommonFunctions.fillSelect(select, self._objConfigs.url.baseFormaPagamento, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarAreasJuridicas(selected_id = null) {
        try {
            const self = this;
            let options = {
                outInstanceParentBln: true,
                firstOptionName: 'Todas as áreas jurídicas',
            };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#area_juridica_id${self.getSufixo}`);
            await CommonFunctions.fillSelect(select, self._objConfigs.url.baseAreaJuridicaTenant, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarPagamentoStatusTipo(selected_id = null) {
        try {
            const self = this;
            const arrayOpcoes = window.Details.PagamentoStatusTipoEnum;
            let options = { firstOptionName: 'Todos os status' };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#pagamento_status_tipo_id${self.getSufixo}`);
            await CommonFunctions.fillSelectArray(select, arrayOpcoes, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarPagamentoTipoTenant(selected_id = null) {
        try {
            const self = this;
            let options = { outInstanceParentBln: true };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#pagamento_tipo_tenant_id${self.getSufixo}`);
            await CommonFunctions.fillSelect(select, self._objConfigs.url.basePagamentoTipoTenants, options);
            return true;
        } catch (error) {
            return false;
        }
    }
}

$(function () {
    new PagePagamentoServicoIndex();
});