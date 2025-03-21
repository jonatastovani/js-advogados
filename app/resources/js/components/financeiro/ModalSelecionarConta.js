import { CommonFunctions } from "../../commons/CommonFunctions";
import { ModalDefault } from "../../commons/modal/ModalDefault";
import { ModalContaTenant } from "../tenant/ModalContaTenant";

export class ModalSelecionarConta extends ModalDefault {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: window.apiRoutes.baseContas,
        },
        sufixo: 'ModalSelecionarConta',
    };

    #dataEnvModal = {
        participacoes: [],
    }

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        refresh: false,
        register: {
            conta_debito_id: undefined,
            conta_movimentar: undefined,
        }
    };

    constructor() {
        super({
            idModal: "#ModalSelecionarConta",
        });

        CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        CommonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
        CommonFunctions.deepMergeObject(this._promisseReturnValue, this.#promisseReturnValue);
    }

    async modalOpen() {
        const self = this;

        try {
            if (self._dataEnvModal.perfil.perfil_tipo_id == window.Enums.PessoaPerfilTipoEnum.EMPRESA) {

                self.#eventosTipoEmpresa(true);
            } else {

                await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando contas...', title: 'Aguarde...', elementFocus: null });

                if (await self.#buscarContas()) {
                    self.#addEventosPadrao();
                }
            }

            self.#renderMensagem();
            await CommonFunctions.loadingModalDisplay(false);
            await self._modalHideShow();
            return await self._modalOpen();

        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return await self._returnPromisseResolve();
        }
    }

    _modalReset() {
        const self = this;
        self.#eventosTipoEmpresa(false);

        const modal = $(self.getIdModal);
        const formRegistration = modal.find('.formRegistration');
        formRegistration.find('select').val(0);
        formRegistration[0].reset();
        formRegistration.find('input, select, textarea')
            .removeClass('is-valid')
            .removeClass('is-invalid')
            .removeAttr('disabled');
    }

    #addEventosPadrao() {
        const self = this;
        const modal = $(self._idModal);

        CommonFunctions.handleModal(self, modal.find('.openModalConta'), ModalContaTenant, self.#buscarContas.bind(self));

        const rbContaDebito = modal.find(`#rbContaDebito${self.getSufixo}`);
        const select = modal.find('select[name="conta_debito_id"]');
        const dataRbCkB = [{
            div_group: modal.find(`.divGroupContaDebito`),
            button: rbContaDebito,
            input: [select]
        }];

        CommonFunctions.eventRBCkBHidden(rbContaDebito, dataRbCkB);
        CommonFunctions.eventRBCkBHidden(modal.find(`#rbContaOrigem${self.getSufixo}`), dataRbCkB);
        rbContaDebito.trigger('change');
        self._executeFocusElementOnModal(select, 1000);
    }

    #eventosTipoEmpresa(bln = false) {
        const self = this;
        const divParticipanteParceiro = $(`${self.getIdModal} .divParticipanteParceiro`);

        if (bln) {
            divParticipanteParceiro.hide('fast');
            divParticipanteParceiro.find('select, input').attr('disabled', 'disabled');
            self._updateModalTitle('Confirmação')

        } else {
            divParticipanteParceiro.show('fast');
            self._resetDefaultTitle();
        }

    }

    #renderMensagem() {
        const self = this;
        const message = `Confirma o repasse/compensação da consulta em tela?`;
        $(`${self.getIdModal} .messageConfirmacao`).html(message);
    }

    async #buscarContas(selected_id = null) {
        const self = this;

        try {
            let options = { outInstanceParentBln: true };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#conta_debito_id${self.getSufixo}`);
            await CommonFunctions.fillSelect(select, self._objConfigs.url.base, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = CommonFunctions.getInputsValues(formRegistration[0]);
        data.perfil_tipo_id = self._dataEnvModal.perfil.perfil_tipo_id;

        if (self.#saveVerifications(data, formRegistration)) {
            try {
                self._promisseReturnValue.register = data;
                self._promisseReturnValue.refresh = true;
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                self._setEndTimer = true;
            }
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = true;

        if (data.perfil_tipo_id != window.Enums.PessoaPerfilTipoEnum.EMPRESA) {
            if (data.conta_movimentar == 'conta_debito') {
                blnSave = CommonFunctions.verificationData(data.conta_debito_id, {
                    field: formRegistration.find('select[name="conta_debito_id"]'),
                    messageInvalid: 'Selecione uma conta.', setFocus: true
                });
            }
        }

        return blnSave;
    }

}
