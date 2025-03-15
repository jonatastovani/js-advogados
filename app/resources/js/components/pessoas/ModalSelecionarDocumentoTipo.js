import { CommonFunctions } from "../../commons/CommonFunctions";
import { EnumAction } from "../../commons/EnumAction";
import { ModalDefault } from "../../commons/modal/ModalDefault";
import { ModalPessoaDocumento } from "./ModalPessoaDocumento";

export class ModalSelecionarDocumentoTipo extends ModalDefault {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: `${window.apiRoutes.baseDocumentoTipoTenants}/pessoa-tipo-aplicavel`,
        },
        sufixo: 'ModalSelecionarDocumentoTipo',
    };

    #dataEnvModal = {
        pessoa_tipo_aplicavel: [],
    }

    constructor() {
        super({
            idModal: "#ModalSelecionarDocumentoTipo",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._dataEnvModal = Object.assign(this._dataEnvModal, this.#dataEnvModal);
        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
        let blnOpen = false;
        await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando tipos de documento...', title: 'Aguarde...', elementFocus: null });

        if (!self._dataEnvModal.pessoa_tipo_aplicavel) {
            CommonFunctions.generateNotification('Tipo de pessoa aplicável não informado.', 'error');
            return await self._returnPromisseResolve();
        }

        try {
            await self.#buscarDocumentoTipos();
            blnOpen = true;
        } catch (error) {
            blnOpen = false;
        } finally {
            await CommonFunctions.loadingModalDisplay(false);
        }

        if (!blnOpen) {
            return await self._returnPromisseResolve();
        }
        await self._modalHideShow();
        return await self._modalOpen();
    }

    _modalReset() {
        const self = this;
        const modal = $(self.getIdModal);
        const formRegistration = modal.find('.formRegistration');
        formRegistration.find('select').val(0);
        formRegistration[0].reset();
        formRegistration.find('input, select, textarea').removeClass('is-valid').removeClass('is-invalid');
    }

    #addEventosPadrao() {
        this.#eventosBotoes();
    }

    #eventosBotoes() {
        const self = this;
        const modal = $(self._idModal);

        modal.find('.openModalDocumentoTipoTenant').on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                CommonFunctions.generateNotification('Funcionalidade para criar e alterar formas de documento, em desenvolvimento.', 'warning');
                // const objModal = new ModalContaTenant();
                // objModal.setDataEnvModal = {
                //     attributes: {
                //         select: {
                //             quantity: 1,
                //             autoReturn: true,
                //         }
                //     }
                // }
                // await self._modalHideShow(false);
                // const response = await objModal.modalOpen();
                // if (response.refresh) {
                //     if (response.selecteds.length > 0) {
                //         const item = response.selecteds[0];
                //         self.#buscarContas(item.id);
                //     } else {
                //         self.#buscarContas();
                //     }
                // }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
                // await self._modalHideShow();
            }
        });
    }

    async #buscarDocumentoTipos(selected_id = null) {
        const self = this;
        let options = {
            typeRequest: EnumAction.POST,
            envData: {
                pessoa_tipo_aplicavel: self._dataEnvModal.pessoa_tipo_aplicavel,
            }
        };

        selected_id ? Object.assign(options, { selectedIdOption: selected_id }) : null;
        const select = $(self.getIdModal).find('select[name="documento_tipo_tenant_id"]');
        return await CommonFunctions.fillSelect(select, self._objConfigs.url.base, options);
    }

    async saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = CommonFunctions.getInputsValues(formRegistration[0]);
        if (self.#saveVerifications(data, formRegistration)) {
            try {
                await self._modalHideShow(false);
                const objModal = new ModalPessoaDocumento();
                objModal._dataEnvModal = {
                    documento_tipo_tenant_id: data.documento_tipo_tenant_id,
                }
                self._promisseReturnValue = await objModal.modalOpen();
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                self._setEndTimer = true;
            }
        }
    }

    #saveVerifications(data, formRegistration) {
        return CommonFunctions.verificationData(data.documento_tipo_tenant_id, { field: formRegistration.find('select[name="documento_tipo_tenant_id"]'), messageInvalid: 'Selecione um tipo de documento.', setFocus: true });
    }

}
