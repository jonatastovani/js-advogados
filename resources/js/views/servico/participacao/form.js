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
            this.#action = enumAction.PUT;
            self.#buscarDados();
        } else {
            this.#action = enumAction.POST;
            $(`#nome${self.#sufixo}`).trigger('focus');
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
                    await openModalServicoParticipacao({
                        participacao_registro_tipo_id: window.Enums.ParticipacaoRegistroTipoEnum.PERFIL, referencia: response.selected,
                        referencia_id: response.selected.id,
                    });
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
                    await openModalServicoParticipacao({
                        participacao_registro_tipo_id: window.Enums.ParticipacaoRegistroTipoEnum.GRUPO,
                        nome_grupo: response.name
                    });
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
        }

        // openModalTest();
    }

    async #buscarParticipacaoTipo(id) {
        const self = this;
        return await self.#getRecurse({ idRegister: id, urlApi: self.#objConfigs.url.baseParticipacaoTipo });
    }

    async #inserirParticipanteNaTela(item) {
        const self = this;
        const divParticipantes = $(`#divParticipantes${self.#sufixo}`);
        item.idCard = UUIDHelper.generateUUID();

        let nome = '';
        let btnsAppend = '';
        let accordionIntegrantes = '';
        let displayObservacao = 'none';

        switch (item.participacao_registro_tipo_id) {
            case window.Enums.ParticipacaoRegistroTipoEnum.PERFIL:
                nome = item.referencia.pessoa.pessoa_dados.nome;
                break;
            case window.Enums.ParticipacaoRegistroTipoEnum.GRUPO:
                nome = item.nome_grupo;
                btnsAppend += `<li><button type="button" class="dropdown-item fs-6 btn-add-pessoa">Inserir Pessoa</button></li>`;
                btnsAppend += `<li><button type="button" class="dropdown-item fs-6 btn-edit-name">Editar Nome</button></li>`;
                accordionIntegrantes = self.#accordionIntegrantesGrupo(item);
                break;
            default:
                commonFunctions.generateNotification('Tipo de registro de participação não informado.', 'error');
                console.error('Tipo de registro de participação não informado.', item);
                return false;
        }

        let participacao_tipo = item.participacao_tipo ?? null;
        if (!participacao_tipo) {
            if (item.participacao_tipo_id) {
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
        }

        if (item.observacao) {
            displayObservacao = 'block';
        }

        const naTela = self.#verificaRegistroNaTela(item);
        if (naTela) {
            commonFunctions.generateNotification(`Participante <b>${naTela.referencia.pessoa.pessoa_dados.nome}</b> já foi inserido(a) para este tipo de participação.`, 'error');
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
                            <div class="dropdown">
                                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    ${btnsAppend}
                                    <li><button type="button" class="dropdown-item fs-6 btn-edit">Editar</button></li>
                                    <li><button type="button" class="dropdown-item fs-6 btn-delete">Excluir</button></li>
                                </ul>
                            </div>
                    </div>
                </h5>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4">
                    <div class="col">
                        <div class="form-text">Participação</div>
                        <label class="form-label text-truncate lblParticipacao">${participacao_tipo.nome}</label>
                    </div>
                    <div class="col">
                        <div class="form-text">Método</div>
                        <label class="form-label lblValorTipo">${valor_tipo}</label>
                    </div>
                    <div class="col">
                        <div class="form-text">Valor</div>
                        <label class="form-label text-truncate lblValor">${valor}</label>
                    </div>
                </div>
                <div class="row rowObservacao" style="display: ${displayObservacao};">
                    <div class="col">
                        <div class="form-text">Observação</div>
                        <label class="form-label text-truncate lblObservacao" title="${item.observacao ?? ''}">${item.observacao ?? ''}</label>
                    </div>
                </div>
                ${accordionIntegrantes}
            </div>`;

        self.#objConfigs.data.participantesNaTela.push(item);

        divParticipantes.append(`<div id="${item.idCard}" class="card">${strCard}</div>`);

        self.#addEventoParticipante(item);
        await self.#atualizaPorcentagemLivre(item);
        return item;
    }

    async #atualizaParticipanteNaTela(item) {
        const self = this;

        let participacao_tipo = item.participacao_tipo ?? null;
        if (!participacao_tipo || participacao_tipo && (participacao_tipo.id != item.participacao_tipo_id)) {
            if (item.participacao_tipo_id) {
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
        if (item.observacao) {
            $(`#${item.idCard} .lblObservacao`).html(item.observacao);
            $(`#${item.idCard} .rowObservacao`).show('fast');
        } else {
            $(`#${item.idCard} .rowObservacao`).hide('fast');
        }
        await self.#atualizaPorcentagemLivre();
    }

    async #addEventoParticipante(item) {
        const self = this;

        $(`#${item.idCard} .btn-edit`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                let porcentagem_ocupada = self.#objConfigs.data.porcentagem_ocupada;
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

        $(`#${item.idCard} .btn-delete`).on('click', async function () {
            try {
                const obj = new modalMessage();
                obj.setDataEnvModal = {
                    title: 'Remoção de Participante',
                    message: 'Tem certeza que deseja remover este participante?',
                };
                obj.setFocusElementWhenClosingModal = $(this);
                const result = await obj.modalOpen();
                if (result.confirmResult) {

                    $(`#${item.idCard}`).remove();
                    const participantes = self.#objConfigs.data.participantesNaTela;
                    const indexPart = participantes.findIndex(participante => participante.idCard === item.idCard);

                    if (indexPart > -1) {
                        participantes.splice(indexPart, 1);
                    }

                    self.#atualizaPorcentagemLivre();
                    commonFunctions.generateNotification('Participante removido.', 'success');
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
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

            $(`#${item.idCard} .btn-add-pessoa`).on('click', async function () {
                const btn = $(this);
                commonFunctions.simulateLoading(btn);
                try {
                    const dataEnvModalAppend = {
                        perfis_busca: window.Statics.PerfisPermitidoParticipacaoServico,
                    };
                    const objModal = new modalPessoa({ dataEnvModal: dataEnvModalAppend });
                    const response = await objModal.modalOpen();
                    if (response.refresh && response.selected) {
                        await self.#inserirIntegrante(item, {
                            participacao_registro_tipo_id: window.Enums.ParticipacaoRegistroTipoEnum.PERFIL,
                            referencia: response.selected,
                            referencia_id: response.selected.id,
                        });

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

                if (element.referencia.id == item.referencia.id &&
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

    async #inserirIntegrante(item, integrante) {
        const self = this;
        const rowIntegrantes = $(`#accordionIntegrantes${item.idCard} .rowIntegrantes`);
        integrante.idCard = UUIDHelper.generateUUID();

        let nome = '';
        let tipoReferencia = '';
        switch (integrante.participacao_registro_tipo_id) {
            case window.Enums.ParticipacaoRegistroTipoEnum.PERFIL:
                nome = integrante.referencia.pessoa.pessoa_dados.nome;
                tipoReferencia = `Perfil ${integrante.referencia.perfil_tipo.nome}`;
                break;
            default:
                commonFunctions.generateNotification('Tipo de registro de participação não informado.', 'error');
                console.error('Tipo de registro de participação não informado.', item);
                return false;
        }

        rowIntegrantes.append(`
            <div id="${integrante.idCard}" class="card">
                <div class="card-body">
                    <h5 class="card-title d-flex align-items-center justify-content-between">
                        <span>${nome}</span>
                        <div>
                            <div class="d-grid gap-2 d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-danger btn-sm btn-delete-integrante border-0">Excluir</button>
                            </div>
                        </div>
                    </h5>
                    <div class="row">
                        <div class="col">
                            <div class="form-text">Tipo Referência</div>
                            <label class="form-label">${tipoReferencia}</label>
                        </div>
                    </div>
                </div>
            </div>
            `);


        let element = self.#objConfigs.data.participantesNaTela.find(participante => participante.idCard == item.idCard);
        if (!element.integrantes) {
            element.integrantes = [];
        }
        element.integrantes.push(integrante);

        self.#atualizaQuantidadeIntegrantes(item.idCard);
        self.#addEventoPerfilIntegrante(item, integrante);
    }

    async #addEventoPerfilIntegrante(item, integrante) {
        const self = this;

        $(`#${integrante.idCard} .btn-delete-integrante`).on('click', async function () {
            try {
                const obj = new modalMessage();
                obj.setDataEnvModal = {
                    title: 'Remoção de Integrante',
                    message: 'Tem certeza que deseja remover este integrante?',
                };
                obj.setFocusElementWhenClosingModal = $(this);
                const result = await obj.modalOpen();
                if (result.confirmResult) {

                    $(`#${integrante.idCard}`).remove();
                    const participantes = self.#objConfigs.data.participantesNaTela;
                    const indexPart = participantes.findIndex(participante => participante.idCard === item.idCard);

                    if (indexPart > -1) {
                        const indexInt = participantes[indexPart].integrantes.findIndex(item => item.idCard === integrante.idCard);
                        if (indexInt > -1) {
                            participantes[indexPart].integrantes.splice(indexInt, 1);
                        }
                    }

                    self.#atualizaQuantidadeIntegrantes(item.idCard);
                    commonFunctions.generateNotification('Integrante removido.', 'success');
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            }
        });
    }

    #accordionIntegrantesGrupo(item) {
        return `
            <div class="accordion mt-2" id="accordionIntegrantes${item.idCard}">
                <div class="accordion-item">
                    <div class="accordion-header">
                        <button class="accordion-button py-1 collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseOne${item.idCard}" aria-expanded="true"
                            aria-controls="collapseOne${item.idCard}">
                            <span class="qtdIntegrantes">Nenhum integrante no grupo</span>
                        </button>
                    </div>
                    <div id="collapseOne${item.idCard}" class="accordion-collapse collapse"
                        data-bs-parent="#accordionIntegrantes${item.idCard}">
                        <div class="accordion-body">
                            <div class="row rowIntegrantes row-cols-1 g-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    #atualizaQuantidadeIntegrantes(idCard) {
        const self = this;
        let element = self.#objConfigs.data.participantesNaTela.find(item => item.idCard == idCard);
        const totalIntegrantes = element.integrantes.length;
        const qtdIntegrantes = $(`#accordionIntegrantes${idCard} .qtdIntegrantes`);
        let str = 'Nenhum integrante no grupo';

        if (totalIntegrantes === 1) {
            str = '1 integrante no grupo';
        } else if (totalIntegrantes > 1) {
            str = `${totalIntegrantes} integrantes no grupo`;
        }
        qtdIntegrantes.html(str);
    }

    #saveButtonAction() {
        const self = this;
        const formRegistration = $(`#form${self.#sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        data.participantes = self.#objConfigs.data.participantesNaTela;

        if (self.#saveVerifications(data, formRegistration)) {
            self.#save(data, self.#objConfigs.url.base);
        }
        return false;
    }

    #saveVerifications(data, formRegistration) {
        const self = this;
        console.log(data);
        let blnSave = commonFunctions.verificationData(data.nome, { field: formRegistration.find('input[name="nome"]'), messageInvalid: 'O <b>nome</b> do preset deve ser informado.', setFocus: true });

        let porcentagemOcupada = self.#objConfigs.data.porcentagem_ocupada;
        if (porcentagemOcupada > 0 && porcentagemOcupada < 100 || porcentagemOcupada > 100) {
            commonFunctions.generateNotification(`As somas das porcentagens deve ser igual a 100%. Porcentagem informada ${commonFunctions.formatWithCurrencyCommasOrFraction(porcentagemOcupada)}%`, 'warning');
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
            const form = $(`#form${self.#sufixo}`);
            if (response?.data) {
                const responseData = response.data;
                form.find('input[name="nome"]').val(responseData.nome).trigger('focus');
                form.find('input[name="descricao"]').val(responseData.descricao);

                await Promise.all(
                    responseData.participantes.map(async (participante) => {
                        const integrantes = participante.integrantes ?? [];
                        delete participante.integrantes;
                        const item = await self.#inserirParticipanteNaTela(participante);
                        await Promise.all(
                            integrantes.map(async (integrante) => {
                                await self.#inserirIntegrante(item, integrante);
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
}

$(function () {
    new PageServicoParticipacaoPresetForm();
});