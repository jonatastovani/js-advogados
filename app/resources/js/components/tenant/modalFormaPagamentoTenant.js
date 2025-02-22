import { commonFunctions } from "../../commons/commonFunctions";
import { enumAction } from "../../commons/enumAction";
import { modalSearchAndFormRegistration } from "../../commons/modal/modalSearchAndFormRegistration";
import { modalContaTenant } from "./modalContaTenant";

export class modalFormaPagamentoTenant extends modalSearchAndFormRegistration {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.baseFormaPagamento,
                urlSearch: `${window.apiRoutes.baseFormaPagamento}/consulta-filtros`,
            }
        },
        url: {
            baseContas: window.apiRoutes.baseContas,
        },
        sufixo: 'ModalFormaPagamentoTenant',
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        selecteds: [],
    };

    constructor() {
        super({
            idModal: "#modalFormaPagamentoTenant",
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
            self._updateTitleRegistration('Nova Forma de Pagamento');
        });

        $(`${self.getIdModal} #formDataSearch${self.getSufixo}`)
            .find('.btnBuscar').on('click', async (e) => {
                e.preventDefault();
                await self._executarBusca();
            })
            .trigger('click');

        commonFunctions.fillSelect(modal.find(`select[name="conta_id"]`), self._objConfigs.url.baseContas);

        commonFunctions.handleModal(self, modal.find('.openModalConta'), new modalContaTenant(), self.#buscarContas.bind(self));

        // modal.find('.openModalConta').on('click', async function () {
        //     const btn = $(this);
        //     commonFunctions.simulateLoading(btn);
        //     try {
        //         const objModal = new modalContaTenant();
        //         objModal.setDataEnvModal = {
        //             attributes: {
        //                 select: {
        //                     quantity: 1,
        //                     autoReturn: true,
        //                 }
        //             }
        //         }
        //         await self._modalHideShow(false);
        //         const response = await objModal.modalOpen();
        //         if (response.refresh) {
        //             if (response.selected) {
        //                 self.#buscarContas(response.selected.id);
        //             } else {
        //                 self.#buscarContas();
        //             }
        //         }
        //     } catch (error) {
        //         commonFunctions.generateNotificationErrorCatch(error);
        //     } finally {
        //         commonFunctions.simulateLoading(btn, false);
        //         await self._modalHideShow();
        //     }
        // });


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

        const ativoBln = item.ativo_bln ? 'Sim' : 'Não';

        $(tbody).append(`
            <tr id="${item.idTr}" data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${btnsDropDown}
                    </div>
                </td>
                <td class="text-nowrap text-truncate" title="${item.nome}">${item.nome}</td>
                <td class="text-nowrap text-truncate" title="${item.conta.nome}">${item.conta.nome}</td>
                <td class="text-nowrap text-truncate" title="${ativoBln}">${ativoBln}</td>
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
                    form.find('select[name="conta_id"]').val(responseData.conta_id);
                    form.find('input[name="ativo_bln"]').prop('checked', responseData.ativo_bln);
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
                title: `Exclusão de Forma de Pagamento`,
                message: `Confirma a exclusão da Forma de Pagamento <b>${item.nome}</b>?`,
                success: `Forma de Pagamento excluída com sucesso!`,
                button: this
            });
        });
    }

    async #buscarContas(selected_id = null) {
        try {
            const self = this;
            let options = selected_id ? { selectedIdOption: selected_id } : {};
            const select = $(self.getIdModal).find('select[name="conta_id"]');
            await commonFunctions.fillSelect(select, self._objConfigs.url.baseContas, options);
            return true;
        } catch (error) {
            return false;
        }
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
        let blnSave = commonFunctions.verificationData(data.nome, { field: formRegistration.find('input[name="nome"]'), messageInvalid: 'O nome da forma de pagamento deve ser informado.', setFocus: true });
        blnSave = commonFunctions.verificationData(data.conta_id, { field: formRegistration.find('select[name="conta_id"]'), messageInvalid: 'Uma conta deve ser selecionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        return blnSave;
    }
}