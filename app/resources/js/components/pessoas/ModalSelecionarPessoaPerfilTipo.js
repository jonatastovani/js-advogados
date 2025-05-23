import { CommonFunctions } from "../../commons/CommonFunctions";
import { ModalDefault } from "../../commons/modal/ModalDefault";

export class ModalSelecionarPessoaPerfilTipo extends ModalDefault {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: undefined,
        },
        modeNewOrEditingRegister: true, // Inicia com ela verdadeira, pois o formulário estará pronto para edição
        sufixo: 'ModalSelecionarPessoaPerfilTipo',
    };

    #dataEnvModal = {
        pessoa_tipo_aplicavel: [],
    }

    constructor() {
        super({
            idModal: "#ModalSelecionarPessoaPerfilTipo",
        });

        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._dataEnvModal = CommonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
    }

    async modalOpen() {
        const self = this;

        if (!self._dataEnvModal.pessoa_tipo_aplicavel) {
            CommonFunctions.generateNotification('Tipo de pessoa aplicável não informado.', 'error');
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

    async #buscarPessoaPerfilTipos(selected_id = null) {
        const self = this;
        const arrayOpcoes = window.Details.PessoaPerfilTipoEnum;
        const pessoa_tipo_aplicavel = self._dataEnvModal.pessoa_tipo_aplicavel;

        // Filtrar as opções com base no array pessoa_tipo_aplicavel
        let filtrados = arrayOpcoes.filter(item => {
            return item.configuracao.pessoa_tipo_aplicavel.some(tipo =>
                pessoa_tipo_aplicavel.includes(tipo)
            );
        });

        // Filtra o tipo de perfil empresa, quando o form não é do cadastro da empresa
        if (!self._dataEnvModal?.formEmpresa || !self._dataEnvModal.formEmpresa) {
            filtrados = filtrados.filter(item => item.id != window.Enums.PessoaPerfilTipoEnum.EMPRESA);
        }

        let options = selected_id ? { selectedIdOption: selected_id } : {};
        const select = $(self.getIdModal).find('select[name="perfil_tipo_id"]');
        return await CommonFunctions.fillSelectArray(select, filtrados, options);
    }

    async saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = CommonFunctions.getInputsValues(formRegistration[0]);
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
        return CommonFunctions.verificationData(data.perfil_tipo_id, { field: formRegistration.find('select[name="perfil_tipo_id"]'), messageInvalid: 'Selecione um tipo de perfil.', setFocus: true });
    }

}
