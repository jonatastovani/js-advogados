import { commonFunctions } from "../../../commons/commonFunctions";
import { enumAction } from "../../../commons/enumAction";
import { TemplateForm } from "../../../commons/templates/TemplateForm";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";
import { DocumentoModeloQuillEditorModule } from "../../../modules/DocumentoModeloQuillEditorModule";
import { QueueManager } from "../../../utils/QueueManager";

class PageDocumentoModeloForm extends TemplateForm {

    #quillQueueManager;

    constructor() {

        const objConfigs = {
            url: {
                base: window.apiRoutes.baseDocumentoModeloTenant,
                baseDocumentoModeloTipo: window.apiRoutes.baseDocumentoModeloTipo,
            },
            sufixo: 'PageDocumentoModeloForm',
            data: {},
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
        this.#quillQueueManager = new QueueManager();  // Cria a fila

        this.initEvents();
    }

    async initEvents() {
        const self = this;
        await this.#buscarDocumentoModeloTipo();

        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self._idRegister = uuid;
            // const url = `${self._objConfigs.url.base}/${self._idRegister}`;
            // self._objConfigs.url.baseAnotacao = `${url}/anotacao`;
            // self._objConfigs.url.basePagamentos = `${url}/pagamentos`;
            // self._objConfigs.url.baseParticipacao = `${url}/participacao`;
            // self._objConfigs.url.baseValores = `${url}/relatorio/valores`;
            // self._objConfigs.url.baseCliente = `${url}/cliente`;
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
                selector: `#conteudo${self.getSufixo}`,
                options: { exclude: ['image', 'scriptSub', 'scriptSuper', 'code', 'link'] }
            },
            objConfigs: self._objConfigs
        });
        self.#quillQueueManager.setReady();  // Informa que o quill está pronto

        self._classQuillEditor.addEventClientes();

        self.#inserirTexto();


        // Captura qualquer alteração no texto do editor
        self._classQuillEditor.getQuill.on('text-change', function (delta, oldDelta, source) {
            // console.log("Texto alterado!", delta);

            const resultado = self._classQuillEditor._verificarInconsistencias();
            console.log(resultado);

            // // Se foi uma entrada manual do usuário (não via API)
            // if (source === 'user') {
            //     console.log("Usuário digitou algo!");

            //     const resultado = self._classQuillEditor._verificarInconsistencias();

            //     console.log(resultado);
            // }
        });

        // Captura mudanças na seleção do cursor
        self._classQuillEditor.getQuill.on('selection-change', function (range, oldRange, source) {

            const resultado = self._classQuillEditor._verificarInconsistencias();
            // if (range) {
            //     console.log("Cursor movido", range);
            // } else {
            //     console.log("Usuário perdeu o foco do editor.");
            // }
        });
        const resultado = self._classQuillEditor._verificarInconsistencias();
    }

    #inserirTexto() {
        const self = this;

        const data = {
            "conteudo": {
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
                        "insert": "\n\n"
                    },
                    {
                        "insert": "OUTORGADOS: "
                    },
                    {
                        "attributes": {
                            "bold": true
                        },
                        "insert": "JORGE SILVA SOCIEDADE INDIVIDUAL DE ADVOCACIA"
                    },
                    {
                        "insert": ", registrada na OAB/SP sob nº 367.41, representada pelos advogados "
                    },
                    {
                        "attributes": {
                            "bold": true
                        },
                        "insert": "JORGE DA SILVA"
                    },
                    {
                        "insert": ", OAB/SP 217.759, "
                    },
                    {
                        "attributes": {
                            "bold": true
                        },
                        "insert": "JÉTER LAILTON FERREIRA TOVANI"
                    },
                    {
                        "insert": ", OAB/SP 440.804, "
                    },
                    {
                        "attributes": {
                            "bold": true
                        },
                        "insert": "JOÃO RUBEN BOTELHO"
                    },
                    {
                        "insert": " OAB/SP 117.963, "
                    },
                    {
                        "attributes": {
                            "bold": true
                        },
                        "insert": "THIAGO MAIA GARRIDO TEBET,"
                    },
                    {
                        "insert": " OAB/SP 307.994, "
                    },
                    {
                        "attributes": {
                            "bold": true
                        },
                        "insert": "SANDRA MARIA TOALIARI"
                    },
                    {
                        "insert": ", OAB/SP 179.883, "
                    },
                    {
                        "attributes": {
                            "bold": true
                        },
                        "insert": "ISABEL CRISTINA TOALIARI"
                    },
                    {
                        "insert": ",OAB/SP 113.278, "
                    },
                    {
                        "attributes": {
                            "bold": true
                        },
                        "insert": "DANIELA PINHEIRO YABIKU"
                    },
                    {
                        "insert": ", OAB/SP 229.046, "
                    },
                    {
                        "attributes": {
                            "bold": true
                        },
                        "insert": "PAULA RIBEIRO PIRES"
                    },
                    {
                        "insert": ", OAB/SP 451.550 e "
                    },
                    {
                        "attributes": {
                            "bold": true
                        },
                        "insert": "THAIZA VALÉRIA DA SILVA"
                    },
                    {
                        "insert": ", OAB/SP 351.336, tendo como escritório profissional a "
                    },
                    {
                        "attributes": {
                            "underline": true,
                            "bold": true
                        },
                        "insert": "Unidade 1 (Matriz)"
                    },
                    {
                        "insert": ", situada na Avenida Monte Castelo, 759, Centro, CEP 13450-031, Santa Bárbara d’Oeste, Estado de São Paulo, e a "
                    },
                    {
                        "attributes": {
                            "underline": true,
                            "bold": true
                        },
                        "insert": "Unidade 2"
                    },
                    {
                        "insert": ", situada na Rua Tupinambás, 678, Jardim São Francisco, CEP 13457-027, Santa Bárbara d’Oeste, Estado de São Paulo."
                    },
                    {
                        "attributes": {
                            "align": "justify"
                        },
                        "insert": "\n\n"
                    },
                    {
                        "attributes": {
                            "color": "windowtext"
                        },
                        "insert": "PODERES: por este instrumento particular de procuração, constituo meus bastante procuradores os advogados outorgados, concedendo-lhes os poderes da cláusula “"
                    },
                    {
                        "attributes": {
                            "color": "windowtext",
                            "italic": true
                        },
                        "insert": "ad judicia” “et extra”"
                    },
                    {
                        "attributes": {
                            "color": "windowtext"
                        },
                        "insert": ", para o foro em geral, e especialmente para "
                    },
                    {
                        "attributes": {
                            "color": "windowtext",
                            "bold": true
                        },
                        "insert": "*"
                    },
                    {
                        "attributes": {
                            "color": "windowtext"
                        },
                        "insert": ","
                    },
                    {
                        "attributes": {
                            "color": "#212529",
                            "background": "#f2f2f2"
                        },
                        "insert": " "
                    },
                    {
                        "attributes": {
                            "color": "windowtext"
                        },
                        "insert": "podendo, portanto, promover quaisquer medidas judiciais ou administrativas, em qualquer instância, assinar termo, substabelecer com ou sem reserva de poderes, e praticar ainda, todos e quaisquer atos necessários e convenientes ao bom e fiel desempenho deste mandato."
                    },
                    {
                        "attributes": {
                            "align": "justify"
                        },
                        "insert": "\n"
                    },
                    {
                        "insert": "\nPODERES ESPECÍFICOS: A presente procuração outorga aos advogados acima descritos, os poderes para receber citação, confessar, reconhecer a procedência do pedido, transigir, desistir, renunciar ao direito sobre o qual se funda a ação, receber, dar quitação, firmar compromisso, pedir a justiça gratuita e assinar declaração de hipossuficiência econômica. Os poderes específicos acima outorgados poderão ser substabelecidos.\n"
                    },
                    {
                        "attributes": {
                            "align": "right"
                        },
                        "insert": "\n"
                    },
                    {
                        "insert": "Santa Bárbara d´Oeste/SP, 21 de fevereiro de 2025\n\n\n______________________________________"
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
                    "marcadores": [
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
                    ],
                    "idAccordion": "af3f9610-8e5a-4569-a427-31abd2e47955",
                    "nome": "ClientePF.1"
                },
                {
                    "cliente_contador": 1,
                    "pessoa_tipo": "PJ",
                    "marcadores": [
                        {
                            "display": "Razão Social",
                            "marcacao": "{{clientePJ.1.razao_social}}",
                            "sufixo": "razao_social"
                        },
                        {
                            "display": "Nome Fantasia",
                            "marcacao": "{{clientePJ.1.nome_fantasia}}",
                            "sufixo": "nome_fantasia"
                        },
                        {
                            "display": "Natureza Jurídica",
                            "marcacao": "{{clientePJ.1.natureza_juridica}}",
                            "sufixo": "natureza_juridica"
                        },
                        {
                            "display": "Data de Fundação",
                            "marcacao": "{{clientePJ.1.data_fundacao}}",
                            "sufixo": "data_fundacao"
                        },
                        {
                            "display": "Capital Social",
                            "marcacao": "{{clientePJ.1.capital_social}}",
                            "sufixo": "capital_social"
                        },
                        {
                            "display": "Regime Tributário",
                            "marcacao": "{{clientePJ.1.regime_tributario}}",
                            "sufixo": "regime_tributario"
                        },
                        {
                            "display": "Responsável Legal",
                            "marcacao": "{{clientePJ.1.responsavel_legal}}",
                            "sufixo": "responsavel_legal"
                        },
                        {
                            "display": "CPF Responsável",
                            "marcacao": "{{clientePJ.1.cpf_responsavel}}",
                            "sufixo": "cpf_responsavel"
                        },
                        {
                            "display": "Logradouro",
                            "marcacao": "{{clientePJ.1.logradouro}}",
                            "sufixo": "logradouro"
                        },
                        {
                            "display": "Número",
                            "marcacao": "{{clientePJ.1.numero}}",
                            "sufixo": "numero"
                        },
                        {
                            "display": "Complemento",
                            "marcacao": "{{clientePJ.1.complemento}}",
                            "sufixo": "complemento"
                        },
                        {
                            "display": "Bairro",
                            "marcacao": "{{clientePJ.1.bairro}}",
                            "sufixo": "bairro"
                        },
                        {
                            "display": "Referência",
                            "marcacao": "{{clientePJ.1.referencia}}",
                            "sufixo": "referencia"
                        },
                        {
                            "display": "Cidade",
                            "marcacao": "{{clientePJ.1.cidade}}",
                            "sufixo": "cidade"
                        },
                        {
                            "display": "Estado",
                            "marcacao": "{{clientePJ.1.estado}}",
                            "sufixo": "estado"
                        },
                        {
                            "display": "CEP",
                            "marcacao": "{{clientePJ.1.cep}}",
                            "sufixo": "cep"
                        },
                        {
                            "display": "País",
                            "marcacao": "{{clientePJ.1.pais}}",
                            "sufixo": "pais"
                        }
                    ],
                    "idAccordion": "e21c32bc-885c-414d-9048-665a3f02d054",
                    "nome": "ClientePJ.1",
                    "uuid": "c58c49a2-6cef-41b5-976c-c93d0d1acfd7"
                }
            ]
        };

        data.clientes.map(item =>
            self._classQuillEditor._inserirClienteNaTela(item)
        )

        self._classQuillEditor.getQuill.setContents(data.conteudo);
    }

    // async preenchimentoDados(response, options) {
    //     const self = this;
    //     const form = $(options.form);

    //     const responseData = response.data;
    //     form.find('input[name="titulo"]').val(responseData.titulo);
    //     self.#buscarDocumentoModeloTipo(responseData.area_juridica_id);
    //     self.#quillQueueManager.enqueue(() => {
    //         self._classQuillEditor.getQuill.setContents(responseData.conteudo);
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

    async #buscarDocumentoModeloTipo(selected_id = null) {
        try {
            const self = this;
            let options = selected_id ? { selectedIdOption: selected_id } : {};
            const selector = $(`#documento_modelo_tipo_id${self._objConfigs.sufixo}`);
            await commonFunctions.fillSelect(selector, self._objConfigs.url.baseDocumentoModeloTipo, options); 
            return true
        } catch (error) {
            return false;
        }
    }

    saveButtonAction() {
        const self = this;
        let data = {
            nome: $(`#nome${self.getSufixo}`).val(),
            conteudo: self._classQuillEditor.getQuill.getContents(),
            clientes: self._classQuillEditor._getClientesNaTela(),
        }

        console.log(data);

        if (self.#saveVerifications(data, formRegistration)) {
            self._save(data, self._objConfigs.url.base, {
                success: 'Modelo cadastrado com sucesso!',
                returnObjectSuccess: true,
                redirectBln: true,
            });
        }
        return false;
    }

    #saveVerifications(data, formRegistration) {
        const self = this;
        if (self._action == enumAction.POST) {
            let blnSave = commonFunctions.verificationData(data.nome, { field: $(`#nome${self.getSufixo}`), messageInvalid: 'O nome do modelo deve ser informado.', setFocus: true });
            blnSave = commonFunctions.verificationData(data.area_juridica_id, { field: formRegistration.find('select[name="area_juridica_id"]'), messageInvalid: 'A Área Jurídica deve ser selecionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            // blnSave = commonFunctions.verificationData(data.conteudo, { field: formRegistration.find('textarea[name="conteudo"]'), messageInvalid: 'A descrição deve ser preenchida.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            return blnSave;
        }
        return true;
    }


}

$(function () {
    new PageDocumentoModeloForm();
});