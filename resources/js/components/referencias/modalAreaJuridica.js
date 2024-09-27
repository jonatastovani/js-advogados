import { commonFunctions } from "../../commons/commonFunctions";
import { enumAction } from "../../commons/enumAction";
import { modalSearchAndFormRegistration } from "../../commons/modal/modalSearchAndFormRegistration";

export class modalAreaJuridica extends modalSearchAndFormRegistration {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        formRegistros: true,
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.baseAreaJuridica,
                urlSearch: `${window.apiRoutes.baseAreaJuridica}/consulta-filtros`,
            }
        },
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
    };

    constructor() {
        super({
            idModal: "#modalAreaJuridica",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = Object.assign(this._promisseReturnValue, this.#promisseReturnValue);

        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        const self = this;
        const modal = $(self.getIdModal);
        const formDataSearchModalAreaJuridica = modal.find('#formDataSearchmodalAreaJuridica');

        modal.find('.btn-new-register').on('click', async () => {
            self._updateTitleRegistration('Nova Área Jurídica');
        });

        formDataSearchModalAreaJuridica.find('.btnBuscar').on('click', async (e) => {
            e.preventDefault();
            self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
            self._generateQueryFilters();
        })
            .trigger('click');
    }

    async insertTableData(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        $(tbody).append(`
            <tr id="${item.idTr}" data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm btn-edit" title="Editar registro"><i class="bi bi-pencil"></i></button>
                    </div>
                </td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${item.nome}">${item.nome}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        return true;
    }

    #addEventosRegistrosConsulta(item) {
        const self = this;

        $(`#${item.idTr}`).find(`.btn-edit`).on('click', async function () {
            commonFunctions.simulateLoading($(this));
            try {
                self._clearForm();
                self._idRegister = item.id
                self._action = enumAction.PUT;
                const response = await self._getRecurse();
                if (response?.data) {
                    const responseData = response.data;
                    self._updateTitleRegistration(`Alterar: <b>${responseData.nome}</b>`);
                    const form = $(self.getIdModal).find('.formRegistration');
                    form.find('input[name="nome"]').val(responseData.nome);
                    self._actionsHideShowRegistrationFields(true);
                    self._executeFocusElementOnModal(form.find('input[name="nome"]'));
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading($(this), false);
            }
        });
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data, formRegistration)) {
            self._save(data, self._objConfigs.querys.consultaFiltros.url);
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = commonFunctions.verificationData(data.nome, { field: formRegistration.find('input[name="nome"]'), messageInvalid: 'O nome da Área Jurídica deve ser informado.', setFocus: true });
        return blnSave;
    }
}