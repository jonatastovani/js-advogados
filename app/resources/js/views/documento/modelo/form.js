import { commonFunctions } from "../../../commons/commonFunctions";
import { enumAction } from "../../../commons/enumAction";
import { TemplateForm } from "../../../commons/templates/TemplateForm";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";
import { DocumentoModeloQuillEditorModule } from "../../../modules/DocumentoModeloQuillEditorModule";
import { ParticipacaoModule } from "../../../modules/ParticipacaoModule";
import { QueueManager } from "../../../utils/QueueManager";

class PageDocumentoModeloForm extends TemplateForm {

    #functionsParticipacao;
    #quillQueueManager;

    constructor() {

        const objConfigs = {
            url: {
                base: window.apiRoutes.baseServico,
                baseAnotacao: undefined,
                basePagamentos: undefined,
                baseParticipacao: undefined,
                baseValores: undefined,
                baseCliente: undefined,
                baseAreaJuridicaTenant: window.apiRoutes.baseAreaJuridicaTenant,
                baseParticipacaoPreset: window.apiRoutes.baseParticipacaoPreset,
                baseParticipacaoTipo: window.apiRoutes.baseParticipacaoTipoTenant,
            },
            sufixo: 'PageDocumentoModeloForm',
            data: {
                porcentagemOcupada: 0,
                participantesNaTela: [],
                clientesNaTela: [],
                participacao_tipo_tenant: {
                },
            },
            participacao: {
                // perfis_busca: window.Statics.PerfisPermitidoParticipacaoRessarcimento,
                participacao_tipo_tenant: {
                    configuracao_tipo: window.Enums.ParticipacaoTipoTenantConfiguracaoTipoEnum.LANCAMENTO_SERVICO,
                },
            },
        };

        super({
            objConfigs: objConfigs
        });

        const objData = {
            objConfigs: this._objConfigs,
            extraConfigs: {
                modeParent: 'searchAndUse',
            }
        }
        this.#functionsParticipacao = new ParticipacaoModule(this, objData);
        this.#quillQueueManager = new QueueManager();  // Cria a fila

        this.initEvents();
    }

    async initEvents() {
        const self = this;
        // await this.#buscarAreasJuridicas();

        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self._idRegister = uuid;
            const url = `${self._objConfigs.url.base}/${self._idRegister}`;
            self._objConfigs.url.baseAnotacao = `${url}/anotacao`;
            self._objConfigs.url.basePagamentos = `${url}/pagamentos`;
            self._objConfigs.url.baseParticipacao = `${url}/participacao`;
            self._objConfigs.url.baseValores = `${url}/relatorio/valores`;
            self._objConfigs.url.baseCliente = `${url}/cliente`;
            this._action = enumAction.PUT;
            await self._buscarDados();
        } else {
            this._action = enumAction.POST;
        }

        self.#addEventosBotoes();
    }

    #addEventosBotoes() {
        const self = this;

        self._classQuillEditor = new DocumentoModeloQuillEditorModule(self, {
            quillEditor: {
                selector: `#descricao${self.getSufixo}`,
                options: { exclude: ['image', 'scriptSub', 'scriptSuper'] }
            },
            objConfigs: self._objConfigs
        });
        self.#quillQueueManager.setReady();  // Informa que o quill está pronto

        self._classQuillEditor.addEventClientes();

        self.#inserirTexto();
    }

    #inserirTexto() {
        const self = this;

        const data = {
            "descricao": {
                "ops": [
                    {
                        "attributes": {
                            "underline": true,
                            "bold": true
                        },
                        "insert": "PROCURAÇÃO "
                    },
                    {
                        "attributes": {
                            "underline": true,
                            "italic": true,
                            "bold": true
                        },
                        "insert": "AD JUDICIA ET EXTRA"
                    },
                    {
                        "attributes": {
                            "align": "center"
                        },
                        "insert": "\n"
                    },
                    {
                        "insert": "Art. 105, do CPC"
                    },
                    {
                        "attributes": {
                            "align": "center"
                        },
                        "insert": "\n\n"
                    },
                    {
                        "insert": "OUTORGANTE: "
                    },
                    {
                        "attributes": {
                            "bold": true
                        },
                        "insert": "{{clientePF.1.nome}}"
                    },
                    {
                        "insert": ", {{clientePF.1.nacionalidade}}, {{clientePF.1.estado_civil}}, {{clientePF.1.profissao}}, RG/SP nº {{clientePF.1.rg}} e CPF nº {{clientePF.1.cpf}}, com endereço a {{clientePF.1.logradouro}}, nº {{clientePF.1.numero}}, {{clientePF.1.bairro}}, CEP {{clientePF.1.cep}}, {{clientePF.1.cidade}}-{{clientePF.1.estado}}."
                    },
                    {
                        "attributes": {
                            "align": "justify"
                        },
                        "insert": "\n"
                    },
                    {
                        "attributes": {
                            "align": "center"
                        },
                        "insert": "\n"
                    }
                ]
            },
            "clientes": [
                {
                    "cliente_contador": 1,
                    "pessoa_tipo": "PF",
                    "marcadores": {
                        "pessoa": [
                            {
                                "display": "Nome",
                                "marcacao": "{{clientePF.1.nome}}",
                                "sufixo": "nome"
                            },
                            {
                                "display": "Nacionalidade",
                                "marcacao": "{{clientePF.1.nacionalidade}}",
                                "sufixo": "nacionalidade"
                            },
                            {
                                "display": "Estado Civil",
                                "marcacao": "{{clientePF.1.estado_civil}}",
                                "sufixo": "estado_civil"
                            },
                            {
                                "display": "Profissão",
                                "marcacao": "{{clientePF.1.profissao}}",
                                "sufixo": "profissao"
                            },
                            {
                                "display": "RG",
                                "marcacao": "{{clientePF.1.rg}}",
                                "sufixo": "rg"
                            },
                            {
                                "display": "CPF",
                                "marcacao": "{{clientePF.1.cpf}}",
                                "sufixo": "cpf"
                            },
                            {
                                "display": "Logradouro",
                                "marcacao": "{{clientePF.1.logradouro}}",
                                "sufixo": "logradouro"
                            },
                            {
                                "display": "Número",
                                "marcacao": "{{clientePF.1.numero}}",
                                "sufixo": "numero"
                            },
                            {
                                "display": "Complemento",
                                "marcacao": "{{clientePF.1.complemento}}",
                                "sufixo": "complemento"
                            },
                            {
                                "display": "Bairro",
                                "marcacao": "{{clientePF.1.bairro}}",
                                "sufixo": "bairro"
                            },
                            {
                                "display": "Referência",
                                "marcacao": "{{clientePF.1.referencia}}",
                                "sufixo": "referencia"
                            },
                            {
                                "display": "Cidade",
                                "marcacao": "{{clientePF.1.cidade}}",
                                "sufixo": "cidade"
                            },
                            {
                                "display": "Estado",
                                "marcacao": "{{clientePF.1.estado}}",
                                "sufixo": "estado"
                            },
                            {
                                "display": "CEP",
                                "marcacao": "{{clientePF.1.cep}}",
                                "sufixo": "cep"
                            },
                            {
                                "display": "País",
                                "marcacao": "{{clientePF.1.pais}}",
                                "sufixo": "pais"
                            }
                        ]
                    },
                    "idAccordion": "2e980fff-29d0-4089-86c7-1f111fcbbf91"
                }
            ]
        };

        data.clientes.map(item =>
            self._classQuillEditor._inserirClienteNaTela(item)
        )
        self._classQuillEditor.getQuill.setContents(data.descricao);
    }

    // async preenchimentoDados(response, options) {
    //     const self = this;
    //     const form = $(options.form);

    //     const responseData = response.data;
    //     form.find('input[name="titulo"]').val(responseData.titulo);
    //     self.#buscarAreasJuridicas(responseData.area_juridica_id);
    //     self.#quillQueueManager.enqueue(() => {
    //         self._classQuillEditor.getQuill.setContents(responseData.descricao);
    //     })

    //     $(`#divAnotacao${self._objConfigs.sufixo}`).html('');
    //     responseData.anotacao.forEach(item => {
    //         self.#inserirAnotacao(item);
    //     });

    //     $(`#divPagamento${self._objConfigs.sufixo}`).html('');
    //     responseData.pagamento.forEach(item => {
    //         self.#inserirPagamento(item);
    //     });

    //     self.#limparClientes();
    //     responseData.cliente.forEach(item => {
    //         self.#inserirCliente(item);
    //     });

    //     self.#functionsParticipacao._inserirParticipantesEIntegrantes(responseData.participantes);

    //     self.#atualizaTodosValores(response.data);
    // }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(`#form${self._objConfigs.sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        const descricaoDelta = self._classQuillEditor.getQuill.getContents();
        data.descricao = descricaoDelta;
        data.clientes = self._classQuillEditor._getClientesNaTela();

        console.log(data);

        // if (self.#saveVerifications(data, formRegistration)) {
        //     self._save(data, self._objConfigs.url.base, {
        //         success: 'Serviço cadastrado com sucesso!',
        //         redirectWithIdBln: true,
        //     });
        // }
        return false;
    }

    #saveVerifications(data, formRegistration) {
        const self = this;
        if (self._action == enumAction.POST) {
            let blnSave = commonFunctions.verificationData(data.titulo, { field: formRegistration.find('input[name="titulo"]'), messageInvalid: 'O título deve ser informado.', setFocus: true });
            blnSave = commonFunctions.verificationData(data.area_juridica_id, { field: formRegistration.find('select[name="area_juridica_id"]'), messageInvalid: 'A Área Jurídica deve ser selecionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            // blnSave = commonFunctions.verificationData(data.descricao, { field: formRegistration.find('textarea[name="descricao"]'), messageInvalid: 'A descrição deve ser preenchida.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            return blnSave;
        }
        return true;
    }


}

$(function () {
    new PageDocumentoModeloForm();
});