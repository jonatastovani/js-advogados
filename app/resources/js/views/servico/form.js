import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { enumAction } from "../../commons/enumAction";
import { TemplateForm } from "../../commons/templates/TemplateForm";
import { modalMessage } from "../../components/comum/modalMessage";
import { modalParticipacao } from "../../components/comum/modalParticipacao";
import { modalPessoa } from "../../components/pessoas/modalPessoa";
import { modalSelecionarPagamentoTipo } from "../../components/servico/modalSelecionarPagamentoTipo";
import { modalServicoPagamento } from "../../components/servico/modalServicoPagamento";
import { modalAnotacaoLembreteTenant } from "../../components/tenant/modalAnotacaoLembreteTenant";
import { modalAreaJuridicaTenant } from "../../components/tenant/modalAreaJuridicaTenant";
import { BootstrapFunctionsHelper } from "../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { ParticipacaoHelpers } from "../../helpers/ParticipacaoHelpers";
import { QuillEditorHelper } from "../../helpers/QuillEditorHelper";
import SimpleBarHelper from "../../helpers/SimpleBarHelper";
import { URLHelper } from "../../helpers/URLHelper";
import { UUIDHelper } from "../../helpers/UUIDHelper";
import { ParticipacaoModule } from "../../modules/ParticipacaoModule";

class PageServicoForm extends TemplateForm {

    #functionsParticipacao;

    constructor() {

        const objConfigs = {
            url: {
                base: window.apiRoutes.baseServico,
                baseAnotacao: undefined,
                basePagamentos: undefined,
                baseParticipacao: undefined,
                baseValores: undefined,
                baseCliente: undefined,
                baseAreaJuridicaTenant: window.apiRoutes.baseAreaJuridicaTenant,
                baseParticipacaoPreset: window.apiRoutes.baseParticipacaoPreset,
                baseParticipacaoTipo: window.apiRoutes.baseParticipacaoTipoTenant,
            },
            sufixo: 'PageServicoForm',
            data: {
                porcentagemOcupada: 0,
                participantesNaTela: [],
                clientesNaTela: [],
                participacao_tipo_tenant: {
                },
            },
            participacao: {
                // perfis_busca: window.Statics.PerfisPermitidoParticipacaoRessarcimento,
                participacao_tipo_tenant: {
                    configuracao_tipo: window.Enums.ParticipacaoTipoTenantConfiguracaoTipoEnum.LANCAMENTO_SERVICO,
                },
            },
        };

        super({
            objConfigs: objConfigs
        });

        const objData = {
            objConfigs: this._objConfigs,
            extraConfigs: {
                modeParent: 'searchAndUse',
            }
        }
        this.#functionsParticipacao = new ParticipacaoModule(this, objData);
        this.initEvents();
    }

    async initEvents() {
        const self = this;
        await this.#buscarAreasJuridicas();

        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self._idRegister = uuid;
            const url = `${self._objConfigs.url.base}/${self._idRegister}`;
            self._objConfigs.url.baseAnotacao = `${url}/anotacao`;
            self._objConfigs.url.basePagamentos = `${url}/pagamentos`;
            self._objConfigs.url.baseParticipacao = `${url}/participacao`;
            self._objConfigs.url.baseValores = `${url}/relatorio/valores`;
            self._objConfigs.url.baseCliente = `${url}/cliente`;
            this._action = enumAction.PUT;
            await self._buscarDados();
        } else {
            this._action = enumAction.POST;
        }

        self.#addEventosBotoes();
    }

    #addEventosBotoes() {
        const self = this;

        commonFunctions.handleModal(self, $(`#btnOpenAreaJuridicaTenant${self._objConfigs.sufixo}`), modalAreaJuridicaTenant, self.#buscarAreasJuridicas.bind(self));

        $(`#btnSaveParticipantes${self._objConfigs.sufixo} `).on('click', async function (e) {
            e.preventDefault();
            self.#saveButtonActionParticipacao();
        });

        $(`#btnAdicionarCliente${self._objConfigs.sufixo} `).on('click', async function () {

            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalPessoa();
                objModal.setDataEnvModal = {
                    perfis_busca: window.Statics.PerfisPermitidoClienteServico,
                };
                const response = await objModal.modalOpen();
                if (response.refresh && response.selecteds) {
                    response.selecteds.map(item => {
                        self.#inserirCliente({
                            perfil_id: item.id,
                            perfil: item
                        });
                    })
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#atualizarClientes${self._objConfigs.sufixo} `).on('click', async function () {
            await self.#buscarClientes();
        });

        $(`#btnSaveClientes${self._objConfigs.sufixo} `).on('click', async function (e) {
            e.preventDefault();
            self.#saveButtonActionCliente();
        });

        $(`#btnAdicionarAnotacao${self._objConfigs.sufixo} `).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalAnotacaoLembreteTenant(self._objConfigs.url.baseAnotacao);
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

        $(`#btnInserirPagamento${self._objConfigs.sufixo} `).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalSelecionarPagamentoTipo(`${self._objConfigs.url.base}/${self._idRegister}`);
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

        $(`#btnExcluirParticipante${self._objConfigs.sufixo}`).on('click', async function () {
            const response = await self._delButtonAction(`${self._idRegister}/participacao`, null, {
                title: `Exclusão de Participantes`,
                message: `Confirma a exclusão do(s) participante(s) deste serviço?`,
                success: `Participantes excluídos com sucesso!`,
                button: this,
                url: `${self._objConfigs.url.base}`,
            });

            if (response) {
                self.#functionsParticipacao._buscarParticipantes();
            }
        });

        $(`#atualizarPagamentos${self._objConfigs.sufixo}`).on('click', async function () {
            await self.#buscarPagamentos();
            // commonFunctions.generateNotification('Dados atualizados com sucesso.', 'success');
        });

        self.#functionsParticipacao._buscarPresetParticipacaoTenant();

        QuillEditorHelper.init(`#descricao${self.getSufixo}`); // Usando string seletora
    }

    async #inserirCliente(item) {
        const self = this;
        const divClientes = $(`#divClientes${self._objConfigs.sufixo}`);
        item.idCard = UUIDHelper.generateUUID();

        let nome = '';

        const naTela = self.#verificaClienteNaTela(item);

        switch (item.perfil.pessoa.pessoa_dados_type) {
            case window.Enums.PessoaTipoEnum.PESSOA_FISICA:
                nome = item.perfil.pessoa.pessoa_dados.nome;
                break;
            case window.Enums.PessoaTipoEnum.PESSOA_JURIDICA:
                nome = item.perfil.pessoa.pessoa_dados.nome_fantasia;
                break;

            default:
                commonFunctions.generateNotification(`O tipo de pessoa <b>${item.perfil.pessoa.pessoa_dados_type}</b> ainda não foi implementado.`, 'warning');
                console.error('O tipo de pessoa ainda nao foi implementado.', item);
                return false;
        }

        if (naTela) {
            commonFunctions.generateNotification(`Cliente <b>${nome}</b> já foi inserido(a) para este tipo de participação.`, 'error');
            return false;
        }

        const strCard = `
            <div id="${item.idCard}" class="card card-cliente">
                <div class="card-body">
                    <h5 class="card-title d-flex align-items-center justify-content-between">
                        <span class="spanNome">${nome}</span>
                        <div>
                            <div class="dropdown dropdown-acoes-cliente">
                                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><button type="button" class="dropdown-item fs-6 btn-delete">Excluir</button></li>
                                </ul>
                            </div>
                        </div>
                    </h5>
                </div>
            </div>`;

        self.inserirClienteNaTela(item);
        divClientes.append(strCard);

        await self.#addEventoCliente(item);
        return item;
    }

    async #addEventoCliente(item) {
        const self = this;

        $(`#${item.idCard} .btn-delete`).on('click', async function () {
            try {
                const obj = new modalMessage();
                obj.setDataEnvModal = {
                    title: 'Remoção de Cliente',
                    message: 'Tem certeza que deseja remover este cliente?',
                };
                obj.setFocusElementWhenClosingModal = $(this);
                const result = await obj.modalOpen();
                if (result.confirmResult) {

                    $(`#${item.idCard}`).remove();
                    const clientes = self._objConfigs.data.clientesNaTela;
                    const indexPart = clientes.findIndex(cliente => cliente.idCard === item.idCard);

                    if (indexPart > -1) {
                        clientes.splice(indexPart, 1);
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            }
        });
    }

    #verificaClienteNaTela(item) {
        const self = this;
        for (const element of self._objConfigs.data.clientesNaTela) {
            if (element.perfil_id == item.perfil_id) {
                return element;
            }
        }
        return null;
    }

    inserirClienteNaTela(item) {
        const self = this;
        if (!self._objConfigs.data?.clientesNaTela) {
            self._objConfigs.data.clientesNaTela = []
        }
        self._objConfigs.data.clientesNaTela.push(item);
    }

    #limparClientes() {
        const self = this;
        self._objConfigs.data.clientesNaTela = [];
        $(`#divClientes${self._objConfigs.sufixo}`).html('');
    }

    async #buscarClientes() {
        const self = this;
        try {
            await commonFunctions.loadingModalDisplay(true, { message: 'Carregando clientes...' });
            const obj = new connectAjax(self._objConfigs.url.baseCliente);
            const response = await obj.getRequest();
            if (response.data) {
                self.#limparClientes();
                response.data.map(item => {
                    self.#inserirCliente(item);
                })
                commonFunctions.generateNotification('Clientes atualizados com sucesso.', 'success');
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
    }

    async #inserirAnotacao(item) {
        const self = this;
        const divAnotacao = $(`#divAnotacao${self._objConfigs.sufixo}`);

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
                            <div class="card-text overflow-auto scrollbar text-start" style="max-height: 10rem;">
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
                const objModal = new modalAnotacaoLembreteTenant(self._objConfigs.url.baseAnotacao);
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
            const response = await self._delButtonAction(item.id, item.titulo, {
                title: `Exclusão de Anotação`,
                message: `Confirma a exclusão da anotação <b>${item.titulo}</b>?`,
                success: `Anotação excluída com sucesso!`,
                button: this,
                url: self._objConfigs.url.baseAnotacao,
            });

            if (response) {
                $(`#${item.idCol}`).remove();
            }
        });
    }

    async #inserirPagamento(item) {
        const self = this;
        const divPagamento = $(`#divPagamento${self._objConfigs.sufixo}`);

        item.idCard = `${UUIDHelper.generateUUID()}${self._objConfigs.sufixo}`;
        const created_at = `<span class="text-body-secondary d-block">Pagamento cadastrado em ${DateTimeHelper.retornaDadosDataHora(item.created_at, 12)}</span>`;

        let htmlColsEspecifico = self.#htmlColsEspecificosPagamento(item);
        let htmlAppend = self.#htmlParticipantes(item, 'pagamento', item.status_id);
        htmlAppend += self.#htmlAppendPagamento(item);
        let htmlLancamentos = self.#htmlLancamentos(item);
        if (htmlLancamentos) {
            htmlLancamentos = `
                <div class="accordion mt-2" id="accordionPagamento${item.id}">
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
                            <div class="accordion-body d-flex flex-column gap-2">
                                ${htmlLancamentos}
                            </div>
                        </div>
                    </div>
                </div>`;
        }
        const pagamentoAtivo = item.status_id == window.Enums.PagamentoStatusTipoEnum.ATIVO ? true : false;
        const tachado = (window.Statics.StatusPagamentoTachado.findIndex(x => x == item.status_id) != -1);

        let strCard = `
            <div id="${item.idCard}" class="card p-0">
                <div class="card-body">
                    <div class="row ${tachado ? 'fst-italic text-secondary-emphasis text-decoration-line-through' : ''}">
                        <h5 class="card-title d-flex align-items-center justify-content-between">
                            <span class="text-truncate">
                                <span title="Número do pagamento">N.P.</span> ${item.numero_pagamento} - ${item.pagamento_tipo_tenant.nome}</span>
                            <div>
                                <div class="dropdown">
                                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><button type="button" class="dropdown-item fs-6 btn-participacao-pagamento ${pagamentoAtivo ? '' : 'disabled'}" title="Inserir/Editar Participação ${item.pagamento_tipo_tenant.nome}">Participação</button></li>
                                        <li><button type="button" class="dropdown-item fs-6 btn-edit" title="Editar pagamento">Editar</button></li>
                                        <li><button type="button" class="dropdown-item fs-6 btn-delete" title="Excluir pagamento ${item.pagamento_tipo_tenant.nome}">Excluir</button></li>
                                    </ul>
                                </div>
                            </div>
                        </h5>
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 align-items-end">
                            ${htmlColsEspecifico}
                        </div>
                        ${htmlAppend}
                    </div>
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
        if (item?.status) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Status</div>
                    <p class="text-truncate" title="${item.status?.descricao ?? ''}">${item.status.nome}</p>
                </div>`;
        }

        if (item?.forma_pagamento) {
            htmlColsEspecifico += `
                <div class="col">
                    <div class="form-text mt-0">Forma de Pagamento Padrão</div>
                    <p class="text-truncate">${item.forma_pagamento.nome}</p>
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

            const data_vencimento = DateTimeHelper.retornaDadosDataHora(lancamento.data_vencimento, 2);
            const valor_esperado = commonFunctions.formatWithCurrencyCommasOrFraction(lancamento.valor_esperado);
            const title_forma_pagamento = lancamento.forma_pagamento?.nome ?? 'Forma de Pagamento Padrão do Pagamento';
            const nome_forma_pagamento = lancamento.forma_pagamento?.nome ?? `<i>${title_forma_pagamento}</i>`;
            const pagamentoAtivo = item.status_id == window.Enums.PagamentoStatusTipoEnum.ATIVO ? true : false;

            let editParticipante = true;
            if (window.Statics.StatusImpossibilitaEdicaoLancamentoServico.findIndex(x => x == item.status_id) != -1) {
                editParticipante = false;
            }

            const tachado = (window.Statics.StatusLancamentoTachado.findIndex(x => x == lancamento.status_id) != -1);
            lancamento.idCard = `${UUIDHelper.generateUUID()}${self._objConfigs.sufixo}`;

            let htmlAppend = '';

            if (lancamento.observacao) {
                htmlAppend += `
                <div class="row">
                    <div class="col">
                        <div class="form-text mt-0">Observação</div>
                        <p class="text-truncate text-wrap" title="${lancamento.observacao}">${lancamento.observacao}</p>
                    </div>
                </div>`;
            }

            if (!tachado) htmlAppend += self.#htmlParticipantes(lancamento, 'lancamento', item.status_id);

            htmlLancamentos += `
                <div id="${lancamento.idCard}" class="card p-0 ${tachado ? 'fst-italic text-secondary-emphasis text-decoration-line-through' : ''}">
                    <div class="card-header d-flex align-items-center justify-content-between py-1">
                        <span>${lancamento.descricao_automatica}</span>
                        <div>
                            <div class="dropdown">
                                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><button type="button" class="dropdown-item fs-6 btn-participacao-lancamento ${pagamentoAtivo && editParticipante && !tachado ? '' : 'disabled'}" title="Inserir/Editar Participação ${lancamento.descricao_automatica}">Participação</button></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 align-items-end">
                            <div class="col">
                                <div class="form-text mt-0">Data de vencimento</div>
                                <p>${data_vencimento}</p>
                            </div>
                            <div class="col">
                                <div class="form-text mt-0">Valor</div>
                                <p>${valor_esperado}</p>
                            </div>
                            <div class="col">
                                <div class="form-text mt-0">Status</div>
                                <p>${lancamento.status.nome}</p>
                            </div>
                            <div class="col">
                                <div class="form-text mt-0">Forma de Pagamento</div>
                                <p class="text-truncate" title="${title_forma_pagamento}">
                                    ${nome_forma_pagamento}
                                </p>
                            </div>
                        </div>
                        ${htmlAppend}
                    </div>
                </div>`
        }

        return htmlLancamentos;
    }

    #htmlParticipantes(item, tipo, pagamentoStatusId) {
        let html = '';

        let title = ''
        let empty = '';
        let btnDel = '';

        const pagamentoAtivo = pagamentoStatusId == window.Enums.PagamentoStatusTipoEnum.ATIVO ? true : false;

        let editParticipante = true;
        if (window.Statics.StatusImpossibilitaEdicaoLancamentoServico.findIndex(x => x == item.status_id) != -1) {
            editParticipante = false;
        }

        switch (tipo) {
            case 'pagamento':
                title = `Participantes do pagamento ${item.pagamento_tipo_tenant.nome}`;
                empty = 'Participante(s) herdado do serviço';
                btnDel = 'btn-delete-participante-pagamento';
                break;

            case 'lancamento':
                title = `Participantes do lançamento ${item.descricao_automatica}`;
                empty = 'Participante(s) herdado do pagamento';
                btnDel = 'btn-delete-participante-lancamento';
                break;
        }

        if (editParticipante && pagamentoAtivo) {
            btnDel = `<button type="button" class="btn btn-sm btn-outline-danger border-0 ${btnDel}">Excluir</button>`;
        } else {
            btnDel = '';
        }

        if (item?.participantes && item.participantes.length > 0) {
            const arrays = ParticipacaoHelpers.htmlRenderParticipantesEIntegrantes(item.participantes);
            html = `
                <p class="mb-0">
                Participação personalizada:
                ${btnDel}
                <button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="${title}" data-bs-html="true" data-bs-content="${arrays.arrayParticipantes.join("<hr class='my-1'>")}">Ver</button>
                <button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Integrantes de Grupos" data-bs-html="true" data-bs-content="${arrays.arrayIntegrantes.join("<hr class='my-1'>")}">Ver integrantes dos grupos</button>
                </p>`;
        } else {
            html = `
                <p class="mb-0 fst-italic">${empty}</p>`;
        }
        return html;
    }

    async #addEventosPagamento(item) {
        const self = this;

        $(`#${item.idCard}`).find('.btn-edit').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalServicoPagamento({ urlApi: self._objConfigs.url.basePagamentos });
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
            const response = await self._delButtonAction(item.id, item.pagamento_tipo_tenant.nome, {
                title: `Exclusão de Pagamento`,
                message: `Confirma a exclusão do pagamento <b>${item.pagamento_tipo_tenant.nome}</b>?`,
                success: `Pagamento excluído com sucesso!`,
                button: this,
                url: self._objConfigs.url.basePagamentos,
            });

            if (response) {
                self.#buscarPagamentos();
            }
        });

        $(`#${item.idCard}`).find('.btn-participacao-pagamento').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalParticipacao({
                    urlApi: `${self._objConfigs.url.basePagamentos}/${item.id}/participacao`
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

        $(`#${item.idCard}`).find(`.btn-delete-participante-pagamento`).on('click', async function () {

            const response = await self._delButtonAction(`${item.id}/participacao`, item.pagamento_tipo_tenant.nome, {
                title: `Exclusão de Participantes`,
                message: `Confirma a exclusão do(s) participante(s) personalizado(s) do pagamento <b>${item.pagamento_tipo_tenant.nome}</b>?`,
                success: `Participantes excluídos com sucesso!`,
                button: this,
                url: `${self._objConfigs.url.basePagamentos}`,
            });

            if (response) {
                self.#buscarPagamentos();
            }
        });

        await self.#addEventosLancamento(item);
    }

    async #addEventosLancamento(item) {
        const self = this;
        const accordionBody = $(`#accordionPagamento${item.id} .accordion-body`);
        const urlLancamentos = `${self._objConfigs.url.basePagamentos}/${item.id}/lancamentos`;

        const atualizaLancamentos = async () => {
            try {
                const response = await self._getRecurse({ idRegister: item.id, urlApi: self._objConfigs.url.basePagamentos });
                const htmlLancamentos = self.#htmlLancamentos(response.data);
                accordionBody.html(htmlLancamentos);
                BootstrapFunctionsHelper.addEventPopover();
                await self.#addEventosLancamento(response.data);
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            }
        }

        const pagamentoAtivo = item.status_id == window.Enums.PagamentoStatusTipoEnum.ATIVO ? true : false;
        if (pagamentoAtivo) {
            item.lancamentos.map((lancamento) => {

                $(`#${lancamento.idCard}`).find('.btn-participacao-lancamento').on('click', async function () {
                    const btn = $(this);
                    commonFunctions.simulateLoading(btn);
                    try {
                        const objModal = new modalParticipacao({
                            urlApi: `${urlLancamentos}/${lancamento.id}/participacao`
                        });
                        const response = await objModal.modalOpen();
                        if (response.refresh && response.registers) {
                            atualizaLancamentos();
                        }
                    } catch (error) {
                        commonFunctions.generateNotificationErrorCatch(error);
                    } finally {
                        commonFunctions.simulateLoading(btn, false);
                    }
                });

                $(`#${lancamento.idCard}`).find(`.btn-delete-participante-lancamento`).on('click', async function () {

                    const response = await self._delButtonAction(`${lancamento.id}/participacao`, lancamento.descricao_automatica, {
                        title: `Exclusão de Participantes`,
                        message: `Confirma a exclusão do(s) participante(s) personalizado(s) do lançamento <b>${lancamento.descricao_automatica}</b>?`,
                        success: `Participantes excluídos com sucesso!`,
                        button: this,
                        url: urlLancamentos
                    });
                    if (response) {
                        atualizaLancamentos();
                    }
                });

            });
        }
    }

    async preenchimentoDados(response, options) {
        const self = this;
        const form = $(options.form);

        const responseData = response.data;
        form.find('input[name="titulo"]').val(responseData.titulo);
        self.#buscarAreasJuridicas(responseData.area_juridica_id);
        form.find('textarea[name="descricao"]').val(responseData.descricao);

        $(`#divAnotacao${self._objConfigs.sufixo}`).html('');
        responseData.anotacao.forEach(item => {
            self.#inserirAnotacao(item);
        });

        $(`#divPagamento${self._objConfigs.sufixo}`).html('');
        responseData.pagamento.forEach(item => {
            self.#inserirPagamento(item);
        });

        self.#limparClientes();
        responseData.cliente.forEach(item => {
            self.#inserirCliente(item);
        });

        self.#functionsParticipacao._inserirParticipantesEIntegrantes(responseData.participantes);

        self.#atualizaTodosValores(response.data);
    }

    #atualizaTodosValores(data) {
        const self = this;
        self.#atualizarValorServico(data.valor_servico);
        self.#atualizarTotalAguardando(data.total_aguardando);
        self.#atualizarTotalEmAnalise(data.total_em_analise);
        self.#atualizarTotalLiquidado(data.total_liquidado);
        self.#atualizarTotalInadimplente(data.total_inadimplente);
    }

    #atualizarValorServico(valor) {
        const self = this;
        $(`#valorServico${self._objConfigs.sufixo}`).html(commonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    }

    #atualizarTotalAguardando(valor) {
        const self = this;
        $(`#totalAguardando${self._objConfigs.sufixo}`).html(commonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    }

    #atualizarTotalEmAnalise(valor) {
        const self = this;
        $(`#totalEmAnalise${self._objConfigs.sufixo}`).html(commonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    }

    #atualizarTotalLiquidado(valor) {
        const self = this;
        $(`#totalLiquidado${self._objConfigs.sufixo}`).html(commonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    }

    #atualizarTotalInadimplente(valor) {
        const self = this;
        $(`#totalInadimplente${self._objConfigs.sufixo}`).html(commonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    }

    async #buscarAreasJuridicas(selected_id = null) {
        try {
            const self = this;
            let options = selected_id ? { selectedIdOption: selected_id } : {};
            const selector = $(`#area_juridica_id${self._objConfigs.sufixo}`);
            await commonFunctions.fillSelect(selector, self._objConfigs.url.baseAreaJuridicaTenant, options); 0
            return true
        } catch (error) {
            return false;
        }
    }

    async #buscarPagamentos() {
        const self = this;
        try {
            await commonFunctions.loadingModalDisplay(true, { message: 'Carregando pagamentos...' });

            const obj = new connectAjax(self._objConfigs.url.basePagamentos);
            const response = await obj.getRequest();
            $(`#divPagamento${self._objConfigs.sufixo}`).html('');
            for (const item of response.data) {
                self.#inserirPagamento(item);
            }
            await self.#buscarValores();
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }

    }

    async #buscarValores() {
        const self = this;
        try {
            const obj = new connectAjax(self._objConfigs.url.baseValores);
            const response = await obj.getRequest();
            self.#atualizaTodosValores(response.data);
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(`#form${self._objConfigs.sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        const conteudoHTML = quill.root.innerHTML;
        console.log(conteudoHTML);
        data.descricao = conteudoHTML;

        if (self.#saveVerifications(data, formRegistration)) {
            self._save(data, self._objConfigs.url.base, {
                success: 'Serviço cadastrado com sucesso!',
                redirectWithIdBln: true,
            });
        }
        return false;
    }

    #saveVerifications(data, formRegistration) {
        const self = this;
        if (self._action == enumAction.POST) {
            let blnSave = commonFunctions.verificationData(data.titulo, { field: formRegistration.find('input[name="titulo"]'), messageInvalid: 'O título deve ser informado.', setFocus: true });
            blnSave = commonFunctions.verificationData(data.area_juridica_id, { field: formRegistration.find('select[name="area_juridica_id"]'), messageInvalid: 'A Área Jurídica deve ser selecionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            // blnSave = commonFunctions.verificationData(data.descricao, { field: formRegistration.find('textarea[name="descricao"]'), messageInvalid: 'A descrição deve ser preenchida.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            return blnSave;
        }
        return true;
    }

    async #saveButtonActionCliente() {
        const self = this;
        let data = {
            clientes: self._objConfigs.data.clientesNaTela.map(item => {
                let obj = { perfil_id: item.perfil_id };
                item.id ? obj.id = item.id : null;
                return obj;
            }),
        }

        if (data.clientes.length) {
            const response = await self._save(data, self._objConfigs.url.baseCliente, {
                action: enumAction.POST,
                btnSave: $(`#btnSaveClientes${self._objConfigs.sufixo}`),
                redirectBln: false,
                returnObjectSuccess: true,
            });
            if (response) {
                self.#limparClientes();
                response.data.map(item => { self.#inserirCliente(item); });
            }
        }
    }

    async #saveButtonActionParticipacao() {
        const self = this;
        let data = {
            participantes: self._objConfigs.data.participantesNaTela,
        }

        if (self.#functionsParticipacao._saveVerificationsParticipantes(data)) {
            const response = await self._save(data, self._objConfigs.url.baseParticipacao, {
                action: enumAction.POST,
                btnSave: $(`#btnSaveParticipantes${self._objConfigs.sufixo}`),
                redirectBln: false,
                returnObjectSuccess: true,
            });
            if (response) {
                self.#functionsParticipacao._inserirParticipantesEIntegrantes(response.data);
            }
        }
    }
}

$(function () {
    new PageServicoForm();
});