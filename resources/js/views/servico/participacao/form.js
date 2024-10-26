import { commonFunctions } from "../../../commons/commonFunctions";
import { connectAjax } from "../../../commons/connectAjax";
import { enumAction } from "../../../commons/enumAction";
import { modalMessage } from "../../../components/comum/modalMessage";
import { modalNome } from "../../../components/comum/modalNome";
import { modalPessoa } from "../../../components/pessoas/modalPessoa";
import { modalSelecionarPerfil } from "../../../components/pessoas/modalSelecionarPerfil";
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
            participantesNaTela: []
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
                await self.#inserirParticipanteNaTela(Object.assign(dados_participacao, response.register));
            }
        }

        $(`#btnInserirPessoa${self.#sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const dataEnvModalAppend = {
                    perfis_busca: window.Statics.PerfisPermitidoParticipacaoServico,
                };
                const objModal = new modalPessoa({ dataEnvModal: dataEnvModalAppend });
                const response = await objModal.modalOpen();
                if (response.refresh && response.selected) {
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

        const evento = () => {
            self.#inserirParticipanteNaTela({
                "participacao_registro_tipo_id": 1,
                "pessoa_perfil": {
                    "id": "9d53520a-ac54-404d-b8d6-0a805090d56e",
                    "tenant_id": "jsadvogados",
                    "pessoa_id": "9d53520a-aaa1-4293-b0e9-90226c240ec4",
                    "perfil_tipo_id": 3,
                    "observacao": null,
                    "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
                    "created_ip": "127.0.0.1",
                    "created_at": "2024-10-24 20:17:22",
                    "updated_user_id": null,
                    "updated_ip": null,
                    "updated_at": null,
                    "deleted_user_id": null,
                    "deleted_ip": null,
                    "deleted_at": null,
                    "perfil_tipo": {
                        "id": 3,
                        "nome": "Cliente",
                        "descricao": "Perfil para clientes.",
                        "tabela_ref": null,
                        "tabela_model": null,
                        "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
                        "created_ip": "127.0.0.1",
                        "created_at": "2024-10-24 20:17:22",
                        "updated_user_id": null,
                        "updated_ip": null,
                        "updated_at": null,
                        "deleted_user_id": null,
                        "deleted_ip": null,
                        "deleted_at": null
                    },
                    "pessoa": {
                        "id": "9d53520a-aaa1-4293-b0e9-90226c240ec4",
                        "tenant_id": "jsadvogados",
                        "pessoa_dados_type": "App\\Models\\Pessoa\\PessoaFisica",
                        "pessoa_dados_id": "9d53520a-a8a9-43de-b800-5758a35565e3",
                        "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
                        "created_ip": "127.0.0.1",
                        "created_at": "2024-10-24 20:17:22",
                        "updated_user_id": null,
                        "updated_ip": null,
                        "updated_at": null,
                        "deleted_user_id": null,
                        "deleted_ip": null,
                        "deleted_at": null,
                        "pessoa_perfil": [
                            {
                                "id": "9d53520a-ac54-404d-b8d6-0a805090d56e",
                                "tenant_id": "jsadvogados",
                                "pessoa_id": "9d53520a-aaa1-4293-b0e9-90226c240ec4",
                                "perfil_tipo_id": 3,
                                "observacao": null,
                                "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
                                "created_ip": "127.0.0.1",
                                "created_at": "2024-10-24 20:17:22",
                                "updated_user_id": null,
                                "updated_ip": null,
                                "updated_at": null,
                                "deleted_user_id": null,
                                "deleted_ip": null,
                                "deleted_at": null,
                                "perfil_tipo": {
                                    "id": 3,
                                    "nome": "Cliente",
                                    "descricao": "Perfil para clientes.",
                                    "tabela_ref": null,
                                    "tabela_model": null,
                                    "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
                                    "created_ip": "127.0.0.1",
                                    "created_at": "2024-10-24 20:17:22",
                                    "updated_user_id": null,
                                    "updated_ip": null,
                                    "updated_at": null,
                                    "deleted_user_id": null,
                                    "deleted_ip": null,
                                    "deleted_at": null
                                }
                            }
                        ],
                        "pessoa_dados": {
                            "id": "9d53520a-a8a9-43de-b800-5758a35565e3",
                            "tenant_id": "jsadvogados",
                            "nome": "Dr. Kamila de Arruda",
                            "mae": "Sofia Tamoio",
                            "pai": "Simon Galindo",
                            "nascimento_data": null,
                            "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
                            "created_ip": "127.0.0.1",
                            "created_at": "2024-10-24 20:17:22",
                            "updated_user_id": null,
                            "updated_ip": null,
                            "updated_at": null,
                            "deleted_user_id": null,
                            "deleted_ip": null,
                            "deleted_at": null
                        },
                        "idTr": "bba671d0-1a1a-4e50-b748-90f6209723bf",
                        "idTrSelecionado": "df03d7b1-c521-4523-b059-e5c533f2ea13",
                        "idsTrs": [
                            "bba671d0-1a1a-4e50-b748-90f6209723bf"
                        ]
                    }
                },
                "participacao_tipo_id": "9d538aa2-1764-4cae-b157-a4408b36cb74",
                "valor_tipo": "porcentagem",
                "valor": 23.33,
                "observacao": "",
                "idCard": "f7364cbc-b39c-46c7-867b-cf79736c469f"
            });
            self.#inserirParticipanteNaTela(
                {
                    "participacao_registro_tipo_id": 2,
                    "nome_grupo": "Rachadinha",
                    "participacao_tipo_id": "9d538aa9-bf98-496b-8018-40f20f4afc45",
                    "valor_tipo": "porcentagem",
                    "valor": 23.33,
                    "observacao": "",
                    "idCard": "97546ae8-7a7c-4bcc-914f-b0e5dea5f962"
                }
            );
        }
        const openModalTest = async () => {
            const perfis_busca = window.Statics.PerfisPermitidoParticipacaoServico.map(item => item.id);
            const objCode = new modalSelecionarPerfil();
            objCode.setDataEnvModal = {
                perfis_permitidos: perfis_busca,
                perfis_opcoes: [
                    {
                        "id": "9d5426a5-bd48-4f8d-b278-f05b27f50d3c",
                        "tenant_id": "jsadvogados",
                        "pessoa_id": "9d5426a5-bbaa-4a88-8b99-fb021edf342c",
                        "perfil_tipo_id": 2,
                        "observacao": null,
                        "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
                        "created_ip": "127.0.0.1",
                        "created_at": "2024-10-25 06:11:52",
                        "updated_user_id": null,
                        "updated_ip": null,
                        "updated_at": null,
                        "deleted_user_id": null,
                        "deleted_ip": null,
                        "deleted_at": null,
                        "perfil_tipo": {
                            "id": 2,
                            "nome": "Parceiro",
                            "descricao": "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
                            "tabela_ref": null,
                            "tabela_model": null,
                            "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
                            "created_ip": "127.0.0.1",
                            "created_at": "2024-10-24 20:17:22",
                            "updated_user_id": null,
                            "updated_ip": null,
                            "updated_at": null,
                            "deleted_user_id": null,
                            "deleted_ip": null,
                            "deleted_at": null
                        }
                    },
                    {
                        "id": "9d5426a5-bee9-465a-a68e-ba227c420984",
                        "tenant_id": "jsadvogados",
                        "pessoa_id": "9d5426a5-bbaa-4a88-8b99-fb021edf342c",
                        "perfil_tipo_id": 3,
                        "observacao": null,
                        "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
                        "created_ip": "127.0.0.1",
                        "created_at": "2024-10-25 06:11:52",
                        "updated_user_id": null,
                        "updated_ip": null,
                        "updated_at": null,
                        "deleted_user_id": null,
                        "deleted_ip": null,
                        "deleted_at": null,
                        "perfil_tipo": {
                            "id": 3,
                            "nome": "Cliente",
                            "descricao": "Perfil para clientes.",
                            "tabela_ref": null,
                            "tabela_model": null,
                            "created_user_id": "340c0b8d-2731-472c-bd60-cc2c1fd936ba",
                            "created_ip": "127.0.0.1",
                            "created_at": "2024-10-24 20:17:22",
                            "updated_user_id": null,
                            "updated_ip": null,
                            "updated_at": null,
                            "deleted_user_id": null,
                            "deleted_ip": null,
                            "deleted_at": null
                        }
                    }
                ],
            };
            const retorno = await objCode.modalOpen();
            console.log(retorno);
        }
        // openModalTest();
        // openModalServicoParticipacao({ participacao_registro_tipo_id: 2, valor: 0, tipo_valor: 'porcentagem', nome: 'Rachadinha' });
        evento();
    }

    async #buscarParticipacaoTipo(id) {
        const self = this;
        return await self.#getRecurse({ idRegister: id, urlApi: self.#objConfigs.url.baseParticipacaoTipo });
    }

    async #inserirParticipanteNaTela(item) {
        const self = this;
        const divParticipantes = $(`#divParticipantes${self.#sufixo}`);
        console.log(item);

        let nome = '';
        let btnsAppend = '';
        switch (item.participacao_registro_tipo_id) {
            case 1:
                nome = item.pessoa_perfil.pessoa.pessoa_dados.nome;
                break;
            case 2:
                nome = item.nome_grupo;
                btnsAppend += `<button type="button" class="btn btn-outline-primary btn-sm btn-edit-name border-0">Editar Nome</button>`;
                break;
            default:
                commonFunctions.generateNotification('Tipo de registro de participação não informado.', 'error');
                console.error('Tipo de registro de participação não informado.', item);
                return false;
        }

        let participacao_tipo = item?.participacao_tipo?.nome ?? null;
        if (!participacao_tipo && item.participacao_tipo_id) {
            const response = await self.#buscarParticipacaoTipo(item.participacao_tipo_id);
            if (response) {
                participacao_tipo = response.data;
            } else {
                participacao_tipo = { nome: 'Erro de busca' }
            }
        } else {
            commonFunctions.generateNotification('Tipo de participação não informado.', 'error');
            console.error('Tipo de participação não informado.', item);
            return false;
        }

        const naTela = self.#verificaRegistroNaTela(item);
        if (naTela) {
            commonFunctions.generateNotification(`Participante <b>${naTela.pessoa_perfil.pessoa.pessoa_dados.nome}</b> já foi inserido(a) para este tipo de participação.`, 'error');
            return false;
        }

        let valor_tipo = ''
        let valor = commonFunctions.formatWithCurrencyCommasOrFraction(item.valor);
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
                    <span class="spanNome">${nome}</span>
                    <div>
                        <div class="d-grid gap-2 d-flex justify-content-end">
                            ${btnsAppend}
                            <button type="button" class="btn btn-outline-primary btn-sm btn-edit border-0">Editar</button>
                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete border-0">Excluir</button>
                        </div>
                    </div>
                </h5>
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3">
                    <div class="col">
                        <div class="form-text">Participação</div>
                        <label class="form-label lblParticipacao">${participacao_tipo.nome}</label>
                    </div>
                    <div class="col">
                        <div class="form-text">Método</div>
                        <label class="form-label lblValorTipo">${valor_tipo}</label>
                    </div>
                    <div class="col">
                        <div class="form-text">Valor</div>
                        <label class="form-label lblValor">${valor}</label>
                    </div>
                </div>
            </div>`;


        item.idCard = UUIDHelper.generateUUID();
        self.#objConfigs.data.participantesNaTela.push(item);

        divParticipantes.append(`<div id="${item.idCard}" class="card">${strCard}</div>`);

        self.#addEventoParticipante(item);
        await self.#atualizaPorcentagemLivre(item);
    }

    async #atualizaParticipanteNaTela(item) {
        const self = this;

        let participacao_tipo = item?.participacao_tipo?.nome ?? null;
        if (!participacao_tipo && item.participacao_tipo_id) {
            const response = await self.#buscarParticipacaoTipo(item.participacao_tipo_id);
            if (response) {
                participacao_tipo = response.data;
            } else {
                participacao_tipo = { nome: 'Erro de busca' }
            }
        } else {
            commonFunctions.generateNotification('Tipo de participação não informado.', 'error');
            console.error('Tipo de participação não informado.', item);
            return false;
        }

        let valor_tipo = ''
        let valor = commonFunctions.formatWithCurrencyCommasOrFraction(item.valor);
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

        for (const element of self.#objConfigs.data.participantesNaTela) {
            if (element.idCard == item.idCard) {
                element.participacao_tipo_id = item.participacao_tipo_id;
                element.valor_tipo = item.valor_tipo;
                element.valor = item.valor;
                break;
            }
        }

        $(`#${item.idCard} .lblParticipacao`).text(participacao_tipo.nome);
        $(`#${item.idCard} .lblValorTipo`).text(valor_tipo);
        $(`#${item.idCard} .lblValor`).text(valor);

        await self.#atualizaPorcentagemLivre();
    }

    async #addEventoParticipante(item) {
        const self = this;

        $(`#${item.idCard} .btn-edit`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const porcentagem_ocupada = self.#objConfigs.data.porcentagem_ocupada;
                if (item.valor_tipo == 'porcentagem') {
                    porcentagem_ocupada -= item.valor;
                }
                const objModal = new modalServicoParticipacao();
                objModal.setDataEnvModal = {
                    dados_participacao: item,
                    porcentagem_ocupada: porcentagem_ocupada,
                }
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    await self.#atualizaParticipanteNaTela(Object.assign(item, response.register));
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        if (item.participacao_registro_tipo_id == window.Enums.ParticipacaoRegistroTipoEnum.GRUPO) {

            $(`#${item.idCard} .btn-edit-name`).on('click', async function () {

                let registro = undefined;
                for (const element of self.#objConfigs.data.participantesNaTela) {
                    if (element.idCard == item.idCard) {
                        registro = element;
                        break;
                    }
                }
                console.log(registro)
                const btn = $(this);
                commonFunctions.simulateLoading(btn);
                try {
                    const objModalNome = new modalNome();
                    objModalNome.setDataEnvModal = {
                        title: 'Novo grupo',
                        mensagem: 'Informe o nome do grupo',
                        nome: registro.nome_grupo,
                    }
                    const response = await objModalNome.modalOpen();
                    if (response.refresh) {
                        registro.nome_grupo = response.name;
                        $(`#${item.idCard} .spanNome`).text(registro.nome_grupo);
                    }
                } catch (error) {
                    commonFunctions.generateNotificationErrorCatch(error);
                } finally {
                    commonFunctions.simulateLoading(btn, false);
                }
            });
        }
    }

    #verificaRegistroNaTela(item) {
        const self = this;

        if (item.participacao_registro_tipo_id == window.Enums.ParticipacaoRegistroTipoEnum.PERFIL) {
            for (const element of self.#objConfigs.data.participantesNaTela) {
                if (element.participacao_registro_tipo_id != window.Enums.ParticipacaoRegistroTipoEnum.PERFIL) {
                    continue;
                }

                if (element.pessoa_perfil.id == item.pessoa_perfil.id &&
                    element.participacao_tipo_id == item.participacao_tipo_id
                ) {
                    return element;
                }
            }
        }
        return null;
    }

    async #atualizaPorcentagemLivre() {
        const self = this;
        let porcentagemOcupada = 0;
        let valorFixo = 0;

        for (const itemTela of self.#objConfigs.data.participantesNaTela) {
            if (itemTela.valor_tipo == 'porcentagem') {
                porcentagemOcupada += itemTela.valor;
            } else {
                valorFixo += itemTela.valor;
            }
        }
        self.#objConfigs.data.porcentagem_ocupada = porcentagemOcupada;
        self.#objConfigs.data.valor_fixo = valorFixo;

        let valorMinimo = 0;
        if (porcentagemOcupada > 0 && valorFixo > 0) {
            valorMinimo = valorFixo + 1;
        } else if (valorFixo > 0) {
            valorMinimo = valorFixo;
        }

        $(`#valor_fixo${self.#sufixo}`).text(`${commonFunctions.formatWithCurrencyCommasOrFraction(valorFixo)}`);
        $(`#porcentagem${self.#sufixo}`).text(`${commonFunctions.formatWithCurrencyCommasOrFraction(porcentagemOcupada)}`);
        $(`#valor_minimo${self.#sufixo}`).text(`${commonFunctions.formatWithCurrencyCommasOrFraction(valorMinimo)}`);

        commonFunctions.atualizarProgressBar($(`#progressBar${self.#sufixo}`), porcentagemOcupada);
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