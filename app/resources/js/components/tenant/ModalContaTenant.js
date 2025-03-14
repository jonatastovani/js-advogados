import { commonFunctions } from "../../commons/commonFunctions";
import { enumAction } from "../../commons/enumAction";
import { ModalSearchAndFormRegistration } from "../../commons/modal/ModalSearchAndFormRegistration";

export class modalContaTenant extends ModalSearchAndFormRegistration {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.baseContas,
                urlSearch: `${window.apiRoutes.baseContas}/consulta-filtros`,
            }
        },
        sufixo: 'ModalContaTenant',
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        selecteds: [],
    };

    constructor() {
        super({
            idModal: "#modalContaTenant",
        });

        this._objConfigs = commonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = commonFunctions.deepMergeObject(this._promisseReturnValue, this.#promisseReturnValue);

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

        modal.find('.btn-new-register').on('click', async () => {
            self._updateTitleRegistration('Nova Conta');
        });

        commonFunctions.fillSelect(modal.find(`select[name="conta_subtipo_id"]`), window.apiRoutes.baseContasSubtipo);

        commonFunctions.fillSelect(modal.find(`select[name="conta_status_id"]`), window.apiRoutes.baseContasStatus, { selectedIdOption: 1 });
      
        self._executarBusca();
        const queueCheck = self._queueCheckDomainCustom;
        if (this._objConfigs?.formRegister && queueCheck) {
            queueCheck.setReady();
        }
  }

    async _executarBusca() {
        const self = this;
        self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
        await self._generateQueryFilters();
    }

    async insertTableData(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        let btns = `
            <li><button type="button" class="dropdown-item fs-6 btn-edit" title="Editar registro ${item.nome}">Editar</button></li>
            <li><button type="button" class="dropdown-item fs-6 btn-delete" title="Excluir registro ${item.nome}">Excluir</button></li>`;

        let btnSelect = '';
        if (self._dataEnvModal?.attributes?.select) {
            btnSelect = `<button type="button" class="btn btn-outline-success btn-sm btn-select" title="Selecionar registro"><i class="bi bi-check-lg"></i></button>`
        }

        let btnsDropDown = `
            <div class="btn-group">
                ${btnSelect}
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle ${btnSelect ? 'rounded-start-0 border' : ''}" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu">
                        ${btns}
                    </ul>
                </div>
            </div>`;

        let saldo = 0;
        if (item.ultima_movimentacao) {
            saldo = item.ultima_movimentacao.saldo_atualizado;
        }
        saldo = commonFunctions.formatNumberToCurrency(saldo);

        $(tbody).append(`
            <tr id="${item.idTr}" data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${btnsDropDown}
                    </div>
                </td>
                <td class="text-nowrap text-truncate" style="max-width: 10rem" title="${item.nome}">${item.nome}</td>
                <td class="text-nowrap text-truncate" style="max-width: 10rem" title="${saldo}">${saldo}</td>
                <td class="text-nowrap text-truncate" style="max-width: 10rem" title="${item.banco ?? ''}">${item.banco ?? ''}</td>
                <td class="text-nowrap text-truncate" style="max-width: 10rem" title="${item.conta_subtipo.nome}">${item.conta_subtipo.nome}</td>
                <td class="text-nowrap text-truncate" style="max-width: 10rem" title="${item.conta_status.nome}">${item.conta_status.nome}</td>
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
                const response = await self._getRecurse();
                if (response?.data) {
                    self._action = enumAction.PUT;
                    const responseData = response.data;
                    self._updateTitleRegistration(`Alterar: <b>${responseData.nome}</b>`);
                    const form = $(self.getIdModal).find('.formRegistration');
                    form.find('input[name="nome"]').val(responseData.nome);
                    form.find('select[name="conta_subtipo_id"]').val(responseData.conta_subtipo_id);
                    form.find('select[name="conta_status_id"]').val(responseData.conta_status_id);
                    form.find('input[name="banco"]').val(responseData.banco);
                    form.find('textarea[name="descricao"]').val(responseData.descricao);
                    self._actionsHideShowRegistrationFields(true);
                    self._executeFocusElementOnModal(form.find('input[name="nome"]'));
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading($(this), false);
            }
        });

        $(`#${item.idTr}`).find(`.btn-select`).on('click', async function () {
            if (self._dataEnvModal?.attributes?.select) {
                const select = self._dataEnvModal.attributes.select;
                const promisseReturnValue = self._promisseReturnValue;

                const pushSelected = (item) => {

                    if (select?.quantity && select.quantity == 1) {
                        promisseReturnValue.selected = item;
                    } else {
                        promisseReturnValue.selecteds.push(item);
                    }
                    promisseReturnValue.refresh = true;

                    if (select?.autoReturn && select.autoReturn &&
                        (select?.quantity && promisseReturnValue.selecteds.length == select.quantity ||
                            (select?.quantity && select.quantity == 1 && promisseReturnValue.selected))
                    ) {
                        self._setEndTimer = true;
                    }
                }
                pushSelected(item);
            }
        });

        $(`#${item.idTr}`).find(`.btn-delete`).on('click', async function () {
            self._delButtonAction(item.id, item.nome, {
                title: `Exclusão de Conta`,
                message: `Confirma a exclusão da Conta <b>${item.nome}</b>?`,
                success: `Conta excluída com sucesso!`,
                button: this
            });
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
        let blnSave = commonFunctions.verificationData(data.nome, { field: formRegistration.find('input[name="nome"]'), messageInvalid: 'O nome da conta deve ser informado.', setFocus: true });
        blnSave = commonFunctions.verificationData(data.conta_subtipo_id, { field: formRegistration.find('select[name="conta_subtipo_id"]'), messageInvalid: 'Um subtipo de conta deve ser selecionado.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        blnSave = commonFunctions.verificationData(data.conta_status_id, { field: formRegistration.find('select[name="conta_status_id"]'), messageInvalid: 'Uma status de conta deve ser selecionado.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        return blnSave;
    }
}
