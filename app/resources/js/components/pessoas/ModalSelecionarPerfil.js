import { CommonFunctions } from "../../commons/CommonFunctions";
import { ModalDefault } from "../../commons/modal/ModalDefault";
import { UUIDHelper } from "../../helpers/UUIDHelper";

export class ModalSelecionarPerfil extends ModalDefault {

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
            idModal: "#ModalSelecionarPerfil",
            objConfigs: objConfigs
        });

        this._dataEnvModal = Object.assign(
            this._dataEnvModal,
            this.#dataEnvModal
        );
    }

    async modalOpen() {
        const self = this;
        // await CommonFunctions.loadingModalDisplay();
        if (!await self.#preencherOpcoes()) {
            return self._returnPromisseResolve();
        }
        await self._modalHideShow();
        return await self._modalOpen();
    }

    async #preencherOpcoes() {
        const self = this;
        $(self.getIdModal).find('.rowButtons').html('');
        try {
            for (const perfilOpcao of self._dataEnvModal.perfis_opcoes) {
                const perfil = window.Details.PessoaPerfilTipoEnum.find(item => item.id == perfilOpcao.perfil_tipo_id);
                if (!perfil) {
                    console.error('Tipo de perfil inexistente.', perfilOpcao);
                    CommonFunctions.generateNotification('Tipo de perfil inexistente.', 'error');
                    return false;
                }
                perfilOpcao.idButton = await self.#inserirOpcao(perfil);
                self.#addEventosBotoes(perfilOpcao);
            }
            return true;
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
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
