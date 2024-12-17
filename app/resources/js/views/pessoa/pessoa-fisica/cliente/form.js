import { commonFunctions } from "../../../../commons/commonFunctions";
import { connectAjax } from "../../../../commons/connectAjax";
import { enumAction } from "../../../../commons/enumAction";
import { modalMessage } from "../../../../components/comum/modalMessage";
import { modalPessoaDocumento } from "../../../../components/pessoas/modalPessoaDocumento";
import { modalSelecionarDocumentoTipo } from "../../../../components/pessoas/modalSelecionarDocumentoTipo";
import { modalEscolaridadeTenant } from "../../../../components/tenant/modalEscolaridadeTenant";
import { modalEstadoCivilTenant } from "../../../../components/tenant/modalEstadoCivilTenant";
import { modalSexoTenant } from "../../../../components/tenant/modalSexoTenant";
import { RedirectHelper } from "../../../../helpers/RedirectHelper";
import { URLHelper } from "../../../../helpers/URLHelper";
import { UUIDHelper } from "../../../../helpers/UUIDHelper";

class PageClientePFForm {

    #objConfigs = {
        url: {
            base: window.apiRoutes.basePessoaFisica,
            basePessoaPerfil: window.apiRoutes.basePessoaPerfil,
            baseEstadoCivilTenant: window.apiRoutes.baseEstadoCivilTenant,
            baseEscolaridadeTenant: window.apiRoutes.baseEscolaridadeTenant,
            baseSexoTenant: window.apiRoutes.baseSexoTenant,
        },
        sufixo: 'PageClientePFForm',
        data: {
            pessoa_dados_id: undefined,
            pessoa_perfil_tipo_id: window.Enums.PessoaPerfilTipoEnum.CLIENTE,
            documentosNaTela: [],
        },
    };
    #action;
    #idRegister;

    constructor() {
        const objData = {
            objConfigs: this.#objConfigs,
            extraConfigs: {
                modeParent: 'searchAndUse',
            }
        }
        this.initEvents();
    }

    async initEvents() {
        const self = this;
        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self.#idRegister = uuid;
            const url = `${self.#objConfigs.url.base}/${self.#idRegister}`;
            this.#action = enumAction.PUT;
            await self.#buscarDados();
        } else {
            this.#buscarEscolaridade();
            this.#buscarEstadoCivil();
            this.#buscarSexo();

            this.#action = enumAction.POST;
        }

        self.#addEventosBotoes();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#btnOpenEstadoCivilTenant${self.#objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalEstadoCivilTenant();
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
                        self.#buscarEstadoCivil(response.selected.id);
                    } else {
                        self.#buscarEstadoCivil();
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnOpenEscolaridadeTenant${self.#objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalEscolaridadeTenant();
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
                        self.#buscarEscolaridade(response.selected.id);
                    } else {
                        self.#buscarEscolaridade();
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnOpenSexoTenant${self.#objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalSexoTenant();
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
                        self.#buscarSexo(response.selected.id);
                    } else {
                        self.#buscarSexo();
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnAdicionarDocumento${self.#objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalSelecionarDocumentoTipo();
                objModal.setDataEnvModal = {
                    pessoa_tipo_aplicavel: [
                        window.Enums.PessoaTipoEnum.PESSOA_FISICA,
                    ],
                };
                objModal.setFocusElementWhenClosingModal = btn;
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    await self.#inserirDocumento(response.register, true);
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnSave${self.#objConfigs.sufixo}`).on('click', async function (e) {
            e.preventDefault();
            self.#saveButtonAction();
        });
    }

    async #buscarDados() {
        const self = this;

        try {
            await commonFunctions.loadingModalDisplay();

            const response = await self.#getRecurse({ urlApi: self.#objConfigs.url.basePessoaPerfil });

            if (response?.data) {
                const responseData = response.data;
                const pessoaDados = responseData.pessoa.pessoa_dados;
                const form = $(`#formDados${self.#objConfigs.sufixo}`);

                self.#objConfigs.data.pessoa_dados_id = pessoaDados.id;
                form.find('input[name="nome"]').val(pessoaDados.nome);
                form.find('input[name="mae"]').val(pessoaDados.mae);
                form.find('input[name="pai"]').val(pessoaDados.pai);
                form.find('input[name="nacionalidade"]').val(pessoaDados.nacionalidade);
                form.find('input[name="nascimento_cidade"]').val(pessoaDados.nascimento_cidade);
                form.find('input[name="nascimento_estado"]').val(pessoaDados.nascimento_estado);
                form.find('input[name="nascimento_data"]').val(pessoaDados.nascimento_data);
                self.#buscarEscolaridade(pessoaDados.escolaridade_id);
                self.#buscarEstadoCivil(pessoaDados.estado_civil_id);
                self.#buscarSexo(pessoaDados.sexo_id);
                form.find('textarea[name="observacao"]').val(pessoaDados.observacao);
                form.find('input[name="ativo_bln"]').prop('checked', pessoaDados.ativo_bln);

                if (responseData.pessoa?.documentos.length) {
                    responseData.pessoa.documentos.map(documento => {
                        // Não verifica se o limite de documentos foi atingido porque está vindo direto do banco
                        self.#inserirDocumento(documento);
                    });
                }
            } else {
                $('input, textarea, select, button').prop('disabled', true);
            }

        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
    }

    #verificaDocumentoQuantidadePermitida(documentoAInserir) {
        const self = this;
        const docsNaTela = self.#objConfigs.data.documentosNaTela;

        // Obtém a quantidade permitida para este tipo de documento
        const quantidadePermitida = documentoAInserir.documento_tipo_tenant.configuracao?.quantidade_permitida;

        if (quantidadePermitida) {
            // Filtra os documentos na tela que possuem o mesmo documento_tipo_tenant_id
            const documentosComMesmoTipo = docsNaTela.filter(
                doc => doc.documento_tipo_tenant_id === documentoAInserir.documento_tipo_tenant_id
            );

            // Verifica se ultrapassou o limite permitido
            if (documentosComMesmoTipo.length >= quantidadePermitida) {
                if (quantidadePermitida === 1) {
                    commonFunctions.generateNotification(`Este documento já foi adicionado.`, 'warning');
                    return false;
                } else {
                    commonFunctions.generateNotification(`O limite de ${quantidadePermitida} documentos para este tipo foi atingido.`, 'warning');
                    return false;
                }
            }
        }
        return true;
    }

    async #inserirDocumento(item, validarLimite = false) {
        const self = this;
        const divDocumento = $(`#divDocumento${self.#objConfigs.sufixo}`);

        const nomeDoc = item.documento_tipo_tenant.documento_tipo.nome;
        const numero = item.numero;
        let camposAdicionais = '';

        if (!item?.idCol) {
            if (validarLimite) {
                if (!self.#verificaDocumentoQuantidadePermitida(item)) {
                    return false;
                }
            }

            item.idCol = UUIDHelper.generateUUID();

            let strCard = `
            <div id="${item.idCol}" class="col">
                <div class="card">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title d-flex align-items-center justify-content-between mb-0">
                                <span class="text-truncate spanTitle">${nomeDoc}</span>
                                <div>
                                    <div class="dropdown">
                                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><button type="button" class="dropdown-item fs-6 btn-edit" title="Editar documento ${nomeDoc}">Editar</button></li>
                                            <li><button type="button" class="dropdown-item fs-6 btn-delete" title="Excluir documento ${nomeDoc}">Excluir</button></li>
                                        </ul>
                                    </div>
                                </div>
                            </h5>
                            <p class="card-text pNumero">${numero}</p>
                            ${camposAdicionais}
                        </div>
                    </div>
                </div>
            </div>`;

            divDocumento.append(strCard);
            self.#addEventosDocumento(item);
            self.#objConfigs.data.documentosNaTela.push(item);
        } else {
            $(`#${item.idCol}`).find('.spanTitle').html(nomeDoc);
            $(`#${item.idCol}`).find('.pNumero').html(numero);

            const indexDoc = self.#pesquisaIndexDocumentoNaTela(item);
            if (indexDoc != -1) {
                self.#objConfigs.data.documentosNaTela[indexDoc] = item;
            }
        }

        return true;
    }

    #pesquisaIndexDocumentoNaTela(item, prop = 'idCol') {
        const self = this;
        return self.#objConfigs.data.documentosNaTela.findIndex(doc => doc[prop] === item[prop]);
    }

    async #addEventosDocumento(item) {
        const self = this;

        $(`#${item.idCol}`).find('.btn-edit').on('click', async function () {
            const docNaTela = self.#objConfigs.data.documentosNaTela;
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const indexDoc = self.#pesquisaIndexDocumentoNaTela(item);
                if (indexDoc != -1) {
                    const doc = docNaTela[indexDoc];

                    const objModal = new modalPessoaDocumento();
                    objModal.setDataEnvModal = {
                        register: doc,
                    };
                    const response = await objModal.modalOpen();
                    if (response.refresh && response.register) {
                        await self.#inserirDocumento(response.register);
                    }
                } else {
                    console.error('Documento na tela não encontrado. Docs:', docNaTela);
                    console.error('Item buscado:', item);
                    commonFunctions.generateNotification('Documento na tela não encontrado.', 'error');
                    return false;
                }

            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#${item.idCol}`).find(`.btn-delete`).click(async function () {
            try {
                const docNaTela = self.#objConfigs.data.documentosNaTela;
                const indexDoc = self.#pesquisaIndexDocumentoNaTela(item);
                if (indexDoc != -1) {
                    const doc = docNaTela[indexDoc] = item;

                    const objMessage = new modalMessage();
                    objMessage.setDataEnvModal = {
                        title: `Exclusão de Documento`,
                        message: `Confirma a exclusão do documento <b>${doc.documento_tipo_tenant.nome}</b>?`,
                    };
                    objMessage.setFocusElementWhenClosingModal = $(this);
                    const result = await objMessage.modalOpen();
                    if (result.confirmResult) {
                        docNaTela.splice(indexDoc, 1);
                        $(`#${doc.idCol}`).remove();
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            }
        });
    }

    async #buscarEscolaridade(selected_id = null) {
        try {
            const self = this;
            let options = { firstOptionValue: null };
            selected_id ? Object.assign(options, { selectedIdOption: selected_id }) : null;
            const select = $(`#escolaridade_id${self.#objConfigs.sufixo}`);
            await commonFunctions.fillSelect(select, self.#objConfigs.url.baseEscolaridadeTenant, options); 0
            return true
        } catch (error) {
            return false;
        }
    }

    async #buscarEstadoCivil(selected_id = null) {
        try {
            const self = this;
            let options = { firstOptionValue: null };
            selected_id ? Object.assign(options, { selectedIdOption: selected_id }) : null;
            const select = $(`#estado_civil_id${self.#objConfigs.sufixo}`);
            await commonFunctions.fillSelect(select, self.#objConfigs.url.baseEstadoCivilTenant, options); 0
            return true
        } catch (error) {
            return false;
        }
    }

    async #buscarSexo(selected_id = null) {
        try {
            const self = this;
            let options = { firstOptionValue: null };
            selected_id ? Object.assign(options, { selectedIdOption: selected_id }) : null;
            const select = $(`#sexo_id${self.#objConfigs.sufixo}`);
            await commonFunctions.fillSelect(select, self.#objConfigs.url.baseSexoTenant, options); 0
            return true
        } catch (error) {
            return false;
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

    #saveButtonAction() {
        const self = this;
        const formRegistration = $(`#formDados${self.#objConfigs.sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        data.documentos = self.#objConfigs.data.documentosNaTela.map(item => {
            return {
                id: item.id,
                documento_tipo_tenant_id: item.documento_tipo_tenant_id,
                numero: item.numero,
                // campos_adicionais: item.campos_adicionais,
            }
        });
        data.pessoa_perfil_tipo_id = self.#objConfigs.data.pessoa_perfil_tipo_id;

        data = Object.fromEntries(
            Object.entries(data).map(([key, value]) => {
                if (value === "null") {
                    value = null;
                }
                return [key, value];
            })
        );

        if (self.#saveVerifications(data, formRegistration)) {
            self.#save(data, self.#objConfigs.url.base);
        }
        return false;
    }

    #saveVerifications(data, formRegistration) {
        const self = this;
        let blnSave = commonFunctions.verificationData(data.nome, { field: formRegistration.find('input[name="nome"]'), messageInvalid: 'O campo <b>nome</b> deve ser informado.', setFocus: true });
        // blnSave = commonFunctions.verificationData(data.area_juridica_id, { field: formRegistration.find('select[name="area_juridica_id"]'), messageInvalid: 'A Área Jurídica deve ser selecionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        return blnSave;
    }

    async #save(data, urlApi, options = {}) {
        const self = this;
        const {
            btnSave = $(`#btnSave${self.#objConfigs.sufixo}`),
        } = options;

        try {
            commonFunctions.simulateLoading(btnSave);
            const obj = new connectAjax(urlApi);
            obj.setAction(self.#action);
            obj.setData(data);
            if (self.#action === enumAction.PUT) {
                obj.setParam(self.#objConfigs.data.pessoa_dados_id);
            }
            const response = await obj.envRequest();

            if (response) {
                RedirectHelper.redirectWithUUIDMessage(window.frontRoutes.frontRedirectForm, 'Dados enviados com sucesso!', 'success');
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
        finally {
            commonFunctions.simulateLoading(btnSave, false);
        };
    }

}

$(function () {
    new PageClientePFForm();
});