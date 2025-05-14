// Tipagem apenas para referência, sem carregar o módulo
/** @typedef {import('../views/pessoa/pessoa-fisica/form').PagePessoaFisicaForm} PagePessoaFisicaForm */

import { CommonFunctions } from "../commons/CommonFunctions";
import { ModalSelecionarPessoaPerfilTipo } from "../components/pessoas/ModalSelecionarPessoaPerfilTipo";
import { UUIDHelper } from "../helpers/UUIDHelper";

export class PessoaPerfilModule {

    _objConfigs;
    /** @type {PagePessoaFisicaForm} */
    _parentInstance;
    _extraConfigs;

    constructor(parentInstance, objData) {
        this._objConfigs = objData.objConfigs;
        this._parentInstance = parentInstance;
        this._extraConfigs = objData.extraConfigs;
        this.#addEventosBotoes();
    }

    //#region Getters e Setters

    set setPerfisNaTela(perfisNaTela) {
        // Inicializa a estrutura se não existir
        if (!this._objConfigs.data) this._objConfigs.data = {};
        // Seta o valor mantendo a referência
        this._objConfigs.data.perfisNaTela = perfisNaTela;
    }

    get getPerfisNaTela() {
        // Inicializa se não existir e retorna a referência
        if (!this._objConfigs.data) this._objConfigs.data = {};
        if (!this._objConfigs.data.perfisNaTela) this._objConfigs.data.perfisNaTela = [];
        return this._objConfigs.data.perfisNaTela;
    }

    //#endregion

    #addEventosBotoes() {
        const self = this;

        $(`#btnAdicionarPerfil${self._objConfigs.sufixo}`).on('click', async function () {

            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalSelecionarPessoaPerfilTipo();
                objModal.setDataEnvModal = {
                    pessoa_tipo_aplicavel: self._objConfigs.data.pessoa_tipo_aplicavel,
                };
                // Se for o form do cadastro da empresa, passa a informação para o modal personalizar a lista de perfis
                self._objConfigs?.formEmpresa ? objModal.setDataEnvModal.formEmpresa = self._objConfigs.formEmpresa : null;

                objModal.setFocusElementWhenClosingModal = btn;
                const response = await objModal.modalOpen();

                if (response.refresh && response.register) {
                    const register = response.register;
                    register.perfil_tipo_id = Number(register.perfil_tipo_id);
                    await self._inserirPerfil(register, true);
                    self.#eventosAposInserirOuRemoverPerfil();
                }

            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });
    }

    #verificaPerfilAdicionado(perfilAInserir) {
        const self = this;
        const perfisNaTela = self._objConfigs.data.perfisNaTela;

        // Filtra os perfis na tela que possuem o mesmo perfil_tipo_id
        const perfisComMesmoTipo = perfisNaTela.filter(
            perf => perf.perfil_tipo_id == perfilAInserir.perfil_tipo_id
        );

        // Verifica se ultrapassou o limite permitido
        if (perfisComMesmoTipo.length) {
            CommonFunctions.generateNotification(`Este perfil já foi adicionado.`, 'warning');
            return false;
        }
        return true;
    }

    async _inserirPerfil(perfil, validarLimite = false) {
        const self = this;
        const divPerfil = $(`#divPerfil${self._objConfigs.sufixo}`);

        const nomePerfil = perfil?.perfil_tipo?.nome || 'Perfil Desconhecido';

        if (validarLimite && !self.#verificaPerfilAdicionado(perfil)) {
            return false;
        }

        // Gera o ID e define a rota de edição
        perfil.idCol = UUIDHelper.generateUUID();
        !perfil.id ? perfil.ativo_bln = true : null;

        // Botão de status (ativo/inativo)
        const btnStatus = `
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" role="switch" id="ckbAtivo${perfil.idCol}" name="ativo_bln" ${perfil?.ativo_bln || !perfil.id ? 'checked' : ''}>
                <label class="form-check-label" for="ckbAtivo${perfil.idCol}">Ativo</label>
            </div>
        `;

        // Botão de remoção
        const btnRemover = !perfil.id ? `
            <button type="button" class="btn btn-outline-danger border-0 btn-sm btn-delete" title="Excluir perfil ${nomePerfil}">
                Remover
            </button>
        ` : '';

        // Texto de perfil não cadastrado
        const textoNaoCadastrado = (!perfil.id) ? `
            <div class="form-text fst-italic">Perfil ainda não cadastrado</div>
        ` : '';

        // Montagem do card do perfil
        const strCard = `
            <div id="${perfil.idCol}" class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center justify-content-between mb-0">
                            <span class="text-truncate spanTitle" title="${perfil.perfil_tipo?.descricao || 'Descrição não disponível'}">${nomePerfil}</span>
                            <div>${btnRemover}</div>
                        </h5>
                        ${textoNaoCadastrado}
                        ${btnStatus}
                    </div>
                </div>
            </div>
        `;

        // Adiciona o card ao DOM e aos perfis na tela
        divPerfil.append(strCard);
        self.#addEventosPerfil(perfil);
        self.getPerfisNaTela.push(perfil);

        return true;
    }

    #eventosAposInserirOuRemoverPerfil() {
        const self = this;

        // Verifica se o _parentInstance existe e se tem o método getUsuarioDomainsModule
        if (self._parentInstance && typeof self._parentInstance.getUsuarioDomainsModule === 'function') {
            const usuarioDomainsModule = self._parentInstance.getUsuarioDomainsModule;

            // Verifica se o retorno possui a função _verificaPerfisNaTela
            if (usuarioDomainsModule && typeof usuarioDomainsModule._verificaPerfisNaTela === 'function') {
                usuarioDomainsModule._verificaPerfisNaTela();
            } else {
                console.warn('A função _verificaPerfisNaTela não está disponível no objeto retornado por getUsuarioDomainsModule.');
            }
        }
    }

    #pesquisaIndexPerfilNaTela(item, prop = 'idCol') {
        const self = this;
        return self.getPerfisNaTela.findIndex(doc => doc[prop] === item[prop]);
    }

    async #addEventosPerfil(perfil) {
        const self = this;

        $(`#${perfil.idCol}`).find(`.btn-delete`).click(async function () {
            try {
                const perfNaTela = self.getPerfisNaTela;
                const indexPerf = self.#pesquisaIndexPerfilNaTela(perfil);

                if (indexPerf === -1) return;

                $(`#${perfNaTela[indexPerf].idCol}`).remove();
                perfNaTela.splice(indexPerf, 1);
                self.#eventosAposInserirOuRemoverPerfil();
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            }
        });

        // Evento de check para inativação de perfil
        $(`#${perfil.idCol}`).find(`input[name="ativo_bln"]`).change(function () {
            try {
                const perfNaTela = self.getPerfisNaTela;
                const indexPerf = self.#pesquisaIndexPerfilNaTela(perfil);

                if (indexPerf === -1) return;

                perfNaTela[indexPerf].ativo_bln = $(this).is(':checked');
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            }
        });
    }

    _retornaPerfilsNaTelaSaveButonAction() {
        const self = this;
        return self.getPerfisNaTela.map(i => {
            return {
                id: i.id,
                perfil_tipo_id: i.perfil_tipo_id,
                ativo_bln: i.ativo_bln,
            }
        });
    }

    saveButtonActionEspecificoPerfil(data) {
        const self = this;

        data.perfis = self._retornaPerfilsNaTelaSaveButonAction();
        return data;
    }

    saveVerificationsEspecificoPerfil(data, setFocus, returnForcedFalse) {

        const self = this;
        let blnSave = !returnForcedFalse;

        if (self.getPerfisNaTela.length === 0) {
            const focoTabPerfil = $(`#painelPerfil${self._objConfigs.sufixo}-tab`);
            if (setFocus) focoTabPerfil.trigger('click');
            CommonFunctions.generateNotification('Nenhum perfil foi selecionado. Por favor, selecione ao menos um perfil.', 'warning');
            blnSave = false;
        }

        return blnSave;
    }

    // _inserirPerfilObrigatorio() {
    //     const self = this;
    //     const novoPerfil = {
    //         perfil_tipo_id: self._objConfigs.data.pessoa_perfil_tipo_id,
    //         perfil_tipo: window.Details.PessoaPerfilTipoEnum.filter(item =>
    //             item.id == self._objConfigs.data.pessoa_perfil_tipo_id)[0]
    //     }
    //     self._inserirPerfil(novoPerfil, false, true);
    // }
}