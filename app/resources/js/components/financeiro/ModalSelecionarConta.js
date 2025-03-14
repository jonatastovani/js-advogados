import { commonFunctions } from "../../commons/commonFunctions";
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

        commonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        commonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
        commonFunctions.deepMergeObject(this._promisseReturnValue, this.#promisseReturnValue);
    }

    async modalOpen() {
        const self = this;

        try {
            if (self._dataEnvModal.perfil.perfil_tipo_id == window.Enums.PessoaPerfilTipoEnum.EMPRESA) {

                self.#eventosTipoEmpresa(true);
            } else {

                await commonFunctions.loadingModalDisplay(true, { message: 'Carregando contas...', title: 'Aguarde...', elementFocus: null });

                if (await self.#buscarContas()) {
                    self.#addEventosPadrao();
                }
            }

            self.#renderMensagem();
            await commonFunctions.loadingModalDisplay(false);
            await self._modalHideShow();
            return await self._modalOpen();

        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
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

        modal.find('.openModalConta').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalContaTenant();
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
                        self.#buscarContas(item.id);
                    } else {
                        self.#buscarContas();
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        const rbContaDebito = modal.find(`#rbContaDebito${self.getSufixo}`);
        const select = modal.find('select[name="conta_debito_id"]');
        const dataRbCkB = [{
            div_group: modal.find(`.divGroupContaDebito`),
            button: rbContaDebito,
            input: [select]
        }];

        commonFunctions.eventRBCkBHidden(rbContaDebito, dataRbCkB);
        commonFunctions.eventRBCkBHidden(modal.find(`#rbContaOrigem${self.getSufixo}`), dataRbCkB);
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
            let options = selected_id ? { selectedIdOption: selected_id } : {};
            const select = $(`#conta_debito_id${self.getSufixo}`);
            await commonFunctions.fillSelect(select, self._objConfigs.url.base, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        data.perfil_tipo_id = self._dataEnvModal.perfil.perfil_tipo_id;

        if (self.#saveVerifications(data, formRegistration)) {
            try {
                self._promisseReturnValue.register = data;
                self._promisseReturnValue.refresh = true;
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                self._setEndTimer = true;
            }
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = true;

        if (data.perfil_tipo_id != window.Enums.PessoaPerfilTipoEnum.EMPRESA) {
            if (data.conta_movimentar == 'conta_debito') {
                blnSave = commonFunctions.verificationData(data.conta_debito_id, {
                    field: formRegistration.find('select[name="conta_debito_id"]'),
                    messageInvalid: 'Selecione uma conta.', setFocus: true
                });
            }
        }

        return blnSave;
    }

}
