import { CommonFunctions } from "../../../commons/CommonFunctions";
import { EnumAction } from "../../../commons/EnumAction";
import { TemplateForm } from "../../../commons/templates/TemplateForm";
import { ModalAreaJuridicaTenant } from "../../../components/tenant/ModalAreaJuridicaTenant";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";
import { ParticipacaoModule } from "../../../modules/ParticipacaoModule";

class PagePreenchimentoAutomatico extends TemplateForm {

    _objConfigs = {
        url: {
            base: window.apiRoutes.baseServico,
        },
        sufixo: 'PagePreenchimentoAutomatico',
        data: {
            porcentagemOcupada: 0,
            participantesNaTela: [],
        },
        participacao: {
            // perfis_busca: window.Statics.PerfisPermitidoParticipacaoRessarcimento,
            participacao_tipo_tenant: {
                configuracao_tipo: window.Enums.ParticipacaoTipoTenantConfiguracaoTipoEnum.LANCAMENTO_SERVICO,
            },
        },
    };
    #functionsParticipacao;

    constructor() {
        const objData = {
            objConfigs: this._objConfigs,
            extraConfigs: {
                modeParent: 'searchAndUse',
            }
        }
        this.#functionsParticipacao = new ParticipacaoModule(this, objData);
        this.initEvents();
    }

    async initEvents() {
        const self = this;
        await this.#buscarAreasJuridicas();

        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self._idRegister = uuid;
            const url = `${self._objConfigs.url.base}/${self._idRegister}`;
            self._objConfigs.url.baseAnotacao = `${url}/anotacao`;
            self._objConfigs.url.basePagamentos = `${url}/pagamentos`;
            self._objConfigs.url.baseParticipacao = `${url}/participacao`;
            self._objConfigs.url.baseValores = `${url}/relatorio/valores`;
            this._action = EnumAction.PUT;
            await self._buscarDados();
        } else {
            this._action = EnumAction.POST;
        }

        self.#addEventosBotoes();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#btnOpenAreaJuridicaTenant${self.getSufixo}`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalAreaJuridicaTenant();
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
                    if (response.selected) {
                        self.#buscarAreasJuridicas(response.selected.id);
                    } else {
                        self.#buscarAreasJuridicas();
                    }
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });

        // $(`#btnExcluirParticipante${self.getSufixo}`).on('click', async function () {
        //     const response = await self._delButtonAction(`${self._idRegister}/participacao`, null, {
        //         title: `Exclusão de Participantes`,
        //         message: `Confirma a exclusão do(s) participante(s) deste serviço?`,
        //         success: `Participantes excluídos com sucesso!`,
        //         button: this,
        //         urlApi: `${self._objConfigs.url.base}`,
        //     });

        //     if (response) {
        //         self.#functionsParticipacao._buscarParticipantes();
        //     }
        // });

        self.#functionsParticipacao._buscarPresetParticipacaoTenant();
    }

    async preenchimentoDados(response) {
        const self = this;
        const responseData = response.data;

        self.#addEventosPreenchimentoDados(responseData);
    }

    async #addEventosPreenchimentoDados(item) {
        const self = this;

        // $(`#${item.idCard}`).find('.btn-edit').on('click', async function () {
        //     const btn = $(this);
        //     CommonFunctions.simulateLoading(btn);
        //     try {
        //         const objModal = new ModalServicoPagamento({ urlApi: self._objConfigs.url.basePagamentos });
        //         objModal.setDataEnvModal = {
        //             idRegister: item.id,
        //         }
        //         const response = await objModal.modalOpen();
        //         if (response.refresh && response.register) {
        //             self.#buscarPagamentos();
        //         }
        //     } catch (error) {
        //         CommonFunctions.generateNotificationErrorCatch(error);
        //     } finally {
        //         CommonFunctions.simulateLoading(btn, false);
        //     }
        // });

    }

    async #buscarAreasJuridicas(selected_id = null) {
        try {
            const self = this;
            let options = { outInstanceParentBln: true };
            selected_id ? options.selectedIdOption = selected_id : null;
            const selArea = $(`#area_juridica_id${self.getSufixo}`);
            await CommonFunctions.fillSelect(selArea, self._objConfigs.url.baseAreaJuridicaTenant, options); 0
            return true
        } catch (error) {
            return false;
        }
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(`#form${self.getSufixo}`);
        let data = CommonFunctions.getInputsValues(formRegistration[0]);

        if (self.saveVerifications(data, formRegistration)) {
            self._save(data, self._objConfigs.url.base);
        }
        return false;
    }

    saveVerifications(data, formRegistration) {
        const self = this;
        if (self._action == EnumAction.POST) {
            let blnSave = CommonFunctions.verificationData(data.titulo, { field: formRegistration.find('input[name="titulo"]'), messageInvalid: 'O título deve ser informado.', setFocus: true });
            blnSave = CommonFunctions.verificationData(data.area_juridica_id, { field: formRegistration.find('select[name="area_juridica_id"]'), messageInvalid: 'A Área Jurídica deve ser selecionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            blnSave = CommonFunctions.verificationData(data.descricao, { field: formRegistration.find('textarea[name="descricao"]'), messageInvalid: 'A descrição deve ser preenchida.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            return blnSave;
        }
        return true;
    }

}

$(function () {
    new PagePreenchimentoAutomatico();
});