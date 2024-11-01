import { commonFunctions } from "../../../commons/commonFunctions";
import { connectAjax } from "../../../commons/connectAjax";
import { enumAction } from "../../../commons/enumAction";
import { RedirectHelper } from "../../../helpers/RedirectHelper";
import { RequestsHelpers } from "../../../helpers/RequestsHelpers";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";
import { ServicoParticipacaoModule } from "../../../modules/ServicoParticipacaoModule";

class PageServicoParticipacaoPresetForm {

    _objConfigs = {
        url: {
            base: window.apiRoutes.baseParticipacaoPreset,
            baseParticipacaoTipo: window.apiRoutes.baseServicoParticipacaoTipoTenant,
        },
        sufixo: 'PageServicoParticipacaoPresetForm',
        data: {
            porcentagemOcupada: 0,
            participantesNaTela: []
        },
    };
    #action;
    #idRegister;
    #functionsServicoParticipacao;

    constructor() {
        this.initEvents();
        const objData = {
            objConfigs: this._objConfigs,
        }
        this.#functionsServicoParticipacao = new ServicoParticipacaoModule(this, objData);
    }

    initEvents() {
        const self = this;

        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self.#idRegister = uuid;
            this.#action = enumAction.PUT;
            self.#buscarDados();
        } else {
            this.#action = enumAction.POST;
            $(`#nome${self._objConfigs.sufixo}`).trigger('focus');
        }

        self.#addEventosBotoes();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#btnSave${self._objConfigs.sufixo}`).on('click', async function (e) {
            e.preventDefault();
            self.#saveButtonAction();
        });

        // const openModalTest = async () => {
        //     const perfis_busca = window.Statics.PerfisPermitidoParticipacaoServico.map(item => item.id);
        //     const objCode = new modalSelecionarPerfil();
        //     objCode.setDataEnvModal = {
        //         perfis_permitidos: perfis_busca,
        //         perfis_opcoes: [
        //             {
        //                 "id": "9d5426a5-bd48-4f8d-b278-f05b27f50d3c",
        //                 "tenant_id": "jsadvogados",
        //                 "pessoa_id": "9d5426a5-bbaa-4a88-8b99-fb021edf342c",
        //                 "perfil_tipo_id": 2,
        //                 "observacao": null,
        //                 "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
        //                 "created_ip": "127.0.0.1",
        //                 "created_at": "2024-10-25 06:11:52",
        //                 "updated_user_id": null,
        //                 "updated_ip": null,
        //                 "updated_at": null,
        //                 "deleted_user_id": null,
        //                 "deleted_ip": null,
        //                 "deleted_at": null,
        //                 "perfil_tipo": {
        //                     "id": 2,
        //                     "nome": "Parceiro",
        //                     "descricao": "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
        //                     "tabela_ref": null,
        //                     "tabela_model": null,
        //                     "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
        //                     "created_ip": "127.0.0.1",
        //                     "created_at": "2024-10-24 20:17:22",
        //                     "updated_user_id": null,
        //                     "updated_ip": null,
        //                     "updated_at": null,
        //                     "deleted_user_id": null,
        //                     "deleted_ip": null,
        //                     "deleted_at": null
        //                 }
        //             },
        //             {
        //                 "id": "9d5426a5-bee9-465a-a68e-ba227c420984",
        //                 "tenant_id": "jsadvogados",
        //                 "pessoa_id": "9d5426a5-bbaa-4a88-8b99-fb021edf342c",
        //                 "perfil_tipo_id": 3,
        //                 "observacao": null,
        //                 "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
        //                 "created_ip": "127.0.0.1",
        //                 "created_at": "2024-10-25 06:11:52",
        //                 "updated_user_id": null,
        //                 "updated_ip": null,
        //                 "updated_at": null,
        //                 "deleted_user_id": null,
        //                 "deleted_ip": null,
        //                 "deleted_at": null,
        //                 "perfil_tipo": {
        //                     "id": 3,
        //                     "nome": "Cliente",
        //                     "descricao": "Perfil para clientes.",
        //                     "tabela_ref": null,
        //                     "tabela_model": null,
        //                     "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
        //                     "created_ip": "127.0.0.1",
        //                     "created_at": "2024-10-24 20:17:22",
        //                     "updated_user_id": null,
        //                     "updated_ip": null,
        //                     "updated_at": null,
        //                     "deleted_user_id": null,
        //                     "deleted_ip": null,
        //                     "deleted_at": null
        //                 }
        //             }
        //         ],
        //     };
        //     const retorno = await objCode.modalOpen();
        // }

        // openModalTest();
    }

    #saveButtonAction() {
        const self = this;
        const formRegistration = $(`#form${self._objConfigs.sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        data.participantes = self._objConfigs.data.participantesNaTela;

        if (self.#saveVerifications(data, formRegistration)) {
            self.#save(data, self._objConfigs.url.base);
        }
        return false;
    }

    #saveVerifications(data, formRegistration) {
        const self = this;

        let blnSave = commonFunctions.verificationData(data.nome, { field: formRegistration.find('input[name="nome"]'), messageInvalid: 'O <b>nome</b> do preset deve ser informado.', setFocus: true });

        let porcentagemOcupada = self._objConfigs.data.porcentagem_ocupada;
        if (porcentagemOcupada > 0 && porcentagemOcupada < 100 || porcentagemOcupada > 100) {
            commonFunctions.generateNotification(`As somas das porcentagens deve ser igual a 100%. Porcentagem informada ${commonFunctions.formatWithCurrencyCommasOrFraction(porcentagemOcupada)}%.`, 'warning');
            blnSave = false;
        }
        if (!data.participantes || data.participantes.length == 0) {
            commonFunctions.generateNotification('E necessário informar pelo menos um participante.', 'warning');
            blnSave = false;
        } else {
            for (const participante of data.participantes) {
                if (participante.participacao_registro_tipo_id == window.Enums.ParticipacaoRegistroTipoEnum.GRUPO && (!participante.integrantes || participante.integrantes.length == 0)) {
                    commonFunctions.generateNotification('E necessário informar pelo menos um integrante no grupo.', 'warning');
                    blnSave = false;
                    break;
                }
            }
        }

        return blnSave;
    }

    async #save(data, urlApi, options = {}) {
        const self = this;
        const {
            btnSave = $(`#btnSave${self._objConfigs.sufixo}`),
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
                RedirectHelper.redirectWithUUIDMessage(window.frontRoutes.frontRedirect, 'Dados enviados com sucesso!', 'success');
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
            const form = $(`#form${self._objConfigs.sufixo}`);
            if (response?.data) {
                const responseData = response.data;
                form.find('input[name="nome"]').val(responseData.nome).trigger('focus');
                form.find('input[name="descricao"]').val(responseData.descricao);

                await Promise.all(
                    responseData.participantes.map(async (participante) => {
                        const integrantes = participante.integrantes ?? [];
                        delete participante.integrantes;
                        const item = await self.#functionsServicoParticipacao._inserirParticipanteNaTela(participante);
                        await Promise.all(
                            integrantes.map(async (integrante) => {
                                await self.#functionsServicoParticipacao._inserirIntegrante(item, integrante);
                            })
                        );
                    })
                );

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
            urlApi = self._objConfigs.url.base,
        } = options;
        try {
            return RequestsHelpers.getRecurse({ urlApi: urlApi, idRegister: idRegister });
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }
}

$(function () {
    new PageServicoParticipacaoPresetForm();
});