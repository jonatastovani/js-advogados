import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { enumAction } from "../../commons/enumAction";
import { modalMessage } from "../../components/comum/modalMessage";
import { modalAreaJuridica } from "../../components/referencias/modalAreaJuridica";
import { modalServicoAnotacao } from "../../components/servico/modalServicoAnotacao";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { RedirectHelper } from "../../helpers/RedirectHelper";
import SimpleBarHelper from "../../helpers/SimpleBarHelper";
import { URLHelper } from "../../helpers/URLHelper";
import { UUIDHelper } from "../../helpers/UUIDHelper";

class PageServicoForm {

    #sufixo = 'PageServicoForm';
    #objConfigs = {
        url: {
            base: window.apiRoutes.baseServico,
            baseAnotacao: undefined,
            baseAreaJuridica: window.apiRoutes.baseAreaJuridica,
        },
        data: {
        }
    };
    #action;
    #idRegister;

    constructor() {
        this.initEvents();
    }

    initEvents() {
        const self = this;
        self.#addEventosBotoes();
        commonFunctions.addEventsSelect2Api($(`#area_juridica_id${self.#sufixo}`), `${self.#objConfigs.url.baseAreaJuridica}/select2`);
        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self.#idRegister = uuid;
            self.#objConfigs.url.baseAnotacao = `${self.#objConfigs.url.base}/${self.#idRegister}/anotacao`;
            this.#action = enumAction.PUT;
            self.#buscarDados();
        } else {
            this.#action = enumAction.POST;
        }
    }

    #addEventosBotoes() {
        const self = this;

        $('#btnAdicionarAnotacao').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalServicoAnotacao(self.#objConfigs.url.baseAnotacao);
                objModal.setFocusElementWhenClosingModal = btn;
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    self.#inserirAnotacao(response.register);
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $('#btnOpenAreaJuridica').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalAreaJuridica();
                objModal.setDataEnvModal = {
                    attributes: {
                        select: {
                            quantity: 1,
                            autoReturn: true,
                        }
                    }
                }
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    if (response.selecteds.length > 0) {
                        const item = response.selecteds[0];
                        commonFunctions.updateSelect2Value($(`#area_juridica_id${self.#sufixo}`), item.nome, item.id);
                    } else {
                        const area_juridica_id = $(`#area_juridica_id${self.#sufixo}`).val();
                        if (area_juridica_id) {
                            const update = await commonFunctions.getRecurseWithTrashed(`${self.#objConfigs.url.baseInfoSubjCategorias}/${area_juridica_id}`);
                            if (update?.data) {
                                commonFunctions.updateSelect2Value($(`#area_juridica_id${self.#sufixo}`), update.data.nome, update.data.id);
                            }
                        }
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnSave${self.#sufixo}`).on('click', async function (e) {
            e.preventDefault();
            self.#saveButtonAction();
        });

        // const openModalTest = () => {
        //     const objCode = new modalBuscaPessoas();
        //     objCode.modalOpen();
        // }
        // // openModalTest();
    }

    async #inserirAnotacao(item) {
        const self = this;
        const divEnvolvidos = $(`#divAnotacao${self.#sufixo}`);

        item.idCol = UUIDHelper.generateUUID();
        let created_at = '';
        if (item.created_at) {
            created_at = `<span class="text-body-secondary d-block">Criado em ${DateTimeHelper.retornaDadosDataHora(item.created_at, 12)}</span>`;
            item.statusSalvo = true;
        } else {
            item.statusSalvo = false;
        }

        let strBtns = self.#HtmlBtnEdit(item);
        strBtns += self.#HtmlBtnDelete(item);

        const strToHtml = commonFunctions.formatStringToHTML(item.descricao);
        let strCard = `
            <div id="${item.idCol}" class="col">
                <div class="card">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">${item.titulo}</h5>
                            <div class="card-text overflow-auto scrollbar" style="max-height: 10rem;">
                                <p>${strToHtml}</p>
                            </div>
                            <div class="row justify-content-end g-2 gap-2">
                                ${strBtns}
                            </div>
                        </div>
                        <div class="card-footer text-body-secondary">
                            ${created_at}
                        </div>
                    </div>
                </div>
            </div>`;

        divEnvolvidos.append(strCard);
        self.#addEventosAnotacao(item);
        SimpleBarHelper.apply();
        return true;
    }


    #HtmlBtnEdit(item) {
        const self = this;
        return `<button type="button" class="btn btn-outline-primary btn-sm btn-edit w-50" style="max-width: 7rem" title="Editar anotação ${item.titulo}"><i class="bi bi-pencil"></i> Editar</button>`;
    }

    #HtmlBtnDelete(item) {
        const self = this;
        return `<button type="button" class="btn btn-outline-danger btn-sm btn-delete w-50" style="max-width: 7rem" title="Excluir anotação ${item.titulo}"><i class="bi bi-trash"></i> Excluir</button>`
    }

    async #addEventosAnotacao(item) {
        const self = this;

        $(`#${item.idCol}`).find('.btn-edit').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalServicoAnotacao(self.#objConfigs.url.baseAnotacao);
                objModal.setDataEnvModal = {
                    idRegister: item.id,
                };
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    $(`#${item.idCol}`).find('.card-title').text(response.register.titulo);
                    $(`#${item.idCol}`).find('.card-text').text(response.register.descricao);
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });


        $(`#${item.idCol}`).find(`.btn-delete`).click(async function () {
            const response = await self.#delButtonAction(item.id, item.titulo, {
                title: `Exclusão de Anotação`,
                message: `Confirma a exclusão da anotação <b>${item.titulo}</b>?`,
                success: `Anotação excluída com sucesso!`,
                button: this,
                urlApi: self.#objConfigs.url.baseAnotacao,
            });

            if (response) {
                $(`#${item.idCol}`).remove();
            }
        });
    }

    // /**
    //  * Função para inserir uma pessoa na lista de pessoas envolvidas.
    //  * 
    //  * @param {Object} pessoa - Objeto contendo os dados da pessoa.
    //  */
    // #inserirDadosPessoaEnvolvidaNaTela(pessoa) {
    //     const self = this;

    //     // Insere a pessoa no array `pessoasEnvolvidasNaTela`
    //     self.#objConfigs.data.pessoasEnvolvidasNaTela.push({
    //         id: pessoa.id ?? null,
    //         pessoa_tipo_tabela_id: pessoa.pessoa_tipo_tabela_id,
    //         nome: pessoa.nome,
    //         referencia_id: pessoa.referencia_id,
    //         idCol: pessoa.idCol
    //     });

    //     return true;
    // }

    // /**
    //  * Verifica se a pessoa envolvida já está na tela.
    //  * 
    //  * @param {Object} item - O objeto da pessoa que será verificado.
    //  * @returns {Object|null} - Retorna o elemento correspondente se a pessoa já estiver envolvida, ou null caso contrário.
    //  */
    // #verificaPessoaEnvolvidaNaTela(item) {
    //     const self = this;

    //     for (const element of self.#objConfigs.data.pessoasEnvolvidasNaTela) {
    //         if (element.pessoa_tipo_tabela_id == item.pessoa_tipo_tabela_id && element.referencia_id == item.referencia_id) {
    //             return element; // Pessoa já está envolvida
    //         }
    //     }

    //     return null; // Pessoa não encontrada
    // }

    #saveButtonAction() {
        const self = this;
        const formRegistration = $(`#formServico${self.#sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        // data.pessoas_envolvidas = self.#objConfigs.data.pessoasEnvolvidasNaTela;

        if (self.#saveVerifications(data, formRegistration)) {
            self.#save(data, self.#objConfigs.url.base);
        }
        return false;
    }

    #saveVerifications(data, formRegistration) {
        const self = this;
        if (self.#action == enumAction.POST) {
            let blnSave = commonFunctions.verificationData(data.titulo, { field: formRegistration.find('input[name="titulo"]'), messageInvalid: 'O título deve ser informado.', setFocus: true });
            blnSave = commonFunctions.verificationData(data.area_juridica_id, { field: formRegistration.find('select[name="area_juridica_id"]'), messageInvalid: 'A Área Jurídica deve ser selecionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            blnSave = commonFunctions.verificationData(data.descricao, { field: formRegistration.find('textarea[name="descricao"]'), messageInvalid: 'A descrição deve ser preenchida.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            return blnSave;
        }
        return true;
    }

    async #save(data, urlApi, options = {}) {
        const self = this;
        const {
            btnSave = $(`#btnSave${self.#sufixo}`),
        } = options;

        try {
            commonFunctions.simulateLoading(btnSave);
            const obj = new connectAjax(urlApi);
            obj.setAction(self.#action);
            obj.setData(data);
            if (self.#action === enumAction.PUT) {
                obj.setParam(self.#idRegister);
            }
            const response = await obj.envRequest();

            if (response) {
                if (self.#action === enumAction.PUT) {
                    commonFunctions.generateNotification('Dados do serviço alterados com sucesso!', 'success');
                } else {
                    RedirectHelper.redirectWithUUIDMessage(`${window.frontRoutes.frontRedirectForm} / ${response.data.id}`, 'Serviço iniciado com sucesso!', 'success');
                }
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
        finally {
            commonFunctions.simulateLoading(btnSave, false);
        };
    }

    async #buscarDados() {
        const self = this;

        try {
            await commonFunctions.loadingModalDisplay();
            const response = await self.#getRecurse();
            const form = $(`#formServico${self.#sufixo}`);
            if (response?.data) {
                const responseData = response.data;
                form.find('input[name="titulo"]').val(responseData.titulo);
                commonFunctions.updateSelect2Value($(`#area_juridica_id${self.#sufixo}`), responseData.area_juridica.nome, responseData.area_juridica_id);
                form.find('textarea[name="descricao"]').val(responseData.descricao);

                responseData.anotacao.forEach(item => {
                    self.#inserirAnotacao(item);
                });
            } else {
                form.find('input, textarea, select, button').prop('disabled', true);
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
    }

    async #getRecurse(options = {}) {
        const self = this;
        const { idRegister = self.#idRegister,
            urlApi = self.#objConfigs.url.base,
        } = options;

        try {
            const obj = new connectAjax(urlApi);
            obj.setParam(idRegister);
            return await obj.getRequest();
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async #delButtonAction(idDel, nameDel, options = {}) {
        const self = this;
        const { button = null,
            title = 'Exclusão de Registro',
            message = `Confirma a exclusão do registro < b > ${nameDel}</ >? `,
            success = `Registro excluído com sucesso!`,
        } = options;

        try {
            const obj = new modalMessage();
            obj.setDataEnvModal = {
                title: title,
                message: message,
            };
            obj.setFocusElementWhenClosingModal = button;
            const result = await obj.modalOpen();
            if (result.confirmResult) {
                if (await self._delRecurse(idDel, options)) {
                    commonFunctions.generateNotification(success, 'success');
                    return true;
                }
            }
            return false;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async _delRecurse(idDel, options = {}) {
        const self = this;
        const {
            urlApi = self.#objConfigs.url.base,
        } = options;

        try {
            const obj = new connectAjax(urlApi);
            obj.setParam(idDel);
            obj.setAction(enumAction.DELETE)
            await obj.deleteRequest();
            return true;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }
}

$(function () {
    new PageServicoForm();
});