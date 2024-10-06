import { commonFunctions } from "../../commons/commonFunctions";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { modalConta } from "../financeiro/modalConta";

export class modalServicoPagamento extends modalRegistrationAndEditing {

    #dataEnvModal = {
        idRegister: undefined
    }

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: window.apiRoutes.basePermissoes,
        sufixo: 'ModalServicoPagamento',
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        refresh: false,
        // selecteds: []
    };

    constructor() {
        super({
            idModal: "#modalServicoPagamento",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = Object.assign(this._promisseReturnValue, this.#promisseReturnValue);
        this._dataEnvModal = Object.assign(this._dataEnvModal, this.#dataEnvModal);
        this._action = enumAction.POST;
        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
        if (self._dataEnvModal.idRegister) {
            await self.#buscarDados()
        }
        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        this.#eventosBotoes();
        this.#buscarContas();
    }

    #eventosBotoes() {
        const self = this;
        const modal = $(self._idModal);

        modal.find('.openModalConta').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalConta();
                objModal.setDataEnvModal = {
                    attributes: {
                        select: {
                            quantity: 1,
                            autoReturn: true,
                        }
                    }
                }
                await self._modalHideShow(false);
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    if (response.selecteds.length > 0) {
                        const item = response.selecteds[0];
                        self.#buscarContas(item.id);
                    } else {
                        self.#buscarContas();
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
                await self._modalHideShow();
            }
        });

        modal.find('select[name="modulo_id"]').on('change', async function () {
            self.#buscarGrupos($(this).val());
            self.#buscarPermissoesPai($(this).val());
        });
    }

    _modalReset() {
        super._modalReset();
        const self = this;
        $(self.getIdModal).find(`#dados-pagamento${self._objConfigs.sufixo}-tab`).trigger('click');
    }

    async #buscarContas(selected_id = null) {
        const self = this;
        let options = selected_id ? { selectedIdOption: selected_id } : {};
        const selModulo = $(self.getIdModal).find('select[name="conta_id"]');
        await commonFunctions.fillSelect(selModulo, window.apiRoutes.baseContas, options);
    }

    async #buscarGrupos(modulo_id, selected_id = null) {
        const self = this;
        const selGrupo = $(self.getIdModal).find('select[name="grupo_id"]');
        if (!modulo_id) {
            selGrupo.html('<option value="0">Selecione o módulo</option>');
            return;
        };
        let options = selected_id ? { selectedIdOption: selected_id } : {};
        await commonFunctions.fillSelect(selGrupo, `${window.apiRoutes.baseGrupos}/modulo/${modulo_id}`, options);
    }

    async #buscarPermissoesPai(modulo_id, selected_id = null) {
        const self = this;
        const selPermissaoPai = $(self.getIdModal).find('select[name="permissao_pai_id"]');
        if (!modulo_id) {
            selPermissaoPai.html('<option value="0">Selecione o módulo</option>');
            return;
        };
        let options = selected_id ? { selectedIdOption: selected_id } : {};
        const url = self._dataEnvModal.idRegister ? `${window.apiRoutes.basePermissoes}/modulo/${modulo_id}/admin/exceto-permissao/${self._dataEnvModal.idRegister}` : `${window.apiRoutes.basePermissoes}/modulo/${modulo_id}/admin`;
        await commonFunctions.fillSelect(selPermissaoPai, url, options);
    }

    async #buscarDados() {
        const self = this;

        await commonFunctions.loadingModalDisplay();
        try {
            self._clearForm();
            self._action = enumAction.PUT;
            const response = await self._getRecurse();
            if (response?.data) {
                const responseData = response.data;
                self._updateModalTitle(`Alterar: <b>${responseData.nome}</b>`);
                const form = $(self.getIdModal).find('.formRegistration');
                form.find('input[name="nome"]').val(responseData.nome);
                form.find('input[name="nome_completo"]').val(responseData.nome_completo);
                form.find('textarea[name="descricao"]').val(responseData.descricao);
                form.find('input[name="ativo"]').prop('checked', responseData.ativo);

                form.find('input[name="permite_subst_bln"]').prop('checked', responseData.permite_subst_bln);
                form.find('input[name="gerencia_perm_bln"]').prop('checked', responseData.gerencia_perm_bln);

                if (responseData.config?.grupo?.modulo_id) {
                    await self.#buscarContas(responseData.config.grupo.modulo_id);
                    form.find('select[name="grupo_id"]').val(responseData.config.grupo_id);
                    form.find('select[name="permissao_pai_id"]').val(responseData.config.permissao_pai_id);
                } else {
                    commonFunctions.generateNotification('Esta permissão não possui configuração cadastrada. Favor completar o cadastro.', 'warning');
                    $(self.getIdModal).find('#configuracoesModalServicoPagamento-tab').trigger('click');
                }
                self._executeFocusElementOnModal(form.find('input[name="nome"]'));
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data, formRegistration)) {
            self._save(data, window.apiRoutes.basePermissoes);
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = commonFunctions.verificationData(data.nome, { field: formRegistration.find('input[name="nome"]'), messageInvalid: 'O nome do grupo deve ser informado.', setFocus: true });
        blnSave = commonFunctions.verificationData(data.descricao, { field: formRegistration.find('input[name="descricao"]'), messageInvalid: 'Uma descrição deve ser adicionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        blnSave = commonFunctions.verificationData(data.grupo_id, { field: formRegistration.find('select[name="grupo_id"]'), messageInvalid: 'A permissão deve pertencer a um grupo, selecione um grupo.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        return blnSave;
    }

}