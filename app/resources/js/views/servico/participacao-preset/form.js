import { CommonFunctions } from "../../../commons/CommonFunctions";
import { EnumAction } from "../../../commons/EnumAction";
import { TemplateForm } from "../../../commons/templates/TemplateForm";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";
import { ParticipacaoModule } from "../../../modules/ParticipacaoModule";

class PageParticipacaoPresetForm extends TemplateForm {

    #objConfigs = {
        url: {
            base: window.apiRoutes.baseParticipacaoPreset,
            baseParticipacaoTipo: window.apiRoutes.baseParticipacaoTipoTenant,
        },
        sufixo: 'PageParticipacaoPresetForm',
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
        domainCustom: {
            applyBln: true,
        }
    };

    #functionsParticipacao;

    constructor() {
        const sufixo = "PageParticipacaoPresetForm";
        super({ sufixo });

        CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        CommonFunctions.deepMergeObject(this._objConfigs, { sufixo });

        const objData = {
            objConfigs: this._objConfigs,
        }
        this.#functionsParticipacao = new ParticipacaoModule(this, objData);
        this.initEvents();
    }

    async initEvents() {
        const self = this;
        let buscaDadosBln = true;

        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self._idRegister = uuid;
            self._action = EnumAction.PUT;
            buscaDadosBln = await self._buscarDados();
        } else {
            self._action = EnumAction.POST;
            $(`#nome${self._objConfigs.sufixo}`).trigger('focus');
        }

        if (buscaDadosBln) {
            self._queueCheckDomainCustom.setReady();
        }
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(`#form${self._objConfigs.sufixo}`);
        let data = CommonFunctions.getInputsValues(formRegistration[0]);
        data.participantes = self._objConfigs.data.participantesNaTela;

        if (self.#saveVerifications(data, formRegistration)) {
            self._save(data, self._objConfigs.url.base);
        }
        return false;
    }

    async preenchimentoDados(response, options) {
        const self = this;
        const form = $(options.form);

        const responseData = response.data;
        form.find('input[name="nome"]').val(responseData.nome).trigger('focus');
        form.find('input[name="descricao"]').val(responseData.descricao);

        await Promise.all(
            responseData.participantes.map(async (participante) => {
                const integrantes = participante.integrantes ?? [];
                delete participante.integrantes;
                const item = await self.#functionsParticipacao._inserirParticipanteNaTela(participante);
                await Promise.all(
                    integrantes.map(async (integrante) => {
                        await self.#functionsParticipacao._inserirIntegrante(item, integrante);
                    })
                );
            })
        );

    }

    #saveVerifications(data, formRegistration) {
        const self = this;

        let blnSave = CommonFunctions.verificationData(data.nome, { field: formRegistration.find('input[name="nome"]'), messageInvalid: 'O <b>nome</b> do preset deve ser informado.', setFocus: true });

        let porcentagemOcupada = self._objConfigs.data.porcentagem_ocupada;
        if (porcentagemOcupada > 0 && porcentagemOcupada < 100 || porcentagemOcupada > 100) {
            CommonFunctions.generateNotification(`As somas das porcentagens deve ser igual a 100%. Porcentagem informada ${CommonFunctions.formatWithCurrencyCommasOrFraction(porcentagemOcupada)}%.`, 'warning');
            blnSave = false;
        }
        if (!data.participantes || data.participantes.length == 0) {
            CommonFunctions.generateNotification('E necessário informar pelo menos um participante.', 'warning');
            blnSave = false;
        } else {
            for (const participante of data.participantes) {
                if (participante.participacao_registro_tipo_id == window.Enums.ParticipacaoRegistroTipoEnum.GRUPO && (!participante.integrantes || participante.integrantes.length == 0)) {
                    CommonFunctions.generateNotification('E necessário informar pelo menos um integrante no grupo.', 'warning');
                    blnSave = false;
                    break;
                }
            }
        }

        return blnSave;
    }
}

$(function () {
    new PageParticipacaoPresetForm();
});