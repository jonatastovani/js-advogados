import { commonFunctions } from "../../commons/commonFunctions";
import { enumAction } from "../../commons/enumAction";
import { ModalSearchAndFormRegistration } from "../../commons/modal/ModalSearchAndFormRegistration";
import { modalCode } from "./modalCode";

export class modalPermissaoGrupo extends ModalSearchAndFormRegistration {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        formRegistros: true,
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.basePermissoesGrupos,
                urlSearch: `${window.apiRoutes.basePermissoesGrupos}/consulta-filtros`,
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
            idModal: "#modalPermissaoGrupo",
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
        const modal = $(self._idModal);
        const formDataSearchModalPermissaoGrupo = $(modal).find('#formDataSearchModalPermissaoGrupo');
        self._objConfigs.typeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
        self._generateQueryFilters();

        self.#buscarModulos();

        modal.find('.btn-new-register').on('click', async () => {
            self._updateTitleRegistration('Novo Grupo de Permissões');
        });

        formDataSearchModalPermissaoGrupo.find('.btnBuscar').on('click', async (e) => {
            e.preventDefault();
            self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
            self._generateQueryFilters();
        });

        modal.find('select[name="modulo_id"]').on('change', async function () {
            self.#buscarGruposPai($(this).val());
        });
    }

    async #buscarModulos(selected_id = null) {
        const self = this;
        let options = selected_id ? { selectedIdOption: selected_id } : {};
        await commonFunctions.fillSelect($(self.getIdModal).find('select[name="modulo_id"]'), window.apiRoutes.baseModulos, options);
        await self.#buscarGruposPai();
    }

    async #buscarGruposPai(selected_id = null) {
        const self = this;
        const selGrupoPai = $(self.getIdModal).find('select[name="grupo_pai_id"]');
        const modulo_id = $(self.getIdModal).find('select[name="modulo_id"]').val();
        if (!modulo_id) {
            selGrupoPai.html('<option value="0">Selecione o módulo</option>');
            return;
        };

        let options = selected_id ? { selectedIdOption: selected_id } : {};
        const url = self._idRegister ? `${window.apiRoutes.baseGrupos}/modulo/${modulo_id}/exceto-grupo/${self._idRegister}` : `${window.apiRoutes.baseGrupos}/modulo/${modulo_id}`;
        await commonFunctions.fillSelect(selGrupoPai, url, options);
    }

    async insertTableData(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        const ativo = item.ativo ? 'Sim' : 'Não';
        const individuais = item.individuais ? 'Sim' : 'Não';

        $(tbody).append(`
            <tr id=${item.idTr}>
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm btn-edit" title="Editar registro"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm btn-php" title="Renderizar dados para backend PHP"><i class="bi bi-filetype-php"></i></button>
                        <button type="button" class="btn btn-outline-info btn-sm btn-view" title="Visualizar detalhes"><i class="fa-solid fa-circle-info"></i></button>
                    </div>
                </td>
                <td class="text-center text-nowrap">${item.id}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${item.nome}">${item.nome}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${item.descricao ?? ''}">${item.descricao ?? '**'}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${item.modulo?.nome ?? ''}">${item.modulo?.nome ?? '**'}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${item.grupo_pai?.nome ?? ''}">${item.grupo_pai?.nome ?? '**'}</td>
                <td class="text-nowrap">${individuais}</td>
                <td class="text-nowrap">${ativo}</td>
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
                    // form.find('select[name="modulo_id"]').val(responseData.modulo_id);
                    form.find('textarea[name="descricao"]').val(responseData.descricao);
                    form.find('input[name="individuais"]').prop('checked', responseData.individuais);
                    form.find('input[name="ativo"]').prop('checked', responseData.ativo);
                    await self.#buscarModulos(responseData.modulo_id);
                    form.find('select[name="grupo_pai_id"]').val(responseData.grupo_pai_id);
                    self._actionsHideShowRegistrationFields(true);
                    self._executeFocusElementOnModal(form.find('input[name="nome"]'));
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading($(this), false);
            }
        });

        $(`#${item.idTr}`).find(`.btn-php`).on('click', async function () {
            commonFunctions.simulateLoading($(this));
            try {
                const objModal = new modalCode();
                objModal.setDataEnvModal = {
                    idRegister: item.id,
                    url: `${window.apiRoutes.baseGrupos}/php`,
                };
                await self._modalHideShow(false);
                const response = await objModal.modalOpen();
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                await self._modalHideShow();
                commonFunctions.simulateLoading($(this), false);
            }
        });

        // tr.find(`.btn-delete`).click(async function () {
        //     self.#delButtonAction(item.id, item.name, this);
        // });

        // modal.find('.btnBuscarPessoas').on('click', async function () {
        //     const btn = $(this);
        //     commonFunctions.simulateLoading(btn);
        //     try {
        //         const obj = new modalConsultaPessoas();
        //         await self._modalHideShow(false);
        //         const response = await obj.modalOpen();
        //         console.log(response);
        //     } catch (error) {
        //         commonFunctions.generateNotificationErrorCatch(error);
        //     } finally {
        //         await self._modalHideShow();
        //         commonFunctions.simulateLoading(btn, false);
        //     }
        // });

    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data, formRegistration)) {
            self._save(data, window.apiRoutes.basePermissoesGrupos);
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = commonFunctions.verificationData(data.nome, { field: formRegistration.find('input[name="nome"]'), messageInvalid: 'O nome do grupo deve ser informado.', setFocus: true });
        blnSave = commonFunctions.verificationData(data.modulo_id, { field: formRegistration.find('select[name="modulo_id"]'), messageInvalid: 'O módulo deve ser selecionado.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        return blnSave;
    }

    // async #delButtonAction(idDel, nameDel, button = null) {
    //     const self = this;
    //     try {
    //         const obj = new modalMessage();
    //         obj.setTitle = 'Exclusão de Item';
    //         obj.setMessage = `Confirma a exclusão do Item <b>${nameDel}</b>?`;
    //         obj.setFocusElementWhenClosingModal = button;
    //         await self.#modalHideShow(false);
    //         const result = await obj.modalOpen();
    //         if (result) {
    //             self.#delRecurse(idDel);
    //         }
    //     } catch (error) {
    //         console.error(error);
    //     } finally {
    //         await self.#modalHideShow(true);
    //     }
    // }

    // async #delRecurse(idDel) {
    //     const self = this;
    //     try {
    //         const obj = new conectAjax(self.#urlApi);
    //         obj.setParam(idDel);
    //         obj.setAction(enumAction.DELETE)
    //         const response = await obj.deleteRequest();
    //         commonFunctions.generateNotification(`Item deletado com sucesso!`, 'success');
    //         self.#promisseReturnValue.refresh = true;
    //         self.modalCancel();
    //         self.generateFilters();
    //     } catch (error) {
    //         console.error(error);
    //         const traceId = error.traceId ? error.traceId : undefined;
    //         commonFunctions.generateNotification(commonFunctions.firstUppercaseLetter(error.message), 'error', { traceId: traceId });
    //     }
    // }

}