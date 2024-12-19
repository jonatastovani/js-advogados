import { commonFunctions } from "../../commons/commonFunctions";
import { modalDefault } from "../../commons/modal/modalDefault";

export class modalSelecionarPessoaPerfilTipo extends modalDefault {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: undefined,
        },
        sufixo: 'ModalSelecionarPessoaPerfilTipo',
    };

    #dataEnvModal = {
        pessoa_tipo_aplicavel: [],
    }

    constructor() {
        super({
            idModal: "#modalSelecionarPessoaPerfilTipo",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._dataEnvModal = Object.assign(this._dataEnvModal, this.#dataEnvModal);
        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;

        if (!self._dataEnvModal.pessoa_tipo_aplicavel) {
            commonFunctions.generateNotification('Tipo de pessoa aplicável não informado.', 'error');
            return await self._returnPromisseResolve();
        }

        if (!await self.#buscarPessoaPerfilTipos()) {
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
    }

    async #buscarPessoaPerfilTipos(selected_id = null) {
        const self = this;
        const arrayOpcoes = window.Details.PessoaPerfilTipoEnum;
        const pessoa_tipo_aplicavel = self._dataEnvModal.pessoa_tipo_aplicavel;

        // Filtrar as opções com base no array pessoa_tipo_aplicavel
        const filtrados = arrayOpcoes.filter(item => {
            return item.configuracao.pessoa_tipo_aplicavel.some(tipo =>
                pessoa_tipo_aplicavel.includes(tipo)
            );
        });

        let options = selected_id ? { selectedIdOption: selected_id } : {};

        const select = $(self.getIdModal).find('select[name="perfil_tipo_id"]');
        return await commonFunctions.fillSelectArray(select, filtrados, options);
    }

    async saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        if (self.#saveVerifications(data, formRegistration)) {
            self._promisseReturnValue.register = {
                perfil_tipo_id: data.perfil_tipo_id,
                perfil_tipo: window.Details.PessoaPerfilTipoEnum.filter(item =>
                    item.id == data.perfil_tipo_id)[0]
            };
            self._promisseReturnValue.refresh = true;
            self._endTimer = true;
        }
    }

    #saveVerifications(data, formRegistration) {
        return commonFunctions.verificationData(data.perfil_tipo_id, { field: formRegistration.find('select[name="perfil_tipo_id"]'), messageInvalid: 'Selecione um tipo de perfil.', setFocus: true });
    }

}