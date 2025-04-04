import { CommonFunctions } from "../../commons/CommonFunctions";
import { EnumAction } from "../../commons/EnumAction";
import { ModalRegistrationAndEditing } from "../../commons/modal/ModalRegistrationAndEditing";
import { MasksAndValidateHelpers } from "../../helpers/MasksAndValidateHelpers";

export class ModalEndereco extends ModalRegistrationAndEditing {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: undefined,
            baseCep: window.apiRoutes.baseCep,
        },
        sufixo: 'ModalEndereco',
    };

    #dataEnvModal = {
        register: undefined,
    }

    constructor() {
        super({
            idModal: "#ModalEndereco",
        });

        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._dataEnvModal = CommonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
        this._action = EnumAction.POST;
    }

    async modalOpen() {
        const self = this;
        let blnOpen = true;

        if (self._dataEnvModal.register) {
            blnOpen = await self.#preencherDados()
        }

        if (!blnOpen) {
            return await self._returnPromisseResolve();
        }

        self.#addEventosPadrao();
        await self._modalHideShow();
        self.setReadyQueueOpen();

        return await self._modalOpen();
    }

    #addEventosPadrao() {
        const self = this;
        const modal = $(self.getIdModal);
        const inputCep = modal.find(`input[name="cep"]`);

        const eventoBuscaCep = () => {
            let cep = inputCep.val().replace(/\D/g, '');
            if (cep.length == 8) {
                self._buscarCep(cep);
            }
        }

        MasksAndValidateHelpers.cepMask(inputCep);
        modal.find(`#${inputCep[0].id}`).on('input', function () {
            eventoBuscaCep();
        });

        modal.find(`#btnBuscaCep${self.getSufixo}`).on('click', function () {
            eventoBuscaCep();
        });

        self.setActionQueueOpen(() => self._executeFocusElementOnModal(inputCep, 1000));

        return true;
    }

    async _buscarCep(cep, options = {}) {
        const self = this;
        const modal = $(self.getIdModal);
        const {
            btnBuscaCep = modal.find(`#btnBuscaCep${self.getSufixo}`),
        } = options;

        try {
            CommonFunctions.simulateLoading(btnBuscaCep);
            const response = await self._getRecurse({
                urlApi: self._objConfigs.url.baseCep,
                idRegister: cep
            });
            if (!response) { return false; }

            const responseData = response.data;
            modal.find(`input[name="logradouro"]`).val(responseData.street);
            modal.find(`input[name="bairro"]`).val(responseData.neighborhood);
            modal.find(`input[name="cidade"]`).val(responseData.city);
            modal.find(`input[name="estado"]`).val(responseData.state);
            modal.find(`input[name="pais"]`).val('Brasil');
            self._executeFocusElementOnModal(modal.find(`input[name="numero"]`));
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return false;
        } finally {
            CommonFunctions.simulateLoading(btnBuscaCep, false);
        }

    }

    async #preencherDados() {
        const self = this;

        try {
            await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando dados do endereço...', title: 'Aguarde...', elementFocus: null });
            self._clearForm();
            const registerData = self._dataEnvModal.register;

            if (registerData) {
                self._updateModalTitle(`Alterar: <b>${registerData.logradouro}, ${registerData.numero}</b>`);

                const form = $(self.getIdModal).find('.formRegistration');
                form.find('input[name="logradouro"]').val(registerData.logradouro);
                form.find('input[name="numero"]').val(registerData.numero);
                form.find('input[name="complemento"]').val(registerData.complemento);
                form.find('input[name="bairro"]').val(registerData.bairro);
                form.find('input[name="referencia"]').val(registerData.referencia);
                form.find('input[name="cidade"]').val(registerData.cidade);
                form.find('input[name="estado"]').val(registerData.estado);
                form.find('input[name="pais"]').val(registerData.pais);
                form.find('input[name="cep"]').val(MasksAndValidateHelpers.formatCep(registerData.cep));
                return true;
            }
            return false;
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return false;
        } finally {
            await CommonFunctions.loadingModalDisplay(false);
        }

    }

    async saveButtonAction() {
        const self = this;
        const data = self.#obterDados();

        if (await self.#saveVerifications(data)) {
            self._promisseReturnValue.refresh = true;
            self._promisseReturnValue.register = data;
            self._endTimer = true;
        }
    }

    #obterDados() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = CommonFunctions.getInputsValues(formRegistration[0]);

        if (self._dataEnvModal.register) {
            data = CommonFunctions.deepMergeObject(self._dataEnvModal.register, data);
        }
        return data;
    }

    #saveVerifications(data) {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');

        let blnSave = CommonFunctions.verificationData(data.logradouro, {
            field: formRegistration.find('input[name="logradouro"]'),
            messageInvalid: 'O logradouro deve ser informado.', setFocus: true
        });

        blnSave = CommonFunctions.verificationData(data.numero, {
            field: formRegistration.find('input[name="numero"]'),
            messageInvalid: 'O número deve ser informado.',
            setFocus: blnSave == true,
            returnForcedFalse: blnSave == false
        });

        blnSave = CommonFunctions.verificationData(data.bairro, {
            field: formRegistration.find('input[name="bairro"]'),
            messageInvalid: 'O bairro deve ser informado.',
            setFocus: blnSave == true,
            returnForcedFalse: blnSave == false
        });

        blnSave = CommonFunctions.verificationData(data.cidade, {
            field: formRegistration.find('input[name="cidade"]'),
            messageInvalid: 'A cidade deve ser informada.',
            setFocus: blnSave == true,
            returnForcedFalse: blnSave == false
        });

        blnSave = CommonFunctions.verificationData(data.estado, {
            field: formRegistration.find('input[name="estado"]'),
            messageInvalid: 'O Estado deve ser informado.',
            setFocus: blnSave == true,
            returnForcedFalse: blnSave == false
        });

        return blnSave;
    }
}
