import { CommonFunctions } from "../commons/CommonFunctions";
import { ConnectAjax } from "../commons/ConnectAjax";
import { ModalMessage } from "../components/comum/ModalMessage";
import { ModalNome } from "../components/comum/ModalNome";
import { ModalParticipacaoParticipante } from "../components/comum/ModalParticipacaoParticipante";
import { ModalParticipacaoPreset } from "../components/comum/ModalParticipacaoPreset";
import { ModalPessoa } from "../components/pessoas/ModalPessoa";
import TenantTypeDomainCustomHelper from "../helpers/TenantTypeDomainCustomHelper";
import { UUIDHelper } from "../helpers/UUIDHelper";

export class ParticipacaoModule {

    #objConfigs = {
        operacaoComParticipantesPersonalizaveis: [
            window.Enums.LancamentoTipoEnum.LANCAMENTO_RESSARCIMENTO,
            window.Enums.LancamentoTipoEnum.LANCAMENTO_AGENDAMENTO,
        ],
        participacao: {
            perfis_busca: window.Statics.PerfisPermitidoParticipacaoServico,
        }
    };
    _objConfigs;
    _parentInstance;
    _extraConfigs;

    constructor(parentInstance, objData) {
        this._objConfigs = objData.objConfigs;
        // Mescla na variável restrita para depois mesclar na compartilhada para manter as relações
        CommonFunctions.deepMergeObject(this.#objConfigs, this._objConfigs);
        CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._parentInstance = parentInstance;
        this._extraConfigs = objData.extraConfigs;
        this.#addEventosBotoes();
    }

    get getExibirPainelParticipantesPersonalizaveisBln() {

        switch (this._objConfigs.modoOperacao) {

            // Se o modo de operação for "ressarcimento", já retorna true diretamente
            case window.Enums.LancamentoTipoEnum.LANCAMENTO_RESSARCIMENTO:
                return true;

            case window.Enums.LancamentoTipoEnum.LANCAMENTO_AGENDAMENTO:

                if (this._parentInstance._dataEnvModal.agendamento_tipo == window.Enums.LancamentoTipoEnum.LANCAMENTO_RESSARCIMENTO) {
                    return true;
                }
                return false;

            default:
                return false;
                break;
        }
    }

    #addEventosBotoes() {
        const self = this;

        const openmodalParticipacao = async (dados_participacao) => {
            // const inheritDomainId = self._domainInherit();

            const objModal = new ModalParticipacaoParticipante();
            if (this._objConfigs?.participacao?.valor_tipo_permitido) {
                objModal.setValorTipoPermitido = this._objConfigs.participacao.valor_tipo_permitido;
            }
            const dataEnvModal = {
                dados_participacao: dados_participacao,
                porcentagem_ocupada: self._objConfigs.data.porcentagem_ocupada,
                configuracao_tipo: self._objConfigs.participacao.participacao_tipo_tenant.configuracao_tipo,
            };
            // inheritDomainId ? dataEnvModal.inherit_domain_id = inheritDomainId : null;
            objModal.setDataEnvModal = dataEnvModal;
            const response = await objModal.modalOpen();
            if (response.refresh) {
                await self._inserirParticipante(CommonFunctions.deepMergeObject(dados_participacao, response.register));
            }
        }

        $(`#btnInserirPessoa${self._objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const dataEnvModalAppend = {
                    perfis_busca: self._objConfigs.participacao.perfis_busca,
                };
                const objModal = new ModalPessoa({ dataEnvModal: dataEnvModalAppend });
                if (self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow(false);
                const response = await objModal.modalOpen();
                if (response.refresh && response.selected) {
                    await openmodalParticipacao({
                        participacao_registro_tipo_id: window.Enums.ParticipacaoRegistroTipoEnum.PERFIL,
                        referencia: response.selected,
                        referencia_id: response.selected.id,
                    });
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
                if (self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow();
            }
        });

        $(`#btnInserirGrupo${self._objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModalNome = new ModalNome();
                objModalNome.setDataEnvModal = {
                    title: 'Novo grupo',
                    mensagem: 'Informe o nome do grupo',
                }
                if (self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow(false);
                const response = await objModalNome.modalOpen();
                if (response.refresh) {
                    await openmodalParticipacao({
                        participacao_registro_tipo_id: window.Enums.ParticipacaoRegistroTipoEnum.GRUPO,
                        nome_grupo: response.name
                    });
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
                if (self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow();
            }
        });

        $(`#btnOpenModalPresetParticipacao${self._objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalParticipacaoPreset();
                objModal.setDataEnvModal = self._parentInstance._checkDomainCustomInheritDataEnvModal({
                    attributes: {
                        select: {
                            quantity: 1,
                            autoReturn: true,
                        }
                    }
                });

                if (self._extraConfigs?.typeParent == 'modal') {
                    await self._parentInstance._modalHideShow(false)
                };

                const response = await objModal.modalOpen();
                if (response.refresh) {
                    if (response.selected) {

                        if (self._extraConfigs?.typeParent == 'modal') {
                            await self._parentInstance._modalHideShow()
                        };

                        await self._buscarPresetParticipacaoTenant(response.selected.id);
                        $(`#preset_id${self._objConfigs.sufixo}`).trigger('change');
                    } else {
                        self._buscarPresetParticipacaoTenant(null);
                    }
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
                if (self._extraConfigs?.typeParent == 'modal') {
                    await self._parentInstance._modalHideShow()
                };
            }
        });

        const modeParent = self._extraConfigs?.modeParent ?? 'preset';
        switch (modeParent) {
            case 'searchAndUse':

                $(`#preset_id${self._objConfigs.sufixo}`).on('change', async function () {
                    let blnInserir = false;
                    const preset_id = $(this).val();
                    const divParticipantes = $(`#divParticipantes${self._objConfigs.sufixo}`);

                    const resetPreset = () => {
                        $(this).val(0).trigger('change');
                    }

                    const inserirPreset = async (hideShowModal) => {
                        try {
                            if (hideShowModal && self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow(false);
                            await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando informações do preset...' });

                            const response = await self._parentInstance._getRecurse({
                                urlApi: self._objConfigs.url.baseParticipacaoPreset,
                                idRegister: preset_id,
                                checkForcedBefore: true,
                            });
                            if (response.data) {
                                self._limparDivParticipantes();
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
                            CommonFunctions.generateNotificationErrorCatch(error);
                        } finally {
                            $(this).val(0);
                            await CommonFunctions.loadingModalDisplay(false);
                            if (self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow();
                        }
                    }

                    if (UUIDHelper.isValidUUID(preset_id)) {
                        if (divParticipantes.children().length > 0) {
                            try {
                                const obj = new ModalMessage();
                                obj.setDataEnvModal = {
                                    title: 'Inserção de Preset',
                                    message: 'A inserção deste preset limpará todos os participantes atuais. Confirma esta ação?',
                                };
                                obj.setFocusElementWhenClosingModal = $(this);
                                if (self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow(false);
                                const result = await obj.modalOpen();
                                if (result.confirmResult) {
                                    blnInserir = true;
                                    await inserirPreset(false);
                                } else {
                                    resetPreset();
                                }
                            } catch (error) {
                                CommonFunctions.generateNotificationErrorCatch(error);
                            } finally {
                                if (!blnInserir && self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow();
                            }
                        } else {
                            await inserirPreset(true);
                        }
                    } else {
                        if (preset_id != '0') CommonFunctions.generateNotification('O ID do preset é inválido.', 'error');
                        resetPreset();
                    }
                });
                break;

            default:
                break;
        }

        self._limparDivParticipantes();
    }

    // _domainInherit() {
    //     const self = this;

    //     const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
    //     if (!instance) return false;

    //     if (self._objConfigs?.domainCustom?.applyBln) {

    //         const selectDomain = $(`#${self._parentInstance.getSufixo} .${instance.getDomainCustomIdentificationClassName}`);

    //         if (selectDomain.length) {

    //             const selected = Number(selectDomain.val());

    //             if (!selected) throw new Error('A unidade selecionada  para o novo registro é inválida.');

    //             const findDomain = instance.getArrayDomains.find(domain => domain.id == selected);
    //             if (!findDomain) throw new Error('O valor da unidade selecionada para o novo registro é inválido.');

    //             return selected;
    //         }
    //     }
    //     return false;
    // }

    _limparDivParticipantes() {
        const self = this;
        self._getParticipantesNaTela().length = 0;
        $(`#divParticipantes${self._objConfigs.sufixo}`).html('');
        self._atualizaPorcentagemLivre();
    }

    async #buscarParticipacaoTipo(id) {
        const self = this;
        return await self.#getRecurse({ idRegister: id, urlApi: self._objConfigs.url.baseParticipacaoTipo });
    }

    async _inserirParticipante(participante) {
        const self = this;
        const divParticipantes = $(`#divParticipantes${self._objConfigs.sufixo}`);
        participante.idCard = UUIDHelper.generateUUID();

        let nome = '';
        let btnsAppend = '';
        let accordionIntegrantes = '';
        let displayObservacao = 'none';
        let tipoReferencia = '';
        let tipoReferenciaTitle = '';

        const naTela = self.#verificaRegistroParticipanteNaTela(participante);

        switch (participante.participacao_registro_tipo_id) {
            case window.Enums.ParticipacaoRegistroTipoEnum.PERFIL:

                const pessoa = participante.referencia.pessoa;

                switch (pessoa.pessoa_dados_type) {
                    case window.Enums.PessoaTipoEnum.PESSOA_FISICA:
                        nome = pessoa.pessoa_dados.nome;
                        break;
                    case window.Enums.PessoaTipoEnum.PESSOA_JURIDICA:
                        nome = pessoa.pessoa_dados.nome_fantasia;
                        break;

                    default:
                        CommonFunctions.generateNotification(`O tipo de pessoa <b>${pessoa.pessoa_dados_type}</b> ainda não foi implementado.`, 'error');
                        console.error('O tipo de pessoa ainda nao foi implementado.', participante);
                        return false;
                }

                if (naTela) {
                    CommonFunctions.generateNotification(`Participante <b>${nome}</b> já foi inserido(a) para este tipo de participação.`, 'error');
                    return false;
                }

                tipoReferenciaTitle = participante.referencia.perfil_tipo.nome;
                tipoReferencia = `Perfil <span class="fw-bolder">${tipoReferenciaTitle}</span>`;
                break;

            case window.Enums.ParticipacaoRegistroTipoEnum.GRUPO:

                nome = participante.nome_grupo;
                if (naTela) {
                    CommonFunctions.generateNotification(`O Grupo <b>${nome}</b> já foi inserido. O nome foi alterado para <b>${nome} (Alterar)</b>.<br>Altere o nome do grupo posteriormente.`, 'warning');
                    participante.nome_grupo = `${nome} (Alterar)`;
                    nome = participante.nome_grupo;
                }
                btnsAppend += `<li><button type="button" class="dropdown-item fs-6 btn-add-pessoa">Inserir Pessoa</button></li>`;
                btnsAppend += `<li><button type="button" class="dropdown-item fs-6 btn-edit-name">Editar Nome</button></li>`;
                accordionIntegrantes = self.#accordionIntegrantesGrupo(participante);

                break;

            default:

                CommonFunctions.generateNotification('Tipo de registro de participação não informado.', 'error');
                console.error('Tipo de registro de participação não informado.', participante);
                return false;
        }

        let participacao_tipo = participante.participacao_tipo ?? null;
        if (!participacao_tipo) {
            if (participante.participacao_tipo_id) {
                const response = await self.#buscarParticipacaoTipo(participante.participacao_tipo_id);
                if (response) {
                    participacao_tipo = response.data;
                } else {
                    participacao_tipo = { nome: 'Erro de busca' }
                }
            } else {
                CommonFunctions.generateNotification('Tipo de participação não informado.', 'error');
                console.error('Tipo de participação não informado.', participante);
                return false;
            }
        }

        if (participante.observacao) {
            displayObservacao = 'block';
        }

        let valor_tipo = ''
        let valor = CommonFunctions.formatWithCurrencyCommasOrFraction(participante.valor);
        switch (participante.valor_tipo) {
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
                console.error('Erro no tipo de valor', participante);
                break;
        }

        let rowColsDados = 'row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4';
        if (self._extraConfigs?.typeParent == 'modal') rowColsDados = 'row-cols-1 row-cols-sm-2 row-cols-md-3';

        if (tipoReferencia) {
            tipoReferencia = `
                <div class="col">
                    <div class="form-text">Perfil Referência</div>
                    <label class="form-label" title="${tipoReferenciaTitle}">${tipoReferencia}</label>
                </div>`;
        }

        const strCard = `
            <div class="card-body">
                <h5 class="card-title d-flex align-items-center justify-content-between">
                    <span class="spanNome" title="${nome}">${nome}</span>
                    <div>
                        <div class="dropdown dropdown-acoes-participante">
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
                <div class="row ${rowColsDados}">
                    ${tipoReferencia}
                    <div class="col">
                        <div class="form-text">Participação</div>
                        <label class="form-label text-truncate w-100 lblParticipacao" title="${participacao_tipo.nome}">${participacao_tipo.nome}</label>
                    </div>
                    <div class="col">
                        <div class="form-text">Método</div>
                        <label class="form-label lblValorTipo" title="${valor_tipo}">${valor_tipo}</label>
                    </div>
                    <div class="col">
                        <div class="form-text">Valor</div>
                        <label class="form-label text-truncate w-100 lblValor" title="${valor}">${valor}</label>
                    </div>
                </div>
                <div class="row rowObservacao" style="display: ${displayObservacao};">
                    <div class="col">
                        <div class="form-text">Observação</div>
                        <label class="form-label lblObservacao" title="${participante.observacao ?? ''}">${participante.observacao ?? ''}</label>
                    </div>
                </div>
                ${accordionIntegrantes}
            </div>`;

        self._pushObjetoParticipanteNaTela(participante);

        divParticipantes.append(`<div id="${participante.idCard}" class="card card-participante">${strCard}</div>`);

        await self.#addEventoParticipante(participante);
        self._atualizaPorcentagemLivre(participante);
        return participante;
    }

    /**
     * Adiciona um novo objeto de participante ao array `participantesNaTela`.
     * 
     * @param {Object} participante - Objeto do participante a ser inserido.
     */
    _pushObjetoParticipanteNaTela(participante) {
        const self = this;
        self._getParticipantesNaTela().push(participante);
    }

    /**
     * Retorna o índice de um participante na lista `participantesNaTela`, baseado em um campo específico.
     * 
     * @param {*} valor - Valor a ser comparado.
     * @param {Object} [options={}] - Opções adicionais.
     * @param {string} [options.campo='idCard'] - Nome do campo do participante para comparar.
     * @returns {number} - Índice do participante encontrado ou -1 se não encontrado.
     */
    _getIndexObjetoParticipanteNaTela(valor, options = {}) {
        const self = this;
        const campo = options.campo ?? 'idCard';

        return self._getParticipantesNaTela().findIndex(part => part[campo] === valor);
    }

    /**
     * Retorna o objeto participante da lista `participantesNaTela` com base em um campo específico.
     * 
     * @param {*} valor - Valor a ser comparado.
     * @param {Object} [options={}] - Opções adicionais.
     * @param {string} [options.campo='idCard'] - Nome do campo do participante para comparar.
     * @returns {Object|null} - Participante encontrado ou null.
     */
    _getObjetoParticipanteNaTela(valor, options = {}) {
        const self = this;
        const campo = options.campo ?? 'idCard';

        return self._getParticipantesNaTela().find(p => p[campo] === valor);
    }

    /**
     * Remove um participante da lista `participantesNaTela` e do DOM (HTML), se encontrado.
     * 
     * @param {*} valor - Valor a ser comparado para identificar o participante.
     * @param {Object} [options={}] - Opções adicionais.
     * @param {string} [options.campo='idCard'] - Nome do campo do participante para comparação.
     */
    _removerParticipanteDaTela(valor, options = {}) {
        const self = this;
        const index = self._getIndexObjetoParticipanteNaTela(valor, options);

        if (index > -1) {
            const idHtml = self._getParticipantesNaTela()[index].idCard;
            $(`#${idHtml}`).remove();
            self._getParticipantesNaTela().splice(index, 1);
            self._atualizaPorcentagemLivre?.();
        }
    }

    /**
     * Insere um integrante no array `integrantes` de um participante previamente adicionado à tela.
     *
     * A função busca o participante no array `participantesNaTela` com base no `idCard` do item informado
     * e insere o novo integrante no array interno `integrantes`. Caso o array ainda não exista, ele é inicializado.
     *
     * @param {Object} participante - Objeto do participante, deve conter ao menos o campo `idCard`.
     * @param {Object} integrante - Objeto do integrante a ser inserido dentro do participante.
     * @returns {void}
     */
    _inserirObjetoIntegranteNoParticipante(participante, integrante) {
        const self = this;

        let part = self._getParticipantesNaTela().find(p => p.idCard === participante.idCard);
        if (!part) return;

        part.integrantes = part.integrantes ?? [];
        part.integrantes.push(integrante);
    }

    // /**
    //  * Busca um integrante dentro de um participante específico, com base em campos de identificação.
    //  * 
    //  * @param {*} valorParticipante - Valor usado para identificar o participante.
    //  * @param {*} valorIntegrante - Valor usado para identificar o integrante.
    //  * @param {Object} [options={}] - Opções adicionais.
    //  * @param {string} [options.campoParticipante='idCard'] - Campo usado para identificar o participante.
    //  * @param {string} [options.campoIntegrante='idCard'] - Campo usado para identificar o integrante.
    //  * @returns {Object|null} - Objeto do integrante, se encontrado, ou null.
    //  */
    // _getIntegranteDeParticipanteNaTela(valorParticipante, valorIntegrante, options = {}) {
    //     const self = this;
    //     const campoParticipante = options.campoParticipante ?? 'idCard';
    //     const campoIntegrante = options.campoIntegrante ?? 'idCard';

    //     const participante = self._getObjetoParticipanteNaTela(valorParticipante, { campo: campoParticipante });
    //     if (!participante || !Array.isArray(participante.integrantes)) return null;

    //     return participante.integrantes.find(integrante => integrante[campoIntegrante] === valorIntegrante);
    // }

    /**
     * Remove um integrante do array `integrantes` dentro de um participante, com base em campos personalizados.
     *
     * @param {*} valorParticipante - Valor usado para identificar o participante.
     * @param {*} valorIntegrante - Valor usado para identificar o integrante.
     * @param {Object} [options={}] - Opções adicionais.
     * @param {string} [options.campoParticipante='idCard'] - Campo usado para identificar o participante.
     * @param {string} [options.campoIntegrante='idCard'] - Campo usado para identificar o integrante.
     * @returns {boolean} - Retorna true se o integrante foi removido, false caso contrário.
     */
    _removerIntegranteDeParticipanteNaTela(valorParticipante, valorIntegrante, options = {}) {
        const self = this;
        const campoParticipante = options.campoParticipante ?? 'idCard';
        const campoIntegrante = options.campoIntegrante ?? 'idCard';

        const participante = self._getObjetoParticipanteNaTela(valorParticipante, { campo: campoParticipante });
        if (!participante || !Array.isArray(participante.integrantes)) return false;

        const index = participante.integrantes.findIndex(integrante => integrante[campoIntegrante] === valorIntegrante);
        if (index > -1) {
            const idHtml = participante.integrantes[index].idCard;
            $(`#${idHtml}`).remove();
            participante.integrantes.splice(index, 1);
            self.#atualizaQuantidadeIntegrantes(participante);
            return true;
        }

        return false;
    }

    async #atualizaParticipanteNaTela(participante) {
        const self = this;

        let participacao_tipo = participante.participacao_tipo ?? null;

        if (!participacao_tipo || participacao_tipo && (participacao_tipo.id != participante.participacao_tipo_id)) {

            if (participante.participacao_tipo_id) {

                const response = await self.#buscarParticipacaoTipo(participante.participacao_tipo_id);
                if (response) {
                    participacao_tipo = response.data;
                } else {
                    participacao_tipo = { nome: 'Erro de busca' }
                }
            } else {

                CommonFunctions.generateNotification('Tipo de participação não informado.', 'error');
                console.error('Tipo de participação não informado.', participante);
                return false;
            }
        }

        let valor_tipo = ''
        let valor = CommonFunctions.formatWithCurrencyCommasOrFraction(participante.valor);
        switch (participante.valor_tipo) {
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
                console.error('Erro no tipo de valor', participante);
                break;
        }

        for (const part of self._getParticipantesNaTela()) {
            if (part.idCard == participante.idCard) {
                part.participacao_tipo_id = participante.participacao_tipo_id;
                part.valor_tipo = participante.valor_tipo;
                part.valor = participante.valor;
                break;
            }
        }

        $(`#${participante.idCard} .lblParticipacao`).html(participacao_tipo.nome);
        $(`#${participante.idCard} .lblValorTipo`).html(valor_tipo);
        $(`#${participante.idCard} .lblValor`).html(valor);
        if (participante.observacao) {
            $(`#${participante.idCard} .lblObservacao`).html(participante.observacao);
            $(`#${participante.idCard} .rowObservacao`).show('fast');
        } else {
            $(`#${participante.idCard} .rowObservacao`).hide('fast');
        }
        self._atualizaPorcentagemLivre();
    }

    async #addEventoParticipante(participante) {
        const self = this;

        $(`#${participante.idCard} .btn-edit`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                let porcentagem_ocupada = self._objConfigs.data.porcentagem_ocupada;
                if (participante.valor_tipo == 'porcentagem') {
                    porcentagem_ocupada -= participante.valor;
                }
                const objModal = new ModalParticipacaoParticipante();
                if (this._objConfigs?.participacao?.valor_tipo_permitido) {
                    objModal.setValorTipoPermitido = this._objConfigs.participacao.valor_tipo_permitido;
                }
                objModal.setDataEnvModal = {
                    dados_participacao: participante,
                    porcentagem_ocupada: porcentagem_ocupada,
                    configuracao_tipo: self._objConfigs.participacao.participacao_tipo_tenant.configuracao_tipo,
                }
                if (self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow(false);
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    await self.#atualizaParticipanteNaTela(CommonFunctions.deepMergeObject(participante, response.register));
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
                if (self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow();
            }
        });

        $(`#${participante.idCard} .btn-delete`).on('click', async function () {
            try {
                const obj = new ModalMessage();
                obj.setDataEnvModal = {
                    title: 'Remoção de Participante',
                    message: 'Tem certeza que deseja remover este participante?',
                };
                obj.setFocusElementWhenClosingModal = $(this);
                if (self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow(false);
                const result = await obj.modalOpen();
                if (result.confirmResult) {
                    self._removerParticipanteDaTela(participante.idCard);
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                if (self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow();
            }
        });

        if (participante.participacao_registro_tipo_id == window.Enums.ParticipacaoRegistroTipoEnum.GRUPO) {

            $(`#${participante.idCard} .btn-edit-name`).on('click', async function () {

                let registro = undefined;
                for (const element of self._getParticipantesNaTela()) {
                    if (element.idCard == participante.idCard) {
                        registro = element;
                        break;
                    }
                }
                const btn = $(this);
                CommonFunctions.simulateLoading(btn);
                try {
                    const objModalNome = new ModalNome();
                    objModalNome.setDataEnvModal = {
                        title: 'Nome grupo',
                        mensagem: 'Informe o nome do grupo',
                        nome: registro.nome_grupo,
                    }
                    if (self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow(false);
                    const response = await objModalNome.modalOpen();
                    if (response.refresh) {
                        registro.nome_grupo = response.name;
                        $(`#${participante.idCard} .spanNome`).html(registro.nome_grupo);
                    }
                } catch (error) {
                    CommonFunctions.generateNotificationErrorCatch(error);
                } finally {
                    CommonFunctions.simulateLoading(btn, false);
                    if (self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow();
                }
            });

            $(`#${participante.idCard} .btn-add-pessoa`).on('click', async function () {
                const btn = $(this);
                CommonFunctions.simulateLoading(btn);
                try {
                    const objModal = new ModalPessoa();
                    objModal.setDataEnvModal = {
                        perfis_busca: self._objConfigs.participacao.perfis_busca,
                    }
                    if (self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow(false);
                    const response = await objModal.modalOpen();
                    if (response.refresh && response.selecteds) {
                        await Promise.all(
                            response.selecteds.map(async (integrante) => {
                                await self._inserirIntegrante(participante, {
                                    participacao_registro_tipo_id: window.Enums.ParticipacaoRegistroTipoEnum.PERFIL,
                                    referencia: integrante,
                                    referencia_id: integrante.id,
                                });
                            })
                        );
                    }
                } catch (error) {
                    CommonFunctions.generateNotificationErrorCatch(error);
                } finally {
                    CommonFunctions.simulateLoading(btn, false);
                    if (self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow();
                }
            });
        }
    }

    /**
     * Verifica se um participante com os mesmos identificadores já está na tela.
     *
     * @param {Object} participante - Participante a ser verificado.
     * @returns {Object|null} - Retorna o participante existente ou null se não encontrado.
     */
    #verificaRegistroParticipanteNaTela(participante) {
        const self = this;
        const registroTipo = participante.participacao_registro_tipo_id;

        for (const element of self._getParticipantesNaTela()) {
            if (element.participacao_registro_tipo_id !== registroTipo) continue;

            const mesmoTipo = element.participacao_tipo_id === participante.participacao_tipo_id;

            if (
                registroTipo === window.Enums.ParticipacaoRegistroTipoEnum.PERFIL &&
                mesmoTipo &&
                element.referencia?.id === participante.referencia?.id
            ) {
                return element;
            }

            if (
                registroTipo === window.Enums.ParticipacaoRegistroTipoEnum.GRUPO &&
                element.nome_grupo === participante.nome_grupo
            ) {
                return element;
            }
        }

        return null;
    }

    async _buscarParticipantes() {
        const self = this;
        try {
            const obj = new ConnectAjax(self._objConfigs.url.baseParticipacao);
            const response = await obj.getRequest();
            if (response.data) {
                self._inserirParticipantesEIntegrantes(response.data);
            }
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        }
    }

    _atualizaPorcentagemLivre() {
        const self = this;
        let porcentagemOcupada = 0;
        let valorFixo = 0;

        for (const itemTela of self._getParticipantesNaTela()) {
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

        $(`#valor_fixo${self._objConfigs.sufixo}`).html(`${CommonFunctions.formatWithCurrencyCommasOrFraction(valorFixo)}`);
        $(`#porcentagem${self._objConfigs.sufixo}`).html(`${CommonFunctions.formatWithCurrencyCommasOrFraction(porcentagemOcupada)}`);
        $(`#valor_minimo${self._objConfigs.sufixo}`).html(`${CommonFunctions.formatWithCurrencyCommasOrFraction(valorMinimo)}`);

        CommonFunctions.atualizarProgressBar($(`#progressBar${self._objConfigs.sufixo}`), porcentagemOcupada);
    }

    async _inserirIntegrante(participante, integrante) {
        const self = this;
        const rowIntegrantes = $(`#accordionIntegrantes${participante.idCard} .rowIntegrantes`);
        integrante.idCard = UUIDHelper.generateUUID();

        let nome = '';
        let tipoReferencia = '';
        let tipoReferenciaTitle = '';
        switch (integrante.participacao_registro_tipo_id) {
            case window.Enums.ParticipacaoRegistroTipoEnum.PERFIL:

                switch (integrante.referencia.pessoa.pessoa_dados_type) {
                    case window.Enums.PessoaTipoEnum.PESSOA_FISICA:
                        nome = integrante.referencia.pessoa.pessoa_dados.nome;
                        break;
                    case window.Enums.PessoaTipoEnum.PESSOA_JURIDICA:
                        nome = integrante.referencia.pessoa.pessoa_dados.nome_fantasia;
                        break;

                    default:
                        break;
                }

                tipoReferenciaTitle = integrante.referencia.perfil_tipo.nome;
                tipoReferencia = `Perfil <span class="fw-bolder">${tipoReferenciaTitle}</span>`;

                break;
            default:
                CommonFunctions.generateNotification('Tipo de registro de participação não informado.', 'error');
                console.error('Tipo de registro de participação não informado.', participante);
                return false;
        }

        rowIntegrantes.append(`
            <div id="${integrante.idCard}" class="card card-integrante">
                <div class="card-body">
                    <h5 class="card-title d-flex align-items-center justify-content-between">
                        <span title="${nome}">${nome}</span>
                        <div>
                            <div class="d-grid gap-2 d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-danger btn-sm btn-delete-integrante border-0">Excluir</button>
                            </div>
                        </div>
                    </h5>
                    <div class="row">
                        <div class="col">
                            <div class="form-text">Perfil Referência</div>
                            <label class="form-label" title="${tipoReferenciaTitle}">${tipoReferencia}</label>
                        </div>
                    </div>
                </div>
            </div>
            `);

        self._inserirObjetoIntegranteNoParticipante(participante, integrante);
        self.#atualizaQuantidadeIntegrantes(participante);
        await self.#addEventoPerfilIntegrante(participante, integrante);
    }

    async #addEventoPerfilIntegrante(participante, integrante) {
        const self = this;

        $(`#${integrante.idCard} .btn-delete-integrante`).on('click', async function () {
            try {
                const obj = new ModalMessage();
                obj.setDataEnvModal = {
                    title: 'Remoção de Integrante',
                    message: 'Tem certeza que deseja remover este integrante?',
                };
                obj.setFocusElementWhenClosingModal = $(this);
                if (self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow(false);
                const result = await obj.modalOpen();
                if (result.confirmResult) {

                    self._removerIntegranteDeParticipanteNaTela(participante.idCard, integrante.idCard);
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                if (self._extraConfigs?.typeParent == 'modal') await self._parentInstance._modalHideShow();
            }
        });
    }

    #accordionIntegrantesGrupo(grupo) {
        return `
            <div class="accordion mt-2" id="accordionIntegrantes${grupo.idCard}">
                <div class="accordion-item">
                    <div class="accordion-header">
                        <button class="accordion-button py-1 collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseOne${grupo.idCard}" aria-expanded="true"
                            aria-controls="collapseOne${grupo.idCard}">
                            <span class="qtdIntegrantes">Nenhum integrante no grupo</span>
                        </button>
                    </div>
                    <div id="collapseOne${grupo.idCard}" class="accordion-collapse collapse"
                        data-bs-parent="#accordionIntegrantes${grupo.idCard}">
                        <div class="accordion-body">
                            <div class="row rowIntegrantes row-cols-1 g-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    #atualizaQuantidadeIntegrantes(participante) {
        const self = this;
        let idCard = participante.idCard
        let element = self._getParticipantesNaTela().find(item => item.idCard == idCard);

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
        const options = self._retornaOptionsPreset({ instanceParent: self._parentInstance });
        selected_id ? options.selectedIdOption = selected_id : null;
        const select = $(`#preset_id${self._objConfigs.sufixo}`);
        await CommonFunctions.fillSelect(select, self._objConfigs.url.baseParticipacaoPreset, options);
    }

    _retornaOptionsPreset(optionsPreset = {}) {
        const self = this;
        const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
        if (instance) {
            if (self._objConfigs?.domainCustom?.domain_id) {
                return optionsPreset;
            }
            instance.getSelectedValue == 0 ? optionsPreset.outInstanceParentBln = true : null;
        }
        return optionsPreset;
    }

    async _inserirParticipantesEIntegrantes(participantes) {
        const self = this;
        self._limparDivParticipantes();

        // Somente páginas tem esse botão, nos modais não há
        !participantes.length ? $(`#btnExcluirParticipante${self._objConfigs.sufixo}`).hide('fast') :
            $(`#btnExcluirParticipante${self._objConfigs.sufixo}`).show('fast');

        await Promise.all(
            participantes.map(async (participante) => {
                const integrantes = participante.integrantes ?? [];
                delete participante.integrantes;
                const item = await self._inserirParticipante(participante);
                await Promise.all(
                    integrantes.map(async (integrante) => {
                        await self._inserirIntegrante(item, integrante);
                    })
                );
            })
        );
    }

    /**
     * Retorna a lista de participantes na tela, garantindo que seja um array.
     * Se a propriedade não existir ou não for um array, inicializa como um array vazio.
     * 
     * @returns {Array} Lista de participantes na tela.
     */
    _getParticipantesNaTela() {
        const self = this;

        if (!Array.isArray(self._objConfigs.data.participantesNaTela)) {
            self._objConfigs.data.participantesNaTela = [];
        }

        return self._objConfigs.data.participantesNaTela;
    }

    _getParticipantesNaTelaFiltrado() {
        const self = this;
        return self._getParticipantesNaTela().map(part => {
            let participante = {};
            part.id ? participante.id = part.id : null;
            participante.participacao_registro_tipo_id = part.participacao_registro_tipo_id;
            participante.nome_grupo = part.nome_grupo;
            participante.referencia_id = part.referencia_id;
            participante.participacao_tipo_id = part.participacao_tipo_id;
            participante.valor_tipo = part.valor_tipo;
            participante.valor = part.valor;
            participante.observacao = part.observacao;

            if (part.integrantes) {
                participante.integrantes = part.integrantes.map(integ => {
                    let integrante = {};
                    integ.id ? integrante.id = integ.id : null;
                    integrante.participacao_registro_tipo_id = integ.participacao_registro_tipo_id;
                    integrante.referencia_id = integ.referencia_id;
                    return integrante;
                });
            }
            return participante;
        });
    }

    _saveVerificationsParticipantes(data, blnSave = true) {
        const self = this;

        let porcentagemOcupada = self._objConfigs.data.porcentagem_ocupada;
        if (porcentagemOcupada > 0 && porcentagemOcupada < 100 || porcentagemOcupada > 100) {
            CommonFunctions.generateNotification(`As somas das porcentagens deve ser igual a 100%. Porcentagem informada ${CommonFunctions.formatWithCurrencyCommasOrFraction(porcentagemOcupada)}%.`, 'warning');
            blnSave = false;
        }
        if (!data.participantes || data.participantes.length == 0) {
            CommonFunctions.generateNotification('E necessário informar pelo menos um participante.', 'warning');
            blnSave = false;
        } else {
            for (const participante of data.participantes) {
                if (participante.participacao_registro_tipo_id == window.Enums.ParticipacaoRegistroTipoEnum.GRUPO && (!participante.integrantes || participante.integrantes.length == 0)) {
                    CommonFunctions.generateNotification('E necessário informar pelo menos um integrante para participante do tipo Grupo.', 'warning');
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
            const objConn = new ConnectAjax(urlApi);
            objConn.setParam(idRegister);
            return await objConn.getRequest();
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async _inserirParticipanteObrigatorioEmpresaParticipacaoGeral() {
        const self = this;

        try {
            const responsePerfilEmpresa = await self.#getRecurse({
                idRegister: 'empresa',
                urlApi: self._objConfigs.url.basePessoaPerfil
            });
            const responseEmpresa = await self.#getRecurse({
                idRegister: responsePerfilEmpresa.data.id,
                urlApi: self._objConfigs.url.basePessoaPerfil
            });
            const responseParticipacaoEmpresa = await self.#getRecurse({
                idRegister: 'empresa-geral',
                urlApi: self._objConfigs.url.baseParticipacaoTipo
            });

            let nomeFuncao = self.getExibirPainelParticipantesPersonalizaveisBln ?
                '_inserirParticipante' : '_pushObjetoParticipanteNaTela';

            self[nomeFuncao]({
                participacao_registro_tipo_id: window.Enums.ParticipacaoRegistroTipoEnum.PERFIL,
                referencia_id: responseEmpresa.data.id,
                referencia: responseEmpresa.data,
                participacao_tipo_id: responseParticipacaoEmpresa.data.id,
                participacao_tipo: responseParticipacaoEmpresa.data,
                valor: 100,
                valor_tipo: 'porcentagem',
            })
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        }

    }
}