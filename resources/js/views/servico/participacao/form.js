import { commonFunctions } from "../../../commons/commonFunctions";
import { connectAjax } from "../../../commons/connectAjax";
import { enumAction } from "../../../commons/enumAction";
import { modalMessage } from "../../../components/comum/modalMessage";
import { modalNome } from "../../../components/comum/modalNome";
import { modalPessoa } from "../../../components/pessoas/modalPessoa";
import { modalServicoPagamento } from "../../../components/servico/modalServicoPagamento";
import { modalServicoParticipacao } from "../../../components/servico/modalServicoParticipacao";
import { RedirectHelper } from "../../../helpers/RedirectHelper";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";

class PageServicoParticipacaoPresetForm {

    #sufixo = 'PageServicoParticipacaoPresetForm';
    #objConfigs = {
        url: {
            base: window.apiRoutes.baseParticipacaoPreset,
            baseParticipacaoTipo: window.apiRoutes.baseServicoParticipacaoTipoTenant,
        },
        data: {
            porcentagemOcupada: 0,
        }
    };
    #action;
    #idRegister;

    constructor() {
        this.initEvents();
    }

    initEvents() {
        const self = this;

        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self.#idRegister = uuid;
            self.#objConfigs.url.baseAnotacao = `${self.#objConfigs.url.base}/${self.#idRegister}/anotacao`;
            this.#action = enumAction.PUT;
            // self.#buscarDados();
        } else {
            this.#action = enumAction.POST;
        }

        self.#addEventosBotoes();
    }

    #addEventosBotoes() {
        const self = this;

        const openModalServicoParticipacao = async (dados_participacao) => {
            const objModal = new modalServicoParticipacao();
            objModal.setDataEnvModal = {
                dados_participacao: dados_participacao,
                porcentagem_ocupada: self.#objConfigs.data.porcentagem_ocupada,
            }
            const response = await objModal.modalOpen();
            if (response.refresh) {
                self.#atualizaRegistroParticipanteNaTela(response.register);
                self.#atualizaPorcentagemLivre();
            }
        }

        $(`#btnInserirPessoa${self.#sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalPessoa();

                const response = await objModal.modalOpen();
                if (response.refresh) {
                    await openModalServicoParticipacao({ participacao_registro_tipo_id: 1, pessoa_perfil: response.selected });
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnInserirGrupo${self.#sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModalNome = new modalNome();
                objModalNome.setDataEnvModal = {
                    title: 'Novo grupo',
                    mensagem: 'Informe o nome do grupo',
                }
                const response = await objModalNome.modalOpen();
                if (response.refresh) {
                    await openModalServicoParticipacao({ participacao_registro_tipo_id: 2, nome_grupo: response.name });
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

        const openModalTest = async () => {
            const objCode = new modalServicoParticipacao();
            const retorno = await objCode.modalOpen();
            console.log(retorno);
        }
        // openModalTest();
        // openModalServicoParticipacao({ participacao_registro_tipo_id: 2, valor: 0, tipo_valor: 'porcentagem', nome: 'Rachadinha' });

    }

    async #buscarParticipacaoTipo(id) {
        const self = this;
        return self.#getRecurse({ idRegister: id, urlApi: self.#objConfigs.url.baseParticipacaoTipo });

    }

    async #atualizaRegistroParticipanteNaTela(item) {
        const self = this;
        const divParticipantes = $(`#divParticipantes${self.#sufixo}`);

        let nome = '';
        switch (item.participacao_registro_tipo_id) {
            case 1:
                nome = item.pessoa_perfil.pessoa.pessoa_dados.nome;
                break;
            case 2:
                nome = item.nome_grupo;
                break;
            default:
                commonFunctions.generateNotification('Tipo de registro de participação não informado.', 'error');
                console.error('Tipo de registro de participação não informado.', item);
                return false;
        }

        let participacao_tipo = item?.participacao_tipo?.nome ?? null;
        if (!participacao_tipo && item.participacao_tipo_id) {
            participacao_tipo = self.#buscarParticipacaoTipo(item.participacao_tipo_id) ?? { nome: 'Erro de busca' };
        } else {
            commonFunctions.generateNotification('Tipo de participação não informado.', 'error');
            console.error('Tipo de participação não informado.', item);
            return false;
        }

        let valor_tipo = ''
        let valor = commonFunctions.formatNumberWithLimitDecimalPlaces(item.valor);
        switch (item.valor_tipo) {
            case 'porcentagem':
                valor_tipo = 'Porcentagem';
                valor += '%';
                break;
            case 'valor_fixo':
                valor_tipo = 'Valor Fixo';
                valor = `R$ ${valor}`;
                break;
            default:
                valor_tipo = 'Erro valor tipo';
                console.error('Erro no tipo de valor', item);
                break;
        }

        const strCard = `
            <div class="card-body">
                <h5 class="card-title d-flex align-items-center justify-content-between">
                    <span>${nome}</span>
                    <div>
                        <div class="d-grid gap-2 d-flex justify-content-end">
                            <button type="button" class="btn btn-outline-primary btn-sm btn-edit border-0"
                                style="max-width: 7rem">Editar</button>
                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete border-0"
                                style="max-width: 7rem">Excluir</button>
                        </div>
                    </div>
                </h5>
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3">
                    <div class="col">
                        <div class="form-text">Participação</div>
                        <label class="form-label">${participacao_tipo.nome}</label>
                    </div>
                    <div class="col">
                        <div class="form-text">Método</div>
                        <label class="form-label">${valor_tipo}</label>
                    </div>
                    <div class="col">
                        <div class="form-text">Valor</div>
                        <label class="form-label">${valor}</label>
                    </div>
                </div>
            </div>`;

        if (item.idCard) {
            $(`#${item.idCard}`).html(strCard);
        } else {
            item.idCard = UUIDHelper.generateUUID();
            divParticipantes.append(`<div id="${item.idCard}" class="card">${strCard}</div>`);
        }
    }

    async #atualizaPorcentagemLivre() {

    }

    #htmlBtnEdit(options = {}) {
        const {
            title = 'Editar registro',
        } = options;
        return `<button type="button" class="btn btn-outline-primary btn-sm btn-edit border-0" style="max-width: 7rem" title="${title}"><i class="bi bi-pencil"></i> Editar</button>`;
    }

    #htmlBtnDelete(options = {}) {
        const {
            title = 'Editar registro',
        } = options;
        return `<button type="button" class="btn btn-outline-danger btn-sm btn-delete border-0" style="max-width: 7rem" title="${title}"><i class="bi bi-trash"></i> Excluir</button>`
    }

    async #addEventosPagamento(item) {
        const self = this;

        $(`#${item.idCard}`).find('.btn-edit').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalServicoPagamento(self.#objConfigs.url.basePagamentos);
                objModal.setDataEnvModal = {
                    idRegister: item.id,
                }
                const response = await objModal.modalOpen();
                console.log(response);
                if (response.refresh && response.register) {
                    // AtualizarRegistro
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        // $(`#${item.idCol}`).find(`.btn-delete`).click(async function () {
        //     const response = await self.#delButtonAction(item.id, item.titulo, {
        //         title: `Exclusão de Anotação`,
        //         message: `Confirma a exclusão da anotação <b>${item.titulo}</b>?`,
        //         success: `Anotação excluída com sucesso!`,
        //         button: this,
        //         urlApi: self.#objConfigs.url.baseAnotacao,
        //     });

        //     if (response) {
        //         $(`#${item.idCol}`).remove();
        //     }
        // });
    }

    #saveButtonAction() {
        const self = this;
        const formRegistration = $(`#formServico${self.#sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);

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
                    RedirectHelper.redirectWithUUIDMessage(`${window.frontRoutes.frontRedirectForm}/${response.data.id}`, 'Serviço iniciado com sucesso!', 'success');
                }
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
        finally {
            commonFunctions.simulateLoading(btnSave, false);
        };
    }

    // async #buscarDados() {
    //     const self = this;

    //     try {
    //         await commonFunctions.loadingModalDisplay();
    //         const response = await self.#getRecurse();
    //         const form = $(`#formServico${self.#sufixo}`);
    //         if (response?.data) {
    //             const responseData = response.data;
    //             form.find('input[name="titulo"]').val(responseData.titulo);
    //             commonFunctions.updateSelect2Value($(`#area_juridica_id${self.#sufixo}`), responseData.area_juridica.nome, responseData.area_juridica_id);
    //             form.find('textarea[name="descricao"]').val(responseData.descricao);
    //             self.#atualizarValorServico(responseData.valor_servico);
    //             self.#atualizarTotalAguardando(responseData.total_aguardando);
    //             self.#atualizarTotalLiquidado(responseData.total_liquidado);
    //             self.#atualizarTotalInadimplente(responseData.total_inadimplente);

    //             responseData.anotacao.forEach(item => {
    //                 self.#inserirAnotacao(item);
    //             });

    //             responseData.pagamento.forEach(item => {
    //                 self.#inserirPagamento(item);
    //             });
    //         } else {
    //             form.find('input, textarea, select, button').prop('disabled', true);
    //         }
    //     } catch (error) {
    //         commonFunctions.generateNotificationErrorCatch(error);
    //     } finally {
    //         await commonFunctions.loadingModalDisplay(false);
    //     }
    // }

    // #atualizarValorServico(valor) {
    //     const self = this;
    //     $(`#valorServico${self.#sufixo}`).html(commonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    // }

    // #atualizarTotalAguardando(valor) {
    //     const self = this;
    //     $(`#totalAguardando${self.#sufixo}`).html(commonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    // }

    // #atualizarTotalLiquidado(valor) {
    //     const self = this;
    //     $(`#totalLiquidado${self.#sufixo}`).html(commonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    // }

    // #atualizarTotalInadimplente(valor) {
    //     const self = this;
    //     $(`#totalInadimplente${self.#sufixo}`).html(commonFunctions.formatWithCurrencyCommasOrFraction(valor ?? 0));
    // }

    // async #buscarAreasJuridicas(selected_id = null) {
    //     const self = this;
    //     let options = selected_id ? { selectedIdOption: selected_id } : {};
    //     const selArea = $(`#area_juridica_id${self.#sufixo}`);
    //     await commonFunctions.fillSelect(selArea, self.#objConfigs.url.baseAreaJuridicaTenant, options);
    // }

    // async #buscarPagamentos() {
    //     const self = this;
    //     try {
    //         const obj = new connectAjax(self.#objConfigs.url.basePagamentos);
    //         const response = await obj.getRequest();
    //         $(`#divPagamento${self.#sufixo}`).html('');
    //         for (const item of response.data) {
    //             console.log(item);
    //             self.#inserirPagamento(item);
    //         }
    //     } catch (error) {
    //         commonFunctions.generateNotificationErrorCatch(error);
    //     }
    // }

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
                if (await self.#delRecurse(idDel, options)) {
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

    async #delRecurse(idDel, options = {}) {
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
    new PageServicoParticipacaoPresetForm();
});