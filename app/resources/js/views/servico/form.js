import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { enumAction } from "../../commons/enumAction";
import { modalMessage } from "../../components/comum/modalMessage";
import { modalSelecionarPagamentoTipo } from "../../components/servico/modalSelecionarPagamentoTipo";
import { modalServicoAnotacao } from "../../components/servico/modalServicoAnotacao";
import { modalServicoPagamento } from "../../components/servico/modalServicoPagamento";
import { modalServicoParticipacao } from "../../components/servico/modalServicoParticipacao";
import { modalAreaJuridicaTenant } from "../../components/tenant/modalAreaJuridicaTenant";
import { BootstrapFunctionsHelper } from "../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { RedirectHelper } from "../../helpers/RedirectHelper";
import { RequestsHelpers } from "../../helpers/RequestsHelpers";
import { ServicoParticipacaoHelpers } from "../../helpers/ServicoParticipacaoHelpers";
import SimpleBarHelper from "../../helpers/SimpleBarHelper";
import { URLHelper } from "../../helpers/URLHelper";
import { UUIDHelper } from "../../helpers/UUIDHelper";
import { ServicoParticipacaoModule } from "../../modules/ServicoParticipacaoModule";

class PageServicoForm {

    #objConfigs = {
        url: {
            base: window.apiRoutes.baseServico,
            baseAnotacao: undefined,
            basePagamentos: undefined,
            baseParticipacao: undefined,
            baseValores: undefined,
            baseAreaJuridicaTenant: window.apiRoutes.baseAreaJuridicaTenant,
            baseParticipacaoPreset: window.apiRoutes.baseParticipacaoPreset,
            baseParticipacaoTipo: window.apiRoutes.baseServicoParticipacaoTipoTenant,
        },
        sufixo: 'PageServicoForm',
        data: {
            porcentagemOcupada: 0,
            participantesNaTela: [],
        },
    };
    #action;
    #idRegister;
    #functionsServicoParticipacao;

    constructor() {
        const objData = {
            objConfigs: this.#objConfigs,
            extraConfigs: {
                modeParent: 'searchAndUse',
            }
        }
        this.#functionsServicoParticipacao = new ServicoParticipacaoModule(this, objData);
        this.initEvents();
    }

    initEvents() {
        const self = this;
        this.#buscarAreasJuridicas();

        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self.#idRegister = uuid;
            self.#objConfigs.url.baseAnotacao = `${self.#objConfigs.url.base}/${self.#idRegister}/anotacao`;
            self.#objConfigs.url.basePagamentos = `${self.#objConfigs.url.base}/${self.#idRegister}/pagamentos`;
            self.#objConfigs.url.baseParticipacao = `${self.#objConfigs.url.base}/${self.#idRegister}/participacao`;
            self.#objConfigs.url.baseValores = `${self.#objConfigs.url.base}/${self.#idRegister}/relatorio/valores`;
            this.#action = enumAction.PUT;
            self.#buscarDados();
        } else {
            this.#action = enumAction.POST;
        }

        self.#addEventosBotoes();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#btnOpenAreaJuridicaTenant${self.#objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalAreaJuridicaTenant();
                objModal.setDataEnvModal = {
                    attributes: {
                        select: {
                            quantity: 1,
                            autoReturn: true,
                        }
                    }
                }
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    if (response.selecteds.length > 0) {
                        const item = response.selecteds[0];
                        self.#buscarAreasJuridicas(item.id);
                    } else {
                        self.#buscarAreasJuridicas();
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnSave${self.#objConfigs.sufixo}`).on('click', async function (e) {
            e.preventDefault();
            self.#saveButtonAction();
        });

        $(`#btnSaveParticipantes${self.#objConfigs.sufixo}`).on('click', async function (e) {
            e.preventDefault();
            self.#saveButtonActionParticipacao();
        });

        $(`#btnAdicionarCliente${self.#objConfigs.sufixo}`).on('click', async function () {
            commonFunctions.generateNotification('Funcionalidade em desenvolvimento.', 'warning');
        });

        $(`#btnAdicionarAnotacao${self.#objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalServicoAnotacao(self.#objConfigs.url.baseAnotacao);
                objModal.setFocusElementWhenClosingModal = btn;
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    self.#inserirAnotacao(response.register);
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnInserirPagamento${self.#objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalSelecionarPagamentoTipo(`${self.#objConfigs.url.base}/${self.#idRegister}`);
                objModal.setFocusElementWhenClosingModal = btn;
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    await self.#buscarPagamentos();
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnExcluirParticipante${self.#objConfigs.sufixo}`).on('click', async function () {
            const response = await self.#delButtonAction(`${self.#idRegister}/participacao`, null, {
                title: `Exclusão de Participantes`,
                message: `Confirma a exclusão do(s) participante(s) deste serviço?`,
                success: `Participantes excluídos com sucesso!`,
                button: this,
                urlApi: `${self.#objConfigs.url.base}`,
            });

            if (response) {
                self.#buscarParticipantes();
            }
        });

        self.#functionsServicoParticipacao._buscarPresetParticipacaoTenant();

        const openModalTest = async () => {
            const objCode = new modalServicoPagamento(`${self.#objConfigs.url.base}/${self.#idRegister}/pagamentos`);
            objCode._dataEnvModal = {

                pagamento_tipo_tenant_id: '9d3f0306-030e-4e2b-a42a-380a87a091ae',
            }
            const retorno = await objCode.modalOpen();
            // console.log(retorno);
        }
        // openModalTest();
    }

    async #inserirAnotacao(item) {
        const self = this;
        const divAnotacao = $(`#divAnotacao${self.#objConfigs.sufixo}`);

        item.idCol = UUIDHelper.generateUUID();
        let created_at = '';
        if (item.created_at) {
            created_at = `<span class="text-body-secondary d-block">Cadastrado em ${DateTimeHelper.retornaDadosDataHora(item.created_at, 12)}</span>`;
            item.statusSalvo = true;
        } else {
            item.statusSalvo = false;
        }

        const strToHtml = commonFunctions.formatStringToHTML(item.descricao);
        let strCard = `
            <div id="${item.idCol}" class="col">
                <div class="card">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title d-flex align-items-center justify-content-between">
                                <span class="text-truncate spanTitle">${item.titulo}</span>
                                <div>
                                    <div class="dropdown">
                                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><button type="button" class="dropdown-item fs-6 btn-edit" title="Editar anotação ${item.titulo}">Editar</button></li>
                                            <li><button type="button" class="dropdown-item fs-6 btn-delete" title="Excluir anotação ${item.titulo}">Excluir</button></li>
                                        </ul>
                                    </div>
                                </div>
                            </h5>
                            <div class="card-text overflow-auto scrollbar" style="max-height: 10rem;">
                                <p class="my-0 pText">${strToHtml}</p>
                            </div>
                        </div>
                        <div class="card-footer text-body-secondary">
                            ${created_at}
                        </div>
                    </div>
                </div>
            </div>`;

        divAnotacao.append(strCard);
        self.#addEventosAnotacao(item);
        SimpleBarHelper.apply();
        return true;
    }

    async #addEventosAnotacao(item) {
        const self = this;

        $(`#${item.idCol}`).find('.btn-edit').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalServicoAnotacao(self.#objConfigs.url.baseAnotacao);
                objModal.setDataEnvModal = {
                    idRegister: item.id,
                };
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    $(`#${item.idCol}`).find('.spanTitle').html(response.register.titulo);
                    $(`#${item.idCol}`).find('.pText').html(commonFunctions.formatStringToHTML(response.register.descricao));
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });


        $(`#${item.idCol}`).find(`.btn-delete`).click(async function () {
            const response = await self.#delButtonAction(item.id, item.titulo, {
                title: `Exclusão de Anotação`,
                message: `Confirma a exclusão da anotação <b>${item.titulo}</b>?`,
                success: `Anotação excluída com sucesso!`,
                button: this,
                urlApi: self.#objConfigs.url.baseAnotacao,
            });

            if (response) {
                $(`#${item.idCol}`).remove();
            }
        });
    }

    async #inserirPagamento(item) {
        const self = this;
        const divPagamento = $(`#divPagamento${self.#objConfigs.sufixo}`);

        item.idCard = `${UUIDHelper.generateUUID()}${self.#objConfigs.sufixo}`;
        const created_at = `<span class="text-body-secondary d-block">Pagamento cadastrado em ${DateTimeHelper.retornaDadosDataHora(item.created_at, 12)}</span>`;

        let htmlColsEspecifico = self.#htmlColsEspecificosPagamento(item);
        let htmlAppend = self.#htmlParticipantes(item);
        htmlAppend += self.#htmlAppendPagamento(item);
        let htmlLancamentos = self.#htmlLancamentos(item);

        let strCard = `
            <div id="${item.idCard}" class="card p-0">
                <div class="card-body">
                    <h5 class="card-title d-flex align-items-center justify-content-between">
                        <span>${item.pagamento_tipo_tenant.nome}</span>
                        <div>
                            <div class="dropdown">
                                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><button type="button" class="dropdown-item fs-6 btn-participacao" title="Inserir/Editar Participação ${item.pagamento_tipo_tenant.nome}">Participação</button></li>
                                    <li><button type="button" class="dropdown-item fs-6 btn-edit" title="Editar pagamento">Editar</button></li>
                                    <li><button type="button" class="dropdown-item fs-6 btn-delete" title="Excluir pagamento ${item.pagamento_tipo_tenant.nome}">Excluir</button></li>
                                </ul>
                            </div>
                        </div>
                    </h5>
                    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 align-items-end">
                        ${htmlColsEspecifico}
                    </div>
                    ${htmlAppend}
                    ${htmlLancamentos}
                    <div class="form-text mt-2">${created_at}</div>
                </div>
            </div>`;

        divPagamento.append(strCard);
        BootstrapFunctionsHelper.addEventPopover();
        self.#addEventosPagamento(item);
        return true;
    }

    #htmlColsEspecificosPagamento(item) {

        let htmlColsEspecifico = '';
        if (item?.conta) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Conta Padrão</div>
                    <p class="text-truncate">${item.conta.nome}</p>
                </div>`;
        }

        if (item.valor_total) {
            const valorTotal = commonFunctions.formatWithCurrencyCommasOrFraction(item.valor_total);
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Valor Total</div>
                    <p class="">${valorTotal}</p>
                </div>`;
        }

        if (item.entrada_valor) {
            const valorEntrada = commonFunctions.formatWithCurrencyCommasOrFraction(item.entrada_valor);
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Valor Entrada</div>
                    <p class="">${valorEntrada}</p>
                </div>`;
        }

        if (item.entrada_data) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Data Entrada</div>
                    <p class="">${DateTimeHelper.retornaDadosDataHora(item.entrada_data, 2)}</p>
                </div>`;
        }

        if (item.parcela_data_inicio) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Primeira Parcela</div>
                    <p class="">${DateTimeHelper.retornaDadosDataHora(item.parcela_data_inicio, 2)}</p>
                </div>`;
        }

        if (item.parcela_valor) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Valor Parcela</div>
                    <p class="">${commonFunctions.formatWithCurrencyCommasOrFraction(item.parcela_valor)}</p>
                </div>`;
        }

        if (item.parcela_vencimento_dia) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Dia Vencimento</div>
                    <p class="">${item.parcela_vencimento_dia}</p>
                </div>`;
        }

        if (item.parcela_quantidade) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Qtd Parcelas</div>
                    <p class="">${item.parcela_quantidade}</p>
                </div>`;
        }
        if (!item.descricao_condicionado) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Total Aguardando</div>
                    <p class="">${commonFunctions.formatWithCurrencyCommasOrFraction(item.total_aguardando ?? 0)}</p>
                </div>`;

            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Total Liquidado</div>
                    <p class="">${commonFunctions.formatWithCurrencyCommasOrFraction(item.total_liquidado ?? 0)}</p>
                </div>`;

            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Total Inadimplente</div>
                    <p class="">${commonFunctions.formatWithCurrencyCommasOrFraction(item.total_inadimplente ?? 0)}</p>
                </div>`;
        }
        return htmlColsEspecifico;
    }

    #htmlAppendPagamento(item) {
        let htmlAppend = '';

        if (item.descricao_condicionado) {
            htmlAppend += `
            <p class="mb-0 text-truncate" title="${item.descricao_condicionado}">
               <b>Descrição condicionado:</b> ${item.descricao_condicionado}
            </p>`;
        }

        if (item.observacao) {
            htmlAppend += `
            <p class="mb-0 text-truncate" title="${item.observacao}">
               <b>Obs:</b> ${item.observacao}
            </p>`;
        }

        return htmlAppend;
    }

    #htmlLancamentos(item) {
        const self = this;

        let htmlLancamentos = '';
        for (const lancamento of item.lancamentos) {
            let htmlObservacao = '';
            if (lancamento.observacao) {
                htmlObservacao = `<p class="mb-0 text-truncate" title="${lancamento.observacao}">${lancamento.observacao}</p>`;
            }

            const data_vencimento = DateTimeHelper.retornaDadosDataHora(lancamento.data_vencimento, 2);
            const valor_esperado = commonFunctions.formatWithCurrencyCommasOrFraction(lancamento.valor_esperado);
            const title_conta = lancamento.conta?.nome ?? 'Conta Padrão do Pagamento';
            const nome_conta = lancamento.conta?.nome ?? `<i>${title_conta}</i>`;

            lancamento.idCard = `${UUIDHelper.generateUUID()}${self.#objConfigs.sufixo}`;

            htmlLancamentos += `
                <div id="${lancamento.idCard}" class="card p-0">
                    <div class="card-header">
                        ${lancamento.descricao_automatica}
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 align-items-end">
                            <div class="col">
                                <div class="form-text mt-0">Data de vencimento</div>
                                <p class="mb-0">${data_vencimento}</p>
                            </div>
                            <div class="col">
                                <div class="form-text mt-0">Valor</div>
                                <p class="mb-0">${valor_esperado}</p>
                            </div>
                            <div class="col">
                                <div class="form-text mt-0">Status</div>
                                <p class="mb-0">${lancamento.status.nome}</p>
                            </div>
                            <div class="col">
                                <div class="form-text mt-0">Conta</div>
                                <p class="mb-0 text-truncate" title="${title_conta}">
                                    ${nome_conta}
                                </p>
                            </div>
                        </div>
                        ${htmlObservacao}
                    </div>
                </div>`
        }

        let html = `<div class="accordion mt-2" id="accordionPagamento${item.id}">
            <div class="accordion-item">
                <div class="accordion-header">
                    <button class="accordion-button py-1 collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseOne${item.id}" aria-expanded="false"
                        aria-controls="collapseOne${item.id}">
                        Lançamentos
                    </button>
                </div>
                <div id="collapseOne${item.id}" class="accordion-collapse collapse"
                    data-bs-parent="#accordionPagamento${item.id}">
                    <div class="accordion-body">
                        <div class="row row-cols-1 g-2">${htmlLancamentos}</div>
                    </div>
                </div>
            </div>
        </div>`;
        return html;
    }

    #htmlParticipantes(item) {
        let html = '';
        if (item?.participantes && item.participantes.length > 0) {
            const arrays = ServicoParticipacaoHelpers.htmlRenderParticipantesEIntegrantes(item.participantes);
            html = `
                <p class="mb-0">
                Participação personalizada:
                <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-delete-participante">Excluir</button>
                <button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Participantes do pagamento ${item.pagamento_tipo_tenant.nome}" data-bs-html="true" data-bs-content="${arrays.arrayParticipantes.join("<hr class='my-1'>")}">Ver</button>
                <button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Integrantes de Grupos" data-bs-html="true" data-bs-content="${arrays.arrayIntegrantes.join("<hr class='my-1'>")}">Ver integrantes dos grupos</button>
                </p>`;
        } else {
            html = `
                <p class="mb-0 fst-italic">Participantes herdado do serviço</p>`;
        }
        return html;
    }

    async #addEventosPagamento(item) {
        const self = this;

        $(`#${item.idCard}`).find('.btn-edit').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalServicoPagamento(self.#objConfigs.url.basePagamentos);
                objModal.setDataEnvModal = {
                    idRegister: item.id,
                }
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    self.#buscarPagamentos();
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#${item.idCard}`).find(`.btn-delete`).on('click', async function () {
            const response = await self.#delButtonAction(item.id, item.pagamento_tipo_tenant.nome, {
                title: `Exclusão de Pagamento`,
                message: `Confirma a exclusão do pagamento <b>${item.pagamento_tipo_tenant.nome}</b>?`,
                success: `Pagamento excluído com sucesso!`,
                button: this,
                urlApi: self.#objConfigs.url.basePagamentos,
            });

            if (response) {
                self.#buscarValores();
                $(`#${item.idCard}`).remove();
            }
        });

        $(`#${item.idCard}`).find('.btn-participacao').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalServicoParticipacao({
                    urlApi: `${self.#objConfigs.url.basePagamentos}/${item.id}/participacao`
                });
                const response = await objModal.modalOpen();
                if (response.refresh && response.registers) {
                    self.#buscarPagamentos();
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#${item.idCard}`).find(`.btn-delete-participante`).on('click', async function () {
            const response = await self.#delButtonAction(`${item.id}/participacao`, item.pagamento_tipo_tenant.nome, {
                title: `Exclusão de Participantes`,
                message: `Confirma a exclusão do(s) participante(s) personalizado(s) do pagamento <b>${item.pagamento_tipo_tenant.nome}</b>?`,
                success: `Participantes excluídos com sucesso!`,
                button: this,
                urlApi: `${self.#objConfigs.url.basePagamentos}`,
            });

            if (response) {
                self.#buscarPagamentos();
            }
        });
    }

    async #buscarDados() {
        const self = this;

        try {
            await commonFunctions.loadingModalDisplay();
            const response = await self.#getRecurse();
            const form = $(`#formServico${self.#objConfigs.sufixo}`);

            if (response?.data) {
                const responseData = response.data;
                form.find('input[name="titulo"]').val(responseData.titulo);
                commonFunctions.updateSelect2Value($(`#area_juridica_id${self.#objConfigs.sufixo}`), responseData.area_juridica.nome, responseData.area_juridica_id);
                form.find('textarea[name="descricao"]').val(responseData.descricao);

                responseData.anotacao.forEach(item => {
                    self.#inserirAnotacao(item);
                });

                responseData.pagamento.forEach(item => {
                    self.#inserirPagamento(item);
                });

                self.#functionsServicoParticipacao._inserirParticipantesEIntegrantes(responseData.participantes);

                self.#atualizaTodosValores(response.data);
            } else {
                form.find('input, textarea, select, button').prop('disabled', true);
            }

        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
    }

    #atualizaTodosValores(data) {
        const self = this;
        self.#atualizarValorServico(data.valor_servico);
        self.#atualizarTotalAguardando(data.total_aguardando);
        self.#atualizarTotalLiquidado(data.total_liquidado);
        self.#atualizarTotalInadimplente(data.total_inadimplente);
    }

    #atualizarValorServico(valor) {
        const self = this;
        $(`#valorServico${self.#objConfigs.sufixo}`).html(commonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    }

    #atualizarTotalAguardando(valor) {
        const self = this;
        $(`#totalAguardando${self.#objConfigs.sufixo}`).html(commonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    }

    #atualizarTotalLiquidado(valor) {
        const self = this;
        $(`#totalLiquidado${self.#objConfigs.sufixo}`).html(commonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    }

    #atualizarTotalInadimplente(valor) {
        const self = this;
        $(`#totalInadimplente${self.#objConfigs.sufixo}`).html(commonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    }

    async #buscarAreasJuridicas(selected_id = null) {
        const self = this;
        let options = selected_id ? { selectedIdOption: selected_id } : {};
        const selArea = $(`#area_juridica_id${self.#objConfigs.sufixo}`);
        await commonFunctions.fillSelect(selArea, self.#objConfigs.url.baseAreaJuridicaTenant, options);
    }

    async #buscarPagamentos() {
        const self = this;
        try {
            const obj = new connectAjax(self.#objConfigs.url.basePagamentos);
            const response = await obj.getRequest();
            $(`#divPagamento${self.#objConfigs.sufixo}`).html('');
            for (const item of response.data) {
                self.#inserirPagamento(item);
            }
            await self.#buscarValores();
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
    }

    async #buscarParticipantes() {
        const self = this;
        try {
            const obj = new connectAjax(self.#objConfigs.url.baseParticipacao);
            const response = await obj.getRequest();
            if (response.data) {
                self.#functionsServicoParticipacao._inserirParticipantesEIntegrantes(response.data);
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
    }

    async #buscarValores() {
        const self = this;
        try {
            const obj = new connectAjax(self.#objConfigs.url.baseValores);
            const response = await obj.getRequest();
            self.#atualizaTodosValores(response.data);
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
    }

    async #getRecurse(options = {}) {
        const self = this;
        const { idRegister = self.#idRegister,
            urlApi = self.#objConfigs.url.base,
        } = options;

        try {
            const obj = new connectAjax(urlApi);
            obj.setParam(idRegister);
            return await obj.getRequest();
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async #delButtonAction(idDel, nameDel, options = {}) {
        const self = this;
        const { button = null,
            title = 'Exclusão de Registro',
            message = `Confirma a exclusão do registro < b > ${nameDel}</ >? `,
            success = `Registro excluído com sucesso!`,
        } = options;

        try {
            const obj = new modalMessage();
            obj.setDataEnvModal = {
                title: title,
                message: message,
            };
            obj.setFocusElementWhenClosingModal = button;
            const result = await obj.modalOpen();
            if (result.confirmResult) {
                if (await self.#delRecurse(idDel, options)) {
                    commonFunctions.generateNotification(success, 'success');
                    return true;
                }
            }
            return false;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async #delRecurse(idDel, options = {}) {
        const self = this;
        const {
            urlApi = self.#objConfigs.url.base,
        } = options;

        try {
            await RequestsHelpers.delRecurse({ idRegister: idDel, urlApi: urlApi });
            return true;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    #saveButtonAction() {
        const self = this;
        const formRegistration = $(`#formServico${self.#objConfigs.sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data, formRegistration)) {
            self.#save(data, self.#objConfigs.url.base);
        }
        return false;
    }

    #saveVerifications(data, formRegistration) {
        const self = this;
        if (self.#action == enumAction.POST) {
            let blnSave = commonFunctions.verificationData(data.titulo, { field: formRegistration.find('input[name="titulo"]'), messageInvalid: 'O título deve ser informado.', setFocus: true });
            blnSave = commonFunctions.verificationData(data.area_juridica_id, { field: formRegistration.find('select[name="area_juridica_id"]'), messageInvalid: 'A Área Jurídica deve ser selecionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            blnSave = commonFunctions.verificationData(data.descricao, { field: formRegistration.find('textarea[name="descricao"]'), messageInvalid: 'A descrição deve ser preenchida.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            return blnSave;
        }
        return true;
    }

    async #save(data, urlApi, options = {}) {
        const self = this;
        const {
            btnSave = $(`#btnSave${self.#objConfigs.sufixo}`),
        } = options;

        try {
            commonFunctions.simulateLoading(btnSave);
            const obj = new connectAjax(urlApi);
            obj.setAction(self.#action);
            obj.setData(data);
            if (self.#action === enumAction.PUT) {
                obj.setParam(self.#idRegister);
            }
            const response = await obj.envRequest();

            if (response) {
                if (self.#action === enumAction.PUT) {
                    commonFunctions.generateNotification('Dados do serviço alterados com sucesso!', 'success');
                } else {
                    RedirectHelper.redirectWithUUIDMessage(`${window.frontRoutes.frontRedirectForm}/${response.data.id}`, 'Serviço iniciado com sucesso!', 'success');
                }
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
        finally {
            commonFunctions.simulateLoading(btnSave, false);
        };
    }

    async #saveButtonActionParticipacao() {
        const self = this;
        let data = {
            participantes: self.#objConfigs.data.participantesNaTela,
        }

        if (self.#functionsServicoParticipacao._saveVerificationsParticipantes(data)) {
            await self.#saveParticipantes(data, self.#objConfigs.url.baseParticipacao, { fieldRegisterName: 'registers' });
        }
    }

    async #saveParticipantes(data, urlApi, options = {}) {
        const self = this;
        const {
            btnSave = $(`#btnSaveParticipantes${self.#objConfigs.sufixo}`),
        } = options;

        try {
            commonFunctions.simulateLoading(btnSave);
            const obj = new connectAjax(urlApi);
            obj.setAction(enumAction.POST);
            obj.setData(data);

            const response = await obj.envRequest();
            if (response) {
                commonFunctions.generateNotification(`Dados enviados com sucesso!`, 'success');
                self.#functionsServicoParticipacao._inserirParticipantesEIntegrantes(response.data);
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
        finally {
            commonFunctions.simulateLoading(btnSave, false);
        };
    }
}

$(function () {
    new PageServicoForm();
});