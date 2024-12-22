import { commonFunctions } from "../../commons/commonFunctions";
import { enumAction } from "../../commons/enumAction";
import { modalSearchAndFormRegistration } from "../../commons/modal/modalSearchAndFormRegistration";

export class modalContaTenant extends modalSearchAndFormRegistration {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        formRegistros: true,
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.baseContas,
                urlSearch: `${window.apiRoutes.baseContas}/consulta-filtros`,
                sufixo: 'ModalContaTenant',
            }
        },
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
        const formDataSearchModalConta = modal.find(`#formDataSearch${self._objConfigs.querys.consultaFiltros.sufixo}`);

        modal.find('.btn-new-register').on('click', async () => {
            self._updateTitleRegistration('Nova Conta');
        });

        formDataSearchModalConta.find('.btnBuscar').on('click', async (e) => {
            e.preventDefault();
            self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
            self._generateQueryFilters();
        })
            .trigger('click');

        commonFunctions.fillSelect(modal.find(`select[name="conta_subtipo_id"]`), window.apiRoutes.baseContasSubtipo);

        commonFunctions.fillSelect(modal.find(`select[name="conta_status_id"]`), window.apiRoutes.baseContasStatus, { selectedIdOption: 1 });
    }

    async insertTableData(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        let strBtns = self.#HtmlBtnSelect(item);
        strBtns += self.#HtmlBtnEdit(item);
        strBtns += self.#HtmlBtnDelete(item);

        let saldo = 0;
        if (item.ultima_movimentacao) {
            saldo = item.ultima_movimentacao.saldo_atualizado;
        }
        saldo = commonFunctions.formatNumberToCurrency(saldo);

        $(tbody).append(`
            <tr id="${item.idTr}" data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
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

    #HtmlBtnEdit(item) {
        return `<button type="button" class="btn btn-outline-primary btn-sm btn-edit" title="Editar conta ${item.nome}"><i class="bi bi-pencil"></i></button>`;
    }

    #HtmlBtnSelect(item) {
        const self = this;
        if (self._dataEnvModal?.attributes?.select) {
            return `<button type="button" class="btn btn-outline-success btn-sm btn-select" title="Selecionar conta ${item.nome}"><i class="bi bi-check-lg"></i></button>`
        }
        return '';
    }

    #HtmlBtnDelete(item) {
        const self = this;
        return `<button type="button" class="btn btn-outline-danger btn-sm btn-delete" title="Excluir conta ${item.nome}"><i class="bi bi-trash"></i></button>`
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