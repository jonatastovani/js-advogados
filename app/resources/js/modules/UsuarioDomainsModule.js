// Tipagem apenas para referência, sem carregar o módulo
/** @typedef {import('../views/pessoa/pessoa-fisica/form').PagePessoaFisicaForm} PagePessoaFisicaForm */

import { CommonFunctions } from "../commons/CommonFunctions";
import { ModalSelecionarUsuarioDomains } from "../components/pessoas/ModalSelecionarUsuarioDomains";
import TenantTypeDomainCustomHelper from "../helpers/TenantTypeDomainCustomHelper";
import { URLHelper } from "../helpers/URLHelper";
import { UUIDHelper } from "../helpers/UUIDHelper";

export class UsuarioDomainsModule {

    _objConfigs;
    /** @type {PagePessoaFisicaForm} */
    _parentInstance;
    _extraConfigs;

    //#region Variáveis privadas

    // Se vai irá requerer os campos obrigatórios ou retornar os dados para serem salvos
    #requiredResult = false;

    //#endregion

    constructor(parentInstance, objData) {
        this._objConfigs = objData.objConfigs;
        this._parentInstance = parentInstance;
        this._extraConfigs = objData.extraConfigs;
    }

    //#region Getters e Setters

    set setUser(user) {
        // Inicializa a estrutura se não existir
        if (!this._objConfigs.data) this._objConfigs.data = {};
        // Seta o valor mantendo a referência
        this._objConfigs.data.user = user;
    }

    get getUser() {
        // Inicializa se não existir e retorna a referência
        if (!this._objConfigs.data) this._objConfigs.data = {};
        if (!this._objConfigs.data.user) this._objConfigs.data.user = undefined;
        return this._objConfigs.data.user;
    }

    set setDominiosNaTela(dominiosNaTela) {
        // Inicializa a estrutura se não existir
        if (!this._objConfigs.data) this._objConfigs.data = {};
        // Seta o valor mantendo a referência
        this._objConfigs.data.dominiosNaTela = dominiosNaTela;
    }

    get getDominiosNaTela() {
        // Inicializa se não existir e retorna a referência
        if (!this._objConfigs.data) this._objConfigs.data = {};
        if (!this._objConfigs.data.dominiosNaTela) this._objConfigs.data.dominiosNaTela = [];
        return this._objConfigs.data.dominiosNaTela;
    }

    //#endregion

    _verificaPerfisNaTela() {
        const perfisNaTela = this._parentInstance.getPerfisNaTela;

        const hasUsuarioPerfil = perfisNaTela.some(perfil => perfil.perfil_tipo_id === window.Enums.PessoaPerfilTipoEnum.USUARIO);
        this.#habilitarCamposUserDomain(hasUsuarioPerfil);
    }

    /**
     * Habilita ou desabilita os campos do painel do usuário, ocultando e desativando os elementos.
     * Remove a ação dos botões quando desabilitado.
     *
     * @param {boolean} status - Se true, habilita os campos; se false, desabilita e oculta.
     */
    #habilitarCamposUserDomain(status = true) {
        const self = this;
        const camposDadosUsuario = $(`#${self._parentInstance.getSufixo} .campos-dados-usuario`);

        if (status) {
            self.#requiredResult = true;

            // Exibe a tab e o painel
            camposDadosUsuario.css('display', '');

            // Habilita todos os inputs e botões dentro do painel
            camposDadosUsuario.find(':input, button').prop('disabled', false);

            self.#addEventosBotoes();

        } else {

            self.#requiredResult = false;
            // Oculta a tab e o painel
            camposDadosUsuario.hide('fast');

            // Desabilita todos os inputs e botões dentro do painel
            camposDadosUsuario.find(':input, button').prop('disabled', true);

            // Remove eventos de clique dos botões para garantir a desativação
            self.#addEventosBotoes(true);
        }
    }

    #addEventosBotoes(blnRemoverEventos = false) {
        const self = this;
        const btnAdicionarDominio = $(`#btnAdicionarDominio${self._objConfigs.sufixo}`);

        if (blnRemoverEventos) {
            btnAdicionarDominio.off('click');

        } else {

            btnAdicionarDominio.off('click').on('click', async function () {
                const btn = $(this);
                CommonFunctions.simulateLoading(btn);
                try {
                    const objModal = new ModalSelecionarUsuarioDomains();
                    objModal.setFocusElementWhenClosingModal = btn;
                    const response = await objModal.modalOpen();
                    if (response.refresh && response.register) {
                        await self._inserirDominio(response.register, true);
                    }
                } catch (error) {
                    CommonFunctions.generateNotificationErrorCatch(error);
                } finally {
                    CommonFunctions.simulateLoading(btn, false);
                }
            });
        }
    }

    /**
     * Verifica se os dados do usuário estão disponíveis e, caso positivo, habilita e preenche os campos.
     * 
     * @param {Object} pessoa - Objeto contendo os dados da pessoa.
     * @param {Object} pessoa.perfil_usuario - Objeto contendo os dados do perfil do usuário.
     * @param {Object} pessoa.perfil_usuario.user - Objeto com os dados do usuário.
     * @param {string} pessoa.perfil_usuario.user.email - Endereço de e-mail do usuário.
     * @param {string} pessoa.perfil_usuario.user.name - Nome do usuário.
     * @param {Array} pessoa.perfil_usuario.user.user_tenant_domains - Lista de domínios vinculados ao usuário.
     */
    _verificaEPreencheDadosUser(pessoa) {
        const self = this;

        // Verifica se há um usuário associado no objeto 'pessoa'
        if (pessoa?.perfil_usuario?.user) {

            // Habilita os campos e torna visível a seção de dados do usuário
            self.#habilitarCamposUserDomain(true);

            // Armazena o usuário para referência posterior
            self.setUser = pessoa.perfil_usuario.user;

            // Obtém a div de dados do usuário usando o sufixo da instância pai
            const divDadosUsuario = $(`#divDadosUsuario${self._parentInstance.getSufixo}`);

            // Preenche os campos de email e nome e se está ativo
            divDadosUsuario.find('input[name="email"]').val(self.getUser.email);
            divDadosUsuario.find('input[name="name"]').val(self.getUser.name);
            divDadosUsuario.find('input[name="ativo_bln"]').prop('checked', self.getUser.ativo_bln);

            // Percorre os domínios vinculados ao usuário e os insere
            self.getUser.user_tenant_domains.forEach(dominio => {
                self._inserirDominio(dominio);
            });
        } else {
            // Desabilita os campos e oculta a seção de dados do usuário se não houver dados
            self.#habilitarCamposUserDomain(false);
        }
    }

    #verificaDominioAdicionado(dominioAInserir) {
        const self = this;

        // Filtra os dominios na tela que possuem o mesmo id
        const dominiosComMesmoId = self.getDominiosNaTela.filter(
            dominio => dominio.domain_id == dominioAInserir.domain_id
        );

        // Verifica se ultrapassou o limite permitido
        if (dominiosComMesmoId.length) {
            CommonFunctions.generateNotification(`Esta Unidade/Domínio já foi adicionada.`, 'warning');
            return false;
        }
        return true;
    }

    async _inserirDominio(dominio, validarLimite = false) {
        const self = this;
        const divDominio = $(`#divDominio${self._objConfigs.sufixo}`);

        const nomeDominio = dominio.domain.name;
        const urlDominio = dominio.domain.domain;

        // Validação de limite
        if (validarLimite && !self.#verificaDominioAdicionado(dominio)) {
            return false;
        }

        // Geração de ID único
        dominio.idCol = UUIDHelper.generateUUID();
        !dominio.id ? dominio.ativo_bln = true : null;

        const dominioVigente = window.location.hostname === urlDominio;
        const customDomain = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;

        const urlDominioFormatada = URLHelper.formatUrlHttp(urlDominio);

        // Definição do botão "Ir para o domínio"
        const btnIrParaDominio = (!dominio.id || customDomain || dominioVigente) ? '' : `
            <a href="${urlDominioFormatada}" class="btn btn-outline-primary border-0 btn-sm" target="_blank">Ir para o domínio</a>
        `;

        // Botão de status (ativo/inativo)
        const btnStatus = `
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" role="switch" id="ckbAtivo${dominio.idCol}" name="ativo_bln" ${dominio.ativo_bln || !dominio.id ? 'checked' : ''}>
                <label class="form-check-label" for="ckbAtivo${dominio.idCol}">Ativo</label>
            </div>
        `;

        // Botão de remoção
        const btnRemover = !dominio.id ? `
            <button type="button" class="btn btn-outline-danger border-0 btn-sm btn-delete" title="Remover acesso à Unidade/Domínio '${nomeDominio}'">
                Remover
            </button>
        ` : '';

        // Criação do cartão HTML
        const strCard = `
            <div id="${dominio.idCol}" class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center justify-content-between mb-0">
                            <span class="text-truncate spanTitle">${nomeDominio}</span>
                            <div>
                                ${btnRemover}
                            </div>
                        </h5>
                        <p class="text-truncate mb-0 spanDominio">${urlDominio}</p>
                        ${!dominio.id ? '<div class="form-text fst-italic">Acesso ainda não cadastrado</div>' : btnIrParaDominio}
                        ${btnStatus}
                    </div>
                </div>
            </div>
        `;

        // Inserção no DOM e registro do evento
        divDominio.append(strCard);
        self.#addEventosDominio(dominio);
        self.getDominiosNaTela.push(dominio);

        return true;
    }

    #pesquisaIndexDominioNaTela(item, prop = 'idCol') {
        const self = this;
        return self.getDominiosNaTela.findIndex(doc => doc[prop] === item[prop]);
    }

    async #addEventosDominio(item) {
        const self = this;

        // Evento de clique para exclusão de domínio
        $(`#${item.idCol}`).find('.btn-delete').click(async function () {
            try {
                const dominioNaTela = self.getDominiosNaTela;
                const indexDominio = self.#pesquisaIndexDominioNaTela(item);

                if (indexDominio === -1) return;

                $(`#${dominioNaTela[indexDominio].idCol}`).remove();
                dominioNaTela.splice(indexDominio, 1);

            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            }
        });

        // Evento de check para inativação de acesso de domínio
        $(`#${item.idCol}`).find(`input[name="ativo_bln"]`).change(function () {
            try {
                const dominioNaTela = self.getDominiosNaTela;
                const indexDominio = self.#pesquisaIndexDominioNaTela(item);

                if (indexDominio === -1) return;

                dominioNaTela[indexDominio].ativo_bln = $(this).is(':checked');
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            }
        });
    }

    _retornaDominiosNaTelaSaveButonAction() {
        const self = this;
        if (!self.#requiredResult) return [];
        return self.getDominiosNaTela.map(i => {
            return {
                id: i.id,
                domain_id: i.domain_id,
                ativo_bln: i.ativo_bln,
            }
        });
    }

    _inserirDominioObrigatorio() {
        const self = this;
        return;
        const novoDominio = {
            domain_id: self._objConfigs.data.pessoa_domain_id,
            domain: window.Details.UsuarioDomainsEnum.filter(item =>
                item.id == self._objConfigs.data.pessoa_domain_id)[0]
        }
        self._inserirDominio(novoDominio, false, true);
    }

    saveButtonActionEspecificoUsuarioDomains(data) {
        const self = this;

        if (!self.#requiredResult) return;

        const divDadosUsuario = $(`#divDadosUsuario${self._parentInstance.getSufixo}`);
        let dataUsers = CommonFunctions.getInputsValues(divDadosUsuario[0]);

        if (self.getUser) {
            data.user = {
                id: self.getUser.id,
                name: self.getUser.name,
                email: self.getUser.email,
                ativo_bln: self.getUser.ativo_bln,
                domain_id: self.getUser.domain_id
            };
        } else {
            data.user = {};
        }

        data.user.name = dataUsers.name;
        data.user.email = dataUsers.email;
        data.user.ativo_bln = dataUsers.ativo_bln;

        data.user_domains = self._retornaDominiosNaTelaSaveButonAction();
        return data;
    }

    saveVerificationsEspecificoUsuarioDomains(data, setFocus, returnForcedFalse) {

        const self = this;
        if (!self.#requiredResult) return !returnForcedFalse;

        const divDadosUsuario = $(`#divDadosUsuario${self._parentInstance.getSufixo}`);
        const painelDadosUsuario = $(`#painelDadosUsuario${self._parentInstance.getSufixo}-tab`);


        let blnSave = CommonFunctions.verificationData(data.user.name, {
            field: divDadosUsuario.find('input[name="name"]'),
            messageInvalid: 'O campo <b>Nome de exibição</b> deve ser informado.',
            setFocus: setFocus,
            returnForcedFalse: returnForcedFalse,
            executeBeforeEventFocusElement: () => painelDadosUsuario.trigger('click'),
        });

        blnSave = CommonFunctions.verificationData(data.user.email, {
            field: divDadosUsuario.find('input[name="email"]'),
            messageInvalid: 'O campo <b>Email</b> deve ser informado.',
            setFocus: blnSave == true,
            returnForcedFalse: blnSave == false,
            executeBeforeEventFocusElement: () => painelDadosUsuario.trigger('click'),
        });

        if (data.user_domains?.length === 0) {
            if (blnSave == true) painelDadosUsuario.trigger('click');
            CommonFunctions.generateNotification('Nenhum acesso de unidade/domínio foi selecionado. Por favor, adicione ao menos um.', 'warning');
            blnSave = false;
        }

        return blnSave;
    }
}