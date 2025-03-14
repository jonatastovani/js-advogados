import { commonFunctions } from "../../commons/commonFunctions";
import { modalDefault } from "../../commons/modal/modalDefault";
import { UUIDHelper } from "../../helpers/UUIDHelper";

export class modalSelecionarPerfil extends modalDefault {

    #dataEnvModal = {
        perfis_opcoes: [],
    };

    constructor() {
        const objConfigs = {
            modalConfig: {
                modalCloseOnEscape: false,
                modalCloseOnClickBackdrop: false,
            },
        }
        super({
            idModal: "#modalSelecionarPerfil",
            objConfigs: objConfigs
        });

        this._dataEnvModal = Object.assign(
            this._dataEnvModal,
            this.#dataEnvModal
        );
    }

    async modalOpen() {
        const self = this;
        // await commonFunctions.loadingModalDisplay();
        if (!await self.#preencherOpcoes()) {
            return self._returnPromisseResolve();
        }
        await self._modalHideShow();
        return await self._modalOpen();
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data, formRegistration)) {
            self._save(data, self._objConfigs.url.base);
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = commonFunctions.verificationData(data.titulo, { field: formRegistration.find('input[name="titulo"]'), messageInvalid: 'O título deve ser informado.', setFocus: true });
        blnSave = commonFunctions.verificationData(data.descricao, { field: formRegistration.find('textarea[name="descricao"]'), messageInvalid: 'Uma descrição deve ser adicionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        return blnSave;
    }

    async #preencherOpcoes() {
        const self = this;
        $(self.getIdModal).find('.rowButtons').html('');
        try {
            for (const perfilOpcao of self._dataEnvModal.perfis_opcoes) {
                const perfil = window.Details.PessoaPerfilTipoEnum.find(item => item.id == perfilOpcao.perfil_tipo_id);
                if (!perfil) {
                    console.error('Tipo de perfil inexistente.', perfilOpcao);
                    commonFunctions.generateNotification('Tipo de perfil inexistente.', 'error');
                    return false;
                }
                perfilOpcao.idButton = await self.#inserirOpcao(perfil);
                self.#addEventosBotoes(perfilOpcao);
            }
            return true;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async #inserirOpcao(perfil) {
        const self = this;
        const rowButtons = $(self.getIdModal).find('.rowButtons');

        const idButton = UUIDHelper.generateUUID();
        const strButton = `
            <div id="${idButton}" class="col">
                <button type="button" class="btn btn-dark w-100 border-0">
                    <h5 class="card-title">${perfil.nome}</h5>
                    <p class="card-text">${perfil.descricao}</p>
                </button>
            </div>`;

        rowButtons.append(strButton);
        return idButton;
    }

    async #addEventosBotoes(perfil) {
        const self = this;
        const modal = $(self.getIdModal);

        modal.find(`#${perfil.idButton}`).on('click', async function () {
            delete perfil.idButton;
            self._promisseReturnValue.register = perfil;
            self._promisseReturnValue.refresh = true;
            self._endTimer = true;
        });
    }
}
