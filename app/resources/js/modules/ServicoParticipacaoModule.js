import { commonFunctions } from "../commons/commonFunctions";
import { connectAjax } from "../commons/connectAjax";
import { modalMessage } from "../components/comum/modalMessage";
import { modalNome } from "../components/comum/modalNome";
import { modalPessoa } from "../components/pessoas/modalPessoa";
import { modalServicoParticipacaoParticipante } from "../components/servico/modalServicoParticipacaoParticipante";
import { RequestsHelpers } from "../helpers/RequestsHelpers";
import { UUIDHelper } from "../helpers/UUIDHelper";

export class ServicoParticipacaoModule {

    _objConfigs;
    _parentInstance;
    _extraConfigs;

    constructor(parentInstance, objData) {
        this._objConfigs = objData.objConfigs;
        this.parentInstance = parentInstance;
        this._extraConfigs = objData.extraConfigs;
        this.#addEventosBotoes();
    }

    #addEventosBotoes() {
        const self = this;

        const openModalServicoParticipacao = async (dados_participacao) => {
            const objModal = new modalServicoParticipacaoParticipante();
            objModal.setDataEnvModal = {
                dados_participacao: dados_participacao,
                porcentagem_ocupada: self._objConfigs.data.porcentagem_ocupada,
            }
            const response = await objModal.modalOpen();
            if (response.refresh) {
                await self._inserirParticipanteNaTela(Object.assign(dados_participacao, response.register));
            }
        }

        $(`#btnInserirPessoa${self._objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const dataEnvModalAppend = {
                    perfis_busca: window.Statics.PerfisPermitidoParticipacaoServico,
                };
                const objModal = new modalPessoa({ dataEnvModal: dataEnvModalAppend });
                if (self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow(false);
                const response = await objModal.modalOpen();
                if (response.refresh && response.selected) {
                    await openModalServicoParticipacao({
                        participacao_registro_tipo_id: window.Enums.ParticipacaoRegistroTipoEnum.PERFIL, referencia: response.selected,
                        referencia_id: response.selected.id,
                    });
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
                if (self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow();
            }
        });

        $(`#btnInserirGrupo${self._objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModalNome = new modalNome();
                objModalNome.setDataEnvModal = {
                    title: 'Novo grupo',
                    mensagem: 'Informe o nome do grupo',
                }
                if (self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow(false);
                const response = await objModalNome.modalOpen();
                if (response.refresh) {
                    await openModalServicoParticipacao({
                        participacao_registro_tipo_id: window.Enums.ParticipacaoRegistroTipoEnum.GRUPO,
                        nome_grupo: response.name
                    });
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
                if (self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow();
            }
        });

        const modeParent = self._extraConfigs?.modeParent ?? 'preset';
        switch (modeParent) {
            case 'searchAndUse':

                $(`#preset_id${self._objConfigs.sufixo}`).on('change', async function () {
                    let blnInserir = false;
                    const preset_id = $(this).val();
                    const divParticipantes = $(`#divParticipantes${self._objConfigs.sufixo}`);

                    const inserirPreset = async (hideShowModal) => {
                        try {
                            if (hideShowModal && self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow(false);
                            await commonFunctions.loadingModalDisplay(true, { message: 'Carregando informações do preset...' });

                            const response = await RequestsHelpers.getRecurse({
                                urlApi: self._objConfigs.url.baseParticipacaoPreset,
                                idRegister: preset_id
                            });
                            if (response.data) {
                                self._objConfigs.data.participantesNaTela = [];
                                divParticipantes.html('');
                                self._atualizaPorcentagemLivre();
                                const participantes = response.data.participantes.map(participante => {
                                    delete participante.id;
                                    delete participante.parent_type;
                                    delete participante.parent_id;
                                    participante.integrantes = participante.integrantes.map(integrante => {
                                        delete integrante.id;
                                        delete integrante.parent_type;
                                        delete integrante.parent_id;
                                        return integrante;
                                    });
                                    return participante;
                                });
                                self._inserirParticipantesEIntegrantes(participantes);
                            }

                        } catch (error) {
                            commonFunctions.generateNotificationErrorCatch(error);
                        } finally {
                            $(this).val(0);
                            await commonFunctions.loadingModalDisplay(false);
                            if (self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow();
                        }
                    }

                    if (UUIDHelper.isValidUUID(preset_id)) {
                        if (divParticipantes.children().length > 0) {
                            try {
                                const obj = new modalMessage();
                                obj.setDataEnvModal = {
                                    title: 'Inserção de Preset',
                                    message: 'A inserção deste preset limpará todos os participantes atuais. Confirma esta ação?',
                                };
                                obj.setFocusElementWhenClosingModal = $(this);
                                if (self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow(false);
                                const result = await obj.modalOpen();
                                if (result.confirmResult) {
                                    blnInserir = true;
                                    await inserirPreset(false);
                                }
                            } catch (error) {
                                commonFunctions.generateNotificationErrorCatch(error);
                            } finally {
                                if (!blnInserir && self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow(false);
                            }
                        } else {
                            await inserirPreset(true);
                        }
                    } else {
                        if (preset_id != '0') commonFunctions.generateNotification('O ID do preset é inválido.', 'error');
                    }
                });
                break;

            default:
                break;
        }
    }

    async #buscarParticipacaoTipo(id) {
        const self = this;
        return await self.#getRecurse({ idRegister: id, urlApi: self._objConfigs.url.baseParticipacaoTipo });
    }

    async _inserirParticipanteNaTela(item) {
        const self = this;
        const divParticipantes = $(`#divParticipantes${self._objConfigs.sufixo}`);
        item.idCard = UUIDHelper.generateUUID();

        let nome = '';
        let btnsAppend = '';
        let accordionIntegrantes = '';
        let displayObservacao = 'none';

        const naTela = self.#verificaRegistroNaTela(item);

        switch (item.participacao_registro_tipo_id) {
            case window.Enums.ParticipacaoRegistroTipoEnum.PERFIL:
                nome = item.referencia.pessoa.pessoa_dados.nome;
                if (naTela) {
                    commonFunctions.generateNotification(`Participante <b>${nome}</b> já foi inserido(a) para este tipo de participação.`, 'error');
                    return false;
                }
                break;
            case window.Enums.ParticipacaoRegistroTipoEnum.GRUPO:
                nome = item.nome_grupo;
                if (naTela) {
                    commonFunctions.generateNotification(`O Grupo <b>${nome}</b> já foi inserido. O nome foi alterado para <b>${nome} (Alterar)</b>.<br>Altere o nome do grupo posteriormente.`, 'warning');
                    item.nome_grupo = `${nome} (Alterar)`;
                    nome = item.nome_grupo;
                }
                btnsAppend += `<li><button type="button" class="dropdown-item fs-6 btn-add-pessoa">Inserir Pessoa</button></li>`;
                btnsAppend += `<li><button type="button" class="dropdown-item fs-6 btn-edit-name">Editar Nome</button></li>`;
                accordionIntegrantes = self.#accordionIntegrantesGrupo(item);
                break;
            default:
                commonFunctions.generateNotification('Tipo de registro de participação não informado.', 'error');
                console.error('Tipo de registro de participação não informado.', item);
                return false;
        }

        let participacao_tipo = item.participacao_tipo ?? null;
        if (!participacao_tipo) {
            if (item.participacao_tipo_id) {
                const response = await self.#buscarParticipacaoTipo(item.participacao_tipo_id);
                if (response) {
                    participacao_tipo = response.data;
                } else {
                    participacao_tipo = { nome: 'Erro de busca' }
                }
            } else {
                commonFunctions.generateNotification('Tipo de participação não informado.', 'error');
                console.error('Tipo de participação não informado.', item);
                return false;
            }
        }

        if (item.observacao) {
            displayObservacao = 'block';
        }

        let valor_tipo = ''
        let valor = commonFunctions.formatWithCurrencyCommasOrFraction(item.valor);
        switch (item.valor_tipo) {
            case 'porcentagem':
                valor_tipo = 'Porcentagem';
                valor += '%';
                break;
            case 'valor_fixo':
                valor_tipo = 'Valor Fixo';
                valor = `R$ ${valor}`;
                break;
            default:
                valor_tipo = 'Erro valor tipo';
                console.error('Erro no tipo de valor', item);
                break;
        }

        const strCard = `
            <div class="card-body">
                <h5 class="card-title d-flex align-items-center justify-content-between">
                    <span class="spanNome">${nome}</span>
                    <div>
                        <div class="dropdown">
                            <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                ${btnsAppend}
                                <li><button type="button" class="dropdown-item fs-6 btn-edit">Editar</button></li>
                                <li><button type="button" class="dropdown-item fs-6 btn-delete">Excluir</button></li>
                            </ul>
                        </div>
                    </div>
                </h5>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4">
                    <div class="col">
                        <div class="form-text">Participação</div>
                        <label class="form-label text-truncate lblParticipacao">${participacao_tipo.nome}</label>
                    </div>
                    <div class="col">
                        <div class="form-text">Método</div>
                        <label class="form-label lblValorTipo">${valor_tipo}</label>
                    </div>
                    <div class="col">
                        <div class="form-text">Valor</div>
                        <label class="form-label text-truncate lblValor">${valor}</label>
                    </div>
                </div>
                <div class="row rowObservacao" style="display: ${displayObservacao};">
                    <div class="col">
                        <div class="form-text">Observação</div>
                        <label class="form-label text-truncate lblObservacao" title="${item.observacao ?? ''}">${item.observacao ?? ''}</label>
                    </div>
                </div>
                ${accordionIntegrantes}
            </div>`;

        self._objConfigs.data.participantesNaTela.push(item);

        divParticipantes.append(`<div id="${item.idCard}" class="card">${strCard}</div>`);

        self.#addEventoParticipante(item);
        await self._atualizaPorcentagemLivre(item);
        return item;
    }

    async #atualizaParticipanteNaTela(item) {
        const self = this;

        let participacao_tipo = item.participacao_tipo ?? null;
        if (!participacao_tipo || participacao_tipo && (participacao_tipo.id != item.participacao_tipo_id)) {
            if (item.participacao_tipo_id) {
                const response = await self.#buscarParticipacaoTipo(item.participacao_tipo_id);
                if (response) {
                    participacao_tipo = response.data;
                } else {
                    participacao_tipo = { nome: 'Erro de busca' }
                }
            } else {
                commonFunctions.generateNotification('Tipo de participação não informado.', 'error');
                console.error('Tipo de participação não informado.', item);
                return false;
            }
        }

        let valor_tipo = ''
        let valor = commonFunctions.formatWithCurrencyCommasOrFraction(item.valor);
        switch (item.valor_tipo) {
            case 'porcentagem':
                valor_tipo = 'Porcentagem';
                valor += '%';
                break;
            case 'valor_fixo':
                valor_tipo = 'Valor Fixo';
                valor = `R$ ${valor}`;
                break;
            default:
                valor_tipo = 'Erro valor tipo';
                console.error('Erro no tipo de valor', item);
                break;
        }

        for (const element of self._objConfigs.data.participantesNaTela) {
            if (element.idCard == item.idCard) {
                element.participacao_tipo_id = item.participacao_tipo_id;
                element.valor_tipo = item.valor_tipo;
                element.valor = item.valor;
                break;
            }
        }

        $(`#${item.idCard} .lblParticipacao`).html(participacao_tipo.nome);
        $(`#${item.idCard} .lblValorTipo`).html(valor_tipo);
        $(`#${item.idCard} .lblValor`).html(valor);
        if (item.observacao) {
            $(`#${item.idCard} .lblObservacao`).html(item.observacao);
            $(`#${item.idCard} .rowObservacao`).show('fast');
        } else {
            $(`#${item.idCard} .rowObservacao`).hide('fast');
        }
        await self._atualizaPorcentagemLivre();
    }

    async #addEventoParticipante(item) {
        const self = this;

        $(`#${item.idCard} .btn-edit`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                let porcentagem_ocupada = self._objConfigs.data.porcentagem_ocupada;
                if (item.valor_tipo == 'porcentagem') {
                    porcentagem_ocupada -= item.valor;
                }
                const objModal = new modalServicoParticipacaoParticipante();
                objModal.setDataEnvModal = {
                    dados_participacao: item,
                    porcentagem_ocupada: porcentagem_ocupada,
                }
                if (self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow(false);
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    await self.#atualizaParticipanteNaTela(Object.assign(item, response.register));
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
                if (self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow();
            }
        });

        $(`#${item.idCard} .btn-delete`).on('click', async function () {
            try {
                const obj = new modalMessage();
                obj.setDataEnvModal = {
                    title: 'Remoção de Participante',
                    message: 'Tem certeza que deseja remover este participante?',
                };
                obj.setFocusElementWhenClosingModal = $(this);
                if (self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow(false);
                const result = await obj.modalOpen();
                if (result.confirmResult) {

                    $(`#${item.idCard}`).remove();
                    const participantes = self._objConfigs.data.participantesNaTela;
                    const indexPart = participantes.findIndex(participante => participante.idCard === item.idCard);

                    if (indexPart > -1) {
                        participantes.splice(indexPart, 1);
                    }

                    self._atualizaPorcentagemLivre();
                    commonFunctions.generateNotification('Participante removido.', 'success');
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                if (self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow();
            }
        });

        if (item.participacao_registro_tipo_id == window.Enums.ParticipacaoRegistroTipoEnum.GRUPO) {

            $(`#${item.idCard} .btn-edit-name`).on('click', async function () {

                let registro = undefined;
                for (const element of self._objConfigs.data.participantesNaTela) {
                    if (element.idCard == item.idCard) {
                        registro = element;
                        break;
                    }
                }
                const btn = $(this);
                commonFunctions.simulateLoading(btn);
                try {
                    const objModalNome = new modalNome();
                    objModalNome.setDataEnvModal = {
                        title: 'Novo grupo',
                        mensagem: 'Informe o nome do grupo',
                        nome: registro.nome_grupo,
                    }
                    if (self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow(false);
                    const response = await objModalNome.modalOpen();
                    if (response.refresh) {
                        registro.nome_grupo = response.name;
                        $(`#${item.idCard} .spanNome`).html(registro.nome_grupo);
                    }
                } catch (error) {
                    commonFunctions.generateNotificationErrorCatch(error);
                } finally {
                    commonFunctions.simulateLoading(btn, false);
                    if (self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow();
                }
            });

            $(`#${item.idCard} .btn-add-pessoa`).on('click', async function () {
                const btn = $(this);
                commonFunctions.simulateLoading(btn);
                try {
                    // const dataEnvModalAppend = {
                    //     perfis_busca: window.Statics.PerfisPermitidoParticipacaoServico,
                    // };
                    // const objModal = new modalPessoa({ dataEnvModal: dataEnvModalAppend });
                    const objModal = new modalPessoa();
                    objModal.setDataEnvModal = {
                        perfis_busca: window.Statics.PerfisPermitidoParticipacaoServico,
                    }
                    if (self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow(false);
                    const response = await objModal.modalOpen();
                    if (response.refresh && response.selecteds) {
                        await Promise.all(
                            response.selecteds.map(async (integrante) => {
                                await self._inserirIntegrante(item, {
                                    participacao_registro_tipo_id: window.Enums.ParticipacaoRegistroTipoEnum.PERFIL,
                                    referencia: integrante,
                                    referencia_id: integrante.id,
                                });
                            })
                        );
                    }
                } catch (error) {
                    commonFunctions.generateNotificationErrorCatch(error);
                } finally {
                    commonFunctions.simulateLoading(btn, false);
                    if (self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow();
                }
            });
        }
    }

    #verificaRegistroNaTela(item) {
        const self = this;

        // if (item.participacao_registro_tipo_id == window.Enums.ParticipacaoRegistroTipoEnum.PERFIL) {
        //     for (const element of self._objConfigs.data.participantesNaTela) {
        //         if (element.participacao_registro_tipo_id != window.Enums.ParticipacaoRegistroTipoEnum.PERFIL) {
        //             continue;
        //         }

        //         if (element.referencia.id == item.referencia.id &&
        //             element.participacao_tipo_id == item.participacao_tipo_id
        //         ) {
        //             return element;
        //         }
        //     }
        // }

        for (const element of self._objConfigs.data.participantesNaTela) {
            if (element.participacao_registro_tipo_id == item.participacao_registro_tipo_id
                && (
                    (element.participacao_tipo_id == item.participacao_tipo_id
                        && (item.participacao_registro_tipo_id == window.Enums.ParticipacaoRegistroTipoEnum.PERFIL
                            && element.referencia.id == item.referencia.id))
                    || (item.participacao_registro_tipo_id == window.Enums.ParticipacaoRegistroTipoEnum.GRUPO
                        && element.nome_grupo == item.nome_grupo))
            ) {
                return element;
            }
        }
        return null;
    }

    async _buscarParticipantes() {
        const self = this;
        try {
            const obj = new connectAjax(self._objConfigs.url.baseParticipacao);
            const response = await obj.getRequest();
            if (response.data) {
                self._inserirParticipantesEIntegrantes(response.data);
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
    }

    async _atualizaPorcentagemLivre() {
        const self = this;
        let porcentagemOcupada = 0;
        let valorFixo = 0;

        for (const itemTela of self._objConfigs.data.participantesNaTela) {
            if (itemTela.valor_tipo == 'porcentagem') {
                porcentagemOcupada += itemTela.valor;
            } else {
                valorFixo += itemTela.valor;
            }
        }
        self._objConfigs.data.porcentagem_ocupada = porcentagemOcupada;
        self._objConfigs.data.valor_fixo = valorFixo;

        let valorMinimo = 0;
        if (porcentagemOcupada > 0 && valorFixo > 0) {
            valorMinimo = valorFixo + 1;
        } else if (valorFixo > 0) {
            valorMinimo = valorFixo;
        }

        $(`#valor_fixo${self._objConfigs.sufixo}`).html(`${commonFunctions.formatWithCurrencyCommasOrFraction(valorFixo)}`);
        $(`#porcentagem${self._objConfigs.sufixo}`).html(`${commonFunctions.formatWithCurrencyCommasOrFraction(porcentagemOcupada)}`);
        $(`#valor_minimo${self._objConfigs.sufixo}`).html(`${commonFunctions.formatWithCurrencyCommasOrFraction(valorMinimo)}`);

        commonFunctions.atualizarProgressBar($(`#progressBar${self._objConfigs.sufixo}`), porcentagemOcupada);
    }

    async _inserirIntegrante(item, integrante) {
        const self = this;
        const rowIntegrantes = $(`#accordionIntegrantes${item.idCard} .rowIntegrantes`);
        integrante.idCard = UUIDHelper.generateUUID();

        let nome = '';
        let tipoReferencia = '';
        switch (integrante.participacao_registro_tipo_id) {
            case window.Enums.ParticipacaoRegistroTipoEnum.PERFIL:
                nome = integrante.referencia.pessoa.pessoa_dados.nome;
                tipoReferencia = `Perfil ${integrante.referencia.perfil_tipo.nome}`;
                break;
            default:
                commonFunctions.generateNotification('Tipo de registro de participação não informado.', 'error');
                console.error('Tipo de registro de participação não informado.', item);
                return false;
        }

        rowIntegrantes.append(`
            <div id="${integrante.idCard}" class="card">
                <div class="card-body">
                    <h5 class="card-title d-flex align-items-center justify-content-between">
                        <span>${nome}</span>
                        <div>
                            <div class="d-grid gap-2 d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-danger btn-sm btn-delete-integrante border-0">Excluir</button>
                            </div>
                        </div>
                    </h5>
                    <div class="row">
                        <div class="col">
                            <div class="form-text">Tipo Referência</div>
                            <label class="form-label">${tipoReferencia}</label>
                        </div>
                    </div>
                </div>
            </div>
            `);


        let element = self._objConfigs.data.participantesNaTela.find(participante => participante.idCard == item.idCard);
        if (!element.integrantes) {
            element.integrantes = [];
        }
        element.integrantes.push(integrante);

        self.#atualizaQuantidadeIntegrantes(item.idCard);
        self.#addEventoPerfilIntegrante(item, integrante);
    }

    async #addEventoPerfilIntegrante(item, integrante) {
        const self = this;

        $(`#${integrante.idCard} .btn-delete-integrante`).on('click', async function () {
            try {
                const obj = new modalMessage();
                obj.setDataEnvModal = {
                    title: 'Remoção de Integrante',
                    message: 'Tem certeza que deseja remover este integrante?',
                };
                obj.setFocusElementWhenClosingModal = $(this);
                if (self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow(false);
                const result = await obj.modalOpen();
                if (result.confirmResult) {

                    $(`#${integrante.idCard}`).remove();
                    const participantes = self._objConfigs.data.participantesNaTela;
                    const indexPart = participantes.findIndex(participante => participante.idCard === item.idCard);

                    if (indexPart > -1) {
                        const indexInt = participantes[indexPart].integrantes.findIndex(item => item.idCard === integrante.idCard);
                        if (indexInt > -1) {
                            participantes[indexPart].integrantes.splice(indexInt, 1);
                        }
                    }

                    self.#atualizaQuantidadeIntegrantes(item.idCard);
                    commonFunctions.generateNotification('Integrante removido.', 'success');
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                if (self._extraConfigs?.typeParent == 'modal') await self.parentInstance._modalHideShow();
            }
        });
    }

    #accordionIntegrantesGrupo(item) {
        return `
            <div class="accordion mt-2" id="accordionIntegrantes${item.idCard}">
                <div class="accordion-item">
                    <div class="accordion-header">
                        <button class="accordion-button py-1 collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseOne${item.idCard}" aria-expanded="true"
                            aria-controls="collapseOne${item.idCard}">
                            <span class="qtdIntegrantes">Nenhum integrante no grupo</span>
                        </button>
                    </div>
                    <div id="collapseOne${item.idCard}" class="accordion-collapse collapse"
                        data-bs-parent="#accordionIntegrantes${item.idCard}">
                        <div class="accordion-body">
                            <div class="row rowIntegrantes row-cols-1 g-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    #atualizaQuantidadeIntegrantes(idCard) {
        const self = this;
        let element = self._objConfigs.data.participantesNaTela.find(item => item.idCard == idCard);
        const totalIntegrantes = element.integrantes.length;
        const qtdIntegrantes = $(`#accordionIntegrantes${idCard} .qtdIntegrantes`);
        let str = 'Nenhum integrante no grupo';

        if (totalIntegrantes === 1) {
            str = '1 integrante no grupo';
        } else if (totalIntegrantes > 1) {
            str = `${totalIntegrantes} integrantes no grupo`;
        }
        qtdIntegrantes.html(str);
    }

    async _buscarPresetParticipacaoTenant(selected_id = null) {
        const self = this;
        let options = selected_id ? { selectedIdOption: selected_id } : {};
        const selModulo = $(`#preset_id${self._objConfigs.sufixo}`);
        await commonFunctions.fillSelect(selModulo, self._objConfigs.url.baseParticipacaoPreset, options);
    }

    async _inserirParticipantesEIntegrantes(participantes) {
        const self = this;
        $(`#divParticipantes${self._objConfigs.sufixo}`).html('');
        self._objConfigs.data.participantesNaTela = [];
        self._atualizaPorcentagemLivre();
        !participantes.length ? $(`#btnExcluirParticipante${self._objConfigs.sufixo}`).hide('fast') :
            $(`#btnExcluirParticipante${self._objConfigs.sufixo}`).show('fast');

        await Promise.all(
            participantes.map(async (participante) => {
                const integrantes = participante.integrantes ?? [];
                delete participante.integrantes;
                const item = await self._inserirParticipanteNaTela(participante);
                await Promise.all(
                    integrantes.map(async (integrante) => {
                        await self._inserirIntegrante(item, integrante);
                    })
                );
            })
        );
    }

    _saveVerificationsParticipantes(data) {
        const self = this;
        let blnSave = true;

        let porcentagemOcupada = self._objConfigs.data.porcentagem_ocupada;
        if (porcentagemOcupada > 0 && porcentagemOcupada < 100 || porcentagemOcupada > 100) {
            commonFunctions.generateNotification(`As somas das porcentagens deve ser igual a 100%. Porcentagem informada ${commonFunctions.formatWithCurrencyCommasOrFraction(porcentagemOcupada)}%.`, 'warning');
            blnSave = false;
        }
        if (!data.participantes || data.participantes.length == 0) {
            commonFunctions.generateNotification('E necessário informar pelo menos um participante.', 'warning');
            blnSave = false;
        } else {
            for (const participante of data.participantes) {
                if (participante.participacao_registro_tipo_id == window.Enums.ParticipacaoRegistroTipoEnum.GRUPO && (!participante.integrantes || participante.integrantes.length == 0)) {
                    commonFunctions.generateNotification('E necessário informar pelo menos um integrante para participante do tipo Grupo.', 'warning');
                    blnSave = false;
                    break;
                }
            }
        }

        return blnSave;
    }

    async #getRecurse(options = {}) {
        const self = this;
        const { idRegister = self._idRegister,
            urlApi = self._objConfigs.url.base,
        } = options;

        try {
            return RequestsHelpers.getRecurse({ urlApi: urlApi, idRegister: idRegister });
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }
}